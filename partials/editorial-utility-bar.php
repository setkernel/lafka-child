<?php
/**
 * Partial: Editorial utility bar (dark top strip).
 *
 * Reads NAP/hours from lafka_get_restaurant_info() (W2-T1 source-of-truth).
 * Falls back gracefully when the plugin is inactive.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$info = function_exists( 'lafka_get_restaurant_info' ) ? lafka_get_restaurant_info() : array();

$address       = ! empty( $info['address_display'] ) ? $info['address_display'] : '';
$phone_e164    = ! empty( $info['phone_e164'] )      ? $info['phone_e164']      : '';
$phone_display = ! empty( $info['phone_display'] )   ? $info['phone_display']   : $phone_e164;
?>
<div class="editorial-utility">
    <?php if ( $address ) : ?>
    <span><?php echo esc_html( $address ); ?></span>
    <?php endif; ?>

    <div class="utility-right">
        <span class="utility-open"><?php esc_html_e( 'Open now', 'lafka' ); ?></span>
        <?php if ( $phone_e164 ) : ?>
        <a href="tel:<?php echo esc_attr( $phone_e164 ); ?>"><?php echo esc_html( $phone_display ); ?></a>
        <?php endif; ?>
    </div>
</div>
