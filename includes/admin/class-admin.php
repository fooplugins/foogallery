<?php
/*
 * FooGallery Admin class
 */

if ( ! class_exists( 'FooGallery_Admin' ) ) {

	/**
	 * Class FooGallery_Admin
	 */
	class FooGallery_Admin {

		/**
		 *
		 */
		function __construct() {
			global $foogallery_admin_datasource_instance;

			//init some other actions
			add_action( 'init', array( $this, 'init' ) );

			new FooGallery_Admin_Settings();
			new FooGallery_Admin_Menu();
			new FooGallery_Admin_Gallery_Editor();
			new FooGallery_Admin_Gallery_MetaBoxes();
			new FooGallery_Admin_Gallery_MetaBox_Items();
			new FooGallery_Admin_Gallery_MetaBox_Settings();
			new FooGallery_Admin_Gallery_MetaBox_Fields();
			new FooGallery_Admin_Columns();
			new FooGallery_Admin_Extensions();
			new FooGallery_Attachment_Fields();
			new FooGallery_Admin_Notices();
			new FooGallery_Admin_Gallery_Attachment_Modal();
			$foogallery_admin_datasource_instance = new FooGallery_Admin_Gallery_Datasources();

			// include PRO promotion.
			new FooGallery_Pro_Promotion();
		}

		function init() {
			add_filter( 'foogallery_admin_has_settings_page', '__return_false' );
			add_action( 'foogallery_admin_print_styles', array( $this, 'admin_print_styles' ) );
			add_action( 'foogallery_admin_print_scripts', array( $this, 'admin_print_scripts' ) );
			// Add a links to the plugin listing
			add_filter( 'foogallery_admin_plugin_action_links', array( $this, 'plugin_listing_links' ) );
			//output shortcode for javascript
			add_action( 'admin_footer', array( $this, 'output_shortcode_variable' ), 200 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

			add_filter( 'fs_show_trial_foogallery', array( $this, 'force_trial_hide' ) );
			add_action( 'admin_init', array( $this, 'force_hide_trial_notice' ), 99 );
		}

		public function enqueue_scripts_and_styles( $hook ) {
			//check if the gallery edit page is being shown
			$screen = get_current_screen();
			if ( 'foogallery' !== $screen->id ) {
				return;
			}

			foogallery_enqueue_core_gallery_template_script();
			foogallery_enqueue_core_gallery_template_style();

            $foogallery = FooGallery_Plugin::get_instance();
            $foogallery->register_and_enqueue_js( 'admin-foogallery-edit.js' );

			do_action('foogallery_admin_enqueue_scripts' );
		}

		function admin_print_styles() {
			$page       = safe_get_from_request( 'page' );
			$foogallery = FooGallery_Plugin::get_instance();
			$foogallery->register_and_enqueue_css( 'admin-page-' . $page . '.css' );
		}

		function admin_print_scripts() {
			$page       = safe_get_from_request( 'page' );
			$foogallery = FooGallery_Plugin::get_instance();
			$foogallery->register_and_enqueue_js( 'admin-page-' . $page . '.js' );
		}

		/**
		 * @param $links
		 *
		 * @return string[]
		 */
		function plugin_listing_links( $links ) {
            if ( !is_array( $links ) ) {
	            $links = array();
            }

			// Add a 'Settings' link to the plugin listing
			$links[] = '<a href="' . esc_url( foogallery_admin_settings_url() ) . '"><b>' . __( 'Settings', 'foogallery' ) . '</b></a>';

			$links[] = '<a href="' . esc_url( foogallery_admin_help_url() ) . '"><b>' . __( 'Help', 'foogallery' ) . '</b></a>';

			return $links;
		}

		function output_shortcode_variable() {
			if ( foogallery_gallery_shortcode_tag() != FOOGALLERY_CPT_GALLERY ) {
				?>
				<script type="text/javascript">
					window.FOOGALLERY_SHORTCODE = '<?php echo foogallery_gallery_shortcode_tag(); ?>';
				</script>
			<?php
			}
		}

		function force_trial_hide( $show_trial ) {
			if ( 'on' === foogallery_get_setting( 'force_hide_trial', false ) ) {
				$show_trial = false;
			}

			return $show_trial;
		}

		function force_hide_trial_notice() {
			if ( 'on' === foogallery_get_setting( 'force_hide_trial', false ) ) {
				$freemius_sdk = foogallery_fs();
				$plugin_id    = $freemius_sdk->get_slug();
				$admin_notice_manager = FS_Admin_Notice_Manager::instance( $plugin_id );
				$admin_notice_manager->remove_sticky( 'trial_promotion' );
			}
		}
	}
}
