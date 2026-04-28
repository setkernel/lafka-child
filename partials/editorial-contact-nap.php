<?php
/**
 * Partial: Editorial Contact — NAP + hours column.
 *
 * Reads from lafka_get_restaurant_info() (W2-T1 source-of-truth).
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$info = function_exists( 'lafka_get_restaurant_info' ) ? lafka_get_restaurant_info() : array();

$phone_e164    = ! empty( $info['phone_e164'] )      ? $info['phone_e164']      : '';
$phone_display = ! empty( $info['phone_display'] )   ? $info['phone_display']   : $phone_e164;
$email         = ! empty( $info['email'] )           ? $info['email']           : '';
$address       = ! empty( $info['address_display'] ) ? $info['address_display'] : '';
$hours         = ! empty( $info['hours'] )           ? $info['hours']           : array();

$today_name = wp_date( 'l' );
?>
<div class="contact-nap">
    <h2><?php esc_html_e( 'Find us', 'lafka' ); ?></h2>

    <?php if ( $phone_e164 ) : ?>
    <div class="contact-block">
        <div class="block-label"><?php esc_html_e( 'Phone', 'lafka' ); ?></div>
        <div class="block-value">
            <a href="tel:<?php echo esc_attr( $phone_e164 ); ?>"><?php echo esc_html( $phone_display ); ?></a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( $email ) : ?>
    <div class="contact-block">
        <div class="block-label"><?php esc_html_e( 'Email', 'lafka' ); ?></div>
        <div class="block-value">
            <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( $address ) : ?>
    <div class="contact-block">
        <div class="block-label"><?php esc_html_e( 'Address', 'lafka' ); ?></div>
        <div class="block-value"><?php echo nl2br( esc_html( $address ) ); ?></div>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $hours ) ) : ?>
    <div class="contact-block">
        <div class="block-label"><?php esc_html_e( 'Hours', 'lafka' ); ?></div>
        <div class="block-value">
            <div class="contact-hours">
                <?php foreach ( $hours as $day => $time ) :
                    $is_today = ( strtolower( $today_name ) === strtolower( $day ) );
                ?>
                <div class="<?php echo $is_today ? 'day today' : 'day'; ?>"><?php echo esc_html( $day ); ?></div>
                <div class="<?php echo $is_today ? 'time today' : 'time'; ?>"><?php echo esc_html( $time ); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
