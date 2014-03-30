<?php

/*
 * FooGallery MetaBoxes
 */

if (!class_exists('FooGallery_MetaBoxes')) {

    class FooGallery_MetaBoxes {

		private $_plugin_file;
        private $_gallery;

		function __construct($plugin_file) {

			$this->_plugin_file = $plugin_file;

			add_action('add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array($this, 'add_meta_boxes_to_gallery'));

			//save extra post data
			add_action('save_post', array(&$this, 'save_gallery'));
		}

		function add_meta_boxes_to_gallery() {
			add_meta_box(
				'gallery_images',
				__('Gallery Images', 'foogallery'),
				array($this, 'render_gallery_media_metabox'),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);

            add_meta_box(
                'gallery_settings',
                __('Gallery Settings', 'foogallery'),
                array($this, 'render_gallery_settings_metabox'),
                FOOGALLERY_CPT_GALLERY,
                'normal',
                'high'
            );

            add_meta_box(
                'gallery_help',
                __('Gallery Help', 'foogallery'),
                array($this, 'render_gallery_help_metabox'),
                FOOGALLERY_CPT_GALLERY,
                'normal',
                'high'
            );
		}

        function get_gallery($post) {
            if ( !isset($this->_gallery) ) {
                $this->_gallery = FooGallery_Gallery::get($post);
            }

            return $this->_gallery;
        }

		function save_gallery($post_id) {
			// check autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return $post_id;
			}

			// verify nonce
			if (array_key_exists(FOOGALLERY_CPT_GALLERY . '_nonce', $_POST) &&
				wp_verify_nonce($_POST[FOOGALLERY_CPT_GALLERY . '_nonce'], plugin_basename($this->_plugin_file))
			) {
				//if we get here, we are dealing with the Gallery custom post type

				//get previous attachments
				//compare previous to current
				//remove link to gallery from all attachments that have been removed
				//add link to gallery for all attachments that have been added
				//do nothing to all attachments that have stayed the same
				//do this all in the gallery class

				$attachments = apply_filters('foogallery_data_attachments', $_POST[FOOGALLERY_META_ATTACHMENTS]);
				update_post_meta($post_id, FOOGALLERY_META_ATTACHMENTS, $attachments);

				do_action('foogallery_after_save_gallery', $post_id, $_POST);
			}
		}


		function render_gallery_media_metabox($post) {
			$gallery = $this->get_gallery($post);

			wp_enqueue_media();

			?>
			<input type="hidden" name="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce" id="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce" value="<?php echo wp_create_nonce( plugin_basename($this->_plugin_file) ); ?>" />
			<input type="hidden" name='foogallery_attachments' id="foogallery_attachments" value="<?php echo $gallery->attachments_meta; ?>" />
			<div>
				<ul class="foogallery-attachments-list">
					<?php
					if ($gallery->has_attachments()) {
						foreach ($gallery->attachments() as $attachment_id) {
                            $attachment = wp_get_attachment_image_src($attachment_id);
                            ?>
					<li class="attachment details" data-attachment-id="<?php echo $id; ?>">
						<div class="attachment-preview type-image">
							<div class="thumbnail">
								<div class="centered">
									<img width="<?php echo $attachment[1]; ?>" height="<?php echo $attachment[2]; ?>" src="<?php echo $attachment[0]; ?>" />
								</div>
							</div>
							<a class="close media-modal-icon" href="#" title="<?php _e('Remove from gallery','foogallery'); ?>"><div class="media-modal-icon"></div></a>
						</div>
						<input type="text" value="" class="describe" data-setting="caption" placeholder="Caption this image…" />
					</li>
					<?php } } ?>
					<li class="add-attachment">
						<a href="#" data-uploader-title="<?php _e( 'Add Images To Gallery', 'foogallery' ); ?>"
						   data-uploader-button-text="<?php _e( 'Add Images', 'foogallery' ); ?>"
						   data-post-id="<?php echo $post->ID; ?>" class="upload_image_button"
						   title="<?php _e( 'Add Images To Gallery', 'foogallery' ); ?>">
							<div class="dashicons dashicons-format-gallery"></div>
							<span><?php _e( 'Add Images', 'foogallery' ); ?></span>
						</a>
					</li>
				</ul>
				<div style="clear: both;"></div>
			</div>
			<textarea style="display: none" id="foogallery-attachment-template">
<li class="attachment details">
	<div class="attachment-preview type-image">
		<div class="thumbnail">
			<div class="centered">
				<img />
			</div>
		</div>
		<a class="close media-modal-icon" href="#" title="<?php _e('Remove from gallery','foogallery'); ?>"><div class="media-modal-icon"></div></a>
	</div>
</li></textarea>
		<?php

		}

        function render_gallery_settings_metabox($post) {
            //gallery settings including:
            //gallery images link to image or attachment page
            //default template to use
            $gallery = $this->get_gallery($post);
            $available_templates = foogallery_get_templates();

            ?>
            <table class="foogallery-metabox-settings">
                <tbody>
                    <tr>
                        <td>
                            <label for="FooGallerySettings_DefaultTemplate">Default Template</label>
                        </td>
                        <td>
                            <select id="FooGallerySettings_DefaultTemplate" name="foogallery[default_template]">
                                <?php
                                foreach($available_templates as $template){
                                    $selected = ($gallery->default_template === $template->name) ? 'selected' : '';
                                    echo "<option {$selected} value=\"{$template->name}\">{$template->name}</option>";
                                }
                                ?>
                            </select>
                            <small><?php _e('The default template that will be used when rendering the gallery. This can be override when the gallery is inserted into a page or post.','foogallery');?></small>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="FooGallerySettings_LinkToImage">Link To Image</label>
                        </td>
                        <td>
                            <label for="FooGallerySettings_LinkToImage">
                                <?php $checked = ($gallery->link_to_image) ? 'checked' : ''; ?>
                                <input id="FooGallerySettings_LinkToImage" type="checkbox" name="foogallery[link_to_image]" value="on" <?php echo $checked ?>/>
                                <small><?php _e('Should the images in the gallery link to the full size images. If not set, then the images will link to the attachment page.','foogallery');?></small>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        function render_gallery_help_metabox($post) {
            $gallery = $this->get_gallery($post);

            if ($gallery->is_published()) {
                ?>
                <p><?php _e('Paste the shortcode', 'foogallery'); ?> <code><?php echo $gallery->shortcode(); ?></code> <?php _e('into a post or page to show the gallery.', 'foogallery'); ?></p>
                <?php
            }
            ?>
            <p><?php _e('Add media to your gallery by clicking the "Add Images" button.','foogallery'); ?></p>
            <p><?php _e('Remove an image from the gallery by hovering over the image and clicking the "x" that will appear.','foogallery'); ?></p>
            <p><?php _e('You can set the featured image for the gallery. The featured image will represent the gallery in an album.','foogallery'); ?></p>
            <?php
        }
    }
}