<?php
/**
 * Slide-in cart drawer.
 *
 * Hooked to wp_footer in lafka-child/functions.php (W4-T21). Hidden
 * until activated via JS class toggle (W4-T15).
 *
 * The <ul class="lafka-cart-drawer__items"> and <div class="lafka-cart-drawer__total">
 * children are populated/refreshed by lafka-plugin's wc_cart_fragments
 * registrar (W4-T7).
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lafka_pdp_redesign_enabled' ) || ! lafka_pdp_redesign_enabled() ) {
    return;
}
if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
    return;
}
?>
<aside class="lafka-cart-drawer" aria-hidden="true" aria-labelledby="lafka-cart-drawer-title" tabindex="-1">
    <div class="lafka-cart-drawer__panel">
        <header class="lafka-cart-drawer__header">
            <h2 id="lafka-cart-drawer-title"><?php esc_html_e( 'Your Cart', 'lafka-child' ); ?></h2>
            <button type="button" class="lafka-cart-drawer__close" data-lafka-cart-close aria-label="<?php esc_attr_e( 'Close cart', 'lafka-child' ); ?>">×</button>
        </header>
        <div class="lafka-cart-drawer__body">
            <ul class="lafka-cart-drawer__items"></ul>
        </div>
        <footer class="lafka-cart-drawer__footer">
            <div class="lafka-cart-drawer__total"></div>
            <div class="lafka-cart-drawer__actions">
                <button type="button" class="lafka-cart-drawer__continue" data-lafka-cart-close>
                    <?php esc_html_e( 'Continue shopping', 'lafka-child' ); ?>
                </button>
                <a class="lafka-cart-drawer__checkout" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
                    <?php esc_html_e( 'Checkout', 'lafka-child' ); ?>
                </a>
            </div>
        </footer>
    </div>
    <div class="lafka-cart-drawer__overlay" data-lafka-cart-close></div>
</aside>
