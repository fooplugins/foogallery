<?php
/**
 * Adds video support within FooGallery
 */

if ( !class_exists( 'FooGallery_Pro_Video' ) ) {

	define( 'FOOGALLERY_VIDEO_POST_META', '_foogallery_video_data' );
	define( 'FOOGALLERY_VIDEO_POST_META_VIDEO_COUNT', '_foogallery_video_count' );

    require_once plugin_dir_path( __FILE__ ) . 'functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'class-foogallery-pro-video-query.php';
    require_once plugin_dir_path( __FILE__ ) . 'class-foogallery-pro-video-import.php';

    class FooGallery_Pro_Video
    {
        /**
         * Wire up everything we need
         */
        function __construct()
        {
            new FooGallery_Pro_Video_Query();
            new FooGallery_Pro_Video_Import();

            //setup script includes
            add_action( 'wp_enqueue_media', array( $this, 'enqueue_assets' ) );

			//make sure the gallery items render with a video icon
			add_filter( 'foogallery_admin_render_gallery_item_extra_classes', array( $this, 'render_gallery_item_with_video_icon' ), 10, 2 );

            //add attachment custom fields
            add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ) );

            //add extra fields to all templates
            add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'all_template_fields' ) );

            // add additional templates
            add_action( 'admin_footer', array( $this, 'add_media_templates' ) );

			//load all video info into the attachment, so that it is only done once
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'set_video_flag_on_attachment' ), 10, 2 );

            //add attributes to front-end anchor
            add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_video_link_attributes' ), 24, 3 );

			//add class to front-end item
			add_filter( 'foogallery_attachment_html_item_classes', array( $this, 'alter_video_item_attributes' ), 24, 3 );

            //add video icon class to galleries
            add_filter( 'foogallery_build_class_attribute', array( $this, 'foogallery_build_class_attribute' ) );

            //intercept gallery save and calculate how many videos are in the gallery
            add_action( 'foogallery_after_save_gallery', array( $this, 'calculate_video_count' ) );

            //change the image count to include videos if they are present in the gallery
            add_filter( 'foogallery_image_count', array( $this, 'include_video_count' ), 10, 2 );

            //check if the gallery is using foobox free and also has a video and if so, enqueue foobox video scripts.
            add_action( 'foogallery_loaded_template', array( $this, 'enqueue_foobox_free_dependencies' ) );

            //add settings for video
            add_filter( 'foogallery_admin_settings_override', array( $this, 'include_video_settings' ) );
        }

        /**
         * Enqueue styles and scripts
         */
        function enqueue_assets() {
			foogallery_enqueue_media_views_script();
			foogallery_enqueue_media_views_style();
        }

		/**
		 * Include the templates into the page if they are needed
		 */
		public function add_media_templates() {
			if ( wp_script_is( 'foogallery-media-views' ) ) {
				foogallery_include_media_views_templates();
			}
		}

		/**
		 * Add an extra class so that a video icon shows for videos
		 * @param $extra_class
		 * @param $attachment_post
		 *
		 * @return string
		 */
		function render_gallery_item_with_video_icon( $extra_class, $attachment_post ) {
			//check if the attachment is a video and append a class
			if ( foogallery_is_attachment_video( $attachment_post ) ) {
				if ( !isset( $extra_class ) ) $extra_class = '';
				$extra_class .= 'subtype-foogallery';
			}

			return $extra_class;
		}

        /**
         * Add video specific custom fields.
         *
         * @uses "foogallery_attachment_custom_fields" filter
         * @param array $fields
         * @return array
         */
        public function attachment_custom_fields( $fields )
        {
			$fields[ 'data-width' ] = array(
				'label'       =>  __( 'Override Width', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);
			$fields[ 'data-height' ] = array(
				'label'       =>  __( 'Override Height', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);
            return $fields;
        }

        /**
         * Add fields to all galleries.
         *
         * @uses "foogallery_override_gallery_template_fields"
         * @param $fields
         *
         * @return mixed
         */
        public function all_template_fields( $fields ) {
            $fields[] = array(
                'id'      => 'video_hover_icon',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Video Hover Icon', 'foogallery' ),
                'type'    => 'htmlicon',
                'default' => 'fg-video-default',
				'choices'  => apply_filters( 'foogallery_gallery_template_video_hover_icon_choices', array(
					'' 				   => array( 'label' => __( 'None', 'foogallery' ), 		'html' => '<div class="foogallery-setting-video_overlay"></div>' ),
					'fg-video-default' => array( 'label' => __( 'Default Icon', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay fg-video-default"></div>' ),
					'fg-video-1'       => array( 'label' => __( 'Icon 1', 'foogallery' ),       'html' => '<div class="foogallery-setting-video_overlay fg-video-1"></div>' ),
					'fg-video-2'       => array( 'label' => __( 'Icon 2', 'foogallery' ),       'html' => '<div class="foogallery-setting-video_overlay fg-video-2"></div>' ),
					'fg-video-3'       => array( 'label' => __( 'Icon 3', 'foogallery' ),       'html' => '<div class="foogallery-setting-video_overlay fg-video-3"></div>' ),
					'fg-video-4'       => array( 'label' => __( 'Icon 4', 'foogallery' ),       'html' => '<div class="foogallery-setting-video_overlay fg-video-4"></div>' ),
				)),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'class'
				)
            );
            $fields[] = array(
                'id'      => 'video_sticky_icon',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Sticky Video Icon', 'foogallery' ),
                'desc'    => __( 'Always show the video icon for videos in the gallery, and not only when you hover.', 'foogallery' ),
                'type'    => 'radio',
                'default' => '',
                'spacer'  => '<span class="spacer"></span>',
                'choices' => array(
                	'fg-video-sticky' => __( 'Yes', 'foogallery' ),
                	''                => __( 'No', 'foogallery' ),
            	),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'class'
				)
            );

			$fields[] = array(
				'id'      => 'video_size_help',
				'title'   => __( 'Video Size Help', 'foogallery' ),
				'desc'    => __( 'The lightbox video size can be overridden on each individual video by editing the attachment info, and changing the Override Width and Override Height properties.', 'foogallery' ),
				'section' => __( 'Video', 'foogallery' ),
				'type'    => 'help',
			);

            $fields[] = array(
                'id'      => 'video_size',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Lightbox Video Size', 'foogallery' ),
                'desc'    => __( 'The default video size when opening videos in the lightbox.', 'foogallery' ),
                'type'    => 'select',
                'default' => '640x360',
                'choices' => array(
					'640x360'   => __( '640 x 360', 'foogallery' ),
					'854x480'   => __( '854 x 480', 'foogallery' ),
					'960x540'   => __( '960 x 540', 'foogallery' ),
					'1024x576'  => __( '1024 x 576', 'foogallery' ),
					'1280x720'  => __( '1280 x 720 (HD)', 'foogallery' ),
					'1366x768'  => __( '1366 x 768', 'foogallery' ),
					'1600x900'  => __( '1600 x 900', 'foogallery' ),
					'1920x1080' => __( '1920 x 1080 (Full HD)', 'foogallery' ),
				),
            );

            $fields[] = array(
                'id'      => 'video_autoplay',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Lightbox Autoplay', 'foogallery' ),
                'desc'    => __( 'Try to autoplay the video when opened in a lightbox. This will only work with videos hosted on Youtube or Vimeo.', 'foogallery' ),
                'type'    => 'radio',
                'default' => 'yes',
                'spacer'  => '<span class="spacer"></span>',
                'choices' => array(
					'yes' => __( 'Yes', 'foogallery' ),
					'no'  => __( 'No', 'foogallery' ),
				),
            );
            return $fields;
        }

        public function set_video_flag_on_attachment( $foogallery_attachment, $post ) {
			$foogallery_attachment->is_video = false;

			if ( foogallery_is_attachment_video( $foogallery_attachment ) ) {
				$foogallery_attachment->is_video = true;
			}
		}

		/**
		 * @uses "foogallery_attachment_html_item_classes" filter
		 *
		 * @param                             $classes
		 * @param                             $args
		 * @param object|FooGalleryAttachment $attachment
		 *
		 * @return mixed
		 */
		public function alter_video_item_attributes( $classes, $attachment, $args ) {
			if ( $attachment->is_video ) {
				$classes[] = 'fg-video';
			}

			return $classes;
		}

		/**
		 * @uses "foogallery_attachment_html_link_attributes" filter
		 *
		 * @param                             $attr
		 * @param                             $args
		 * @param object|FooGalleryAttachment $attachment
		 *
		 * @return mixed
		 */
        public function alter_video_link_attributes( $attr, $args, $attachment ) {
            if ( $attachment->is_video ) {

            	//set the video URL
                $url = foogallery_get_video_url_from_attachment( $attachment );
                $attr['href'] = $url;

                //if we have no widths or heights then use video default size
                if ( !isset( $attr['data-width'] ) ) {
                    $size = foogallery_gallery_template_setting( 'video_size', '640x360' );
                    list( $width, $height ) = explode( 'x', $size );
                    $attr['data-width'] = $width;
                    $attr['data-height'] = $height;
                }

                //override width
				$override_width = get_post_meta( $attachment->ID, '_data-width', true );
				if ( !empty( $override_width ) ) {
					$attr['data-width'] = intval( $override_width );
				}

				//override height
				$override_height = get_post_meta( $attachment->ID, '_data-height', true );
				if ( !empty( $override_height ) ) {
					$attr['data-height'] = intval( $override_height );
				}

				$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
                //if no lightbox is being used then force to open in new tab
                if ( 'unknown' === $lightbox || 'none' === $lightbox ) {
                    $attr['target'] = '_blank';
                }
            }

            return $attr;
        }

        public function foogallery_build_class_attribute( $classes ) {
            global $current_foogallery;

            //first determine if the gallery has any videos
			if ( 0 === foogallery_get_gallery_video_count( $current_foogallery->ID ) ) {
				return $classes;
			}

			//get the selected video icon
			$classes[] = foogallery_gallery_template_setting( 'video_hover_icon', 'fg-video-default' );

            //include the video sticky class
            $classes[] = foogallery_gallery_template_setting( 'video_sticky_icon', '' );;

            return $classes;
        }

        /**
         * Enqueue any script or stylesheet file dependencies that FooGallery_Pro_Video relies on
         *
         * @param $foogallery FooGallery
         */
        function enqueue_foobox_free_dependencies( $foogallery ) {
            if ( $foogallery ) {
                $video_count = foogallery_get_gallery_video_count( $foogallery->ID );

                if ( $video_count > 0 ) {

                    $lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
                    //we want to add some JS to the front-end ONLY if we are using FooBox Free

                    if ( class_exists( 'Foobox_Free' ) && ('foobox' == $lightbox || 'foobox-free' == $lightbox) ) {
                        $js = FOOGALLERY_PRO_URL . 'js/foobox.video.min.js';
                        wp_enqueue_script(
                            'foogallery-foobox-video',
                            $js,
                            array( 'jquery', 'foobox-free-min' ),
							FOOGALLERY_VERSION
                        );
                    }
                }
            }
        }

        public function calculate_video_count( $post_id ) {
			foogallery_set_gallery_video_count( $post_id );
        }

        public function include_video_count( $image_count_text, $gallery ) {
            $count = sizeof( $gallery->attachment_ids );
            $video_count = foogallery_get_gallery_video_count( $gallery->ID );
            $image_count = $count - $video_count;
            return foogallery_gallery_image_count_text( $count, $image_count, $video_count );
        }

        public function include_video_settings( $settings ) {
            $settings['settings'][] = array(
                'id'      => 'video_default_target',
                'title'   => __( 'Default Video Target', 'foogallery' ),
                'desc'    => __( 'The default target set for a video when it is imported into the gallery.', 'foogallery' ),
                'type'    => 'select',
                'default' => '_blank',
                'section' => __( 'Gallery Defaults', 'foogallery ' ),
                'tab'     => 'general',
                'choices' => array(
					'default' => __( 'Default', 'foogallery' ),
					'_blank'  => __( 'New tab (_blank)', 'foogallery' ),
					'_self'   => __( 'Same tab (_self)', 'foogallery' ),
					'foobox'  => __( 'FooBox', 'foogallery' ),
				),
            );
            $settings['settings'][] = array(
                'id'      => 'language_video_count_none_text',
                'title'   => __( 'Video Count None Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'No images or videos', 'foogallery' ),
                'tab'     => 'language',
            );
            $settings['settings'][] = array(
                'id'      => 'language_video_count_single_text',
                'title'   => __( 'Video Count Single Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( '1 video', 'foogallery' ),
                'tab'     => 'language',
            );
            $settings['settings'][] = array(
                'id'      => 'language_video_count_plural_text',
                'title'   => __( 'Video Count Many Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( '%s videos', 'foogallery' ),
                'tab'     => 'language',
            );
            return $settings;
        }
    }
}