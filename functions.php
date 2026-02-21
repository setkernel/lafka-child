<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'lafka_child_enqueue_styles', 20 );
function lafka_child_enqueue_styles() {
	// Parent already enqueues 'lafka-style' and 'lafka-responsive' — just add child after them.
	$child_deps = array( 'lafka-style' );
	if ( wp_style_is( 'lafka-responsive', 'enqueued' ) || wp_style_is( 'lafka-responsive', 'registered' ) ) {
		$child_deps[] = 'lafka-responsive';
	}

	wp_enqueue_style(
		'lafka-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		$child_deps,
		wp_get_theme()->get( 'Version' )
	);

	if ( is_rtl() && file_exists( get_stylesheet_directory() . '/styles/rtl.css' ) ) {
		wp_enqueue_style(
			'lafka-child-rtl',
			get_stylesheet_directory_uri() . '/styles/rtl.css',
			array( 'lafka-child-style', 'lafka-rtl' )
		);
	}

	if ( file_exists( get_stylesheet_directory() . '/js/lafka-front.js' ) ) {
		wp_enqueue_script(
			'lafka-child-front',
			get_stylesheet_directory_uri() . '/js/lafka-front.js',
			array( 'lafka-front' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}

/*
 * ============================================================================
 * CUSTOMIZATION EXAMPLES
 * ============================================================================
 *
 * Below are common customizations for a Lafka child theme. Each example is
 * commented out and ready to use — just uncomment the block you need.
 *
 * Tip: the parent theme's options panel (Appearance > Theme Options) handles
 * most settings. Use these hooks when you need logic the options panel can't
 * provide, or when you want version-controlled overrides.
 * ============================================================================
 */

/*
 * 1. Force sidebar on/off for specific page types
 *    Filter: lafka_has_sidebar (used in page.php, single.php, archive.php, etc.)
 *    Return a sidebar slug or empty string to hide.
 */
// add_filter( 'lafka_has_sidebar', 'lafka_child_sidebar_override' );
// function lafka_child_sidebar_override( $sidebar ) {
//     if ( is_product() ) {
//         return ''; // hide sidebar on single products
//     }
//     return $sidebar;
// }

/*
 * 2. Add content before the add-to-cart button (e.g. allergen info, prep time)
 *    Filter: lafka_links_before_add_to_cart (in loop/add-to-cart.php)
 */
// add_filter( 'lafka_links_before_add_to_cart', 'lafka_child_before_cart_link' );
// function lafka_child_before_cart_link( $html ) {
//     global $product;
//     $prep = get_post_meta( $product->get_id(), '_prep_time', true );
//     if ( $prep ) {
//         $html .= '<span class="lafka-prep-time">' . esc_html( $prep ) . ' min</span>';
//     }
//     return $html;
// }

/*
 * 3. Change the number of products per page
 *    Filter: loop_shop_per_page (WooCommerce core)
 */
// add_filter( 'loop_shop_per_page', 'lafka_child_products_per_page' );
// function lafka_child_products_per_page( $cols ) {
//     return 24;
// }

/*
 * 4. Customize the related products section heading
 *    Filter: woocommerce_product_related_products_heading (WooCommerce core)
 */
// add_filter( 'woocommerce_product_related_products_heading', 'lafka_child_related_heading' );
// function lafka_child_related_heading( $heading ) {
//     return __( 'You might also like', 'lafka' );
// }

/*
 * 5. Override theme colors with CSS custom properties
 *    Action: wp_head — outputs a <style> block that overrides :root variables.
 *    See dynamic-css.php for the full list of --lafka-* variables.
 */
/*
add_action( 'wp_head', 'lafka_child_color_overrides', 100 );
function lafka_child_color_overrides() {
    ?>
    <style>
        :root {
            --lafka-accent-color: #e85d2a;
            --lafka-button-color: #e85d2a;
            --lafka-button-hover-color: #c44a1e;
        }
    </style>
    <?php
}
*/

/*
 * 6. Modify the pagination HTML output
 *    Filter: lafka_pagination (in functions.php lafka_pagination())
 */
// add_filter( 'lafka_pagination', 'lafka_child_pagination' );
// function lafka_child_pagination( $html ) {
//     // Example: wrap pagination in an extra container
//     return '<nav class="lafka-child-pagination" aria-label="Products">' . $html . '</nav>';
// }

/*
 * 7. Register an additional widget area (e.g. for a promotional banner)
 *    Action: widgets_init (WordPress core)
 */
// add_action( 'widgets_init', 'lafka_child_widgets' );
// function lafka_child_widgets() {
//     register_sidebar( array(
//         'name'          => __( 'Promo Banner', 'lafka' ),
//         'id'            => 'lafka-child-promo',
//         'before_widget' => '<div id="%1$s" class="widget lafka-promo-widget %2$s">',
//         'after_widget'  => '</div>',
//         'before_title'  => '<h3 class="widget-title">',
//         'after_title'   => '</h3>',
//     ) );
// }

/*
 * 8. Add a store-wide notice above the shop (e.g. holiday hours, delivery delay)
 *    Action: woocommerce_before_shop_loop (WooCommerce core)
 */
// add_action( 'woocommerce_before_shop_loop', 'lafka_child_shop_notice', 5 );
// function lafka_child_shop_notice() {
//     echo '<div class="lafka-child-shop-notice">';
//     echo esc_html__( 'Free delivery on orders over $30!', 'lafka' );
//     echo '</div>';
// }

/**
 * ─── 9. Delivery Minimum ──────────────────────────────────────────────────────
 * Hide all shipping methods except local pickup when cart is below the minimum.
 * Customers can still pick up in store regardless of order size.
 */
define( 'LAFKA_CHILD_DELIVERY_MINIMUM', 30 );

add_filter( 'woocommerce_package_rates', 'lafka_child_minimum_order_for_delivery', 10, 2 );
function lafka_child_minimum_order_for_delivery( $rates, $package ) {
	if ( $package['contents_cost'] < LAFKA_CHILD_DELIVERY_MINIMUM ) {
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'local_pickup' !== $rate->method_id ) {
				unset( $rates[ $rate_id ] );
			}
		}
	}
	return $rates;
}

add_action( 'woocommerce_before_cart', 'lafka_child_delivery_minimum_notice' );
add_action( 'woocommerce_before_checkout_form', 'lafka_child_delivery_minimum_notice' );
function lafka_child_delivery_minimum_notice() {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	$subtotal = 0;
	foreach ( WC()->cart->get_cart() as $item ) {
		$subtotal += $item['line_subtotal'];
	}

	if ( $subtotal < LAFKA_CHILD_DELIVERY_MINIMUM ) {
		$remaining = LAFKA_CHILD_DELIVERY_MINIMUM - $subtotal;
		printf(
			'<div class="woocommerce-info" style="background-color:#e94560;color:#fff;border-top-color:#c4374d;padding:12px 20px;font-size:15px;font-weight:600;">%s</div>',
			sprintf(
				esc_html__( 'Delivery is available on orders over %1$s. Add %2$s more to your cart for delivery.', 'lafka' ),
				wp_kses_post( wc_price( LAFKA_CHILD_DELIVERY_MINIMUM ) ),
				wp_kses_post( wc_price( $remaining ) )
			)
		);
	}
}

/**
 * ─── 10. BOGO 50% Off — Per Pair ─────────────────────────────────────────────
 * For every 2 items, the cheaper one gets 50% off.
 * 4 items = 2 discounted, 6 = 3 discounted, etc.
 * Cheapest units are always the ones discounted. Odd item pays full.
 * PHP 8.2+ safe — no dynamic properties on WC_Product.
 */
add_action( 'woocommerce_before_calculate_totals', 'bogo_50_cheapest_item', 20, 1 );
function bogo_50_cheapest_item( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( did_action( 'woocommerce_before_calculate_totals' ) > 1 ) {
		return;
	}

	// 1. Reset everything and store original prices.
	$total_quantity = 0;
	foreach ( $cart->get_cart() as $key => $cart_item ) {
		if ( isset( $cart->cart_contents[ $key ]['_bogo_original_price'] ) ) {
			$cart_item['data']->set_price( $cart->cart_contents[ $key ]['_bogo_original_price'] );
		} else {
			$cart->cart_contents[ $key ]['_bogo_original_price'] = (float) $cart_item['data']->get_price();
		}
		unset( $cart->cart_contents[ $key ]['_bogo_50'] );
		unset( $cart->cart_contents[ $key ]['_bogo_discounted_qty'] );
		unset( $cart->cart_contents[ $key ]['_bogo_savings'] );
		$total_quantity += $cart_item['quantity'];
	}

	if ( $total_quantity < 2 ) {
		return;
	}

	// 2. Expand into individual units, sorted by price ascending (cheapest first).
	$units = array();
	foreach ( $cart->get_cart() as $key => $cart_item ) {
		$price = (float) $cart->cart_contents[ $key ]['_bogo_original_price'];
		for ( $i = 0; $i < $cart_item['quantity']; $i++ ) {
			$units[] = array( 'key' => $key, 'price' => $price );
		}
	}
	usort( $units, function( $a, $b ) {
		return $a['price'] <=> $b['price'];
	} );

	// 3. Cheapest floor(total/2) units get 50% off.
	$discount_count    = floor( $total_quantity / 2 );
	$discounts_per_key = array();

	for ( $i = 0; $i < $discount_count; $i++ ) {
		$k = $units[ $i ]['key'];
		if ( ! isset( $discounts_per_key[ $k ] ) ) {
			$discounts_per_key[ $k ] = 0;
		}
		$discounts_per_key[ $k ]++;
	}

	// 4. Apply blended price per line item.
	foreach ( $discounts_per_key as $key => $disc_qty ) {
		$item = $cart->cart_contents[ $key ];
		$qty  = (int) $item['quantity'];
		$orig = (float) $item['_bogo_original_price'];

		$full_units = $qty - $disc_qty;
		$savings    = $orig * 0.5 * $disc_qty;
		$blended    = ( $full_units * $orig + $disc_qty * $orig * 0.5 ) / $qty;

		$item['data']->set_price( $blended );

		$cart->cart_contents[ $key ]['_bogo_50']            = true;
		$cart->cart_contents[ $key ]['_bogo_discounted_qty'] = $disc_qty;
		$cart->cart_contents[ $key ]['_bogo_savings']        = $savings;
	}
}

/**
 * Show promotion label below item in cart.
 */
add_filter( 'woocommerce_get_item_data', 'bogo_50_cart_label', 10, 2 );
function bogo_50_cart_label( $item_data, $cart_item ) {
	if ( ! empty( $cart_item['_bogo_50'] ) ) {
		$disc_qty = (int) $cart_item['_bogo_discounted_qty'];
		$item_data[] = array(
			'name'  => esc_html__( '🎉 Promotion', 'lafka' ),
			'value' => sprintf(
				esc_html__( 'BOGO 50%% Off applied to %d unit(s)', 'lafka' ),
				$disc_qty
			),
		);
	}
	return $item_data;
}

/**
 * Show original unit price in the price column (not blended).
 */
add_filter( 'woocommerce_cart_item_price', 'bogo_50_display_price', 10, 3 );
function bogo_50_display_price( $price_html, $cart_item, $cart_item_key ) {
	if ( ! empty( $cart_item['_bogo_50'] ) && isset( $cart_item['_bogo_original_price'] ) ) {
		$price_html = wc_price( (float) $cart_item['_bogo_original_price'] );
	}
	return $price_html;
}

/**
 * Show strikethrough original subtotal + savings in the subtotal column.
 */
add_filter( 'woocommerce_cart_item_subtotal', 'bogo_50_display_subtotal', 10, 3 );
function bogo_50_display_subtotal( $subtotal_html, $cart_item, $cart_item_key ) {
	if ( ! empty( $cart_item['_bogo_50'] ) && isset( $cart_item['_bogo_savings'] ) ) {
		$orig_subtotal = (float) $cart_item['_bogo_original_price'] * (int) $cart_item['quantity'];
		$savings       = (float) $cart_item['_bogo_savings'];
		$new_subtotal  = $orig_subtotal - $savings;

		$subtotal_html  = '<del>' . wc_price( $orig_subtotal ) . '</del> ';
		$subtotal_html .= wc_price( $new_subtotal );
		$subtotal_html .= '<br><small style="color:#4ecca3;font-weight:600;">';
		$subtotal_html .= sprintf( esc_html__( 'You save %s', 'lafka' ), wc_price( $savings ) );
		$subtotal_html .= '</small>';
	}
	return $subtotal_html;
}

/**
 * ─── 11. BOGO Promotional Banner ─────────────────────────────────────────────
 * Dismissible top banner. Per-promo key with 7-day expiry.
 */
add_action( 'wp_footer', 'bogo_50_dismissible_banner' );
function bogo_50_dismissible_banner() {
	$promo_key = 'bogo50_feb2026';
	?>
	<style>
		#bogoBanner {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			background: #c62828;
			color: #fff;
			z-index: 10000;
			transform: translateY(-100%);
			opacity: 0;
			transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1),
			            opacity 0.3s ease;
			box-shadow: 0 4px 14px rgba(0,0,0,0.25);
		}
		#bogoBanner.visible {
			transform: translateY(0);
			opacity: 1;
		}
		.bogo-inner {
			max-width: 1200px;
			margin: 0 auto;
			padding: 14px 48px;
			text-align: center;
			font-size: 15px;
			font-weight: 600;
			letter-spacing: 0.3px;
		}
		.bogo-close {
			position: absolute;
			right: 18px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
			font-size: 20px;
			line-height: 1;
			opacity: 0.9;
			background: none;
			border: none;
			color: inherit;
			padding: 8px;
		}
		.bogo-close:hover,
		.bogo-close:focus {
			opacity: 1;
			outline: 2px solid rgba(255,255,255,0.6);
			outline-offset: 2px;
			border-radius: 4px;
		}
		@media (max-width: 768px) {
			.bogo-inner {
				padding: 12px 48px 12px 16px;
				font-size: 14px;
				text-align: left;
			}
			.bogo-close {
				right: 10px;
				font-size: 24px;
			}
		}
	</style>

	<div id="bogoBanner" role="banner">
		<div class="bogo-inner">
			🔥 <?php esc_html_e( 'Buy 1, Get 1 50% Off', 'lafka' ); ?>
		</div>
		<button class="bogo-close" aria-label="<?php esc_attr_e( 'Close banner', 'lafka' ); ?>">&times;</button>
	</div>

	<script>
	(function() {
		var PROMO_KEY   = <?php echo wp_json_encode( $promo_key ); ?>;
		var DISMISS_KEY = 'bogo_dismissed_' + PROMO_KEY;
		var EXPIRY_DAYS = 7;

		function isDismissed() {
			try {
				var ts = localStorage.getItem( DISMISS_KEY );
				if ( ! ts ) return false;
				return ( Date.now() - parseInt( ts, 10 ) ) < EXPIRY_DAYS * 86400000;
			} catch(e) { return false; }
		}

		function dismiss() {
			try { localStorage.setItem( DISMISS_KEY, Date.now().toString() ); } catch(e) {}
			banner.classList.remove('visible');
		}

		var banner   = document.getElementById('bogoBanner');
		var closeBtn = banner.querySelector('.bogo-close');

		if ( ! isDismissed() ) {
			requestAnimationFrame(function() { banner.classList.add('visible'); });
		}

		closeBtn.addEventListener('click', dismiss);
	})();
	</script>
	<?php
}
