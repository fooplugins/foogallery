<?php
/*
 * Foo Functions - General
 * A bunch of common and useful functions that don't fall into any specific category
 *
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !function_exists( 'foo_check_php_version' ) ) {

	/**
	 * Checks the version of PHP running on the server.
	 *
	 * @param string $plugin_title The title of the plugin that is doing the check.
	 * @param string $ver          The minimum required version
	 *
	 * @throws Exception if the version does not meet minimum requirements
	 */
	function foo_check_php_version($plugin_title, $ver) {
		$php_version = phpversion();
		if ( version_compare( $php_version, $ver ) < 0 ) {
			throw new Exception( "$plugin_title requires at least version $ver of PHP. You are running an older version ($php_version). Please update!" );
		}
	}
}

if ( !function_exists( 'foo_check_wp_version' ) ) {

	/**
	 * Checks the version of WordPress running on the server.
	 *
	 * @param string $plugin_title The title of the plugin that is doing the check.
	 * @param string $ver          The minimum required version
	 *
	 * @throws Exception if the version does not meet minimum requirements
	 */
	function foo_check_wp_version($plugin_title, $ver) {
		global $wp_version;
		if ( version_compare( $wp_version, $ver ) < 0 ) {
			throw new Exception( "$plugin_title requires at least version $ver of WordPress. You are running an older version ($wp_version). Please update!" );
		}
	}
}

if ( !function_exists( 'foo_check_wp_version_at_least' ) ) {

	/**
	 * @TODO
	 * @param $ver
	 *
	 * @return bool
	 */
	function foo_check_wp_version_at_least($ver) {
		global $wp_version;

		return version_compare( $wp_version, $ver ) >= 0;
	}
}
