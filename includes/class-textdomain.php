<?php
/*
 * FooGallery Text Domain Setup
 */

if ( !class_exists( 'FooGallery_TextDomain' ) ) {

	class FooGallery_TextDomain {

		function __construct() {
			$this->load_textdomain();
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since  1.0
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( FOOGALLERY_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'aff_wp_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale  = apply_filters( 'plugin_locale', get_locale(), 'foogallery' );
			$mo_file = sprintf( '%1$s-%2$s.mo', 'foogallery', $locale );

			// Setup paths to current locale file
			$mo_file_local  = $lang_dir . $mo_file;
			$mo_file_global = WP_LANG_DIR . '/foogallery/' . $mo_file;

			if ( file_exists( $mo_file_global ) ) {
				// Look in global /wp-content/languages/foogallery/ folder
				load_textdomain( 'foogallery', $mo_file_global );
			} elseif ( file_exists( $mo_file_local ) ) {
				// Look in local /wp-content/plugins/foogallery/languages/ folder
				load_textdomain( 'foogallery', $mo_file_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'foogallery', false, $lang_dir );
			}
		}
	}
}