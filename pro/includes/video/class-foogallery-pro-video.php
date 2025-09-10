<?php
/**
 * Adds video support within FooGallery
 */

if ( ! class_exists( 'FooGallery_Pro_Video' ) ) {

	define( 'FOOGALLERY_VIDEO_POST_META', '_foogallery_video_data' );
	define( 'FOOGALLERY_VIDEO_POST_META_VIDEO_COUNT', '_foogallery_video_count' );

	require_once plugin_dir_path( __FILE__ ) . 'functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'class-foogallery-pro-video-query.php';
	require_once plugin_dir_path( __FILE__ ) . 'class-foogallery-pro-video-import.php';

	class FooGallery_Pro_Video {
		/**
		 * Wire up everything we need
		 */
		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_feature' ) );

			add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );			
		}

		function load_feature(){
			if ( foogallery_feature_enabled( 'foogallery-video' ) ){
				new FooGallery_Pro_Video_Query();
				new FooGallery_Pro_Video_Import();

				//check if the gallery is using foobox free and also has a video and if so, enqueue foobox video scripts.
				add_action( 'foogallery_loaded_template', array( $this, 'enqueue_foobox_free_dependencies' ) );

				//check if the album is using foobox free and also has a video and if so, enqueue foobox video scripts.
				add_action( 'foogallery_loaded_album_template', array( $this, 'enqueue_foobox_free_dependencies_for_album' ) );

				//output the embeds after the gallery if needed
				add_action( 'foogallery_loaded_template', array( $this, 'include_video_embeds' ) );

				//output the embeds after the album if needed
				add_action( 'foogallery_loaded_album_template', array( $this, 'include_video_embeds_for_album' ) );

				//load all video info into the attachment, so that it is only done once
				add_action( 'foogallery_attachment_instance_after_load', array( $this, 'set_video_flag_on_attachment' ), 10, 2 );

				//add attributes to front-end anchor
				add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_video_link_attributes' ), 24, 3 );

				//add video icon class to galleries
				add_filter( 'foogallery_build_class_attribute', array( $this, 'foogallery_build_class_attribute' ) );

				if ( is_admin() ) {

					//setup script includes
					add_action( 'wp_enqueue_media', array( $this, 'enqueue_assets' ) );
	
					//make sure the gallery items render with a video icon
					add_filter( 'foogallery_admin_render_gallery_item_extra_classes', array( $this, 'render_gallery_item_with_video_icon' ), 10, 2 );
	
					//add attachment custom fields
					add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ) );
	
					//add extra fields to all templates
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_video_fields' ) );
	
					// add additional templates
					add_action( 'admin_footer', array( $this, 'add_media_templates' ) );
	
					//intercept gallery save and calculate how many videos are in the gallery
					add_action( 'foogallery_after_save_gallery', array( $this, 'calculate_video_count' ) );
	
					//change the image count to include videos if they are present in the gallery
					add_filter( 'foogallery_image_count', array( $this, 'include_video_count' ), 11, 3 );
	
					//add settings for video
					add_filter( 'foogallery_admin_settings_override', array( $this, 'include_video_settings' ) );
	
					//ajax call to save the Vimeo access token
					add_action( 'wp_ajax_fgi_save_access_token', array( $this, 'save_vimeo_access_token') );
	
					//allow thumbnails for videos to be stored in a separate subdirectory
					add_filter( 'upload_dir', array( $this, 'override_video_upload_dir' ), 99 );
	
					//override the file_type in attachment modal
					add_filter( 'foogallery_attachment_modal_info_file_type', array( $this, 'override_attachment_modal_file_type' ) );

					// Attachment modal actions:
					add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'attachment_modal_display_tab' ), 10 );
					add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'attachment_modal_display_tab_content' ), 10, 1 );
					add_action( 'foogallery_attachment_save_data', array( $this, 'attachment_modal_save_data' ), 10, 2 );
					add_filter( 'foogallery_attachment_modal_data', array( $this, 'attachment_modal_data' ), 20, 4 );
				}
			}
		}

		function register_extension( $extensions_list ) {
			$pro_features = foogallery_pro_features();

            $extensions_list[] = array(
                'slug' => 'foogallery-video',
                'class' => 'FooGallery_Pro_Video',
                'categories' => array( 'Premium' ),
                'title' => foogallery__( 'Video', 'foogallery' ),
                'description' => $pro_features['video']['desc'],
                'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                'external_link_url' => $pro_features['video']['link'],
                'dashicon'          => 'dashicons-video-alt3',
                'tags' => array( 'Premium' ),
                'source' => 'bundled',
                'activated_by_default' => true,
                'feature' => true
            );

            return $extensions_list;
        }

        /**
         * Override the file_type in attachment modal for videos.
         *
         * @param $file_type string
         * @return string
         */
        function override_attachment_modal_file_type( $file_type ) {
            if ( 'image/foogallery' === $file_type ) {
                return 'video';
            }
            return $file_type;
        }

		/**
		 * Override upload directory
		 *
		 * @return array Upload directory information
		 */
		function override_video_upload_dir( $upload ) {
			global $foogallery_video_upload;

			//only think about any changes if we are importing video thumbnails
			if ( isset( $foogallery_video_upload ) ) {

				$directory = foogallery_get_setting( 'video_thumbnail_directory' );

				if ( !empty( $directory ) ) {
					$upload['subdir'] = '/' . $directory . $upload['subdir'];
					$upload['path'] = $upload['basedir'] . $upload['subdir'];
					$upload['url']  = $upload['baseurl'] . $upload['subdir'];
				}
			}

			return $upload;
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
		 *
		 * @param $extra_class
		 * @param $attachment_post
		 *
		 * @return string
		 */
		function render_gallery_item_with_video_icon( $extra_class, $attachment_post ) {
			//check if the attachment is a video and append a class
			if ( foogallery_is_attachment_video( $attachment_post ) ) {
				if ( ! isset( $extra_class ) ) {
					$extra_class = '';
				}
				$extra_class .= ' subtype-foogallery';
			}

			return $extra_class;
		}

		/**
		 * Add video specific custom fields.
		 *
		 * @uses "foogallery_attachment_custom_fields" filter
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function attachment_custom_fields( $fields ) {
			$fields['data-width']  = array(
				'label'       => __( 'Override Width', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);
			$fields['data-height'] = array(
				'label'       => __( 'Override Height', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			return $fields;
		}

		/**
		 * Add fields to all galleries.
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 *
		 * @return mixed
		 */
		public function add_video_fields( $fields ) {
			$video_fields[] = array(
				'id'       => 'video_hover_icon',
				'section'  => __( 'Video', 'foogallery' ),
				'title'    => __( 'Video Hover Icon', 'foogallery' ),
				'type'     => 'htmlicon',
				'default'  => 'fg-video-1',
				'choices'  => apply_filters(
					'foogallery_gallery_template_video_hover_icon_choices', array(
						''                 => array( 'label' => __( 'None', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay"></div>' ),
						'fg-video-default' => array( 'label' => __( 'Video Icon 1', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay fg-video-default"></div>' ),
						'fg-video-1'       => array( 'label' => __( 'Video Icon 2', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay fg-video-1"></div>' ),
						'fg-video-2'       => array( 'label' => __( 'Video Icon 3', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay fg-video-2"></div>' ),
						'fg-video-3'       => array( 'label' => __( 'Video Icon 4', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay fg-video-3"></div>' ),
						'fg-video-4'       => array( 'label' => __( 'Video Icon 5', 'foogallery' ), 'html' => '<div class="foogallery-setting-video_overlay fg-video-4"></div>' ),
					)
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode'
				)
			);
			$video_fields[] = array(
				'id'       => 'video_sticky_icon',
				'section'  => __( 'Video', 'foogallery' ),
				'title'    => __( 'Sticky Video Icon', 'foogallery' ),
				'desc'     => __( 'Always show the video icon for videos in the gallery, and not only when you hover.', 'foogallery' ),
				'type'     => 'radio',
				'default'  => 'fg-video-sticky',
				'choices'  => array(
					'fg-video-sticky' => __( 'Yes', 'foogallery' ),
					''                => __( 'No', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode'
				)
			);

			$video_fields[] = array(
				'id'      => 'video_size_help',
				'title'   => __( 'Video Size Help', 'foogallery' ),
				'desc'    => __( 'The lightbox video size can be overridden on each individual video by editing the attachment info, and changing the Override Width and Override Height properties.', 'foogallery' ),
				'section' => __( 'Video', 'foogallery' ),
				'type'    => 'help',
			);

			$video_fields[] = array(
				'id'      => 'video_size',
				'section' => __( 'Video', 'foogallery' ),
				'title'   => __( 'Lightbox Video Size', 'foogallery' ),
				'desc'    => __( 'The default video size when opening videos in the lightbox.', 'foogallery' ),
				'type'    => 'select',
				'default' => '1920x1080',
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
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode'
				)
			);

			$video_fields[] = array(
				'id'      => 'video_autoplay',
				'section' => __( 'Video', 'foogallery' ),
				'title'   => __( 'Lightbox Autoplay', 'foogallery' ),
				'desc'    => __( 'Try to autoplay the video when opened in a lightbox. This will only work with videos hosted on Youtube or Vimeo.', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'yes',
				'choices' => array(
					'yes' => __( 'Yes', 'foogallery' ),
					'no'  => __( 'No', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode'
				)
			);

			//find the index of the Advanced section
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $video_fields );

			return $fields;
		}

		/**
		 * After the attachment is loaded, determine if the attachment is a video
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function set_video_flag_on_attachment( $foogallery_attachment, $post ) {
			$foogallery_attachment->is_video = false;
			$foogallery_attachment->is_embed = false;

			if ( foogallery_is_attachment_video( $foogallery_attachment ) ) {
				//set the video flag
				$foogallery_attachment->is_video = true;

				//set the video data object.
				// this data is in the format:
				// array( 
				//	"type" => "video", 
				//	"provider" => "youtube", 
				//	"id" => "JBEci22Ct74",
				//	"url" => "https://www.youtube.com/embed/JBEci22Ct74",
				//  "title" => "Video title",
				//	"description" => "Video description",
				//	"thumbnail" => "https://www.youtube.com/thumbnail/JBEci22Ct74",
				//);
				$foogallery_attachment->video_data = get_post_meta( $foogallery_attachment->ID, FOOGALLERY_VIDEO_POST_META, true );

				//check if we have no video data and set flag
				if ( empty( $foogallery_attachment->video_data ) ) {
					$foogallery_attachment->is_video = false;
				} else {
					//set the embed flag
					$foogallery_attachment->is_embed = isset( $foogallery_attachment->video_data['type'] ) && 'embed' === $foogallery_attachment->video_data['type'];
				}
			}
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
			global $current_foogallery;
			global $current_foogallery_template;
			global $current_foogallery_album;

			if ( isset( $attachment->is_video ) && $attachment->is_video === true ) {

				//set a flag on the gallery level
				$current_foogallery->has_videos = true;

				//if we have no widths or heights then use video default size
				if ( ! isset( $attr['data-width'] ) ) {
					$size = foogallery_gallery_template_setting( 'video_size', '640x360' );
					list( $width, $height ) = explode( 'x', $size );
					$attr['data-width']  = $width;
					$attr['data-height'] = $height;
				}

				//override width
				$override_width = get_post_meta( $attachment->ID, '_data-width', true );
				if ( ! empty( $override_width ) ) {
					$attr['data-width'] = intval( $override_width );
				}

				//override height
				$override_height = get_post_meta( $attachment->ID, '_data-height', true );
				if ( ! empty( $override_height ) ) {
					$attr['data-height'] = intval( $override_height );
				}

				//make some changes for embeds
				if ( $attachment->is_embed ) {
					$args = array();
					if ( isset( $attr['data-width'] ) ) {
						$args['width'] = $attr['data-width'];
					}

					$oembed_data = foogallery_oembed_get_data( $attachment->custom_url, $args );

					$data = array(
						'id'            => 'foogallery_embed_'.$current_foogallery->ID . '-' . $attachment->ID,
						'attachment_id' => $attachment->ID,
						'url'           => $attachment->custom_url,
						'provider'      => $attachment->video_data['provider'],
						'html'          => $oembed_data->html
					);

					$current_foogallery->video_embeds[] = $data;
					$attr['href'] = '#' . $data['id'];

				} else {
					$attr['href'] = foogallery_get_video_url_from_attachment( $attachment );
				}

				if ( isset( $current_foogallery_template ) ) {
					$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

					//if no lightbox is being used then force to open in new tab
					if ( 'unknown' === $lightbox || 'none' === $lightbox ) {
						$attr['target'] = '_blank';
					} else if ( 'foobox' === $lightbox ) {
						//make sure FooBox opens the embed
						$attr['target'] = 'foobox';
					}
				}
			}

			return $attr;
		}

		public function foogallery_build_class_attribute( $classes ) {
			/** @var FooGallery */
			global $current_foogallery;

			$has_video = false;

			if ( isset( $current_foogallery ) ) {

				$loop_attachments = $current_foogallery->is_dynamic() || foogallery_is_preview();

				//if it is a dynamic gallery, then loop through all attachments and see if there are videos
				if ( $loop_attachments ) {
					foreach ( $current_foogallery->attachments() as $attachment ) {
						if ( isset ( $attachment->is_video ) ) {
						    if ( $attachment->is_video ) {
							    $has_video = true;
							    break;
						    }
						}
					}
				} else {
					$has_video = foogallery_get_gallery_video_count( $current_foogallery->ID ) > 0;
				}

				if ( $has_video ) {
					$current_foogallery->has_videos = $has_video;

					//get the selected video icon
					$classes[] = foogallery_gallery_template_setting( 'video_hover_icon', 'fg-video-default' );

					//include the video sticky class
					$classes[] = foogallery_gallery_template_setting( 'video_sticky_icon', '' );
				}
			}

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

					if ( class_exists( 'Foobox_Free' ) && ( 'foobox' == $lightbox || 'foobox-free' == $lightbox ) ) {
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

		/**
		 * Enqueue any script or stylesheet file dependencies that FooGallery_Pro_Video relies on for an album
		 *
		 * @param $foogallery_album FooGalleryAlbum
		 */
		function enqueue_foobox_free_dependencies_for_album( $foogallery_album ) {
			if ( $foogallery_album ) {
				if ( apply_filters( 'foogallery_albums_supports_video-' . $foogallery_album->album_template, false ) ) {
					$video_count = 0;
					foreach ( $foogallery_album->gallery_ids as $gallery_id ) {
						$video_count += foogallery_get_gallery_video_count( $gallery_id );
					}
					if ( $video_count > 0 ) {
						$lightbox = foogallery_album_template_setting( 'lightbox', 'unknown' );
						//we want to add some JS to the front-end ONLY if we are using FooBox Free
						if ( class_exists( 'Foobox_Free' ) && ( 'foobox' == $lightbox || 'foobox-free' == $lightbox ) ) {
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
		}

		public function calculate_video_count( $post_id ) {
			foogallery_set_gallery_video_count( $post_id );
		}

		public function include_video_count( $image_count_text, $gallery, $count ) {
			$video_count = foogallery_get_gallery_video_count( $gallery->ID );
			$image_count = $count - $video_count;

			return foogallery_gallery_image_count_text( $count, $image_count, $video_count );
		}

		public function include_video_settings( $settings ) {

			$settings['tabs']['video'] = __( 'Video', 'foogallery' );

			$settings['settings'][] = array(
				'id'      => 'video_default_target',
				'title'   => __( 'Default Video Target', 'foogallery' ),
				'desc'    => __( 'The default target set for a video when it is imported into the gallery.', 'foogallery' ),
				'type'    => 'select',
				'default' => '_blank',
				'tab'     => 'video',
				'choices' => array(
					'default' => __( 'Default', 'foogallery' ),
					'_blank'  => __( 'New tab (_blank)', 'foogallery' ),
					'_self'   => __( 'Same tab (_self)', 'foogallery' ),
					'foobox'  => __( 'FooBox', 'foogallery' ),
				),
			);

			$settings['settings'][] = array(
				'id'      => 'vimeo_access_token',
				'title'   => __( 'Vimeo Access Token', 'foogallery' ),
				'desc'    => __( 'An access token is required by the Vimeo API in order to import multiple videos from channels, albums or a user. This is not required to import a single video.', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'video',
			);

			$settings['settings'][] = array(
				'id'      => 'youtube_api_key',
				'title'   => __( 'YouTube API Key', 'foogallery' ),
				'desc'    => __( 'An API key is required by the YouTube API in order to search or import multiple videos from a playlist. This is not required to import a single video.', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'video',
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

			$settings['settings'][] = array(
				'id'      => 'video_thumbnail_directory',
				'title'   => __( 'Video Thumbnail Directory', 'foogallery' ),
				'desc'    => __( 'You can choose to override where the video thumbnail images will be saved within your media library. Leave blank to use the default location.', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'tab'     => 'video',
			);

			return $settings;
		}


		/**
		 * Renders any video embeds for the gallery
		 *
		 * @param FooGallery $gallery
		 */
		function include_video_embeds( $gallery ) {
			if ( isset( $gallery->has_videos ) && $gallery->has_videos && isset( $gallery->video_embeds ) ) {

				?>
				<div style="display: none;"><?php

				foreach ( $gallery->video_embeds as $embed ) {
					?>
					<div id="<?php echo $embed['id']; ?>" data-provider="<?php echo $embed['provider']; ?>">
						<?php echo $embed['html']; ?>
					</div>
					<?php
				}

				?></div><?php
			}
		}

		/**
		 * Renders any video embeds for the album
		 *
		 * @param FooGalleryAlbum $album
		 */
		function include_video_embeds_for_album( $album ) {
			foreach ( $album->galleries() as $gallery ) {
				$this->include_video_embeds( $gallery );
			}
		}

		/**
		 * Save the Vimeo Access Token to the foogallery settings
		 */
		function save_vimeo_access_token() {
			$nonce = !empty($_POST["fgi_nonce"]) ? $_POST["fgi_nonce"] : null;

			if (wp_verify_nonce($nonce, "fgi_nonce")) {
				$access_token = !empty( $_POST["access_token"] ) ? $_POST["access_token"] : null;

				foogallery_settings_set_vimeo_access_token( $access_token );
				wp_send_json_success( __('Saved successfully.', 'foogallery' ) );
			}
			die();
		}

		        /**
         * Image modal Commerce tab title
         */
        public function attachment_modal_display_tab() { ?>
            <div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-video">
                <input type="radio" name="tabset" id="foogallery-tab-video" aria-controls="foogallery-panel-video">
                <label for="foogallery-tab-video"><?php _e('Video', 'foogallery'); ?></label>
            </div>
        <?php }

        /**
         * Image modal Video tab content
		 * 
		 * @param array $modal_data
		 * 
         */

		//	"type" => "video", 
		//	"provider" => "youtube", 
		//	"id" => "JBEci22Ct74",
		//	"url" => "https://www.youtube.com/embed/JBEci22Ct74",
		//  "title" => "Video title",
		//	"description" => "Video description",
		//	"thumbnail" => "https://www.youtube.com/thumbnail/JBEci22Ct74",
        public function attachment_modal_display_tab_content( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {

				if ( $modal_data['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-video" class="tab-panel">
						<div class="settings">
							<span class="setting has-description" data-setting="video_url">
								<label for="attachment-details-video-url" class="name"><?php esc_html_e( 'Video URL', 'foogallery'); ?></label>
								<input type="text" name="foogallery[video_url]" id="attachment-details-video-url" value="<?php echo esc_attr( $modal_data['video_url'] ); ?>">
							</span>
							<p class="description">
                                <?php esc_html_e( 'The URL of the video. If this is provided then this image will be shown as a video in the gallery.', 'foogallery' ); ?>
                            </p>

							<span class="setting has-description" data-setting="video_provider">
								<label for="attachment-details-video-provider" class="name"><?php esc_html_e('Video Provider', 'foogallery'); ?></label>
								<input type="text" name="foogallery[video_provider]" id="attachment-details-video-provider" value="<?php echo esc_attr( $modal_data['video_provider'] ); ?>">
							</span>
							<p class="description">
                                <?php esc_html_e( 'The provider of the video (YouTube, Vimeo, etc.). This is optional.', 'foogallery' ); ?>
                            </p>

							<span class="setting has-description" data-setting="video_type">
								<label for="attachment-details-video-type" class="name"><?php esc_html_e('Override Video Type', 'foogallery'); ?></label>
								<input type="text" name="foogallery[video_type]" id="attachment-details-video-type" value="<?php echo esc_attr( $modal_data['video_type'] ); ?>">
							</span>
							<p class="description">
                                <?php esc_html_e( 'The type of the video embed. This is optional.', 'foogallery' ); ?>
                            </p>

							<span class="setting has-description" data-setting="video_id">
								<label for="attachment-details-video-id" class="name"><?php esc_html_e('Video ID', 'foogallery'); ?></label>
								<input type="text" name="foogallery[video_id]" id="attachment-details-video-id" value="<?php echo esc_attr( $modal_data['video_id'] ); ?>">
							</span>
							<p class="description">
                                <?php esc_html_e( 'The ID of the video from the video provider or used for embeds. This is optional.', 'foogallery' ); ?>
                            </p>
						</div>
					</section>
					<?php
				}
			}
        }

        /**
         * Save EXIF tab data content
         *
         * @param $img_id int attachment id to update data
         *
         * @param $foogallery array of form post data
         *
         */
        public function attachment_modal_save_data( $img_id, $foogallery ) {
			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {
				$video_data = array();

				foreach( $foogallery as $key => $val ) {
					if ( $key === 'video_id' && !empty( $val ) ) {
						$video_data['id'] = $val;
					}
					else if ( $key === 'video_provider' && !empty( $val ) ) {
						$video_data['provider'] = $val;
					}
					else if ( $key === 'video_type' && !empty( $val ) ) {
						$video_data['type'] = $val;
					}
					else if ( $key === 'video_url' && !empty( $val ) ) {
						$video_data['url'] = $val;
					}
				}

				if ( !empty( $video_data ) ) {
					update_post_meta( $img_id, FOOGALLERY_VIDEO_POST_META, $video_data );
				}
			}
        }


		/**
		 * Image modal more tab data update
		 */
		public function attachment_modal_data( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
				$video_data = get_post_meta( $attachment_id, FOOGALLERY_VIDEO_POST_META, true );
				if ( is_array( $video_data ) ) {
					if ( !isset( $video_data['id'] ) ) {
						$video_data['id'] = '';
					}
					if ( !isset( $video_data['provider'] ) ) {
						$video_data['provider'] = '';
					}
					if ( !isset( $video_data['type'] ) ) {
						$video_data['type'] = '';
					}
					if ( !isset( $video_data['url'] ) ) {
						$video_data['url'] = '';
					}
				} else {
					$video_data = array(
						'id' => '',
						'provider' => '',
						'type' => '',
						'url' => '',
					);
				}

				$modal_data['video_id'] = $video_data['id'];
				$modal_data['video_provider'] = $video_data['provider'];
				$modal_data['video_type'] = $video_data['type'];
				$modal_data['video_url'] = $video_data['url'];
            }
			return $modal_data;
		}
	}
}