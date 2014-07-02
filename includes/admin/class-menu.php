<?php
/*
 * FooGallery Admin Menu class
 */

if (!class_exists('FooGallery_Admin_Menu')) {



	class FooGallery_Admin_Menu {

		function __construct() {
			add_action( 'admin_menu', array($this, 'register_menu_items') );
		}

		function register_menu_items() {
			//we rely on the register_post_type call to add our main menu items
			$parent_slug = foogallery_admin_menu_parent_slug();

			//allow extensions to add their own menu items beforehand
			do_action( 'foogallery_admin_menu_before' );

			$menu_labels = apply_filters( 'foogallery_admin_menu_labels',
				array(
					array(
						'page_title' => __( 'FooGallery Settings', 'foogallery' ),
						'menu_title' => __( 'Settings', 'foogallery' )
					),
					array(
						'page_title' => __( 'FooGallery Extensions', 'foogallery' ),
						'menu_title' => __( 'Extensions', 'foogallery' )
					),
					array(
						'page_title' => __( 'FooGallery Help', 'foogallery' ),
						'menu_title' => __( 'Help', 'foogallery' )
					)
				)
			);

			$capability = apply_filters('foogallery_admin_menu_capability', 'manage_options');

			add_submenu_page( $parent_slug, $menu_labels[0]['page_title'], $menu_labels[0]['menu_title'], $capability, 'foogallery-settings', array($this, 'foogallery_settings') );
			add_submenu_page( $parent_slug, $menu_labels[1]['page_title'], $menu_labels[1]['menu_title'], $capability, 'foogallery-extensions', array($this, 'foogallery_extensions') );
			add_submenu_page( $parent_slug, $menu_labels[2]['page_title'], $menu_labels[2]['menu_title'], $capability, 'foogallery-help', array($this, 'foogallery_help') );

      		//allow extensions to add their own menu items afterwards
			do_action( 'foogallery_admin_menu_after' );
		}

		function foogallery_settings() {
			if( isset($_GET['settings-updated']) ) { ?>
	<div id="message" class="updated">
		<p><strong><?php _e('FooGallery settings updated.', 'foogallery'); ?></strong></p>
	</div>
			<?php }

			$instance = FooGallery_Plugin::get_instance();
			$instance->admin_settings_render_page();
		}

		function foogallery_extensions() {
			require_once FOOGALLERY_PATH . 'includes/admin/view-extensions.php';
		}

		function foogallery_help() {
			require_once FOOGALLERY_PATH . 'includes/admin/view-help.php';
		}
	}
}