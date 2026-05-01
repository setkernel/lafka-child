<?php
declare(strict_types=1);

namespace LafkaChild\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * v6.0.0 lock: lafka-child must remain a thin override layer.
 *
 * If a future commit re-introduces product features into the child theme,
 * this test fails — pushing the contributor to put the feature in the
 * parent theme or the plugin instead.
 */
final class ThinLayerTest extends TestCase {

	public function test_functions_php_under_size_limit(): void {
		$lines = count( file( dirname( __DIR__, 2 ) . '/functions.php', FILE_IGNORE_NEW_LINES ) );
		$this->assertLessThan(
			120,
			$lines,
			"lafka-child/functions.php is {$lines} lines — should stay under 120. If you're adding a feature, it probably belongs in lafka-theme or lafka-plugin."
		);
	}

	public function test_no_promotions_symbols(): void {
		$src = file_get_contents( dirname( __DIR__, 2 ) . '/functions.php' );
		foreach ( array( 'bogo_50_', 'lafka_bogo_', 'lafka_child_should_block_delivery', 'LAFKA_CHILD_DELIVERY_MINIMUM' ) as $sym ) {
			$this->assertStringNotContainsString( $sym, $src, "Promotions symbol '{$sym}' must live in lafka-plugin." );
		}
	}

	public function test_no_pdp_redesign_symbols(): void {
		$src = file_get_contents( dirname( __DIR__, 2 ) . '/functions.php' );
		foreach ( array( 'lafka-pdp-redesign', 'lafka-cart-drawer', 'lafka-order-method', 'lafkaPdpCurrency', 'lafkaOrderMethodLabels' ) as $sym ) {
			$this->assertStringNotContainsString( $sym, $src, "PDP-redesign symbol '{$sym}' must live in lafka-theme." );
		}
	}

	public function test_no_editorial_symbols(): void {
		$src = file_get_contents( dirname( __DIR__, 2 ) . '/functions.php' );
		foreach ( array( 'lafka_editorial', 'template-editorial', 'editorial-home', 'editorial-contact' ) as $sym ) {
			$this->assertStringNotContainsString( $sym, $src, "Editorial symbol '{$sym}' must live in lafka-theme." );
		}
	}

	public function test_no_perf_helper_symbols(): void {
		$src = file_get_contents( dirname( __DIR__, 2 ) . '/functions.php' );
		foreach ( array( 'lafka_inject_image_dimensions', 'lafka_url_to_local_path', 'lafka_lcp_image_url' ) as $sym ) {
			$this->assertStringNotContainsString( $sym, $src, "Perf helper '{$sym}' must live in lafka-plugin." );
		}
	}

	public function test_enqueue_function_still_present(): void {
		$src = file_get_contents( dirname( __DIR__, 2 ) . '/functions.php' );
		$this->assertStringContainsString( 'function lafka_child_enqueue_styles', $src );
	}

	public function test_no_orphan_partials_directory(): void {
		$child_root = dirname( __DIR__, 2 );
		$this->assertFileDoesNotExist( $child_root . '/inc/customizer-editorial.php' );
		$this->assertFileDoesNotExist( $child_root . '/inc/lafka-promotions.php' );
		$this->assertFileDoesNotExist( $child_root . '/woocommerce/single-product.php' );
		$this->assertFileDoesNotExist( $child_root . '/partials/cart-drawer.php' );
		$this->assertFileDoesNotExist( $child_root . '/styles/editorial.css' );
		$this->assertFileDoesNotExist( $child_root . '/styles/pdp-redesign.css' );
	}
}
