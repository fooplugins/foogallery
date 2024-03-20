<?php
/**
 * FooGallery Admin FooPilot Modal class
 *
 * @package   FooGallery
 */

if ( ! class_exists( 'FooGallery_Admin_FooPilot_Modal' ) ) {

	/**
	 * FooGallery Admin FooPilot Modal class
	 */
	class FooGallery_Admin_FooPilot_Modal {
		/**
		 * Property to hold an instance of FooGallery_Admin_FooPilot_Points_Manager.
		 */
		private $points_manager;

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
			add_action( 'admin_footer', array( $this, 'display_foopilot_modal_html' ) );
			$this->points_manager = new FooGallery_Admin_FooPilot_Points_Manager();
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
										require_once FOOGALLERY_PATH . 'includes/admin/foopilot/modals/foopilot-modal-content.php';
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
	}
}