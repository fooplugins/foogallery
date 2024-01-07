<?php
/*
 * FooGallery Admin Menu class
 */

if ( ! class_exists( 'FooGallery_Admin_Menu' ) ) {

	class FooGallery_Admin_Menu {

		function __construct() {
			add_action( 'admin_menu', array( $this, 'register_menu_items' ) );

			add_action( 'wp_ajax_foogallery_admin_import_demos', array( $this, 'create_demo_galleries' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Load the foogallery assets on the help page
		 *
		 * @param $hook
		 */
		function enqueue_scripts( $hook ) {
			if ( 'foogallery_page_foogallery-help' === $hook ) {
				foogallery_enqueue_core_gallery_template_script( array('jquery', 'masonry' ) );
				foogallery_enqueue_core_gallery_template_style();
			}
		}

		/**
		 * @todo add context to the translations
		 */
		function register_menu_items() {
			//we rely on the register_post_type call to add our main menu items
			$parent_slug = foogallery_admin_menu_parent_slug();

			//allow extensions to add their own menu items beforehand
			do_action( 'foogallery_admin_menu_before' );

			$menu_labels = apply_filters( 'foogallery_admin_menu_labels',
				array(
					array(
						'page_title' => sprintf( __( '%s Settings', 'foogallery' ), foogallery_plugin_name() ),
						'menu_title' => __( 'Settings', 'foogallery' ),
					),
					array(
						'page_title' => sprintf( __( '%s Features', 'foogallery' ), foogallery_plugin_name() ),
						'menu_title' => __( 'Features', 'foogallery' ),
					),
					array(
						'page_title' => sprintf( __( '%s Help', 'foogallery' ), foogallery_plugin_name() ),
						'menu_title' => __( 'Help', 'foogallery' ),
					),
					array(
						'page_title' => sprintf( __( '%s System Information', 'foogallery' ), foogallery_plugin_name() ),
						'menu_title' => __( 'System Info', 'foogallery' ),
					),
				)
			);

			$capability = apply_filters( 'foogallery_admin_menu_capability', 'manage_options' );

			add_submenu_page( $parent_slug, $menu_labels[0]['page_title'], $menu_labels[0]['menu_title'], $capability, 'foogallery-settings', array( $this, 'foogallery_settings' ) );
			add_submenu_page( $parent_slug, $menu_labels[1]['page_title'], $menu_labels[1]['menu_title'], $capability, 'foogallery-features', array( $this, 'foogallery_features' ) );
			add_submenu_page( $parent_slug, $menu_labels[2]['page_title'], $menu_labels[2]['menu_title'], $capability, 'foogallery-help', array( $this, 'foogallery_help' ) );

			if ( current_user_can( 'activate_plugins' ) ) {
				add_submenu_page( $parent_slug, $menu_labels[3]['page_title'], $menu_labels[3]['menu_title'], $capability, 'foogallery-systeminfo', array( $this, 'foogallery_systeminfo' ) );
			}

			//allow extensions to add their own menu items afterwards
			do_action( 'foogallery_admin_menu_after' );
		}

		function foogallery_settings() {

			$admin_errors = get_transient( 'settings_errors' );
			$show_reset_message = false;

			if ( is_array( $admin_errors ) ) {
				//try to find a reset 'error'
				foreach ( $admin_errors as $error ) {
					if ( 'reset' === $error['setting'] ) {
						$show_reset_message = true;
						break;
					}
				}
			}

			if ( $show_reset_message ) {
				do_action( 'foogallery_settings_reset' );
				?>
				<div id="message" class="updated">
					<p><strong><?php printf( __( '%s settings reset to defaults.', 'foogallery' ), foogallery_plugin_name() ); ?></strong></p>
				</div>
			<?php } else if ( isset($_GET['settings-updated']) ) { ?>
				<div id="message" class="updated">
					<p><strong><?php printf( __( '%s settings updated.', 'foogallery' ), foogallery_plugin_name() ); ?></strong></p>
				</div>
			<?php }

			$instance = FooGallery_Plugin::get_instance();
			$instance->admin_settings_render_page();
		}

		function foogallery_features() {
			require_once FOOGALLERY_PATH . 'includes/admin/view-features.php';
		}

		function foogallery_help() {
			require_once FOOGALLERY_PATH . 'includes/admin/view-help.php';
		}

		function foogallery_systeminfo() {
			require_once FOOGALLERY_PATH . 'includes/admin/view-system-info.php';
		}

		function create_demo_galleries() {
			if ( check_admin_referer( 'foogallery_admin_import_demos' ) ) {

				$results = foogallery_create_demo_content();

				if ( $results === false ) {
					echo __('There was a problem creating the demo galleries!', 'foogallery');
				} else {
					echo sprintf(__('%d sample images imported, and %d demo galleries created!', 'foogallery'), $results['attachments'], $results['galleries']);
				}
			}
			die();
		}
	}
}
