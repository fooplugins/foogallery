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
				<?php esc_html_e( 'Bulk Taxonomy Manager', 'foogallery' ); ?>
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
            <div class="foogallery-bulk-management-modal-wrapper" data-foogalleryid="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery-bulk-management-content' ) ); ?>" style="display: none;">
                <div class="media-modal wp-core-ui">
                    <button type="button" class="media-modal-close foogallery-bulk-management-modal-close">
                        <span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
                    </button>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui">
                            <div class="foogallery-bulk-management-modal-title">
                                <h1><?php esc_html_e( 'Bulk Taxonomy Manager', 'foogallery' ); ?></h1>
                                <select class="foogallery-bulk-management-select-taxonomy">
                                    <?php
                                    $taxonomy_objects = get_object_taxonomies( 'attachment', 'objects' );
                                    foreach ( $taxonomy_objects as $taxonomy_object ) {
                                        printf(
	                                        '<option value="%1$s">%2$s</option>',
	                                        esc_attr( $taxonomy_object->name ),
	                                        esc_html( $taxonomy_object->label )
                                        );
                                    }
                                    ?>
                                </select>
                                <a class="foogallery-bulk-management-modal-reload button" href="#" style="display: none;"><span style="padding-top: 4px;" class="dashicons dashicons-update"></span> <?php esc_html_e( 'Reload', 'foogallery' ); ?></a>
                            </div>
                            <div class="foogallery-bulk-management-modal-container not-loaded">
                                <div class="spinner is-active"></div>
                            </div>
                            <div class="foogallery-bulk-management-modal-toolbar">
                                <div class="foogallery-bulk-management-modal-toolbar-inner">
                                    <div class="media-toolbar-primary">
                                        <a href="#"
                                           class="foogallery-bulk-management-modal-close button button-large button-secondary"
                                           title="<?php esc_attr_e( 'Close', 'foogallery' ); ?>"><?php esc_html_e( 'Close', 'foogallery' ); ?></a>
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

                        if ( is_array( $term_ids ) && count( $term_ids ) > 0 ) {
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
					echo ' ' . esc_html(
						sprintf(
							_n( '%d assignment added.', '%d assignments added.', $assignments, 'foogallery' ),
							$assignments
						)
					);
                }
				if ( $removals > 0 ) {
					echo ' ' . esc_html(
						sprintf(
							_n( '%d assignment removed.', '%d assignments removed.', $removals, 'foogallery' ),
							$removals
						)
					);
                }
				if ( ($assignments + $removals) === 0 ) {
					esc_html_e( 'Nothing was done.', 'foogallery' );
				}
				if ( $errors > 0 ) {
					echo ' ' . esc_html(
						sprintf(
							_n( '%d error!', '%d errors!', $errors, 'foogallery' ),
							$errors
						)
					);
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

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					printf(
						'<a href="#" class="button button-small foogallery-bulk-management-select-term" data-term-id="%1$s">%2$s</a>',
						esc_attr( $term->term_id ),
						esc_html( $term->name )
					);
				}
			}

			echo '</div>';

			$foogallery = FooGallery::get_by_id( $foogallery_id );

			if ( !empty( $attachments ) ) {
				$attachment_query_args = array(
					'post_type'      => 'attachment',
					'posts_per_page' => -1,
					'post__in'       => explode( ',', $attachments ),
					'orderby'        => foogallery_sorting_get_posts_orderby_arg( $foogallery->sorting ),
					'order'          => foogallery_sorting_get_posts_order_arg( $foogallery->sorting )
				);
			} else {
				$attachment_query_args = array(
					'post_type'      => 'attachment',
					'posts_per_page' => 100,
				);
			}

			$attachment_query_args = apply_filters( 'foogallery_bulk_taxonomy_manager_attachments_query_args', $attachment_query_args, $foogallery_id, $attachments, $taxonomy );

			$attachment_posts = get_posts( $attachment_query_args );

			echo '<ul>';

			foreach ( $attachment_posts as $attachment_post ) {
				$attachment_id = $attachment_post->ID;
				$attachment    = wp_get_attachment_image_src( $attachment_id );
				$img_src       = is_array( $attachment ) ? esc_url( $attachment[0] ) : '';
				$terms_list    = get_the_terms( $attachment_post, $taxonomy );
				$term_data     = array();

				if ( ! is_wp_error( $terms_list ) && ! empty( $terms_list ) ) {
					foreach ( $terms_list as $term ) {
						$term_data[] = array(
							'id'   => (int) $term->term_id,
							'name' => sanitize_text_field( $term->name ),
						);
					}
				}

				$meta_data = array(
					'Title'   => sanitize_text_field( $attachment_post->post_title ),
					'Caption' => sanitize_text_field( $attachment_post->post_excerpt ),
					'File'    => sanitize_text_field( wp_basename( $attachment_post->guid ) ),
				);

				$term_json = wp_json_encode( $term_data );
				$meta_json = wp_json_encode( $meta_data );
				?>
                <li data-attachment-id="<?php echo esc_attr( $attachment_id ); ?>" data-terms="<?php echo esc_attr( $term_json ); ?>" data-meta="<?php echo esc_attr( $meta_json ); ?>">
                    <div>
						<img width="150" height="150" data-src="<?php echo esc_url( $img_src ); ?>" alt="" />
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
				esc_html_e( 'You have chosen to disable attachment taxonomies. You can turn on attachment taxonomies which will allow you to assign attachments to Media Tags and Media Categories.', 'foogallery' );
			}

			if ( empty( $taxonomy ) ) {
				printf( '<h2>%s</h2>', esc_html__( 'No Taxonomies Available!', 'foogallery' ) );
			    return;
            }

			if ( ! $taxonomy_object ) {
				return;
			}

			printf(
				'<h2>%s</h2>',
				sprintf(
					esc_html__( 'Bulk Assign %s', 'foogallery' ),
					esc_html( $taxonomy_object->label )
				)
			);
			printf(
				'<p>%s</p>',
				esc_html__( 'You can quickly and easily assign multiple taxonomy terms to the items in your gallery. You can also click on a term above the gallery items to see which items have already been assigned to the selected term.', 'foogallery' )
			);

			printf( '<h3>%s</h3>', esc_html__( 'Select Gallery Items', 'foogallery' ) );
			echo '<span class="foogallery-bulk-management-modal-selected foogallery-bulk-management-modal-toggle"><strong>0</strong> ' . esc_html__( 'item(s) selected.', 'foogallery' );
			echo '&nbsp;<a href="#clear" class="foogallery-bulk-management-modal-action-clear">' . esc_html__( 'Clear Selection', 'foogallery' ) . '</a>';
	        echo '</span><br />';

			printf(
				'<h3>%s</h3>',
				sprintf(
					esc_html__( 'Select %s', 'foogallery' ),
					esc_html( $taxonomy_object->label )
				)
			);

			$nonce_for_adding_term = wp_create_nonce( 'foogallery-attachment-taxonomy' );
			$terms_recursive      = foogallery_build_terms_recursive( $taxonomy, array( 'hide_empty' => false ) );
			$terms_recursive      = is_array( $terms_recursive ) ? $terms_recursive : array();
			$taxonomy_data        = array(
				'nonce'     => $nonce_for_adding_term,
				'terms'     => $terms_recursive,
				'query_var' => true,
				'labels'    => array(
					'placeholder' => sprintf(
						'%s %s',
						esc_html__( 'Select one or more', 'foogallery' ),
						strtolower( sanitize_text_field( $taxonomy_object->label ) )
					),
					'add'        => sanitize_text_field( $taxonomy_object->labels->add_new_item ),
				),
			);

			printf(
				'<input type="text" data-taxonomy="%1$s" class="foogallery-bulk-management-selectize" id="bulk-management-input-%2$s" data-taxonomy-data="%3$s" />',
				esc_attr( $taxonomy_object->name ),
				esc_attr( $taxonomy ),
				esc_attr( wp_json_encode( $taxonomy_data ) )
			);

			echo '<br /><div class="foogallery-bulk-management-modal-actions foogallery-bulk-management-modal-toggle">';
			printf(
				'<button type="button" class="button button-large button-primary foogallery-bulk-management-modal-action-assign" data-nonce="%1$s">%2$s</button>',
				esc_attr( wp_create_nonce( 'foogallery-bulk-management-assign' ) ),
				esc_html__( 'Bulk Assign', 'foogallery' )
			);
			echo '<div style="display: none" class="spinner is-active"></div>';
			echo '<div class="foogallery-bulk-management-modal-action-message"></div>';
			echo '</div>';

			echo '<div style="display: none" class="foogallery-bulk-management-modal-metadata">';
            printf( '<h3>%s</h3>', esc_html__( 'Last Selected Image Details', 'foogallery' ) );
            echo '<div class="foogallery-bulk-management-modal-metadata-inner"></div>';
            echo '</div>';

			echo '</div>';
        }
	}
}
