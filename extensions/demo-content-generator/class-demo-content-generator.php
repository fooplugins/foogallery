<?php
/**
 * Created by Brad Vincent.
 * Date: 04/03/2018
 */
if ( ! class_exists( 'FooGallery_Demo_Content_Generator' ) ) {

	class FooGallery_Demo_Content_Generator {
		function __construct() {
			//always show the menu
			add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );
			add_action( 'foogallery_extension_activated-demo-content', array( $this, 'add_menu' ) );
		}

		function add_menu() {
			foogallery_add_submenu_page( __( 'Demo Content', 'foogallery' ), 'manage_options', 'foogallery-demo-content', array(
				$this,
				'render_view',
			) );
		}

		function render_view() {
			require_once 'view-demo-content.php';
		}

		static function generate( $query ) {
			require_once 'includes/class-pixabay.php';

			$client = new FooGallery_PixabayClient();
			$key = apply_filters( 'foogallery_pixabay_key', '1843003-12be68cf2726df47797f19cd7' );

			$results = $client->search( $key, $query );

			$hits = $results->hits;

			return 'found ' . count($hits);
		}
	}
}