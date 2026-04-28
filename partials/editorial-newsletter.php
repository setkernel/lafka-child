<?php
/**
 * Partial: Editorial newsletter section (brand-red background).
 *
 * Settings: lafka_editorial_home_newsletter_heading / _intro / _form_html
 *
 * The form HTML is operator-provided (Mailchimp / CF7 embed). When not
 * configured, the entire section is suppressed.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$heading   = get_theme_mod( 'lafka_editorial_home_newsletter_heading', '' );
$intro     = get_theme_mod( 'lafka_editorial_home_newsletter_intro',   '' );
$form_html = get_theme_mod( 'lafka_editorial_home_newsletter_form_html', '' );

if ( ! $heading && ! $form_html ) {
    return;
}
?>
<section class="newsletter-section">
    <div class="newsletter-grid">
        <div>
            <?php if ( $heading ) : ?>
            <h2><?php echo esc_html( $heading ); ?></h2>
            <?php endif; ?>
            <?php if ( $intro ) : ?>
            <p><?php echo esc_html( $intro ); ?></p>
            <?php endif; ?>
        </div>

        <?php if ( $form_html ) : ?>
        <div class="newsletter-form-wrap">
            <?php echo wp_kses_post( $form_html ); ?>
        </div>
        <?php endif; ?>
    </div>
</section>
