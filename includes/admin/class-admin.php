<?php
/*
 * FooGallery Admin class
 */

if ( !class_exists( 'FooGallery_Admin' ) ) {

	/**
	 * Class FooGallery_Admin
	 */
	class FooGallery_Admin {

		/**
		 *
		 */
		function __construct() {
			add_action( 'init', array($this, 'init') );

			new FooGallery_Admin_Settings();
			new FooGallery_Admin_Menu();
			new FooGallery_Admin_Gallery_Editor();
			new FooGallery_Admin_Gallery_MetaBoxes();
			new FooGallery_Admin_Gallery_MetaBox_Fields();
			new FooGallery_Admin_Columns();
			new FooGallery_Admin_Extensions();
			new FooGallery_Boilerplate_Download_Handler();
		}

		function init() {

			//the following will be implemented in a future version
//			add_filter( 'media_upload_tabs', array($this, 'add_media_manager_tab') );
//			add_action( 'media_upload_foo_gallery', array($this, 'media_manager_iframe_content') );
//			add_filter( 'media_view_strings', array($this, 'custom_media_string'), 10, 2);

			add_filter( 'foogallery_admin_has_settings_page', '__return_false' );
			add_action( 'foogallery_admin_print_styles', array($this, 'admin_print_styles') );
			add_action( 'foogallery_admin_print_scripts', array($this, 'admin_print_scripts') );
			// Add a links to the plugin listing
			add_filter( 'foogallery_admin_plugin_action_links', array($this, 'plugin_listing_links') );
		}

		/**
		 * @param $strings
		 * @param $post
		 *
		 * @return mixed
		 */
		function custom_media_string($strings, $post) {
			$strings['customMenuTitle'] = __( 'Custom Menu Title', 'custom' );
			$strings['customButton']    = __( 'Custom Button', 'custom' );

			return $strings;
		}

		/**
		 * @param $tabs
		 *
		 * @return array
		 */
		function add_media_manager_tab($tabs) {
			$newtab = array('foo_gallery' => __( 'Insert FooGallery', '' ));

			return array_merge( $tabs, $newtab );
		}

		function media_manager_iframe() {
			return wp_iframe( array($this, 'media_manager_iframe_content') );
		}

		function media_manager_iframe_content() {
			echo media_upload_header();
			//echo 'Still under development!';

			//return;
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
						<a href="#" class="button media-button button-primary button-large media-button-insert"
						   disabled="disabled">Insert into page</a>
					</div>
				</div>
			</div>
		<?php
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
		 * @return array
		 */
		function plugin_listing_links($links) {
			// Add a 'Settings' link to the plugin listing
			$links[] = '<a href="' . foogallery_admin_settings_url() . '"><b>' . __( 'Settings', 'foogallery' ) . '</b></a>';

			$links[] = '<a href="' . foogallery_admin_extensions_url() . '"><b>' . __( 'Extensions', 'foogallery' ) . '</b></a>';

			$links[] = '<a href="' . foogallery_admin_help_url() . '"><b>' . __( 'Help', 'foogallery' ) . '</b></a>';

			return $links;
		}
	}
}
