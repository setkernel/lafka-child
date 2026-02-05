<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'lafka_child_enqueue_styles' );
function lafka_child_enqueue_styles() {
	$child_style_dependency = array('lafka-style');
	if (lafka_get_option('is_responsive')) {
		$child_style_dependency[] = 'lafka-responsive';
	}
	wp_enqueue_style( 'lafka-style',
		get_template_directory_uri() . '/style.css'
	);
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		$child_style_dependency
	);

	if ( is_rtl() ) {
		wp_enqueue_style( 'lafka-rtl', get_template_directory_uri() . '/styles/rtl.css' );
		wp_enqueue_style( 'child-rtl',
			get_stylesheet_directory_uri() . '/styles/rtl.css',
			array( 'lafka-rtl' )
		);
	}

	wp_enqueue_script( 'child-lafka-front',
		get_stylesheet_directory_uri() . '/js/lafka-front.js',
		array( 'lafka-front' ),
		false,
		true
	);
}