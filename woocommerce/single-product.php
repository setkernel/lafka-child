<?php
/**
 * Single product template — overrides woocommerce/templates/single-product.php (WC 10.7).
 *
 * When the redesign feature flag is OFF, falls through to the parent theme's
 * legacy template via locate_template().
 *
 * @package LafkaChild\WooCommerce
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lafka_pdp_redesign_enabled' ) || ! lafka_pdp_redesign_enabled() ) {
    locate_template( 'woocommerce/single-product.php', false, false );
    return;
}

get_header( 'shop' );
?>
<div class="lafka-pdp">
    <main class="lafka-pdp__main">
        <?php while ( have_posts() ) : the_post(); ?>

            <nav class="lafka-pdp__breadcrumb"><?php woocommerce_breadcrumb(); ?></nav>

            <div class="lafka-pdp__hero">
                <div class="lafka-pdp__gallery">
                    <?php woocommerce_show_product_images(); ?>
                </div>
                <?php require get_stylesheet_directory() . '/partials/pdp-summary.php'; ?>
            </div>

            <?php require get_stylesheet_directory() . '/partials/pdp-make-it-a-meal.php'; ?>
            <?php require get_stylesheet_directory() . '/partials/pdp-tabs.php'; ?>

            <?php woocommerce_output_related_products(); ?>

        <?php endwhile; ?>
    </main>

    <?php require get_stylesheet_directory() . '/partials/cart-drawer.php'; ?>
</div>
<?php
get_footer( 'shop' );
