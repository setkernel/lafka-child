<?php
/**
 * Ratchet: dev-only files must never ship in the installable release zip.
 *
 * release.yml builds the distributable via `rsync -a --exclude=... ./ lafka-child/`.
 * A future dev-file class (a new tooling dir, a new config) could silently start
 * shipping to end users if that exclude list isn't kept in step. This test parses
 * the workflow's `--exclude=` patterns and asserts the known dev-file classes are
 * excluded — and, symmetrically, that the runtime files a child theme needs, plus
 * the examples/ customization reference that functions.php points operators to,
 * are NOT excluded. Scans the workflow as text; runs no rsync, needs no
 * node_modules, so it fires in `composer test` + the pre-push hook.
 *
 * NX1-10d.
 */

declare(strict_types=1);

namespace Lafka\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ReleasePackagingTest extends TestCase {

	private const ROOT = __DIR__ . '/../..';

	/**
	 * The rsync `--exclude='...'` patterns from the release workflow.
	 *
	 * @return list<string>
	 */
	private function release_excludes(): array {
		$yml = (string) file_get_contents( self::ROOT . '/.github/workflows/release.yml' );
		self::assertNotSame( '', $yml, 'release.yml is missing or empty' );
		self::assertStringContainsString( 'rsync -a ', $yml, 'release.yml has no rsync build step' );
		self::assertStringContainsString(
			'zip -r lafka-child.zip lafka-child/',
			$yml,
			'release.yml no longer zips the rsync destination'
		);

		$count = preg_match_all( "/--exclude='([^']*)'/", $yml, $m );
		self::assertGreaterThan( 0, $count, 'no rsync --exclude entries found in release.yml' );

		return $m[1];
	}

	public function test_dev_only_files_excluded_from_zip(): void {
		$excludes = $this->release_excludes();

		$dev = array(
			'.git',
			'.github',
			'.gitignore',
			'.githooks',
			'.npmrc',
			'.wp-env*.json',
			'node_modules',
			'vendor',
			'package.json',
			'package-lock.json',
			'composer.json',
			'composer.lock',
			'.phpcs.xml.dist',
			'.stylelintrc.json',
			'eslint.config.mjs',
			'phpunit.xml.dist',
			'.phpunit.result.cache',
			'tests',
			'scripts',
			'CONTRIBUTING.md',
		);

		foreach ( $dev as $needle ) {
			self::assertContains(
				$needle,
				$excludes,
				"release.yml must exclude dev-only '{$needle}' from the release zip"
			);
		}
	}

	public function test_runtime_files_not_excluded_from_zip(): void {
		$excludes = $this->release_excludes();

		// `examples` must stay: functions.php points operators to
		// examples/customizations.php.example as the customization reference.
		$runtime = array(
			'functions.php',
			'js',
			'style.css',
			'screenshot.png',
			'examples',
		);

		foreach ( $runtime as $needle ) {
			self::assertNotContains(
				$needle,
				$excludes,
				"release.yml must NOT exclude runtime path '{$needle}' from the release zip"
			);
		}
	}
}
