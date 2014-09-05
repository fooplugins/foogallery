<?php
if ( ! class_exists( 'FooGallery_Albums_Extension' ) ) {

	define( 'FOOGALLERY_ALBUM_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOGALLERY_ALBUM_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOGALLERY_CPT_ALBUM', 'foogallery-album' );
	define( 'FOOGALLERY_ALBUM_META_GALLERIES', 'foogallery_album_galleries' );
	define( 'FOOGALLERY_ALBUM_META_TEMPLATE', 'foogallery_album_template' );

	class FooGallery_Albums_Extension {

		function __construct() {
			$this->includes();

			new FooGallery_Albums_PostTypes();
			if ( is_admin() ) {
				new FooGallery_Albums_Admin_Columns();
				new FooGallery_Admin_Album_MetaBoxes();
			}

			add_filter( 'foogallery_defaults', array( $this, 'apply_album_defaults' ) );
		}

		function includes() {
			require_once( FOOGALLERY_ALBUM_PATH . 'functions.php' );
			require_once( FOOGALLERY_ALBUM_PATH . 'class-posttypes.php' );
			require_once( FOOGALLERY_ALBUM_PATH . 'class-foogallery-album.php' );

			if ( is_admin() ) {
				//only admin
				require_once( FOOGALLERY_ALBUM_PATH . 'admin/class-metaboxes.php' );
				require_once( FOOGALLERY_ALBUM_PATH . 'admin/class-columns.php' );
			} else {
				//only front-end
				//require_once( FOOGALLERY_ALBUM_PATH . 'public/class-shortcodes.php' );
				//load Template \ Loader files
				//require_once( FOOGALLERY_ALBUM_PATH . 'public/class-foogallery-template-loader.php' );
			}
		}

		function apply_album_defaults( $defaults ) {
			$defaults['album_template'] = 'default';
		}
	}
}
