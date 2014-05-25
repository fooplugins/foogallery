<?php
/*
 * Foo Functions - Dates
 * A bunch of common and useful functions related to dates
 *
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !function_exists( 'foo_friendly_date' ) ) {

	/**
	 * Return a friendly date compared to the current date
	 *
	 * @param $timestamp	string|int 	The timestamp we want return a friendly date for
	 *
	 * @return string		A friendly date
	 */
	function foo_friendly_date( $timestamp ) {
		$instance = new Foo_Friendly_Dates_v1();
		return $instance->friendly_date( $timestamp );
	}
}
