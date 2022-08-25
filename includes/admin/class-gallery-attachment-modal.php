<?php
/*
 * FooGallery Admin Gallery Attachment Modal class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Attachment_Modal' ) ) {

	class FooGallery_Admin_Gallery_Attachment_Modal {

		public function __construct() {
			add_action( 'wp_ajax_open_foogallery_image_edit_modal', array( $this, 'open_foogallery_image_edit_modal_ajax' ) );
			add_action( 'admin_footer', array( $this, 'foogallery_image_editor_modal' ) );
			add_filter( 'foogallery_attachment_custom_fields', array( $this, 'foogallery_add_override_thumbnail_field' ) );
			add_action( 'wp_ajax_foogallery_save_modal_metadata', array( $this, 'foogallery_save_modal_metadata' ) );
		}

		// Generate image edit modal on gallery creation
		public function open_foogallery_image_edit_modal_ajax() {

			// Check for nonce security      
			if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery-modal-nonce' ) ) {
				die ( 'Busted!');
			}

			ob_start();
			$img_id = $_POST['img_id'];
			$gallery_id = $_GET['post'];
			$img_post = get_post( $img_id );
			$image_attributes = wp_get_attachment_image_src( $img_id );
			$title = $img_post->post_title;
			$caption = $img_post->post_excerpt;
			$description = $img_post->post_content;
			$post_date = date( 'F d, Y', strtotime( $img_post->post_date ) );
			$file_type = $img_post->post_mime_type;
			$author_id = $img_post->post_author;
			$author_name = get_the_author_meta( 'display_name', $author_id );
			$file_url = get_the_guid( $img_id );
			$image_alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
			$custom_url = get_post_meta( $img_id, '_foogallery_custom_url', true );
			$custom_target = ( get_post_meta( $img_id, '_foogallery_custom_target', true ) ? get_post_meta( $img_id, '_foogallery_custom_target', true ) : 'default' );
			$attachment_watermark = get_post_meta( $img_id, FOOGALLERY_META_WATERMARK, true );
			$custom_class = get_post_meta( $img_id, '_foogallery_custom_class', true );
			$override_type = get_post_meta( $img_id, '_foogallery_override_type', true );
			$data_width = get_post_meta( $img_id, '_data-width', true );
			$data_height = get_post_meta( $img_id, '_data-height', true );
			$foogallery_ribbon = get_post_meta( $img_id, '_foogallery_ribbon', true );
			$foogallery_ribbon_text = get_post_meta( $img_id, '_foogallery_ribbon_text', true );
			$foogallery_button_text = get_post_meta( $img_id, '_foogallery_button_text', true );
			$foogallery_button_url = get_post_meta( $img_id, '_foogallery_button_url', true );
			$foogallery_product = get_post_meta( $img_id, '_foogallery_product', true );
			$panning = '';
			$progress = get_post_meta( $gallery_id, FOOGALLERY_META_WATERMARK_PROGRESS, true );
			$category_taxonomy = 'foogallery_attachment_category';
			$tag_taxonomy = 'foogallery_attachment_tag';
			$img_categories = get_the_terms( $img_id, $category_taxonomy );
			$img_tags = get_the_terms( $img_id, $tag_taxonomy );
			$selected_categories = array();
			$selected_tags = array();
			$nonce = wp_create_nonce( 'foogallery_image_edit_'.$img_id );
			$meta = wp_get_attachment_metadata( $img_id );
			$img_width = $meta['width'];
			$img_height = $meta['height'];
			$dimensions = '';
			if ( $img_width && $img_height ) {
				$dimensions = $img_width . ' x ' . $img_height . ' px';
			}
			$file_size = size_format( $meta['filesize'], 2 );
			$keywords_str = implode( ',', $meta['image_data']['keywords'] );
			$keywords = rtrim( $keywords_str, ',' );
			foreach ( $img_categories as $cat ) {
				$selected_categories[] = $cat->term_id;
			}
			foreach ( $img_tags as $tag ) {
				$selected_tags[] = $tag->term_id;
			}
			$categories = $terms = get_terms( array(
				'taxonomy' => $category_taxonomy,
				'hide_empty' => false,
			) );
			$tags = $terms = get_terms( array(
				'taxonomy' => $tag_taxonomy,
				'hide_empty' => false,
			) );
			?>
			<div class="foogallery-image-edit-main" data-img_id="<?php echo $img_id; ?>" data-gallery_id="<?php echo $gallery_id; ?>">
				<div class="foogallery-image-edit-view">
				<?php if ( $image_attributes ) : ?>
					<img src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>" />
				<?php endif; ?>
				</div>
				<div class="foogallery-image-edit-button"> <?php //get_admin_url($blog_id:integer|null,$path:string,$scheme:string );?>
				<a id="imgedit-open-btn-<?php echo $img_id; ?>" href="<?php echo get_admin_url().'upload.php?item='.$img_id;?>&mode=edit" class="button">Edit Image</a>
				</div>
			</div>
			<div class="foogallery-image-edit-meta">
				<div class="tabset">
					<!-- Tab 1 -->
					<input type="radio" name="tabset" id="foogallery-tab-main" aria-controls="foogallery-panel-main" checked>
					<label for="foogallery-tab-main">Main</label>
					<!-- Tab 2 -->
					<input type="radio" name="tabset" id="foogallery-tab-taxonomies" aria-controls="foogallery-panel-taxonomies">
					<label for="foogallery-tab-taxonomies">Taxonomies</label>
					<!-- Tab 3 -->
					<input type="radio" name="tabset" id="foogallery-tab-thumbnails" aria-controls="foogallery-panel-thumbnails">
					<label for="foogallery-tab-thumbnails">Thumbnails</label>
					<!-- Tab 4 -->
					<input type="radio" name="tabset" id="foogallery-tab-watermark" aria-controls="foogallery-panel-watermark">
					<label for="foogallery-tab-watermark">Watermark</label>
					<!-- Tab 5 -->
					<input type="radio" name="tabset" id="foogallery-tab-exif" aria-controls="foogallery-panel-exif">
					<label for="foogallery-tab-exif">EXIF</label>
					<!-- Tab 6 -->
					<input type="radio" name="tabset" id="foogallery-tab-more" aria-controls="foogallery-panel-more">
					<label for="foogallery-tab-more">More</label>
					<!-- Tab 7 -->
					<input type="radio" name="tabset" id="foogallery-tab-info" aria-controls="foogallery-panel-info">
					<label for="foogallery-tab-info">Info</label>		
					
					<div class="tab-panels">
						<section id="foogallery-panel-main" class="tab-panel" data-nonce="<?php echo $nonce;?>">
							<div class="settings">								
								<span class="setting" data-setting="title">
									<label for="attachment-details-two-column-title" class="name">Title</label>
									<input type="text" id="attachment-details-two-column-title" value="<?php echo $title;?>">
								</span>								
								<span class="setting" data-setting="caption">
									<label for="attachment-details-two-column-caption" class="name">Caption</label>
									<textarea id="attachment-details-two-column-caption"><?php echo $caption;?></textarea>
								</span>
								<span class="setting" data-setting="description">
									<label for="attachment-details-two-column-description" class="name">Description</label>
									<textarea id="attachment-details-two-column-description"><?php echo $description;?></textarea>
								</span>
								<span class="setting has-description" data-setting="alt">
									<label for="attachment-details-two-column-alt-text" class="name">ALT Text</label>
									<input type="text" id="attachment-details-two-column-alt-text" value="<?php echo $image_alt;?>" aria-describedby="alt-text-description">
								</span>
								<!-- <p class="description" id="alt-text-description"><a href="https://www.w3.org/WAI/tutorials/images/decision-tree" target="_blank" rel="noopener">Learn how to describe the purpose of the image<span class="screen-reader-text"> (opens in a new tab)</span></a>. Leave empty if the image is purely decorative.</p> -->
								<!-- <span class="setting" data-setting="url">
									<label for="attachment-details-two-column-copy-link" class="name">File URL:</label>
									<input type="text" class="attachment-details-copy-link" id="attachment-details-two-column-copy-link" value="<?php echo $file_url;?>" readonly="">
									<span class="copy-to-clipboard-container">
										<button type="button" class="button button-small copy-attachment-url" data-clipboard-target="#attachment-details-two-column-copy-link">Copy URL to clipboard</button>
										<span class="success hidden" aria-hidden="true">Copied!</span>
									</span>
								</span> -->
								<span class="setting" data-setting="custom_url">
									<label for="attachments-foogallery-custom-url" class="name">Custom URL</label>
									<input type="text" id="attachments-foogallery-custom-url" value="<?php echo $custom_url;?>">
								</span>
								<span class="setting" data-setting="custom_target">
									<label for="attachments-foogallery-custom-target" class="name">Custom Target</label>
									<select name="attachments-foogallery-custom-target" id="attachments-foogallery-custom-target">
										<option value="default" <?php selected( 'default', $custom_target, true ); ?>>Default</option>
										<option value="_blank" <?php selected( '_blank', $custom_target, true ); ?>>New tab (_blank)</option>
										<option value="_self" <?php selected( '_self', $custom_target, true ); ?>>Same tab (_self)</option>
										<option value="foobox" <?php selected( 'foobox', $custom_target, true ); ?>>FooBox</option>
									</select>
								</span>
								<span class="setting" data-setting="custom_class">
									<label for="attachments-foogallery-custom-class" class="name">Custom Class</label>
									<input type="text" id="attachments-foogallery-custom-class" value="<?php echo $custom_class;?>">
								</span>	
							</div>
						</section>
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
						<section id="foogallery-panel-thumbnails" class="tab-panel">
							<label for="attachments-crop-from-position">Crop Position</label>
							<div id="foogallery_crop_pos">
								<?php $foogallery_crop_pos = 'foogallery_crop_pos'; ?>
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="left,top" title="Left, Top">
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="center,top" title="Center, Top">
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="right,top" title="Right, Top"><br>
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="left,center" title="Left, Center">
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="center,center" title="Center, Center" checked="checked">
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="right,center" title="Right, Center"><br>
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="left,bottom" title="Left, Bottom">
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="center,bottom" title="Center, Bottom">
								<input type="radio" name="<?php echo $foogallery_crop_pos;?>" value="right,bottom" title="Right, Bottom">
							</div>
							<div class="foogallery-attachments-list-bar">
								<button type="button" class="button button-primary button-large upload_image_button"
										data-uploader-title="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
										data-uploader-button-text="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
										data-post-id="<?php echo $gallery_id; ?>">
									<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>
								</button>
							</div>
							<div class="foogallery-attachments-list-bar clear-thumbnail">
								<button type="button" class="button button-primary button-large upload_image_button"
										data-uploader-title="<?php _e( 'Clear Generated Thumbnails', 'foogallery' ); ?>"
										data-uploader-button-text="<?php _e( 'Clear Generated Thumbnails', 'foogallery' ); ?>"
										data-post-id="<?php echo $gallery_id; ?>">
									<?php _e( 'Clear Generated Thumbnails', 'foogallery' ); ?>
								</button>
							</div>
						</section>
						<section id="foogallery-panel-watermark" class="tab-panel">
							<?php if ( $attachment_watermark ) { ?>
								<div id="foogallery-panel-watermark-preview">
									<label for="attachments-crop-from-position">Watermark Image Preview: </label>
									<a href="<?php echo $attachment_watermark['url']; ?>" target="_blank">
										<img width="150" src="<?php echo $attachment_watermark['url']; ?>" alt="watermark">
									</a>
								</div>
							<?php }  ?>
							<div class="foogallery_metabox_field-watermark_status">
								<button type="button" class="button button-primary button-large protection_generate">
								<?php
								if ( empty( $progress ) ) {
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
						<section id="foogallery-panel-exif" class="tab-panel">							
							<div class="settings">	
								<span class="setting" data-setting="title">
									<label for="attachment-details-two-column-aperture" class="name">Aperture Text</label>
									<input type="text" id="attachment-details-two-column-aperture" value="<?php echo $meta['image_data']['aperture'];?>">
								</span>		
								<span class="setting" data-setting="camera">
									<label for="attachment-details-two-column-camera" class="name">Camera Text</label>
									<input type="text" id="attachment-details-two-column-camera" value="<?php echo $meta['image_data']['camera'];?>">
								</span>	
								<span class="setting" data-setting="created-timestamp">
									<label for="attachment-details-two-column-created-timestamp" class="name">Created Timestamp</label>
									<input type="text" id="attachment-details-two-column-created-timestamp" value="<?php echo $meta['image_data']['created_timestamp'];?>">
								</span>	
								<span class="setting" data-setting="shutter-speed">
									<label for="attachment-details-two-column-shutter-speed" class="name">Shutter Speed Text</label>
									<input type="text" id="attachment-details-two-column-shutter-speed" value="<?php echo $meta['image_data']['shutter_speed'];?>">
								</span>			
								<span class="setting" data-setting="focal-length">
									<label for="attachment-details-two-column-focal-length" class="name">Focal Length Text</label>
									<input type="text" id="attachment-details-two-column-focal-length" value="<?php echo $meta['image_data']['focal_length'];?>">
								</span>		
								<span class="setting" data-setting="iso">
									<label for="attachment-details-two-column-iso" class="name">ISO Text</label>
									<input type="text" id="attachment-details-two-column-iso" value="<?php echo $meta['image_data']['iso'];?>">
								</span>	
								<span class="setting" data-setting="orientation">
									<label for="attachment-details-two-column-orientation" class="name">Orientation</label>
									<input type="text" id="attachment-details-two-column-orientation" value="<?php echo $meta['image_data']['orientation'];?>">
								</span>	
								<span class="setting" data-setting="keywords">
									<label for="attachment-details-two-column-keywords" class="name">Keywords</label>
									<input type="text" id="attachment-details-two-column-keywords" value="<?php echo $keywords;?>">
								</span>	
							</div>
						</section>
						<section id="foogallery-panel-more" class="tab-panel">
							<div class="settings">	
								<span class="setting" data-setting="data-width">
									<label for="attachment-details-two-column-data-width" class="name">Operride Width</label>
									<input type="text" id="attachment-details-two-column-data-width" value="<?php echo $data_width; ?>">
								</span>		
								<span class="setting" data-setting="data-height">
									<label for="attachment-details-two-column-data-height" class="name">Override Height</label>
									<input type="text" id="attachment-details-two-column-data-height" value="<?php echo $data_height; ?>">
								</span>	
								<span class="setting" data-setting="panning">
									<label for="attachment-details-two-column-panning" class="name">Panning</label>
									<input type="text" id="attachment-details-two-column-panning" value="<?php echo $panning; ?>">
								</span>	
								<span class="setting" data-setting="override-type">
									<label for="attachment-details-two-column-override-type" class="name">Override Type</label>
									<input type="text" id="attachment-details-two-column-override-type" value="<?php echo $override_type; ?>">
								</span>	
								<span class="setting" data-setting="button-text">
									<label for="attachment-details-two-column-button-text" class="name">Button Text</label>
									<input type="text" id="attachment-details-two-column-button-text" value="<?php echo $foogallery_button_text; ?>">
								</span>	
								<span class="setting" data-setting="button-url">
									<label for="attachment-details-two-column-button-url" class="name">Button URL</label>
									<input type="text" id="attachment-details-two-column-button-url" value="<?php echo $foogallery_button_url; ?>">
								</span>	
								<span class="setting" data-setting="ribbon">
									<label for="attachment-details-two-column-ribbon" class="name">Ribbon</label>
									<select id="attachment-details-two-column-ribbon">
										<option selected="selected" value="">None</option>
										<option value="fg-ribbon-5" <?php selected( $foogallery_ribbon, 'fg-ribbon-5', true ); ?>>Type 1 (top-right, diagonal, green)</option>
										<option value="fg-ribbon-3" <?php selected( $foogallery_ribbon, 'fg-ribbon-3', true ); ?>>Type 2 (top-left, small, blue)</option>
										<option value="fg-ribbon-4" <?php selected( $foogallery_ribbon, 'fg-ribbon-4', true ); ?>>Type 3 (top, full-width, yellow)</option>
										<option value="fg-ribbon-6" <?php selected( $foogallery_ribbon, 'fg-ribbon-6', true ); ?>>Type 4 (top-right, rounded, pink)</option>
										<option value="fg-ribbon-2" <?php selected( $foogallery_ribbon, 'fg-ribbon-2', true ); ?>>Type 5 (top-left, medium, purple)</option>
										<option value="fg-ribbon-1" <?php selected( $foogallery_ribbon, 'fg-ribbon-1', true ); ?>>Type 6 (top-left, vertical, orange)</option>
									</select>
								</span>	
								<span class="setting" data-setting="ribbon-text">
									<label for="attachment-details-two-column-ribbon-text" class="name">Ribbon Text</label>
									<input type="text" id="attachment-details-two-column-ribbon-text" value="<?php echo $foogallery_ribbon_text; ?>">
								</span>
								<span class="setting" data-setting="product-id">
									<label for="attachment-details-two-column-product-id" class="name">Product ID</label>
									<input type="text" id="attachment-details-two-column-product-id" value="<?php echo $foogallery_product; ?>">
								</span>	
							</div>
						</section>
						<section id="foogallery-panel-info" class="tab-panel">
							<div class="foogallery-panel-info-inner">
								<div>
									<label for="attachment-details-two-column-uploaded-on" class="name">Uploaded On: </label>
									<span><?php echo $post_date; ?></span>
								</div>
								<div>
									<label for="attachment-details-two-column-uploaded-by" class="name">Uploaded By: </label>
									<span><?php echo $author_name; ?></span>
								</div>
								<div>
									<label for="attachment-details-two-column-file-name" class="name">File Name: </label>
									<span id="attachment-details-two-column-copy-file-name"><?php echo $title; ?></span>
									<span class="copy-to-clipboard-container">
										<button type="button" class="button button-small copy-attachment-file-name" data-clipboard-target="#attachment-details-two-column-copy-file-name">Copy file name to clipboard</button>
										<span class="success hidden" aria-hidden="true">Copied!</span>
									</span>
								</div>
								<div>
									<label for="attachment-details-two-column-file-type" class="name">File Type: </label>
									<span><?php echo $file_type; ?></span>
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
					</div>		
				</div>
			</div>
			<?php //echo ob_get_clean();
			wp_die();
		}

		// Admin modal wrapper for gallery image edit
		public function foogallery_image_editor_modal() {
			$modal_style = foogallery_get_setting( 'hide_admin_gallery_attachment_modal' );
			?>
			<div id="foogallery-image-edit-modal" style="display: none;" data-nonce="<?php echo wp_create_nonce('foogallery-modal-nonce');?>" data-modal_style="<?php echo $modal_style; ?>">
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
			$img_id = $_POST['id'];
			$meta = $_POST['meta'];
			$is_post_data = 'no';
			$is_img_meta = 'no';
			$meta_key = '';
			$foogallery_post = array(
				'ID' => $img_id,
			);
			$image_meta = wp_get_attachment_metadata( $img_id );

			if ( array_key_exists( 'input_id', $meta ) ) {
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
				wp_set_object_terms( $img_id, $tags, 'foogallery_attachment_tag', false );
			} elseif ( array_key_exists( 'categories', $meta ) ) {
				$categories = array();
				foreach ( $meta['categories'] as $category ) {
					$categories[] = (int) $category;
				}
				wp_set_object_terms( $img_id, $categories, 'foogallery_attachment_category', false );
			}
			wp_die();
		}

	}

}