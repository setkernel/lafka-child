<?php
/**
 * Smoke test that locks in the child-theme style.css header block.
 *
 * The `Template:` line is the critical one — without it WordPress refuses to
 * activate the child theme. Catching that in CI is cheaper than catching it
 * after a release tag.
 */

declare(strict_types=1);

namespace Lafka\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class StyleHeaderTest extends TestCase {

	private const STYLE_PATH = __DIR__ . '/../../style.css';

	public function test_style_css_exists(): void {
		self::assertFileExists( self::STYLE_PATH );
	}

	public function test_required_headers_are_present(): void {
		$header_block = $this->read_header_block();

		self::assertMatchesRegularExpression( '/Theme Name:\s*Lafka Child\s*$/m', $header_block );
		self::assertMatchesRegularExpression( '/Version:\s*\d+\.\d+\.\d+\s*$/m', $header_block );
		self::assertMatchesRegularExpression( '/Template:\s*lafka\s*$/m', $header_block );
		self::assertMatchesRegularExpression( '/Text Domain:\s*lafka\s*$/m', $header_block );
	}

	private function read_header_block(): string {
		$contents = file_get_contents( self::STYLE_PATH );
		self::assertNotFalse( $contents, 'style.css unreadable' );

		return substr( $contents, 0, 8192 );
	}
}
