<?php
defined( 'ABSPATH' ) || exit;

// Pure pricing/promotion helpers (BOGO math, delivery-min predicate).
// Extracted so they can be unit-tested without booting WP/WC, and so the
// P2-01 child→plugin migration can lift them with a namespace change.
require_once __DIR__ . '/inc/lafka-promotions.php';

// P6-UX-1 + P6-UX-4 W3-T8: Customizer panels for Editorial templates.
require_once __DIR__ . '/inc/customizer-editorial.php';

/**
 * Whether the lafka-plugin's promotions module owns BOGO + delivery-min today.
 *
 * When true, every hook in this file's "Delivery Minimum" + "BOGO 50%" + "BOGO
 * Promotional Banner" blocks must self-gate to no-op so we don't double-apply
 * (banner shown twice, BOGO discount applied twice, etc.). The plugin module
 * (lafka-plugin/incl/promotions/) loads when `is_lafka_promotions()` returns
 * true; default is false (legacy child implementation stays active).
 */
function lafka_child_promotions_owned_by_plugin() {
	return function_exists( 'is_lafka_promotions' ) && is_lafka_promotions();
}

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
			array( 'lafka-child-style', 'lafka-rtl' ),
			wp_get_theme()->get( 'Version' )
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
// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- examples are intentional documentation

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
// phpcs:enable Squiz.PHP.CommentedOutCode.Found

/**
 * ─── 9. Delivery Minimum ──────────────────────────────────────────────────────
 * Hide all shipping methods except local pickup when cart is below the minimum.
 * Customers can still pick up in store regardless of order size.
 *
 * Threshold + predicate live in inc/lafka-promotions.php so the math is
 * unit-testable. See LafkaPromotionsTest.
 */
add_filter( 'woocommerce_package_rates', 'lafka_child_minimum_order_for_delivery', 10, 2 );
function lafka_child_minimum_order_for_delivery( $rates, $package ) {
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return $rates;
	}
	if ( lafka_child_should_block_delivery( $package['contents_cost'] ) ) {
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
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return;
	}
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
				/* translators: 1: minimum order amount in store currency, 2: amount remaining to qualify for delivery */
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
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return;
	}
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

	// 2. Expand into individual units. Sort + distribution happen inside the helper.
	$units = array();
	foreach ( $cart->get_cart() as $key => $cart_item ) {
		$price = (float) $cart->cart_contents[ $key ]['_bogo_original_price'];
		for ( $i = 0; $i < $cart_item['quantity']; $i++ ) {
			$units[] = array(
				'key'   => $key,
				'price' => $price,
			);
		}
	}

	// 3. Distribute the discount across line-item keys (pure helper).
	$discounts_per_key = lafka_bogo_distribute_discounts( $units );

	// 4. Apply blended price per line item.
	foreach ( $discounts_per_key as $key => $disc_qty ) {
		$item = $cart->cart_contents[ $key ];
		$qty  = (int) $item['quantity'];
		$orig = (float) $item['_bogo_original_price'];

		$savings = $orig * 0.5 * $disc_qty;
		$blended = lafka_bogo_blended_price( $orig, $qty, $disc_qty );

		$item['data']->set_price( $blended );

		$cart->cart_contents[ $key ]['_bogo_50']             = true;
		$cart->cart_contents[ $key ]['_bogo_discounted_qty'] = $disc_qty;
		$cart->cart_contents[ $key ]['_bogo_savings']        = $savings;
	}
}

/**
 * Show promotion label below item in cart.
 */
add_filter( 'woocommerce_get_item_data', 'bogo_50_cart_label', 10, 2 );
function bogo_50_cart_label( $item_data, $cart_item ) {
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return $item_data;
	}
	if ( ! empty( $cart_item['_bogo_50'] ) ) {
		$disc_qty    = (int) $cart_item['_bogo_discounted_qty'];
		$item_data[] = array(
			'name'  => esc_html__( '🎉 Promotion', 'lafka' ),
			'value' => sprintf(
				/* translators: %d: number of units in this line item that received the BOGO 50%% discount */
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
// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- WC filter signature
function bogo_50_display_price( $price_html, $cart_item, $cart_item_key ) {
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return $price_html;
	}
	if ( ! empty( $cart_item['_bogo_50'] ) && isset( $cart_item['_bogo_original_price'] ) ) {
		$price_html = wc_price( (float) $cart_item['_bogo_original_price'] );
	}
	return $price_html;
}

/**
 * Show strikethrough original subtotal + savings in the subtotal column.
 */
add_filter( 'woocommerce_cart_item_subtotal', 'bogo_50_display_subtotal', 10, 3 );
// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- WC filter signature
function bogo_50_display_subtotal( $subtotal_html, $cart_item, $cart_item_key ) {
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return $subtotal_html;
	}
	if ( ! empty( $cart_item['_bogo_50'] ) && isset( $cart_item['_bogo_savings'] ) ) {
		$orig_subtotal = (float) $cart_item['_bogo_original_price'] * (int) $cart_item['quantity'];
		$savings       = (float) $cart_item['_bogo_savings'];
		$new_subtotal  = $orig_subtotal - $savings;

		$subtotal_html  = '<del>' . wc_price( $orig_subtotal ) . '</del> ';
		$subtotal_html .= wc_price( $new_subtotal );
		$subtotal_html .= '<br><small style="color:#4ecca3;font-weight:600;">';
		/* translators: %s: amount saved in store currency, formatted by wc_price() */
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
	if ( lafka_child_promotions_owned_by_plugin() ) {
		return;
	}
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

/**
 * P6-PERF-1: Per-page LCP image preload + fetchpriority on the matching image.
 *
 * Adjust the hero URL when the homepage hero changes (e.g. after the Week 3
 * homepage redesign). The attachment-id-keyed filter is a no-op until the
 * `lafka_homepage_hero_attachment_id` WP option is set by the operator.
 */
add_filter( 'lafka_lcp_image_url', function ( $url ) {
	if ( is_front_page() ) {
		// TODO P6-PERF-1: confirm with operator that this is the correct hero URL
		// after the Week 3 homepage redesign. Until then, this preloads the
		// existing top promo image.
		return content_url( '/uploads/2026/01/Untitled-design-11.png' );
	}
	return $url;
} );

add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( ! is_front_page() ) {
		return $attr;
	}
	$hero_attachment_id = (int) get_option( 'lafka_homepage_hero_attachment_id', 0 );
	if ( $hero_attachment_id && $hero_attachment_id === (int) ( is_object( $attachment ) ? $attachment->ID : 0 ) ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = 'eager';
	}
	return $attr;
}, 10, 2 );

/**
 * P6-PERF-2: ensure every <img> in front-end output has explicit
 * width + height attributes. Without these the browser can't reserve
 * vertical space, causing CLS as images arrive.
 *
 * WP core tries to do this automatically via wp_filter_content_tags() but
 * skips images that aren't WP-managed (e.g. WPBakery emissions, custom
 * templates with hardcoded URLs). This filter catches the stragglers.
 *
 * Approach: parse the_content + post_thumbnail_html + widget output, find
 * <img> tags missing width/height, attempt to look up dimensions from the
 * attachment ID (if present in class name) or read the actual file via
 * getimagesize() with a transient cache.
 */
add_filter( 'the_content', 'lafka_inject_image_dimensions', 999 );
add_filter( 'post_thumbnail_html', 'lafka_inject_image_dimensions', 999 );
add_filter( 'widget_text', 'lafka_inject_image_dimensions', 999 );

if ( ! function_exists( 'lafka_inject_image_dimensions' ) ) {
	function lafka_inject_image_dimensions( $content ) {
		if ( empty( $content ) || strpos( $content, '<img' ) === false ) {
			return $content;
		}
		return preg_replace_callback(
			'/<img\b([^>]*)>/i',
			function ( $m ) {
				$attrs = $m[1];
				// Already has width and height? Leave alone.
				if ( preg_match( '/\bwidth=/i', $attrs ) && preg_match( '/\bheight=/i', $attrs ) ) {
					return $m[0];
				}
				// Pull src.
				if ( ! preg_match( '/\bsrc=["\']([^"\']+)["\']/', $attrs, $src ) ) {
					return $m[0];
				}
				$url = $src[1];

				// Try WP attachment lookup first (no I/O if we can avoid it).
				$attachment_id = 0;
				if ( preg_match( '/\bclass=["\'][^"\']*\bwp-image-(\d+)\b/', $attrs, $cls ) ) {
					$attachment_id = (int) $cls[1];
				}
				if ( ! $attachment_id ) {
					$attachment_id = function_exists( 'attachment_url_to_postid' )
						? (int) attachment_url_to_postid( $url )
						: 0;
				}

				$w = 0;
				$h = 0;

				if ( $attachment_id ) {
					$meta = wp_get_attachment_metadata( $attachment_id );
					if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
						$w = (int) $meta['width'];
						$h = (int) $meta['height'];
					}
				}

				// Last-resort: getimagesize via cached transient. Keyed off URL.
				if ( ! $w || ! $h ) {
					$cache_key = 'lafka_imgdims_' . md5( $url );
					$cached    = get_transient( $cache_key );
					if ( is_array( $cached ) && ! empty( $cached[0] ) && ! empty( $cached[1] ) ) {
						list( $w, $h ) = $cached;
					} else {
						// Only inspect local URLs — never fetch remote.
						$local_path = lafka_url_to_local_path( $url );
						if ( $local_path && file_exists( $local_path ) ) {
							$size = @getimagesize( $local_path );
							if ( is_array( $size ) && $size[0] && $size[1] ) {
								$w = (int) $size[0];
								$h = (int) $size[1];
								set_transient( $cache_key, array( $w, $h ), DAY_IN_SECONDS );
							}
						}
					}
				}

				if ( ! $w || ! $h ) {
					return $m[0]; // give up
				}

				$injected = sprintf( ' width="%d" height="%d"', $w, $h );
				return '<img' . $attrs . $injected . '>';
			},
			$content
		);
	}
}

if ( ! function_exists( 'lafka_url_to_local_path' ) ) {
	function lafka_url_to_local_path( $url ) {
		$upload_dir = wp_get_upload_dir();
		if ( strpos( $url, $upload_dir['baseurl'] ) === 0 ) {
			return $upload_dir['basedir'] . substr( $url, strlen( $upload_dir['baseurl'] ) );
		}
		$content_url = content_url();
		if ( strpos( $url, $content_url ) === 0 ) {
			return WP_CONTENT_DIR . substr( $url, strlen( $content_url ) );
		}
		return null;
	}
}

/**
 * P6-SEO-7: WooCommerce shop archive AND product taxonomy archives (categories,
 * tags) were emitting two <h1>s — one from `lafka-theme/woocommerce/global/
 * wrapper-start.php:114` (themed), plus WC core's own from
 * `woocommerce_shop_loop_header` action. Both templates gate on the same
 * `woocommerce_show_page_title` filter, so filtering that value suppresses
 * both. Instead, remove the WC core action so only lafka's styled <h1>
 * remains as the single canonical heading.
 *
 * Gated to `is_shop()` and product taxonomy archives only — product detail
 * pages and other archives are unaffected.
 */
add_action( 'wp', function () {
	if ( ! function_exists( 'is_shop' ) ) {
		return;
	}
	if ( is_shop() || is_product_taxonomy() ) {
		remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' );
	}
} );

/**
 * P6-UX-1 / P6-UX-4 W3-T8: enqueue editorial design system (Fraunces font +
 * editorial.css) ONLY on pages using the editorial templates. Zero perf impact
 * on the rest of the site.
 *
 * @font-face declarations for Fraunces live inside editorial.css, so they
 * automatically inherit this conditional enqueue — no separate wp_enqueue_style
 * call needed for the font files.
 */
add_action( 'wp_enqueue_scripts', 'lafka_editorial_assets_enqueue', 30 );
function lafka_editorial_assets_enqueue() {
	if ( ! is_page() ) {
		return;
	}
	$editorial_templates = array(
		'page-templates/template-editorial-home.php',
		'page-templates/template-editorial-contact.php',
	);
	if ( ! is_page_template( $editorial_templates ) ) {
		return;
	}
	wp_enqueue_style(
		'lafka-editorial',
		get_stylesheet_directory_uri() . '/styles/editorial.css',
		array( 'lafka-child-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
