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
			new FooGallery_Album_Shortcodes();

			if ( is_admin() ) {
				new FooGallery_Albums_Admin_Columns();
				new FooGallery_Admin_Album_MetaBoxes();

				//add some global settings for albums
				add_filter( 'foogallery_admin_settings_override', array($this, 'add_album_settings' ) );

				add_action( 'foogallery_uninstall', array($this, 'uninstall' ) );
			}
			add_filter( 'foogallery_album_templates_files', array( $this, 'register_myself' ) );
			add_filter( 'foogallery_defaults', array( $this, 'apply_album_defaults' ) );
			add_action( 'foogallery_extension_activated-albums', array( $this, 'flush_rewrite_rules' ) );
			add_filter( 'foogallery_albums_supports_video-stack', '__return_true' );

			add_filter( 'fooboxshare_use_permalink', array( $this, 'check_for_albums_for_fooboxshare' ) );

			add_action( 'foogallery_located_album_template-stack', array( $this, 'load_stack_assets' ) );

			add_filter( 'foogallery_album_default_gallery_content', array( $this, 'render_gallery_description' ), 10, 2 );

			add_filter( 'foogallery_gallery_posttype_register_args', array( $this, 'override_gallery_posttype_register_args' ) );

			add_filter( 'foogallery_allowed_post_types_for_attachment', array( $this, 'allow_albums' ) );
		}

		/**
		 * Add the album post type to the allowed list of post types that galleries can be attached to.
		 * This will then show albums in the Usage column for the galleries
		 *
		 * @param array $allowed The allowed list of post types.
		 *
		 * @return array
		 */
		public function allow_albums( $allowed ) {
			$allowed[] = FOOGALLERY_CPT_ALBUM;
			return $allowed;
		}

		/**
		 * Overrides the gallery posttype register args
		 *
		 * @param array $args The arguments.
		 *
		 * @return array
		 */
		public function override_gallery_posttype_register_args( $args ) {
			if ( 'on' === foogallery_get_setting( 'enable_gallery_descriptions' ) ) {
				$args['supports'][] = 'editor';
			}
			return $args;
		}

		/**
		 * Render the gallery description
		 *
		 * @param string     $content    The default content to be rendered.
		 * @param FooGallery $foogallery The gallery we are showing.
		 *
		 * @return string
		 */
		public function render_gallery_description( $content, $foogallery ) {
			if ( 'on' === foogallery_get_setting( 'enable_gallery_descriptions' ) ) {
				if ( isset( $foogallery->_post ) && ! empty( $foogallery->_post->post_content ) ) {
					$content = apply_filters( 'the_content', $foogallery->_post->post_content );
				}
			}

			return $content;
		}

		function load_stack_assets( $current_foogallery_album ) {
			foogallery_enqueue_core_gallery_template_script();
			foogallery_enqueue_core_gallery_template_style();
		}

		function check_for_albums_for_fooboxshare( $default ) {

			$album_gallery = foogallery_album_get_current_gallery();

			if ( !empty( $album_gallery) ) {
				return false;
			}

			return $default;
		}

		function includes() {
			require_once FOOGALLERY_ALBUM_PATH . 'functions.php';
			require_once FOOGALLERY_ALBUM_PATH . 'class-posttypes.php';
			require_once FOOGALLERY_ALBUM_PATH . 'class-foogallery-album.php';
			require_once FOOGALLERY_ALBUM_PATH . 'public/class-rewrite-rules.php';
			require_once FOOGALLERY_ALBUM_PATH . 'public/class-shortcodes.php';
			require_once FOOGALLERY_ALBUM_PATH . 'public/class-foogallery-album-template-loader.php';

			if ( is_admin() ) {
				// only admin.
				require_once FOOGALLERY_ALBUM_PATH . 'admin/class-metaboxes.php';
				require_once FOOGALLERY_ALBUM_PATH . 'admin/class-columns.php';
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

		function add_album_settings( $settings ) {

			$settings['tabs']['albums'] = __( 'Albums', 'foogallery' );

			$settings['settings'][] = array(
				'id'      => 'album_gallery_slug',
				'title'   => __( 'Gallery Slug', 'foogallery' ),
				'type'    => 'text',
				'default' => 'gallery',
				'desc'    => __( 'The slug that is used when generating gallery URL\'s for albums. PLEASE NOTE : if you change this value, you might need to save your Permalinks again.', 'foogallery' ),
				'tab'     => 'albums',
			);

			$settings['settings'][] = array(
				'id'      => 'language_back_to_album_text',
				'title'   => __( 'Back To Album Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( '&laquo; back to album', 'foogallery' ),
				'tab'     => 'albums',
			);

			$settings['settings'][] = array(
				'id'    => 'enable_gallery_descriptions',
				'title' => __( 'Enable Gallery Descriptions', 'foogallery' ),
				'desc'  => __( 'Enable descriptions for galleries. These descriptions will be displayed under the gallery title within the Responsive Album Layout.', 'foogallery' ),
				'type'  => 'checkbox',
				'tab'   => 'albums',
			);

            $settings['settings'][] = array(
                'id'      => 'album_limit_galleries',
                'title'   => __( 'Limit Galleries In Admin', 'foogallery' ),
                'desc'  => __( 'Limit the number of galleries shown in the admin when editing an album.', 'foogallery' ),
                'type'    => 'text',
                'default' => '',
                'tab'     => 'albums',
            );


			return $settings;
		}

		function uninstall() {
			foogallery_album_uninstall();
		}
	}
}
