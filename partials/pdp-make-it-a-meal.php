<?php
/**
 * "Make it a meal" upsell row partial.
 *
 * Delegates to lafka_pdp_render_upsell_row() in lafka-plugin (W4-T6).
 * Visual styling lives in pdp-redesign.css (this task).
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( $product instanceof WC_Product && function_exists( 'lafka_pdp_render_upsell_row' ) ) {
	lafka_pdp_render_upsell_row( $product->get_id() );
}
