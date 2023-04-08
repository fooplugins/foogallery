<?php
/*
 * FooGallery Admin Gallery Attachment Modal class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Attachment_Modal' ) ) {

	class FooGallery_Admin_Gallery_Attachment_Modal {

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
            add_action( 'admin_footer', array( $this, 'foogallery_image_editor_modal' ) );

			add_action( 'wp_ajax_foogallery_attachment_modal_open', array( $this, 'ajax_open_modal' ) );
			add_action( 'wp_ajax_foogallery_attachment_modal_save', array( $this, 'ajax_save_modal' ) );

            add_action( 'wp_ajax_foogallery_attachment_modal_taxonomy_add', array( $this, 'ajax_add_taxonomy' ) );

			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_main' ), 10 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_taxonomies' ), 20 );
			add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_thumbnails' ), 30 );
            add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'display_tab_more' ), 200 );

            add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_main' ), 10, 1 );
            add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_taxonomies' ), 20, 1 );
            add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_thumbnails' ), 30, 1 );
            add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'display_tab_content_more' ), 60, 1 );

            add_action( 'foogallery_attachment_modal_before_thumbnail', array( $this, 'display_attachment_info' ), 10, 1 );

			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_main' ), 10, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_taxonomies' ), 20, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_thumbnails' ), 30, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_more' ), 60, 4 );
			add_filter( 'foogallery_attachment_modal_data', array( $this, 'foogallery_attachment_modal_data_info' ), 70, 4 );

			add_action( 'foogallery_attachment_modal_after_tabs', array( $this, 'foogallery_img_modal_save_btn' ) );

			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_main' ), 10, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_taxonomies' ), 20, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_thumbnails' ), 30, 2 );
			add_action( 'foogallery_attachment_save_data', array( $this, 'foogallery_attachment_save_data_more' ), 60, 2 );
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
            global $post;

            // Check if the gallery edit page is being shown.
            $screen = get_current_screen();
            if ( 'foogallery' !== $screen->id ) {
                return;
            }

            if ( !is_a( $post, 'WP_Post' ) ) {
                return;
            }

			$modal_style = foogallery_get_setting( 'advanced_attachment_modal' );

            // Only show the attachment modal if the setting is turned on.
            if ( 'on' !== $modal_style ) {
                return;
            }

			?>
			<div id="foogallery-image-edit-modal" style="display: none;"
                 data-nonce="<?php echo wp_create_nonce( 'foogallery_attachment_modal_open' ); ?>"
                 data-gallery_id="<?php echo $post->ID; ?>"
                 data-modal_style="<?php echo $modal_style; ?>">
				<div class="media-modal wp-core-ui">
					<div class="media-modal-content">
						<div class="edit-attachment-frame mode-select hide-menu hide-router">
							<div class="edit-media-header">

								<button title="<?php _e( 'Edit previous attachment in the gallery', 'foogallery' ); ?>" class="left dashicons"><span class="screen-reader-text"><?php _e( 'Edit previous attachment in the gallery', 'foogallery' ); ?></span></button>
								<button title="<?php _e( 'Edit next attachment in the gallery', 'foogallery' ); ?>" class="right dashicons"><span class="screen-reader-text"><?php _e( 'Edit next attachment in the gallery', 'foogallery' ); ?></span></button>
								<button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php _e('Close dialog', 'foogallery'); ?></span></span></button>
							</div>
							<div class="media-frame-title">
                                <h1><?php _e('Edit Attachment Details', 'foogallery'); ?></h1>
                                <div class="attachment-modal-autosave">
                                    <input id="attachment-modal-autosave" type="checkbox" />
                                    <label for="attachment-modal-autosave"><?php _e( 'Autosave', 'foogallery' ); ?></label>
                                </div>
                            </div>
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
         * Save new taxonomy
         */
        public function ajax_add_taxonomy() {
            // Check for nonce security
            if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery_attachment_modal_taxonomies' ) ) {
                die ( 'Busted!');
            }

            $img_id = intval( sanitize_text_field( $_POST['img_id'] ) );
            $taxonomy = sanitize_text_field( $_POST['taxonomy'] );
            $term = sanitize_text_field( $_POST['term'] );

            if ( $img_id > 0 && strlen( $taxonomy ) > 0 && strlen( $term ) > 0 ) {
                $new_term = wp_insert_term( $term, $taxonomy );
                if ( is_wp_error( $new_term ) ) {
                    wp_send_json_error( array(
                        'message' => __( 'Could not add new taxonomy term!', 'foogallery' )
                    ));
                } else {
                    $new_term_obj = null;

                    if ( isset( $new_term['term_id'] ) ) {
                        $new_term_obj = get_term( $new_term['term_id'] );

                        wp_send_json_success( array(
                            'id' => $new_term_obj->term_id,
                            'name' => $new_term_obj->name
                        ));
                    }
                }
            } else {
                wp_send_json_error( array(
                    'message' => __( 'Invalid data!', 'foogallery' )
                ));
            }
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

                if ( !$this->attachments_have_taxonomies() ) {
                    return;
                }

                if ( array_key_exists( 'taxonomies', $foogallery ) ) {
                    foreach ( $foogallery['taxonomies'] as $taxonomy => $taxonomy_value ) {
                        $terms = explode( ',', $taxonomy_value );
                        if ( is_array( $terms ) ) {
                            $terms = array_map( 'intval', $terms );
                            wp_set_object_terms( $img_id, $terms, $taxonomy, false );
                        }
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
					if ( $key === 'data-width' ) {
						update_post_meta( $img_id, '_data-width', $val );
					}
                    else if ( $key === 'data-height' ) {
						update_post_meta( $img_id, '_data-height', $val );
					}
					else if ( $key === 'panning' ) {
						update_post_meta( $img_id, '_foobox_panning', $val );
					}
                    else if ( $key === 'override_type' ) {
                        update_post_meta( $img_id, '_foogallery_override_type', $val );
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
                    $modal_data['file_url'] = wp_get_attachment_url( $attachment_id );
                    $modal_data['file_name'] = basename( $modal_data['file_url'] );
                    $modal_data['file_type'] = apply_filters( 'foogallery_attachment_modal_info_file_type', $attachment_post->post_mime_type );
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

                if ( !$this->attachments_have_taxonomies() ) {
                    return $modal_data;
                }

                $modal_data['taxonomies'] = array();

                $taxonomies = get_object_taxonomies( 'attachment', 'objects' );

                foreach ( $taxonomies as $tax_name => $taxonomy ) {
                    $modal_data['taxonomies'][$tax_name] = array(
                        'label' => $taxonomy->label,
                        'terms' => get_the_terms( $attachment_id, $tax_name )
                    );
                }
            }

			return $modal_data;
		}

		/**
		 * Image modal thumbnails tab data update
		 */
		public function foogallery_attachment_modal_data_thumbnails( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {

                $modal_data['foogallery_crop_pos'] = get_post_meta( $attachment_id, 'foogallery_crop_pos', true );

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
		 * Image modal more tab data update
		 */
		public function foogallery_attachment_modal_data_more( $modal_data, $data, $attachment_id, $gallery_id ) {
            if ( $attachment_id > 0 ) {
                $modal_data['data_width'] =    get_post_meta( $attachment_id, '_data-width', true );
                $modal_data['data_height'] =   get_post_meta( $attachment_id, '_data-height', true );
                $modal_data['panning'] =       get_post_meta( $attachment_id, '_foobox_panning', true );
                $modal_data['override_type'] = get_post_meta( $attachment_id, '_foogallery_override_type', true );
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
		public function display_tab_taxonomies() {
            if ( !$this->attachments_have_taxonomies() ) {
                return;
            }

        ?>
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
         * Returns true if attachments have any taxonomies registered.
         *
         * @return bool
         */
        function attachments_have_taxonomies() {
            $taxonomies = get_object_taxonomies( 'attachment' );
            return count( $taxonomies ) > 0;
        }

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
							<span class="setting" data-setting="alt">
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
							<span class="setting has-description" data-setting="custom_class">
								<label for="attachments-foogallery-custom-class" class="name"><?php _e('Custom Class', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-custom-class" name="foogallery[custom-class]" value="<?php echo $modal_data['custom_class'];?>">
							</span>
                            <p class="description">
                                <?php _e( 'The custom class will be applied to the anchor tag of the image, which you can target with custom CSS.', 'foogallery' ); ?>
                            </p>
							<span class="setting" data-setting="file_url">
								<label for="attachments-foogallery-file-url" class="name"><?php _e('File URL', 'foogallery'); ?></label>
                                <div class="setting-with-buttons">
                                    <input type="text" id="attachments-foogallery-file-url" value="<?php echo $modal_data['file_url'];?>" readonly />
                                    <button type="button" class="button button-small copy-attachment-file-url" data-clipboard-target="#attachments-foogallery-file-url"><?php _e('Copy to clipboard', 'foogallery'); ?></button>
                                </div>
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
            if ( !$this->attachments_have_taxonomies() ) {
                return;
            }

            if ( is_array( $modal_data ) && array_key_exists( 'taxonomies', $modal_data ) ) {
                if ( $modal_data['img_id'] > 0 ) {
                    $taxonomies = get_object_taxonomies( 'attachment', 'objects' );

                    ?>
                        <section id="foogallery-panel-taxonomies" class="tab-panel" data-nonce="<?php echo wp_create_nonce('foogallery_attachment_modal_taxonomies'); ?>">
						<div class="settings">
                    <?php

                    foreach ( $taxonomies as $tax_name => $taxonomy ) {
                        $terms = get_terms( array(
                            'taxonomy' => $tax_name,
                            'hide_empty' => false,
                        ) );

                        $selected_terms = array();

                        if ( array_key_exists( $tax_name, $modal_data['taxonomies'] ) ) {
                            foreach ( $modal_data['taxonomies'][$tax_name]['terms'] as $term ) {
                                $selected_terms[] = $term->term_id;
                            }
                        }

                        ?>

                        <span class="setting">
                            <label for="foogallery_attachment_taxonomy_<?php echo $tax_name; ?>" class="name"><?php echo $modal_data['taxonomies'][$tax_name]['label']; ?></label>
                            <div>
                                <ul data-taxonomy="<?php echo $tax_name; ?>">
                                    <?php
                                    foreach ($terms as $term) {
                                        $term_selected = in_array( $term->term_id, $selected_terms );
                                        ?>
                                        <li>
                                        <a href="javascript:void(0);" class="button button-small<?php echo $term_selected ? ' button-primary' : ''; ?>"
                                           data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
                                        </li><?php
                                    }
                                    ?>
                                    <li class="taxonomy_add">
                                        <a href="javascript:void(0);" class="button button-small active foogallery_attachment_taxonomy_add" data-action="add">+</a>
                                        <input type="text" class="foogallery_attachment_taxonomy_add" style="display: none" />
                                        <a href="javascript:void(0);" class="button button-small active foogallery_attachment_taxonomy_add" style="display: none" data-action="save"><?php echo __( 'Save','foogallery' ); ?></a>
                                    </li>
                                </ul>
                                <div>
                                    <a target="_blank" href="<?php echo admin_url( 'edit-tags.php?taxonomy=' . $tax_name ); ?>"?><?php printf( __('Manage %s', 'foogallery' ), $taxonomy->labels->name ); ?></a>
                                </div>
                            </div>
                            <input type="hidden" id="foogallery_attachment_taxonomy_<?php echo $tax_name; ?>_selected" name="foogallery[taxonomies][<?php echo $tax_name; ?>]" value="<?php echo implode( ',', $selected_terms ); ?>">

                        </span>

                        <?php
                    }

                    ?>
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

                    $crop_pos = !empty( $modal_data['foogallery_crop_pos'] ) ? $modal_data['foogallery_crop_pos'] : 'center,center';
					?>
					<section id="foogallery-panel-thumbnails" class="tab-panel">
						<div class="settings">
							<span class="setting" data-setting="crop-from-position">
								<label for="attachments-crop-from-position" class="name"><?php _e('Crop Position', 'foogallery'); ?></label>
								<div id="foogallery_crop_pos">
									<input type="radio" name="foogallery[crop_pos]" value="left,top" title="<?php _e('Left, Top', 'foogallery'); ?>" <?php checked( 'left,top', $crop_pos, true); ?>>
									<input type="radio" name="foogallery[crop_pos]" value="center,top" title="<?php _e('Center, Top', 'foogallery'); ?>" <?php checked( 'center,top', $crop_pos, true); ?>>
									<input type="radio" name="foogallery[crop_pos]" value="right,top" title="<?php _e('Right, Top', 'foogallery'); ?>" <?php checked( 'right,top', $crop_pos, true); ?>><br>
									<input type="radio" name="foogallery[crop_pos]" value="left,center" title="<?php _e('Left, Center', 'foogallery'); ?>" <?php checked( 'left,center', $crop_pos, true); ?>>
									<input type="radio" name="foogallery[crop_pos]" value="center,center" title="<?php _e('Center, Center', 'foogallery'); ?>" <?php checked( 'center,center', $crop_pos, true); ?>>
									<input type="radio" name="foogallery[crop_pos]" value="right,center" title="<?php _e('Right, Center', 'foogallery'); ?>" <?php checked( 'right,center', $crop_pos, true); ?>><br>
									<input type="radio" name="foogallery[crop_pos]" value="left,bottom" title="<?php _e('Left, Bottom', 'foogallery'); ?>" <?php checked( 'left,bottom', $crop_pos, true); ?>>
									<input type="radio" name="foogallery[crop_pos]" value="center,bottom" title="<?php _e('Center, Bottom', 'foogallery'); ?>" <?php checked( 'center,bottom', $crop_pos, true); ?>>
									<input type="radio" name="foogallery[crop_pos]" value="right,bottom" title="<?php _e('Right, Bottom', 'foogallery'); ?>" <?php checked( 'right,bottom', $crop_pos, true); ?>>
								</div>
							</span>

						<?php if ( $engine->has_local_cache() ) { ?>
                            <div class="foogallery-attachments-list-bar clear-thumbnail">
                                <span class="setting" data-setting="clear-image-cache">
                                    <label class="name"></label>
                                    <button class="button button-primary button-large" style="width: 180px"
                                            id="foogallery_clear_img_thumb_cache"><?php _e( 'Clear Thumbnail Cache', 'foogallery' ); ?></button>
                                    <span id="foogallery_clear_img_thumb_cache_spinner" class="spinner"></span>
                                    <?php wp_nonce_field( 'foogallery_clear_attachment_thumb_cache', 'foogallery_clear_attachment_thumb_cache_nonce', false ); ?>
                                </span>
                            </div>
						<?php }
                        do_action( 'foogallery_attachment_modal_tab_content_thumbnails', $modal_data );
                        ?></div>
					</section>
					<?php
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
								<input type="text" name="foogallery[override_type]" id="attachment-details-two-column-override-type" value="<?php echo $modal_data['override_type']; ?>">
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

		public function foogallery_img_modal_save_btn() {
			?>
            <div class="foogallery-image-edit-footer">
                <button id="attachments-data-save-btn" type="submit"
                        class="button button-primary button-large"><?php _e( 'Save Attachment Details', 'foogallery' ); ?>
                </button>
                <span class="spinner"></span>
            </div>
            <?php
		}
	}
}