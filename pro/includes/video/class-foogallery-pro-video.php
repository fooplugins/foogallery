<?php
/**
 * Adds video support within FooGallery
 */

if ( !class_exists( 'FooGallery_Pro_Video' ) ) {

    define( 'FOOVIDEO_BATCH_LIMIT', 10 );
    define( 'FOOVIDEO_POST_META', '_foovideo_video_data' );
    define( 'FOOVIDEO_POST_META_VIDEO_COUNT', '_foovideo_video_count' );

    require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/import/class-import-manager.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/import/class-import-handler-youtube.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/import/class-import-handler-vimeo.php';

    class FooGallery_Pro_Video
    {
        /**
         * Wire up everything we need
         */
        function __construct()
        {
            new FooGallery_FooGallery_Pro_Video_Import_Manager();
            new FooGallery_FooGallery_Pro_Video_Import_Handler_YouTube();
            new FooGallery_FooGallery_Pro_Video_Import_Handler_Vimeo();

            //setup script includes
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylescripts' ) );
            //add attachment custom fields
            add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ) );
            //add extra fields to all templates
            add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'all_template_fields' ) );
            // add additional templates
            add_action( 'admin_footer', array( $this, 'add_media_templates' ) );
            //add attributes to front-end anchor
            add_filter(
                'foogallery_attachment_html_link_attributes',
                array( $this, 'alter_video_link_attributes' ),
                24,
                3
            );
            //do search handler
            add_action( 'wp_ajax_foo_video_search', array( $this, 'video_search' ) );
            //add video icon class to galleries
            add_filter( 'foogallery_build_class_attribute', array( $this, 'foogallery_build_class_attribute' ) );
            //intercept gallery save and calculate how many videos are in the gallery
            add_action( 'foogallery_after_save_gallery', array( $this, 'calculate_video_count' ) );
            //change the image count to include videos if they are present in the gallery
            add_filter(
                'foogallery_image_count',
                array( $this, 'include_video_count' ),
                10,
                2
            );
            //check if another gallery is using a video and if so, enqueue our CSS.
            add_action( 'foogallery_loaded_template', array( $this, 'enqueue_foovideo_dependencies' ) );
            //add settings for video
            add_filter( 'foogallery_admin_settings_override', array( $this, 'include_video_settings' ) );
            // do select attachment handler
            add_action( 'wp_ajax_foo_video_attachments', array( $this, 'attachment_search' ) );
        }

        public function attachment_search()
        {

            if ( isset( $_POST['foo_video_nonce'] ) && wp_verify_nonce( $_POST['foo_video_nonce'], 'foo_video_nonce' ) ) {
                $page = 1;
                if ( !empty($_POST['page']) ) {
                    $page = (int) $_POST['page'];
                }
                $attachment_query_args = array(
                    'post_type'      => 'attachment',
                    'posts_per_page' => 10,
                    'paged'          => $page,
                    'orderby'        => 'date_desc',
                );
                $attachment_objects = get_posts( $attachment_query_args );
                $attachments = array();
                foreach ( $attachment_objects as $attachment ) {
                    $attachments[] = array(
                        'ID'    => $attachment->ID,
                        'title' => $attachment->post_title,
                        'html'  => wp_get_attachment_image( $attachment->ID, array( 80, 60 ), true ),
                        'image' => wp_get_attachment_url( $attachment->ID ),
                    );
                }
                wp_send_json_success( $attachments );
            } else {
                status_header( 500 );
                echo  __( 'Could not search attachments!', 'foogallery' ) ;
                wp_die();
            }

        }

        /**
         *
         *
         */
        public function video_search()
        {
            if ( empty($_POST['q']) ) {
                exit;
            }
            $page = 1;
            $type = 'youtube';
            $query_str = trim( $_POST['q'] );
            if ( !empty($_POST['vidpage']) ) {
                $page = (int) $_POST['vidpage'];
            }
            if ( !empty($_POST['type']) && in_array( $_POST['type'], array( 'youtube', 'vimeo' ) ) ) {
                $type = (string) $_POST['type'];
            }

            if ( $type == 'youtube' ) {
                //check if videoID
                if ( 0 === strpos( $query_str, 'PL' ) ) {
                    $query_str = add_query_arg( 'list', $query_str, 'https://www.youtube.com/playlist' );
                }
                if ( false === strpos( $query_str, ' ' ) && strlen( $query_str ) > 10 ) {
                    // check if its a URL

                    if ( $is_url = wp_http_validate_url( $query_str ) ) {
                        $struct = parse_url( $is_url );
                        $query = array();
                        if ( !empty($struct['query']) ) {
                            parse_str( $struct['query'], $query );
                        }

                        if ( !empty($query['list']) ) {
                            $data = wp_remote_get( 'http://www.youtube.com/oembed?url=' . urlencode( $query_str ) );

                            if ( !is_wp_error( $data ) ) {
                                $url = 'https://www.youtube.com/list_ajax?style=json&action_get_list=true&list=' . $query['list'];
                                $isplaylist = json_decode( wp_remote_retrieve_body( $data ), true );
                                $isplaylist['playlist_id'] = $query['list'];
                            }

                        }

                    }

                }
                if ( empty($url) ) {
                    $url = 'https://www.youtube.com/search_ajax?style=json&search_query=' . urlencode( $query_str ) . '&page=' . $page;
                }
            } elseif ( $type == 'vimeo' ) {

                if ( $is_url = wp_http_validate_url( $query_str ) ) {
                    // check album or not

                    if ( strpos( $query_str, '/album/' ) || strpos( $query_str, '/user' ) ) {
                        $isstream = true;

                        if ( strpos( $query_str, '/user' ) ) {
                            $url = 'https://player.vimeo.com/hubnut/config/user/' . basename( $query_str );
                        } else {
                            $url = 'https://player.vimeo.com/hubnut/config/album/' . basename( $query_str );
                        }

                        //} else if ( ) {
                    } else {
                        $url = 'https://vimeo.com/api/oembed.json?url=' . urlencode( $query_str );
                    }

                } else {
                    $vidid = 'https%3A//vimeo.com/' . basename( $query_str );
                    $url = 'https://vimeo.com/api/oembed.json?url=' . $vidid;
                }

            }

            $data = wp_remote_get( $url );

            if ( is_wp_error( $data ) ) {
                echo  '<div class="notice error"><p>' . $data->get_error_message() . '</p></div>' ;
                exit;
            }

            $results = json_decode( wp_remote_retrieve_body( $data ), true );

            if ( empty($results['stream']) && empty($results['provider_name']) && empty($results['video']) && empty($results['video_id']) ) {
                if ( $type == 'youtube' ) {
                    echo  '<div class="notice error"><p>' . sprintf( __( 'No videos found matching "%s"', 'foogallery' ), '<strong>' . stripslashes_deep( $query_str ) . '</strong>' ) . '</p></div>' ;
                }
                if ( $type == 'vimeo' ) {
                    echo  '<div class="notice error"><p>' . __( 'Invalid ID or URL', 'foogallery' ) . '</p></div>' ;
                }
                exit;
            }


            if ( $type == 'vimeo' ) {

                if ( empty($isstream) ) {
                    $video = $results;
                    include plugin_dir_path( __FILE__ ) . 'views/general-single-result.php';
                } else {
                    include plugin_dir_path( __FILE__ ) . 'views/vimeo-playlist-result.php';
                    foreach ( $results['stream']['clips'] as $index => $video ) {
                        include plugin_dir_path( __FILE__ ) . 'views/vimeo-result.php';
                    }
                }

            } else {
                if ( !empty($isplaylist) ) {
                    include plugin_dir_path( __FILE__ ) . 'views/youtube-playlist-result.php';
                }

                if ( !empty($results['provider_name']) ) {
                    $video = $results;
                    include plugin_dir_path( __FILE__ ) . 'views/youtube-playlist-result.php';
                }


                if ( !empty($results['video']) ) {
                    echo  '<span id="import-playlist-id" data-loading="' . esc_attr( __( 'Importing Video(s)', 'foogallery' ) ) . '"></span>' ;
                    foreach ( $results['video'] as $index => $video ) {
                        include plugin_dir_path( __FILE__ ) . 'views/youtube-result.php';
                    }
                }

                if ( !empty($results['hits']) && ($index + 1) * $page < $results['hits'] ) {
                    echo  '<div class="foovideo-loadmore button" data-page="' . ($page + 1) . '">' . __( 'Load More', 'foogallery' ) . '</div>' ;
                }
            }

            exit;
        }

        /**
         * Enqueue admin styles and scripts
         */
        function enqueue_admin_stylescripts()
        {
            $screen = get_current_screen();
            if ( !is_object( $screen ) || $screen->id != "foogallery" ) {
                return;
            }
            $js = plugin_dir_url( __FILE__ ) . 'js/admin-gallery-foo_video.js';
            wp_enqueue_script(
                'foo_video_admin',
                $js,
                array( 'jquery' ),
                FOOGALLERY_VERSION
            );
            $css = plugin_dir_url( __FILE__ ) . 'css/gallery-foo_video-admin.css';
            wp_enqueue_style(
                'foo_video_admin',
                $css,
                array(),
				FOOGALLERY_VERSION
            );
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
				'exclusions'  => array( 'audio', 'video' ),
			);
			$fields[ 'data-height' ] = array(
				'label'       =>  __( 'Override Height', 'foogallery' ),
				'input'       => 'text',
				'exclusions'  => array( 'audio', 'video' ),
			);
//            $fields['foovideo_video_type'] = array(
//                'label'      => __( 'Video Source', 'foogallery' ),
//                'input'      => 'select',
//                'options'    => array(
//                'youtube' => __( 'YouTube', 'foogallery' ),
//                'vimeo'   => __( 'Vimeo', 'foogallery' ),
//            ),
//                'exclusions' => array( 'audio', 'video' ),
//            );
//            $fields['foovideo_video_description'] = array(
//                'label'      => __( 'Video Description', 'foogallery' ),
//                'input'      => 'text',
//                'helps'      => __( 'Video description.', 'foogallery' ),
//                'exclusions' => array( 'audio', 'video' ),
//            );
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
        public function all_template_fields( $fields )
        {
            $fields[] = array(
                'id'      => 'foovideo_video_overlay',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Video Hover Icon', 'foogallery' ),
                'type'    => 'icon',
                'default' => 'video-icon-default',
                'choices' => array(
					'video-icon-default' => array(
						'label' => __( 'Default Icon', 'foogallery' ),
						'img'   => plugin_dir_url( __FILE__ ) . 'assets/video-icon-default.png',
					),
						'video-icon-1'       => array(
						'label' => __( 'Icon 1', 'foogallery' ),
						'img'   => plugin_dir_url( __FILE__ ) . 'assets/video-icon-1.png',
					),
						'video-icon-2'       => array(
						'label' => __( 'Icon 2', 'foogallery' ),
						'img'   => plugin_dir_url( __FILE__ ) . 'assets/video-icon-2.png',
					),
						'video-icon-3'       => array(
						'label' => __( 'Icon 3', 'foogallery' ),
						'img'   => plugin_dir_url( __FILE__ ) . 'assets/video-icon-3.png',
					),
						'video-icon-4'       => array(
						'label' => __( 'Icon 4', 'foogallery' ),
						'img'   => plugin_dir_url( __FILE__ ) . 'assets/video-icon-4.png',
					),
				),
            );
            $fields[] = array(
                'id'      => 'foovideo_sticky_icon',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Sticky Video Icon', 'foogallery' ),
                'desc'    => __( 'Always show the video icon for videos in the gallery, and not only when you hover.', 'foogallery' ),
                'type'    => 'radio',
                'default' => 'no',
                'spacer'  => '<span class="spacer"></span>',
                'choices' => array(
                	'video-icon-sticky' => __( 'Yes', 'foogallery' ),
                	'no'                  => __( 'No', 'foogallery' ),
            	),
            );
            $fields[] = array(
                'id'      => 'foovideo_video_size',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Video Size', 'foogallery' ),
                'desc'    => __( 'The default video size when opening videos in FooBox. This can be overridden on each individual video by editing the attachment info, and changing the Data Width and Data Height properties.', 'foogallery' ),
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
                'id'      => 'foovideo_autoplay',
                'section' => __( 'Video', 'foogallery' ),
                'title'   => __( 'Autoplay', 'foogallery' ),
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

        /**
         * @uses "foogallery_attachment_html_link_attributes" filter
         *
         * @param $attr
         * @param $args
         * @param object|FooGalleryAttachment $object
         */
        public function alter_video_link_attributes( $attr, $args, $object )
        {
            global  $current_foogallery_template ;
            $video_info = get_post_meta( $object->ID, FOOVIDEO_POST_META, true );

            if ( $video_info && isset( $video_info['id'] ) ) {
                $video_id = $video_info['id'];
                $type = $video_info['type'];
                $url = foogallery_foovideo_get_video_url_from_attachment( $object );
                if ( 'videoslider' !== $current_foogallery_template ) {

                    if ( !isset( $attr['class'] ) ) {
                        $attr['class'] = ' foogallery';
                    } else {
                        $attr['class'] .= ' foogallery';
                    }

                }
                $lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
                $attr['href'] = $url;
                //if we have no widths or heights then use video default size

                if ( !isset( $attr['data-width'] ) ) {
                    $size = foogallery_gallery_template_setting( 'foovideo_video_size', '640x360' );
                    list( $width, $height ) = explode( 'x', $size );
                    $attr['data-width'] = $width;
                    $attr['data-height'] = $height;
                }

                //override width
				$override_width = get_post_meta( $object->ID, '_data-width', true );
				if ( !empty( $override_width ) ) {
					$attr['data-width'] = intval( $override_width );
				}

				//override height
				$override_height = get_post_meta( $object->ID, '_data-height', true );
				if ( !empty( $override_height ) ) {
					$attr['data-height'] = intval( $override_height );
				}


                if ( class_exists( 'Foobox_Free' ) && ('foobox' == $lightbox || 'foobox-free' == $lightbox) ) {
                    //we want to add some JS to the front-end if we are using FooBox Free
                    $js = plugin_dir_url( __FILE__ ) . 'js/foobox.video.min.js';
                    wp_enqueue_script(
                        'foo_video',
                        $js,
                        array( 'jquery' ),
						FOOGALLERY_VERSION
                    );
                }

                //if no lightbox is being used then force to open in new tab
                if ( 'unknown' === $lightbox || 'none' === $lightbox ) {
                    $attr['target'] = '_blank';
                }
            }

            return $attr;
        }

        public function add_media_templates()
        {
            $screen = get_current_screen();
            if ( !is_object( $screen ) || $screen->id != "foogallery" ) {
                return;
            }
            include dirname( __FILE__ ) . '/views/media-ui.php';
        }

        public function foogallery_build_class_attribute( $classes )
        {
            global  $current_foogallery_template ;
            //first determine if the gallery has any videos
            //then get the selected video icon
            $video_hover_icon = foogallery_gallery_template_setting( 'foovideo_video_overlay', 'video-icon-default' );

            if ( 'videoslider' === $current_foogallery_template ) {
                switch ( $video_hover_icon ) {
                    case 'video-icon-default':
                        $video_hover_icon = 'rvs-flat-circle-play';
                        break;
                    case 'video-icon-1':
                        $video_hover_icon = 'rvs-plain-arrow-play';
                        break;
                    case 'video-icon-2':
                        $video_hover_icon = 'rvs-youtube-play';
                        break;
                    case 'video-icon-3':
                        $video_hover_icon = 'rvs-bordered-circle-play';
                        break;
                    default:
                        $video_hover_icon = '';
                }
            } else {
                //leave it as is for other galleries
            }

            //include the video icon class
            $classes[] = $video_hover_icon;
            //get the video icon sticky state
            $video_icon_sticky = foogallery_gallery_template_setting( 'foovideo_sticky_icon', '' );
            if ( 'videoslider' === $current_foogallery_template && '' === $video_icon_sticky ) {
                $video_icon_sticky = 'rvs-show-play-on-hover';
            }
            //include the video sticky class
            $classes[] = $video_icon_sticky;
            return $classes;
        }

        /**
         * Load CSS if there are videos
         *
         * @uses foogallery_foogallery_instance_after_load
         *
         * @param $obj
         */
        public function maybe_load_css_in_other_templates( $obj )
        {
            $ids = $obj->attachment_ids;
            if ( !empty($ids) ) {
                foreach ( $ids as $id ) {
                    $video_info = get_post_meta( $id, FOOVIDEO_POST_META, true );

                    if ( isset( $video_info['id'] ) && 0 < absint( $video_info['id'] ) ) {
                        $css = plugin_dir_url( __FILE__ ) . 'css/gallery-foo_video.css';
                        foogallery_enqueue_style(
                            'foo_video',
                            $css,
                            array(),
							FOOGALLERY_VERSION
                        );
                        return;
                    }

                }
            }
        }

        /**
         * Enqueue any script or stylesheet file dependencies that FooGallery_Pro_Video relies on
         *
         * @param $foogallery FooGallery
         */
        function enqueue_foovideo_dependencies( $foogallery )
        {

            if ( $foogallery ) {
                $video_count = foogallery_foovideo_get_gallery_video_count( $foogallery->ID );

                if ( $video_count > 0 ) {
                    $css = plugin_dir_url( __FILE__ ) . 'css/gallery-foo_video.css';
                    wp_enqueue_style(
                        'foo_video',
                        $css,
                        array(),
						FOOGALLERY_VERSION
                    );
                    $lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );
                    //we want to add some JS to the front-end ONLY if we are using FooBox Free

                    if ( class_exists( 'Foobox_Free' ) && ('foobox' == $lightbox || 'foobox-free' == $lightbox) ) {
                        $js = plugin_dir_url( __FILE__ ) . 'js/foobox.video.min.js';
                        wp_enqueue_script(
                            'foo_video',
                            $js,
                            array( 'jquery' ),
							FOOGALLERY_VERSION
                        );
                    }

                }

            }

        }

        public function calculate_video_count( $post_id )
        {
            //calculate the video count
            $video_count = foogallery_foovideo_calculate_gallery_video_count( $post_id );
            //store the video in post meta to save time later
            update_post_meta( $post_id, FOOVIDEO_POST_META_VIDEO_COUNT, $video_count );
        }

        public function include_video_count( $image_count_text, $gallery )
        {
            $count = sizeof( $gallery->attachment_ids );
            $video_count = foogallery_foovideo_get_gallery_video_count( $gallery->ID );
            $image_count = $count - $video_count;
            return foogallery_foovideo_gallery_image_count_text( $count, $image_count, $video_count );
        }

        public function include_video_settings( $settings )
        {
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