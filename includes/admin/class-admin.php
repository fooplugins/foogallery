<?php
/*
 * FooGallery Admin class
 */

if (!class_exists('FooGallery_Admin')) {

	class FooGallery_Admin {

		function __construct() {
			add_action( 'init', array($this, 'init') );

			new FooGallery_Admin_Settings();
			new FooGallery_Admin_Menu();
			new FooGallery_Admin_MetaBoxes();
			new FooGallery_Admin_Columns();
		}

		function init() {
			add_filter( 'media_upload_tabs', array($this, 'add_media_manager_tab') );
			add_action( 'media_upload_foo_gallery', array($this, 'media_manager_iframe_content') );
//			add_filter( 'media_view_strings', array($this, 'custom_media_string'), 10, 2);
			add_filter( 'foogallery-has_settings_page', '__return_false' );
		}

		function custom_media_string($strings,  $post){
			$strings['customMenuTitle'] = __('Custom Menu Title', 'custom');
			$strings['customButton'] = __('Custom Button', 'custom');
			return $strings;
		}

		function add_media_manager_tab($tabs) {
			$newtab = array( 'foo_gallery' => __('Insert FooGallery', '') );
			return array_merge( $tabs, $newtab );
		}

		function media_manager_iframe() {
			return wp_iframe( array($this, 'media_manager_iframe_content') );
		}

		function media_manager_iframe_content() {
			echo media_upload_header();
			echo 'Still under development!';
			return;
			?>
			<div class="media-frame-router">
				<div class="media-router">
					<a href="#" class="media-menu-item">Select Gallery</a>
					<a href="#" class="media-menu-item active">Create New Gallery</a>
				</div>
			</div>
			<div class="media-frame-toolbar">
				<div class="media-toolbar">
					<div class="media-toolbar-primary">
						<a href="#" class="button media-button button-primary button-large media-button-insert" disabled="disabled">Insert into page</a>
					</div>
				</div>
			</div>
		<?php
		}
	}
}