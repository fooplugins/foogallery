<?php
/**
 * PHPUnit bootstrap file for FooGallery.
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

$polyfills_path = dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
if ( file_exists( $polyfills_path ) ) {
	require_once $polyfills_path;
}

require_once $_tests_dir . '/includes/functions.php';

function foogallery_tests_load_plugin() {
	require dirname( __DIR__, 2 ) . '/foogallery.php';
}

tests_add_filter( 'muplugins_loaded', 'foogallery_tests_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
