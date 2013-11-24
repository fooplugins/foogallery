<?php
/*
 * Foo_PluginBase
 * A base class for WordPress plugins. This class makes it really easy and straight forward to create useful bug free plugins.
 * Simply inherit from Foo_PluginBase_v1_0
 * Version: 1.1
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !class_exists( 'Foo_Plugin_Base_v1_1' ) ) {

	abstract class Foo_Plugin_Base_v1_1 {

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

		/* internal dependencies */

		/** @var Foo_Utils_v1_0 */
		protected $_utils = false; //a reference to our utils class

		/** @var Foo_Plugin_Settings_v1_0 */
		protected $_settings = false; //a ref to our settings helper class

		/** @var Foo_Plugin_Options_v1_1 */
		protected $_options = false; //a ref to our options helper class

		/** @var Foo_Plugin_Screen_v1_0 */
		protected $_screen; //a ref to our screen helper class

        /*
         * @return Foo_Plugin_Settings_v1_0
         */
        public function settings() {
            return $this->_settings;
        }

        /*
         * @return Foo_Plugin_Options_v1_1
         */
        public function options() {
            return $this->_options;
        }

        /*
         * @return Foo_Plugin_Screen_v1_0
         */
        public function screen() {
            return $this->_screen;
        }

        /*
         * @return Foo_Utils_v1_0
         */
        public function utils() {
            return $this->_utils;
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
		 * plugin constructor
		 * If the subclass makes use of a constructor, make sure the subclass calls parent::__construct() or parent::init()
		 */
		function __construct($file) {
			$this->init($file);
		}

		/*
		 * Initializes the plugin.
		 */
		function init($file, $slug = false, $version = false, $title = false) {

			$this->plugin_file     = $file;
			$this->plugin_dir      = trailingslashit( dirname( $file ) );
			$this->plugin_dir_name = plugin_basename( $this->plugin_dir );
			$this->plugin_url      = trailingslashit( plugins_url( '', $file ) );

			if ( $slug !== false ) $this->plugin_slug = $slug;
			if ( $version !== false ) $this->plugin_version = $version;
			if ( $title !== false ) $this->plugin_title = $title;

			//check to make sure the mandatory plugin fields have been set
			$this->check_mandatory_plugin_variables_set();

			//load any plugin dependencies
			$this->load_dependencies();

			//check we are using php 5
			$this->_utils->check_php_version( $this->plugin_title, '5.0.0' );

			// Load plugin text domain
			add_action( 'init', array($this, 'load_plugin_textdomain') );

			// Render any inline styles that need to go at the end of the head tag
			add_action( 'wp_head', array($this, 'inline_styles'), 100 );

			// Render any inline scripts at the bottom of the page just before the closing body tag
			add_action( 'wp_footer', array($this, 'inline_scripts'), 200 );

			if ( is_admin() ) {
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

			do_action( $this->plugin_slug . '-' . (is_admin() ? 'admin' : '') . '_init' );
		}

		function check_mandatory_plugin_variables_set() {
			if ( empty($this->plugin_file) ) {
				throw new Exception('Required plugin variable not set : \'plugin_file\'. Please set this in the init() function of your plugin.');
			}
			if ( $this->plugin_slug === false ) {
				throw new Exception('Required plugin variable not set : \'plugin_slug\'. Please set this in the init() function of your plugin.');
			}
			if ( $this->plugin_title === false ) {
				throw new Exception('Required plugin variable not set : \'plugin_title\'. Please set this in the init() function of your plugin.');
			}
			if ( $this->plugin_version === false ) {
				throw new Exception('Required plugin variable not set : \'plugin_version\'. Please set this in the init() function of your plugin.');
			}
		}

		//load any dependencies
		function load_dependencies() {
			$this->_utils    = new Foo_Utils_v1_0();
			$this->_settings = new Foo_Plugin_Settings_v1_0($this->plugin_slug);
			$this->_options  = new Foo_Plugin_Options_v1_1($this->plugin_slug);
			$this->_screen   = new Foo_Plugin_Screen_v1_0($this->plugin_slug);

			do_action( $this->plugin_slug . '-load_dependencies' );
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			$domain = $this->plugin_slug;
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, false, $this->plugin_dir . '/lang/' );
		}

		//wrapper around the apply_filters function that appends the plugin slug to the tag
		function apply_filters($tag, $value) {
			if ( !$this->_utils->starts_with( $tag, $this->plugin_slug ) ) {
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
			if ( !$this->_utils->str_contains( $file, '://' ) ) {
				$js_src_url = $this->plugin_url . 'js/' . $file;
				if ( !file_exists( $this->plugin_dir . 'js/' . $file ) ) return;
			}
			$h = str_replace( '.', '-', pathinfo( $file, PATHINFO_FILENAME ) );

			wp_register_script(
				$handle = $h,
				$src = $js_src_url,
				$deps = $d,
				$ver = $v,
				$in_footer = $f );

			wp_enqueue_script( $h );
		}

		// register and enqueue a CSS
		function register_and_enqueue_css($file, $d = false, $v = false) {
			if ( $v === false ) {
				$v = $this->plugin_version;
			}

			$css_src_url = $file;
			if ( !$this->_utils->str_contains( $file, '://' ) ) {
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
		}

		// register any options/settings we may want to store for this plugin
		function admin_create_settings() {
			do_action( $this->plugin_slug . '-admin_create_settings', $this, $this->_settings );
		}

		// enqueue the admin scripts
		function admin_print_scripts() {

			//add a general admin script
			$this->register_and_enqueue_js( 'admin.js' );

			//if we are on the current plugin's settings page then check for file named /js/admin-settings.js
			if ( $this->_screen->is_plugin_settings_page() ) {
				$this->register_and_enqueue_js( 'admin-settings.js' );

				//check if we are using an upload setting and add media uploader scripts
				if ( $this->_settings->has_setting_of_type( 'image' ) ) {
					wp_enqueue_script( 'media-upload' );
					wp_enqueue_script( 'thickbox' );
					$this->register_and_enqueue_js( 'admin-uploader.js', array('jquery', 'media-upload', 'thickbox') );
				}
			}

			//add any scripts for the current post type
			$post_type = $this->_screen->get_screen_post_type();
			if ( !empty($post_type) ) {
				$this->register_and_enqueue_js( 'admin-' . $post_type . '.js' );
			}

			//finally try add any scripts for the current screen id /css/admin-screen-id.css
			$this->register_and_enqueue_js( 'admin-' . $this->_screen->get_screen_id() . '.js' );

			do_action( $this->plugin_slug . '-admin_print_scripts' );
		}

		// register the admin stylesheets
		function admin_print_styles() {

			//add a general admin stylesheet
			$this->register_and_enqueue_css( 'admin.css' );

			//if we are on the current plugin's settings page then check for file /css/admin-settings.css
			if ( $this->_screen->is_plugin_settings_page() ) {
				$this->register_and_enqueue_css( 'admin-settings.css' );

				//Media Uploader Style
				wp_enqueue_style( 'thickbox' );
			}

			//add any scripts for the current post type /css/admin-foobar.css
			$post_type = $this->_screen->current_post_type();
			if ( !empty($post_type) ) {
				$this->register_and_enqueue_css( 'admin-' . $post_type . '.css' );
			}

			//finally try add any styles for the current screen id /css/admin-screen-id.css
			$this->register_and_enqueue_css( 'admin-' . $this->_screen->get_screen_id() . '.css' );

			do_action( $this->plugin_slug . '-admin_print_styles' );
		}

		function admin_plugin_listing_actions($links) {
			if ( $this->has_admin_settings_page() ) {
				// Add the 'Settings' link to the plugin page
				$links[] = '<a href="options-general.php?page=' . $this->plugin_slug . '"><b>Settings</b></a>';
			}

			return apply_filters( $this->plugin_slug . '-plugin_action_links', $links );
		}

		function has_admin_settings_page() {
			return apply_filters( $this->plugin_slug . '-has_settings_page', true );
		}

		// add a settings admin menu
		function admin_settings_page_menu() {
			if ( $this->has_admin_settings_page() ) {

				$page_title = $this->apply_filters( 'settings_page_title', $this->plugin_title . __( ' Settings', $this->plugin_slug ) );
				$menu_title = $this->apply_filters( 'settings_menu_title', $this->plugin_title );

				add_options_page( $page_title, $menu_title, 'manage_options', $this->plugin_slug, array($this, 'admin_settings_render_page') );
			}
		}

		// render the setting page
		function admin_settings_render_page() {
			global $settings_data;

			//check if a settings.php file exists in the views folder. If so then include it
			if ( file_exists( $this->plugin_dir . 'views/settings.php' ) ) {

				//global variable that can be used by the included settings pages
				$settings_data = array(
					'plugin_info'      => $this->get_plugin_info(),
					'settings_summary' => $this->apply_filters( 'settings_page_summary', '' ),
					'settings_tabs'    => $this->_settings->get_tabs()
				);

                do_action( $this->plugin_slug . '-before_settings_page_render', $settings_data );

                include_once( $this->plugin_dir . 'views/settings.php' );

                do_action( $this->plugin_slug . '-after_settings_page_render', $settings_data );
			}
		}

		function inline_styles() {
            do_action( $this->plugin_slug . '-' . (is_admin() ? 'admin' : '') . '_inline_styles', $this );
		}

		function inline_scripts() {
			do_action( $this->plugin_slug . '-' . (is_admin() ? 'admin' : '') . '_inline_scripts', $this );
		}
	}
}

if ( !class_exists( 'Foo_Utils_v1_0' ) ) {
	class Foo_Utils_v1_0 {

		//safely get a value from an array
		function safe_get($array, $key, $default = null) {
			if ( !is_array( $array ) ) return $default;
			$value = array_key_exists( $key, $array ) ? $array[$key] : null;
			if ( $value === null ) {
				return $default;
			}

			return $value;
		}

		function safe_get_from_request($key, $default = null) {
			return $this->safe_get( $_REQUEST, $key, $default );
		}

		// check the version of PHP running on the server
		function check_php_version($plugin_title, $ver) {
			$php_version = phpversion();
			if ( version_compare( $php_version, $ver ) < 0 ) {
				throw new Exception($plugin_title . " requires at least version $ver of PHP. You are running an older version ($php_version). Please upgrade!");
			}
		}

		// check the version of WP running
		function check_wp_version($plugin_title, $ver) {
			global $wp_version;
			if ( version_compare( $wp_version, $ver ) < 0 ) {
				throw new Exception($plugin_title . " requires at least version $ver of WordPress. You are running an older version ($wp_version). Please upgrade!");
			}
		}

		function to_key($input) {
			return str_replace( " ", "_", strtolower( $input ) );
		}

		function to_title($input) {
			return ucwords( str_replace( array("-", "_"), " ", $input ) );
		}

		/*
		 * returns true if a needle can be found in a haystack
		 */
		function str_contains($haystack, $needle) {
			if ( empty($haystack) || empty($needle) ) {
				return false;
			}

			$pos = strpos( strtolower( $haystack ), strtolower( $needle ) );

			if ( $pos === false ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * starts_with
		 * Tests if a text starts with an given string.
		 *
		 * @param     string
		 * @param     string
		 *
		 * @return    bool
		 */
		function starts_with($haystack, $needle) {
			return strpos( $haystack, $needle ) === 0;
		}

		function ends_with($haystack, $needle, $case = true) {
			$expectedPosition = strlen( $haystack ) - strlen( $needle );

			if ( $case ) {
				return strrpos( $haystack, $needle, 0 ) === $expectedPosition;
			}

			return strripos( $haystack, $needle, 0 ) === $expectedPosition;
		}
	}
}

if ( !class_exists( 'Foo_Plugin_Options_v1_1' ) ) {
	class Foo_Plugin_Options_v1_1 {

		protected $plugin_slug;

		function __construct($plugin_slug) {
			$this->plugin_slug = $plugin_slug;
		}

		private function get_all() {
			return get_option( $this->plugin_slug );
		}

		// save a WP option for the plugin. Stores and array of data, so only 1 option is saved for the whole plugin to save DB space and so that the options table is not poluted
		function save($key, $value) {
			$options = $this->get_all();
			if ( !$options ) {
				//no options have been saved for this plugin
				add_option( $this->plugin_slug, array($key => $value) );
			} else {
				$options[$key] = $value;
				update_option( $this->plugin_slug, $options );
			}
		}

		//get a WP option value for the plugin
		function get($key, $default = false) {
			$options = $this->get_all();
			if ( $options ) {
				return (array_key_exists( $key, $options )) ? $options[$key] : $default;
			}

			return $default;
		}

		function is_checked($key, $default = false) {
			$options = $this->get_all();
			if ( $options ) {
				return array_key_exists( $key, $options );
			}

			return $default;
		}

		function delete($key) {
			$options = $this->get_all();
			if ( $options ) {
				unset($options[$key]);
				update_option( $this->plugin_slug, $options );
			}
		}

        function get_int($key, $default = 0) {
            return intval( $this->get($key, $default) );
        }

        function get_float($key, $default = 0) {
            return floatval( $this->get($key, $default) );
        }
	}
}

if ( !class_exists( 'Foo_Plugin_Screen_v1_0' ) ) {
	class Foo_Plugin_Screen_v1_0 {

		protected $plugin_slug;

		function __construct($plugin_slug) {
			$this->plugin_slug = $plugin_slug;
		}

		function get_screen_id() {
			$screen = get_current_screen();
			if ( empty($screen) ) return false;

			return $screen->id;
		}

		function get_screen_post_type() {
			$screen = get_current_screen();
			if ( empty($screen) ) return false;

			return $screen->post_type;
		}

		function is_plugin_settings_page() {
			return is_admin() && $this->get_screen_id() === 'settings_page_' . $this->plugin_slug;
		}

		function is_plugin_post_type_page($post_type) {
			return is_admin() && $this->get_screen_post_type() === $post_type;
		}

		/**
		 * gets the current post type in the WordPress Admin
		 */
		function current_post_type() {
			global $get_current_post_type, $post, $typenow, $current_screen;

			if ( $get_current_post_type ) return $get_current_post_type;

			//we have a post so we can just get the post type from that
			if ( $post && $post->post_type ) {
				$get_current_post_type = $post->post_type;
			} //check the global $typenow - set in admin.php
			elseif ( $typenow ) {
				$get_current_post_type = $typenow;
			} //check the global $current_screen object - set in sceen.php
			elseif ( $current_screen && $current_screen->post_type ) {
				$get_current_post_type = $current_screen->post_type;
			} //lastly check the post_type querystring
			elseif ( isset($_REQUEST['post_type']) ) {
				$get_current_post_type = sanitize_key( $_REQUEST['post_type'] );
			}

			return $get_current_post_type;
		}

		// returns the current URL
		function current_url() {
			global $wp;
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

			return $current_url;
		}

		// returns the current page name
		function current_page_name() {
			return basename( $_SERVER['SCRIPT_FILENAME'] );
		}
	}
}

if ( !class_exists( 'Foo_Plugin_Settings_v1_0' ) ) {
	class Foo_Plugin_Settings_v1_0 {

		protected $plugin_slug;

		/** @var Foo_Utils_v1_0 */
		protected $_utils = false;

		protected $_settings = array(); //the plugin settings array
		protected $_settings_sections = array(); //the plugin sections array
		protected $_settings_tabs = array(); //the plugin tabs array
		protected $_admin_errors = false; //store of admin errors

		function __construct($plugin_slug) {
			$this->plugin_slug = $plugin_slug;
			$this->_utils      = new Foo_Utils_v1_0();
		}

		function get_tabs() {
			return $this->_settings_tabs;
		}

		//check if we have any setting of a certain type
		function has_setting_of_type($type) {
			foreach ( $this->_settings as $setting ) {
				if ( $setting['type'] == $type ) return true;
			}

			return false;
		}

		// add a setting tab
		function add_tab($tab_id, $title) {
			if ( !array_key_exists( $tab_id, $this->_settings_tabs ) ) {

				//pre action
				do_action( $this->plugin_slug . '-before_settings_tab', $tab_id, $title );

				$tab = array(
					'id'    => $tab_id,
					'title' => $title
				);

				$this->_settings_tabs[$tab_id] = $tab;

				//post action
				do_action( $this->plugin_slug . '-after_settings_tab', $tab_id, $title );
			}
		}

		// add a setting section
		function add_section($section_id, $title, $desc = '') {

			//check we have the section
			if ( !array_key_exists( $section_id, $this->_settings_sections ) ) {

				//pre action
				do_action( $this->plugin_slug . '-before_settings_section', $section_id, $title, $desc );

				$section = array(
					'id'    => $section_id,
					'title' => $title,
					'desc'  => $desc
				);

				$this->_settings_sections[$section_id] = $section;

				$section_callback = create_function( '',
					'echo "' . $desc . '";' );

				add_settings_section( $section_id, $title, $section_callback, $this->plugin_slug );

				//post action
				do_action( $this->plugin_slug . '-after_settings_section', $section_id, $title, $desc );
			}
		}

		function add_section_to_tab($tab_id, $section_id, $title, $desc = '') {
			if ( array_key_exists( $tab_id, $this->_settings_tabs ) ) {

				//get the correct section id for the tab
				$section_id = $tab_id . '-' . $section_id;

				//add the section to the tab
				if ( !array_key_exists( $section_id, $this->_settings_sections ) ) {
					$this->_settings_tabs[$tab_id]['sections'][$section_id] = $section_id;
				}

				//add the section
				$this->add_section( $section_id, $title, $desc );

			}

			return $section_id;
		}

		// add a settings field
		function add_setting($args = array()) {

			$defaults = array(
				'id'          => 'default_field',
				'title'       => 'Default Field',
				'desc'        => '',
				'default'     => '',
				'placeholder' => '',
				'type'        => 'text',
				'section'     => '',
				'choices'     => array(),
				'class'       => '',
				'tab'         => ''
			);

			//only declare up front so no debug warnings are shown
			$title = $type = $id = $desc = $default = $placeholder = $choices = $class = $section = $tab = null;

			extract( wp_parse_args( $args, $defaults ) );

			$field_args = array(
				'type'        => $type,
				'id'          => $id,
				'desc'        => $desc,
				'default'     => $default,
				'placeholder' => $placeholder,
				'choices'     => $choices,
				'label_for'   => $id,
				'class'       => $class
			);

			if ( count( $this->_settings ) == 0 ) {
				//only do this once
				register_setting( $this->plugin_slug, $this->plugin_slug, array($this, 'validate') );
			}

			$this->_settings[] = $args;

			$section_id = $this->_utils->to_key( $section );

			//check we have the tab
			if ( !empty($tab) ) {
				$tab_id = $this->_utils->to_key( $tab );

				//add the tab
				$this->add_tab( $tab_id, $this->_utils->to_title( $tab ) );

				//add the section
				$section_id = $this->add_section_to_tab( $tab_id, $section_id, $this->_utils->to_title( $section ) );
			} else {
				//just add the section
				$this->add_section( $section_id, $this->_utils->to_title( $section ) );
			}

            do_action( $this->plugin_slug . '-before_setting', $args );

			//add the setting!
			add_settings_field( $id, $title, array($this, 'render'), $this->plugin_slug, $section_id, $field_args );

            do_action( $this->plugin_slug . '-after_setting', $args );
		}

		// render HTML for individual settings
		function render($args = array()) {

			//only declare up front so no debug warnings are shown
			$type = $id = $desc = $default = $placeholder = $choices = $class = $section = $tab = null;

			extract( $args );

			$options = get_option( $this->plugin_slug );

			if ( !isset($options[$id]) && $type != 'checkbox' ) {
				$options[$id] = $default;
			}

			$field_class = '';
			if ( $class != '' ) {
				$field_class = ' class="' . $class . '"';
			}

			$errors = get_settings_errors( $id );

            do_action( $this->plugin_slug . '-before_settings_render', $args );

			switch ( $type ) {

				case 'heading':
					echo '</td></tr><tr valign="top"><td colspan="2">' . $desc;
					break;

				case 'html':
					echo $desc;
					break;

				case 'checkbox':
					$checked = '';
					if ( isset($options[$id]) && $options[$id] == 'on' ) {
						$checked = ' checked="checked"';
					} else if ( $options === false && $default == 'on' ) {
						$checked = ' checked="checked"';
					}

					//echo '<input type="hidden" name="'.$this->plugin_slug.'[' . $id . '_default]" value="' . $default . '" />';
					echo '<input' . $field_class . ' type="checkbox" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" value="on"' . $checked . ' /> <label for="' . $id . '"><small>' . $desc . '</small></label>';

					break;

				case 'select':
					echo '<select' . $field_class . ' name="' . $this->plugin_slug . '[' . $id . ']">';

					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $options[$id] == $value ) {
							$selected = ' selected="selected"';
						}
						echo '<option ' . $selected . ' value="' . $value . '">' . $label . '</option>';
					}

					echo '</select>';

					break;

				case 'radio':
					$i           = 0;
					$saved_value = $options[$id];
					if ( empty($saved_value) ) {
						$saved_value = $default;
					}
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $saved_value == $value ) {
							$selected = ' checked="checked"';
						}
						echo '<input' . $field_class . $selected . ' type="radio" name="' . $this->plugin_slug . '[' . $id . ']" id="' . $id . $i . '" value="' . $value . '"> <label for="' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;

				case 'textarea':
					echo '<textarea' . $field_class . ' id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" placeholder="' . $placeholder . '">' . esc_attr( $options[$id] ) . '</textarea>';

					break;

				case 'password':
					echo '<input' . $field_class . ' type="password" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" />';

					break;

				case 'text':
					echo '<input class="regular-text ' . $class . '" type="text" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" placeholder="' . $placeholder . '" value="' . esc_attr( $options[$id] ) . '" />';

					break;

				case 'checkboxlist':
					$i = 0;
					foreach ( $choices as $value => $label ) {

						$checked = '';
						if ( isset($options[$id][$value]) && $options[$id][$value] == 'true' ) {
							$checked = 'checked="checked"';
						}

						echo '<input' . $field_class . ' ' . $checked . ' type="checkbox" name="' . $this->plugin_slug . '[' . $id . '|' . $value . ']" id="' . $id . $i . '" value="on"> <label for="' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;
				case 'image':
					echo '<input class="regular-text image-upload-url" type="text" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" placeholder="' . $placeholder . '" value="' . esc_attr( $options[$id] ) . '" />';
					echo '<input id="st_upload_button" class="image-upload-button" type="button" name="upload_button" value="' . __( 'Select Image', $this->plugin_slug ) . '" />';
					break;

				default:
					do_action( $this->plugin_slug . '-settings_custom_type_render', $args );
					break;
			}

            do_action( $this->plugin_slug . '-after_settings_render', $args );

			if ( is_array( $errors ) ) {
				foreach ( $errors as $error ) {
					echo "<span class='error'>{$error['message']}</span>";
				}
			}

			if ( $type != 'checkbox' && $type != 'heading' && $type != 'html' && $desc != '' ) {
				echo '<br /><small>' . $desc . '</small>';
			}
		}

		// validate our settings
		function validate($input) {

			//check to see if the options were reset
			if ( isset ($input['reset-defaults']) ) {
				delete_option( $this->plugin_slug );
				delete_option( $this->plugin_slug . '_valid' );
				delete_option( $this->plugin_slug . '_valid_expires' );
				add_settings_error(
					'reset',
					'reset_error',
					__( 'Settings restored to default values', $this->plugin_slug ),
					'updated'
				);

				return false;
			}

//            if (empty($input['sample_text'])) {
//
//                add_settings_error(
//                    'sample_text',           // setting title
//                    'sample_text_error',            // error ID
//                    'Please enter some sample text',   // error message
//                    'error'                        // type of message
//                );
//
//            }

			foreach ( $this->_settings as $setting ) {
				$this->validate_setting( $setting, $input );
			}

			return $input;
		}

		function validate_setting($setting, &$input) {
			//validate a single setting

			if ( $setting['type'] == 'checkboxlist' ) {

				unset($checkboxarray);

				foreach ( $setting['choices'] as $value => $label ) {
					if ( !empty($input[$setting['id'] . '|' . $value]) ) {
						// If it's not null, make sure it's true, add it to an array
						$checkboxarray[$value] = 'true';
					} else {
						$checkboxarray[$value] = 'false';
					}
				}

				if ( !empty($checkboxarray) ) {
					$input[$setting['id']] = $checkboxarray;
				}

			}
		}
	}
}

if ( !class_exists( 'Foo_Plugin_Metabox_v1_0') ) {
    class Foo_Plugin_Metabox_v1_0 {
        function get_meta($data, $key, $default) {
            if (!is_array($data)) return $default;

            $value = array_key_exists($key, $data) ? $data[$key] : NULL;

            if ($value === NULL)
                return $default;

            return $value;
        }

        function is_checked($data, $key, $default = false) {
            if (!is_array($data)) return $default;

            return array_key_exists($key, $data);

            return $default;
        }
    }
}