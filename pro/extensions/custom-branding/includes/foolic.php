<?php
/**
 * FooPlugins.com Postflow
 *
 * @author    Brad Vincent
 * @version   1
 */

if (!class_exists('Custom_Branding_FooGallery_Extension_Fooplugins')) {

	class Custom_Branding_FooGallery_Extension_Fooplugins {

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		const UPDATE_URL   = 'http://fooplugins.com/api/postflow/check';

		private function __construct() {

			//required files
			require_once( CUSTOM_BRANDING_FOOGALLERY_EXTENSION_PATH . 'includes/foolic_update_checker.php' );
			require_once( CUSTOM_BRANDING_FOOGALLERY_EXTENSION_PATH . 'includes/foolic_validation.php');

			//initialize plugin update checks with fooplugins.com
			new foolic_update_checker_v1_6(
				CUSTOM_BRANDING_FOOGALLERY_EXTENSION_FILE, //the plugin file
				CUSTOM_BRANDING_FOOGALLERY_EXTENSION_UPDATE_URL, //the URL to check for updates
				CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG, //the plugin slug
				get_site_option(CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG . '_licensekey') //the stored license key
			);

			//initialize license key validation with fooplugins.com
			new foolic_validation_v1_4(
				CUSTOM_BRANDING_FOOGALLERY_EXTENSION_UPDATE_URL, //the URL to validate
				CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG
			);

			add_filter( 'foolic_validation_include_css-' . CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG, array($this, 'include_foolic_files') );
			add_filter( 'foolic_validation_include_js-' . CUSTOM_BRANDING_FOOGALLERY_EXTENSION_SLUG, array($this, 'include_foolic_files') );
		}

		//make sure the foo license validation CSS & JS are included on the correct page
		function include_foolic_files($screen) {
			return $screen->id === 'settings_page_foogallery-custom-branding';
		}

		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self();
			} // end if

			return self::$instance;

		} // end get_instance
	}
}