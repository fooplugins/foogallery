<?php

/*
 * FooGallery Admin Gallery MetaBoxes class
 */

if ( !class_exists( 'FooGallery_Admin_Gallery_MetaBoxes' ) ) {

	class FooGallery_Admin_Gallery_MetaBoxes {

		private $_gallery;

		function __construct() {
			add_action( 'add_meta_boxes', array($this, 'add_meta_boxes_to_gallery') );

			//save extra post data for a gallery
			add_action( 'save_post', array($this, 'save_gallery') );

			//whitelist metaboxes for our gallery postype
			add_filter( 'foogallery_metabox_sanity', array($this, 'whitelist_metaboxes') );

			add_action( 'admin_enqueue_scripts', array( $this, 'include_required_scripts' ) );
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
				__( 'Gallery Shortcode', 'foogallery' ),
				array($this, 'render_gallery_shortcode_metabox'),
				FOOGALLERY_CPT_GALLERY,
				'side',
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


				$attachments = apply_filters( 'foogallery_save_gallery_attachments', explode( ',', $_POST[FOOGALLERY_META_ATTACHMENTS] ) );
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
				   value="<?php echo $gallery->attachment_id_csv(); ?>"/>
			<div>
				<ul class="foogallery-attachments-list">
					<?php
					if ( $gallery->has_attachments() ) {
						foreach ( $gallery->attachments() as $attachment ) {
							$this->render_gallery_item( $attachment );
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

		function render_gallery_item($attachment_post = false) {
			if ( $attachment_post != false ) {
				$attachment_id = $attachment_post->ID;
				$attachment = wp_get_attachment_image_src( $attachment_id );
			} else {
				$attachment_id = '';
				$attachment = '';
			}
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
				<tr class="gallery_template_field gallery_template_field_selector">
					<th>
						<label for="FooGallerySettings_GalleryTemplate"><?php _e('Gallery Template', 'foogallery'); ?></label>
					</th>
					<td>
						<select id="FooGallerySettings_GalleryTemplate" name="<?php echo FOOGALLERY_META_TEMPLATE; ?>">
							<?php
							foreach ( $available_templates as $template ) {
								$selected = ($gallery_template === $template['slug']) ? 'selected' : '';
								$preview_css = isset( $template['preview_css'] ) ? ' data-preview-css="' . $template['preview_css'] . '" ' : '';
								echo "<option {$selected}{$preview_css} value=\"{$template['slug']}\">{$template['name']}</option>";
							}
							?>
						</select>
						<br />
						<small><?php _e( 'The gallery template that will be used when the gallery is output to the frontend.', 'foogallery' ); ?></small>
					</td>
				</tr>
				<?php
				foreach ( $available_templates as $template ) {
					$field_visibility = ($gallery_template !== $template['slug']) ? 'style="display:none"' : '';
					$section          = '';
					foreach ( $template['fields'] as $field ) {
						if ( isset($field['section']) && $field['section'] !== $section ) {
							$section = $field['section'];
							?>
							<tr class="gallery_template_field gallery_template_field-<?php echo $template['slug']; ?>" <?php echo $field_visibility; ?>>
								<td colspan="2"><h4><?php echo $section; ?></h4></td>
							</tr>
						<?php
						}
						?>
					<tr class="gallery_template_field gallery_template_field-<?php echo $template['slug']; ?>" <?php echo $field_visibility; ?>>
						<th>
							<label
								for="FooGallerySettings_<?php echo $template['slug'] . '_' . $field['id']; ?>"><?php echo $field['title']; ?></label>
						</th>
						<td>
							<?php do_action('foogallery_render_gallery_template_field', $field, $gallery, $template ); ?>
						</td>
						</tr><?php
					}
				}
				?>
				</tbody>
			</table>
		<?php
		}

		function render_gallery_shortcode_metabox($post) {
			$gallery = $this->get_gallery( $post );
			$shortcode = $gallery->shortcode();
			?>
			<p>
				<code id="foogallery-copy-shortcode" data-clipboard-text="<?php echo htmlspecialchars( $shortcode ); ?>"
					  title="<?php _e('Click to copy to your clipboard', 'foogallery'); ?>"
					  class="foogallery-shortcode"><?php echo $shortcode; ?></code>
			</p>
			<p>
				<?php _e( 'Paste the above shortcode into a post or page to show the gallery. Simply click the shortcode to copy it to your clipboard.', 'foogallery' ); ?>
			</p>
			<script>
				jQuery(function($) {
					var $el = $('#foogallery-copy-shortcode');
					ZeroClipboard.config({ moviePath: "<?php echo FOOGALLERY_URL; ?>lib/zeroclipboard/ZeroClipboard.swf" });
					var client = new ZeroClipboard($el);

					client.on( "load", function(client) {
						client.on( "complete", function(client, args) {
							$('.foogallery-shortcode-message').remove();
							$el.after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>');
						} );
					} );
				});
			</script>
			<?php
		}

		function include_required_scripts() {

			//zeroclipboard needed for copy to clipboard functionality
			$url = FOOGALLERY_URL . 'lib/zeroclipboard/ZeroClipboard.min.js';
			wp_enqueue_script( 'foogallery-zeroclipboard', $url, array('jquery'), FOOGALLERY_VERSION );

			//include any admin js required for the templates
			foreach ( foogallery_gallery_templates() as $template ) {
				$admin_js = foo_safe_get( $template, 'admin_js' );
				if ( $admin_js ) {
					wp_enqueue_script( 'foogallery-gallery-admin-' . $template['slug'], $admin_js, array('jquery'), FOOGALLERY_VERSION );
				}
			}
		}
	}
}
