<?php
declare(strict_types=1);

namespace LafkaChild\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Regression lock for f040: the child must never ship an empty RTL
 * stylesheet.
 *
 * styles/rtl.css used to contain only the 4-line theme header (zero CSS
 * rules) yet was enqueued as 'lafka-child-rtl' whenever is_rtl() was true,
 * costing RTL visitors an extra request for no effect. The parent theme's
 * 'lafka-rtl' already provides RTL base styling.
 *
 * If a future commit re-introduces a child RTL override, it may only do so
 * with a file that actually contains CSS rules — otherwise this test fails.
 */
final class RtlEnqueueTest extends TestCase {

	private function child_root(): string {
		return dirname( __DIR__, 2 );
	}

	public function test_no_empty_rtl_stub_is_shipped(): void {
		$rtl = $this->child_root() . '/styles/rtl.css';

		if ( ! file_exists( $rtl ) ) {
			$this->assertFileDoesNotExist( $rtl, 'No child RTL stylesheet — parent lafka-rtl covers RTL base styling.' );
			return;
		}

		// If a child RTL override is re-introduced, it must contain real rules.
		$css = file_get_contents( $rtl );
		$this->assertStringContainsString(
			'{',
			$css,
			'styles/rtl.css exists but contains no CSS rule — do not ship an empty RTL stub; the parent lafka-rtl already covers RTL base styling.'
		);
	}

	public function test_functions_does_not_enqueue_empty_child_rtl(): void {
		$src = file_get_contents( $this->child_root() . '/functions.php' );
		$rtl = $this->child_root() . '/styles/rtl.css';

		$has_real_rtl = file_exists( $rtl ) && str_contains( (string) file_get_contents( $rtl ), '{' );

		if ( ! $has_real_rtl ) {
			$this->assertStringNotContainsString(
				'lafka-child-rtl',
				$src,
				"functions.php enqueues 'lafka-child-rtl' but there is no child RTL stylesheet with real CSS rules to back it."
			);
		} else {
			$this->assertStringContainsString( 'lafka-child-rtl', $src );
		}
	}
}
