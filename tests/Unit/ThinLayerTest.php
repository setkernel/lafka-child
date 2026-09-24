<?php
declare(strict_types=1);

namespace LafkaChild\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * lafka-child must remain a thin override layer: a small functions.php and a
 * style.css that carries only per-install overrides. Product features belong
 * in lafka-theme (appearance) or lafka-plugin (behavior).
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

	/**
	 * style.css is the other place stranded product features accumulate:
	 * default-feature CSS could (and did) drift into the child unnoticed. The child stylesheet must
	 * carry only per-install overrides; styling for markup the PARENT theme or
	 * the lafka-plugin emit belongs in lafka-theme/styles/lafka-base.css (the
	 * SSOT), tokenised from --lafka-*. Mirrors the v6.0.6 carousel/a11y and
	 * f022 grouped-menu moves into the parent.
	 *
	 * Comments are stripped first so commented-out override examples pasted
	 * from examples/style-overrides.css.example are never flagged — only
	 * ACTIVE rules are inspected.
	 */
	public function test_style_css_holds_no_parent_owned_features(): void {
		$raw = file_get_contents( dirname( __DIR__, 2 ) . '/style.css' );

		// Drop CSS block comments — inspect ACTIVE rules only.
		$active = preg_replace( '#/\*.*?\*/#s', '', $raw );

		// (a) Parent/plugin-emitted feature selectors the child must never own
		// outright. The grouped mobile menu markup comes from the lafka-plugin
		// walker (styled in the parent baseline since f022); the .single-product
		// .product .cart sticky form is superseded by the parent's tokenised
		// .lafka-sticky-cart bar. An active copy of either here is silent SSOT
		// drift the parent already owns.
		foreach ( array(
			'.lafka-mobile-menu-group',
			'.lafka-mobile-menu-group-label',
			'.lafka-mobile-menu-group-items',
			'.single-product .product .cart',
		) as $selector ) {
			$this->assertStringNotContainsString(
				$selector,
				$active,
				"lafka-child/style.css owns the parent feature selector '{$selector}'. "
					. 'Default-feature styling belongs in lafka-theme/styles/lafka-base.css '
					. '(the parent/plugin emits the markup); keep only per-install overrides here.'
			);
		}

		// (b) No hardcoded #hex colours in active default-feature rules. Genuine
		// per-install colour overrides live in an uncommented :root { --lafka-*:
		// ... } block (the override mechanism the style.css header invites), so
		// those are exempted; a raw hex anywhere else is a stranded default that
		// should be tokenised from --lafka-* in the parent baseline.
		$without_root = preg_replace( '/:root\s*\{[^}]*\}/s', '', $active );
		$found        = preg_match_all( '/:\s*[^;{}]*?(#[0-9a-fA-F]{3,8})\b/', $without_root, $m );
		$this->assertSame(
			0,
			$found,
			'lafka-child/style.css carries hardcoded hex colour(s) in active default-feature rules: '
				. implode( ', ', array_unique( $m[1] ) ) . '. '
				. 'Tokenise them from --lafka-* in lafka-theme/styles/lafka-base.css; '
				. 'put per-install colour overrides in an uncommented :root { --lafka-*: ... } block.'
		);
	}
}
