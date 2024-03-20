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
		 * Property to hold an instance of FooGallery_Admin_FooPilot_Points_Manager.
		 */
		private $points_manager;

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_foopilot_settings' ), 50 );
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			add_action( 'admin_footer', array( $this, 'display_foopilot_modal_html' ) );
			$this->points_manager = new FooGallery_Admin_FooPilot_Points_Manager();
			new FooGallery_Admin_FooPilot_Ajax_Handler();
		}

		/**
		 * Enqueue scripts and styles.
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'foogallery.admin.foopilot', FOOGALLERY_URL . 'includes/admin/foopilot/css/foopilot-modal.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.foopilot', FOOGALLERY_URL . 'includes/admin/foopilot/js/foopilot-modal.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}

		/**
		 * Generate foopilot modal HTML.
		 */
		public function display_foopilot_modal_html() {
			// Check if the FooPilot API key is present.
			$foopilot_api_key = foogallery_get_setting( 'foopilot_api_key' );
			$credit_points    = $this->points_manager->get_foopilot_credit_points();
			?>
			<div id="foopilot-modal" class="foogallery-foopilots-modal-wrapper" data-nonce="<?php esc_attr( foopilot_generate_nonce() ); ?>" style="display: none;">
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
											echo '<button class="buy-credits button button-primary button-small" data-task="credits" style="margin-left: 10px;">' . esc_html__( 'Buy credits', 'foogallery' ) . '</button>';
										}
										?>
									</h3>
								</div>
								<section>
									<?php
									// If the API key is not present, display the sign-up form.
									if ( empty( $foopilot_api_key ) ) {
										require_once FOOGALLERY_PATH . 'includes/admin/foopilot/modals/foopilot-sign-up-form.php';
									} else {
										echo $this->display_foopilot_content_html();
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
		 * Display FooPilot content HTML.
		 */
		public function display_foopilot_content_html() {
			ob_start();
			?>
			<div class="foogallery-foopilot-modal-sidebar">
				<?php echo $this->display_foopilot_settings_html(); ?>
			</div>
			<div class="foogallery-foopilot-modal-container">
				<div class="foogallery-foopilot-modal-container-inner">
					<?php echo $this->display_foopilot_selected_task_html(); ?>
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
					<a href="#" class="media-menu-item foogallery-foopilot" data-task="captions"><?php esc_html_e( 'Generate Caption', 'foogallery' ); ?></a>
					<a href="#" class="media-menu-item foogallery-foopilot" data-task="credits"><?php esc_html_e( 'Buy Credits', 'foogallery' ); ?></a>
				</div>
			<?php
			return ob_get_clean();
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
