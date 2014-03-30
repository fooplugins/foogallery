<?php
/**
 * FooGallery_Main class
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 */


/*
 * TODO
 *
 * Add Gallery column to attachments in media gallery ('add to gallery' link directly from here)
 * Check out post attachments plugin to see how the images are shown for a post
 *
 */

if ( !class_exists( 'FooGallery_Main' ) ) {

	require_once( FOOGALLERY_PATH . 'includes/foopluginbase/bootstrapper.php' );

	/**
	 * FooGallery class.
	 *
	 * @package FooGallery
	 * @author  Brad Vincent <brad@fooplugins.com>
	 */
	class FooGallery_Main extends Foo_Plugin_Base_v2_0 {

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof FooGallery_Main ) ) {
				self::$instance = new FooGallery_Main();
			}
			return self::$instance;
		}

		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 */
		private function __construct() {

			//include everything we need!
			require_once( FOOGALLERY_PATH . 'includes/includes.php' );

			//init FooPluginBase
			$this->init( FOOGALLERY_FILE, FOOGALLERY_SLUG, FOOGALLERY_VERSION, 'FooGallery' );

			//setup text domain
			new FooGallery_TextDomain();

			//setup post types and taxonomies
			new FooGallery_PostTypes_Taxonomies();

			if (is_admin()) {
				new FooGallery_Admin();
			} else {
				new FooGallery_Public();
			}
		}
	}
}