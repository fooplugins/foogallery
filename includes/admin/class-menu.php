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

			add_media_page( __('Galleries', 'foogallery'), __('Galleries', 'foogallery'), 'upload_files', 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY);
			add_media_page( __('Add Gallery', 'foogallery'), __('Add Gallery', 'foogallery'), 'upload_files', 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY);

		}
	}
}