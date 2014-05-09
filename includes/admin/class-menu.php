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
			if ( foogallery_use_media_menu() ) {

				add_media_page( __( 'Galleries', 'foogallery' ), __( 'Galleries', 'foogallery' ), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
				add_media_page( __( 'Add Gallery', 'foogallery' ), __( 'Add Gallery', 'foogallery' ), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY );
				//add_media_page( __('Albums', 'foogallery'), __('Albums', 'foogallery'), 'upload_files', 'edit-tags.php?taxonomy=' . FOOGALLERY_TAX_ALBUM );
				add_media_page( __( 'FooGallery Settings', 'foogallery' ), __( 'Gallery Settings', 'foogallery' ), 'manage_options', 'foogallery-settings', array($this, 'foogallery_settings') );

			} else {
				//rely on the register_post_type call to add our main menu items
				add_submenu_page( 'edit.php?post_type=foogallery', __( 'FooGallery Settings', 'foogallery' ), __( 'Settings', 'foogallery' ), 'manage_options', 'foogallery-settings', array($this, 'foogallery_settings') );
				add_submenu_page( 'edit.php?post_type=foogallery', __( 'FooGallery Extensions', 'foogallery' ), __( 'Extensions', 'foogallery' ), 'manage_options', 'foogallery-extensions', array($this, 'foogallery_extensions') );
				add_submenu_page( 'edit.php?post_type=foogallery', __( 'FooGallery Help', 'foogallery' ), __( 'Help', 'foogallery' ), 'manage_options', 'foogallery-help', array($this, 'foogallery_help') );

			}

			do_action( 'foogallery_admin_menu' );
		}

		function foogallery_settings() {
			if( isset($_GET['settings-updated']) ) { ?>
	<div id="message" class="updated">
		<p><strong><?php _e('FooGallery settings updated.', 'foogallery') ?></strong></p>
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