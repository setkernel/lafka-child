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

	function refreshSummary( $group ) {
		var count = 0;
		var total = 0;
		$group.find( 'input.addon-checkbox:checked, input.addon-radio:checked' ).each( function () {
			count++;
			var p = parseFloat( $( this ).data( 'raw-price' ) );
			if ( ! isNaN( p ) ) {
				total += p;
			}
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

	function initGroup( $group ) {
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

		refreshSummary( $group );
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
				initGroup( $( this ) );
			} );
		}

		initAll();

		// Toggle group expand/collapse on heading click + keyboard.
		$form.on( 'click keydown', '.product-addon .addon-name', function ( e ) {
			// Don't toggle when clicking the summary span itself (no event
			// would fire from there since it's inside the heading, but guard
			// anyway for clarity).
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
			refreshSummary( $( this ).closest( '.product-addon' ) );
		} );

		// Plugin events that signal addon re-pricing (e.g. size change).
		$form.on( 'lafka-product-addons-update updated_addons found_variation', function () {
			// Defer one tick so addons.js has finished updating data-raw-price
			// on each checkbox before we read it.
			setTimeout( initAll, 0 );
		} );

		// Re-init when WC swaps the variation form (e.g. quick-view, AJAX).
		$( document.body ).on( 'wc_variation_form', initAll );
	} );

} )( window.jQuery );
