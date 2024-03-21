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
		public function __construct( FooGallery_Admin_FooPilot_Points_Manager $points_manager ) {
			add_action( 'wp_ajax_generate_foopilot_api_key', array( $this, 'generate_random_api_key' ) );
			add_action( 'wp_ajax_deduct_foopilot_points', array( $this, 'deduct_foopilot_points' ) );
			add_action( 'wp_ajax_foopilot_generate_task_content', array( $this, 'foopilot_generate_task_content' ) );
			$this->points_manager = $points_manager;
		}

		/**
		 * Generate Foopilot api keys and credit points to the registered user.
		 */
		public function generate_random_api_key() {
			$current_points = $this->points_manager->get_foopilot_credit_points();

			// Reset points to 0 if the user has any points.
			if ( $current_points > 0 ) {
				update_option( 'foopilot_credit_points', 0 );
			}

			// Add 20 points to the user's account.
			$this->points_manager->add_foopilot_credit_points( 20 );

			// Generate a random API key.
			do {
				$random_api_key = bin2hex( random_bytes( 32 ) );
				// Check if the generated API key already exists.
				$existing_api_key = foogallery_get_setting( 'foopilot_api_key' );
			} while ( $existing_api_key === $random_api_key );

			// Save the generated API key.
			foogallery_set_setting( 'foopilot_api_key', $random_api_key );

			// Check if the API key was saved successfully and return the appropriate response.
			$saved_api_key = foogallery_get_setting( 'foopilot_api_key' );

			if ( $saved_api_key === $random_api_key ) {
				wp_send_json_success( 'API key generated successfully.' );
			} else {
				wp_send_json_error( 'Failed to save API key.' );
			}
		}

		/**
		 * Deduct points from the user's account.
		 */
		public function deduct_foopilot_points() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die();
			}
			if ( ! isset( $_POST['points'] ) ) {
				wp_die();
			}
			$points = intval( $_POST['points'] );
			$current_points = $this->points_manager->get_foopilot_credit_points();
			$updated_points = $current_points - $points;
			update_option( 'foopilot_credit_points', $updated_points );
			wp_send_json_success( $updated_points );
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
