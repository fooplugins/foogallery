<?php

/*
 * FooGallery Admin Gallery MetaBoxes class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBoxes' ) ) {

	class FooGallery_Admin_Gallery_MetaBoxes {

		private $_gallery;

		public function __construct() {
			//add our foogallery metaboxes
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_to_gallery' ) );

			//save extra post data for a gallery
			add_action( 'save_post', array( $this, 'save_gallery' ) );

			//save custom field on a page or post
			add_Action( 'save_post', array( $this, 'attach_gallery_to_post' ), 10, 2 );

			//whitelist metaboxes for our gallery postype
			add_filter( 'foogallery_metabox_sanity', array( $this, 'whitelist_metaboxes' ) );

			//add scripts used by metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'include_required_scripts' ) );

			// Ajax calls for creating a page for the gallery
			add_action( 'wp_ajax_foogallery_create_gallery_page', array( $this, 'ajax_create_gallery_page' ) );
		}

		public function whitelist_metaboxes() {
			return array(
				FOOGALLERY_CPT_GALLERY => array(
					'whitelist'  => apply_filters( 'foogallery_metabox_sanity_foogallery',
						array(
							'submitdiv',
							'slugdiv',
							'postimagediv',
							'foogallery_items',
							'foogallery_settings',
							'foogallery_help',
							'foogallery_pages',
							'foogallery_customcss',
							'foogallery_sorting'
						) ),
					'contexts'   => array( 'normal', 'advanced', 'side', ),
					'priorities' => array( 'high', 'core', 'default', 'low', ),
				)
			);
		}

		public function add_meta_boxes_to_gallery() {
			global $post;

			add_meta_box(
				'foogallery_items',
				__( 'Gallery Items', 'foogallery' ),
				array( $this, 'render_gallery_media_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);

			add_meta_box(
				'foogallery_settings',
				__( 'Gallery Settings', 'foogallery' ),
				array( $this, 'render_gallery_settings_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);

			add_meta_box(
				'foogallery_help',
				__( 'Gallery Shortcode', 'foogallery' ),
				array( $this, 'render_gallery_shortcode_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'side',
				'default'
			);

			if ( 'publish' == $post->post_status ) {
				add_meta_box( 'foogallery_pages',
					__( 'Gallery Usage', 'foogallery' ),
					array( $this, 'render_gallery_usage_metabox' ),
					FOOGALLERY_CPT_GALLERY,
					'side',
					'default'
				);
			}

			add_meta_box(
				'foogallery_customcss',
				__( 'Custom CSS', 'foogallery' ),
				array( $this, 'render_customcss_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'low'
			);

			add_meta_box(
				'foogallery_sorting',
				__( 'Gallery Sorting', 'foogallery' ),
				array( $this, 'render_sorting_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'side',
				'default'
			);
		}

		public function get_gallery( $post ) {
			if ( ! isset($this->_gallery) ) {
				$this->_gallery = FooGallery::get( $post );

				//attempt to load default gallery settings from another gallery, as per FooGallery settings page
				$this->_gallery->load_default_settings_if_new();
			}

			return $this->_gallery;
		}

		public function save_gallery( $post_id ) {
			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// verify nonce
			if ( array_key_exists( FOOGALLERY_CPT_GALLERY . '_nonce', $_POST ) &&
				wp_verify_nonce( $_POST[FOOGALLERY_CPT_GALLERY . '_nonce'], plugin_basename( FOOGALLERY_FILE ) )
			) {
				//if we get here, we are dealing with the Gallery custom post type

				$attachments = apply_filters( 'foogallery_save_gallery_attachments', explode( ',', $_POST[FOOGALLERY_META_ATTACHMENTS] ) );
				update_post_meta( $post_id, FOOGALLERY_META_ATTACHMENTS, $attachments );

				$settings = isset($_POST[FOOGALLERY_META_SETTINGS]) ?
					$_POST[FOOGALLERY_META_SETTINGS] : array();

				$settings = apply_filters( 'foogallery_save_gallery_settings', $settings );

				update_post_meta( $post_id, FOOGALLERY_META_TEMPLATE, $_POST[FOOGALLERY_META_TEMPLATE] );

				update_post_meta( $post_id, FOOGALLERY_META_SETTINGS, $settings );

				update_post_meta( $post_id, FOOGALLERY_META_SORT, $_POST[FOOGALLERY_META_SORT] );

				$custom_css = isset($_POST[FOOGALLERY_META_CUSTOM_CSS]) ?
					$_POST[FOOGALLERY_META_CUSTOM_CSS] : '';

				if ( empty( $custom_css ) ) {
					delete_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS );
				} else {
					update_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS, $custom_css );
				}

				do_action( 'foogallery_after_save_gallery', $post_id, $_POST );
			}
		}

		public function attach_gallery_to_post( $post_id, $post ) {

			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			//only do this check for a page or post
			if ( 'post' == $post->post_type ||
				'page' == $post->post_type ) {

				//first, clear any foogallery usages that the post might have
				delete_post_meta( $post_id, FOOGALLERY_META_POST_USAGE );

				//if the content contains the foogallery shortcode then add a custom field
				$gallery_shortcodes = foogallery_extract_gallery_shortcodes( $post->post_content );

				foreach ( $gallery_shortcodes as $id => $shortcode ) {
					add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE, $id, false );
				}
			}
		}

		public function render_gallery_media_metabox( $post ) {
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

		public function render_gallery_item( $attachment_post = false ) {
			if ( $attachment_post != false ) {
				$attachment_id = $attachment_post->ID;
				$attachment = wp_get_attachment_image_src( $attachment_id );
			} else {
				$attachment_id = '';
				$attachment = '';
			}
			$data_attribute = empty($attachment_id) ? '' : "data-attachment-id=\"{$attachment_id}\"";
			$img_tag        = empty($attachment) ? '<img width="150" height="150" />' : "<img width=\"150\" height=\"150\" src=\"{$attachment[0]}\" />";
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

		public function render_gallery_settings_metabox( $post ) {
			//gallery settings including:
			//gallery images link to image or attachment page
			//default template to use
			$gallery             = $this->get_gallery( $post );
			$available_templates = foogallery_gallery_templates();
			$gallery_template    = foogallery_default_gallery_template();
			if ( ! empty($gallery->gallery_template) ) {
				$gallery_template = $gallery->gallery_template;
			}
			$hide_help = 'on' == foogallery_get_setting( 'hide_gallery_template_help' );
			?>
			<table class="foogallery-metabox-settings">
				<tbody>
				<tr class="gallery_template_field gallery_template_field_selector">
					<th>
						<label for="FooGallerySettings_GalleryTemplate"><?php _e( 'Gallery Template', 'foogallery' ); ?></label>
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

						//allow for the field to be altered by extensions. Also used by the build-in fields, e.g. lightbox
						$field = apply_filters( 'foogallery_alter_gallery_template_field', $field, $gallery );

						$class = "gallery_template_field gallery_template_field-{$template['slug']} gallery_template_field-{$template['slug']}-{$field['id']}";

						if ( isset($field['section']) && $field['section'] !== $section ) {
							$section = $field['section'];
							?>
							<tr class="<?php echo $class; ?>" <?php echo $field_visibility; ?>>
								<td colspan="2"><h4><?php echo $section; ?></h4></td>
							</tr>
						<?php }
						if (isset($field['type']) && 'help' == $field['type'] && $hide_help) {
							continue; //skip help if the 'hide help' setting is turned on
						}
						?>
						<tr class="<?php echo $class; ?>" <?php echo $field_visibility; ?>>
							<?php if ( isset($field['type']) && 'help' == $field['type'] ) { ?>
							<td colspan="2">
								<div class="foogallery-help">
									<?php echo $field['desc']; ?>
								</div>
							</td>
							<?php } else { ?>
							<th>
								<label
									for="FooGallerySettings_<?php echo $template['slug'] . '_' . $field['id']; ?>"><?php echo $field['title']; ?></label>
							</th>
							<td>
								<?php do_action( 'foogallery_render_gallery_template_field', $field, $gallery, $template ); ?>
							</td>
							<?php } ?>
						</tr>
					<?php
					}
				}
				?>
				</tbody>
			</table>
		<?php
		}

		public function render_gallery_shortcode_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$shortcode = $gallery->shortcode();
			?>
			<p class="foogallery-shortcode">
				<code id="foogallery-copy-shortcode" data-clipboard-text="<?php echo htmlspecialchars( $shortcode ); ?>"
					  title="<?php _e( 'Click to copy to your clipboard', 'foogallery' ); ?>"><?php echo $shortcode; ?></code>
			</p>
			<p>
				<?php _e( 'Paste the above shortcode into a post or page to show the gallery. Simply click the shortcode to copy it to your clipboard.', 'foogallery' ); ?>
			</p>
			<script>
				jQuery(function($) {
					var $el = $('#foogallery-copy-shortcode');
					ZeroClipboard.config({ swfPath: "<?php echo FOOGALLERY_URL; ?>lib/zeroclipboard/ZeroClipboard.swf", forceHandCursor: true });
					var client = new ZeroClipboard($el);

					client.on( "ready", function() {
						this.on( "aftercopy", function() {
							$('.foogallery-shortcode-message').remove();
							$el.after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foonav' ); ?></p>');
						} );
					} );

					client.on("error", function(event) {
						alert('error[name="' + event.name + '"]: ' + event.message);
						ZeroClipboard.destroy();
					});
				});
			</script>
			<?php
		}

		public function render_gallery_usage_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$posts = $gallery->find_usages();
			if ( $posts && count( $posts ) > 0 ) { ?>
				<p>
					<?php _e( 'This gallery is used on the following posts or pages:', 'foogallery' ); ?>
				</p>
				<ul class="ul-disc">
				<?php foreach ( $posts as $post ) {
					$url = get_permalink( $post->ID );
					echo '<li>' . $post->post_title . ' <span class="row-actions">';
					edit_post_link( __( 'Edit', 'foogallery' ), '<span class="edit">', ' | </span>', $post->ID );
					echo '<span class="view"><a href="' . $url . '" target="_blank">' . __( 'View', 'foogallery' ) . '</a></span></li>';
				} ?>
				</ul>
			<?php } else { ?>
				<p>
					<?php _e( 'This gallery is not used on any pages or pages yet. Quickly create a page:', 'foogallery' ); ?>
				</p>
				<div class="foogallery_metabox_actions">
					<button class="button button-primary button-large" id="foogallery_create_page"><?php _e( 'Create Gallery Page', 'foogallery' ); ?></button>
					<span id="foogallery_create_page_spinner" class="spinner"></span>
					<?php wp_nonce_field( 'foogallery_create_gallery_page', 'foogallery_create_gallery_page_nonce', false ); ?>
				</div>
				<p>
					<?php _e( 'A draft page will be created which includes the gallery shortcode in the content. The title of the page will be the same title as the gallery.', 'foogallery' ); ?>
				</p>
			<?php }
		}

		public function render_sorting_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$sorting_options = foogallery_sorting_options(); ?>
			<p>
				<?php _e('Change the way images are sorted within your gallery. By default, they are sorted in the order you see them.', 'foogallery'); ?>
			</p>
			<?php
			foreach ( $sorting_options as $sorting_key => $sorting_label ) { ?>
				<p>
				<input type="radio" value="<?php echo $sorting_key; ?>" <?php checked( $sorting_key === $gallery->sorting ); ?> id="FooGallerySettings_GallerySort_<?php echo $sorting_key; ?>" name="<?php echo FOOGALLERY_META_SORT; ?>" />
				<label for="FooGallerySettings_GallerySort_<?php echo $sorting_key; ?>"><?php echo $sorting_label; ?></label>
				</p><?php
			}
		}

		public function include_required_scripts() {
			//only include scripts if we on the foogallery page
			if ( FOOGALLERY_CPT_GALLERY === foo_current_screen_post_type() ) {

				//zeroclipboard needed for copy to clipboard functionality
				$url = FOOGALLERY_URL . 'lib/zeroclipboard/ZeroClipboard.min.js';
				wp_enqueue_script( 'foogallery-zeroclipboard', $url, array('jquery'), FOOGALLERY_VERSION );

				//spectrum needed for the colorpicker field
				$url = FOOGALLERY_URL . 'lib/spectrum/spectrum.js';
				wp_enqueue_script( 'foogallery-spectrum', $url, array('jquery'), FOOGALLERY_VERSION );
				$url = FOOGALLERY_URL . 'lib/spectrum/spectrum.css';
				wp_enqueue_style( 'foogallery-spectrum', $url, array(), FOOGALLERY_VERSION );

				//include any admin js required for the templates
				foreach ( foogallery_gallery_templates() as $template ) {
					$admin_js = foo_safe_get( $template, 'admin_js' );
					if ( $admin_js ) {
						wp_enqueue_script( 'foogallery-gallery-admin-' . $template['slug'], $admin_js, array('jquery'), FOOGALLERY_VERSION );
					}
				}
			}
		}

		public function render_customcss_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$custom_css = $gallery->custom_css;
			$example = '<code>#foogallery-gallery-' . $post->ID . ' { }</code>';
			?>
			<p>
				<?php printf( __( 'Add any custom CSS to target this specific gallery. For example %s', 'foogallery' ), $example ); ?>
			</p>
			<table id="table_styling" class="form-table">
				<tbody>
				<tr>
					<td>
						<textarea class="foogallery_metabox_custom_css" name="<?php echo FOOGALLERY_META_CUSTOM_CSS; ?>" type="text"><?php echo $custom_css; ?></textarea>
					</td>
				</tr>
				</tbody>
			</table>
		<?php
		}

		public function ajax_create_gallery_page() {
			if ( check_admin_referer( 'foogallery_create_gallery_page', 'foogallery_create_gallery_page_nonce' ) ) {

				$foogallery_id = $_POST['foogallery_id'];

				$foogallery = FooGallery::get_by_id( $foogallery_id );

				$post = array(
					'post_content' => $foogallery->shortcode(),
					'post_title'   => $foogallery->name,
					'post_status'  => 'draft',
					'post_type'    => 'page',
				);

				wp_insert_post( $post );
			}
			die();
		}
	}
}
