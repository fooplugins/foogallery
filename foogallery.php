<?php
/**
 * FooGallery
 *
 * The Most Intuitive and Extensible Gallery Creation and Management Tool Ever Created for WordPress.
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
 * Version:     1.2.0
 * Author:      FooPlugins
 * Author URI:  http://fooplugins.com
 * Text Domain: foogallery
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FOOGALLERY_SLUG', 'foogallery' );
define( 'FOOGALLERY_PATH', plugin_dir_path( __FILE__ ) );
define( 'FOOGALLERY_URL', plugin_dir_url( __FILE__ ) );
define( 'FOOGALLERY_FILE', __FILE__ );
define( 'FOOGALLERY_VERSION', '1.2.0' );

/**
 * FooGallery_Plugin class
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2013 FooPlugins LLC
 */

if ( ! class_exists( 'FooGallery_Plugin' ) ) {

	require_once( FOOGALLERY_PATH . 'includes/foopluginbase/bootstrapper.php' );

	/**
	 * FooGallery_Plugin class.
	 *
	 * @package FooGallery
	 * @author  Brad Vincent <brad@fooplugins.com>
	 */
	class FooGallery_Plugin extends Foo_Plugin_Base_v2_3 {

		private static $instance;

		public static function get_instance() {
			if ( ! isset(self::$instance) && ! (self::$instance instanceof FooGallery_Plugin) ) {
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

			register_activation_hook( __FILE__, array( 'FooGallery_Plugin', 'activate' ) );

			//init FooPluginBase
			$this->init( FOOGALLERY_FILE, FOOGALLERY_SLUG, FOOGALLERY_VERSION, 'FooGallery' );

			//setup text domain
			$this->load_plugin_textdomain();

			//setup gallery post type
			new FooGallery_PostTypes();

			//load any extensions
			new FooGallery_Extensions_Loader();

			if ( is_admin() ) {
				new FooGallery_Admin();
			} else {
				new FooGallery_Public();
			}

			new FooGallery_Thumbnails();
		}

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses
		 *                                       "Network Activate" action, false if
		 *                                       WPMU is disabled or plugin is
		 *                                       activated on an individual blog.
		 */
		public static function activate( $network_wide ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide  ) {

					// Get all blog ids
					$blog_ids = self::get_blog_ids();
					if ( is_array( $blog_ids ) ) {
						foreach ( $blog_ids as $blog_id ) {

							switch_to_blog( $blog_id );
							self::single_activate();
						}

						restore_current_blog();
					}

				} else {
					self::single_activate();
				}

			} else {
				self::single_activate( false );
			}
		}

		/**
		 * Fired for each blog when the plugin is activated.
		 *
		 * @since    1.0.0
		 */
		private static function single_activate( $multisite = true ) {
			if ( false === get_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, false ) ) {
				$api = new FooGallery_Extensions_API();

				$api->auto_activate_extensions();

				update_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, true );

			}
			if ( false === $multisite ) {
				//Make sure we redirect to the welcome page
				set_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY, true, 30 );
			}
		}

		/**
		 * Get all blog ids of blogs in the current network that are:
		 * - not archived
		 * - not spam
		 * - not deleted
		 *
		 * @since    1.0.0
		 *
		 * @return   array|false    The blog ids, false if no matches.
		 */
		private static function get_blog_ids() {

			if ( function_exists( 'wp_get_sites' ) ) {

				$sites = wp_get_sites();
				$blog_ids = array();
				foreach ( $sites as $site ) {
					$blog_ids[] = $site['blog_id'];
				}
				return $blog_ids;
			} else {
				//pre WP 3.7 - do this the old way!
				global $wpdb;

				// get an array of blog ids
				$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";

				return $wpdb->get_col( $sql );
			}
		}
	}
}

FooGallery_Plugin::get_instance();
