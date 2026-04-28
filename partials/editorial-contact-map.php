<?php
/**
 * Partial: Editorial Contact — map column.
 *
 * Map embed URL from Customizer: lafka_editorial_contact_map_embed_url.
 * Lazy-loaded iframe for performance. Renders nothing if no URL is configured.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$map_url = get_theme_mod( 'lafka_editorial_contact_map_embed_url', '' );

if ( ! $map_url ) {
    return;
}
?>
<div class="contact-map-frame">
    <iframe
        src="<?php echo esc_url( $map_url ); ?>"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        title="<?php esc_attr_e( 'Location map', 'lafka' ); ?>"
        allowfullscreen
    ></iframe>
</div>
