<?php
/*
 * FooGallery Admin Gallery Attachment Modal class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Attachment_Modal' ) ) {

	class FooGallery_Admin_Gallery_Attachment_Modal {

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
		);

		public function __construct() {
			add_action( 'wp_ajax_open_foogallery_image_edit_modal', array( $this, 'open_foogallery_image_edit_modal_ajax' ) );
			add_action( 'admin_footer', array( $this, 'foogallery_image_editor_modal' ) );
			add_filter( 'foogallery_attachment_custom_fields', array( $this, 'foogallery_add_override_thumbnail_field' ) );
			add_action( 'wp_ajax_foogallery_save_modal_metadata', array( $this, 'foogallery_save_modal_metadata' ) );
			add_action( 'foogallery_img_modal_before_tab_container', array( $this, 'foogallery_img_modal_edit_section' ) );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_main' ), 10 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_taxonomies' ), 20 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_thumbnails' ), 30 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_watermark' ), 40 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_exif' ), 50 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_more' ), 60 );
			add_action( 'foogallery_img_modal_tabs_view', array( $this, 'foogallery_img_modal_tab_info' ), 70 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_main' ), 10, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_taxonomies' ), 20, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_thumbnails' ), 30, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_watermark' ), 40, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_exif' ), 50, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_more' ), 60, 1 );
			add_action( 'foogallery_img_modal_tab_content', array( $this, 'foogallery_img_modal_tab_content_info' ), 70, 1 );
		}

		// Generate image edit modal on gallery creation
		public function open_foogallery_image_edit_modal_ajax() {

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
					<?php do_action( 'foogallery_img_modal_tab_content', $_POST ); ?>
				</div>
			</div>

			<?php do_action( 'foogallery_img_modal_after_tab_container' ); ?>	
				
			<?php echo ob_get_clean();
			wp_die();
		}

		// Admin modal wrapper for gallery image edit
		public function foogallery_image_editor_modal() {
			$modal_style = foogallery_get_setting( 'hide_admin_gallery_attachment_modal' );
			?>
			<div id="foogallery-image-edit-modal" style="display: none;" data-img_type="normal" data-gallery_id="<?php echo $_GET['post']; ?>" data-nonce="<?php echo wp_create_nonce('foogallery-modal-nonce');?>" data-modal_style="<?php echo $modal_style; ?>">
				<div class="media-modal wp-core-ui">
					<div class="media-modal-content">
						<div class="edit-attachment-frame mode-select hide-menu hide-router">
							<div class="edit-media-header">
								<button type="button" class="media-modal-close" onclick="close_foogallery_img_modal();"><span class="media-modal-icon"><span class="screen-reader-text">Close dialog</span></span></button>
							</div>
							<div class="media-frame-title"><h1>Foogallery attachment details</h1></div>
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

		public function foogallery_save_modal_metadata() {
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
					$image_meta['image_data']['aperture'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-camera' ) {
					$is_img_meta = 'yes';
					$image_meta['image_data']['camera'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-created-timestamp' ) {
					$is_img_meta = 'yes';
					$image_meta['image_data']['created_timestamp'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-shutter-speed' ) {
					$is_img_meta = 'yes';
					$image_meta['image_data']['shutter_speed'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-focal-length' ) {
					$is_img_meta = 'yes';
					$image_meta['image_data']['focal_length'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-iso' ) {
					$is_img_meta = 'yes';
					$image_meta['image_data']['iso'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-orientation' ) {
					$is_img_meta = 'yes';
					$image_meta['image_data']['orientation'] = $meta['input_val'];
				}
				if ( $meta['input_id'] == 'attachment-details-two-column-keywords' ) {
					$is_img_meta = 'yes';
					$keywords = explode(',', $meta['input_val']);
					$image_meta['image_data']['keywords'] = $keywords;
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

		private function set_image_modal_vars( $args = array() ) {
			$this->img_modal['img_id'] = (int) sanitize_text_field( $args['img_id'] );
			$this->img_modal['gallery_id'] = (int) sanitize_text_field( $args['gallery_id'] );
			$this->img_modal['img_post'] = get_post( $this->img_modal['img_id'] );
			$this->img_modal['image_attributes'] = wp_get_attachment_image_src( $this->img_modal['img_id'] );
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
			$this->img_modal['img_categories'] = get_the_terms( $this->img_modal['img_id'], FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
			$this->img_modal['img_tags'] = get_the_terms( $this->img_modal['img_id'], FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
			$this->img_modal['nonce'] = wp_create_nonce( 'foogallery_image_edit_'.$this->img_modal['img_id'] );
			$this->img_modal['meta'] = wp_get_attachment_metadata( $this->img_modal['img_id'] );
			return $this->img_modal;
		}

		public function foogallery_img_modal_tab_main() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-main">
				<input type="radio" name="tabset" id="foogallery-tab-main" aria-controls="foogallery-panel-main" checked>
				<label for="foogallery-tab-main">Main</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_taxonomies() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-taxonomies">
				<input type="radio" name="tabset" id="foogallery-tab-taxonomies" aria-controls="foogallery-panel-taxonomies">
				<label for="foogallery-tab-taxonomies">Taxonomies</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_thumbnails() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-thumbnails">
				<input type="radio" name="tabset" id="foogallery-tab-thumbnails" aria-controls="foogallery-panel-thumbnails">
				<label for="foogallery-tab-thumbnails">Thumbnails</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_watermark() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-watermark">
				<input type="radio" name="tabset" id="foogallery-tab-watermark" aria-controls="foogallery-panel-watermark">
				<label for="foogallery-tab-watermark">Watermark</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_exif() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-exif">
				<input type="radio" name="tabset" id="foogallery-tab-exif" aria-controls="foogallery-panel-exif">
				<label for="foogallery-tab-exif">EXIF</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_more() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-more">
				<input type="radio" name="tabset" id="foogallery-tab-more" aria-controls="foogallery-panel-more">
				<label for="foogallery-tab-more">More</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_info() { ?>
			<div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-info">
				<input type="radio" name="tabset" id="foogallery-tab-info" aria-controls="foogallery-panel-info">
				<label for="foogallery-tab-info">Info</label>
			</div>
		<?php }

		public function foogallery_img_modal_tab_content_main( $args = array() ) {
			$img_modal = $this->set_image_modal_vars( $args );
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-main" class="tab-panel active" data-nonce="<?php echo $this->img_modal['nonce'];?>">
						<div class="settings">								
							<span class="setting" data-setting="title">
								<label for="attachment-details-two-column-title" class="name">Title</label>
								<input type="text" id="attachment-details-two-column-title" value="<?php echo $this->img_modal['img_title'];?>">
							</span>								
							<span class="setting" data-setting="caption">
								<label for="attachment-details-two-column-caption" class="name">Caption</label>
								<textarea id="attachment-details-two-column-caption"><?php echo $this->img_modal['caption'];?></textarea>
							</span>
							<span class="setting" data-setting="description">
								<label for="attachment-details-two-column-description" class="name">Description</label>
								<textarea id="attachment-details-two-column-description"><?php echo $this->img_modal['description'];?></textarea>
							</span>
							<span class="setting has-description" data-setting="alt">
								<label for="attachment-details-two-column-alt-text" class="name">ALT Text</label>
								<input type="text" id="attachment-details-two-column-alt-text" value="<?php echo $this->img_modal['image_alt'];?>" aria-describedby="alt-text-description">
							</span>
							<span class="setting" data-setting="custom_url">
								<label for="attachments-foogallery-custom-url" class="name">Custom URL</label>
								<input type="text" id="attachments-foogallery-custom-url" value="<?php echo $this->img_modal['custom_url'];?>">
							</span>
							<span class="setting" data-setting="custom_target">
								<label for="attachments-foogallery-custom-target" class="name">Custom Target</label>
								<select name="attachments-foogallery-custom-target" id="attachments-foogallery-custom-target">
									<option value="default" <?php selected( 'default', $this->img_modal['custom_target'], true ); ?>>Default</option>
									<option value="_blank" <?php selected( '_blank', $this->img_modal['custom_target'], true ); ?>>New tab (_blank)</option>
									<option value="_self" <?php selected( '_self', $this->img_modal['custom_target'], true ); ?>>Same tab (_self)</option>
									<option value="foobox" <?php selected( 'foobox', $this->img_modal['custom_target'], true ); ?>>FooBox</option>
								</select>
							</span>
							<span class="setting" data-setting="custom_class">
								<label for="attachments-foogallery-custom-class" class="name">Custom Class</label>
								<input type="text" id="attachments-foogallery-custom-class" value="<?php echo $this->img_modal['custom_class'];?>">
							</span>	
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

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
						<label for="foogallery_woocommerce_tags">Media Tags:</label>
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
						<label for="foogallery_woocommerce_categories">Media Categories:</label>
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

		public function foogallery_img_modal_tab_content_thumbnails() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					$engine = foogallery_thumb_active_engine();
					?>
					<section id="foogallery-panel-thumbnails" class="tab-panel">
						<div class="settings">
							<span class="setting" data-setting="crop-from-position">
								<label for="attachments-crop-from-position" class="name">Crop Position</label>
								<div id="foogallery_crop_pos">
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="left,top" title="Left, Top" <?php checked( 'left,top', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="center,top" title="Center, Top" <?php checked( 'center,top', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="right,top" title="Right, Top" <?php checked( 'right,top', $this->img_modal['foogallery_crop_pos_val'], true); ?>><br>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="left,center" title="Left, Center" <?php checked( 'left,center', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="center,center" title="Center, Center" <?php checked( 'center,center', $this->img_modal['foogallery_crop_pos'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="right,center" title="Right, Center" <?php checked( 'right,center', $this->img_modal['foogallery_crop_pos_val'], true); ?>><br>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="left,bottom" title="Left, Bottom" <?php checked( 'left,bottom', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="center,bottom" title="Center, Bottom" <?php checked( 'center,bottom', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
									<input type="radio" name="<?php echo $this->img_modal['foogallery_crop_pos'];?>" value="right,bottom" title="Right, Bottom" <?php checked( 'right,bottom', $this->img_modal['foogallery_crop_pos_val'], true); ?>>
								</div>
							</span>
						
						<div class="foogallery-attachments-list-bar">
							<div class="settings">								
								<?php if ( $this->img_modal['foogallery_override_thumbnail'] ) {
									$alternate_thumb_img = wp_get_attachment_image_src( $this->img_modal['foogallery_override_thumbnail'] ); 
									?>
									<span class="setting" data-setting="override-thumbnail">
										<label for="attachment-details-two-column-override-thumbnail" class="name">Alternate Thumbnail URL</label>
										<input type="text" id="attachments-foogallery-override-thumbnail" value="<?php echo $alternate_thumb_img[0]; ?>">
										<input type="hidden" id="attachments-foogallery-override-thumbnail-id" value="<?php echo $this->img_modal['foogallery_override_thumbnail']; ?>">
									</span>
									<span class="setting" data-setting="override-thumbnail-preview">
										<label for="attachment-details-two-column-override-thumbnail-preview" class="name">Alternate Thumbnail Preview</label>
										<img id="attachment-details-two-column-override-thumbnail-preview" src="<?php echo $alternate_thumb_img[0]; ?>" alt="Alternate Thumbnail">
									</span>
								<?php } ?>
								<span class="setting" data-setting="alternate-image-upload">
									<label class="name"></label>
									<button type="button" class="button button-primary button-large" id="foogallery-img-modal-alternate-image-upload"
											data-uploader-title="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
											data-uploader-button-text="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
											data-img-id="<?php echo $this->img_modal['img_id']; ?>">
										<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>
									</button>
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

		public function foogallery_img_modal_tab_content_watermark() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-watermark" class="tab-panel">
						<?php if ( $this->img_modal['attachment_watermark'] ) { ?>
							<div id="foogallery-panel-watermark-preview">
								<label for="attachments-crop-from-position">Watermark Image Preview: </label>
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

		public function foogallery_img_modal_tab_content_exif() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					if ( is_array ( $this->img_modal['meta'] ) && !empty ( $this->img_modal['meta'] ) ) {
						$keywords = '';
						$image_data = array_key_exists( 'image_data', $this->img_modal['meta'] ) ? $this->img_modal['meta']['image_data'] : '';
						if ( is_array( $image_data ) && !empty ( $image_data ) ) {				
							$keywords_str = array_key_exists( 'keywords', $image_data ) ? implode( ',', $this->img_modal['meta']['image_data']['keywords'] ) : '';
							$keywords = rtrim( $keywords_str, ',' );
						} ?>
						<section id="foogallery-panel-exif" class="tab-panel">							
							<div class="settings">	
								<span class="setting" data-setting="title">
									<label for="attachment-details-two-column-aperture" class="name">Aperture Text</label>
									<input type="text" id="attachment-details-two-column-aperture" value="<?php echo $this->img_modal['meta']['image_data']['aperture'];?>">
								</span>		
								<span class="setting" data-setting="camera">
									<label for="attachment-details-two-column-camera" class="name">Camera Text</label>
									<input type="text" id="attachment-details-two-column-camera" value="<?php echo $this->img_modal['meta']['image_data']['camera'];?>">
								</span>	
								<span class="setting" data-setting="created-timestamp">
									<label for="attachment-details-two-column-created-timestamp" class="name">Created Timestamp</label>
									<input type="text" id="attachment-details-two-column-created-timestamp" value="<?php echo $this->img_modal['meta']['image_data']['created_timestamp'];?>">
								</span>	
								<span class="setting" data-setting="shutter-speed">
									<label for="attachment-details-two-column-shutter-speed" class="name">Shutter Speed Text</label>
									<input type="text" id="attachment-details-two-column-shutter-speed" value="<?php echo $this->img_modal['meta']['image_data']['shutter_speed'];?>">
								</span>			
								<span class="setting" data-setting="focal-length">
									<label for="attachment-details-two-column-focal-length" class="name">Focal Length Text</label>
									<input type="text" id="attachment-details-two-column-focal-length" value="<?php echo $this->img_modal['meta']['image_data']['focal_length'];?>">
								</span>		
								<span class="setting" data-setting="iso">
									<label for="attachment-details-two-column-iso" class="name">ISO Text</label>
									<input type="text" id="attachment-details-two-column-iso" value="<?php echo $this->img_modal['meta']['image_data']['iso'];?>">
								</span>	
								<span class="setting" data-setting="orientation">
									<label for="attachment-details-two-column-orientation" class="name">Orientation</label>
									<input type="text" id="attachment-details-two-column-orientation" value="<?php echo $this->img_modal['meta']['image_data']['orientation'];?>">
								</span>	
								<span class="setting" data-setting="keywords">
									<label for="attachment-details-two-column-keywords" class="name">Keywords</label>
									<input type="text" id="attachment-details-two-column-keywords" value="<?php echo $keywords;?>">
								</span>	
							</div>
						</section>
						<?php echo ob_get_clean();
					}
				}
			}
		}

		public function foogallery_img_modal_tab_content_more() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) { ?>
					<section id="foogallery-panel-more" class="tab-panel">
						<div class="settings">	
							<span class="setting" data-setting="data-width">
								<label for="attachment-details-two-column-data-width" class="name">Operride Width</label>
								<input type="text" id="attachment-details-two-column-data-width" value="<?php echo $this->img_modal['data_width']; ?>">
							</span>		
							<span class="setting" data-setting="data-height">
								<label for="attachment-details-two-column-data-height" class="name">Override Height</label>
								<input type="text" id="attachment-details-two-column-data-height" value="<?php echo $this->img_modal['data_height']; ?>">
							</span>	
							<span class="setting" data-setting="panning">
								<label for="attachment-details-two-column-panning" class="name">Panning</label>
								<input type="text" id="attachment-details-two-column-panning" value="<?php echo $this->img_modal['panning']; ?>">
							</span>	
							<span class="setting" data-setting="override-type">
								<label for="attachment-details-two-column-override-type" class="name">Override Type</label>
								<input type="text" id="attachment-details-two-column-override-type" value="<?php echo $this->img_modal['override_type']; ?>">
							</span>	
							<span class="setting" data-setting="button-text">
								<label for="attachment-details-two-column-button-text" class="name">Button Text</label>
								<input type="text" id="attachment-details-two-column-button-text" value="<?php echo $this->img_modal['foogallery_button_text']; ?>">
							</span>	
							<span class="setting" data-setting="button-url">
								<label for="attachment-details-two-column-button-url" class="name">Button URL</label>
								<input type="text" id="attachment-details-two-column-button-url" value="<?php echo $this->img_modal['foogallery_button_url']; ?>">
							</span>	
							<span class="setting" data-setting="ribbon">
								<label for="attachment-details-two-column-ribbon" class="name">Ribbon</label>
								<select id="attachment-details-two-column-ribbon">
									<option selected="selected" value="">None</option>
									<option value="fg-ribbon-5" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-5', true ); ?>>Type 1 (top-right, diagonal, green)</option>
									<option value="fg-ribbon-3" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-3', true ); ?>>Type 2 (top-left, small, blue)</option>
									<option value="fg-ribbon-4" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-4', true ); ?>>Type 3 (top, full-width, yellow)</option>
									<option value="fg-ribbon-6" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-6', true ); ?>>Type 4 (top-right, rounded, pink)</option>
									<option value="fg-ribbon-2" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-2', true ); ?>>Type 5 (top-left, medium, purple)</option>
									<option value="fg-ribbon-1" <?php selected( $this->img_modal['foogallery_ribbon'], 'fg-ribbon-1', true ); ?>>Type 6 (top-left, vertical, orange)</option>
								</select>
							</span>	
							<span class="setting" data-setting="ribbon-text">
								<label for="attachment-details-two-column-ribbon-text" class="name">Ribbon Text</label>
								<input type="text" id="attachment-details-two-column-ribbon-text" value="<?php echo $this->img_modal['foogallery_ribbon_text']; ?>">
							</span>
							<span class="setting" data-setting="product-id">
								<label for="attachment-details-two-column-product-id" class="name">Product ID</label>
								<input type="text" id="attachment-details-two-column-product-id" value="<?php echo $this->img_modal['foogallery_product']; ?>">
							</span>	
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		public function foogallery_img_modal_tab_content_info() {
			ob_start();
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					$dimensions = '';
					$img_width = array_key_exists ('width', $this->img_modal['meta'] ) ? $this->img_modal['meta']['width'] : '';
					$img_height = array_key_exists( 'height', $this->img_modal['meta'] ) ? $this->img_modal['meta']['height'] : '';
					$file_size = array_key_exists( 'filesize', $this->img_modal['meta'] ) ? ( size_format( $this->img_modal['meta']['filesize'], 2 ) ) : '';
					if ( $img_width && $img_height ) {
						$dimensions = $img_width . ' x ' . $img_height . ' px';
					} ?>
					<section id="foogallery-panel-info" class="tab-panel">
						<div class="foogallery-panel-info-inner">
							<div>
								<label for="attachment-details-two-column-uploaded-on" class="name">Uploaded On: </label>
								<span><?php echo $this->img_modal['post_date']; ?></span>
							</div>
							<div>
								<label for="attachment-details-two-column-uploaded-by" class="name">Uploaded By: </label>
								<span><?php echo $this->img_modal['author_name']; ?></span>
							</div>
							<div>
								<label for="attachment-details-two-column-file-name" class="name">File Name: </label>
								<span id="attachment-details-two-column-copy-file-name"><?php echo $this->img_modal['img_title']; ?></span>
								<span class="copy-to-clipboard-container">
									<button type="button" class="button button-small copy-attachment-file-name" data-clipboard-target="#attachment-details-two-column-copy-file-name">Copy file name to clipboard</button>
									<span class="success hidden" aria-hidden="true">Copied!</span>
								</span>
							</div>
							<div>
								<label for="attachment-details-two-column-file-type" class="name">File Type: </label>
								<span><?php echo $this->img_modal['file_type']; ?></span>
							</div>
							<div>
								<label for="attachment-details-two-column-file-size" class="name">File Size: </label>
								<span><?php echo $file_size; ?></span>
							</div>
							<div>
								<label for="attachment-details-two-column-dimensions" class="name">Dimensions: </label>
								<span><?php echo $dimensions; ?></span>
							</div>
						</div>
					</section>
					<?php echo ob_get_clean();
				}
			}
		}

		public function foogallery_img_modal_edit_section() {
			if ( is_array( $this->img_modal ) && !empty ( $this->img_modal ) ) {
				if ( $this->img_modal['img_id'] > 0 ) {
					ob_start(); ?>
					<div class="foogallery-image-edit-view">
					<?php if ( $this->img_modal['image_attributes'] ) : ?>
						<img src="<?php echo $this->img_modal['image_attributes'][0]; ?>" width="<?php echo $this->img_modal['image_attributes'][1]; ?>" height="<?php echo $this->img_modal['image_attributes'][2]; ?>" />
					<?php endif; ?>
					</div>
					<div class="foogallery-image-edit-button">
					<a id="imgedit-open-btn-<?php echo $this->img_modal['img_id']; ?>" href="<?php echo get_admin_url().'upload.php?item='.$this->img_modal['img_id'];?>&mode=edit" class="button">Edit Image</a>
					</div>
					<?php echo ob_get_clean();
				}
			}
		}

	}

}