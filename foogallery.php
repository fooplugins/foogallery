<?php
/*
Plugin Name: FooGallery
Description: FooGallery is the most intuitive and extensible gallery management tool ever created for WordPress
Version:     1.4.18
Author:      FooPlugins
Plugin URI:  https://foo.gallery
Author URI:  http://fooplugins.com
Text Domain: foogallery
License:     GPL-2.0+
Domain Path: /languages

@fs_premium_only /pro/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'FooGallery_Plugin' ) ) {

	define( 'FOOGALLERY_SLUG', 'foogallery' );
	define( 'FOOGALLERY_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOGALLERY_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOGALLERY_FILE', __FILE__ );
	define( 'FOOGALLERY_VERSION', '1.4.18' );
	define( 'FOOGALLERY_SETTINGS_VERSION', '2' );

	require_once( FOOGALLERY_PATH . 'includes/constants.php' );

	// Create a helper function for easy SDK access.
	function foogallery_fs() {
		global $foogallery_fs;

		if ( ! isset( $foogallery_fs ) ) {
			// Include Freemius SDK.
			require_once dirname(__FILE__) . '/freemius/start.php';

			$foogallery_fs = fs_dynamic_init( array(
				'id'                => '843',
				'slug'              => 'foogallery',
				'type'              => 'plugin',
				'public_key'        => 'pk_d87616455a835af1d0658699d0192',
				'is_premium'        => true,
				'has_paid_plans'    => true,
				'trial'               => array(
					'days'               => 7,
					'is_require_payment' => false,
				),
				'menu'              => array(
					'slug'       => 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY,
					'first-path' => 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY . '&page=' . FOOGALLERY_ADMIN_MENU_HELP_SLUG,
					'account'    => true,
					'contact'    => false,
					'support'    => false,
				),
			) );
		}

		return $foogallery_fs;
	}

	// Init Freemius.
	foogallery_fs();

	// Signal that SDK was initiated.
	do_action( 'foogallery_fs_loaded' );


	require_once( FOOGALLERY_PATH . 'includes/foopluginbase/bootstrapper.php' );

	/**
	 * FooGallery_Plugin class
	 *
	 * @package   FooGallery
	 * @author    Brad Vincent <brad@fooplugins.com>
	 * @license   GPL-2.0+
	 * @link      https://github.com/fooplugins/foogallery
	 * @copyright 2013 FooPlugins LLC
	 */
	class FooGallery_Plugin extends Foo_Plugin_Base_v2_4 {

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
				add_action( 'wpmu_new_blog', array( $this, 'set_default_extensions_for_multisite_network_activated' ) );
				add_action( 'admin_page_access_denied', array( $this, 'check_for_access_denied' ) );
				foogallery_fs()->add_filter( 'connect_message_on_update', array( $this, 'override_connect_message_on_update' ), 10, 6 );
				foogallery_fs()->add_filter( 'is_submenu_visible', array( $this, 'is_submenu_visible' ), 10, 2 );
				foogallery_fs()->add_filter( 'hide_account_tabs', '__return_true' );
				add_action( 'foogallery_admin_menu_before', array( $this, 'add_freemius_activation_menu' ) );
			} else {
				new FooGallery_Public();
			}

			new FooGallery_Thumbnails();

			new FooGallery_Polylang_Compatibility();

			new FooGallery_Attachment_Filters();

			new FooGallery_Retina();

			new FooGallery_WPThumb_Enhancements();

			new FooGallery_Animated_Gif_Support();

			new FooGallery_Cache();

			new FooGallery_Common_Fields();

			new FooGallery_LazyLoad();

			new FooGallery_Paging();

			new FooGallery_Thumbnail_Dimensions();

			new FooGallery_FooBox_Support();

			new FooGallery_Responsive_Lightbox_dFactory_Support();

			new FooGallery_Attachment_Custom_Class();

			new FooGallery_Upgrade();

			new FooGallery_Extensions_Compatibility();

			new FooGallery_Default_Crop_Position();

			$checker = new FooGallery_Version_Check();
			$checker->wire_up_checker();

            new FooGallery_Widget_Init();

            //include the default templates no matter what!
            new FooGallery_Default_Templates();

			if ( foogallery_fs()->is__premium_only() ) {
				if ( foogallery_fs()->can_use_premium_code() ) {
					require_once FOOGALLERY_PATH . 'pro/foogallery-pro.php';

					new FooGallery_Pro();
				}
			} else {
				add_filter( 'foogallery_extensions_for_view', array( $this, 'add_foogallery_pro_extension' ) );
			}
		}

		function add_foogallery_pro_extension( $extensions ) {

			$extension = array(
				'slug' => 'foogallery-pro',
				'class' => 'FooGallery_Pro',
				'categories' => array( 'Featured', 'Premium' ),
				'title' => 'FooGallery Pro',
				'description' => 'The best gallery plugin for WordPress just got even better!',
				'price' => '$49',
				'author' => 'FooPlugins',
				'author_url' => 'http://fooplugins.com',
				'thumbnail' => 'https://s3.amazonaws.com/foogallery/extensions/foogallerypro.png',
				'tags' => array( 'premium' ),
				'source' => 'fooplugins',
				"download_button" => array(
					"text" => "Start FREE Trial",
					"target" => "_self",
					"href" => foogallery_fs()->checkout_url( WP_FS__PERIOD_ANNUALLY, true ),
					"confirm" => false
				)
			);

			array_unshift( $extensions, $extension );

			return $extensions;
		}

		/**
		 * Checks for the access denied page after we have activated/updated the plugin
		 */
		function check_for_access_denied() {
			global $plugin_page;

			if ( FOOGALLERY_ADMIN_MENU_HELP_SLUG === $plugin_page ||
				FOOGALLERY_ADMIN_MENU_SETTINGS_SLUG === $plugin_page ||
				FOOGALLERY_ADMIN_MENU_EXTENSIONS_SLUG === $plugin_page ||
				FOOGALLERY_ADMIN_MENU_SYSTEMINFO_SLUG === $plugin_page) {
				//fs_redirect( 'admin.php?page=' . FOOGALLERY_SLUG );
			}
		}

		/**
		 *
		 */
		function override_connect_message_on_update( $original, $first_name, $plugin_name, $login, $link, $freemius_link ) {

			return
				sprintf( __( 'Hey %s', 'foogallery' ), $first_name ) . '<br>' .
				sprintf(
					__( '<h2>Thank you for updating to %1$s v%5$s!</h2>Our goal with this update is to make %1$s the best gallery plugin for WordPress, but we need your help!<br><br>We have introduced this opt-in so that you can help us improve %1$s by simply clicking <strong>Allow &amp; Continue</strong>.<br><br>If you opt-in, some data about your usage of %1$s will be sent to %4$s. If you skip this, that\'s okay! %1$s will still work just fine.', 'foogallery' ),
					'<b>' . $plugin_name . '</b>',
					'<b>' . $login . '</b>',
					$link,
					$freemius_link,
					FOOGALLERY_VERSION
				);
		}

		function add_freemius_activation_menu() {
			global $foogallery_fs;

			$parent_slug = foogallery_admin_menu_parent_slug();

			if ( ! $foogallery_fs->is_registered() ) {
				add_submenu_page(
					$parent_slug,
					__( 'FooGallery Opt-In', 'foogallery' ),
					__( 'Activation', 'foogallery' ),
					'manage_options',
					'foogallery-optin',
					array( $foogallery_fs, '_connect_page_render' )
				);
			}
		}

		function is_submenu_visible( $visible, $id ) {
			if ( 'addons' === $id ) {
				//hide addons submenu for now
				$visible = false;
			}
			return $visible;
		}

		/**
		 * Set default extensions when a new site is created in multisite and FooGallery is network activated
		 *
		 * @since 1.2.5
		 *
		 * @param int $blog_id The ID of the newly created site
		 */
		public function set_default_extensions_for_multisite_network_activated( $blog_id ) {
			switch_to_blog( $blog_id );

			if ( false === get_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, false ) ) {
				$api = new FooGallery_Extensions_API();

				$api->auto_activate_extensions();

				update_option( FOOGALLERY_EXTENSIONS_AUTO_ACTIVATED_OPTIONS_KEY, true );
			}

			restore_current_blog();
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

			//force a version check on activation to make sure housekeeping is performed
			foogallery_perform_version_check();
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

			if ( function_exists( 'get_sites' ) ) {

				$sites = get_sites();
				$blog_ids = array();
                foreach ( $sites as $site ) {
                    $blog_ids[] = $site->blog_id;
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
