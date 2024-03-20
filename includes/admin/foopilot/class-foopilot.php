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
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			$points_manager = new FooGallery_Admin_FooPilot_Points_Manager();
			new FooGallery_Admin_FooPilot_Ajax_Handler( $points_manager );
			new FooGallery_Admin_FooPilot_Modal( $points_manager );
		}

		/**
		 * Enqueue scripts and styles.
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'foogallery.admin.foopilot', FOOGALLERY_URL . 'includes/admin/foopilot/css/foopilot-modal.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.foopilot', FOOGALLERY_URL . 'includes/admin/foopilot/js/foopilot-modal.js', array( 'jquery' ), FOOGALLERY_VERSION );
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
