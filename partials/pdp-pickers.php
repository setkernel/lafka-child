<?php
/**
 * PDP variation + addon pickers — size + crust chips, then toppings.
 *
 * Required global: $product (WC_Product instance). Renders inside the
 * cart variations_form so the existing add-to-cart submit path still works.
 *
 * The Lafka addons hook 'woocommerce_before_add_to_cart_button' fires here
 * to render the Pizza Toppings group via lafka-plugin's existing addon
 * markup; we only restyle visually (chip CSS).
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
    return;
}

$attributes = $product->get_variation_attributes();
$variations = $product->is_type( 'variable' ) ? $product->get_available_variations() : array();
$prices_by_attrs = array();
foreach ( $variations as $v ) {
    $key = wp_json_encode( $v['attributes'] );
    $prices_by_attrs[ $key ] = wc_format_decimal( (string) $v['display_price'], 2 );
}
?>
<div class="lafka-pdp-pickers" data-prices='<?php echo esc_attr( wp_json_encode( $prices_by_attrs ) ); ?>'>
    <?php foreach ( $attributes as $attr_name => $options ): ?>
        <?php
        // $attr_name from get_variation_attributes() is the taxonomy slug
        // (e.g. "pa_size") — no "attribute_" prefix. Use it directly as the
        // taxonomy name for term-label lookups.
        //
        // The form-input `name` attribute, however, MUST be prefixed with
        // "attribute_" so WC_Form_Handler::add_to_cart_handler_variable()
        // can find $_REQUEST['attribute_pa_size'] when matching variations.
        // Get this wrong and add-to-cart silently fails with variation_id=0.
        //
        // Per-variation $v['attributes'] keys (from get_available_variations)
        // also use the "attribute_" prefix — that's why the price lookup
        // below indexes with "attribute_$attr_name".
        $taxonomy   = $attr_name;
        $label      = wc_attribute_label( $attr_name, $product );
        $field_name = 'attribute_' . $attr_name;
        ?>
        <fieldset class="lafka-pdp-picker" data-attribute="<?php echo esc_attr( $field_name ); ?>" data-required="true">
            <legend class="lafka-pdp-picker__label"><?php echo esc_html( $label ); ?></legend>
            <div class="lafka-pdp-picker__chips" role="radiogroup" aria-required="true">
                <?php foreach ( $options as $opt ): ?>
                    <?php
                    $term       = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $opt, $taxonomy ) : null;
                    $opt_label  = $term ? $term->name : $opt;
                    $option_price = '';
                    foreach ( $variations as $v ) {
                        if ( ( $v['attributes'][ $field_name ] ?? '' ) === $opt ) {
                            $option_price = wc_price( $v['display_price'] );
                            break;
                        }
                    }
                    ?>
                    <label class="lafka-pdp-chip">
                        <input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $opt ); ?>">
                        <span class="lafka-pdp-chip__inner">
                            <span class="lafka-pdp-chip__name"><?php echo esc_html( $opt_label ); ?></span>
                            <?php if ( $option_price ): ?>
                                <span class="lafka-pdp-chip__price"><?php echo wp_kses_post( $option_price ); ?></span>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    <?php endforeach; ?>
</div>
<?php
// Note: standard WC variations-form hooks (woocommerce_before_variations_form,
// _before_single_variation, _single_variation, _before_add_to_cart_button, etc.)
// are fired by pdp-summary.php in the correct WC-conformant order.
// Don't add do_action() calls here — they'd fire at the wrong place in the
// form structure and break addon-plugin reposition logic.
