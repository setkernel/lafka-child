<?php
defined( 'ABSPATH' ) || exit;

// P6-UX-1 + P6-UX-4 W3-T8: Customizer panels for Editorial templates.
require_once __DIR__ . '/inc/customizer-editorial.php';

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
 * For common customization recipes (sidebars, related-products heading,
 * pagination wrappers, widget areas, etc.), see examples/customizations.php.example.
 * Copy the block you want into this functions.php to activate it.
 */

/**
 * P6-PERF-1: Per-page LCP image preload + fetchpriority on the matching image.
 *
 * Hero image URL comes from the Customizer setting `lafka_homepage_hero_image`
 * (registered in lafka-plugin/incl/customizer/class-lafka-customizer-restaurant-info.php).
 * Accepts either a full URL or a numeric attachment ID. When unset, no preload
 * is emitted — keeps the OSS plugin free of any restaurant-specific media URL.
 */
add_filter( 'lafka_lcp_image_url', function ( $url ) {
	if ( ! is_front_page() ) {
		return $url;
	}
	$hero = get_theme_mod( 'lafka_homepage_hero_image', '' );
	if ( '' === $hero || null === $hero ) {
		return $url;
	}
	// Attachment ID stored as int/numeric string.
	if ( is_numeric( $hero ) ) {
		$resolved = wp_get_attachment_image_url( (int) $hero, 'full' );
		return $resolved ? $resolved : $url;
	}
	// Full URL (image control's default storage mode).
	return esc_url_raw( (string) $hero );
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

/**
 * P6-PDP (W4-T21, 2026-04-29): Enqueue PDP redesign CSS+JS conditionally.
 *
 * CSS + always-needed JS load on every page (the order-method bar + cart
 * drawer can fire from the header on any page). PDP-only JS (pickers,
 * upsell modal) loads only when is_product().
 */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! function_exists( 'lafka_pdp_redesign_enabled' ) || ! lafka_pdp_redesign_enabled() ) {
        return;
    }
    if ( ! function_exists( 'is_product' ) ) {
        return;
    }

    $stylesheet_uri = get_stylesheet_directory_uri();
    $base_dir       = get_stylesheet_directory();
    $version_for    = static function ( string $rel ) use ( $base_dir ): string {
        $f = $base_dir . '/' . ltrim( $rel, '/' );
        return file_exists( $f ) ? (string) filemtime( $f ) : (string) time();
    };

    wp_enqueue_style(
        'lafka-pdp-redesign',
        $stylesheet_uri . '/styles/pdp-redesign.css',
        array(),
        $version_for( 'styles/pdp-redesign.css' )
    );

    wp_enqueue_script(
        'lafka-order-method',
        $stylesheet_uri . '/js/order-method.js',
        array(),
        $version_for( 'js/order-method.js' ),
        array( 'in_footer' => true, 'strategy' => 'defer' )
    );

    // Localize operator-specific labels to the order-method script.
    // Must come from the resolver (theme_mod → option → empty); never
    // hardcode literals into the JS — lafka-child is public OSS.
    if ( function_exists( 'lafka_get_restaurant_info' ) ) {
        $info = lafka_get_restaurant_info();
        wp_localize_script(
            'lafka-order-method',
            'lafkaOrderMethodLabels',
            array(
                'pickupLabel'   => trim( (string) ( $info['address_short'] ?? '' ) ),
                'deliveryLabel' => trim( (string) ( ( $info['city'] ?? '' ) ) ),
            )
        );
    }

    wp_enqueue_script(
        'lafka-cart-drawer',
        $stylesheet_uri . '/js/cart-drawer.js',
        array( 'jquery' ),
        $version_for( 'js/cart-drawer.js' ),
        array( 'in_footer' => true, 'strategy' => 'defer' )
    );

    if ( is_product() ) {
        wp_enqueue_script(
            'lafka-pdp-pickers',
            $stylesheet_uri . '/js/pdp-pickers.js',
            array(),
            $version_for( 'js/pdp-pickers.js' ),
            array( 'in_footer' => true, 'strategy' => 'defer' )
        );

        // Localize WC currency formatter so the live-price + CTA-label JS
        // updates honour woocommerce_currency_pos and the symbol — without
        // this, JS hardcoded `$` leaked operator currency and broke any
        // non-USD shop.
        wp_localize_script(
            'lafka-pdp-pickers',
            'lafkaPdpCurrency',
            array(
                'symbol'      => function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol() ) : '$',
                'position'    => get_option( 'woocommerce_currency_pos', 'left' ),
                'thousandSep' => function_exists( 'wc_get_price_thousand_separator' ) ? wc_get_price_thousand_separator() : ',',
                'decimalSep'  => function_exists( 'wc_get_price_decimal_separator' ) ? wc_get_price_decimal_separator() : '.',
                'decimals'    => function_exists( 'wc_get_price_decimals' ) ? (int) wc_get_price_decimals() : 2,
            )
        );

        wp_enqueue_script(
            'lafka-upsell-modal',
            $stylesheet_uri . '/js/upsell-modal.js',
            array( 'jquery' ),
            $version_for( 'js/upsell-modal.js' ),
            array( 'in_footer' => true, 'strategy' => 'defer' )
        );

        // PDP addon UX layer: collapsible groups + selected-count summary
        // in each addon group's heading. Sits on top of the lafka-plugin
        // theme-agnostic addon markup; the plugin itself stays untouched.
        wp_enqueue_script(
            'lafka-pdp-addons',
            $stylesheet_uri . '/js/pdp-addons.js',
            array( 'jquery' ),
            $version_for( 'js/pdp-addons.js' ),
            array( 'in_footer' => true, 'strategy' => 'defer' )
        );
    }
}, 11 );

/**
 * P6-PDP (W4-T21, 2026-04-29): Hook the always-sticky order-method bar.
 */
add_action( 'wp_body_open', function () {
    if ( ! function_exists( 'lafka_pdp_redesign_enabled' ) || ! lafka_pdp_redesign_enabled() ) {
        return;
    }
    $partial = get_stylesheet_directory() . '/partials/order-method-bar.php';
    if ( file_exists( $partial ) ) {
        include $partial;
    }
}, 5 );

/**
 * P6-PDP (W4-T21, 2026-04-29): Cart drawer to wp_footer.
 */
add_action( 'wp_footer', function () {
    if ( ! function_exists( 'lafka_pdp_redesign_enabled' ) || ! lafka_pdp_redesign_enabled() ) {
        return;
    }
    $partial = get_stylesheet_directory() . '/partials/cart-drawer.php';
    if ( file_exists( $partial ) ) {
        include $partial;
    }
}, 5 );

/**
 * P6-PDP (W4-T21, 2026-04-29): body_class signal for redesign-disabled state.
 */
add_filter( 'body_class', function ( $classes ) {
    if ( function_exists( 'lafka_pdp_redesign_enabled' ) && ! lafka_pdp_redesign_enabled() ) {
        $classes[] = 'lafka-pdp-disabled';
    }
    return $classes;
} );
