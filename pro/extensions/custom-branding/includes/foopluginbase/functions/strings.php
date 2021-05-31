<?php
/*
 * Foo Functions - Strings
 * A bunch of common and useful functions related to strings
 *
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !function_exists( 'foo_convert_to_key' ) ) {
	function foo_convert_to_key($input) {
		return str_replace( " ", "_", strtolower( $input ) );
	}
}

if ( !function_exists( 'foo_title_case' ) ) {
	function foo_title_case($input) {
		return ucwords( str_replace( array("-", "_"), " ", $input ) );
	}
}

if ( !function_exists( 'foo_contains' ) ) {
	/*
	* returns true if a needle can be found in a haystack
	*/
	function foo_contains($haystack, $needle) {
		if ( empty($haystack) || empty($needle) ) {
			return false;
		}

		$pos = strpos( strtolower( $haystack ), strtolower( $needle ) );

		if ( $pos === false ) {
			return false;
		} else {
			return true;
		}
	}
}

if ( !function_exists( 'foo_starts_with' ) ) {
	/**
	 * starts_with
	 * Tests if a text starts with an given string.
	 *
	 * @param     string
	 * @param     string
	 *
	 * @return    bool
	 */
	function foo_starts_with($haystack, $needle) {
		return strpos( $haystack, $needle ) === 0;
	}
}

if ( !function_exists( 'foo_ends_with' ) ) {
	function foo_ends_with($haystack, $needle, $case = true) {
		$expectedPosition = strlen( $haystack ) - strlen( $needle );

		if ( $case ) {
			return strrpos( $haystack, $needle, 0 ) === $expectedPosition;
		}

		return strripos( $haystack, $needle, 0 ) === $expectedPosition;
	}
}