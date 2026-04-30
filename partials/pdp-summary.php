<?php
/**
 * PDP summary right-column composition.
 *
 * Composes: best-seller eyebrow, title, short description, live price,
 * last-order card, variation+addon pickers, qty stepper + Add-to-Cart,
 * mobile-only sticky bottom CTA, trust signal.
 *
 * Form structure mirrors WooCommerce's standard variable.php template so
 * plugins that hook into the addon/variation pipeline (e.g. lafka-plugin's
 * own product-addons system, which `reposition_display_for_variable_product()`
 * relies on woocommerce_before_variations_form firing first) get the same
 * lifecycle they expect. All standard hooks fire in the same order.
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! ( $product instanceof WC_Product ) ) {
    return;
}

$is_variable = $product->is_type( 'variable' );
$form_action = apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() );
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
        <?php // Currency symbol/position MUST come from WC settings (wc_price()
              // honours woocommerce_currency_pos + currency code). Hardcoding
              // `$` leaks the operator's currency and breaks any non-USD shop.
              // JS replaces this textContent on size change via the formatter
              // localized in functions.php — same currency settings, same
              // output shape. ?>
        <span data-lafka-live-price><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
        <?php if ( $is_variable ): ?>
            <small><?php printf(
                esc_html__( 'starting at %s', 'lafka-child' ),
                wp_kses_post( wc_price( $product->get_variation_price( 'min', true ) ) )
            ); ?></small>
        <?php endif; ?>
    </div>

    <?php require __DIR__ . '/pdp-last-order-card.php'; ?>

    <?php if ( $is_variable ): ?>
        <?php
        // Fire BEFORE the form opens — this triggers the lafka-plugin addon
        // system's reposition_display_for_variable_product(), which moves
        // the addon display() callback from woocommerce_before_add_to_cart_button
        // to woocommerce_single_variation. Without this, no addons render
        // for variable products.
        do_action( 'woocommerce_before_variations_form' );
        ?>
        <form class="cart variations_form"
              action="<?php echo esc_url( $form_action ); ?>"
              method="post"
              enctype="multipart/form-data"
              data-product_id="<?php echo absint( $product->get_id() ); ?>"
              data-product_variations="<?php echo function_exists( 'wc_esc_json' ) ? wc_esc_json( wp_json_encode( $product->get_available_variations() ) ) : esc_attr( wp_json_encode( $product->get_available_variations() ) ); ?>">

            <?php do_action( 'woocommerce_before_variations_table' ); ?>
            <?php require __DIR__ . '/pdp-pickers.php'; ?>
            <?php do_action( 'woocommerce_after_variations_table' ); ?>

            <div class="single_variation_wrap">
                <?php do_action( 'woocommerce_before_single_variation' ); ?>

                <div class="single_variation"></div>

                <div class="variations_button">
                    <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
                    <?php do_action( 'woocommerce_before_add_to_cart_quantity' ); ?>

                    <div class="lafka-pdp-summary__cart-row">
                        <div class="quantity lafka-pdp-summary__qty">
                            <button type="button" class="lafka-pdp-qty__btn" data-lafka-qty="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'lafka-child' ); ?>">−</button>
                            <input type="number" name="quantity" value="1" min="1" class="qty lafka-pdp-qty__input">
                            <button type="button" class="lafka-pdp-qty__btn" data-lafka-qty="+1" aria-label="<?php esc_attr_e( 'Increase quantity', 'lafka-child' ); ?>">+</button>
                        </div>
                        <button type="submit" class="lafka-pdp-summary__cta" data-lafka-add-to-cart disabled data-lafka-state="incomplete">
                            <span data-lafka-cta-label><?php esc_html_e( 'Pick a size to continue', 'lafka-child' ); ?></span>
                        </button>
                    </div>

                    <div class="lafka-pdp-mobile-cta">
                        <div class="lafka-pdp-mobile-cta__qty">
                            <button type="button" data-lafka-qty="-1" aria-label="<?php esc_attr_e( 'Decrease', 'lafka-child' ); ?>">−</button>
                            <span data-lafka-qty-display>1</span>
                            <button type="button" data-lafka-qty="+1" aria-label="<?php esc_attr_e( 'Increase', 'lafka-child' ); ?>">+</button>
                        </div>
                        <button type="submit" class="lafka-pdp-mobile-cta__btn" data-lafka-add-to-cart disabled data-lafka-state="incomplete">
                            <span data-lafka-cta-label><?php esc_html_e( 'Pick a size', 'lafka-child' ); ?></span>
                        </button>
                    </div>

                    <?php do_action( 'woocommerce_after_add_to_cart_quantity' ); ?>
                    <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
                </div>

                <?php
                // WC core hooks two callbacks to woocommerce_single_variation:
                //   - priority 10: woocommerce_single_variation() — renders an
                //     empty wrapper div we already have above.
                //   - priority 20: woocommerce_single_variation_add_to_cart_button() —
                //     renders ANOTHER quantity input, ANOTHER submit button, and
                //     duplicate hidden inputs (add-to-cart, product_id, variation_id).
                // The duplicate submit button isn't bound to our picker-state JS,
                // so clicking it submits without our validation and WC errors out
                // with "Please choose product options for X".
                //
                // We need woocommerce_single_variation to fire so the lafka-plugin
                // addon system's repositioned display() callback runs (it's at
                // priority 15, between WC's two defaults).
                //
                // CRITICAL: Restore the callbacks IMMEDIATELY after firing.
                // Previously these remove_actions persisted for the rest of the
                // request, which broke quick-view AJAX, combo-product partials,
                // and any later product render in the same request that
                // expected WC's stock single_variation behavior.
                $lafka_wc_sv_priority_10_was_hooked = has_action( 'woocommerce_single_variation', 'woocommerce_single_variation' );
                $lafka_wc_sv_priority_20_was_hooked = has_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button' );

                if ( false !== $lafka_wc_sv_priority_10_was_hooked ) {
                    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', $lafka_wc_sv_priority_10_was_hooked );
                }
                if ( false !== $lafka_wc_sv_priority_20_was_hooked ) {
                    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', $lafka_wc_sv_priority_20_was_hooked );
                }

                do_action( 'woocommerce_single_variation' );

                if ( false !== $lafka_wc_sv_priority_10_was_hooked ) {
                    add_action( 'woocommerce_single_variation', 'woocommerce_single_variation', $lafka_wc_sv_priority_10_was_hooked );
                }
                if ( false !== $lafka_wc_sv_priority_20_was_hooked ) {
                    add_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', $lafka_wc_sv_priority_20_was_hooked );
                }

                do_action( 'woocommerce_after_single_variation' );
                ?>
            </div>

            <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
            <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>">
            <input type="hidden" name="variation_id" class="variation_id" value="0">
        </form>
        <?php do_action( 'woocommerce_after_variations_form' ); ?>

    <?php else: /* simple / combo / etc. */ ?>

        <form class="cart"
              action="<?php echo esc_url( $form_action ); ?>"
              method="post"
              enctype="multipart/form-data">

            <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
            <?php do_action( 'woocommerce_before_add_to_cart_quantity' ); ?>

            <div class="lafka-pdp-summary__cart-row">
                <div class="quantity lafka-pdp-summary__qty">
                    <button type="button" class="lafka-pdp-qty__btn" data-lafka-qty="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'lafka-child' ); ?>">−</button>
                    <input type="number" name="quantity" value="1" min="1" class="qty lafka-pdp-qty__input">
                    <button type="button" class="lafka-pdp-qty__btn" data-lafka-qty="+1" aria-label="<?php esc_attr_e( 'Increase quantity', 'lafka-child' ); ?>">+</button>
                </div>
                <button type="submit" class="lafka-pdp-summary__cta" data-lafka-add-to-cart>
                    <span data-lafka-cta-label><?php esc_html_e( 'Add to Cart', 'lafka-child' ); ?></span>
                </button>
            </div>

            <div class="lafka-pdp-mobile-cta">
                <div class="lafka-pdp-mobile-cta__qty">
                    <button type="button" data-lafka-qty="-1" aria-label="<?php esc_attr_e( 'Decrease', 'lafka-child' ); ?>">−</button>
                    <span data-lafka-qty-display>1</span>
                    <button type="button" data-lafka-qty="+1" aria-label="<?php esc_attr_e( 'Increase', 'lafka-child' ); ?>">+</button>
                </div>
                <button type="submit" class="lafka-pdp-mobile-cta__btn" data-lafka-add-to-cart>
                    <span data-lafka-cta-label><?php esc_html_e( 'Add to Cart', 'lafka-child' ); ?></span>
                </button>
            </div>

            <?php do_action( 'woocommerce_after_add_to_cart_quantity' ); ?>
            <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

            <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
        </form>

    <?php endif; ?>

    <div class="lafka-pdp-summary__trust">
        <?php if ( function_exists( 'lafka_pdp_render_prep_time' ) ) {
            lafka_pdp_render_prep_time( $product->get_id() );
        } ?>
    </div>
</div>
