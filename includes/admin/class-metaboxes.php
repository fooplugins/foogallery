<?php

/*
 * FooGallery Admin MetaBoxes class
 */

if ( !class_exists( 'FooGallery_Admin_MetaBoxes' ) ) {

	class FooGallery_Admin_MetaBoxes {

		private $_gallery;

		function __construct() {
			add_action( 'add_meta_boxes', array($this, 'add_meta_boxes_to_gallery') );

			//save extra post data for a gallery
			add_action( 'save_post', array(&$this, 'save_gallery') );

			//whitelist metaboxes for our gallery postype
			add_filter( 'foogallery_metabox_sanity', array($this, 'whitelist_metaboxes') );
		}

		function whitelist_metaboxes() {
			return array(
				FOOGALLERY_CPT_GALLERY => array(
					'whitelist'  => array('submitdiv', 'slugdiv', 'postimagediv', 'foogallery_items', 'foogallery_settings', 'foogallery_help'),
					'contexts'   => array('normal', 'advanced', 'side'),
					'priorities' => array('high', 'core', 'default', 'low')
				)
			);
		}

		function add_meta_boxes_to_gallery() {
			add_meta_box(
				'foogallery_items',
				__( 'Gallery Items', 'foogallery' ),
				array($this, 'render_gallery_media_metabox'),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);

			add_meta_box(
				'foogallery_settings',
				__( 'Gallery Settings', 'foogallery' ),
				array($this, 'render_gallery_settings_metabox'),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);

			add_meta_box(
				'foogallery_help',
				__( 'Gallery Help', 'foogallery' ),
				array($this, 'render_gallery_help_metabox'),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);
		}

		function get_gallery($post) {
			if ( !isset($this->_gallery) ) {
				$this->_gallery = FooGallery::get( $post );
			}

			return $this->_gallery;
		}

		function save_gallery($post_id) {
			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// verify nonce
			if ( array_key_exists( FOOGALLERY_CPT_GALLERY . '_nonce', $_POST ) &&
				wp_verify_nonce( $_POST[FOOGALLERY_CPT_GALLERY . '_nonce'], plugin_basename( FOOGALLERY_FILE ) )
			) {
				//if we get here, we are dealing with the Gallery custom post type

				//get previous attachments
				//compare previous to current
				//remove link to gallery from all attachments that have been removed
				//add link to gallery for all attachments that have been added
				//do nothing to all attachments that have stayed the same
				//do this all in the gallery class

				$attachments = apply_filters( 'foogallery_save_gallery_attachments', $_POST[FOOGALLERY_META_ATTACHMENTS] );
				update_post_meta( $post_id, FOOGALLERY_META_ATTACHMENTS, $attachments );

				$settings = isset($_POST[FOOGALLERY_META_SETTINGS]) ?
					$_POST[FOOGALLERY_META_SETTINGS] : array();

				$settings = apply_filters( 'foogallery_save_gallery_settings', $settings );

				update_post_meta( $post_id, FOOGALLERY_META_TEMPLATE, $_POST[FOOGALLERY_META_TEMPLATE] );

				update_post_meta( $post_id, FOOGALLERY_META_SETTINGS, $settings );

				do_action( 'foogallery_after_save_gallery', $post_id, $_POST );
			}
		}

		function render_gallery_media_metabox($post) {
			$gallery = $this->get_gallery( $post );

			wp_enqueue_media();

			?>
			<input type="hidden" name="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce"
				   id="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce"
				   value="<?php echo wp_create_nonce( plugin_basename( FOOGALLERY_FILE ) ); ?>"/>
			<input type="hidden" name='foogallery_attachments' id="foogallery_attachments"
				   value="<?php echo $gallery->attachments_meta; ?>"/>
			<div>
				<ul class="foogallery-attachments-list">
					<?php
					if ( $gallery->has_attachments() ) {
						foreach ( $gallery->attachments() as $attachment_id ) {
							$attachment = wp_get_attachment_image_src( $attachment_id );
							$this->render_gallery_item( $attachment_id, $attachment );
						}
					} ?>
					<li class="add-attachment">
						<a href="#" data-uploader-title="<?php _e( 'Add Media To Gallery', 'foogallery' ); ?>"
						   data-uploader-button-text="<?php _e( 'Add Media', 'foogallery' ); ?>"
						   data-post-id="<?php echo $post->ID; ?>" class="upload_image_button"
						   title="<?php _e( 'Add Media To Gallery', 'foogallery' ); ?>">
							<div class="dashicons dashicons-format-gallery"></div>
							<span><?php _e( 'Add Media', 'foogallery' ); ?></span>
						</a>
					</li>
				</ul>
				<div style="clear: both;"></div>
			</div>
			<textarea style="display: none" id="foogallery-attachment-template">
				<?php $this->render_gallery_item(); ?>
			</textarea>
		<?php

		}

		function render_gallery_item($attachment_id = '', $attachment = array()) {
			$data_attribute = empty($attachment_id) ? '' : "data-attachment-id=\"{$attachment_id}\"";
			$img_tag        = empty($attachment) ? '<img />' : "<img width=\"{$attachment[1]}\" height=\"{$attachment[2]}\" src=\"{$attachment[0]}\" />";
			?>
			<li class="attachment details" <?php echo $data_attribute; ?>>
				<div class="attachment-preview type-image">
					<div class="thumbnail">
						<div class="centered">
							<?php echo $img_tag; ?>
						</div>
					</div>
					<a class="info" href="#" title="<?php _e( 'Edit Info', 'foogallery' ); ?>">
						<span class="dashicons dashicons-info"></span>
					</a>
					<a class="remove" href="#" title="<?php _e( 'Remove from gallery', 'foogallery' ); ?>">
						<span class="dashicons dashicons-dismiss"></span>
					</a>
				</div>
				<!--				<input type="text" value="" class="describe" data-setting="caption" placeholder="Caption this image…" />-->
			</li>
		<?php
		}

		function render_gallery_settings_metabox($post) {
			//gallery settings including:
			//gallery images link to image or attachment page
			//default template to use
			$gallery             = $this->get_gallery( $post );
			$available_templates = foogallery_gallery_templates();
			$gallery_template    = foogallery_default_gallery_template();
			if ( !empty($gallery->gallery_template) ) {
				$gallery_template = $gallery->gallery_template;
			}
			?>
			<table class="foogallery-metabox-settings">
				<tbody>
				<tr>
					<td>
						<label for="FooGallerySettings_GalleryTemplate">Gallery Template</label>
					</td>
					<td>
						<select id="FooGallerySettings_GalleryTemplate" name="<?php echo FOOGALLERY_META_TEMPLATE; ?>">
							<?php
							foreach ( $available_templates as $template ) {
								$selected = ($gallery_template === $template['key']) ? 'selected' : '';
								echo "<option {$selected} value=\"{$template['key']}\">{$template['name']}</option>";
							}
							?>
						</select>
						<small><?php _e( 'The gallery template that will be used when the gallery is output to the frontend.', 'foogallery' ); ?></small>
					</td>
				</tr>
				<?php
				foreach ( $available_templates as $template ) {
					$field_visibility = ($gallery_template !== $template['key']) ? 'style="display:none"' : '';
					$section          = '';
					foreach ( $template['fields'] as $field ) {
						if ( isset($field['section']) && $field['section'] !== $section ) {
							$section = $field['section'];
							?>
							<tr class="gallery_template_field gallery_template_field-<?php echo $template['key']; ?>" <?php echo $field_visibility; ?>>
								<td colspan="2"><h4><?php echo $section; ?></h4></td>
							</tr>
						<?php
						}
						?>
					<tr class="gallery_template_field gallery_template_field-<?php echo $template['key']; ?>" <?php echo $field_visibility; ?>>
						<td>
							<label
								for="FooGallerySettings_<?php echo $template['key'] . '_' . $field['id']; ?>"><?php echo $field['title']; ?></label>
						</td>
						<td>
							<?php $this->render_gallery_template_field( $field, $gallery, $template ); ?>
						</td>
						</tr><?php
					}
				}
				?>
				</tbody>
			</table>
		<?php
		}

		function render_gallery_help_metabox($post) {
			$gallery = $this->get_gallery( $post );

			if ( $gallery->is_published() ) {
				?>
				<p><?php _e( 'Paste the shortcode', 'foogallery' ); ?>
					<code><?php echo $gallery->shortcode(); ?></code> <?php _e( 'into a post or page to show the gallery.', 'foogallery' ); ?>
				</p>
			<?php
			}
			?>
			<p><?php _e( 'Add media to your gallery by clicking the "Add Media" button.', 'foogallery' ); ?></p>
			<p><?php _e( 'Remove an item from the gallery by hovering over the image and clicking the "x" icon that appears.', 'foogallery' ); ?></p>
		<?php
		}

		/**
		 * @param array $field
		 * @param       $gallery FooGallery
		 */
		function render_gallery_template_field($field = array(), $gallery, $template) {
			$template_key = $template['key'];

			//only declare up front so no debug warnings are shown
			$type = $id = $desc = $default = $placeholder = $choices = $class = $section = null;

			extract( $field );

			$id = $template_key . '_' . $id;

			$field_value = $gallery->get_meta( $id, $default );

			$field_class = empty($class) ? '' : ' class="' . $class . '"';

			$choices = apply_filters( 'foogallery_render_gallery_template_field_choices', $choices, $field, $gallery );

			//allow for customization
			do_action( 'foogallery_render_gallery_template_field_before', $field, $gallery );

			switch ( $type ) {

				case 'html':
					echo $desc;
					break;

				case 'checkbox':
					if ( isset($gallery->settings[$id]) && $gallery->settings[$id] == 'on' ) {
						$field_value = 'on';
					} else if ( !isset($gallery->settings) && $default == 'on' ) {
						$field_value = 'on';
					} else {
						$field_value = '';
					}

					$checked = 'on' === $field_value ? ' checked="checked"' : '';
					echo '<input' . $field_class . ' type="checkbox" id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" value="on"' . $checked . ' /> <small>' . $desc . '</small>';
					break;

				case 'select':
					echo '<select' . $field_class . ' id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']">';
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $field_value == $value ) {
							$selected = ' selected="selected"';
						}
						echo '<option ' . $selected . ' value="' . $value . '">' . $label . '</option>';
					}

					echo '</select>';
					break;

				case 'radio':
					$i = 0;
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $field_value == $value ) {
							$selected = ' checked="checked"';
						}
						echo '<input' . $field_class . $selected . ' type="radio" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']"  id="FooGallerySettings_' . $id . $i . '" value="' . $value . '"> <label for="FooGallerySettings_' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;

				case 'textarea':
					echo '<textarea' . $field_class . ' id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" placeholder="' . $placeholder . '">' . esc_attr( $field_value ) . '</textarea>';

					break;

				case 'text':
					echo '<input class="regular-text ' . $class . '" type="text" id="FooGallerySettings_' . $id . '" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . ']" placeholder="' . $placeholder . '" value="' . esc_attr( $field_value ) . '" />';

					break;

				case 'checkboxlist':
					$i = 0;
					foreach ( $choices as $value => $label ) {

						$checked = '';
						if ( isset($field_value[$value]) && $field_value[$value] == 'true' ) {
							$checked = 'checked="checked"';
						}

						echo '<input' . $field_class . ' ' . $checked . ' type="checkbox" name="' . FOOGALLERY_META_SETTINGS . '[' . $id . '|' . $value . ']" id="FooGallerySettings_' . $id . $i . '" value="on"> <label for="FooGallerySettings_' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;

				default:
					do_action( 'foogallery_render_gallery_template_field_custom', $field, $gallery );
					break;
			}

			do_action( 'foogallery_render_gallery_template_field_after', $field, $gallery );
		}
	}
}
