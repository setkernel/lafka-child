<?php
/**
 * Partial: Editorial category cards grid (8 slots, 4-col layout).
 *
 * One card can be marked as "spotlight" (2×2). Settings per slot:
 *   lafka_editorial_home_card_{N}_label / _image / _url / _meta / _spotlight
 *
 * Only renders if at least one card has a label or image configured.
 *
 * @package Lafka\Child
 */

defined( 'ABSPATH' ) || exit;

// Build card data from theme mods.
$cards = array();
for ( $i = 1; $i <= 8; $i++ ) {
    $cards[] = array(
        'label'     => get_theme_mod( "lafka_editorial_home_card_{$i}_label", '' ),
        'image'     => get_theme_mod( "lafka_editorial_home_card_{$i}_image", '' ),
        'url'       => get_theme_mod( "lafka_editorial_home_card_{$i}_url", '' ),
        'meta'      => get_theme_mod( "lafka_editorial_home_card_{$i}_meta", '' ),
        'spotlight' => (bool) get_theme_mod( "lafka_editorial_home_card_{$i}_spotlight", false ),
    );
}

// Only render when at least one card has content.
$has_content = false;
foreach ( $cards as $c ) {
    if ( $c['label'] || $c['image'] ) {
        $has_content = true;
        break;
    }
}
if ( ! $has_content ) {
    return;
}
?>
<section>
    <div class="cards-wrap">
        <?php foreach ( $cards as $card ) :
            if ( ! $card['label'] && ! $card['image'] ) {
                continue; // skip unconfigured slots
            }
            $class = $card['spotlight'] ? 'card spotlight' : 'card';
            $tag   = $card['url'] ? 'a' : 'div';
            $href  = $card['url'] ? ' href="' . esc_url( $card['url'] ) . '"' : '';
        ?>
        <<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( $class ); ?>"<?php echo $href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- href already escaped ?>>
            <?php if ( $card['image'] ) : ?>
            <div class="photo" style="background-image: url('<?php echo esc_url( $card['image'] ); ?>')"></div>
            <?php endif; ?>
            <div class="content">
                <?php if ( $card['label'] ) : ?>
                <div class="name"><?php echo esc_html( $card['label'] ); ?></div>
                <?php endif; ?>
                <?php if ( $card['meta'] ) : ?>
                <div class="meta"><?php echo esc_html( $card['meta'] ); ?></div>
                <?php endif; ?>
            </div>
        </<?php echo esc_attr( $tag ); ?>>
        <?php endforeach; ?>
    </div>
</section>
