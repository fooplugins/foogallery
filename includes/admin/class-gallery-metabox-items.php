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

			$mode = $gallery->get_meta( 'foogallery_items_view', 'manage' );

			if ( empty($mode) || $gallery->is_new() ) {
				$mode = 'manage';
			}
			$has_items = !$gallery->is_empty();

			do_action( 'foogallery_gallery_metabox_items', $gallery );
			?>
			<div class="hidden foogallery-items-view-switch-container">
				<div class="foogallery-items-view-switch">
					<a href="#manage" data-value="manage" data-container=".foogallery-items-view-manage" class="<?php echo $mode==='manage' ? 'current' : ''; ?>"><?php _e('Manage Items', 'foogallery'); ?></a>
					<a href="#preview" data-value="preview" data-container=".foogallery-items-view-preview" class="<?php echo $mode==='preview' ? 'current' : ''; ?>"><?php _e('Gallery Preview', 'foogallery'); ?></a>
				</div>
				<span id="foogallery_preview_spinner" class="spinner"></span>
				<input type="hidden" id="foogallery_items_view_input" value="<?php echo $mode; ?>" name="<?php echo FOOGALLERY_META_SETTINGS . '[foogallery_items_view]'; ?>" />
			</div>

			<div class="foogallery-items-view foogallery-items-view-manage <?php echo $mode==='manage' ? '' : 'hidden'; ?>">
				<input type="hidden" name="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce" id="<?php echo FOOGALLERY_CPT_GALLERY; ?>_nonce" value="<?php echo wp_create_nonce( plugin_basename( FOOGALLERY_FILE ) ); ?>"/>
				<div class="foogallery-items-list">
					<div class="foogallery-items-empty <?php echo $has_items ? 'hidden' : ''; ?>" style="padding-top:20px; text-align: center">
						<p><?php _e('Your gallery is currently empty. Add items to see a preview.','foogallery'); ?></p>
					</div>
					<?php do_action( 'foogallery_gallery_metabox_items_list', $gallery ); ?>
				</div>
				<div class="foogallery-items-add <?php echo $has_items ? 'hidden' : ''; ?>">
					<?php do_action( 'foogallery_gallery_metabox_items_add', $gallery ); ?>
				</div>
			</div>
			<div class="foogallery-items-view foogallery-items-view-preview <?php echo $mode==='preview' ? '' : 'hidden'; ?>">
				<div class="foogallery_preview_container <?php echo $mode==='preview' ? '' : 'foogallery-preview-force-refresh'; ?>">
					<?php
					if ( $has_items && $mode==='preview' ) {
						foogallery_render_gallery( $gallery->ID );
					} else {
						$this->render_empty_gallery_preview();
					}
					?>
				</div>
				<div style="clear: both"></div>
				<?php wp_nonce_field( 'foogallery_preview', 'foogallery_preview', false ); ?>
			</div>
			<?php
		}

		public function render_empty_gallery_preview() {
			echo '<div class="foogallery-preview-empty" style="padding:20px; text-align: center">';
			echo '<h3>' . __( 'Please add media to your gallery to see a preview!', 'foogallery' ) . '</h3>';
			echo '</div>';
		}

		public function ajax_gallery_preview() {
			if ( check_admin_referer( 'foogallery_preview', 'foogallery_preview_nonce' ) ) {

				$foogallery_id = $_POST['foogallery_id'];

				$template = $_POST['foogallery_template'];

				//check that the template supports previews
				$gallery_template = foogallery_get_gallery_template( $template );
				if ( isset( $gallery_template['preview_support'] ) && true === $gallery_template['preview_support'] ) {

					global $foogallery_gallery_preview;

					$foogallery_gallery_preview = true;

					$args = array(
						'template'       => $template,
						'attachment_ids' => $_POST['foogallery_attachments'],
						'preview'        => true
					);

					$args = $this->extract_preview_arguments( $args, $_POST, $template );

					$args = apply_filters( 'foogallery_preview_arguments', $args, $_POST, $template );
					$args = apply_filters( 'foogallery_preview_arguments-' . $template, $args, $_POST );

					if ( foogallery_is_debug() ) {
                        echo '<pre style="display: none">' . __('Preview Debug Arguments:', 'foogallery') . '<br>' . print_r( $args, true ) . '</pre>';
                    }

					foogallery_render_gallery( $foogallery_id, $args );

					$foogallery_gallery_preview = false;

				} else {
					echo '<div style="padding:20px 50px 50px 50px; text-align: center">';
					echo '<h3>' . __( 'Preview not available!', 'foogallery' ) . '</h3>';
					echo __('Sorry, but this gallery template does not support live previews. Please update the gallery in order to see what the gallery will look like.', 'foogallery' );
					echo '</div>';
				}
			}

			die();
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
				$this->render_empty_gallery_preview();
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