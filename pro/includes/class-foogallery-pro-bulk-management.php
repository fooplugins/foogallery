<?php
/**
 * FooGallery Pro Bulk Management Class
 */
if ( ! class_exists( 'FooGallery_Pro_Bulk_Management' ) ) {

	class FooGallery_Pro_Bulk_Management {

		function __construct() {
			add_action( 'foogallery_attachments_list_bar_buttons', array( $this, 'add_bulk_button' ) );
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			add_action( 'admin_footer', array( $this, 'render_bulk_modal' ) );

			add_action( 'wp_ajax_foogallery_bulk_management_content', array( $this, 'ajax_load_modal_content' ) );
			add_action( 'wp_ajax_foogallery_bulk_management_assign', array( $this, 'ajax_assign' ) );
		}

		/**
		 * Add a "Bulk Management" button to the manage items button list bar
		 * @param $foogallery
		 */
		function add_bulk_button( $foogallery ) {
?>
			<button type="button" class="button button-primary button-large alignright bulk_media_management">
				<?php _e( 'Bulk Taxonomy Manager', 'foogallery' ); ?>
			</button>
<?php
		}

		/**
		 * Enqueues js assets
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'foogallery.admin.bulk.management', FOOGALLERY_PRO_URL . 'css/foogallery.admin.bulk.management.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.bulk.management', FOOGALLERY_PRO_URL . 'js/foogallery.admin.bulk.management.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}

		/**
		 * Renders the bulk management modal for use on the gallery edit page
		 */
		public function render_bulk_modal() {

			global $post;

			//check if the gallery edit page is being shown
			$screen = get_current_screen();
			if ( 'foogallery' !== $screen->id ) {
				return;
			}

			?>
            <div class="foogallery-bulk-management-modal-wrapper" data-foogalleryid="<?php echo $post->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'foogallery-bulk-management-content' ); ?>" style="display: none;">
                <div class="media-modal wp-core-ui">
                    <button type="button" class="media-modal-close foogallery-bulk-management-modal-close">
                        <span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
                    </button>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui">
                            <div class="foogallery-bulk-management-modal-title">
                                <h1><?php _e('Bulk Taxonomy Manager', 'foogallery'); ?></h1>
                                <select class="foogallery-bulk-management-select-taxonomy">
                                    <?php
                                    $taxonomy_objects = get_object_taxonomies( 'attachment', 'objects' );
                                    foreach ( $taxonomy_objects as $taxonomy_object ) {
                                        echo '<option value="' . $taxonomy_object->name . '">' . $taxonomy_object->label . '</option>';
                                    }
                                    ?>
                                </select>
                                <a class="foogallery-bulk-management-modal-reload button" href="#" style="display: none;"><span style="padding-top: 4px;" class="dashicons dashicons-update"></span> <?php _e('Reload', 'foogallery'); ?></a>
                            </div>
                            <div class="foogallery-bulk-management-modal-container not-loaded">
                                <div class="spinner is-active"></div>
                            </div>
                            <div class="foogallery-bulk-management-modal-toolbar">
                                <div class="foogallery-bulk-management-modal-toolbar-inner">
                                    <div class="media-toolbar-primary">
                                        <a href="#"
                                           class="foogallery-bulk-management-modal-close button button-large button-secondary"
                                           title="<?php esc_attr_e('Close', 'foogallery'); ?>"><?php _e('Close', 'foogallery'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="media-modal-backdrop"></div>
            </div>
			<?php
		}

		/**
		 * Outputs the modal content
		 */
		public function ajax_load_modal_content() {
			$nonce = safe_get_from_request( 'nonce' );

			if ( wp_verify_nonce( $nonce, 'foogallery-bulk-management-content' ) ) {

				$attachments = safe_get_from_request( 'attachments' );
				$foogallery_id = intval( safe_get_from_request( 'foogallery_id' ) );
				$taxonomy = safe_get_from_request( 'taxonomy' );

				if ( empty( $taxonomy ) ) {
				    //select the taxonomy that is chosen for the gallery
	                $foogallery = FooGallery::get_by_id( $foogallery_id );
	                if ( !$foogallery->is_new() ) {
		                $taxonomy = $foogallery->get_setting( 'filtering_taxonomy', '' );
	                }
	            }

				if ( empty( $taxonomy ) ) {
					$taxonomy = FOOGALLERY_ATTACHMENT_TAXONOMY_TAG;
				}

			    echo '<div class="foogallery-bulk-management-modal-content">';
			    $this->render_content( $foogallery_id, $attachments, $taxonomy );
			    echo '</div>';

			    echo '<div class="foogallery-bulk-management-modal-sidebar">';
			    $this->render_sidebar( $attachments, $taxonomy );
				echo '</div>';
			}

			die();
		}

		/**
		 * Assigns the taxonomies
		 */
		public function ajax_assign() {
			$nonce = safe_get_from_request( 'nonce' );
			$attachments = array_unique( array_map( 'intval', explode( ',', safe_get_from_request( 'attachments' ) ) ) );
			$attachments_to_remove = array_unique( array_map( 'intval', explode( ',', safe_get_from_request( 'attachments_remove' ) ) ) );
            $results = array();

			if ( wp_verify_nonce( $nonce, 'foogallery-bulk-management-assign' ) ) {

				$taxonomies = explode( ',', safe_get_from_request( 'taxonomies' ) );

				$errors = $assignments = $removals = 0;

				foreach ( $taxonomies as $taxonomy ) {
					$taxonomy_data = safe_get_from_request( 'taxonomy_data_' . $taxonomy );
					if ( !empty( $taxonomy_data ) ) {
						$term_ids = array_unique( array_map( 'intval', explode( ',', $taxonomy_data ) ) );

						if ( count( $taxonomy_data ) > 0 ) {
							foreach ( $attachments as $attachment ) {
							    if ( $attachment > 0 ) {
								    $results[] = wp_set_post_terms( $attachment, $term_ids, $taxonomy, true );
							    }
							}
							foreach ( $attachments_to_remove as $attachment_to_remove ) {
							    if ( $attachment_to_remove > 0 ) {
								    $results[] = wp_remove_object_terms( $attachment_to_remove, $term_ids, $taxonomy );
								    $removals++;
							    }
							}
						}
					}
				}


				foreach ( $results as $result ) {
					if ( is_wp_error( $result ) ) {
						$errors++;
					} else if ( is_array( $result ) ) {
						$assignments += count( $result );
                    }
                }
				if ( $assignments > 0 ) {
				    echo ' ' . sprintf( _n('%d assignment added.', '%d assignments added.', $assignments, 'foogallery'), $assignments );
                }
				if ( $removals > 0 ) {
					echo ' ' . sprintf( _n('%d assignment removed.', '%d assignments removed.', $removals, 'foogallery'), $removals );
                }
				if ( ($assignments + $removals) === 0 ) {
					echo __( 'Nothing was done.', 'foogallery' );
				}
				if ( $errors > 0 ) {
					echo ' ' . sprintf( _n('%d error!', '%d errors!', $errors, 'foogallery'), $errors );
				}
			}

			die();
		}


		/**
         * Render the attachment container
         *
		 * @param $foogallery_id
		 * @param $attachments
		 */
		public function render_content( $foogallery_id, $attachments, $taxonomy ) {
			echo '<div class="foogallery-bulk-management-modal-content-inner">';

			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			) );

			echo '<div class="foogallery-bulk-management-modal-content-terms">';

			foreach ($terms as $term) {
				echo '<a href="#" class="button button-small foogallery-bulk-management-select-term" data-term-id="' . $term->term_id . '">' . $term->name . '</a>';
			}

			echo '</div>';

			$foogallery = FooGallery::get_by_id( $foogallery_id );

			$attachment_query_args = array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post__in'       => explode( ',', $attachments ),
				'orderby'        => foogallery_sorting_get_posts_orderby_arg( $foogallery->sorting ),
				'order'          => foogallery_sorting_get_posts_order_arg( $foogallery->sorting )
			);

			$attachment_posts = get_posts( $attachment_query_args );

			echo '<ul>';

			foreach ( $attachment_posts as $attachment_post ) {
				$attachment_id = $attachment_post->ID;
				$attachment    = wp_get_attachment_image_src( $attachment_id );
				$img_tag       = "<img width=\"150\" height=\"150\" data-src=\"{$attachment[0]}\" />";
				$terms         = get_the_terms( $attachment_post, $taxonomy );
				$term_data     = array();
				foreach ( $terms as $term ) {
					$term_data[] = array(
                        'id'   => $term->term_id,
                        'name' => $term->name
                    );
				}
				?>
                <li data-attachment-id="<?php echo $attachment_id; ?>" data-terms="<?php echo esc_attr( json_encode( $term_data ) ); ?>">
                    <div>
						<?php echo $img_tag; ?>
                    </div>
                </li>
				<?php
			}

			echo '</ul>';

			echo '</div>';
        }

		/**
		 * Render the sidebar info
		 */
		public function render_sidebar( $attachments, $taxonomy ) {
			$taxonomy_object = get_taxonomy( $taxonomy );

		    echo '<div class="foogallery-bulk-management-modal-sidebar-inner">';

			if ( foogallery_get_setting( 'disable_attachment_taxonomies' ) === 'on' ) {
				echo __('You have chosen to disable attachment taxonomies. You can turn on attachment taxonomies which will allow you to assign attachments to Media Tags and Media Categories.', 'foogallery');
			}

			if ( empty( $taxonomy ) ) {
				echo '<h2>' . __( 'No Taxonomies Available!', 'foogallery' ) . '</h2>';
			    return;
            }

			echo '<h2>' . sprintf( __( 'Bulk Assign %s', 'foogallery' ), $taxonomy_object->label ) . '</h2>';
			echo '<p>' . __( 'You can quickly and easily assign multiple taxonomy terms to the items in your gallery. You can also click on a term above the gallery items to see which items have already been assigned to the selected term.', 'foogallery' ) . '</p>';

			echo '<h3>' . __( 'Select Gallery Items', 'foogallery' ) . '</h3>';
			echo '<span class="foogallery-bulk-management-modal-selected foogallery-bulk-management-modal-toggle"><strong>0</strong> ' . __( 'item(s) selected.', 'foogallery' );
			echo '&nbsp;<a href="#clear" class="foogallery-bulk-management-modal-action-clear">' . __( 'Clear Selection', 'foogallery') . '</a>';
	        echo '</span><br />';

			echo '<h3>' . sprintf( __( 'Select %s', 'foogallery' ), $taxonomy_object->label ) . '</h3>';

			$nonce_for_adding_term = wp_create_nonce( 'foogallery-attachment-taxonomy' );
            $taxonomy_data = array();
            $taxonomy_data['nonce'] = $nonce_for_adding_term;
            $taxonomy_data = array(
                'nonce' => $nonce_for_adding_term,
                'terms' => foogallery_build_terms_recursive($taxonomy, array('hide_empty' => false)),
                'query_var' => true,
                'labels' => array(
                    'placeholder' => __( 'Select one or more', 'foogallery' ) . ' ' . strtolower( $taxonomy_object->label ),
                    'add' => $taxonomy_object->labels->add_new_item
                ),
            );

            echo '<input type="text" data-taxonomy="' . $taxonomy_object->name . '" class="foogallery-bulk-management-selectize" id="bulk-management-input-' . $taxonomy . '" data-taxonomy-data="' . esc_attr( json_encode($taxonomy_data) ) . '" />';

			echo '<br /><div class="foogallery-bulk-management-modal-actions foogallery-bulk-management-modal-toggle">';
			echo '<button type="button" class="button button-large button-primary foogallery-bulk-management-modal-action-assign" data-nonce="' . wp_create_nonce( 'foogallery-bulk-management-assign' ) . '">' . __( 'Bulk Assign', 'foogallery' ) . '</button>';
			echo '<div style="display: none" class="spinner is-active"></div>';
			echo '<div class="foogallery-bulk-management-modal-action-message"></div>';
			echo '</div>';

			echo '</div>';
        }
	}
}
