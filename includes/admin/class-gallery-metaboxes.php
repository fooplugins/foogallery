<?php

/*
 * FooGallery Admin Gallery MetaBoxes class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBoxes' ) ) {

	class FooGallery_Admin_Gallery_MetaBoxes {

		private $_gallery;

		public function __construct() {
			//add our foogallery metaboxes
			add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_meta_boxes_to_gallery' ) );

			//save extra post data for a gallery
			add_action( 'save_post', array( $this, 'save_gallery' ) );

			//save custom field on a page or post
			add_action( 'save_post', array( $this, 'attach_gallery_to_post' ), 10, 2 );

			//whitelist metaboxes for our gallery postype
			add_filter( 'foogallery_metabox_sanity', array( $this, 'whitelist_metaboxes' ) );

			//add scripts used by metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'include_required_scripts' ) );

			// Ajax calls for creating a page for the gallery
			add_action( 'wp_ajax_foogallery_create_gallery_page', array( $this, 'ajax_create_gallery_page' ) );

			// Ajax call for clearing thumb cache for the gallery
			add_action( 'wp_ajax_foogallery_clear_gallery_thumb_cache', array( $this, 'ajax_clear_gallery_thumb_cache' ) );
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
							'foogallery_sorting',
							'foogallery_thumb_settings',
							'foogallery_retina'
						) ),
					'contexts'   => array( 'normal', 'advanced', 'side', ),
					'priorities' => array( 'high', 'core', 'default', 'low', ),
				)
			);
		}

		public function add_meta_boxes_to_gallery( $post ) {

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
					'high'
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
				'foogallery_retina',
				__( 'Retina Support', 'foogallery' ),
				array( $this, 'render_retina_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'side',
				'default'
			);

			add_meta_box(
				'foogallery_sorting',
				__( 'Gallery Sorting', 'foogallery' ),
				array( $this, 'render_sorting_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'side',
				'default'
			);

			add_meta_box(
				'foogallery_thumb_settings',
				__( 'Thumbnails', 'foogallery' ),
				array( $this, 'render_thumb_settings_metabox' ),
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
				do_action( 'foogallery_before_save_gallery', $post_id, $_POST );

				if ( isset( $_POST[FOOGALLERY_META_TEMPLATE] ) ) {
					$gallery_template = $_POST[FOOGALLERY_META_TEMPLATE];
					update_post_meta( $post_id, FOOGALLERY_META_TEMPLATE, $gallery_template );
				} else {
					$gallery_template = foogallery_default_gallery_template();
				}

				if ( isset( $_POST[FOOGALLERY_META_SETTINGS] ) ) {
					$settings = isset( $_POST[FOOGALLERY_META_SETTINGS] ) ?
						$_POST[FOOGALLERY_META_SETTINGS] : array();
				} else {
					$settings = array();
				}

				$settings = apply_filters( 'foogallery_save_gallery_settings', $settings, $post_id, $_POST );
				$settings = apply_filters( 'foogallery_save_gallery_settings-'. $gallery_template, $settings, $post_id, $_POST );

				update_post_meta( $post_id, FOOGALLERY_META_SETTINGS, $settings );

				if ( isset( $_POST[FOOGALLERY_META_SORT] ) ) {
					update_post_meta( $post_id, FOOGALLERY_META_SORT, $_POST[FOOGALLERY_META_SORT] );
				}

				$custom_css = isset($_POST[FOOGALLERY_META_CUSTOM_CSS]) ?
					$_POST[FOOGALLERY_META_CUSTOM_CSS] : '';

				if ( empty( $custom_css ) ) {
					delete_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS );
				} else {
					update_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS, $custom_css );
				}

				if ( isset( $_POST[FOOGALLERY_META_RETINA] ) ) {
					update_post_meta( $post_id, FOOGALLERY_META_RETINA, $_POST[FOOGALLERY_META_RETINA] );
				} else {
					delete_post_meta( $post_id, FOOGALLERY_META_RETINA );
				}

				if ( isset( $_POST[FOOGALLERY_META_FORCE_ORIGINAL_THUMBS] ) ) {
					update_post_meta( $post_id, FOOGALLERY_META_FORCE_ORIGINAL_THUMBS, $_POST[FOOGALLERY_META_FORCE_ORIGINAL_THUMBS] );
				} else {
					delete_post_meta( $post_id, FOOGALLERY_META_FORCE_ORIGINAL_THUMBS );
				}

				do_action( 'foogallery_after_save_gallery', $post_id, $_POST );
			}
		}

		public function attach_gallery_to_post( $post_id, $post ) {

			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			$allowed_post_types = foogallery_allowed_post_types_for_usage();

			//only do this check for a page or post
			if ( in_array( $post->post_type, $allowed_post_types ) ) {

                do_action( 'foogallery_start_attach_gallery_to_post', $post_id );

				//Clear any foogallery usages that the post might have
				delete_post_meta( $post_id, FOOGALLERY_META_POST_USAGE );

				//get all foogallery shortcodes that are on the page/post
				$gallery_shortcodes = foogallery_extract_gallery_shortcodes( $post->post_content );

                if ( is_array( $gallery_shortcodes ) && count( $gallery_shortcodes ) > 0 ) {

                    foreach ( $gallery_shortcodes as $id => $shortcode ) {
                        //if the content contains the foogallery shortcode then add a custom field
                        add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE, $id, false );

                        do_action( 'foogallery_attach_gallery_to_post', $post_id, $id );
                    }
                }

                do_action( 'foogallery_attach_gallery_to_post', $post_id, $post );
			}
		}

		public function render_gallery_shortcode_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$shortcode = $gallery->shortcode();
			?>
			<p class="foogallery-shortcode">
				<input type="text" id="foogallery_copy_shortcode" size="<?php echo strlen( $shortcode ) + 2; ?>" value="<?php echo htmlspecialchars( $shortcode ); ?>" readonly="readonly" />
			</p>
			<p>
				<?php _e( 'Paste the above shortcode into a post or page to show the gallery.', 'foogallery' ); ?>
			</p>
			<script>
				jQuery(function($) {
					var shortcodeInput = document.querySelector('#foogallery_copy_shortcode');
					shortcodeInput.addEventListener('click', function () {
						try {
							// select the contents
							shortcodeInput.select();
							//copy the selection
							document.execCommand('copy');
							//show the copied message
							$('.foogallery-shortcode-message').remove();
							$(shortcodeInput).after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>');
						} catch(err) {
							console.log('Oops, unable to copy!');
						}
					}, false);
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
					echo '<li>' . $post->post_title . '&nbsp;';
					edit_post_link( __( 'Edit', 'foogallery' ), '<span class="edit">', ' | </span>', $post->ID );
					echo '<span class="view"><a href="' . esc_url( $url ) . '" target="_blank">' . __( 'View', 'foogallery' ) . '</a></li>';
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
			$sorting_options = foogallery_sorting_options();
			if ( empty( $gallery->sorting ) ) {
				$gallery->sorting = '';
			}
			?>
			<p>
				<?php _e('Change the way images are sorted within your gallery. By default, they are sorted in the order you see them.', 'foogallery'); ?>
			</p>
			<?php
			foreach ( $sorting_options as $sorting_key => $sorting_label ) { ?>
				<p>
				<input type="radio" value="<?php echo $sorting_key; ?>" <?php checked( $sorting_key === $gallery->sorting ); ?> id="FooGallerySettings_GallerySort_<?php echo $sorting_key; ?>" name="<?php echo FOOGALLERY_META_SORT; ?>" />
				<label for="FooGallerySettings_GallerySort_<?php echo $sorting_key; ?>"><?php echo $sorting_label; ?></label>
				</p><?php
			} ?>
			<p class="foogallery-help">
				<?php _e('PLEASE NOTE : sorting randomly will force HTML Caching for the gallery to be disabled.', 'foogallery'); ?>
			</p>
			<?php
		}

		public function render_retina_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$retina_options = foogallery_retina_options();
			if ( empty( $gallery->retina ) ) {
				$gallery->retina = foogallery_get_setting( 'default_retina_support', array() );
			}
			?>
			<p>
				<?php _e('Add retina support to this gallery by choosing the different pixel densities you want to enable.', 'foogallery'); ?>
			</p>
			<?php
			foreach ( $retina_options as $retina_key => $retina_label ) {
				$checked = array_key_exists( $retina_key, $gallery->retina ) ? ('true' === $gallery->retina[$retina_key]) : false;
				?>
				<p>
				<input type="checkbox" value="true" <?php checked( $checked ); ?> id="FooGallerySettings_Retina_<?php echo $retina_key; ?>" name="<?php echo FOOGALLERY_META_RETINA; ?>[<?php echo $retina_key; ?>]" />
				<label for="FooGallerySettings_Retina_<?php echo $retina_key; ?>"><?php echo $retina_label; ?></label>
				</p><?php
			} ?>
			<p class="foogallery-help">
				<?php _e('PLEASE NOTE : thumbnails will be generated for each of the pixel densities chosen, which will increase your website\'s storage space!', 'foogallery'); ?>
			</p>
			<?php
		}

		public function render_thumb_settings_metabox( $post ) {
			$gallery = $this->get_gallery( $post );
			$force_use_original_thumbs = get_post_meta( $post->ID, FOOGALLERY_META_FORCE_ORIGINAL_THUMBS, true );
			$checked = 'true' === $force_use_original_thumbs; ?>
			<p>
				<?php _e( 'Clear all the previously cached thumbnails that have been generated for this gallery.', 'foogallery' ); ?>
			</p>
			<div class="foogallery_metabox_actions">
				<button class="button button-primary button-large" id="foogallery_clear_thumb_cache"><?php _e( 'Clear Thumbnail Cache', 'foogallery' ); ?></button>
				<span id="foogallery_clear_thumb_cache_spinner" class="spinner"></span>
				<?php wp_nonce_field( 'foogallery_clear_gallery_thumb_cache', 'foogallery_clear_gallery_thumb_cache_nonce', false ); ?>
			</div>
			<p>
				<input type="checkbox" value="true" <?php checked( $checked ); ?> id="FooGallerySettings_ForceOriginalThumbs" name="<?php echo FOOGALLERY_META_FORCE_ORIGINAL_THUMBS; ?>" />
				<label for="FooGallerySettings_ForceOriginalThumbs"><?php _e('Force Original Thumbs', 'foogallery'); ?></label>
			</p>
			<?php
		}

		public function include_required_scripts() {
			$screen_id = foo_current_screen_id();

			//only include scripts if we on the foogallery add/edit page
			if ( FOOGALLERY_CPT_GALLERY === $screen_id ||
			     'edit-' . FOOGALLERY_CPT_GALLERY === $screen_id ) {

				//include any admin js required for the templates
				foreach ( foogallery_gallery_templates() as $template ) {
					$admin_js = foo_safe_get( $template, 'admin_js' );
					if ( is_array( $admin_js ) ) {
						//dealing with an array of js files to include
						foreach( $admin_js as $admin_js_key => $admin_js_src ) {
							wp_enqueue_script( 'foogallery-gallery-admin-' . $template['slug'] . '-' . $admin_js_key, $admin_js_src, array('jquery', 'media-upload', 'jquery-ui-sortable'), FOOGALLERY_VERSION );
						}
					} else {
						//dealing with a single js file to include
						wp_enqueue_script( 'foogallery-gallery-admin-' . $template['slug'], $admin_js, array('jquery', 'media-upload', 'jquery-ui-sortable'), FOOGALLERY_VERSION );
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

				$content = apply_filters( 'foogallery_create_gallery_page_content', $foogallery->shortcode(), $foogallery );

				$post = array(
					'post_content' => $content,
					'post_title'   => $foogallery->name,
					'post_status'  => 'draft',
					'post_type'    => 'page',
				);

				wp_insert_post( $post );
			}
			die();
		}

		public function ajax_clear_gallery_thumb_cache() {
			if ( check_admin_referer( 'foogallery_clear_gallery_thumb_cache', 'foogallery_clear_gallery_thumb_cache_nonce' ) ) {

				$foogallery_id = $_POST['foogallery_id'];

				$foogallery = FooGallery::get_by_id( $foogallery_id );

				ob_start();

				//loop through all images, get the full sized file
				foreach ( $foogallery->attachments() as $attachment ) {
					$meta_data = wp_get_attachment_metadata( $attachment->ID );

					$file = $meta_data['file'];

					wpthumb_delete_cache_for_file( $file );
				}

				ob_end_clean();

				echo __( 'The thumbnail cache has been cleared!', 'foogallery' );
			}

			die();
		}
	}
}
