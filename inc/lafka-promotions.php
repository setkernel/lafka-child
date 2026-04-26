<?php
/**
 * Lafka child theme — pure pricing / promotion helpers.
 *
 * Extracted from functions.php so the math can be unit-tested without booting
 * WordPress or WooCommerce. Behavior is identical to the in-place implementations
 * these helpers replace; if you change one, change both.
 *
 * Slated for migration to lafka-plugin/incl/promotions/ under P2-01. Keeping
 * the helpers pure (no WP / WC calls) makes that migration a copy-paste with a
 * namespace change rather than a rewrite.
 *
 * @package Lafka\Child
 * @since   5.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Delivery-minimum threshold (cart subtotal in store currency). Below this,
 * `lafka_child_should_block_delivery()` returns true and only local pickup
 * shipping methods survive in `woocommerce_package_rates`.
 */
if ( ! defined( 'LAFKA_CHILD_DELIVERY_MINIMUM' ) ) {
	define( 'LAFKA_CHILD_DELIVERY_MINIMUM', 30 );
}

/**
 * Distribute the BOGO-50% discount across line-item keys.
 *
 * Floor(total_units / 2) cheapest individual units get the 50%-off discount.
 * Sort order is determined inside this helper, so callers may pass units in
 * any order.
 *
 * @param array $units Array of ['key' => string, 'price' => float|int]. Each
 *                     element is one physical unit; a line item with quantity 3
 *                     contributes 3 elements with the same key.
 * @return array<string,int>  Map of cart-item key => count of units in that
 *                            line item to discount. Keys with zero units are
 *                            omitted from the result.
 */
function lafka_bogo_distribute_discounts( array $units ) {
	$total = count( $units );
	if ( $total < 2 ) {
		return array();
	}

	usort(
		$units,
		static fn( $a, $b ) => $a['price'] <=> $b['price']
	);

	$discount_count = (int) floor( $total / 2 );
	$distribution   = array();

	for ( $i = 0; $i < $discount_count; $i++ ) {
		$k = $units[ $i ]['key'];
		if ( ! isset( $distribution[ $k ] ) ) {
			$distribution[ $k ] = 0;
		}
		++$distribution[ $k ];
	}

	return $distribution;
}

/**
 * Compute the blended per-unit price for a line item with `$disc_qty` of its
 * `$qty` units at 50% off.
 *
 * @param float|int $orig     Original unit price.
 * @param int       $qty      Total units in the line item.
 * @param int       $disc_qty Units to discount at 50% off (0 ≤ disc_qty ≤ qty).
 * @return float Blended unit price (returns $orig unchanged when no discount applies).
 */
function lafka_bogo_blended_price( $orig, $qty, $disc_qty ) {
	$orig     = (float) $orig;
	$qty      = (int) $qty;
	$disc_qty = (int) $disc_qty;

	if ( $qty <= 0 || $disc_qty <= 0 ) {
		return $orig;
	}

	$full_units = $qty - $disc_qty;
	return ( $full_units * $orig + $disc_qty * $orig * 0.5 ) / $qty;
}

/**
 * Whether the cart's package contents are below the delivery minimum.
 *
 * Boundary semantics (locked in by tests): `< LAFKA_CHILD_DELIVERY_MINIMUM` —
 * exactly at the threshold ALLOWS delivery.
 *
 * @param float|int $contents_cost WooCommerce package `contents_cost` value.
 * @return bool true → hide all non-pickup shipping rates.
 */
function lafka_child_should_block_delivery( $contents_cost ) {
	return (float) $contents_cost < (float) LAFKA_CHILD_DELIVERY_MINIMUM;
}
