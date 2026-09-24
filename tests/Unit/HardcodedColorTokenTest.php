<?php
/**
 * Regression guard for audit f057.
 *
 * Literals that were tokenised out of the child stylesheet must never come
 * back — not even inside comments. The broader "no bare hex in active rules"
 * rule (with the sanctioned :root { --lafka-*: … } override exemption) lives in
 * ThinLayerTest::test_style_css_holds_no_parent_owned_features.
 */

declare(strict_types=1);

namespace Lafka\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HardcodedColorTokenTest extends TestCase {

	private const STYLE_PATH = __DIR__ . '/../../style.css';

	/**
	 * @return array<string, array{string}>
	 */
	public static function forbidden_literals(): array {
		return array(
			'footer dark bg (#1a1a1a -> --lafka-color-text-primary)' => array( '#1a1a1a' ),
			'brand accent literal (#e4584b -> --lafka-color-accent-text)' => array( '#e4584b' ),
			'eyebrow label (#5e5e5e -> --lafka-color-text-muted)' => array( '#5e5e5e' ),
		);
	}

	#[DataProvider( 'forbidden_literals' )]
	public function test_tokenized_literals_are_not_reintroduced( string $hex ): void {
		$css = file_get_contents( self::STYLE_PATH );
		self::assertNotFalse( $css, 'style.css unreadable' );

		self::assertStringNotContainsStringIgnoringCase(
			$hex,
			$css,
			"Literal {$hex} must use its --lafka-* token (audit f057), not a "
				. 'hardcoded value — even inside documentation examples.'
		);
	}
}
