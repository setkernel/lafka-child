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
