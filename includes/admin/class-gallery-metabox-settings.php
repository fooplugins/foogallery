<?php
/**
 * Class to handle adding the Settings metabox to a gallery
 */


if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Settings' ) ) {

    class FooGallery_Admin_Gallery_MetaBox_Settings {

        /**
         * FooGallery_Admin_Gallery_MetaBox_Settings constructor.
         */
        function __construct() {
			add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_settings_metabox' ), 8 );

            //enqueue assets for the new settings tabs
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

            //get the section slug
			add_filter( 'foogallery_gallery_settings_metabox_section_slug', array( $this, 'get_section_slug' ) );

            //set default settings tab icons
            add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons') );
        }

		public function add_settings_metabox( $post ) {
			add_meta_box(
				'foogallery_settings',
				__( 'Gallery Settings', 'foogallery' ),
				array( $this, 'render_gallery_settings_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);
		}

		public function render_gallery_settings_metabox( $post ) {
			$gallery = foogallery_admin_get_current_gallery( $post );

			//attempt to load default gallery settings from another gallery, as per FooGallery settings page
			$gallery->load_default_settings_if_new();

			$gallery = apply_filters( 'foogallery_render_gallery_settings_metabox', $gallery );

			if ( true === apply_filters( 'foogallery_should_render_gallery_settings_metabox', true, $gallery ) ) {

				$settings = new FooGallery_Admin_Gallery_MetaBox_Settings_Helper( $gallery );

				$settings->render_hidden_gallery_template_selector();

				$settings->render_gallery_settings();
			}

			do_action( 'foogallery_after_render_gallery_settings_metabox', $gallery );
		}

        /***
         * Enqueue the assets needed by the settings
         * @param $hook_suffix
         */
        function enqueue_assets( $hook_suffix ){
            if( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
                $screen = get_current_screen();

                if ( is_object( $screen ) && FOOGALLERY_CPT_GALLERY == $screen->post_type ){

					//spectrum needed for the colorpicker field
					$url = FOOGALLERY_URL . 'lib/spectrum/spectrum.js';
					wp_enqueue_script( 'foogallery-spectrum', $url, array('jquery'), FOOGALLERY_VERSION );
					$url = FOOGALLERY_URL . 'lib/spectrum/spectrum.css';
					wp_enqueue_style( 'foogallery-spectrum', $url, array(), FOOGALLERY_VERSION );

                    // Register, enqueue scripts and styles here
                    wp_enqueue_script( 'foogallery-admin-settings', FOOGALLERY_URL . 'js/foogallery.admin.min.js', array('jquery'), FOOGALLERY_VERSION );
                    wp_enqueue_style( 'foogallery-admin-settings', FOOGALLERY_URL . 'css/foogallery.admin.min.css', array(), FOOGALLERY_VERSION );
                }
            }
        }

		/**
		 * Returns the section slug that can be used in the settings tabs
		 * @param $section
		 * @return string
		 */
		function get_section_slug( $section ) {
			switch ( $section ) {
				case __('General', 'foogallery'):
					return 'general';
				case __('Advanced', 'foogallery'):
					return 'advanced';
				case __('Appearance', 'foogallery'):
					return 'appearance';
				case __('Video', 'foogallery'):
					return 'video';
				case __('Hover Effects', 'foogallery'):
					return 'hover effects';
				case __('Captions', 'foogallery'):
					return 'captions';
				case __('Paging', 'foogallery'):
					return 'paging';
			}
			return strtolower( $section );
		}

        /**
         * Returns the Dashicon that can be used in the settings tabs
         *
         * @param string $section_slug
         * @return string
        */
        function add_section_icons( $section_slug ) {
            switch ( $section_slug ) {
                case 'general':
                    return 'dashicons-format-gallery';
                case 'advanced':
                    return 'dashicons-admin-tools';
                case 'appearance':
                    return 'dashicons-admin-appearance';
                case 'video':
                    return 'dashicons-video-alt3';
                case 'hover effects':
                    return 'dashicons-star-filled';
                case 'captions':
                    return 'dashicons-editor-quote';
                case 'paging':
                    return 'dashicons-admin-page';
            }
            return $section_slug;
        }
    }
}