<?php
/*
Plugin Name: FooGallery
Description: FooGallery is the most intuitive and extensible gallery management tool ever created for WordPress
Version:     3.1.10
Author:      FooPlugins
Plugin URI:  https://fooplugins.com/foogallery-wordpress-gallery-plugin/
Author URI:  https://fooplugins.com
Text Domain: foogallery
License:     GPL-2.0+
Domain Path: /languages
Requires PHP: 5.4

@fs_premium_only /pro/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( function_exists( 'foogallery_fs' ) ) {
	foogallery_fs()->set_basename( true, __FILE__ );
} else {

	if ( ! class_exists( 'FooGallery_Plugin' ) ) {

		define( 'FOOGALLERY_SLUG', 'foogallery' );
		define( 'FOOGALLERY_PATH', plugin_dir_path( __FILE__ ) );
		define( 'FOOGALLERY_URL', plugin_dir_url( __FILE__ ) );
		define( 'FOOGALLERY_FILE', __FILE__ );
		define( 'FOOGALLERY_VERSION', '3.1.10' );
		define( 'FOOGALLERY_SETTINGS_VERSION', '2' );

		require_once FOOGALLERY_PATH . 'includes/constants.php';
        require_once FOOGALLERY_PATH . 'includes/functions.php';

		// Create a helper function for easy SDK access.
		function foogallery_fs() {
			global $foogallery_fs;

			if ( ! isset( $foogallery_fs ) ) {
				// Include Freemius SDK.
				require_once dirname( __FILE__ ) . '/freemius/start.php';

				$foogallery_fs = fs_dynamic_init( array(
                    'id'             => '843',
                    'slug'           => 'foogallery',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_d87616455a835af1d0658699d0192',
                    'anonymous_mode' => foogallery_freemius_is_anonymous(),
                    'is_premium'     => true,
                    'has_paid_plans' => true,
                    'has_addons'     => true,
					'has_affiliation'=> 'selected',
                    'trial'          => array(
                        'days'               => 7,
                        'is_require_payment' => false,
                    ),
                    'menu'           => array(
                        'slug'       => 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY,
                        'first-path' => 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY . '&page=' . FOOGALLERY_ADMIN_MENU_HELP_SLUG,
                        'account'    => true,
                        'contact'    => false,
                        'support'    => false,
                    )
                ) );
			}

			return $foogallery_fs;
		}

		// Init Freemius.
		foogallery_fs();

		// Signal that SDK was initiated.
		do_action( 'foogallery_fs_loaded' );


		require_once FOOGALLERY_PATH . 'includes/foopluginbase/bootstrapper.php';

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
				if ( ! isset( self::$instance ) && ! ( self::$instance instanceof FooGallery_Plugin ) ) {
					self::$instance = new FooGallery_Plugin();
				}

				return self::$instance;
			}

			/**
			 * Initialize the plugin by setting localization, filters, and administration functions.
			 */
			private function __construct() {

				// include everything we need!
				require_once FOOGALLERY_PATH . 'includes/includes.php';

				register_activation_hook( __FILE__, array( 'FooGallery_Plugin', 'activate' ) );

				FooGallery_License_Constant_Handler::init();

				// init FooPluginBase.
				$this->init( FOOGALLERY_FILE, FOOGALLERY_SLUG, FOOGALLERY_VERSION, 'FooGallery' );

				add_filter( 'foogallery_default_options', 'foogallery_get_default_options' );

				// load text domain.
				add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

				// setup gallery post type.
				new FooGallery_PostTypes();

				// load any extensions.
				new FooGallery_Extensions_Loader();

                // Load any bundled extension initializers.
                new FooGallery_Import_Export_Extension();

				if ( is_admin() ) {
					new FooGallery_Admin();
					add_action( 'wpmu_new_blog', array( $this, 'set_default_extensions_for_multisite_network_activated' ) );
					foogallery_fs()->add_filter( 'plugin_icon', array( $this, 'freemius_plugin_icon' ), 10, 1 );
                    foogallery_fs()->add_filter( 'pricing/show_annual_in_monthly', '__return_false' );
					add_action( 'foogallery_admin_menu_before', array( $this, 'add_freemius_activation_menu' ) );
				} else {
					new FooGallery_Public();
				}

				// handles previews. Needed on both frontend and backend.
				new FooGallery_Previews();

				// initialize the thumbnail manager.
				new FooGallery_Thumb_Manager();

				new FooGallery_Shortcodes();

				new FooGallery_Thumbnails();

				new FooGallery_Attachment_Filters();

				new FooGallery_Retina();

				new FooGallery_Animated_Gif_Support();

				new FooGallery_Cache();

                new FooGallery_Lightbox();

				new FooGallery_Common_Fields();

				new FooGallery_LazyLoad();

				new FooGallery_Paging();

				new FooGallery_Thumbnail_Dimensions();

				new FooGallery_Attachment_Custom_Class();

				new FooGallery_Compatibility();

				new FooGallery_Extensions_Compatibility();

				new FooGallery_Crop_Position();

				new FooGallery_ForceHttps();

				new FooGallery_Debug();

				new FooGallery_Password_Protect();

				$checker = new FooGallery_Version_Check();
				$checker->wire_up_checker();

				new FooGallery_Widget_Init();

				// include the default templates no matter what!
				new FooGallery_Default_Templates();

				// init the default media library datasource.
				new FooGallery_Datasource_MediaLibrary();

                new FooGallery_Attachment_Type();

				$pro_code_included = false;

				if ( foogallery_fs()->is__premium_only() ) {
					if ( foogallery_fs()->can_use_premium_code() ) {
						require_once FOOGALLERY_PATH . 'pro/foogallery-pro.php';

						new FooGallery_Pro();

						$pro_code_included = true;
					}
				}

				if ( ! $pro_code_included ) {
					add_filter( 'foogallery_extensions_for_view', array( $this, 'add_foogallery_pro_features' ) );
				}

				// init Gutenberg!
				new FooGallery_Gutenberg();

				// init advanced settings.
				new FooGallery_Advanced_Gallery_Settings();

				// init localization for FooGallery.
				new FooGallery_il8n();
			}

			function add_foogallery_pro_features( $extensions ) {

                $pro_features = foogallery_pro_features();

                $extensions[] = array(
                    'slug' => 'foogallery-bulk-copy',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Bulk Copy', 'foogallery' ),
                    'description' => $pro_features['bulk_copy']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['bulk_copy']['link'],
                    'dashicon'          => 'dashicons-admin-page',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-whitelabeling',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'White Labeling', 'foogallery' ),
                    'description' => $pro_features['whitelabeling']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['whitelabeling']['link'],
                    'dashicon'          => 'dashicons-tag',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-exif',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'EXIF', 'foogallery' ),
                    'description' => $pro_features['exif']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['exif']['link'],
                    'dashicon'          => 'dashicons-camera',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-filtering',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Filtering', 'foogallery' ),
                    'description' => $pro_features['filtering']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['filtering']['link'],
                    'dashicon'          => 'dashicons-filter',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-gallery-blueprints',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Gallery Blueprints', 'foogallery' ),
                    'description' => $pro_features['gallery_blueprints']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['gallery_blueprints']['link'],
                    'dashicon'          => 'dashicons-networking',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-paging',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Pagination', 'foogallery' ),
                    'description' => $pro_features['pagination']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['pagination']['link'],
                    'dashicon'          => 'dashicons-arrow-right-alt',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-protection',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Protection', 'foogallery' ),
                    'description' => $pro_features['protection']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['protection']['link'],
                    'dashicon'          => 'dashicons-lock',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-video',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Video', 'foogallery' ),
                    'description' => $pro_features['video']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['video']['link'],
                    'dashicon'          => 'dashicons-video-alt3',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

                $extensions[] = array(
                    'slug' => 'foogallery-woocommerce',
                    'categories' => array( 'Premium' ),
                    'title' => foogallery__( 'Ecommerce', 'foogallery' ),
                    'description' => $pro_features['ecommerce']['desc'],
                    'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                    'external_link_url' => $pro_features['ecommerce']['link'],
                    'dashicon'          => 'dashicons-cart',
                    'tags' => array( 'Premium' ),
                    'source' => 'upgrade'
                );

				return $extensions;
			}

			function add_freemius_activation_menu() {
                if ( foogallery_freemius_is_anonymous() ) {
                    return;
                }

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

			/**
			 * Set Freemius plugin icon.
			 *
			 * @return string
			 */
			public function freemius_plugin_icon( $icon ) {
				return FOOGALLERY_PATH . 'assets/foogallery.jpg';
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
			 * @param    boolean $network_wide       True if WPMU superadmin uses
			 *                                       "Network Activate" action, false if
			 *                                       WPMU is disabled or plugin is
			 *                                       activated on an individual blog.
			 */
			public static function activate( $network_wide ) {
				FooGallery_License_Constant_Handler::flag_activation();

				if ( function_exists( 'is_multisite' ) && is_multisite() ) {

					if ( $network_wide ) {

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
					// Make sure we redirect to the welcome page
					set_transient( FOOGALLERY_ACTIVATION_REDIRECT_TRANSIENT_KEY, true, 30 );
				}
			
				// Set the 'advanced_attachment_modal' setting to 'on'
				if ( ! foogallery_get_setting( 'advanced_attachment_modal' ) ) {
					foogallery_set_setting( 'advanced_attachment_modal', 'on' );
				}
			
				// Force a version check on activation to make sure housekeeping is performed
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

					$sites    = get_sites();
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
}
