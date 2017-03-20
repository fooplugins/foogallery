<?php
if ( ! class_exists( 'FooGallery_Albums_Extension' ) ) {

	define( 'FOOGALLERY_ALBUM_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOGALLERY_ALBUM_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOGALLERY_CPT_ALBUM', 'foogallery-album' );
	define( 'FOOGALLERY_ALBUM_META_GALLERIES', 'foogallery_album_galleries' );
	define( 'FOOGALLERY_ALBUM_META_TEMPLATE', 'foogallery_album_template' );
	define( 'FOOGALLERY_ALBUM_META_SORT', 'foogallery_album_sort' );

	class FooGallery_Albums_Extension {

		function __construct() {
			$this->includes();

			new FooGallery_Album_Rewrite_Rules();
			new FooGallery_Albums_PostTypes();

			if ( is_admin() ) {
				new FooGallery_Albums_Admin_Columns();
				new FooGallery_Admin_Album_MetaBoxes();

				//add language settings
				add_filter( 'foogallery_admin_settings_override', array( $this, 'include_album_language_settings' ) );

				//add some global settings for albums
				add_filter( 'foogallery_admin_settings_override', array($this, 'add_album_settings' ) );

				add_action( 'foogallery_uninstall', array($this, 'uninstall' ) );

			} else {

				new FooGallery_Album_Template_Loader();
				new FooGallery_Album_Shortcodes();
			}
			add_filter( 'foogallery_album_templates_files', array( $this, 'register_myself' ) );
			add_filter( 'foogallery_defaults', array( $this, 'apply_album_defaults' ) );
			add_action( 'foogallery_extension_activated-albums', array( $this, 'flush_rewrite_rules' ) );
			add_filter( 'foogallery_alter_album_template_field', array( $this, 'alter_gallery_template_field' ), 10, 2 );
		}

		function includes() {
			require_once( FOOGALLERY_ALBUM_PATH . 'functions.php' );
			require_once( FOOGALLERY_ALBUM_PATH . 'class-posttypes.php' );
			require_once( FOOGALLERY_ALBUM_PATH . 'class-foogallery-album.php' );
			require_once( FOOGALLERY_ALBUM_PATH . 'public/class-rewrite-rules.php' );

			if ( is_admin() ) {
				//only admin
				require_once( FOOGALLERY_ALBUM_PATH . 'admin/class-metaboxes.php' );
				require_once( FOOGALLERY_ALBUM_PATH . 'admin/class-columns.php' );
			} else {
				//only front-end
				require_once( FOOGALLERY_ALBUM_PATH . 'public/class-shortcodes.php' );

				//load Template \ Loader files
				require_once( FOOGALLERY_ALBUM_PATH . 'public/class-foogallery-album-template-loader.php' );
			}
		}

		function apply_album_defaults( $defaults ) {
			$defaults['album_template'] = 'default';

			return $defaults;
		}

		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		function flush_rewrite_rules() {
			$rewrite = new FooGallery_Album_Rewrite_Rules();
			$rewrite->add_gallery_endpoint();

			flush_rewrite_rules();
		}

		function include_album_language_settings( $settings ) {
			$settings['settings'][] = array(
				'id'      => 'language_back_to_album_text',
				'title'   => __( 'Back To Album Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '&laquo; back to album', 'foogallery' ),
				'tab'     => 'language'
			);

			return $settings;
		}

		function add_album_settings( $settings ) {

			$settings['tabs']['albums'] = __( 'Albums', 'foogallery' );

			$settings['settings'][] = array(
					'id'      => 'album_gallery_slug',
					'title'   => __( 'Gallery Slug', 'foogallery' ),
					'type'    => 'text',
					'default' => 'gallery',
					'desc'    => __( 'The slug that is used when generating gallery URL\'s for albums. PLEASE NOTE : if you change this value, you might need to save your Permalinks again.', 'foogallery' ),
					'tab'     => 'albums'
			);

			return $settings;
		}

		function uninstall() {
			foogallery_album_uninstall();
		}
	}
}
