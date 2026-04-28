<?php
/**
 * Partial: Editorial "Our Story" section.
 *
 * Settings: lafka_editorial_home_story_label / _h2_before / _h2_em / _h2_after
 *           _p1 / _pullquote / _p2 / _image
 *
 * Renders nothing if no content is configured.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$label     = get_theme_mod( 'lafka_editorial_home_story_label',     '' );
$h2_before = get_theme_mod( 'lafka_editorial_home_story_h2_before', '' );
$h2_em     = get_theme_mod( 'lafka_editorial_home_story_h2_em',     '' );
$h2_after  = get_theme_mod( 'lafka_editorial_home_story_h2_after',  '' );
$p1        = get_theme_mod( 'lafka_editorial_home_story_p1',        '' );
$pullquote = get_theme_mod( 'lafka_editorial_home_story_pullquote', '' );
$p2        = get_theme_mod( 'lafka_editorial_home_story_p2',        '' );
$image     = get_theme_mod( 'lafka_editorial_home_story_image',     '' );

if ( ! $h2_before && ! $h2_em && ! $h2_after && ! $p1 && ! $p2 && ! $image ) {
    return;
}
?>
<section class="story-section">
    <div class="story-grid">

        <?php if ( $image ) : ?>
        <div class="story-photo">
            <img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
        </div>
        <?php endif; ?>

        <div class="story-text">
            <?php if ( $label ) : ?>
            <div class="label"><?php echo esc_html( $label ); ?></div>
            <?php endif; ?>

            <?php if ( $h2_before || $h2_em || $h2_after ) : ?>
            <h2>
                <?php echo esc_html( $h2_before ); ?>
                <?php if ( $h2_em ) : ?>
                <em><?php echo esc_html( $h2_em ); ?></em>
                <?php endif; ?>
                <?php echo esc_html( $h2_after ); ?>
            </h2>
            <?php endif; ?>

            <?php if ( $p1 ) : ?>
            <p><?php echo esc_html( $p1 ); ?></p>
            <?php endif; ?>

            <?php if ( $pullquote ) : ?>
            <blockquote class="pullquote"><?php echo esc_html( $pullquote ); ?></blockquote>
            <?php endif; ?>

            <?php if ( $p2 ) : ?>
            <p><?php echo esc_html( $p2 ); ?></p>
            <?php endif; ?>
        </div>

    </div>
</section>
