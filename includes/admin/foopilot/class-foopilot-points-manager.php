<?php

/**
 * FooGallery Admin FooPilot Points Manager class.
 *
 * @package   FooGallery.
 */

if ( ! class_exists( 'FooGallery_Admin_FooPilot_Points_Manager' ) ) {

	/**
	 * FooGallery Admin FooPilot Points Manager class for handling FooPilot-related functionality.
	 */
	class FooGallery_Admin_FooPilot_Points_Manager {
		/**
		 * Primary class constructor for FooGallery_Admin_FooPilot_Points_Manager.
		 */
		public function __construct() {

			// Initialize credit points on plugin init.
			add_action( 'init', array( $this, 'initialize_foopilot_credit_points' ) );
		}

		/**
		 * Function to initialize credit points on plugin init.
		 */
		public function initialize_foopilot_credit_points() {
			// Check if the credit points are already set, if not, set it to 0.
			if ( ! get_option( 'foopilot_credit_points' ) ) {
				update_option( 'foopilot_credit_points', 0 );
			}
		}

		/**
		 * Function to retrieve credit points.
		 */
		public function get_foopilot_credit_points() {
			return get_option( 'foopilot_credit_points', 0 );
		}

		/**
		 * Function to add credit points to the existing points.
		 *
		 * @param int $points    The points to be added to the existing points.
		 */
		public function add_foopilot_credit_points( $points ) {
			$current_points = $this->get_foopilot_credit_points();
			$updated_points = $current_points + $points;
			update_option( 'foopilot_credit_points', $updated_points );
		}
	}
}
