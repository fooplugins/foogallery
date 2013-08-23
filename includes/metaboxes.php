<?php

/*
 * FooGallery MetaBoxes
 */

if (!class_exists('FooGallery_MetaBoxes')) {

    class FooGallery_MetaBoxes {

		private $_plugin_file;

		function __construct($plugin_file) {

			$this->_plugin_file = $plugin_file;

			add_action('add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array($this, 'add_meta_boxes_to_gallery'));

			//save extra post data
			add_action('save_post', array(&$this, 'save_gallery'));
		}

		function add_meta_boxes_to_gallery() {
			add_meta_box(
				'gallery_images',
				__('Gallery Media', 'foogallery'),
				array($this, 'render_gallery_media_metabox'),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);
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
			$gallery = FooGallery_Gallery::get($post);

			//wp_enqueue_script('jquery-ui');

			wp_enqueue_media();

			?>
			<input type="hidden" name="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce" id="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce" value="<?php echo wp_create_nonce( plugin_basename($this->_plugin_file) ); ?>" />
			<input type="hidden" name='foogallery_attachments' id="foogallery_attachments" value="<?php echo $gallery->attachments_meta; ?>" />
			<style type="text/css">
				.foogallery-attachments-list .add-attachment {
					background: #ddd;
					box-shadow: 0 0 0 1px #ccc;
					width: 150px;
					position: relative;
					float: left;
					padding: 0;
					margin: 0 10px 20px;
					color: #464646;
					list-style: none;
					text-align: center;
					-webkit-user-select: none;
					-moz-user-select: none;
					-ms-user-select: none;
					-o-user-select: none;
					user-select: none;
				}

					.foogallery-attachments-list .add-attachment a {
						display: table-cell;
						vertical-align: middle;
						height: 150px;
						text-align: center;
						width: 150px;
						height: 100px;
						padding-top:50px;
						background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAadEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjEwMPRyoQAAAvxJREFUeF7t2kFqG0EQhWGvcokcIOCzBXKDnCcQsK4QCGQbyBkCWQUCXmWV1DPuoqbm13TPSG6pPVp8wpRelbtaIraJ7g6Hw65hcU+wuCdY3BMs7gkW9wSLe4LFPSlfvDWfzE/z75XTjtpVOz9dwDvz21D4NdPO97qAz6G4Nw+6gD+puCePugB6YjduF0DFPbldABUTZUZGO7lqwOSBo6GdXDVg8sDR0E6uGjB54GhoJ1cNmDyweGPemy/m7zN9rZqeo55LoJ1cNWDyQNEfEt8N5UXPPf2xcQXofK4aMHmgXt0fhrKRMtfwTqCzuWrA5IF6i1OOKJv7e6NzuWrA5IFfDeWIsrm/NzqXqwZMHqh/7ChHlM39vdG5XDVg8sA1F/Bocn9vdC5XDZg88JuhHFE29/dG53LVgMkDPxjKEWVz/yk+PqPnjqFzuWrA5IGX+jGoxcvsNZcQzzRTDZg8UPRLztIl6Llz/iIUly9aLyH3TVQDJg8s9OrqLa5ff0tWP/ZUe6lXPmu5BOpz1YDJA3taWr6oXQL1uGrA5IG9tCxfLF0C5V01YPLAHtYsXxy7BMq6asDkgS9ty/IFXQLlXDVg8sAlOsCxV6JFy/LUt8Zk3qwA8oBj4uG3XELL8kK9a0zmzQogDyB0+DWX0Lq8UP8ak3mzAsgDsqXDt1zCmuWFZqwxmTcrgDwgajn80iWsXV7yDMpEi/ktA4o1h6dL2LK85DmUiRbzWwbIlsPHS9i6vMRzCGWixfyWAaccXr2n9Es+D2WixfzaAacevkX8fi1oRkQ9Tg/UFJVwj+XFD9eIZkTU4/RATZEyvZaXyQEb0IyIepweqCnqubzMDllBMyLqcXqgpkvKh6TM2dwugIoXdruAhDJno29wbR+U7H4BD7l4Yd0v4N5c04elu1+A6D8x9KHpXypeWFxeKHM2+ZuNCBcLqMdhcTC0dEQ9DouDoaUj6nFYHAwtHVGPw+JgaOmIehwWB0NLR9TjsDgYWjqiHofFwdDSEfU8O9z9B0Xsl/ttqhw+AAAAAElFTkSuQmCC) no-repeat center 25%;
						color:#888;
						font-weight: bold;
						text-decoration: none;
						opacity: 0.5;
					}

						.foogallery-attachments-list .add-attachment a:hover {
							opacity: 1;
						}

				.foogallery-attachments-list .attachment-preview,
				.foogallery-attachments-list .attachment-preview .thumbnail {
					width: 150px;
					height: 150px;
					cursor:move;
				}

				.foogallery-attachments-list .attachment.placeholder {
					width: 150px;
					height: 150px;
					border: #1e8cbe 1px dashed;
					background: #eee;
				}

				.foogallery-attachments-list .attachment {
					border: transparent 1px solid;
					box-shadow: none;
				}

				.foogallery-attachments-list .attachment.ui-sortable-helper {
					opacity: 0.5;
				}

					.foogallery-attachments-list .attachment.ui-sortable-helper:hover .close {
						display: none;
					}
			</style>
			<div>
				<ul class="foogallery-attachments-list">
					<?php
					if ($gallery->has_attachments()) {
						foreach ($gallery->attachments() as $id=>$attachment) {	?>
					<li class="attachment details" data-attachment-id="<?php echo $id; ?>">
						<div class="attachment-preview type-image">
							<div class="thumbnail">
								<div class="centered">
									<img width="<?php echo $attachment[1]; ?>" height="<?php echo $attachment[2]; ?>" src="<?php echo $attachment[0]; ?>" />
								</div>
							</div>
							<a class="close media-modal-icon" href="#" title="<?php _e('Remove from gallery','foogallery'); ?>"><div class="media-modal-icon"></div></a>
						</div>
					</li>
					<?php } } ?>
					<li class="add-attachment">
						<a href="#" data-uploader-title="<?php _e( 'Add Images To Gallery', 'foogallery' ); ?>"
						   data-uploader-button-text="<?php _e( 'Add Images', 'foogallery' ); ?>"
						   data-post-id="<?php echo $post->ID; ?>" class="upload_image_button"
						   title="<?php _e( 'Add Images To Gallery', 'foogallery' ); ?>"><?php _e( 'Add Images', 'foogallery' ); ?></a>
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
    }
}