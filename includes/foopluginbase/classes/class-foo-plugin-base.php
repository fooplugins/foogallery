<?php
/*
 * Foo_Plugin_Base
 * A base class for WordPress plugins. Get up and running quickly with this opinionated, convention based, plugin framework
 *
 * A note about class versioning: to avoid running into issues when multiple plugins are using different versions of this base class,
 *  we append a version number to the class name. This avoids situations where multiple versions of the same class are loaded into memory and things no longer work as expected.
 *  This situation is extremely difficult to debug, and results in weird errors only when multiple plugins using the base class are activated on a single install
 *
 * Version: 2.3
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !class_exists( 'Foo_Plugin_Base_v2_3' ) ) {

	abstract class Foo_Plugin_Base_v2_3 {

		/**
		 * Unique identifier for your plugin.
		 *
		 * @var      string
		 */
		protected $plugin_slug = false; //the slug (identifier) of the plugin

		/**
		 * The full name of your plugin.
		 *
		 * @var      string
		 */
		protected $plugin_title = false; //the friendly title of the plugin

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @var     string
		 */
		protected $plugin_version = false; //the version number of the plugin

		/* internal variables */
		protected $plugin_file; //the filename of the plugin
		protected $plugin_dir; //the folder path of the plugin
		protected $plugin_dir_name; //the folder name of the plugin
		protected $plugin_url; //the plugin url

		/* internal class dependencies */

		/** @var Foo_Plugin_Settings_v2_1 */
		protected $_settings = false; //a ref to our settings helper class

		/** @var Foo_Plugin_Options_v2_1 */
		protected $_options = false; //a ref to our options helper class

		/*
		 * @return Foo_Plugin_Settings_v2_1
		 */
		public function settings() {
			return $this->_settings;
		}

		/*
		 * @return Foo_Plugin_Options_v2_1
		 */
		public function options() {
			return $this->_options;
		}

		/*
		 * @return string
		 */
		function get_slug() {
			return $this->plugin_slug;
		}

		function get_plugin_info() {
			return array(
				'slug'    => $this->plugin_slug,
				'title'   => $this->plugin_title,
				'version' => $this->plugin_version,
				'dir'     => $this->plugin_dir,
				'url'     => $this->plugin_url
			);
		}

		/*
		 * Initializes the plugin.
		 */
		function init($file, $slug = false, $version = '0.0.1', $title = false) {

			//check to make sure the mandatory plugin fields have been set
			if ( empty($file) ) {
				throw new Exception('Required plugin variable not set : \'plugin_file\'. Please set this in the init() function of your plugin.');
			}
			if ( empty( $version ) ) {
				throw new Exception('Required plugin variable not set : \'plugin_version\'. Please set this in the init() function of your plugin.');
			}

			$this->plugin_file     = $file;
			$this->plugin_dir      = plugin_dir_path( $file );
			$this->plugin_dir_name = plugin_basename( $this->plugin_dir );
			$this->plugin_url      = plugin_dir_url( $file );
			$this->plugin_slug 	= $slug !== false ? $slug : plugin_basename( $file );
			$this->plugin_title 	= $title !== false ? $title : foo_title_case( $this->plugin_slug );
			$this->plugin_version = $version;

			//instantiate our option class
			$this->_options  = new Foo_Plugin_Options_v2_1($this->plugin_slug);

			//check we are using php 5
			foo_check_php_version( $this->plugin_title, '5.0.0' );

			// Load plugin text domain
			add_action( 'init', array($this, 'load_plugin_textdomain') );

			// Render any inline styles that need to go at the end of the head tag
			add_action( 'wp_head', array($this, 'inline_styles'), 100 );

			// Render any inline scripts at the bottom of the page just before the closing body tag
			add_action( 'wp_footer', array($this, 'inline_scripts'), 200 );

			if ( is_admin() ) {
				//instantiate our settings class
				$this->_settings = new Foo_Plugin_Settings_v2_1($this->plugin_slug);

				//instantiate our metabox sanity class
				new Foo_Plugin_Metabox_Sanity_v1($this->plugin_slug);

				// Register any settings for the plugin
				add_action( 'admin_init', array($this, 'admin_create_settings') );

				// Add a settings page menu item
				add_action( 'admin_menu', array($this, 'admin_settings_page_menu') );

				// Add a links to the plugin listing
				add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_file ), array($this, 'admin_plugin_listing_actions') );

				// output CSS to the admin pages
				add_action( 'admin_print_styles', array($this, 'admin_print_styles') );

				// output JS to the admin pages
				add_action( 'admin_print_scripts', array($this, 'admin_print_scripts') );
			}

			do_action( $this->plugin_slug . (is_admin() ? '_admin' : '') . '_init' );
		}

		/**
		 * Loads the plugin language files for translation
		 *
		 * @param string $languages_directory The default language directory location. Default location is /languages/
		 */
		public function load_plugin_textdomain($languages_directory = '/languages/') {
			Foo_Plugin_TextDomain_v1_0::load_textdomain(
				$this->plugin_file,
				$this->plugin_slug,
				$languages_directory
			);
		}

		//wrapper around the apply_filters function that appends the plugin slug to the tag
		function apply_filters($tag, $value) {
			if ( !foo_starts_with( $tag, $this->plugin_slug ) ) {
				$tag = $this->plugin_slug . '-' . $tag;
			}

			return apply_filters( $tag, $value );
		}

		// register and enqueue a script
		function register_and_enqueue_js($file, $d = array('jquery'), $v = false, $f = false) {
			if ( $v === false ) {
				$v = $this->plugin_version;
			}

			$js_src_url = $file;
			if ( !foo_contains( $file, '://' ) ) {

				//check for the file in plugin root js directory
				$js_src_url = $this->plugin_url . 'js/' . $file;
				if ( !file_exists( $this->plugin_dir . 'js/' . $file ) ) {

					//check for the file in relative js directory
					$js_src_url = plugin_dir_url( __FILE__ ) . 'js/' . $file;
					if ( !file_exists( plugin_dir_path( __FILE__ ) . 'js/' . $file ) ) {
						return;
					}

				}
			}
			$h = str_replace( '.', '-', pathinfo( $file, PATHINFO_FILENAME ) );

			wp_register_script(
				$handle = $h,
				$src = $js_src_url,
				$deps = $d,
				$ver = $v,
				$in_footer = $f );

			wp_enqueue_script( $h );

			return $h;
		}

		// register and enqueue a CSS
		function register_and_enqueue_css($file, $d = array(), $v = false) {
			if ( $v === false ) {
				$v = $this->plugin_version;
			}

			$css_src_url = $file;
			if ( !foo_contains( $file, '://' ) ) {
				$css_src_url = $this->plugin_url . 'css/' . $file;
				if ( !file_exists( $this->plugin_dir . 'css/' . $file ) ) return;
			}

			$h = str_replace( '.', '-', pathinfo( $file, PATHINFO_FILENAME ) );

			wp_register_style(
				$handle = $h,
				$src = $css_src_url,
				$deps = $d,
				$ver = $v );

			wp_enqueue_style( $h );

			return $h;
		}

		// register any options/settings we may want to store for this plugin
		function admin_create_settings() {
			$settings = apply_filters( $this->plugin_slug . '_admin_settings', false );

			$this->_settings->add_settings( $settings );
		}

		// enqueue the admin scripts
		function admin_print_scripts() {

			//add a general admin script
			$this->register_and_enqueue_js( 'admin.js' );

			//if we are on the current plugin settings page then check for file named /js/admin-settings.js
			if ( foo_check_plugin_settings_page( $this->plugin_slug ) ) {
				$this->register_and_enqueue_js( 'admin-settings.js' );

				//check if we are using an upload setting and add media uploader scripts
				if ( $this->_settings->has_setting_of_type( 'image' ) ) {
					//wp_enqueue_script( 'media-upload' );
					//wp_enqueue_script( 'thickbox' );
					//$this->register_and_enqueue_js( 'admin-uploader.js', array('jquery', 'media-upload', 'thickbox') );
				}
			}

			//add any scripts for the current post type
			$post_type = foo_current_screen_post_type();
			if ( !empty($post_type) ) {
				$this->register_and_enqueue_js( 'admin-' . $post_type . '.js' );
			}

			//finally try add any scripts for the current screen id /css/admin-screen-id.css
			$this->register_and_enqueue_js( 'admin-' . foo_current_screen_id() . '.js' );

			do_action( $this->plugin_slug . '_admin_print_scripts' );
		}

		// register the admin stylesheets
		function admin_print_styles() {

			//add a general admin stylesheet
			$this->register_and_enqueue_css( 'admin.css' );

			//if we are on the current plugin's settings page then check for file /css/admin-settings.css
			if ( foo_check_plugin_settings_page( $this->plugin_slug ) ) {
				$this->register_and_enqueue_css( 'admin-settings.css' );

				//Media Uploader Style
				wp_enqueue_style( 'thickbox' );
			}

			//add any scripts for the current post type /css/admin-foobar.css
			$post_type = foo_current_screen_post_type();
			if ( !empty($post_type) ) {
				$this->register_and_enqueue_css( 'admin-' . $post_type . '.css' );
			}

			//finally try add any styles for the current screen id /css/admin-screen-id.css
			$this->register_and_enqueue_css( 'admin-' . foo_current_screen_id() . '.css' );

			do_action( $this->plugin_slug . '_admin_print_styles' );
		}

		function admin_plugin_listing_actions($links) {
			if ( $this->has_admin_settings_page() ) {
				// Add the 'Settings' link to the plugin page
				$links[] = '<a href="options-general.php?page=' . $this->plugin_slug . '"><b>' . __('Settings', $this->plugin_slug) .'</b></a>';
			}

			return apply_filters( $this->plugin_slug . '_admin_plugin_action_links', $links );
		}

		function has_admin_settings_page() {
			return apply_filters( $this->plugin_slug . '_admin_has_settings_page', true );
		}

		// add a settings admin menu
		function admin_settings_page_menu() {
			if ( $this->has_admin_settings_page() ) {

				$page_title = $this->apply_filters( $this->plugin_slug . '_admin_settings_page_title', $this->plugin_title . __( ' Settings', $this->plugin_slug ) );
				$menu_title = $this->apply_filters( $this->plugin_slug . '_admin_settings_menu_title', $this->plugin_title );

				add_options_page( $page_title, $menu_title, 'manage_options', $this->plugin_slug, array($this, 'admin_settings_render_page') );
			}
		}

		// render the setting page
		function admin_settings_render_page() {
			$current_directory = trailingslashit(dirname(plugin_dir_path( __FILE__ )));

			//check if a settings.php file exists in the views folder. If so then include it
			if ( file_exists( $current_directory . 'views/settings.php' ) ) {

				//global variable that can be used by the included settings pages
				include_once( $current_directory . 'views/settings.php');
			}

			do_action( $this->plugin_slug . '_admin_settings_render_page', $this->_settings );
		}

		function inline_styles() {
			do_action( $this->plugin_slug . (is_admin() ? '_admin' : '') . '_inline_styles', $this );
		}

		function inline_scripts() {
			do_action( $this->plugin_slug . (is_admin() ? '_admin' : '') . '_inline_scripts', $this );
		}
	}
}