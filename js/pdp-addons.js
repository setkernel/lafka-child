/**
 * pdp-addons.js — Lafka PDP redesign addon UX layer.
 *
 * Theme-side enhancements that sit on top of the lafka-plugin addon
 * engine output (which is intentionally theme-agnostic). The plugin
 * emits flat `<div class="product-addon">` blocks with checkboxes;
 * this script:
 *
 *   1. Makes each group's heading a click-to-collapse accordion. The
 *      heading itself toggles a `data-collapsed` attribute that the
 *      CSS rules in pdp-redesign.css read to show/hide form-rows and
 *      rotate the caret. Keyboard-accessible (Enter/Space).
 *
 *   2. Stamps a "<count> selected · +$X.XX" summary into each group's
 *      heading, refreshed on:
 *        - any input change inside the group (checkbox toggle)
 *        - the plugin's `lafka-product-addons-update` event (fires when
 *          the customer changes pizza size, which re-prices toppings)
 *        - the plugin's `updated_addons` event
 *      The total is computed from each input's data-raw-price, which
 *      addons.js v8.17.4 keeps in sync with the per-size matrix.
 *
 * Default group state is expanded — first-time visitors see what's
 * available. Customer can collapse groups they don't need to see.
 *
 * Currency formatting honours lafkaPdpCurrency localized in
 * functions.php so non-USD shops render correctly.
 *
 * @since lafka-child 5.10.4
 */
( function ( $ ) {
	'use strict';

	if ( ! $ || typeof $.fn !== 'object' ) {
		return;
	}

	function formatMoney( n ) {
		var c = window.lafkaPdpCurrency || {};
		var sym = c.symbol || '$';
		var dec = c.decimalSep || '.';
		var thou = c.thousandSep || ',';
		var decimals = typeof c.decimals === 'number' ? c.decimals : 2;
		var fixed = n.toFixed( decimals );
		var parts = fixed.split( '.' );
		var withSep = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, thou );
		var num = decimals > 0 ? withSep + dec + parts[ 1 ] : withSep;
		return c.position === 'right' ? num + sym : sym + num;
	}

	/**
	 * Gather currently-selected variation attribute values from the form.
	 * Looks at both WC's standard <select> pickers and lafka redesign's
	 * radio chips, mirroring what addons.js does internally.
	 *
	 * Returns { pa_size: 'medium', pa_crust: 'thin', ... }
	 */
	function getAttributeSelections( $form ) {
		var sels = {};
		$form.find( 'table.variations select' ).each( function () {
			var id = $( this ).attr( 'id' );
			if ( id ) {
				sels[ id ] = $( this ).find( 'option:selected' ).val();
			}
		} );
		$form.find( '.lafka-pdp-pickers input[type=radio]:checked' ).each( function () {
			var name = $( this ).attr( 'name' ) || '';
			if ( name.indexOf( 'attribute_' ) === 0 ) {
				sels[ name.substring( 'attribute_'.length ) ] = $( this ).val();
			}
		} );
		return sels;
	}

	/**
	 * Resolve the effective price for an addon input given the current
	 * variation selections.
	 *
	 * Why we compute this instead of trusting data-raw-price: the plugin's
	 * addons.js calls `.prop('data-raw-price', c)` when the size changes,
	 * which sets a JS property literally called "data-raw-price" on the
	 * element — it does NOT update the `data-raw-price` HTML attribute or
	 * jQuery's data cache. So `$el.data('raw-price')` returns the ORIGINAL
	 * page-render price, not the per-size price. The displayed wc_price
	 * span updates correctly (replaceWith on the span's innerHTML), but
	 * the stored price doesn't.
	 *
	 * Reading directly from the data-attribute-raw-prices JSON matrix is
	 * the source-of-truth approach — it's stable per page-render and
	 * always present when per-attribute pricing is configured.
	 */
	function getEffectivePrice( $input, attrSelections ) {
		var matrix = $input.data( 'attribute-raw-prices' );
		if ( matrix && typeof matrix === 'object' ) {
			for ( var attr in attrSelections ) {
				if ( ! Object.prototype.hasOwnProperty.call( attrSelections, attr ) ) {
					continue;
				}
				var val = attrSelections[ attr ];
				if ( matrix[ attr ] && val in matrix[ attr ] ) {
					var p = parseFloat( matrix[ attr ][ val ] );
					if ( ! isNaN( p ) ) {
						return p;
					}
				}
			}
		}
		// Fallback for non-matrix pricing: the input's original data-raw-price
		// (set at page render). Read via .attr() not .data() to avoid jQuery's
		// data-cache returning stale values.
		var fallback = parseFloat( $input.attr( 'data-raw-price' ) );
		return isNaN( fallback ) ? 0 : fallback;
	}

	function refreshSummary( $group, $form ) {
		var attrSelections = getAttributeSelections( $form );
		var count = 0;
		var total = 0;
		$group.find( 'input.addon-checkbox:checked, input.addon-radio:checked' ).each( function () {
			count++;
			total += getEffectivePrice( $( this ), attrSelections );
		} );
		var $summary = $group.find( '.lafka-addon-summary' ).first();
		if ( ! $summary.length ) {
			return;
		}
		if ( count === 0 ) {
			$summary.text( '' ).removeClass( 'is-active' );
		} else {
			$summary
				.text( count + ' selected · +' + formatMoney( total ) )
				.addClass( 'is-active' );
		}
	}

	function initGroup( $group, $form ) {
		var $heading = $group.find( '.addon-name' ).first();
		if ( ! $heading.length ) {
			return;
		}
		if ( $heading.find( '.lafka-addon-summary' ).length === 0 ) {
			$heading.append( '<span class="lafka-addon-summary" aria-live="polite"></span>' );
		}
		$heading
			.attr( 'role', 'button' )
			.attr( 'tabindex', '0' )
			.attr( 'aria-expanded', $group.attr( 'data-collapsed' ) === 'true' ? 'false' : 'true' );

		refreshSummary( $group, $form );
	}

	$( function () {
		// Init at document ready. WC's variation_form may re-render parts of
		// the DOM after the variation JS hydrates; we re-init on its events.
		var $form = $( 'form.cart' );
		if ( ! $form.length ) {
			return;
		}

		function initAll() {
			$form.find( '.product-addon' ).each( function () {
				initGroup( $( this ), $form );
			} );
		}

		function refreshAll() {
			$form.find( '.product-addon' ).each( function () {
				refreshSummary( $( this ), $form );
			} );
		}

		initAll();

		// Toggle group expand/collapse on heading click + keyboard.
		$form.on( 'click keydown', '.product-addon .addon-name', function ( e ) {
			if ( e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ' ) {
				return;
			}
			e.preventDefault();
			var $group = $( this ).closest( '.product-addon' );
			var collapsed = $group.attr( 'data-collapsed' ) === 'true';
			$group.attr( 'data-collapsed', collapsed ? 'false' : 'true' );
			$( this ).attr( 'aria-expanded', collapsed ? 'true' : 'false' );
		} );

		// Selection change → refresh that group's summary.
		$form.on( 'change', '.product-addon input', function () {
			refreshSummary( $( this ).closest( '.product-addon' ), $form );
		} );

		// Plugin events that signal addon re-pricing (e.g. size change).
		// Recompute totals for ALL groups since per-size matrix prices apply
		// across groups uniformly when the customer changes pizza size.
		$form.on( 'lafka-product-addons-update updated_addons found_variation', function () {
			setTimeout( refreshAll, 0 );
		} );

		// Also catch the size-picker radio changes directly — the plugin
		// fires lafka-product-addons-update only after a debounced 300ms
		// delay, so we want a more responsive update right when the
		// customer clicks a size chip.
		$( document ).on( 'change', '.lafka-pdp-pickers input[type=radio]', refreshAll );

		// Re-init when WC swaps the variation form (e.g. quick-view, AJAX).
		$( document.body ).on( 'wc_variation_form', initAll );
	} );

} )( window.jQuery );
