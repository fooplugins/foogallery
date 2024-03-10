<?php
/**
 * FooGallery Admin FooPilot class
 *
 * @package   FooGallery
 */

if ( ! class_exists( 'FooGallery_Admin_FooPilot' ) ) {

	/**
	 * FooGallery Admin FooPilot class
	 */
	class FooGallery_Admin_FooPilot {

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_foopilot_settings' ), 50 );
			add_action( 'wp_ajax_generate_foopilot_api_key', array( $this, 'generate_random_api_key' ) );
			add_action( 'wp_ajax_deduct_foopilot_points', array( $this, 'deduct_foopilot_points' ) );
			add_action( 'init', array( $this, 'add_nonce' ) );

			// Initialize credit points.
			add_action( 'init', array( $this, 'initialize_foopilot_credit_points' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			add_action( 'wp_ajax_foopilot_generate_task_content', array( $this, 'foopilot_generate_task_content' ) );
		}

		/**
		 * Enqueue scripts and styles.
		 */
		public function enqueue_scripts_and_styles() {
			// check if the gallery edit page is being shown.
			$screen = get_current_screen();
			if ( 'foogallery' !== $screen->id ) {
				return;
			}
			// Enqueue CSS.
			wp_enqueue_style( 'foopilot-modal-css', FOOGALLERY_URL . 'includes/admin/foopilot/css/foopilot-modal.css', array(), FOOGALLERY_VERSION );

			$foogallery = FooGallery_Plugin::get_instance();
			$foogallery->register_and_enqueue_js( FOOGALLERY_URL . 'includes/admin/foopilot/css/foopilot-modal.js' );

			do_action( 'foogallery_admin_foopilot_enqueue_scripts' );
		}

		/**
		 * Add nonce for FooPilot actions.
		 */
		public function add_nonce() {
			// Add nonce for generating API key.
			add_action( 'wp_ajax_generate_foopilot_api_key', array( $this, 'generate_random_api_key' ) );
			// Add nonce for deducting points.
			add_action( 'wp_ajax_deduct_foopilot_points', array( $this, 'deduct_foopilot_points' ) );
			// Add nonce for saving attachment data.
			add_action( 'wp_ajax_save_attachment_data', array( $this, 'save_attachment_data' ) );
		}

		/**
		 * Generate the nonce.
		 */
		public function generate_nonce() {
			return wp_create_nonce( 'foopilot_nonce' );
		}

		/**
		 * Verify the nonce.
		 *
		 * @param string $nonce The nonce to verify.
		 * @return bool Whether the nonce is valid.
		 */
		public function verify_nonce( $nonce ) {
			return wp_verify_nonce( $nonce, 'foopilot_nonce' );
		}

		/**
		 * Deduct points after completing a task and return updated modal content.
		 */
		public function deduct_foopilot_points() {
			// Verify the nonce.
			$foopilot_nonce = isset( $_POST['foopilot_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foopilot_nonce'] ) ) : '';

			if ( wp_verify_nonce( $foopilot_nonce, 'foopilot_nonce' ) ) {
				// Deduct points.
				if ( isset( $_POST['points'] ) ) {
					$points_to_deduct = intval( $_POST['points'] );
					// Check if user has sufficient points.
					$current_points = $this->get_foopilot_credit_points();
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
			} else {
				wp_die( 'Unauthorized request!' );
			}

			wp_die();
		}

		/**
		 * Generate foopilot modal HTML.
		 */
		public function display_foopilot_modal_html() {
			// Check if the FooPilot API key is present.
			$foopilot_api_key = foogallery_get_setting( 'foopilot_api_key' );
			$credit_points    = $this->get_foopilot_credit_points();
			?>
			<div id="foopilot-modal" class="foogallery-foopilots-modal-wrapper" style="display: none;">
				<div class="media-modal wp-core-ui" id="fg-foopilot-modal">
					<div>
						<button type="button" class="media-modal-close">
							<span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
						</button>
						<div class="media-modal-content">
							<div class="media-frame wp-core-ui">

								<div class="foogallery-foopilot-modal-title">
									<h2>
										<?php esc_html_e( 'FooPilot AI Image Tools', 'foogallery' ); ?>
									</h2>
									<h3>
										<?php
										esc_html_e( 'Credit Points:', 'foogallery' );
										?>
										<span id="foogallery-credit-points">
											<?php
											echo esc_html( $credit_points );
											?>
										</span>
										<?php
										// Show "Buy" button if credit points are less than 10.
										if ( $credit_points < 10 ) {
											echo '<button class="buy-credits button button-primary button-small" data-task="credit" style="margin-left: 10px;">' . esc_html__( 'Buy credits', 'foogallery' ) . '</button>';
										}
										?>
									</h3>
								</div>
								<section>
									<?php
									// If the API key is not present, display the sign-up form.
									if ( empty( $foopilot_api_key ) ) {
										echo esc_html( $this->display_foopilot_signup_form_html() );
									} else {
										echo esc_html( $this->display_foopilot_content_html() );
									}
									?>
								</section>
								<div class="foogallery-foopilot-modal-toolbar">
									<div class="foogallery-foopilot-modal-toolbar-inner">
										<div class="media-toolbar-secondary">
											<a href="#"
											class="foogallery-foopilot-modal-cancel button"
											title="<?php esc_attr_e( 'Cancel', 'foogallery' ); ?>"><?php _e( 'Cancel', 'foogallery' ); ?></a>
										</div>
										<div class="media-toolbar-primary">
											<a href="#"
											class="foogallery-foopilot-modal-insert button"
											disabled="disabled"
											title="<?php esc_attr_e( 'OK', 'foogallery' ); ?>"><?php _e( 'OK', 'foogallery' ); ?></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Display FooPilot sign-up form HTML.
		 */
		public function display_foopilot_signup_form_html() {
			ob_start();
			?>
			<div class="foogallery-foopilot-signup-form">
				<div class="foogallery-foopilot-signup-form-inner">
					<p><?php esc_html_e( 'Unlock the power of FooPilot! Sign up for free and get 20 credits to explore our service.', 'foogallery' ); ?></p>
					<form class="foogallery-foopilot-signup-form-inner-content">
						<div style="margin-bottom: 20px;">
							<input type="email" id="foopilot-email" name="email" placeholder="<?php echo esc_attr(__( 'Enter your email', 'foogallery' ) ); ?>" value="<?php echo esc_attr(foogallery_sanitize_javascript(wp_get_current_user()->user_email) ); ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 250px;">
						</div>
						<button class="foogallery-foopilot-signup-form-inner-content-button button button-primary button-large" type="submit" style="padding: 10px 20px; background-color: #0073e6; color: #fff; border: none; border-radius: 5px; cursor: pointer;"><?php esc_html_e( 'Sign Up for free', 'foogallery' ); ?></button>
					</form>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Display FooPilot content HTML.
		 */
		public function display_foopilot_content_html() {
			ob_start();
			?>
			<div class="foogallery-foopilot-modal-sidebar">
				<?php echo esc_html( $this->display_foopilot_settings_html() ); ?>
			</div>
			<div class="foogallery-foopilot-modal-container">
				<div class="foogallery-foopilot-modal-container-inner">
					<?php echo esc_html( $this->display_foopilot_selected_task_html() ); ?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Generate foopilot settings HTML.
		 */
		public function display_foopilot_settings_html() {
			ob_start();
			?>
				<div class="foogallery-foopilot-modal-sidebar-menu">
					<a href="#" class="media-menu-item foogallery-foopilot" data-task="tags"><?php esc_html_e( 'Generate Tags', 'foogallery' ); ?></a>
					<a href="#" class="media-menu-item foogallery-foopilot" data-task="caption"><?php esc_html_e( 'Generate Caption', 'foogallery' ); ?></a>
					<a href="#" class="media-menu-item foogallery-foopilot" data-task="credit"><?php esc_html_e( 'Buy Credits', 'foogallery' ); ?></a>
				</div>
			<?php
			return ob_get_clean();
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
			// Verify nonce and user permissions.
			$foopilot_nonce = isset( $_POST['foopilot_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foopilot_nonce'] ) ) : '';

			if ( wp_verify_nonce( $foopilot_nonce, 'foopilot_nonce' ) ) {
				// Retrieve task from POST data.
				$task = isset( $_POST['task'] ) ? sanitize_text_field( wp_unslash( $_POST['task'] ) ) : '';

				// Include the appropriate PHP file based on the task.
				if ( 'tag' === $task ) {
					require_once FOOGALLERY_PATH . 'includes/admin/foopilot/tasks/class-foopilot-generate-tags.php';
					echo esc_html( FooGallery_Admin_Foopilot_Generate_Tags::get_foopilot_generate_tags_html() );
				} elseif ( 'caption' === $task ) {
					require_once FOOGALLERY_PATH . 'includes/admin/foopilot/tasks/class-foopilot-generate-caption.php';
					echo esc_html( FooGallery_Admin_Foopilot_Generate_Caption::get_foopilot_generate_caption_html() );
				} elseif ( 'credit' === $task ) {
					require_once FOOGALLERY_PATH . 'includes/admin/foopilot/tasks/class-foopilot-generate-credit.php';
					echo esc_html( FooGallery_Admin_Foopilot_Generate_Credit::get_foopilot_generate_credit_html() );
				} else {
					// Handle unknown task.
					echo esc_html__( 'Task not found', 'fogallery' );
				}
				wp_die();
			} else {
				wp_die( 'Unauthorized request!' );
			}
		}

		/**
		 * Generate foopilot selected task HTML.
		 */
		public function display_foopilot_selected_task_html() {
			ob_start();
			?>
			<div class="foopilot-task-html" style="display: flex; justify-content: center; align-items:center; text-align:center; color: black;">
				
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Generate Foopilot api keys
		 */
		public function generate_random_api_key() {
			// Verify the nonce.
			$foopilot_nonce = isset( $_POST['foopilot_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foopilot_nonce'] ) ) : '';

			if ( wp_verify_nonce( $foopilot_nonce, 'foopilot_nonce' ) ) {
				$current_points = $this->get_foopilot_credit_points();

				// If the current points balance is greater than 0, reset it to 0.
				if ( $current_points > 0 ) {
					update_option( 'foopilot_credit_points', 0 );
				}

				// Credit the registered user +20 points.
				$this->add_foopilot_credit_points( 20 );

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
			} else {
				wp_die( 'Unauthorized request!' );
			}
		}

		/**
		 * Function to initialize credit points
		 */
		public function initialize_foopilot_credit_points() {
			// Check if the credit points are already set, if not, set it to 0.
			if ( ! get_option( 'foopilot_credit_points' ) ) {
				update_option( 'foopilot_credit_points', 0 );
			}
		}

		/**
		 * Function to retrieve credit points
		 */
		public function get_foopilot_credit_points() {
			return get_option( 'foopilot_credit_points', 0 );
		}

		/**
		 * Function to add credit points
		 *
		 * @param int $points    The points to be added.
		 */
		public function add_foopilot_credit_points( $points ) {
			$current_points = $this->get_foopilot_credit_points();
			$updated_points = $current_points + $points;
			update_option( 'foopilot_credit_points', $updated_points );
		}

		/**
		 * Add FooPilot settings to the provided settings array.
		 *
		 * This function adds foopilot-related settings for the foogallery Box Slider section.
		 *
		 * @param array $settings An array of existing settings.
		 *
		 * @return array The modified settings array with added foopilot settings.
		 */
		public function add_foopilot_settings( $settings ) {

			$settings['settings'][] = array(
				'id'      => 'foopilot_api_key',
				'title'   => __( 'FooPilot API key', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '', 'foogallery' ),
				'tab'     => 'FooPilot',
			);

			return $settings;
		}
	}
}
