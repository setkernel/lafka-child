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

	// No child RTL stylesheet is enqueued: the parent theme's 'lafka-rtl'
	// already provides RTL base styling. A child override should only be
	// added back here once styles/rtl.css actually contains CSS rules —
	// shipping an empty stub would cost an extra request for no effect.

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
 * Hero background image override (this install only).
 *
 * v5.51.0: removed the yellow-lafka-hero-back-1.jpg default. The
 * texture had lighter spots that dropped headline contrast below
 * WCAG-AA per UX review. Hero now uses the parent theme's flat
 * brand-50 surface for legibility.
 *
 * To use a brand-specific hero image on this install, either:
 *  1. Customizer > Lafka — Home Page > Hero > Hero background image (media picker)
 *  2. Add a callback to the lafka_home_hero_default_bg_url filter
 *     returning your image URL (e.g. home_url('/wp-content/uploads/...')).
 */
