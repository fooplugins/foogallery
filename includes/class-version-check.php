<?php
/**
 * Created by brad.
 * Date: 15/11/2015
 */
if ( ! class_exists( 'FooGallery_Version_Check' ) ) {

	class FooGallery_Version_Check {

		/**
		 * Wire up the so the check is done in the admin once all plugins have been loaded
		 */
		public function wire_up_checker() {
			if ( is_admin() ) {
				//when in admin, check if a new version is running
				add_action( 'plugins_loaded', array( $this, 'perform_check' ) );
			}
		}

		/**
		 * Perform a check to see if the plugin has been updated
		 */
		public function perform_check() {
			if ( get_site_option( FOOGALLERY_OPTION_VERSION ) != FOOGALLERY_VERSION ) {
				//This code will run every time the plugin is updated

				//perform all our housekeeping
				$this->perform_housekeeping();

				//set the current version, so that this does not run again until the next update!
				update_site_option( FOOGALLERY_OPTION_VERSION, FOOGALLERY_VERSION );
			}
		}

		/**
		 * Runs after FooGallery has been updated via the backend
		 */
		function perform_housekeeping() {
			//allow extensions or other plugins to do stuff when foogallery is updated
			// this will catch both manual and auto updates!
			do_action( 'foogallery_admin_new_version_detected' );

			//we need to clear the foogallery css load optimizations when we update the plugin, to ensure the latest CSS files are loaded
			foogallery_clear_all_css_load_optimizations();
		}
	}
}