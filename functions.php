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

