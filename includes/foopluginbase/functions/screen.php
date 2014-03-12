<?php
/*
 * Foo Functions - Screen
 * A bunch of common and useful functions related to WP_screen object
 *
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !function_exists( 'foo_current_screen_id' ) ) {
	function foo_current_screen_id() {
		$screen = get_current_screen();
		if ( empty($screen) ) return false;

		return $screen->id;
	}
}

if ( !function_exists( 'foo_current_screen_base' ) ) {
	function foo_current_screen_base() {
		$screen = get_current_screen();
		if ( empty($screen) ) return false;

		return $screen->base;
	}
}

if ( !function_exists( 'foo_current_screen_post_type' ) ) {
	function foo_current_screen_post_type() {
		$screen = get_current_screen();
		if ( empty($screen) ) return false;

		return $screen->post_type;
	}
}

if ( !function_exists( 'foo_check_plugin_settings_page' ) ) {
	function foo_check_plugin_settings_page($plugin_slug) {
		return is_admin() && 'settings_page_' . $plugin_slug === foo_current_screen_id();
	}
}

if ( !function_exists( 'foo_current_url' ) ) {
// returns the current URL
	function foo_current_url() {
		global $wp;
		$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

		return $current_url;
	}
}