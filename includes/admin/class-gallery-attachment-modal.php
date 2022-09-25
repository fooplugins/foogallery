<?php
/*
 * FooGallery Admin Gallery Attachment Modal class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Attachment_Modal' ) ) {

	class FooGallery_Admin_Gallery_Attachment_Modal {

		/**
		 * Default variables set for image modal
		 */
		private $img_modal = array(
			'img_id' => 0,
			'gallery_id' => 0,
			'img_post' => '',
			'image_attributes' => array(),
			'img_title' => '',
			'caption' => '',
			'description' => '',
			'post_date' => '',
			'file_type' => '',
			'author_id' => 0,
			'author_name' => '',
			'file_url' => '',
			'image_alt' => '',
			'custom_url' => '',
			'custom_target' => '',
			'attachment_watermark' => '',
			'custom_class' => '',
			'override_type' => '',
			'data_width' => '',
			'data_height' => '',
			'foogallery_ribbon' => '',
			'foogallery_ribbon_text' => '',
			'foogallery_button_text' => '',
			'foogallery_button_url' => '',
			'foogallery_product' => '',
			'panning' => '',
			'progress' => '',
			'img_categories' => array(),
			'img_tags' => array(),
			'nonce' => '',
			'meta' => array(),
			'foogallery_crop_pos' => 'foogallery[crop_pos]',
			'foogallery_crop_pos_val' => 'center,center',
			'foogallery_override_thumbnail' => '',
			'image_size' => 'medium',
			'img_path' => '',
			'foogallery_attachments' => array(),
			'slide_num' => -1,
			'prev_slide' => FALSE,
			'next_slide' => FALSE,
			'next_img_id' => 0,
			'prev_img_id' => 0,
			'alternate_img_src' => '',
			'override_class' => '',
		);

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
            add_action( 'admin_footer', array( $this, 'foogallery_image_editor_modal' ) );

            add_filter( 'foogallery_attachment_custom_fields', array( $this, 'foogallery_add_override_thumbnail_field' ) );

			add_action( 'wp_ajax_foogallery_attachment_modal_open', array( $this, 'ajax_open_modal' ) );
			add_action( 'wp_ajax_foogallery_attachment_modal_save', array( $this, 'ajax_save_modal' ) );

			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_main' ), 10 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_taxonomies' ), 20 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_thumbnails' ), 30 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_watermark' ), 40 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_exif' ), 50 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_more' ), 60 );

            add_action( 'foogallery_attachment_modal_before_thumbnail', array( $this, 'display_attachment_info' ), 10, 1 );

            add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_main' ), 10, 1 );
			add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_taxonomies' ), 20, 1 );
			add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_thumbnails' ), 30, 1 );
			add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_watermark' ), 40, 1 );
			add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_exif' ), 50, 1 );
			add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_more' ), 60, 1 );



			add_action( 'wp_ajax_foogallery_remove_alternate_img', array( $this, 'ajax_alternate_img_remove' ) );

			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_main' ), 10, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_taxonomies' ), 20, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_thumbnails' ), 30, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_watermark' ), 40, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_exif' ), 50, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_more' ), 60, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_info' ), 70, 4 );

			add_action( 'foogallery_attachment_modal_after_tabs', array( $this, 'foogallery_img_modal_save_btn' ) );

			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_main' ), 10, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_taxonomies' ), 20, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_thumbnails' ), 30, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_watermark' ), 40, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_exif' ), 50, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_more' ), 60, 2 );
			
			// Callback for the generate watermark ajax call.
			add_action( 'wp_ajax_foogallery_attachment_protection_generate', array( $this, 'ajax_attachment_generate_watermark' ) );

			// Append some custom script after the gallery settings metabox.
			add_action( 'foogallery_after_render_gallery_settings_metabox', array( $this, 'append_script_for_watermarking' ), 10, 1 );
		}

		/**
		 * Generate image edit modal on gallery creation
		 */ 
		public function ajax_open_modal() {

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery_attachment_modal_open' ) ) {
				die ( 'Busted!');
			}

			$modal_data = $this->build_modal_data( $_POST );

			ob_start() ?>

			<div class="foogallery-image-edit-main" data-img_id="<?php echo $modal_data['img_id']; ?>" data-gallery_id="<?php echo $modal_data['gallery_id']; ?>">
				<?php do_action( 'foogallery_attachment_modal_before_tab_container', $modal_data ); ?>
                <div class="foogallery-image-edit-view">
                    <?php

                    do_action( 'foogallery_attachment_modal_before_thumbnail', $modal_data );

                    if ( $modal_data['image_attributes'] ) { ?>
                        <img src="<?php echo $modal_data['image_attributes'][0]; ?>" width="<?php echo $modal_data['image_attributes'][1]; ?>" height="<?php echo $modal_data['image_attributes'][2]; ?>" />
                    <?php } ?>
                </div>
                <div class="foogallery-image-edit-button">
                    <a target="_blank" href="<?php echo get_admin_url().'upload.php?item='.$modal_data['img_id'];?>&mode=edit" class="button"><?php _e('Edit Image', 'foogallery'); ?></a>
                    <a target="_blank" href="<?php echo $modal_data['img_path'];?>" class="button"><?php _e('Open Full Size Image', 'foogallery'); ?></a>
                </div>
			</div>

			<div class="foogallery-image-edit-meta">

				<?php do_action( 'foogallery_attachment_modal_before_tabs', $modal_data ); ?>

				<div class="tabset">
					<?php do_action( 'foogallery_attachment_modal_tabs_view', $modal_data ); ?>
				</div>
				<div class="tab-panels">
					<form id="foogallery_attachment_modal_save_form" method="post" enctype="multipart/form-data">
						<input type="hidden" name="action" value="foogallery_attachment_modal_save">
						<input type="hidden" name="nonce" value="<?php echo $modal_data['nonce']; ?>">
						<input type="hidden" name="img_id" value="<?php echo $modal_data['img_id']; ?>">
						<?php do_action( 'foogallery_attachment_modal_tab_content', $modal_data ); ?>
					</form>
				</div>

				<?php do_action( 'foogallery_attachment_modal_after_tabs', $modal_data ); ?>
				
			</div>
            <?php

            do_action( 'foogallery_attachment_modal_after_tab_container', $modal_data );
				
			wp_send_json( array(
                'html' => ob_get_clean(),
                'prev_slide' => $modal_data['prev_slide'],
                'next_slide' => $modal_data['next_slide'],
                'next_img_id' => $modal_data['next_img_id'],
                'prev_img_id' => $modal_data['prev_img_id'],
                'override_thumbnail' => $modal_data['foogallery_override_thumbnail'],
                'current_tab' => $modal_data['current_tab']
            ) );
		}

		/**
		 * 	Admin modal wrapper for gallery image edit 
		 */ 
		public function foogallery_image_editor_modal() {
			$modal_style = foogallery_get_setting( 'hide_admin_gallery_attachment_modal' );
			?>
			<div id="foogallery-image-edit-modal" style="display: none;" data-img_type="normal" data-nonce="<?php echo wp_create_nonce( 'foogallery_attachment_modal_open' ); ?>" data-gallery_id="<?php echo $_GET['post']; ?>" data-modal_style="<?php echo $modal_style; ?>">
				<div class="media-modal wp-core-ui">
					<div class="media-modal-content">
						<div class="edit-attachment-frame mode-select hide-menu hide-router">
							<div class="edit-media-header">
								<button class="left dashicons"><span class="screen-reader-text"><?php _e( 'Edit previous attachment in the gallery', 'foogallery' ); ?></span></button>
								<button class="right dashicons"><span class="screen-reader-text"><?php _e( 'Edit next attachment in the gallery', 'foogallery' ); ?></span></button>
								<button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php _e('Close dialog', 'foogallery'); ?></span></span></button>
							</div>
							<div class="media-frame-title"><h1><?php _e('Edit Attachment Details', 'foogallery'); ?></h1></div>
							<div class="media-frame-content">
								<div class="attachment-details save-ready">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php }

		/**
		 * Adds a custom field to the attachments for override thumbnail
		 *
		 * @param $fields array
		 *
		 * @return array
		 */
		public function foogallery_add_override_thumbnail_field( $fields ) {
			$fields['foogallery_override_thumbnail'] = array(
				'label'       =>  __( 'Override Thumbnail', 'foogallery' ),
				'input'       => 'text',
				'helps'       => __( 'Add another image to override this attachment', 'foogallery' ),
			);

			return $fields;
		}

		/**
		 * Save modal form data to database
		 */
		public function ajax_save_modal() {

			$foogallery = ( isset( $_POST['foogallery'] ) ? $_POST['foogallery'] : array() );

			if ( !is_array( $foogallery ) || empty( $foogallery ) ) {
				return;
			}

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery-modal-nonce' ) ) {
				die ( 'Busted!');
			}

			$img_id = intval( sanitize_text_field( $_POST['img_id'] ) );

			if ( $img_id > 0 ) {
				do_action( 'foogallery_attachment_save_data', $img_id, $foogallery );
			}
			
			wp_die();
		}

		/**
		 * Save main tab data content
		 * 
		 * @param $img_id int attachment id to update data
		 * 
		 * @param $foogallery array of form post data
		 * 
		 */

		 public function foogallery_attachment_save_data_main( $img_id, $foogallery ) {

			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {

				$foogallery_post = array(
					'ID' => $img_id
				);

				foreach( $foogallery as $key => $val ) {
					
					if ( $key == 'title' ) {
						$foogallery_post['post_title'] = $val;
					}
					if ( $key == 'caption' ) {
						$foogallery_post['post_excerpt'] = $val;
					}
					if ( $key == 'description' ) {
						$foogallery_post['post_content'] = $val;
					}

					// Update post meta values
					if ( $key == 'alt-text' ) {
						update_post_meta( $img_id, '_wp_attachment_image_alt', $val );
					}
					if ( $key == 'custom-url' ) {
						update_post_meta( $img_id, '_foogallery_custom_url', $val );
					}
					if ( $key == 'custom-target' ) {
						update_post_meta( $img_id, '_foogallery_custom_target', $val );
					}
					if ( $key == 'custom-class' ) {
						update_post_meta( $img_id, '_foogallery_custom_class', $val );
					}
				}

				if ( is_array( $foogallery_post ) && count( $foogallery_post ) > 1 ) {
					// Update the post into the database
					wp_update_post( $foogallery_post );
				}
			}

		}

		/**
		 * Save taxonomies tab data content
		 * 
		 * @param $img_id int attachment id to update data
		 * 
		 * @param $foogallery array of form post data
		 * 
		 */

		public function foogallery_attachment_save_data_taxonomies( $img_id, $foogallery ) {

			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {

				foreach( $foogallery as $key => $val ) {
					if ( $key == 'tags' ) {
						$tags = array();
						$selected_tags = explode( ',', $val );
						foreach ( $selected_tags as $tag ) {
							$tags[] = (int) $tag;
						}
						wp_set_object_terms( $img_id, $tags, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, false );
					}
					if ( $key == 'taxonomies' ) {
						$categories = array();
						$selected_cats = explode( ',', $val );
						foreach ( $selected_cats as $category ) {
							$categories[] = (int) $category;
						}
						wp_set_object_terms( $img_id, $categories, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, false );
					}
				}
			}

		}

		/**
		 * Save thumbnails tab data content
		 * 
		 * @param $img_id int attachment id to update data
		 * 
		 * @param $foogallery array of form post data
		 * 
		 */

		public function foogallery_attachment_save_data_thumbnails( $img_id, $foogallery ) {

			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {

				foreach( $foogallery as $key => $val ) {
					if ( $key == 'crop_pos' ) {
						update_post_meta( $img_id, 'foogallery_crop_pos', $val );
					}
					if ( $key == 'override-thumbnail-id' ) {
						update_post_meta( $img_id, 'foogallery_override_thumbnail', $val );
					}
				}

			}

		}

		/**
		 * Save watermark tab data content
		 * 
		 * @param $img_id int attachment id to update data
		 * 
		 * @param $foogallery array of form post data
		 * 
		 */

		public function foogallery_attachment_save_data_watermark( $img_id, $foogallery ) {

			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {

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

		public function foogallery_attachment_save_data_exif( $img_id, $foogallery ) {

			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {

				$image_meta = wp_get_attachment_metadata( $img_id );
				foreach( $foogallery as $key => $val ) {
					if ( $key == 'aperture' ) {
						$image_meta['image_meta']['aperture'] = $val;
					}
					if ( $key == 'camera' ) {
						$image_meta['image_meta']['camera'] = $val;
					}
					if ( $key == 'created-timestamp' ) {
						$image_meta['image_meta']['created_timestamp'] = $val;
					}
					if ( $key == 'shutter-speed' ) {
						$image_meta['image_meta']['shutter_speed'] = $val;
					}
					if ( $key == 'focal-length' ) {
						$image_meta['image_meta']['focal_length'] = $val;
					}
					if ( $key == 'iso' ) {
						$image_meta['image_meta']['iso'] = $val;
					}
					if ( $key == 'orientation' ) {
						$image_meta['image_meta']['orientation'] = $val;
					}
					if ( $key == 'keywords' ) {
						$keywords = explode(',', $val);
						$image_meta['image_meta']['keywords'] = $keywords;
					}
				}

				wp_update_attachment_metadata( $img_id, $image_meta );
				
			}

		}

		/**
		 * Save more tab data content
		 * 
		 * @param $img_id int attachment id to update data
		 * 
		 * @param $foogallery array of form post data
		 * 
		 */

		public function foogallery_attachment_save_data_more( $img_id, $foogallery ) {

			if ( is_array( $foogallery ) && !empty( $foogallery ) ) {
				foreach( $foogallery as $key => $val ) {
					if ( $key == 'data-width' ) {						
						update_post_meta( $img_id, '_data-width', $val );
					}
					if ( $key == 'data-height' ) {
						update_post_meta( $img_id, '_data-height', $val );
					}
					if ( $key == 'override-type' ) {
						update_post_meta( $img_id, '_foogallery_override_type', $val );
					}
					if ( $key == 'button-text' ) {
						update_post_meta( $img_id, '_foogallery_button_text', $val );
					}
					if ( $key == 'button-url' ) {
						update_post_meta( $img_id, '_foogallery_button_url', $val );
					}
					if ( $key == 'ribbon' ) {
						update_post_meta( $img_id, '_foogallery_ribbon', $val );
					}
					if ( $key == 'ribbon-text' ) {
						update_post_meta( $img_id, '_foogallery_ribbon_text', $val );
					}
					if ( $key == 'product-id' ) {
						update_post_meta( $img_id, '_foogallery_product', $val );
					}
				}
			}

		}

		/**
		 * Builds up the state used to populate the modal.
		 * 
		 * @param $data array
		 * @return array
		 */
		private function build_modal_data( $data = array() ) {

            $modal_data = array(
                'img_id' => 0,
                'gallery_id' => 0,
            );

            if ( is_array ( $data ) && isset( $data['img_id'] ) && isset( $data['gallery_id'] ) ) {
                $modal_data['img_id'] = $attachment_id = intval( sanitize_text_field( $data['img_id'] ) );
                $modal_data['gallery_id'] = $gallery_id = intval( sanitize_text_field( $data['gallery_id'] ) );
                $modal_data['current_tab'] = isset( $data['current_tab'] ) ? sanitize_text_field( $data['current_tab'] ) : '';
                $modal_data['nonce'] = wp_create_nonce( 'foogallery-modal-nonce' );
                $modal_data = apply_filters( 'foogallery_attachment_modal_data', $modal_data, $data, $attachment_id, $gallery_id );
            }

            return $modal_data;
		}

		/**
		 * Image modal main tab data update
		 */
		public function foogallery_attachment_modal_data_main( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                $attachment_post = get_post( $attachment_id );

                if ( is_a( $attachment_post, 'WP_Post' ) ) {
                    $modal_data['file_url'] = get_attached_file( $attachment_id );
                    $modal_data['file_name'] = basename( $modal_data['file_url'] );
                    $modal_data['file_type'] = $attachment_post->post_mime_type;
                    $modal_data['author_id'] = intval( $attachment_post->post_author );
                    $modal_data['author_name'] = get_the_author_meta( 'display_name', $modal_data['author_id'] );
                    $modal_data['post_date'] = date('F d, Y', strtotime( $attachment_post->post_date ) );
                    $modal_data['img_title'] = $attachment_post->post_title;
                    $modal_data['caption'] = $attachment_post->post_excerpt;
                    $modal_data['description'] = $attachment_post->post_content;
                    $modal_data['image_alt'] = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
                    $modal_data['meta'] = wp_get_attachment_metadata( $attachment_id );

                    // Get attachment file size.
                    $file_size = false;
                    if ( isset( $modal_data['meta']['filesize'] ) ) {
                        $file_size = $modal_data['meta']['filesize'];
                    } elseif ( file_exists( $modal_data['file_url'] ) ) {
                        $file_size = wp_filesize( $modal_data['file_url'] );
                    }
                    if ( ! empty( $file_size ) ) {
                        $modal_data['file_size'] = size_format( $file_size );
                    }

                    // Get attachment dimensions.
                    $media_dims = '';
                    if ( isset( $modal_data['meta']['width'], $modal_data['meta']['height'] ) ) {
                        $media_dims = "{$modal_data['meta']['width']}&nbsp;&times;&nbsp;{$modal_data['meta']['height']}";
                    }
                    /** This filter is documented in wp-admin/includes/media.php */
                    $modal_data['media_dims'] = apply_filters( 'media_meta', $media_dims, $attachment_post );

                    $modal_data['custom_url'] = get_post_meta( $attachment_id, '_foogallery_custom_url', true );
                    $modal_data['custom_target'] = get_post_meta( $attachment_id, '_foogallery_custom_target', true );
                    $modal_data['custom_class'] = get_post_meta( $attachment_id, '_foogallery_custom_class', true );
                }
            }

			return $modal_data;
		}
			
		/**
		 * Image modal taxonomies & tags tab data update
		 */
		public function foogallery_attachment_modal_data_taxonomies( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {

                // Rather use $taxonomies = get_object_taxonomies( 'post' ); and loop through all taxonomies for an attachment

                $categories = get_the_terms( $attachment_id, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
                $tags = get_the_terms( $attachment_id, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );

                if (is_array($categories) && !empty ($categories)) {
                    $modal_data['img_categories'] = $categories;
                }

                if (is_array($categories) && !empty ($categories)) {
                    $modal_data['img_tags'] = $tags;
                }
            }
			return $modal_data;
		}

		/**
		 * Image modal thumbnails tab data update
		 */
		public function foogallery_attachment_modal_data_thumbnails( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {

                $modal_data['foogallery_crop_pos_val'] = get_post_meta( $attachment_id, 'foogallery_crop_pos', true );

                $foogallery_override_thumbnail = get_post_meta( $attachment_id, '_foogallery_override_thumbnail', true );

                if ( isset( $foogallery_override_thumbnail ) ) {

                    $modal_data['foogallery_override_thumbnail'] = $foogallery_override_thumbnail;
                    $modal_data['override_class'] = 'is-override-thumbnail';
                    $alternate_thumb_img = wp_get_attachment_image_src( $foogallery_override_thumbnail );

                    if ( is_array( $alternate_thumb_img ) && !empty( $alternate_thumb_img ) ) {
                        $modal_data['alternate_img_src'] = $alternate_thumb_img[0];
                    }
                }
            }

			return $modal_data;
		}

		/**
		 * Image modal watermark tab data update
		 */
		public function foogallery_attachment_modal_data_watermark( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                $modal_data['attachment_watermark'] = get_post_meta( $attachment_id, FOOGALLERY_META_WATERMARK, true );
            }
			return $modal_data;
		}

		/**
		 * Image modal exif tab data update
		 */
		public function foogallery_attachment_modal_data_exif( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                //Get any EXIF data
            }
			return $modal_data;
		}

		/**
		 * Image modal more tab data update
		 */
		public function foogallery_attachment_modal_data_more( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                $modal_data['data_width'] =             get_post_meta( $attachment_id, '_data-width', true );
                $modal_data['data_height'] =            get_post_meta( $attachment_id, '_data-height', true );
                $modal_data['override_type'] =          get_post_meta( $attachment_id, '_foogallery_override_type', true );
                $modal_data['foogallery_button_text'] = get_post_meta( $attachment_id, '_foogallery_button_text', true );
                $modal_data['foogallery_button_url'] =  get_post_meta( $attachment_id, '_foogallery_button_url', true );
                $modal_data['foogallery_ribbon'] =      get_post_meta( $attachment_id, '_foogallery_ribbon', true );
                $modal_data['foogallery_ribbon_text'] = get_post_meta( $attachment_id, '_foogallery_ribbon_text', true );
                $modal_data['foogallery_product'] =     get_post_meta( $attachment_id, '_foogallery_product', true );
            }
			return $modal_data;
		}

		/**
		 * Image modal info tab data update
		 */
		public function foogallery_attachment_modal_data_info( $modal_data, $data, $attachment_id, $gallery_id ) {

            if ( $attachment_id > 0 ) {
                $modal_data['image_attributes'] = wp_get_attachment_image_src( $attachment_id, 'medium' );
                $full_img_path = wp_get_attachment_image_src( $attachment_id, 'full' );
                $modal_data['img_path'] = $full_img_path[0];

                $gallery_attachments = get_post_meta( $gallery_id, FOOGALLERY_META_ATTACHMENTS, true);

                if ( is_array( $gallery_attachments ) && !empty ( $gallery_attachments ) ) {
                    $modal_data['gallery_attachments'] = $gallery_attachments;

                    $current_slide_id = 0;
                    $prev_slide_enabled = false;
                    $next_slide_enabled = false;
                    $prev_slide_id = 0;
                    $next_slide_id = 0;
                    foreach ( $gallery_attachments as $gallery_attachment_id ) {
                        if ( $attachment_id === intval( $gallery_attachment_id ) ) {
                            //$prev_slide_id = $current_slide_id;
                            $prev_slide_enabled = $prev_slide_id > 0;
                            $current_slide_id = $attachment_id;
                        } else if ( $next_slide_id > 0 ) {
                            break;
                        } else if ( $current_slide_id > 0 ) {
                            $next_slide_id = intval($gallery_attachment_id);
                            $next_slide_enabled = true;
                        } else {
                            $prev_slide_id = intval( $gallery_attachment_id );
                        }
                    }

                    if ( $current_slide_id >= 0 ) {
                        $modal_data['prev_slide'] = $prev_slide_enabled;
                        $modal_data['next_slide'] = $next_slide_enabled;
                        $modal_data['prev_img_id'] = $prev_slide_id;
                        $modal_data['next_img_id'] = $next_slide_id;
                    }
                }
            }

			return $modal_data;
		}

		/**
		 * Image modal main tab title
		 */
		public function display_tab_main() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-main">
				<input type="radio" name="tabset" id="foogallery-tab-main" aria-controls="foogallery-panel-main" checked>
				<label for="foogallery-tab-main"><?php _e('Main', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal taxonomies & tags title
		 */
		public function display_tab_taxonomies() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-taxonomies">
				<input type="radio" name="tabset" id="foogallery-tab-taxonomies" aria-controls="foogallery-panel-taxonomies">
				<label for="foogallery-tab-taxonomies"><?php _e('Taxonomies', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal thumbnails tab title
		 */
		public function display_tab_thumbnails() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-thumbnails">
				<input type="radio" name="tabset" id="foogallery-tab-thumbnails" aria-controls="foogallery-panel-thumbnails">
				<label for="foogallery-tab-thumbnails"><?php _e('Thumbnails', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal watermark tab title
		 */
		public function display_tab_watermark() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-watermark">
				<input type="radio" name="tabset" id="foogallery-tab-watermark" aria-controls="foogallery-panel-watermark">
				<label for="foogallery-tab-watermark"><?php _e('Watermark', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal EXIF tab title
		 */
		public function display_tab_exif() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-exif">
				<input type="radio" name="tabset" id="foogallery-tab-exif" aria-controls="foogallery-panel-exif">
				<label for="foogallery-tab-exif"><?php _e('EXIF', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal more tab title
		 */
		public function display_tab_more() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-more">
				<input type="radio" name="tabset" id="foogallery-tab-more" aria-controls="foogallery-panel-more">
				<label for="foogallery-tab-more"><?php _e('More', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal main tab content
		 */
		public function display_tab_content_main( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-main" class="tab-panel active" data-nonce="<?php echo $modal_data['nonce'];?>">
						<div class="settings">
							<span class="setting" data-setting="title">
								<label for="attachment-details-two-column-title" class="name"><?php _e('Title', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-title" name="foogallery[title]" value="<?php echo $modal_data['img_title'];?>">
							</span>								
							<span class="setting" data-setting="caption">
								<label for="attachment-details-two-column-caption" class="name"><?php _e('Caption', 'foogallery'); ?></label>
								<textarea id="attachment-details-two-column-caption" name="foogallery[caption]"><?php echo $modal_data['caption'];?></textarea>
							</span>
							<span class="setting" data-setting="description">
								<label for="attachment-details-two-column-description" class="name"><?php _e('Description', 'foogallery'); ?></label>
								<textarea id="attachment-details-two-column-description" name="foogallery[description]"><?php echo $modal_data['description'];?></textarea>
							</span>
							<span class="setting has-description" data-setting="alt">
								<label for="attachment-details-two-column-alt-text" class="name"><?php _e('ALT Text', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-alt-text" name="foogallery[alt-text]" value="<?php echo $modal_data['image_alt'];?>" aria-describedby="alt-text-description">
							</span>
							<span class="setting" data-setting="custom_url">
								<label for="attachments-foogallery-custom-url" class="name"><?php _e('Custom URL', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-custom-url" name="foogallery[custom-url]" value="<?php echo $modal_data['custom_url'];?>">
							</span>
							<span class="setting" data-setting="custom_target">
								<label for="attachments-foogallery-custom-target" class="name"><?php _e('Custom Target', 'foogallery'); ?></label>
								<select name="foogallery[custom-target']" id="attachments-foogallery-custom-target">
									<option value="default" <?php selected( 'default', $modal_data['custom_target'], true ); ?>><?php _e('Default', 'foogallery'); ?></option>
									<option value="_blank" <?php selected( '_blank', $modal_data['custom_target'], true ); ?>><?php _e('New tab (_blank)', 'foogallery'); ?></option>
									<option value="_self" <?php selected( '_self', $modal_data['custom_target'], true ); ?>><?php _e('Same tab (_self)', 'foogallery'); ?></option>
									<option value="foobox" <?php selected( 'foobox', $modal_data['custom_target'], true ); ?>><?php _e('FooBox', 'foogallery'); ?></option>
								</select>
							</span>
							<span class="setting" data-setting="custom_class">
								<label for="attachments-foogallery-custom-class" class="name"><?php _e('Custom Class', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-custom-class" name="foogallery[custom-class]" value="<?php echo $modal_data['custom_class'];?>">
							</span>	
							<span class="setting" data-setting="file_url">
								<label for="attachments-foogallery-file-url" class="name"><?php _e('File URL', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-file-url" value="<?php echo $modal_data['file_url'];?>" readonly>
							</span>
							<span class="setting" data-setting="file_url_copy">
								<label for="attachments-foogallery-file-url-copy" class="name"><?php _e('', 'foogallery'); ?></label>
								<span class="copy-to-clipboard-container">
									<button type="button" class="button button-small copy-attachment-file-url" data-clipboard-target="#attachments-foogallery-file-url"><?php _e('Copy URL to clipboard', 'foogallery'); ?></button>
									<span class="success hidden" aria-hidden="true"><?php _e('Copied!', 'foogallery'); ?></span>
								</span>
							</span>
						</div>
					</section>
					<?php
				}
			}
		}

		/**
		 * Image modal taxonomies & tags tab content
		 */
		public function display_tab_content_taxonomies( $modal_data ) {
            if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
                if ( $modal_data['img_id'] > 0 ) {
					$selected_categories = $selected_tags = array();
					$tags = get_terms( array(
						'taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
						'hide_empty' => false,
					) );
					$categories = get_terms( array(
						'taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
						'hide_empty' => false,
					) );
					if ( is_array ( $modal_data['img_categories'] ) && !empty ( $modal_data['img_categories'] ) ) {
						foreach ( $modal_data['img_categories'] as $cat ) {
							$selected_categories[] = $cat->term_id;
						}
					}
					if ( is_array ( $modal_data['img_tags'] ) && !empty ( $modal_data['img_tags'] ) ) {
						foreach ( $modal_data['img_tags'] as $tag ) {
							$selected_tags[] = $tag->term_id;
						}
					}
					?>
					<section id="foogallery-panel-taxonomies" class="tab-panel">
						<div class="settings">
							<span class="setting">
								<label for="foogallery_woocommerce_tags" class="name"><?php _e('Media Tags:', 'foogallery'); ?></label>
								<ul class="foogallery_woocommerce_tags">
									<?php
									foreach ($tags as $tag) {
										$tag_selected = in_array($tag->term_id, $selected_tags);
										?>
										<li>
											<a href="javascript:void(0);" class="button button-small<?php echo $tag_selected ? ' button-primary' : ''; ?>"
												data-term-id="<?php echo $tag->term_id; ?>"><?php echo $tag->name; ?></a>
										</li><?php
									}
									?>
								</ul>
								<input type="hidden" id="foogallery_woocommerce_tags_selected"  name="foogallery[tags]" value="<?php echo implode( ',', $selected_tags ); ?>">
							</span>
							<span class="setting">
								<label for="foogallery_woocommerce_categories" class="name"><?php _e('Media Categories:', 'foogallery'); ?></label>
								<ul class="foogallery_woocommerce_categories">
									<?php
									foreach ($categories as $category) {
										$cat_selected = in_array($category->term_id, $selected_categories);
										?>
										<li>
											<a href="javascript:void(0);" class="button button-small<?php echo $cat_selected ? ' button-primary' : ''; ?>"
												data-term-id="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></a>
										</li><?php
									}
									?>
								</ul>
								<input type="hidden" id="foogallery_woocommerce_taxonomies_selected"  name="foogallery[taxonomies]" value="<?php echo implode( ',', $selected_categories ); ?>">
							</span>
						</div>
					</section>
					<?php
				}
			}
		}

		/**
		 * Image modal thumbnails tab content
		 */
		public function display_tab_content_thumbnails( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) {
					$engine = foogallery_thumb_active_engine();
					?>
					<section id="foogallery-panel-thumbnails" class="tab-panel">
						<div class="settings">
							<span class="setting" data-setting="crop-from-position">
								<label for="attachments-crop-from-position" class="name"><?php _e('Crop Position', 'foogallery'); ?></label>
								<div id="foogallery_crop_pos">
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="left,top" title="<?php _e('Left, Top', 'foogallery'); ?>" <?php checked( 'left,top', $modal_data['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="center,top" title="<?php _e('Center, Top', 'foogallery'); ?>" <?php checked( 'center,top', $modal_data['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="right,top" title="<?php _e('Right, Top', 'foogallery'); ?>" <?php checked( 'right,top', $modal_data['foogallery_crop_pos_val'], true); ?>><br>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="left,center" title="<?php _e('Left, Center', 'foogallery'); ?>" <?php checked( 'left,center', $modal_data['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="center,center" title="<?php _e('Center, Center', 'foogallery'); ?>" <?php checked( 'center,center', $modal_data['foogallery_crop_pos'], true); ?>>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="right,center" title="<?php _e('Right, Center', 'foogallery'); ?>" <?php checked( 'right,center', $modal_data['foogallery_crop_pos_val'], true); ?>><br>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="left,bottom" title="<?php _e('Left, Bottom', 'foogallery'); ?>" <?php checked( 'left,bottom', $modal_data['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="center,bottom" title="<?php _e('Center, Bottom', 'foogallery'); ?>" <?php checked( 'center,bottom', $modal_data['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $modal_data['foogallery_crop_pos'];?>" value="right,bottom" title="<?php _e('Right, Bottom', 'foogallery'); ?>" <?php checked( 'right,bottom', $modal_data['foogallery_crop_pos_val'], true); ?>>
								</div>
							</span>
						
						<div class="foogallery-attachments-list-bar">
							<div class="settings">								
								<span class="setting override-thumbnail <?php echo $modal_data['override_class']; ?>" data-setting="override-thumbnail">
									<label for="attachment-details-two-column-override-thumbnail" class="name"><?php _e('Alternate Thumbnail URL', 'foogallery'); ?></label>
									<input type="text" id="attachments-foogallery-override-thumbnail" value="<?php echo $modal_data['alternate_img_src']; ?>" readonly>
									<input type="hidden" name="foogallery[override-thumbnail-id]" id="attachments-foogallery-override-thumbnail-id" value="<?php echo $modal_data['foogallery_override_thumbnail']; ?>">
								</span>
								<span class="setting override-thumbnail-preview <?php echo $modal_data['override_class']; ?>" data-setting="override-thumbnail-preview">
										<label for="attachment-details-two-column-override-thumbnail-preview" class="name"><?php _e('Alternate Thumbnail Preview', 'foogallery'); ?></label>
										<img id="attachment-details-two-column-override-thumbnail-preview" src="<?php echo $modal_data['alternate_img_src']; ?>" alt="Alternate Thumbnail">
								</span>
								<span class="setting alternate-image-upload-settings" data-setting="alternate-image-upload">
									<div class="alternate-image-upload-wrap">
										<button type="button" class="button button-primary button-large" id="foogallery-img-modal-alternate-image-upload"
												data-uploader-title="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
												data-uploader-button-text="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
												data-img-id="<?php echo $modal_data['img_id']; ?>">
											<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>
										</button>
										<button type="button" class="button button-primary button-large <?php echo $modal_data['override_class']; ?>" id="foogallery-img-modal-alternate-image-delete"
												data-uploader-title="<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>"
												data-uploader-button-text="<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>"
												data-img-id="<?php echo $modal_data['img_id']; ?>">
											<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>
										</button>
										<span id="foogallery_clear_alternate_img_spinner" class="spinner"></span>
									</div>
								</span>
							</div>
						</div>
						<?php if ( $engine->has_local_cache() ) { ?>
						<div class="foogallery-attachments-list-bar clear-thumbnail">
							<span class="setting" data-setting="clear-image-cache">
								<label class="name"></label>
								<button class="button button-primary button-large" id="foogallery_clear_img_thumb_cache"><?php _e( 'Clear Thumbnail Cache', 'foogallery' ); ?></button>
								<span id="foogallery_clear_img_thumb_cache_spinner" class="spinner"></span>
								<?php wp_nonce_field( 'foogallery_clear_attachment_thumb_cache', 'foogallery_clear_attachment_thumb_cache_nonce', false ); ?>
							</span>
						</div>
						<?php } ?>
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		/**
		 * Image modal watermark tab content
		 */
		public function display_tab_content_watermark( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 && isset( $modal_data['attachment_watermark'] ) && is_array( $modal_data['attachment_watermark'] ) ) { ?>
					<section id="foogallery-panel-watermark" class="tab-panel">
						<div id="foogallery-panel-watermark-preview" class="settings <?php echo isset( $modal_data['attachment_watermark']['url'] ) ? 'watermark-preview-show' : ''; ?>">
							<span class="setting" data-setting="watermark-image-preview">
								<label for="attachments-watermark-image-preview" class="name"><?php _e('Watermark Image Preview', 'foogallery'); ?></label>
								<a id="attachments-watermark-image-preview" href="<?php echo $modal_data['attachment_watermark']['url']; ?>" target="_blank">
									<img width="150" src="<?php echo $modal_data['attachment_watermark']['url']; ?>" alt="watermark">
								</a>
							</span>
						</div>
						<div class="foogallery_metabox_field-watermark_status settings">
							<span class="setting" data-setting="watermark-generate-button">
								<label for="attachments-watermark-generate-btn" class="name"><?php _e('Click on the button to generate watermarked image', 'foogallery'); ?></label>
								<button id="attachments-watermark-generate-btn" type="button" class="button button-primary button-large attachment_protection_generate" data-attach_id="<?php echo $modal_data['img_id']; ?>">
								<?php echo esc_html( __( 'Generate Watermarked Image', 'foogallery' ) ); ?>
								</button>
								<span style="position: absolute" class="spinner foogallery_protection_generate_spinner"></span>
								<span style="padding-left: 40px; line-height: 25px; float:none;" class="foogallery_protection_generate_progress"></span>
							</span>
						</div>
					</section>
					<?php
				}
			}
		}

		/**
		 * Image modal EXIF tab content
		 */
		public function display_tab_content_exif( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) {
					if ( is_array ( $modal_data['meta'] ) && !empty ( $modal_data['meta'] ) ) {
						$keywords = '';
						$image_meta = array_key_exists( 'image_meta', $modal_data['meta'] ) ? $modal_data['meta']['image_meta'] : '';
						if ( is_array( $image_meta ) && !empty ( $image_meta ) ) {				
							$keywords_str = array_key_exists( 'keywords', $image_meta ) ? implode( ',', $modal_data['meta']['image_meta']['keywords'] ) : '';
							$keywords = rtrim( $keywords_str, ',' );
						} ?>
						<section id="foogallery-panel-exif" class="tab-panel">							
							<div class="settings">	
								<span class="setting" data-setting="title">
									<label for="attachment-details-two-column-aperture" class="name"><?php _e('Aperture Text', 'foogallery'); ?></label>
									<input type="text" name="foogallery[aperture]" id="attachment-details-two-column-aperture" value="<?php echo $modal_data['meta']['image_meta']['aperture'];?>">
								</span>		
								<span class="setting" data-setting="camera">
									<label for="attachment-details-two-column-camera" class="name"><?php _e('Camera Text', 'foogallery'); ?></label>
									<input type="text" name="foogallery[camera]" id="attachment-details-two-column-camera" value="<?php echo $modal_data['meta']['image_meta']['camera'];?>">
								</span>	
								<span class="setting" data-setting="created-timestamp">
									<label for="attachment-details-two-column-created-timestamp" class="name"><?php _e('Created Timestamp', 'foogallery'); ?></label>
									<input type="text" name="foogallery[created-timestamp]" id="attachment-details-two-column-created-timestamp" value="<?php echo $modal_data['meta']['image_meta']['created_timestamp'];?>">
								</span>	
								<span class="setting" data-setting="shutter-speed">
									<label for="attachment-details-two-column-shutter-speed" class="name"><?php _e('Shutter Speed Text', 'foogallery'); ?></label>
									<input type="text" name="foogallery[shutter-speed]" id="attachment-details-two-column-shutter-speed" value="<?php echo $modal_data['meta']['image_meta']['shutter_speed'];?>">
								</span>			
								<span class="setting" data-setting="focal-length">
									<label for="attachment-details-two-column-focal-length" class="name"><?php _e('Focal Length Text', 'foogallery'); ?></label>
									<input type="text" name="foogallery[focal-length]" id="attachment-details-two-column-focal-length" value="<?php echo $modal_data['meta']['image_meta']['focal_length'];?>">
								</span>		
								<span class="setting" data-setting="iso">
									<label for="attachment-details-two-column-iso" class="name"><?php _e('ISO Text', 'foogallery'); ?></label>
									<input type="text" name="foogallery[iso]" id="attachment-details-two-column-iso" value="<?php echo $modal_data['meta']['image_meta']['iso'];?>">
								</span>	
								<span class="setting" data-setting="orientation">
									<label for="attachment-details-two-column-orientation" class="name"><?php _e('Orientation', 'foogallery'); ?></label>
									<input type="text" name="foogallery[orientation]" id="attachment-details-two-column-orientation" value="<?php echo $modal_data['meta']['image_meta']['orientation'];?>">
								</span>	
								<span class="setting" data-setting="keywords">
									<label for="attachment-details-two-column-keywords" class="name"><?php _e('Keywords', 'foogallery'); ?></label>
									<input type="text" name="foogallery[keywords]" id="attachment-details-two-column-keywords" value="<?php echo $keywords;?>">
								</span>	
							</div>
						</section>
						<?php
					}
				}
			}
		}

		/**
		 * Image modal more tab content
		 */
		public function display_tab_content_more( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-more" class="tab-panel">
						<div class="settings">	
							<span class="setting" data-setting="data-width">
								<label for="attachment-details-two-column-data-width" class="name"><?php _e('Override Width', 'foogallery'); ?></label>
								<input type="text" name="foogallery[data-width]" id="attachment-details-two-column-data-width" value="<?php echo $modal_data['data_width']; ?>">
							</span>		
							<span class="setting" data-setting="data-height">
								<label for="attachment-details-two-column-data-height" class="name"><?php _e('Override Height', 'foogallery'); ?></label>
								<input type="text" name="foogallery[data-height]" id="attachment-details-two-column-data-height" value="<?php echo $modal_data['data_height']; ?>">
							</span>	
							<span class="setting" data-setting="panning">
								<label for="attachment-details-two-column-panning" class="name"><?php _e('Panning', 'foogallery'); ?></label>
								<input type="text" name="foogallery[panning]" id="attachment-details-two-column-panning" value="<?php echo $modal_data['panning']; ?>">
							</span>	
							<span class="setting" data-setting="override-type">
								<label for="attachment-details-two-column-override-type" class="name"><?php _e('Override Type', 'foogallery'); ?></label>
								<input type="text" name="foogallery[override-type]" id="attachment-details-two-column-override-type" value="<?php echo $modal_data['override_type']; ?>">
							</span>	
							<span class="setting" data-setting="button-text">
								<label for="attachment-details-two-column-button-text" class="name"><?php _e('Button Text', 'foogallery'); ?></label>
								<input type="text" name="foogallery[button-text]" id="attachment-details-two-column-button-text" value="<?php echo $modal_data['foogallery_button_text']; ?>">
							</span>	
							<span class="setting" data-setting="button-url">
								<label for="attachment-details-two-column-button-url" class="name"><?php _e('Button URL', 'foogallery'); ?></label>
								<input type="text" name="foogallery[button-url]" id="attachment-details-two-column-button-url" value="<?php echo $modal_data['foogallery_button_url']; ?>">
							</span>	
							<span class="setting" data-setting="ribbon">
								<label for="attachment-details-two-column-ribbon" class="name"><?php _e('Ribbon', 'foogallery'); ?></label>
								<select id="attachment-details-two-column-ribbon" name="foogallery[ribbon]">
									<option selected="selected" value=""><?php _e('None', 'foogallery'); ?></option>
									<option value="fg-ribbon-5" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-5', true ); ?>><?php _e('Type 1 (top-right, diagonal, green)', 'foogallery'); ?></option>
									<option value="fg-ribbon-3" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-3', true ); ?>><?php _e('Type 2 (top-left, small, blue)', 'foogallery'); ?></option>
									<option value="fg-ribbon-4" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-4', true ); ?>><?php _e('Type 3 (top, full-width, yellow)', 'foogallery'); ?></option>
									<option value="fg-ribbon-6" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-6', true ); ?>><?php _e('Type 4 (top-right, rounded, pink)', 'foogallery'); ?></option>
									<option value="fg-ribbon-2" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-2', true ); ?>><?php _e('Type 5 (top-left, medium, purple)', 'foogallery'); ?></option>
									<option value="fg-ribbon-1" <?php selected( $modal_data['foogallery_ribbon'], 'fg-ribbon-1', true ); ?>><?php _e('Type 6 (top-left, vertical, orange)', 'foogallery'); ?></option>
								</select>
							</span>	
							<span class="setting" data-setting="ribbon-text">
								<label for="attachment-details-two-column-ribbon-text" class="name"><?php _e('Ribbon Text', 'foogallery'); ?></label>
								<input type="text" name="foogallery[ribbon-text]" id="attachment-details-two-column-ribbon-text" value="<?php echo $modal_data['foogallery_ribbon_text']; ?>">
							</span>
							<span class="setting" data-setting="product-id">
								<label for="attachment-details-two-column-product-id" class="name"><?php _e('Product ID', 'foogallery'); ?></label>
								<input type="text" name="foogallery[product-id]" id="attachment-details-two-column-product-id" value="<?php echo $modal_data['foogallery_product']; ?>">
							</span>	
						</div>
					</section>
					<?php
				}
			}
		}

		/**
		 * Image modal info section
		 */
		public function display_attachment_info( $modal_data ) {
			if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
				if ( $modal_data['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-info">
						<div class="foogallery-panel-info-inner">
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-uploaded-on" class="name"><?php _e('Uploaded On: ', 'foogallery'); ?></label>
								<span><?php echo $modal_data['post_date']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-uploaded-by" class="name"><?php _e('Uploaded By: ', 'foogallery'); ?></label>
								<span><?php echo $modal_data['author_name']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-file-name" class="name"><?php _e('File Name: ', 'foogallery'); ?></label>
								<span id="attachment-details-two-column-copy-file-name"><?php echo $modal_data['file_name']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-file-type" class="name"><?php _e('File Type: ', 'foogallery'); ?></label>
								<span><?php echo $modal_data['file_type']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-file-size" class="name"><?php _e('File Size: ', 'foogallery'); ?></label>
								<span><?php echo $modal_data['file_size']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-dimensions" class="name"><?php _e('Dimensions: ', 'foogallery'); ?></label>
								<span><?php echo $modal_data['media_dims']; ?></span>
							</div>
						</div>
					</section>
					<?php
				}
			}
		}

		/**
		 * Ajax function to remove alternate image
		 */
		public function ajax_alternate_img_remove() {

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery-modal-nonce' ) ) {
				die ( 'Busted!');
			}

			$img_id = sanitize_text_field( $_POST['img_id'] );

			delete_post_meta( $img_id, '_foogallery_override_thumbnail' );

			wp_die();

		}

		/**
		 * Ajax callback for generating watermarked image for single attachment.
		 */
		public function ajax_attachment_generate_watermark() {
			if ( check_admin_referer( 'foogallery_protection_generate' ) ) {
				$progress = array();

				if ( isset( $_POST['attachment_id'] ) ) {
					//$foogallery_id = intval( sanitize_text_field( wp_unslash( $_POST['foogallery'] ) ) );
					$attachment_id = intval( sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) ) );

					// Generate watermark image for given attachment id
					$protection = new FooGallery_Pro_Protection(); 
					$protection->generate_watermark( $attachment_id );

					$progress['message'] = 'Watermark image generated.';
					$progress['title'] = 'Continue Generating';

					ob_start();
					$this->render_attachment_watermark_status_field( $attachment_id );
					$progress['refreshfield'] = true;
					$progress['fieldhtml']    = ob_get_contents();
					ob_end_clean();

					// Set attachment watermark image from meta field
					$this->img_modal['attachment_watermark'] = get_post_meta( $attachment_id, FOOGALLERY_META_WATERMARK, true );
					$progress['image'] = $this->img_modal['attachment_watermark']['url'];
					
					wp_send_json_success( $progress );
				}
			}

			die();
		}

		/**
		 * Append some script for the generation button.
		 *
		 * @param FooGallery $gallery The gallery.
		 */
		public function append_script_for_watermarking( $gallery ) {
			wp_nonce_field( 'foogallery_protection_generate', 'foogallery_nonce_protection_generate', false );
			?>
			<script>
				jQuery( function() {
					jQuery(document).on('click', '.attachment_protection_generate', function(e) {
						e.preventDefault();

						var $this = jQuery( this );
						var attach_id = $this.attr('data-attach_id');

						jQuery('.foogallery_protection_generate_spinner').addClass('is-active');

						var nonce = jQuery('#foogallery_nonce_protection_generate').val(),
							data = 'action=foogallery_attachment_protection_generate' +
						           '&foogallery=<?php echo $gallery->ID; ?>' +
								   '&attachment_id=' + attach_id +
						           '&_wpnonce=' + nonce +
						           '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name="_wp_http_referer"]').val() );

						jQuery.ajax({
							type: "POST",
							url: ajaxurl,
							data: data,
							success: function(result) {
								if ( result.data ) {
									jQuery( '.foogallery_protection_generate_progress' ).html( result.data.message );
									$this.text( result.data.title );
									jQuery('#foogallery-panel-watermark-preview').addClass( 'watermark-preview-show' );
									jQuery('#foogallery-panel-watermark-preview img').attr( 'src', result.data.image );
									jQuery( '.foogallery_protection_generate_spinner' ).removeClass( 'is-active' );

									if ( result.data.refreshfield ) {
										jQuery('.foogallery_metabox_field-watermark_status').html(result.data.fieldhtml);
									}
								}
							},
							error: function() {
								jQuery('#foogallery-panel-watermark-preview').removeClass( 'watermark-preview-show' );
								jQuery( '.foogallery_protection_generate_spinner' ).removeClass( 'is-active' );
								jQuery( '.foogallery_protection_generate_progress' ).html( '<?php echo esc_html( __( 'There was an error! Please try again.', 'foogallery' ) ); ?>' );
							}
						});
					});
				});
			</script>
			<?php
		}

		/**
		 * Get the watermark data for an attachment.
		 *
		 * @param FooGallery $attachment_id The image id we are working with.
		 *
		 * @return array
		 */

		private function build_attachment_watermark_data( $attachment_id ) {
			global $foogallery_watermark_data;

			// We do not want to fetch this info every time for every template, so store it globally to save time.
			if ( ! isset( $foogallery_watermark_data ) ) {
				$protection = new FooGallery_Pro_Protection(); 
				$watermark_options = $protection->get_watermark_options();

				$foogallery_watermark_data = array();

				$outdated_count        = 0;
				$error_count           = 0;

				// Generate a checksum we can use to check if the watermark is outdated.
				$watermark_checksum = crc32( foogallery_json_encode( $watermark_options ) );

				// Check if the attachment has a watermark.
				$attachment_watermark = get_post_meta( $attachment_id, FOOGALLERY_META_WATERMARK, true );

				if ( ! is_array( $attachment_watermark ) ) {
					$attachment_watermark = array(
						'has_watermark' => false,
					);
				}

				if ( isset( $attachment_watermark['has_watermark'] ) && $attachment_watermark['has_watermark'] ) {
					$attachment_watermark['outdated'] = $attachment_watermark['checksum'] !== $watermark_checksum;

					if ( $attachment_watermark['outdated'] ) {
						$outdated_count ++;
					}
				}
				if ( isset( $attachment_watermark['error'] ) ) {
					$error_count++;
				}

				$foogallery_watermark_data[ $attachment_id ] = $attachment_watermark;

				$foogallery_watermark_data['summary'] = array(
					'errors'     => $error_count,
					'outdated'   => $outdated_count,
				);
			}

			return $foogallery_watermark_data;
			
		}

		private function render_attachment_watermark_status_field( $attachment_id ) {
			$watermark_data = $this->build_attachment_watermark_data( $attachment_id );

			echo '<span class="setting" data-setting="watermark-generate-button">';
			echo '<label for="attachments-watermark-generate-btn" class="name">';
			echo esc_html( __('Click on the button to generate watermarked image', 'foogallery') );
			echo '</label><div><span class="foogallery-watermark-response-message">';

			if ( is_array( $watermark_data ) && array_key_exists( 'summary', $watermark_data ) ) {
				$summary_watermark_data = $watermark_data['summary'];
				$outdated_count         = $summary_watermark_data['outdated'];
				$error_count            = $summary_watermark_data['errors'];

				echo esc_html( sprintf( __( 'Watermarked image have been generated.', 'foogallery' ) ) );
				if ( $outdated_count > 0 ) {
					echo ' ' . esc_html( sprintf( __( '%d are outdated and need to be re-generated!', 'foogallery' ), $outdated_count ) );
				}
				if ( $error_count > 0 ) {
					echo '<br /><br />';
					echo esc_html( sprintf( __( '%d had errors and could not be generated!', 'foogallery' ), $error_count ) );
				}
				echo '</span><br /><br />';
				echo '<button id="attachments-watermark-generate-btn" type="button" class="button button-primary button-large attachment_protection_generate" data-attach_id="'.$this->img_modal['img_id'].'">';
				echo esc_html( __( 'Generate Watermarked Images', 'foogallery' ) );
				echo '</button>';
				echo '<span style="position: absolute" class="spinner foogallery_protection_generate_spinner"></span>';
				echo '<span style="padding-left: 40px; line-height: 25px; float: none;" class="foogallery_protection_generate_progress"></span>';
			} else {
				echo esc_html( __( 'Something went wrong!', 'foogallery' ) );
			}

			echo '</div></span>';

		}

		public function foogallery_img_modal_save_btn() {
			echo '<div class="foogallery-image-edit-footer"><button id="attachments-data-save-btn" type="submit" class="button button-primary button-large">'. __( 'Save Attachment Details', 'foogallery' ) .'</button></div>';
		}

	}

}