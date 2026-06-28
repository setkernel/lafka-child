<?php
declare(strict_types=1);

namespace LafkaChild\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * f090 regression lock: the /cart/ page is fully parent-owned.
 *
 * The child theme once carried a 2026-04-29 CLS reservation
 * (`.woocommerce-cart .woocommerce { min-height: 60vh }` plus a dead
 * `.woocommerce-cart-form__cart-item { min-height: 80px }` table-row guess).
 * The parent has since taken over the cart page: lafka-theme/styles/cart-item.css
 * reserves each rendered `.lafka-cart-item` row via a fixed 80x80
 * `.lafka-cart-item__img-wrap`, and lafka-cart-handoff.css owns the container
 * grid + summary card. The child's 60vh magic number over-reserved the
 * container (forcing >=60vh of height even with one item) and the 80px row
 * reservation targeted WC's legacy `.woocommerce-cart-form__cart-item` table
 * row that cart.php no longer emits.
 *
 * If a future commit re-introduces either rule into the child stylesheet,
 * this test fails — pushing any genuine cart CLS work back into the parent,
 * keyed to the actual rendered selectors rather than a container magic number.
 */
final class CartClsReservationTest extends TestCase {

	/**
	 * Active CSS rules in the child stylesheet, with block comments stripped.
	 *
	 * The header invites pasting commented-out override EXAMPLES and documents
	 * past migrations by NAMING moved selectors in prose, so only ACTIVE rules
	 * are inspected (mirrors ThinLayerTest::test_style_css_holds_no_parent_owned_features).
	 */
	private static function active_style_css(): string {
		$raw = file_get_contents( dirname( __DIR__, 2 ) . '/style.css' );

		return (string) preg_replace( '#/\*.*?\*/#s', '', $raw );
	}

	/**
	 * Stale cart-page reservation fragments the parent now owns. An active
	 * occurrence of any of these is SSOT drift back into the child.
	 *
	 * @return array<string, array{0: string}>
	 */
	public static function provide_stale_cart_reservation_fragments(): array {
		return array(
			'60vh container magic number' => array( 'min-height: 60vh' ),
			'dead WC table-row class'     => array( '.woocommerce-cart-form__cart-item' ),
		);
	}

	#[DataProvider('provide_stale_cart_reservation_fragments')]
	public function test_child_style_css_drops_stale_cart_cls_reservation( string $fragment ): void {
		$this->assertStringNotContainsString(
			$fragment,
			self::active_style_css(),
			"lafka-child/style.css re-introduced the stale cart CLS reservation '{$fragment}'. "
				. 'The /cart/ page is parent-owned: row height is reserved by the fixed 80x80 '
				. '.lafka-cart-item__img-wrap in lafka-theme/styles/cart-item.css. Any genuine '
				. 'cart CLS work belongs in the parent, keyed to .lafka-cart-item — never a '
				. '60vh container magic number in the child.'
		);
	}

	/**
	 * The child must not re-assert a min-height on the cart container itself
	 * (`.woocommerce-cart .woocommerce`); the parent's cart-handoff.css owns
	 * that container as a grid with no over-reserving min-height.
	 */
	public function test_child_style_css_does_not_override_cart_container_min_height(): void {
		$active = self::active_style_css();

		$this->assertSame(
			0,
			preg_match( '/\.woocommerce-cart\s+\.woocommerce\s*\{[^}]*min-height[^}]*\}/s', $active ),
			'lafka-child/style.css sets a min-height on the parent-owned cart container '
				. '`.woocommerce-cart .woocommerce`. That container is owned by '
				. 'lafka-theme/styles/lafka-cart-handoff.css; remove the override from the child.'
		);
	}
}
