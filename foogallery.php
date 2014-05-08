<?php
/**
 * FooGallery
 *
 * The goal of FooGallery is simple : To provide an easy-to-use and intuitive image gallery management solution.
 * Also, the plugin must utilize as much WordPress core functionality as possible!
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 *
 * @wordpress-plugin
 * Plugin Name: FooGallery
 * Plugin URI:  https://github.com/fooplugins/foogallery
 * Description: Better Image Galleries for WordPress
 * Version:     1.0.1
 * Author:      bradvin
 * Author URI:  http://fooplugins.com
 * Text Domain: foogallery
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

/*
 * TODO
 *
 * Add Gallery column to attachments in media gallery ('add to gallery' link directly from here)
 * Check out post attachments plugin to see how the images are shown for a post
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FOOGALLERY_SLUG', 'foogallery' );
define( 'FOOGALLERY_PATH', plugin_dir_path( __FILE__ ));
define( 'FOOGALLERY_URL', plugin_dir_url( __FILE__ ));
define( 'FOOGALLERY_FILE', __FILE__ );
define( 'FOOGALLERY_VERSION', '1.0.1' );

/**
 * FooGallery_Plugin class
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 */


if ( !class_exists( 'FooGallery_Plugin' ) ) {

	require_once( FOOGALLERY_PATH . 'includes/foopluginbase/bootstrapper.php' );

	/**
	 * FooGallery_Plugin class.
	 *
	 * @package FooGallery
	 * @author  Brad Vincent <brad@fooplugins.com>
	 */
	class FooGallery_Plugin extends Foo_Plugin_Base_v2_1 {

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof FooGallery_Plugin ) ) {
				self::$instance = new FooGallery_Plugin();
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
			$this->load_plugin_textdomain();

			//setup gallery post type
			new FooGallery_PostTypes();

			if (is_admin()) {
				new FooGallery_Admin();
			} else {
				new FooGallery_Public();
			}
		}
	}
}

FooGallery_Plugin::get_instance();