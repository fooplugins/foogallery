<?php
/**
 * FooGallery Admin FooPilot Ajax Handler class
 *
 * @package   FooGallery
 */

if ( ! class_exists( 'FooGallery_Admin_FooPilot_Ajax_Handler' ) ) {

	/**
	 * FooGallery Admin FooPilot Ajax Handler class
	 */
	class FooGallery_Admin_FooPilot_Ajax_Handler {
		/**
		 * Property to hold an instance of FooGallery_Admin_FooPilot_Points_Manager.
		 */
		private $points_manager;

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_generate_foopilot_api_key', array( $this, 'generate_random_api_key' ) );
			add_action( 'wp_ajax_deduct_foopilot_points', array( $this, 'deduct_foopilot_points' ) );
			add_action( 'wp_ajax_foopilot_generate_task_content', array( $this, 'foopilot_generate_task_content' ) );
			$this->points_manager = new FooGallery_Admin_FooPilot_Points_Manager();
		}

		/**
		 * Generate Foopilot api keys
		 */
		public function generate_random_api_key() {
			$current_points = $this->points_manager->get_foopilot_credit_points();

			// If the current points balance is greater than 0, reset it to 0.
			if ( $current_points > 0 ) {
				update_option( 'foopilot_credit_points', 0 );
			}

			// Credit the registered user +20 points.
			$this->points_manager->add_foopilot_credit_points( 20 );

			// Generate a random API key (64 characters in hexadecimal).
			$random_api_key = bin2hex( random_bytes( 32 ) );

			// Save API key to foogallery setting.
			foogallery_set_setting( 'foopilot_api_key', $random_api_key );

			// Check if the API key was saved successfully.
			$saved_api_key = foogallery_get_setting( 'foopilot_api_key' );

			if ( $saved_api_key === $random_api_key ) {
				wp_send_json_success( 'API key generated successfully.' );
			} else {
				wp_send_json_error( 'Failed to save API key.' );
			}
		}

		/**
		 * Deduct points after completing a task and return updated modal content.
		 */
		public function deduct_foopilot_points() {
			// Deduct points.
			if ( isset( $_POST['points'] ) ) {
				$points_to_deduct = intval( $_POST['points'] );
				// Check if user has sufficient points.
				$current_points = $this->points_manager->get_foopilot_credit_points();
				if ( $current_points >= $points_to_deduct && $points_to_deduct > 0 ) {
					// Deduct points only if the user has sufficient points.
					$updated_points = max( 0, $current_points - $points_to_deduct );
					update_option( 'foopilot_credit_points', $updated_points );
					wp_send_json_success( $updated_points );
				} else {
					// Handle case where user doesn't have enough points.
					wp_send_json_error( 'Insufficient points' );
				}
			}
			wp_die();
		}

		/**
		 * Callback function to generate task content dynamically.
		 *
		 * This function handles AJAX requests to generate task content based on the provided task.
		 * It verifies the nonce, retrieves the task from the POST data,
		 * and includes the appropriate PHP file based on the task.
		 * It then echoes the HTML content returned by the corresponding class-based method.
		 *
		 * @return void
		 */
		public function foopilot_generate_task_content() {
			$task = isset( $_POST['task'] ) ? sanitize_text_field( wp_unslash( $_POST['task'] ) ) : '';

			if ( empty( $task ) ) {
				$task = 'credits';
			}

			if ( ! empty( $task ) ) {

				$require = FOOGALLERY_PATH . 'includes/admin/foopilot/tasks/' . $task . '.php';

				if ( file_exists( $require ) ) {
					require_once $require;
				} else {
					echo esc_html__( 'Unknown FooPilot task!', 'foogallery' );
				}
			}

			wp_die();
		}
	}
}
