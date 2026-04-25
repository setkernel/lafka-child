<?php
/**
 * Behavior lock for child-theme BOGO + delivery-min math (P2-03a).
 *
 * Pure-helper tests — no WP/WC runtime. Locks in the exact semantics so the
 * P2-01 child→plugin migration can refactor freely without changing math.
 */

declare(strict_types=1);

namespace Lafka\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/inc/lafka-promotions.php';

final class LafkaPromotionsTest extends TestCase {

	// ─── BOGO distribute_discounts — quantity edge cases ────────────────────

	public function test_bogo_zero_units_returns_no_discounts(): void {
		self::assertSame( array(), lafka_bogo_distribute_discounts( array() ) );
	}

	public function test_bogo_one_unit_returns_no_discounts(): void {
		$units = array( array( 'key' => 'A', 'price' => 10.0 ) );
		self::assertSame( array(), lafka_bogo_distribute_discounts( $units ) );
	}

	public function test_bogo_two_units_one_discounted_on_cheaper(): void {
		$units = array(
			array( 'key' => 'B', 'price' => 10.0 ),
			array( 'key' => 'A', 'price' => 5.0 ),
		);
		self::assertSame( array( 'A' => 1 ), lafka_bogo_distribute_discounts( $units ) );
	}

	public function test_bogo_three_units_one_discounted_floor_div_two(): void {
		$units = array(
			array( 'key' => 'A', 'price' => 3.0 ),
			array( 'key' => 'B', 'price' => 5.0 ),
			array( 'key' => 'C', 'price' => 10.0 ),
		);
		self::assertSame( array( 'A' => 1 ), lafka_bogo_distribute_discounts( $units ) );
	}

	public function test_bogo_four_units_two_discounted_on_cheapest_pair(): void {
		$units = array(
			array( 'key' => 'A', 'price' => 3.0 ),
			array( 'key' => 'A', 'price' => 3.0 ),
			array( 'key' => 'B', 'price' => 5.0 ),
			array( 'key' => 'C', 'price' => 10.0 ),
		);
		self::assertSame( array( 'A' => 2 ), lafka_bogo_distribute_discounts( $units ) );
	}

	public function test_bogo_five_units_two_discounted_odd_extra_pays_full(): void {
		$units = array(
			array( 'key' => 'A', 'price' => 5.0 ),
			array( 'key' => 'A', 'price' => 5.0 ),
			array( 'key' => 'A', 'price' => 5.0 ),
			array( 'key' => 'A', 'price' => 5.0 ),
			array( 'key' => 'A', 'price' => 5.0 ),
		);
		self::assertSame( array( 'A' => 2 ), lafka_bogo_distribute_discounts( $units ) );
	}

	public function test_bogo_six_units_three_discounted_across_keys(): void {
		$units = array(
			array( 'key' => 'A', 'price' => 3.0 ),
			array( 'key' => 'A', 'price' => 3.0 ),
			array( 'key' => 'B', 'price' => 5.0 ),
			array( 'key' => 'B', 'price' => 5.0 ),
			array( 'key' => 'C', 'price' => 10.0 ),
			array( 'key' => 'C', 'price' => 10.0 ),
		);
		self::assertSame( array( 'A' => 2, 'B' => 1 ), lafka_bogo_distribute_discounts( $units ) );
	}

	public function test_bogo_input_order_does_not_matter(): void {
		$shuffled = array(
			array( 'key' => 'C', 'price' => 10.0 ),
			array( 'key' => 'A', 'price' => 3.0 ),
			array( 'key' => 'B', 'price' => 5.0 ),
			array( 'key' => 'A', 'price' => 3.0 ),
		);
		// 4 units → discount cheapest 2 (both A's).
		self::assertSame( array( 'A' => 2 ), lafka_bogo_distribute_discounts( $shuffled ) );
	}

	// ─── BOGO blended_price — math correctness ──────────────────────────────

	public function test_blended_price_returns_orig_when_zero_discounted(): void {
		self::assertSame( 10.0, lafka_bogo_blended_price( 10.0, 3, 0 ) );
	}

	public function test_blended_price_two_units_one_discounted_is_75_percent(): void {
		// 1 full @ $10 + 1 half @ $5 = $15 / 2 = $7.50/unit
		self::assertSame( 7.5, lafka_bogo_blended_price( 10.0, 2, 1 ) );
	}

	public function test_blended_price_four_units_two_discounted_is_75_percent(): void {
		// 2 full @ $10 + 2 half @ $5 = $30 / 4 = $7.50/unit
		self::assertSame( 7.5, lafka_bogo_blended_price( 10.0, 4, 2 ) );
	}

	public function test_blended_price_returns_orig_when_qty_zero(): void {
		self::assertSame( 10.0, lafka_bogo_blended_price( 10.0, 0, 1 ) );
	}

	// ─── Delivery-minimum boundary semantics (locked in by spec) ────────────

	public function test_delivery_blocked_just_below_threshold(): void {
		self::assertTrue( lafka_child_should_block_delivery( 29.99 ) );
	}

	public function test_delivery_allowed_exactly_at_threshold(): void {
		// Boundary is `<` not `<=`: $30.00 EXACTLY ALLOWS delivery.
		self::assertFalse( lafka_child_should_block_delivery( 30.00 ) );
	}

	public function test_delivery_allowed_above_threshold(): void {
		self::assertFalse( lafka_child_should_block_delivery( 30.01 ) );
	}

	public function test_delivery_blocked_for_zero_cart(): void {
		self::assertTrue( lafka_child_should_block_delivery( 0.0 ) );
	}
}
