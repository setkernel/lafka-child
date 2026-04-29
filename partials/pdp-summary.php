<?php
/**
 * PDP summary right-column composition.
 *
 * Composes: best-seller eyebrow, title, short description, live price,
 * last-order card, variation+addon pickers, qty stepper + Add-to-Cart,
 * mobile-only sticky bottom CTA, trust signal.
 *
 * Used inside the WC variations_form so the existing add-to-cart submit
 * path is preserved (variation_id hidden field, action URL, classes).
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! ( $product instanceof WC_Product ) ) {
    return;
}
?>
<div class="lafka-pdp-summary">

    <?php if ( function_exists( 'lafka_pdp_render_bestseller_eyebrow' ) ) {
        lafka_pdp_render_bestseller_eyebrow( $product->get_id() );
    } ?>

    <h1 class="lafka-pdp-summary__title"><?php echo esc_html( $product->get_name() ); ?></h1>

    <div class="lafka-pdp-summary__short">
        <?php echo wp_kses_post( $product->get_short_description() ); ?>
    </div>

    <div class="lafka-pdp-summary__price">
        <span data-lafka-live-price>$<?php echo esc_html( wc_format_decimal( (string) $product->get_price(), 2 ) ); ?></span>
        <?php if ( $product->is_type( 'variable' ) ): ?>
            <small><?php printf(
                esc_html__( 'starting at %s', 'lafka-child' ),
                wp_kses_post( wc_price( $product->get_variation_price( 'min', true ) ) )
            ); ?></small>
        <?php endif; ?>
    </div>

    <?php require __DIR__ . '/pdp-last-order-card.php'; ?>

    <form class="cart variations_form"
          action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
          method="post"
          enctype="multipart/form-data"
          data-product_id="<?php echo absint( $product->get_id() ); ?>"
          data-product_variations="<?php echo function_exists('wc_esc_json') ? wc_esc_json( wp_json_encode( $product->get_available_variations() ) ) : esc_attr( wp_json_encode( $product->get_available_variations() ) ); ?>">

        <?php require __DIR__ . '/pdp-pickers.php'; ?>

        <div class="lafka-pdp-summary__cart-row">
            <div class="lafka-pdp-summary__qty">
                <button type="button" class="lafka-pdp-qty__btn" data-lafka-qty="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'lafka-child' ); ?>">−</button>
                <input type="number" name="quantity" value="1" min="1" class="lafka-pdp-qty__input">
                <button type="button" class="lafka-pdp-qty__btn" data-lafka-qty="+1" aria-label="<?php esc_attr_e( 'Increase quantity', 'lafka-child' ); ?>">+</button>
            </div>
            <button type="submit" class="lafka-pdp-summary__cta" data-lafka-add-to-cart disabled data-lafka-state="incomplete">
                <span data-lafka-cta-label><?php esc_html_e( 'Pick a size to continue', 'lafka-child' ); ?></span>
            </button>
        </div>

        <!-- Mobile-only sticky bottom CTA -->
        <div class="lafka-pdp-mobile-cta">
            <div class="lafka-pdp-mobile-cta__qty">
                <button type="button" data-lafka-qty="-1" aria-label="<?php esc_attr_e( 'Decrease', 'lafka-child' ); ?>">−</button>
                <span data-lafka-qty-display>1</span>
                <button type="button" data-lafka-qty="+1" aria-label="<?php esc_attr_e( 'Increase', 'lafka-child' ); ?>">+</button>
            </div>
            <button type="submit" class="lafka-pdp-mobile-cta__btn" data-lafka-add-to-cart disabled>
                <span data-lafka-cta-label><?php esc_html_e( 'Pick a size', 'lafka-child' ); ?></span>
            </button>
        </div>

        <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
        <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>">
        <input type="hidden" name="variation_id" class="variation_id" value="0">
    </form>

    <div class="lafka-pdp-summary__trust">
        <?php if ( function_exists( 'lafka_pdp_render_prep_time' ) ) {
            lafka_pdp_render_prep_time( $product->get_id() );
        } ?>
    </div>
</div>
