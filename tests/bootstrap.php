<?php
/**
 * PHPUnit bootstrap for the Lafka child theme test harness.
 *
 * Pure unit tests only — no WordPress runtime. Child-theme functions reached
 * from tests must be mocked or invoked via shims declared in the test file.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
