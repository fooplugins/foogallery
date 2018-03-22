<?php

if ( ! class_exists( 'FooGallery_Extensions_Loader' ) ) {
	class FooGallery_Extensions_Loader {

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_active_extensions' ) );
		}

		/**
		 * Load all FooGallery extensions that have been activated.
		 * For each extension, create an instance of the extension class and add it to a global extensions array
		 */
		function load_active_extensions() {
			$action = foo_safe_get( $_POST, 'action');
			if ( 'deactivate' === $action || 'heartbeat' === $action ) { return; }

			if ( ! function_exists( 'get_current_screen' ) ) {
				require_once(ABSPATH . 'wp-admin/includes/screen.php');
			}

			$api               = new FooGallery_Extensions_API();
			$active_extensions = $api->get_active_extensions();
			foreach ( $active_extensions as $slug => $class ) {
				try {
					$this->load_extension( $slug, $class );
				}
				catch (Exception $e) {
					$error = $e;
					$something = $error;
				}
			}

			//What if no extensions were loaded?
		}

		function load_extension( $slug, $class ) {
			global $foogallery_extensions;
			global $foogallery_currently_loading;
			if ( is_null( $foogallery_extensions ) ) {
				$foogallery_extensions = array();
			}
			if ( class_exists( $class ) && !array_key_exists( $slug, $foogallery_extensions ) ) {
				$foogallery_currently_loading = $slug;
				$instance = new $class();
				$foogallery_extensions[ $slug ] = $instance;
			}
		}

		function handle_load_exceptions( $errno, $errstr, $errfile, $errline ) {
			global $foogallery_currently_loading;
			$api = new FooGallery_Extensions_API();
			$api->deactivate( $foogallery_currently_loading, false, true );

			//don't execute PHP internal error handler
			return true;
		}
	}
}
