<?php
/**
 * Regression guard for audit f057.
 *
 * Active CSS rules in the child theme's style.css must reference the parent's
 * --lafka-* token set instead of literal hex colors, so theme-wide recoloring
 * (Customizer / :root overrides / auto dark-mode) keeps working and no brand
 * accent literal is baked in (no-hardcoded-site-values convention).
 *
 * Commented-out documentation examples (e.g. the ":root brand override"
 * snippet that literally tells the operator "put your brand color here") are
 * stripped before the broad assertion, so those examples stay literal on
 * purpose — only *active* rules are required to be tokenized.
 */

declare(strict_types=1);

namespace Lafka\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HardcodedColorTokenTest extends TestCase {

	private const STYLE_PATH = __DIR__ . '/../../style.css';

	private static function raw_css(): string {
		$css = file_get_contents( self::STYLE_PATH );
		self::assertNotFalse( $css, 'style.css unreadable' );

		return $css;
	}

	/**
	 * style.css with block comments removed and var(...) calls (one level of
	 * nesting) stripped, so a legitimate var(--token, #fff) fallback is not
	 * mistaken for a bare literal. What remains is the active, non-fallback CSS.
	 */
	private static function active_css(): string {
		// CSS has no line comments — only block comments.
		$css = (string) preg_replace( '#/\*.*?\*/#s', '', self::raw_css() );

		return (string) preg_replace( '#var\((?:[^()]|\([^()]*\))*\)#', '', $css );
	}

	public function test_no_bare_hex_color_in_active_rules(): void {
		self::assertDoesNotMatchRegularExpression(
			'/#[0-9a-fA-F]{3,8}\b/',
			self::active_css(),
			'Active CSS rules must use --lafka-* tokens, not literal hex colors. '
				. 'A hex value may only appear as a var() fallback (audit f057).'
		);
	}

	public function test_sticky_cart_background_uses_surface_token(): void {
		self::assertStringContainsString(
			'var(--lafka-color-surface-page)',
			self::raw_css(),
			'Mobile sticky Add-to-Cart background must reference '
				. '--lafka-color-surface-page, not #fff (audit f057).'
		);
	}

	public function test_dark_surface_uses_text_primary_token(): void {
		self::assertStringContainsString(
			'var(--lafka-color-text-primary)',
			self::raw_css(),
			'The dark footer surface must reference --lafka-color-text-primary, '
				. 'not #1a1a1a (audit f057).'
		);
	}

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
		self::assertStringNotContainsStringIgnoringCase(
			$hex,
			self::raw_css(),
			"Literal {$hex} must use its --lafka-* token (audit f057), not a "
				. 'hardcoded value — even inside documentation examples.'
		);
	}
}
