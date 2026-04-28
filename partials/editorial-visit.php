<?php
/**
 * Partial: Editorial Visit / Map section.
 *
 * NAP and hours come from lafka_get_restaurant_info() — the W2-T1 single
 * source of truth. Map embed URL comes from the Customizer.
 *
 * Renders nothing if neither map URL nor restaurant info is available.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$info = function_exists( 'lafka_get_restaurant_info' ) ? lafka_get_restaurant_info() : array();

$map_url       = get_theme_mod( 'lafka_editorial_home_map_embed_url', '' );
$phone_e164    = ! empty( $info['phone_e164'] )      ? $info['phone_e164']      : '';
$phone_display = ! empty( $info['phone_display'] )   ? $info['phone_display']   : $phone_e164;
$address       = ! empty( $info['address_display'] ) ? $info['address_display'] : '';
$hours         = ! empty( $info['hours'] )           ? $info['hours']           : array();
$address_h2    = ! empty( $info['address_short'] )   ? $info['address_short']   : $address;

if ( ! $map_url && ! $phone_e164 && ! $address && empty( $hours ) ) {
    return;
}

// Determine today's day name for "today" highlight.
$today_name = wp_date( 'l' ); // e.g. "Monday"
?>
<section class="visit-section">
    <div class="visit-grid">

        <?php if ( $map_url ) : ?>
        <div class="map-frame">
            <iframe
                src="<?php echo esc_url( $map_url ); ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="<?php esc_attr_e( 'Map', 'lafka' ); ?>"
                allowfullscreen
            ></iframe>
        </div>
        <?php endif; ?>

        <div class="visit-info">
            <div class="label"><?php esc_html_e( '&mdash; Come say hi', 'lafka' ); ?></div>

            <?php if ( $address_h2 ) : ?>
            <h2><?php echo esc_html( $address_h2 ); ?></h2>
            <?php endif; ?>

            <?php if ( $phone_e164 ) : ?>
            <div class="visit-block">
                <div class="small-label"><?php esc_html_e( 'Phone', 'lafka' ); ?></div>
                <div class="value">
                    <a href="tel:<?php echo esc_attr( $phone_e164 ); ?>"><?php echo esc_html( $phone_display ); ?></a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $address ) : ?>
            <div class="visit-block">
                <div class="small-label"><?php esc_html_e( 'Address', 'lafka' ); ?></div>
                <div class="value"><?php echo nl2br( esc_html( $address ) ); ?></div>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $hours ) ) : ?>
            <div class="visit-block">
                <div class="small-label"><?php esc_html_e( 'Hours', 'lafka' ); ?></div>
                <div class="value">
                    <div class="hours-table">
                        <?php foreach ( $hours as $day => $time ) :
                            $is_today = ( strtolower( $today_name ) === strtolower( $day ) );
                            $day_class  = $is_today ? 'day today' : 'day';
                            $time_class = $is_today ? 'time today' : 'time';
                        ?>
                        <div class="<?php echo esc_attr( $day_class ); ?>">
                            <?php echo esc_html( $day ); ?>
                            <?php if ( $is_today ) : ?>
                            <span class="sr-only"> (<?php esc_html_e( 'today', 'lafka' ); ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="<?php echo esc_attr( $time_class ); ?>"><?php echo esc_html( $time ); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $info['directions_url'] ) ) : ?>
            <a href="<?php echo esc_url( $info['directions_url'] ); ?>" class="visit-cta" rel="noopener noreferrer" target="_blank">
                <?php esc_html_e( 'Get directions', 'lafka' ); ?> &rarr;
            </a>
            <?php endif; ?>
        </div>

    </div>
</section>
