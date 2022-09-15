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
			'foogallery_crop_pos' => 'foogallery_crop_pos',
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
			add_action( 'wp_ajax_open_foogallery_image_edit_modal', array( $this, 'ajax_open_modal' ) );
			add_action( 'admin_footer', array( $this, 'foogallery_image_editor_modal' ) );
			add_filter( 'foogallery_attachment_custom_fields', array( $this, 'foogallery_add_override_thumbnail_field' ) );
			add_action( 'wp_ajax_foogallery_save_modal_metadata', array( $this, 'ajax_save_modal' ) );
			add_action( 'foogallery_img_modal_before_tab_container', array( $this, 'foogallery_img_modal_edit_section' ) );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_main' ), 10 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_taxonomies' ), 20 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_thumbnails' ), 30 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_watermark' ), 40 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_exif' ), 50 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_more' ), 60 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_main' ), 10, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_taxonomies' ), 20, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_thumbnails' ), 30, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_watermark' ), 40, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_exif' ), 50, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_more' ), 60, 1 );
			add_action( 'foogallery_img_modal_before_thumbnail', array( $this, 'foogallery_img_modal_info' ) );
			add_action( 'wp_ajax_foogallery_remove_alternate_img', array( $this, 'ajax_alternate_img_remove' ) );
		}

		/**
		 * Generate image edit modal on gallery creation
		 */ 
		public function ajax_open_modal() {

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery-modal-nonce' ) ) {
				die ( 'Busted!');
			}

			$img_modal = $this->set_image_modal_vars( $_POST );
			ob_start() ?>

			<div class="foogallery-image-edit-main" data-img_id="<?php echo $img_modal['img_id']; ?>" data-gallery_id="<?php echo $img_modal['gallery_id']; ?>">
				<?php do_action( 'foogallery_img_modal_before_tab_container' ); ?>
			</div>
					
			<?php do_action( 'foogallery_img_modal_before_tabs' ); ?>

			<div class="foogallery-image-edit-meta">
				<div class="tabset">
					<?php do_action( 'foogallery_img_modal_tabs_view' ); ?>
				</div>
				<div class="tab-panels">
					<?php do_action( 'foogallery_img_modal_tab_content', $this->img_modal ); ?>
				</div>
			</div>

			<?php do_action( 'foogallery_img_modal_after_tab_container' ); ?>	
				
			<?php echo wp_send_json( array( 'html' => ob_get_clean(), 'slide_num' => $this->img_modal['slide_num'], 'prev_slide' => $this->img_modal['prev_slide'], 'next_slide' => $this->img_modal['next_slide'], 'next_img_id' => $this->img_modal['next_img_id'], 'prev_img_id' => $this->img_modal['prev_img_id'], 'override_thumbnail' => $this->img_modal['foogallery_override_thumbnail'] ) );
		}

		/**
		 * 	Admin modal wrapper for gallery image edit 
		 */ 
		public function foogallery_image_editor_modal() {
			$modal_style = foogallery_get_setting( 'hide_admin_gallery_attachment_modal' );
			?>
			<div id="foogallery-image-edit-modal" style="display: none;" data-img_type="normal" data-gallery_id="<?php echo $_GET['post']; ?>" data-nonce="<?php echo wp_create_nonce('foogallery-modal-nonce');?>" data-modal_style="<?php echo $modal_style; ?>">
				<div class="media-modal wp-core-ui">
					<div class="media-modal-content">
						<div class="edit-attachment-frame mode-select hide-menu hide-router">
							<div class="edit-media-header">
								<button class="left dashicons" <?php echo $this->img_modal['prev_slide']; ?> data-key="<?php echo $this->img_modal['slide_num']; ?>" data-next="<?php echo $this->img_modal['next_img_id']; ?>" data-prev="<?php echo $this->img_modal['prev_img_id']; ?>"><span class="screen-reader-text"><?php _e( 'Edit previous media item', 'foogallery' ); ?></span></button>
								<button class="right dashicons" <?php echo $this->img_modal['next_slide']; ?> data-key="<?php echo $this->img_modal['slide_num']; ?>" data-next="<?php echo $this->img_modal['next_img_id']; ?>" data-prev="<?php echo $this->img_modal['prev_img_id']; ?>"><span class="screen-reader-text"><?php _e( 'Edit next media item', 'foogallery' ); ?></span></button>
								<button type="button" class="media-modal-close" onclick="close_foogallery_img_modal();"><span class="media-modal-icon"><span class="screen-reader-text"><?php _e('Close dialog', 'foogallery'); ?></span></span></button>
							</div>
							<div class="media-frame-title"><h1><?php _e('Foogallery Attachment Details', 'foogallery'); ?></h1></div>
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

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery-modal-nonce' ) ) {
				die ( 'Busted!');
			}

			$img_id = sanitize_text_field( $_POST['id'] );
			$meta = $_POST['meta'];
			$is_post_data = 'no';
			$is_img_meta = 'no';
			$meta_key = '';
			$foogallery_post = array(
				'ID' => $img_id,
			);
			$image_meta = wp_get_attachment_metadata( $img_id );

			if ( is_array( $meta ) && ( array_key_exists( 'input_id', $meta ) || array_key_exists( 'tags', $meta ) || array_key_exists( 'categories', $meta ) ) ) {
				if ( $meta['input_id'] == 'attachment-details-two-column-title' ) {
					$is_post_data = 'yes';
					$foogallery_post['post_title'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-caption' ) {
					$is_post_data = 'yes';
					$foogallery_post['post_excerpt'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-description' ) {
					$is_post_data = 'yes';
					$foogallery_post['post_content'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-alt-text' ) {
					$meta_key = '_wp_attachment_image_alt';
				}
				if ( $meta['input_id'] == 'attachments-foogallery-custom-url' ) {
					$meta_key = '_foogallery_custom_url';
				}
				if ( $meta['input_id'] == 'attachments-foogallery-custom-target' ) {
					$meta_key = '_foogallery_custom_target';
				}
				if ( $meta['input_id'] == 'attachments-foogallery-custom-class' ) {
					$meta_key = '_foogallery_custom_class';
				}
				if ( $meta['input_id'] == 'attachments-foogallery-override-thumbnail-id' ) {
					$meta_key = 'foogallery_override_thumbnail';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-data-width' ) {
					$meta_key = '_data-width';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-data-height' ) {
					$meta_key = '_data-height';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-override-type' ) {
					$meta_key = '_foogallery_override_type';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-button-text' ) {
					$meta_key = '_foogallery_button_text';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-button-url' ) {
					$meta_key = '_foogallery_button_url';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-ribbon' ) {
					$meta_key = '_foogallery_ribbon';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-ribbon-text' ) {
					$meta_key = '_foogallery_ribbon_text';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-product-id' ) {
					$meta_key = '_foogallery_product';
				}
				if ( $meta['input_id'] == 'foogallery_crop_pos' ) {
					$meta_key = 'foogallery_crop_pos';
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-aperture' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['aperture'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-camera' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['camera'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-created-timestamp' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['created_timestamp'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-shutter-speed' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['shutter_speed'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-focal-length' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['focal_length'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-iso' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['iso'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-orientation' ) {
					$is_img_meta = 'yes';
					$image_meta['image_meta']['orientation'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-keywords' ) {
					$is_img_meta = 'yes';
					$keywords = explode(',', $meta['input_val']);
					$image_meta['image_meta']['keywords'] = $keywords;
				}
				if ( $is_post_data == 'yes' ) {
					// Update the post into the database
					wp_update_post( $foogallery_post );
				} elseif ( $meta_key ) {
					update_post_meta( $img_id, $meta_key, $meta['input_val'] );
				} elseif( $is_img_meta == 'yes' ) {
					wp_update_attachment_metadata( $img_id, $image_meta );
				} elseif ( array_key_exists( 'tags', $meta ) ) {
					$tags = array();
					foreach ( $meta['tags'] as $tag ) {
						$tags[] = (int) $tag;
					}
					wp_set_object_terms( $img_id, $tags, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, false );
				} elseif ( array_key_exists( 'categories', $meta ) ) {
					$categories = array();
					foreach ( $meta['categories'] as $category ) {
						$categories[] = (int) $category;
					}
					wp_set_object_terms( $img_id, $categories, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, false );
				}
			}
			wp_die();
		}

		/**
		 * Set values for private variable $img_modal using ajax after modal open
		 * 
		 * @param $args array
		 * 
		 * @return array
		 */
		private function set_image_modal_vars( $args = array() ) {
			$this->img_modal['img_id'] = (int) sanitize_text_field( $args['img_id'] );
			$this->img_modal['gallery_id'] = (int) sanitize_text_field( $args['gallery_id'] );
			$this->img_modal['img_post'] = get_post( $this->img_modal['img_id'] );
			$this->img_modal['image_attributes'] = wp_get_attachment_image_src( $this->img_modal['img_id'], $this->img_modal['image_size'] );
			$this->img_modal['img_title'] = $this->img_modal['img_post']->post_title;;
			$this->img_modal['caption'] = $this->img_modal['img_post']->post_excerpt;
			$this->img_modal['description'] = $this->img_modal['img_post']->post_content;
			$this->img_modal['post_date'] = date( 'F d, Y', strtotime( $this->img_modal['img_post']->post_date ) );
			$this->img_modal['file_type'] = $this->img_modal['img_post']->post_mime_type;
			$this->img_modal['author_id'] = (int) $this->img_modal['img_post']->post_author;
			$this->img_modal['author_name'] = get_the_author_meta( 'display_name', $this->img_modal['author_id'] );
			$this->img_modal['file_url'] = get_the_guid( $this->img_modal['img_id'] );
			$this->img_modal['image_alt'] = get_post_meta( $this->img_modal['img_id'], '_wp_attachment_image_alt', true );
			$this->img_modal['custom_url'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_custom_url', true );
			$this->img_modal['custom_target'] = ( get_post_meta( $this->img_modal['img_id'], '_foogallery_custom_target', true ) ? get_post_meta( $this->img_modal['img_id'], '_foogallery_custom_target', true ) : 'default' );
			$this->img_modal['attachment_watermark'] = get_post_meta( $this->img_modal['img_id'], FOOGALLERY_META_WATERMARK, true );
			$this->img_modal['custom_class'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_custom_class', true );
			$this->img_modal['override_type'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_override_type', true );
			$this->img_modal['data_width'] = get_post_meta( $this->img_modal['img_id'], '_data-width', true );
			$this->img_modal['data_height'] = get_post_meta( $this->img_modal['img_id'], '_data-height', true );
			$this->img_modal['foogallery_crop_pos_val'] = get_post_meta( $this->img_modal['img_id'], 'foogallery_crop_pos', true );
			$this->img_modal['foogallery_ribbon'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_ribbon', true );
			$this->img_modal['foogallery_ribbon_text'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_ribbon_text', true );
			$this->img_modal['foogallery_button_text'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_button_text', true );
			$this->img_modal['foogallery_button_url'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_button_url', true );
			$this->img_modal['foogallery_product'] = get_post_meta( $this->img_modal['img_id'], '_foogallery_product', true );
			$this->img_modal['progress'] = get_post_meta( $this->img_modal['gallery_id'], FOOGALLERY_META_WATERMARK_PROGRESS, true );
			$this->img_modal['foogallery_override_thumbnail'] = get_post_meta( $this->img_modal['img_id'], 'foogallery_override_thumbnail', true );
			$this->img_modal['foogallery_attachments'] = get_post_meta( $this->img_modal['gallery_id'], 'foogallery_attachments', true );
			$this->img_modal['img_categories'] = get_the_terms( $this->img_modal['img_id'], FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
			$this->img_modal['img_tags'] = get_the_terms( $this->img_modal['img_id'], FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
			$this->img_modal['nonce'] = wp_create_nonce('foogallery-modal-nonce');
			$this->img_modal['meta'] = wp_get_attachment_metadata( $this->img_modal['img_id'] );
			$full_img_path = wp_get_attachment_image_src( $this->img_modal['img_id'], 'full' );
			$this->img_modal['img_path'] = $full_img_path[0];

			if ( is_array( $this->img_modal['foogallery_attachments'] ) && !empty ( $this->img_modal['foogallery_attachments'] ) ) {
				foreach ( $this->img_modal['foogallery_attachments'] as $gal_img_key => $gal_img_id ) {
					if ( $this->img_modal['img_id'] == $gal_img_id ) {
						$this->img_modal['slide_num'] = $gal_img_key;
						break;
					}
				}								
				if ( $this->img_modal['slide_num'] >= 0 ) {
					if ( $this->img_modal['slide_num'] === 0 ) {
						$this->img_modal['prev_slide'] = TRUE; // TRUE means disabled prev slide
						$this->img_modal['next_slide'] = FALSE; // FALSE means enabled next slide
					} elseif( $this->img_modal['slide_num'] === ( count ( $this->img_modal['foogallery_attachments'] ) - 1 ) ) {
						$this->img_modal['prev_slide'] = FALSE; // FALSE means enabled prev slide
						$this->img_modal['next_slide'] = TRUE; // TRUE means disabled next slide
					} else {
						$this->img_modal['prev_slide'] = FALSE; // FALSE means enabled prev slide
						$this->img_modal['next_slide'] = FALSE; // FALSE means enabled next slide
					}	
					$prev_slide_num = $this->img_modal['slide_num'] - 1;
					$next_slide_num = $this->img_modal['slide_num'] + 1;
					if ( isset ( $this->img_modal['foogallery_attachments'][ $prev_slide_num ] ) ) {
						$this->img_modal['prev_img_id'] = $this->img_modal['foogallery_attachments'][ $prev_slide_num ];
					}
					if ( isset ( $this->img_modal['foogallery_attachments'][ $next_slide_num ] ) ) {
						$this->img_modal['next_img_id'] = $this->img_modal['foogallery_attachments'][ $next_slide_num ];
					}
				}
			}

			if ( $this->img_modal['foogallery_override_thumbnail'] ) {
				$alternate_thumb_img = wp_get_attachment_image_src( $this->img_modal['foogallery_override_thumbnail'] );
				$this->img_modal['override_class'] = 'is-override-thumbnail';
				$this->img_modal['alternate_img_src'] = $alternate_thumb_img[0];
			}
			return $this->img_modal;
		}

		/**
		 * Image modal main tab title
		 */
		public function foogallery_img_modal_tab_main() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-main">
				<input type="radio" name="tabset" id="foogallery-tab-main" aria-controls="foogallery-panel-main" checked>
				<label for="foogallery-tab-main"><?php _e('Main', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal taxonomies & tags title
		 */
		public function foogallery_img_modal_tab_taxonomies() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-taxonomies">
				<input type="radio" name="tabset" id="foogallery-tab-taxonomies" aria-controls="foogallery-panel-taxonomies">
				<label for="foogallery-tab-taxonomies"><?php _e('Taxonomies', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal thumbnails tab title
		 */
		public function foogallery_img_modal_tab_thumbnails() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-thumbnails">
				<input type="radio" name="tabset" id="foogallery-tab-thumbnails" aria-controls="foogallery-panel-thumbnails">
				<label for="foogallery-tab-thumbnails"><?php _e('Thumbnails', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal watermark tab title
		 */
		public function foogallery_img_modal_tab_watermark() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-watermark">
				<input type="radio" name="tabset" id="foogallery-tab-watermark" aria-controls="foogallery-panel-watermark">
				<label for="foogallery-tab-watermark"><?php _e('Watermark', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal EXIF tab title
		 */
		public function foogallery_img_modal_tab_exif() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-exif">
				<input type="radio" name="tabset" id="foogallery-tab-exif" aria-controls="foogallery-panel-exif">
				<label for="foogallery-tab-exif"><?php _e('EXIF', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal more tab title
		 */
		public function foogallery_img_modal_tab_more() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-more">
				<input type="radio" name="tabset" id="foogallery-tab-more" aria-controls="foogallery-panel-more">
				<label for="foogallery-tab-more"><?php _e('More', 'foogallery'); ?></label>
			</div>
		<?php }

		/**
		 * Image modal main tab content
		 */
		public function foogallery_img_modal_tab_content_main( $args = array() ) {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-main" class="tab-panel active" data-nonce="<?php echo $this->img_modal['nonce'];?>">
						<div class="settings">								
							<span class="setting" data-setting="title">
								<label for="attachment-details-two-column-title" class="name"><?php _e('Title', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-title" value="<?php echo $this->img_modal['img_title'];?>">
							</span>								
							<span class="setting" data-setting="caption">
								<label for="attachment-details-two-column-caption" class="name"><?php _e('Caption', 'foogallery'); ?></label>
								<textarea id="attachment-details-two-column-caption"><?php echo $this->img_modal['caption'];?></textarea>
							</span>
							<span class="setting" data-setting="description">
								<label for="attachment-details-two-column-description" class="name"><?php _e('Description', 'foogallery'); ?></label>
								<textarea id="attachment-details-two-column-description"><?php echo $this->img_modal['description'];?></textarea>
							</span>
							<span class="setting has-description" data-setting="alt">
								<label for="attachment-details-two-column-alt-text" class="name"><?php _e('ALT Text', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-alt-text" value="<?php echo $this->img_modal['image_alt'];?>" aria-describedby="alt-text-description">
							</span>
							<span class="setting" data-setting="custom_url">
								<label for="attachments-foogallery-custom-url" class="name"><?php _e('Custom URL', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-custom-url" value="<?php echo $this->img_modal['custom_url'];?>">
							</span>
							<span class="setting" data-setting="custom_target">
								<label for="attachments-foogallery-custom-target" class="name"><?php _e('Custom Target', 'foogallery'); ?></label>
								<select name="attachments-foogallery-custom-target" id="attachments-foogallery-custom-target">
									<option value="default" <?php selected( 'default', $this->img_modal['custom_target'], true ); ?>><?php _e('Default', 'foogallery'); ?></option>
									<option value="_blank" <?php selected( '_blank', $this->img_modal['custom_target'], true ); ?>><?php _e('New tab (_blank)', 'foogallery'); ?></option>
									<option value="_self" <?php selected( '_self', $this->img_modal['custom_target'], true ); ?>><?php _e('Same tab (_self)', 'foogallery'); ?></option>
									<option value="foobox" <?php selected( 'foobox', $this->img_modal['custom_target'], true ); ?>><?php _e('FooBox', 'foogallery'); ?></option>
								</select>
							</span>
							<span class="setting" data-setting="custom_class">
								<label for="attachments-foogallery-custom-class" class="name"><?php _e('Custom Class', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-custom-class" value="<?php echo $this->img_modal['custom_class'];?>">
							</span>	
							<span class="setting" data-setting="file_url">
								<label for="attachments-foogallery-file-url" class="name"><?php _e('File URL', 'foogallery'); ?></label>
								<input type="text" id="attachments-foogallery-file-url" value="<?php echo $this->img_modal['file_url'];?>">
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
					<?php echo ob_get_clean();
				}
			}
		}

		/**
		 * Image modal taxonomies & tags tab content
		 */
		public function foogallery_img_modal_tab_content_taxonomies() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					$selected_categories = $selected_tags = array();
					$tags = get_terms( array(
						'taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
						'hide_empty' => false,
					) );
					$categories = get_terms( array(
						'taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
						'hide_empty' => false,
					) );
					if ( is_array ( $this->img_modal['img_categories'] ) && !empty ( $this->img_modal['img_categories'] ) ) {
						foreach ( $this->img_modal['img_categories'] as $cat ) {
							$selected_categories[] = $cat->term_id;
						}
					}
					if ( is_array ( $this->img_modal['img_tags'] ) && !empty ( $this->img_modal['img_tags'] ) ) {
						foreach ( $this->img_modal['img_tags'] as $tag ) {
							$selected_tags[] = $tag->term_id;
						}
					}
					?>
					<section id="foogallery-panel-taxonomies" class="tab-panel">
						<label for="foogallery_woocommerce_tags"><?php _e('Media Tags:', 'foogallery'); ?></label>
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
						<br class="clear">
						<label for="foogallery_woocommerce_categories"><?php _e('Media Categories:', 'foogallery'); ?></label>
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
						<br class="clear">
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		/**
		 * Image modal thumbnails tab content
		 */
		public function foogallery_img_modal_tab_content_thumbnails() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					$engine = foogallery_thumb_active_engine();
					?>
					<section id="foogallery-panel-thumbnails" class="tab-panel">
						<div class="settings">
							<span class="setting" data-setting="crop-from-position">
								<label for="attachments-crop-from-position" class="name"><?php _e('Crop Position', 'foogallery'); ?></label>
								<div id="foogallery_crop_pos">
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="left,top" title="<?php _e('Left, Top', 'foogallery'); ?>" <?php checked( 'left,top', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="center,top" title="<?php _e('Center, Top', 'foogallery'); ?>" <?php checked( 'center,top', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="right,top" title="<?php _e('Right, Top', 'foogallery'); ?>" <?php checked( 'right,top', $this->img_modal['foogallery_crop_pos_val'], true); ?>><br>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="left,center" title="<?php _e('Left, Center', 'foogallery'); ?>" <?php checked( 'left,center', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="center,center" title="<?php _e('Center, Center', 'foogallery'); ?>" <?php checked( 'center,center', $this->img_modal['foogallery_crop_pos'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="right,center" title="<?php _e('Right, Center', 'foogallery'); ?>" <?php checked( 'right,center', $this->img_modal['foogallery_crop_pos_val'], true); ?>><br>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="left,bottom" title="<?php _e('Left, Bottom', 'foogallery'); ?>" <?php checked( 'left,bottom', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="center,bottom" title="<?php _e('Center, Bottom', 'foogallery'); ?>" <?php checked( 'center,bottom', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="right,bottom" title="<?php _e('Right, Bottom', 'foogallery'); ?>" <?php checked( 'right,bottom', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
								</div>
							</span>
						
						<div class="foogallery-attachments-list-bar">
							<div class="settings">								
								<span class="setting override-thumbnail <?php echo $this->img_modal['override_class']; ?>" data-setting="override-thumbnail">
									<label for="attachment-details-two-column-override-thumbnail" class="name"><?php _e('Alternate Thumbnail URL', 'foogallery'); ?></label>
									<input type="text" id="attachments-foogallery-override-thumbnail" value="<?php echo $this->img_modal['alternate_img_src']; ?>">
									<input type="hidden" id="attachments-foogallery-override-thumbnail-id" value="<?php echo $this->img_modal['foogallery_override_thumbnail']; ?>">
								</span>
								<span class="setting override-thumbnail-preview <?php echo $this->img_modal['override_class']; ?>" data-setting="override-thumbnail-preview">
										<label for="attachment-details-two-column-override-thumbnail-preview" class="name"><?php _e('Alternate Thumbnail Preview', 'foogallery'); ?></label>
										<img id="attachment-details-two-column-override-thumbnail-preview" src="<?php echo $this->img_modal['alternate_img_src']; ?>" alt="Alternate Thumbnail">
								</span>
								<span class="setting alternate-image-upload-settings" data-setting="alternate-image-upload">
									<div class="alternate-image-upload-wrap">
										<button type="button" class="button button-primary button-large" id="foogallery-img-modal-alternate-image-upload"
												data-uploader-title="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
												data-uploader-button-text="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
												data-img-id="<?php echo $this->img_modal['img_id']; ?>">
											<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>
										</button>
										<button type="button" class="button button-primary button-large <?php echo $this->img_modal['override_class']; ?>" id="foogallery-img-modal-alternate-image-delete"
												data-uploader-title="<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>"
												data-uploader-button-text="<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>"
												data-img-id="<?php echo $this->img_modal['img_id']; ?>">
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
		public function foogallery_img_modal_tab_content_watermark() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-watermark" class="tab-panel">
						<?php if ( $this->img_modal['attachment_watermark'] ) { ?>
							<div id="foogallery-panel-watermark-preview">
								<label for="attachments-crop-from-position"><?php _e('Watermark Image Preview:', 'foogallery'); ?></label>
								<a href="<?php echo $this->img_modal['attachment_watermark']['url']; ?>" target="_blank">
									<img width="150" src="<?php echo $this->img_modal['attachment_watermark']['url']; ?>" alt="watermark">
								</a>
							</div>
						<?php }  ?>
						<div class="foogallery_metabox_field-watermark_status">
							<button type="button" class="button button-primary button-large protection_generate">
							<?php
							if ( empty( $this->img_modal['progress'] ) ) {
								echo esc_html( __( 'Generate Watermarked Images', 'foogallery' ) );
							} else {
								echo esc_html( __( 'Continue Generating', 'foogallery' ) );
							}
							?>
							</button>
							<span style="position: absolute" class="spinner foogallery_protection_generate_spinner"></span>
							<span style="padding-left: 40px; line-height: 25px;" class="foogallery_protection_generate_progress"></span>
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		/**
		 * Image modal EXIF tab content
		 */
		public function foogallery_img_modal_tab_content_exif() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					if ( is_array ( $this->img_modal['meta'] ) && !empty ( $this->img_modal['meta'] ) ) {
						$keywords = '';
						$image_meta = array_key_exists( 'image_meta', $this->img_modal['meta'] ) ? $this->img_modal['meta']['image_meta'] : '';
						if ( is_array( $image_meta ) && !empty ( $image_meta ) ) {				
							$keywords_str = array_key_exists( 'keywords', $image_meta ) ? implode( ',', $this->img_modal['meta']['image_meta']['keywords'] ) : '';
							$keywords = rtrim( $keywords_str, ',' );
						} ?>
						<section id="foogallery-panel-exif" class="tab-panel">							
							<div class="settings">	
								<span class="setting" data-setting="title">
									<label for="attachment-details-two-column-aperture" class="name"><?php _e('Aperture Text', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-aperture" value="<?php echo $this->img_modal['meta']['image_meta']['aperture'];?>">
								</span>		
								<span class="setting" data-setting="camera">
									<label for="attachment-details-two-column-camera" class="name"><?php _e('Camera Text', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-camera" value="<?php echo $this->img_modal['meta']['image_meta']['camera'];?>">
								</span>	
								<span class="setting" data-setting="created-timestamp">
									<label for="attachment-details-two-column-created-timestamp" class="name"><?php _e('Created Timestamp', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-created-timestamp" value="<?php echo $this->img_modal['meta']['image_meta']['created_timestamp'];?>">
								</span>	
								<span class="setting" data-setting="shutter-speed">
									<label for="attachment-details-two-column-shutter-speed" class="name"><?php _e('Shutter Speed Text', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-shutter-speed" value="<?php echo $this->img_modal['meta']['image_meta']['shutter_speed'];?>">
								</span>			
								<span class="setting" data-setting="focal-length">
									<label for="attachment-details-two-column-focal-length" class="name"><?php _e('Focal Length Text', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-focal-length" value="<?php echo $this->img_modal['meta']['image_meta']['focal_length'];?>">
								</span>		
								<span class="setting" data-setting="iso">
									<label for="attachment-details-two-column-iso" class="name"><?php _e('ISO Text', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-iso" value="<?php echo $this->img_modal['meta']['image_meta']['iso'];?>">
								</span>	
								<span class="setting" data-setting="orientation">
									<label for="attachment-details-two-column-orientation" class="name"><?php _e('Orientation', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-orientation" value="<?php echo $this->img_modal['meta']['image_meta']['orientation'];?>">
								</span>	
								<span class="setting" data-setting="keywords">
									<label for="attachment-details-two-column-keywords" class="name"><?php _e('Keywords', 'foogallery'); ?></label>
									<input type="text" id="attachment-details-two-column-keywords" value="<?php echo $keywords;?>">
								</span>	
							</div>
						</section>
						<?php echo ob_get_clean();
					}
				}
			}
		}

		/**
		 * Image modal more tab content
		 */
		public function foogallery_img_modal_tab_content_more() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-more" class="tab-panel">
						<div class="settings">	
							<span class="setting" data-setting="data-width">
								<label for="attachment-details-two-column-data-width" class="name"><?php _e('Operride Width', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-data-width" value="<?php echo $this->img_modal['data_width']; ?>">
							</span>		
							<span class="setting" data-setting="data-height">
								<label for="attachment-details-two-column-data-height" class="name"><?php _e('Override Height', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-data-height" value="<?php echo $this->img_modal['data_height']; ?>">
							</span>	
							<span class="setting" data-setting="panning">
								<label for="attachment-details-two-column-panning" class="name"><?php _e('Panning', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-panning" value="<?php echo $this->img_modal['panning']; ?>">
							</span>	
							<span class="setting" data-setting="override-type">
								<label for="attachment-details-two-column-override-type" class="name"><?php _e('Override Type', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-override-type" value="<?php echo $this->img_modal['override_type']; ?>">
							</span>	
							<span class="setting" data-setting="button-text">
								<label for="attachment-details-two-column-button-text" class="name"><?php _e('Button Text', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-button-text" value="<?php echo $this->img_modal['foogallery_button_text']; ?>">
							</span>	
							<span class="setting" data-setting="button-url">
								<label for="attachment-details-two-column-button-url" class="name"><?php _e('Button URL', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-button-url" value="<?php echo $this->img_modal['foogallery_button_url']; ?>">
							</span>	
							<span class="setting" data-setting="ribbon">
								<label for="attachment-details-two-column-ribbon" class="name"><?php _e('Ribbon', 'foogallery'); ?></label>
								<select id="attachment-details-two-column-ribbon">
									<option selected="selected" value=""><?php _e('None', 'foogallery'); ?></option>
									<option value="fg-ribbon-5" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-5', true ); ?>><?php _e('Type 1 (top-right, diagonal, green)', 'foogallery'); ?></option>
									<option value="fg-ribbon-3" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-3', true ); ?>><?php _e('Type 2 (top-left, small, blue)', 'foogallery'); ?></option>
									<option value="fg-ribbon-4" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-4', true ); ?>><?php _e('Type 3 (top, full-width, yellow)', 'foogallery'); ?></option>
									<option value="fg-ribbon-6" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-6', true ); ?>><?php _e('Type 4 (top-right, rounded, pink)', 'foogallery'); ?></option>
									<option value="fg-ribbon-2" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-2', true ); ?>><?php _e('Type 5 (top-left, medium, purple)', 'foogallery'); ?></option>
									<option value="fg-ribbon-1" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-1', true ); ?>><?php _e('Type 6 (top-left, vertical, orange)', 'foogallery'); ?></option>
								</select>
							</span>	
							<span class="setting" data-setting="ribbon-text">
								<label for="attachment-details-two-column-ribbon-text" class="name"><?php _e('Ribbon Text', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-ribbon-text" value="<?php echo $this->img_modal['foogallery_ribbon_text']; ?>">
							</span>
							<span class="setting" data-setting="product-id">
								<label for="attachment-details-two-column-product-id" class="name"><?php _e('Product ID', 'foogallery'); ?></label>
								<input type="text" id="attachment-details-two-column-product-id" value="<?php echo $this->img_modal['foogallery_product']; ?>">
							</span>	
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		/**
		 * Image modal info section
		 */
		public function foogallery_img_modal_info() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					$dimensions = $img_width = $img_height = $file_size = '';
					if ( is_array( $this->img_modal['meta'] ) && !empty ( $this->img_modal['meta'] ) ) {
						$img_width = ( array_key_exists ('width', $this->img_modal['meta'] ) ? $this->img_modal['meta']['width'] : '' );
						$img_height = ( array_key_exists( 'height', $this->img_modal['meta'] ) ? $this->img_modal['meta']['height'] : '' );
						$file_size = ( array_key_exists( 'filesize', $this->img_modal['meta'] ) ? ( size_format( $this->img_modal['meta']['filesize'], 2 ) ) : '' );
					}
					
					if ( $img_width && $img_height ) {
						$dimensions = $img_width . ' x ' . $img_height . ' px';
					} ?>
					<section id="foogallery-panel-info">
						<div class="foogallery-panel-info-inner">
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-uploaded-on" class="name"><?php _e('Uploaded On: ', 'foogallery'); ?></label>
								<span><?php echo $this->img_modal['post_date']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-uploaded-by" class="name"><?php _e('Uploaded By: ', 'foogallery'); ?></label>
								<span><?php echo $this->img_modal['author_name']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-file-name" class="name"><?php _e('File Name: ', 'foogallery'); ?></label>
								<span id="attachment-details-two-column-copy-file-name"><?php echo $this->img_modal['img_title']; ?></span>
								<?php /* ?>
								<span class="copy-to-clipboard-container">
									<button type="button" class="button button-small copy-attachment-file-name" data-clipboard-target="#attachment-details-two-column-copy-file-name"><?php _e('Copy file name to clipboard', 'foogallery'); ?></button>
									<span class="success hidden" aria-hidden="true"><?php _e('Copied!', 'foogallery'); ?></span>
								</span>
								<?php */ ?>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-file-type" class="name"><?php _e('File Type: ', 'foogallery'); ?></label>
								<span><?php echo $this->img_modal['file_type']; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-file-size" class="name"><?php _e('File Size: ', 'foogallery'); ?></label>
								<span><?php echo $file_size; ?></span>
							</div>
							<div class="foogallery-modal-info-fields">
								<label for="attachment-details-two-column-dimensions" class="name"><?php _e('Dimensions: ', 'foogallery'); ?></label>
								<span><?php echo $dimensions; ?></span>
							</div>
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		/**
		 * Image modal edit section 
		 */
		public function foogallery_img_modal_edit_section() {
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					ob_start(); ?>
					<div class="foogallery-image-edit-view">
					<?php do_action( 'foogallery_img_modal_before_thumbnail' );
					
					if ( $this->img_modal['image_attributes'] ) : ?>
						<img src="<?php echo $this->img_modal['image_attributes'][0]; ?>" width="<?php echo $this->img_modal['image_attributes'][1]; ?>" height="<?php echo $this->img_modal['image_attributes'][2]; ?>" />
					<?php endif; ?>
					</div>
					<div class="foogallery-image-edit-button">
					<a id="imgedit-open-btn-<?php echo $this->img_modal['img_id']; ?>" href="<?php echo get_admin_url().'upload.php?item='.$this->img_modal['img_id'];?>&mode=edit" class="button"><?php _e('Edit Image', 'foogallery'); ?></a>
					<a target="_blank" id="img-open-full-btn-<?php echo $this->img_modal['img_id']; ?>" href="<?php echo $this->img_modal['img_path'];?>" class="button"><?php _e('Open Full Size Image', 'foogallery'); ?></a>
					</div>
					<?php echo ob_get_clean();
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

			delete_post_meta( $img_id, 'foogallery_override_thumbnail' );

			wp_die();

		}

	}

}