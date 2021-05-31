<?php
/*
 * Foo Functions - Arrays
 * A bunch of common and useful functions related to arrays
 *
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !function_exists( 'foo_safe_get' ) ) {
	/**
	 * safely get a value from an array
	 *
	 * @param array  $array   The array we want to extract info from.
	 * @param string $key     The key of the item within the array.
	 * @param mixed  $default The default value toi return if the value is not found in the array.
	 *
	 * @return mixed
	 */
	function foo_safe_get($array, $key, $default = null) {
		if ( !is_array( $array ) ) return $default;
		$value = array_key_exists( $key, $array ) ? $array[$key] : null;
		if ( $value === null ) {
			return $default;
		}

		return $value;
	}
}

if ( !function_exists( 'safe_get_from_request' ) ) {

	/**
	 * Safely get a value from the global $_REQUEST array
	 *
	 * @param string $key     The key of the item within the array.
	 * @param mixed  $default The default value toi return if the value is not found in the array.
	 *
	 * @return mixed
	 */
	function safe_get_from_request($key, $default = null) {
		return foo_safe_get( $_REQUEST, $key, $default );
	}
}