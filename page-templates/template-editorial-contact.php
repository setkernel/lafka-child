<?php
/**
 * Template Name: Editorial Contact (Lafka)
 * Template Post Type: page
 *
 * P6-UX-4 W3-T8: alternate contact page template with editorial / magazine
 * design language. Selectable via Page Attributes → Template dropdown.
 *
 * Content lives in Customizer (panel "Lafka — Editorial Contact"). NAP and
 * hours come from lafka_get_restaurant_info() (W2-T1 source-of-truth).
 *
 * Assets (Fraunces font + editorial.css) are conditionally enqueued by
 * lafka_editorial_assets_enqueue() — only loaded when this template is active.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="content" class="lafka-editorial-contact" tabindex="-1">

    <?php /* Utility bar */ ?>
    <?php get_template_part( 'partials/editorial-utility-bar' ); ?>

    <?php /* Page heading */ ?>
    <section class="contact-head">
        <div class="contact-head-inner">
            <h1><?php echo esc_html( get_theme_mod( 'lafka_editorial_contact_h1', __( 'Contact us', 'lafka' ) ) ); ?></h1>
            <?php $intro = get_theme_mod( 'lafka_editorial_contact_intro', '' ); ?>
            <?php if ( $intro ) : ?>
            <p class="contact-intro"><?php echo esc_html( $intro ); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php /* 3-column grid: NAP | Map | Form */ ?>
    <div class="contact-grid">
        <?php get_template_part( 'partials/editorial-contact-nap' ); ?>
        <?php get_template_part( 'partials/editorial-contact-map' ); ?>
        <?php get_template_part( 'partials/editorial-contact-form' ); ?>
    </div>

</main>

<?php
get_footer();
