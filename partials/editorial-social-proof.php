<?php
/**
 * Partial: Editorial social proof strip (dark bar below hero).
 *
 * Settings: lafka_editorial_home_proof_quote / _stars / _stats
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

$quote = get_theme_mod( 'lafka_editorial_home_proof_quote', '' );
$stars = (int) get_theme_mod( 'lafka_editorial_home_proof_stars', 5 );
$stats = get_theme_mod( 'lafka_editorial_home_proof_stats', '' );
$stars = max( 1, min( 5, $stars ) );

if ( ! $quote && ! $stats ) {
    return; // nothing to show — render nothing
}

$star_str = str_repeat( '&#9733; ', $stars );
?>
<div class="social-proof">
    <div class="stars"><?php echo $star_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- only &# entities ?></div>
    <?php if ( $quote ) : ?>
    <div class="quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</div>
    <?php endif; ?>
    <?php if ( $stats ) : ?>
    <div class="stats"><?php echo esc_html( $stats ); ?></div>
    <?php endif; ?>
</div>
