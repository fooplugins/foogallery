<?php
/**
 * Class to handle adding the Items metabox to a gallery
 */
if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Items' ) ) {

    class FooGallery_Admin_Gallery_MetaBox_Items {

        /**
         * FooGallery_Admin_Gallery_MetaBox_Items constructor.
         */
        function __construct() {
			add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_items_metabox' ), 7 );

            //enqueue assets for the items metabox
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			// Ajax call for generating a gallery preview
			add_action( 'wp_ajax_foogallery_preview', array( $this, 'ajax_gallery_preview' ) );

			//handle previews that have no attachments
			add_action( 'foogallery_template_no_attachments', array( $this, 'preview_no_attachments' ) );
        }

		public function add_items_metabox( $post ) {
			add_meta_box(
				'foogallery_items',
				__( 'Gallery Items', 'foogallery' ) . '<span class="foogallery-gallery-items-metabox-title spinner is-active"></span>',
				array( $this, 'render_gallery_items_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);
		}

		public function render_gallery_items_metabox( $post ) {
			$gallery = foogallery_admin_get_current_gallery( $post );

			//attempt to load default gallery settings from another gallery, as per FooGallery settings page
			$gallery->load_default_settings_if_new();

			//attempt to load default attachments from the settings page
			if ( $gallery->is_new() ) {
				$default_attachments = foogallery_get_setting( 'default_gallery_attachments' );
				if ( !empty($default_attachments) ) {
					$gallery->attachment_ids = explode( ',', $default_attachments );
				}
			}

			$mode = $gallery->get_meta( 'foogallery_items_view', 'manage' );

			if ( empty($mode) || $gallery->is_new() ) {
				$mode = 'manage';
			}
			$has_items = !$gallery->is_empty();

			do_action( 'foogallery_gallery_metabox_items', $gallery );
			?>
			<div class="foogallery-hidden foogallery-items-view-switch-container">
				<div class="foogallery-items-view-switch">
					<a href="#manage" data-value="manage" data-container=".foogallery-items-view-manage" class="<?php echo $mode==='manage' ? 'current' : ''; ?>"><?php esc_html_e('Manage Items', 'foogallery'); ?></a>
					<a href="#preview" data-value="preview" data-container=".foogallery-items-view-preview" class="<?php echo $mode==='preview' ? 'current' : ''; ?>"><?php esc_html_e('Gallery Preview', 'foogallery'); ?></a>
				</div>
				<div class="foogallery-preview-actions">
					<button type="button" class="foogallery-preview-refresh-btn" title="<?php esc_attr_e('Refresh Preview', 'foogallery'); ?>">
						<span class="dashicons dashicons-update"></span>
					</button>
					<span></span>
					<button type="button" class="foogallery-viewport-btn active" data-viewport="desktop" title="<?php esc_attr_e('Desktop View', 'foogallery'); ?>">
						<span class="dashicons dashicons-desktop"></span>
					</button>
					<button type="button" class="foogallery-viewport-btn" data-viewport="tablet" title="<?php esc_attr_e('Tablet View', 'foogallery'); ?>">
						<span class="dashicons dashicons-tablet"></span>
					</button>
					<button type="button" class="foogallery-viewport-btn" data-viewport="mobile" title="<?php esc_attr_e('Mobile View', 'foogallery'); ?>">
						<span class="dashicons dashicons-smartphone"></span>
					</button>
				</div>
				<span id="foogallery_preview_spinner" class="spinner"></span>
				<input type="hidden" id="foogallery_items_view_input" value="<?php echo esc_attr( $mode ); ?>" name="<?php echo esc_attr( FOOGALLERY_META_SETTINGS . '[foogallery_items_view]' ); ?>" /> <!-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -->
			</div>

			<div class="foogallery-items-view foogallery-items-view-manage <?php echo $mode==='manage' ? '' : 'hidden'; ?>">
				<input type="hidden" name="<?php echo esc_attr( FOOGALLERY_CPT_GALLERY ); ?>_nonce" id="<?php echo esc_attr( FOOGALLERY_CPT_GALLERY ); ?>_nonce" value="<?php echo esc_attr( wp_create_nonce( plugin_basename( FOOGALLERY_FILE ) ) ); ?>"/> <!-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -->
				<div class="foogallery-items-list">
					<div class="foogallery-items-empty <?php echo $has_items ? 'hidden' : ''; ?>" style="padding-top:20px; text-align: center">
						<p><?php esc_html_e('Your gallery is currently empty. Add items to see a preview.','foogallery'); ?></p>
					</div>
					<?php do_action( 'foogallery_gallery_metabox_items_list', $gallery ); ?>
				</div>
				<div class="foogallery-items-add <?php echo $has_items ? 'foogallery-hidden' : ''; ?>">
					<?php do_action( 'foogallery_gallery_metabox_items_add', $gallery ); ?>
				</div>
			</div>
			<div class="foogallery-items-view foogallery-items-view-preview <?php echo $mode==='preview' ? '' : 'foogallery-hidden'; ?>">
				<!-- Wrap existing preview container -->
				<div class="foogallery-preview-wrapper viewport-desktop">
					<div class="foogallery_preview_container <?php echo $mode==='preview' ? '' : 'foogallery-preview-force-refresh'; ?>">
						<?php
						if ( $has_items && $mode === 'preview' ) {
							foogallery_render_gallery( $gallery->ID );
						} else if ( $has_items && $mode === 'manage' ) {
							echo '<div style="padding:20px; text-align: center">';
							echo '<h3>' . esc_html__( 'Generating preview...', 'foogallery' ) . '</h3>';
							echo '</div>';
						} else {
							$this->render_empty_gallery_preview();
						}
						?>
					</div>
				</div>
                
				<div style="clear: both"></div>
				<?php wp_nonce_field( 'foogallery_preview', 'foogallery_preview', false ); ?>
			</div>
			<?php
		}

		public function render_empty_gallery_preview( $message = '' ) {
			if ( empty($message) ) {
				$message = __( 'Please add media to your gallery to see a preview!', 'foogallery' );
			}
			echo '<div class="foogallery-preview-empty" style="padding:20px; text-align: center">';
			echo '<h3>' . esc_html__( 'Please add media to your gallery to see a preview!', 'foogallery' ) . '</h3>';
			echo '</div>';
		}

		public function ajax_gallery_preview() {
			if ( ! check_ajax_referer( 'foogallery_preview', 'foogallery_preview_nonce', false ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Invalid security token.', 'foogallery' ) ),
					403
				);
			}

			$post_data = wp_unslash( $_POST );
			$foogallery_id = isset( $post_data['foogallery_id'] ) ? absint( $post_data['foogallery_id'] ) : 0;
			if ( ! $foogallery_id ) {
				wp_send_json_error(
					array( 'message' => __( 'Invalid gallery ID.', 'foogallery' ) ),
					400
				);
			}

			if ( ! current_user_can( 'edit_post', $foogallery_id ) ) {
				wp_send_json_error(
					array( 'message' => __( 'Insufficient permissions.', 'foogallery' ) ),
					403
				);
			}

			$template = isset( $post_data['foogallery_template'] ) ? sanitize_key( $post_data['foogallery_template'] ) : foogallery_default_gallery_template();
			$template = apply_filters( 'foogallery_preview_template', $template, $foogallery_id );

			//check that the template supports previews
			$gallery_template = foogallery_get_gallery_template( $template );
			if ( isset( $gallery_template['preview_support'] ) && true === $gallery_template['preview_support'] ) {

				global $foogallery_gallery_preview;

				$foogallery_gallery_preview = true;

				$attachment_ids = array();
				if ( isset( $post_data['foogallery_attachments'] ) ) {
					$attachment_ids = $post_data['foogallery_attachments'];
					if ( is_string( $attachment_ids ) ) {
						$attachment_ids = array_map( 'absint', array_filter( explode( ',', $attachment_ids ) ) );
					} else {
						$attachment_ids = array_map( 'absint', (array) $attachment_ids );
					}
				}

				$args = array(
					'template'       => $template,
					'attachment_ids' => $attachment_ids,
					'preview'        => true,
				);

				$args = $this->extract_preview_arguments( $args, $post_data, $template );

				$args = apply_filters( 'foogallery_preview_arguments', $args, $post_data, $template );
				$args = apply_filters( 'foogallery_preview_arguments-' . $template, $args, $post_data );

				if ( foogallery_is_debug() ) {
					echo '<pre style="display: none">' . esc_html__( 'Preview Debug Arguments:', 'foogallery' ) . '<br>' . esc_html( print_r( $args, true ) ) . '</pre> <!-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -->';
				}

				do_action( 'foogallery_preview_before_render', $foogallery_id, $args );

				foogallery_render_gallery( $foogallery_id, $args );

				do_action( 'foogallery_preview_after_render', $foogallery_id, $args );

				$foogallery_gallery_preview = false;

			} else {
				echo '<div style="padding:20px 50px 50px 50px; text-align: center">';
				echo '<h3>' . esc_html__( 'Preview not available!', 'foogallery' ) . '</h3>';
				echo esc_html__( 'Sorry, but this gallery template does not support live previews. Please update the gallery in order to see what the gallery will look like.', 'foogallery' );
				echo '</div>';
			}

			wp_die();
		}

	    /**
         * Extract all the preview arguments from the post data
         *
	     * @param $args
	     * @param $post_data
	     * @param $template
	     *
	     * @return mixed
	     */
		private function extract_preview_arguments( $args, $post_data, $template ) {
		    if ( array_key_exists( FOOGALLERY_META_SETTINGS, $post_data ) ) {
			    $settings = $post_data[FOOGALLERY_META_SETTINGS];
			    foreach ( $settings as $key => $value ) {
			        if ( strpos( $key, $template . '_' ) === 0 ) {
				        $args[ $this->str_replace_first( $template . '_', '', $key ) ] = $value;
			        }
			    }
		    }

            return $args;
		}

	    function str_replace_first($search, $replace, $subject) {
		    $pos = strpos($subject, $search);
		    if ($pos !== false) {
			    return substr_replace($subject, $replace, $pos, strlen($search));
		    }
		    return $subject;
	    }

		/**
		 * Handle gallery previews where there are no attachments
		 *
		 * @param $foogallery FooGallery
		 */
		public function preview_no_attachments( $foogallery ) {
			global $foogallery_gallery_preview;

			if ( isset( $foogallery_gallery_preview ) && true === $foogallery_gallery_preview ) {
				$message = '';
				if ( foogallery_default_datasource() !== $foogallery->datasource_name ) {
					$message = __( 'No items were found for this gallery.', 'foogallery' );
				}
				$this->render_empty_gallery_preview( $message );
			}
		}

		public function enqueue_assets() {
			$screen_id = foo_current_screen_id();

			//only include scripts if we on the foogallery add/edit page
			if ( FOOGALLERY_CPT_GALLERY === $screen_id ||
				'edit-' . FOOGALLERY_CPT_GALLERY === $screen_id ) {

				//enqueue any dependencies from extensions or gallery templates
				do_action( 'foogallery_enqueue_preview_dependencies' );
				//add core foogallery files for preview
				foogallery_enqueue_core_gallery_template_style();
				foogallery_enqueue_core_gallery_template_script();

				//make sure we have jquery UI sortable enqueued
				wp_enqueue_script( 'jquery-ui-sortable');
			}
		}
    }
}