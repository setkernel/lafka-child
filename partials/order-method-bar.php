<?php
/**
 * Always-sticky address/method bar.
 *
 * Hooked to wp_body_open in lafka-child/functions.php (W4-T21).
 * Reads from lafka_get_restaurant_info() for store address + hours.
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lafka_pdp_redesign_enabled' ) || ! lafka_pdp_redesign_enabled() ) {
    return;
}
if ( ! function_exists( 'lafka_get_restaurant_info' ) ) {
    return;
}

$info = lafka_get_restaurant_info();
$method = isset( $_COOKIE['lafka_order_method'] ) && 'pickup' === $_COOKIE['lafka_order_method'] ? 'pickup' : 'delivery';
$is_open = function_exists( 'lafka_pdp_is_store_open' ) ? lafka_pdp_is_store_open() : true;
$today_hours = $info['hours'][ wp_date( 'l' ) ] ?? '';
$close_time = '';
if ( preg_match( '/-(\d{2}:\d{2})$/', $today_hours, $m ) ) {
    $close_time = $m[1];
}
?>
<div class="lafka-order-method-bar" data-method="<?php echo esc_attr( $method ); ?>">
    <div class="lafka-order-method-bar__inner">
        <button type="button" class="lafka-order-method-bar__method" data-lafka-method-toggle>
            <span class="lafka-order-method-bar__icon" aria-hidden="true">
                <?php echo 'pickup' === $method ? '🏪' : '🚚'; ?>
            </span>
            <span class="lafka-order-method-bar__method-label">
                <?php echo 'pickup' === $method
                    ? esc_html__( 'Pickup at', 'lafka-child' )
                    : esc_html__( 'Delivery to', 'lafka-child' ); ?>
            </span>
            <span class="lafka-order-method-bar__location">
                <?php
                if ( 'pickup' === $method ) {
                    // Operator-specific value MUST come from the resolver
                    // (theme_mod → option → WP-core → empty). Never hardcode
                    // a literal address — the lafka-* repos are public OSS
                    // and operator data must not leak into them. If the
                    // resolver returns empty, render an empty span rather
                    // than fall back to a literal string.
                    echo esc_html( $info['address_short'] );
                } else {
                    echo esc_html( $info['city'] . ', ' . $info['region'] );
                }
                ?>
            </span>
            <span class="lafka-order-method-bar__switch"><?php esc_html_e( 'Switch', 'lafka-child' ); ?></span>
        </button>
        <div class="lafka-order-method-bar__right">
            <?php if ( ! empty( $info['phone_display'] ) ): ?>
                <a class="lafka-order-method-bar__phone" href="tel:<?php echo esc_attr( $info['phone_e164'] ); ?>">📞 <?php echo esc_html( $info['phone_display'] ); ?></a>
            <?php endif; ?>
            <span class="lafka-order-method-bar__hours">
                <?php if ( $is_open && $close_time ): ?>
                    <?php printf( esc_html__( 'Open until %s', 'lafka-child' ), esc_html( $close_time ) ); ?>
                <?php elseif ( $is_open ): ?>
                    <?php esc_html_e( 'Open', 'lafka-child' ); ?>
                <?php else: ?>
                    <?php esc_html_e( 'Closed — opens 11:00 AM', 'lafka-child' ); ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>
