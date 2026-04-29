<?php
/**
 * PDP tabbed details — Description + Reviews only (5B option 2 from spec).
 *
 * Drops Allergens + Nutrition tabs for v1; those become Phase 2 once
 * operator-curated dietary data is populated per product.
 *
 * @package LafkaChild\Partials
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! ( $product instanceof WC_Product ) ) {
    return;
}
$reviews_enabled = comments_open() && get_option( 'woocommerce_enable_reviews' ) === 'yes';
?>
<div class="lafka-pdp-tabs">
    <div class="lafka-pdp-tabs__buttons" role="tablist">
        <button type="button" role="tab" aria-selected="true" aria-controls="lafka-pdp-tab-description" id="lafka-pdp-tab-button-description">
            <?php esc_html_e( 'Description', 'lafka-child' ); ?>
        </button>
        <?php if ( $reviews_enabled ): ?>
            <button type="button" role="tab" aria-selected="false" aria-controls="lafka-pdp-tab-reviews" id="lafka-pdp-tab-button-reviews">
                <?php
                $count = $product->get_review_count();
                printf( esc_html__( 'Reviews (%d)', 'lafka-child' ), (int) $count );
                ?>
            </button>
        <?php endif; ?>
    </div>

    <div class="lafka-pdp-tabs__panels">
        <div role="tabpanel" id="lafka-pdp-tab-description" aria-labelledby="lafka-pdp-tab-button-description" class="lafka-pdp-tabs__panel" data-active="true">
            <?php
            $content = $product->get_description();
            echo wp_kses_post( wpautop( $content ) );
            ?>
        </div>
        <?php if ( $reviews_enabled ): ?>
            <div role="tabpanel" id="lafka-pdp-tab-reviews" aria-labelledby="lafka-pdp-tab-button-reviews" class="lafka-pdp-tabs__panel" hidden>
                <?php comments_template(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
  var buttons = document.querySelectorAll('.lafka-pdp-tabs__buttons [role="tab"]');
  var panels  = document.querySelectorAll('.lafka-pdp-tabs__panel');
  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      buttons.forEach(function (b) { b.setAttribute('aria-selected', 'false'); });
      panels.forEach(function (p) { p.hidden = true; p.dataset.active = 'false'; });
      btn.setAttribute('aria-selected', 'true');
      var target = document.getElementById(btn.getAttribute('aria-controls'));
      if (target) { target.hidden = false; target.dataset.active = 'true'; }
    });
  });
})();
</script>
