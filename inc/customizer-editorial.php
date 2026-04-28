<?php
/**
 * Customizer panels for the Editorial templates.
 *
 * P6-UX-1 + P6-UX-4 W3-T8: registers two panels —
 *   "Lafka — Editorial Home"    (maps to template-editorial-home.php)
 *   "Lafka — Editorial Contact" (maps to template-editorial-contact.php)
 *
 * All operator content flows through these settings. No hardcoded
 * restaurant-specific text — defaults are generic or empty.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', 'lafka_editorial_customizer_register' );

/**
 * Register editorial Customizer panels, sections, and settings.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function lafka_editorial_customizer_register( WP_Customize_Manager $wp_customize ) {

    // =========================================================================
    // PANEL: Lafka — Editorial Home
    // =========================================================================

    $wp_customize->add_panel( 'lafka_editorial_home', array(
        'title'       => __( 'Lafka — Editorial Home', 'lafka' ),
        'description' => __( 'Content for the Editorial Home alternate page template (P6-UX-1).', 'lafka' ),
        'priority'    => 160,
    ) );

    // -------------------------------------------------------------------------
    // Section: Hero
    // -------------------------------------------------------------------------

    $wp_customize->add_section( 'lafka_editorial_home_hero', array(
        'title'    => __( 'Hero', 'lafka' ),
        'panel'    => 'lafka_editorial_home',
        'priority' => 10,
    ) );

    // Eyebrow line
    $wp_customize->add_setting( 'lafka_editorial_home_hero_eyebrow', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_hero_eyebrow', array(
        'label'   => __( 'Eyebrow (small uppercase line above H1)', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    // H1 — before accent word
    $wp_customize->add_setting( 'lafka_editorial_home_hero_h1_before', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_hero_h1_before', array(
        'label'   => __( 'H1 — text before the gold-accent word', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    // H1 — accent word (shown in gold)
    $wp_customize->add_setting( 'lafka_editorial_home_hero_h1_accent', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_hero_h1_accent', array(
        'label'   => __( 'H1 — gold-accent word', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    // H1 — after accent word
    $wp_customize->add_setting( 'lafka_editorial_home_hero_h1_after', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_hero_h1_after', array(
        'label'   => __( 'H1 — text after the gold-accent word', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    // Hero subtitle
    $wp_customize->add_setting( 'lafka_editorial_home_hero_subtitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_hero_subtitle', array(
        'label'   => __( 'Hero subtitle paragraph', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'textarea',
    ) );

    // Hero background image
    $wp_customize->add_setting( 'lafka_editorial_home_hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lafka_editorial_home_hero_image', array(
        'label'   => __( 'Hero background image', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
    ) ) );

    // CTA 1 (primary)
    $wp_customize->add_setting( 'lafka_editorial_home_cta1_label', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_cta1_label', array(
        'label'   => __( 'Primary CTA — button label', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_cta1_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_cta1_url', array(
        'label'   => __( 'Primary CTA — URL', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'url',
    ) );

    // CTA 2 (secondary — often phone)
    $wp_customize->add_setting( 'lafka_editorial_home_cta2_label', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_cta2_label', array(
        'label'   => __( 'Secondary CTA — button label', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_cta2_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_cta2_url', array(
        'label'   => __( 'Secondary CTA — URL (e.g. tel:+1…)', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    // CTA 3 (tertiary text-link)
    $wp_customize->add_setting( 'lafka_editorial_home_cta3_label', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_cta3_label', array(
        'label'   => __( 'Tertiary CTA — text-link label', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_cta3_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_cta3_url', array(
        'label'   => __( 'Tertiary CTA — URL', 'lafka' ),
        'section' => 'lafka_editorial_home_hero',
        'type'    => 'url',
    ) );

    // -------------------------------------------------------------------------
    // Section: Social Proof
    // -------------------------------------------------------------------------

    $wp_customize->add_section( 'lafka_editorial_home_proof', array(
        'title'    => __( 'Social Proof Strip', 'lafka' ),
        'panel'    => 'lafka_editorial_home',
        'priority' => 20,
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_proof_quote', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_proof_quote', array(
        'label'   => __( 'Pull-quote text', 'lafka' ),
        'section' => 'lafka_editorial_home_proof',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_proof_stars', array(
        'default'           => 5,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_proof_stars', array(
        'label'       => __( 'Star rating (1–5)', 'lafka' ),
        'section'     => 'lafka_editorial_home_proof',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 1, 'max' => 5, 'step' => 1 ),
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_proof_stats', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_proof_stats', array(
        'label'       => __( 'Stats line (e.g. "4.4 average · 153+ reviews")', 'lafka' ),
        'section'     => 'lafka_editorial_home_proof',
        'type'        => 'text',
    ) );

    // -------------------------------------------------------------------------
    // Section: Category Cards (8 slots)
    // -------------------------------------------------------------------------

    $wp_customize->add_section( 'lafka_editorial_home_cards', array(
        'title'    => __( 'Category Cards (8 slots)', 'lafka' ),
        'panel'    => 'lafka_editorial_home',
        'priority' => 30,
    ) );

    for ( $i = 1; $i <= 8; $i++ ) {
        $pfx = "lafka_editorial_home_card_{$i}";

        $wp_customize->add_setting( "{$pfx}_label", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "{$pfx}_label", array(
            /* translators: %d: card slot number 1–8 */
            'label'   => sprintf( __( 'Card %d — label', 'lafka' ), $i ),
            'section' => 'lafka_editorial_home_cards',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( "{$pfx}_image", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "{$pfx}_image", array(
            /* translators: %d: card slot number 1–8 */
            'label'   => sprintf( __( 'Card %d — image', 'lafka' ), $i ),
            'section' => 'lafka_editorial_home_cards',
        ) ) );

        $wp_customize->add_setting( "{$pfx}_url", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( "{$pfx}_url", array(
            /* translators: %d: card slot number 1–8 */
            'label'   => sprintf( __( 'Card %d — link URL', 'lafka' ), $i ),
            'section' => 'lafka_editorial_home_cards',
            'type'    => 'url',
        ) );

        $wp_customize->add_setting( "{$pfx}_meta", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "{$pfx}_meta", array(
            /* translators: %d: card slot number 1–8 */
            'label'   => sprintf( __( 'Card %d — meta text (e.g. "From $8.50")', 'lafka' ), $i ),
            'section' => 'lafka_editorial_home_cards',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( "{$pfx}_spotlight", array(
            'default'           => ( 1 === $i ) ? '1' : '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "{$pfx}_spotlight", array(
            /* translators: %d: card slot number 1–8 */
            'label'       => sprintf( __( 'Card %d — spotlight (2×2, one card only)', 'lafka' ), $i ),
            'section'     => 'lafka_editorial_home_cards',
            'type'        => 'checkbox',
        ) );
    }

    // -------------------------------------------------------------------------
    // Section: Story
    // -------------------------------------------------------------------------

    $wp_customize->add_section( 'lafka_editorial_home_story', array(
        'title'    => __( 'Our Story', 'lafka' ),
        'panel'    => 'lafka_editorial_home',
        'priority' => 40,
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_label', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_label', array(
        'label'   => __( 'Section label (e.g. "— Our story")', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_h2_before', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_h2_before', array(
        'label'   => __( 'H2 — text before italic portion', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_h2_em', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_h2_em', array(
        'label'   => __( 'H2 — italic (brand-color) phrase', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_h2_after', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_h2_after', array(
        'label'   => __( 'H2 — text after italic portion', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_p1', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_p1', array(
        'label'   => __( 'First paragraph', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_pullquote', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_pullquote', array(
        'label'   => __( 'Pull-quote', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_p2', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_story_p2', array(
        'label'   => __( 'Second paragraph', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_story_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lafka_editorial_home_story_image', array(
        'label'   => __( 'Story photo (4:5, bordered offset frame)', 'lafka' ),
        'section' => 'lafka_editorial_home_story',
    ) ) );

    // -------------------------------------------------------------------------
    // Section: Visit / Map
    // -------------------------------------------------------------------------

    $wp_customize->add_section( 'lafka_editorial_home_visit', array(
        'title'       => __( 'Visit / Map', 'lafka' ),
        'description' => __( 'NAP and hours come from the Restaurant Information Customizer section (W2-T1). Only the map embed URL is set here.', 'lafka' ),
        'panel'       => 'lafka_editorial_home',
        'priority'    => 50,
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_map_embed_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_map_embed_url', array(
        'label'       => __( 'Google Maps embed iframe src URL', 'lafka' ),
        'description' => __( 'Paste the src="" value from the Google Maps embed <iframe>.', 'lafka' ),
        'section'     => 'lafka_editorial_home_visit',
        'type'        => 'url',
    ) );

    // -------------------------------------------------------------------------
    // Section: Newsletter
    // -------------------------------------------------------------------------

    $wp_customize->add_section( 'lafka_editorial_home_newsletter', array(
        'title'    => __( 'Newsletter', 'lafka' ),
        'panel'    => 'lafka_editorial_home',
        'priority' => 60,
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_newsletter_heading', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_newsletter_heading', array(
        'label'   => __( 'Heading', 'lafka' ),
        'section' => 'lafka_editorial_home_newsletter',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_newsletter_intro', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_newsletter_intro', array(
        'label'   => __( 'Intro line', 'lafka' ),
        'section' => 'lafka_editorial_home_newsletter',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'lafka_editorial_home_newsletter_form_html', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'lafka_editorial_home_newsletter_form_html', array(
        'label'       => __( 'Form HTML (Mailchimp / CF7 embed)', 'lafka' ),
        'description' => __( 'Paste the full embed code. Operator-provided; never hardcoded.', 'lafka' ),
        'section'     => 'lafka_editorial_home_newsletter',
        'type'        => 'textarea',
    ) );

    // =========================================================================
    // SECTION: Lafka — Mobile Menu (P6-UX-6 W3-T10)
    // =========================================================================

    $wp_customize->add_section( 'lafka_mobile_menu', array(
        'title'    => __( 'Lafka — Mobile Menu', 'lafka' ),
        'priority' => 200,
    ) );

    $wp_customize->add_setting( 'lafka_mobile_menu_grouping', array(
        'default'           => 'no',
        'sanitize_callback' => function ( $v ) { return 'yes' === $v ? 'yes' : 'no'; },
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'lafka_mobile_menu_grouping', array(
        'label'       => __( 'Group categories on mobile', 'lafka' ),
        'description' => __( 'When ON, groups your menu items into Pizzas / Mains / Sides / Combos & Kids / Desserts / Drinks. Filterable via lafka_mobile_menu_groups for custom mappings. Default: OFF (flat menu, current behavior).', 'lafka' ),
        'section'     => 'lafka_mobile_menu',
        'type'        => 'select',
        'choices'     => array(
            'no'  => __( 'Flat menu (default)', 'lafka' ),
            'yes' => __( 'Grouped by category type', 'lafka' ),
        ),
    ) );

    // =========================================================================
    // PANEL: Lafka — Editorial Contact
    // =========================================================================

    $wp_customize->add_panel( 'lafka_editorial_contact', array(
        'title'       => __( 'Lafka — Editorial Contact', 'lafka' ),
        'description' => __( 'Content for the Editorial Contact alternate page template (P6-UX-4).', 'lafka' ),
        'priority'    => 161,
    ) );

    $wp_customize->add_section( 'lafka_editorial_contact_main', array(
        'title'    => __( 'Contact Page', 'lafka' ),
        'panel'    => 'lafka_editorial_contact',
        'priority' => 10,
    ) );

    // Page H1
    $wp_customize->add_setting( 'lafka_editorial_contact_h1', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_contact_h1', array(
        'label'   => __( 'Page H1', 'lafka' ),
        'section' => 'lafka_editorial_contact_main',
        'type'    => 'text',
    ) );

    // Intro paragraph
    $wp_customize->add_setting( 'lafka_editorial_contact_intro', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'lafka_editorial_contact_intro', array(
        'label'   => __( 'Intro paragraph', 'lafka' ),
        'section' => 'lafka_editorial_contact_main',
        'type'    => 'textarea',
    ) );

    // CF7 form ID
    $wp_customize->add_setting( 'lafka_editorial_contact_cf7_form_id', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'lafka_editorial_contact_cf7_form_id', array(
        'label'       => __( 'Contact Form 7 — form post ID', 'lafka' ),
        'description' => __( 'Set to 0 to fall back to a mailto: link from Restaurant Information.', 'lafka' ),
        'section'     => 'lafka_editorial_contact_main',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 0, 'step' => 1 ),
    ) );

    // Map embed URL
    $wp_customize->add_setting( 'lafka_editorial_contact_map_embed_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'lafka_editorial_contact_map_embed_url', array(
        'label'       => __( 'Google Maps embed iframe src URL', 'lafka' ),
        'description' => __( 'Can reuse the same URL configured in the Editorial Home panel.', 'lafka' ),
        'section'     => 'lafka_editorial_contact_main',
        'type'        => 'url',
    ) );
}
