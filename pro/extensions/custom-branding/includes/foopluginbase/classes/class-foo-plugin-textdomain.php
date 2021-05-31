<?php
/*
 * Foo Plugin TextDomain Class
 *
 * A helpful class to handle loading plugin language files
 *
 * Version: 1.0
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !class_exists( 'Foo_Plugin_TextDomain_v1_0' ) ) {

	class Foo_Plugin_TextDomain_v1_0 {

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param string $plugin_file The main plugin file
		 * @param string $plugin_slug The plugin slug/folder name
		 * @param string $language_directory The plugin language directory relative to the main plugin file. Default directory is /languages/
		 *
		 * @return void
		 */
		public static function load_textdomain($plugin_file, $plugin_slug, $language_directory = '/languages/') {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( $plugin_file ) ) . $language_directory;
			$lang_dir = apply_filters( $plugin_slug . '_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale  = apply_filters( 'plugin_locale', get_locale(), $plugin_slug );
			$mo_file = sprintf( '%1$s-%2$s.mo', $plugin_slug, $locale );

			// Setup paths to current locale file
			$mo_file_local  = $lang_dir . $mo_file;
			$mo_file_global = WP_LANG_DIR . "/{$plugin_slug}/" . $mo_file;

			if ( file_exists( $mo_file_global ) ) {
				// Look in global /wp-content/languages/plugin-slug/ folder
				load_textdomain( $plugin_slug, $mo_file_global );
			} elseif ( file_exists( $mo_file_local ) ) {
				// Look in local /wp-content/plugins/plugin-slug/languages/ folder
				load_textdomain( $plugin_slug, $mo_file_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( $plugin_slug, false, $lang_dir );
			}
		}
	}
}