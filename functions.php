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
// add_action( 'wp_head', 'lafka_child_color_overrides', 100 );
// function lafka_child_color_overrides() {
//     ?>
//     <style>
//         :root {
//             --lafka-accent-color: #e85d2a;
//             --lafka-button-color: #e85d2a;
//             --lafka-button-hover-color: #c44a1e;
//         }
//     </style>
//     <?php
// }

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
