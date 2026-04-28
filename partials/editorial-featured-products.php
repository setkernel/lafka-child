<?php
/**
 * Partial: Editorial featured products section (dark background, 3-up grid).
 *
 * Pulls WooCommerce products marked as "featured". Renders nothing if WC is
 * inactive or returns no featured products (graceful degradation).
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
    return; // WooCommerce not active
}

$products = wc_get_products( array(
    'featured' => true,
    'limit'    => 3,
    'status'   => 'publish',
    'orderby'  => 'date',
    'order'    => 'DESC',
) );

if ( empty( $products ) ) {
    return;
}
?>
<section class="featured-section">
    <div class="section-head">
        <div>
            <div class="label"><?php esc_html_e( '&mdash; Most ordered this week', 'lafka' ); ?></div>
            <h2><?php esc_html_e( 'Our featured items', 'lafka' ); ?></h2>
        </div>
    </div>

    <div class="products-grid">
        <?php foreach ( $products as $product ) :
            $thumb_url = get_the_post_thumbnail_url( $product->get_id(), 'medium_large' );
            $price_html = $product->get_price_html();
            $name       = $product->get_name();
            $desc       = wp_strip_all_tags( $product->get_short_description() );
            $url        = get_permalink( $product->get_id() );
        ?>
        <article class="product-card">
            <a href="<?php echo esc_url( $url ); ?>" class="product-photo">
                <?php if ( $thumb_url ) : ?>
                <img
                    src="<?php echo esc_url( $thumb_url ); ?>"
                    alt="<?php echo esc_attr( $name ); ?>"
                    loading="lazy"
                >
                <?php endif; ?>
            </a>
            <h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a></h3>
            <?php if ( $desc ) : ?>
            <p class="product-desc"><?php echo esc_html( $desc ); ?></p>
            <?php endif; ?>
            <div class="price-row">
                <span class="product-price"><?php echo wp_kses_post( $price_html ); ?></span>
                <a href="<?php echo esc_url( $url ); ?>" class="btn btn-primary" style="font-size:0.8rem;padding:0.6rem 1rem;">
                    <?php esc_html_e( 'View', 'lafka' ); ?>
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
