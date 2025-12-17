<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Taxonomy_Base' ) ) {

    abstract class FooGallery_Pro_Datasource_Taxonomy_Base {

    	private $taxonomy;
    	private $datasource_name;

    	public function __construct( $datasource_name, $taxonomy ) {
    		$this->taxonomy = $taxonomy;
    		$this->datasource_name = $datasource_name;

			add_filter( "foogallery_datasource_{$datasource_name}_item_count", array( $this, 'get_gallery_attachment_count' ), 10, 2 );
		    add_filter( "foogallery_datasource_{$datasource_name}_attachment_ids", array( $this, 'get_gallery_attachment_ids' ), 10, 2 );
			add_filter( "foogallery_datasource_{$datasource_name}_featured_image", array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( "foogallery_datasource_{$datasource_name}_attachments", array( $this, 'get_gallery_attachments' ), 10, 2 );
			add_action( "foogallery-datasource-modal-content_{$datasource_name}", array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
			add_action( 'added_term_relationship', array( $this, 'change_term_relationship_clear_datasource_cached_attachments' ), 10, 3 );
			add_action( 'deleted_term_relationships', array( $this, 'change_term_relationship_clear_datasource_cached_attachments' ), 10, 3 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_attachments' ) );
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
			add_filter( 'foogallery_render_template_argument_overrides', array( $this, 'shortcode_taxonomy_attribute' ), 10, 2 );
		}

		/**
		 * Allow for a shortcode attribute of "tags"
		 *
		 * @param $foogallery
		 * @param $args
		 *
		 * @return mixed
		 */
		function shortcode_taxonomy_attribute( $foogallery, $args ) {
			//check for the taxonomy shortcode attribute
			if ( array_key_exists( $this->datasource_name, $args ) ) {
				$foogallery->datasource_name = $this->datasource_name;
				$foogallery->datasource_value = array(
					'taxonomy'       => $this->taxonomy,
					'field'	         => 'slug',
					'value'          => explode( ',', $args[$this->datasource_name] ),
                    'enhanced_cache' => true
				);
			}

			return $foogallery;
		}

		/**
		 * Enqueues taxonomy-specific assets
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'foogallery.admin.datasources.taxonomy', FOOGALLERY_PRO_URL . 'css/foogallery.admin.datasources.taxonomy.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.datasources.taxonomy', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.taxonomy.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}

		/**
		 * Clear the previously saved datasource cache for the gallery
		 * @param $foogallery_id
		 */
		public function before_save_gallery_datasource_clear_datasource_cached_attachments( $foogallery_id ) {
            //clear any previously cached post meta for the taxonomy for the gallery
			$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $this->taxonomy;
			delete_post_meta($foogallery_id, $cache_post_meta_key);

			$this->clear_enhanced_cache_for_gallery( $foogallery_id, $cache_post_meta_key );
        }

	    /**
         * Clears any enhanced cache for the gallery
         *
	     * @param $foogallery_id
	     */
        private function clear_enhanced_cache_for_gallery( $foogallery_id, $cache_post_meta_key ) {
	        $meta = get_post_meta($foogallery_id);

	        foreach ( $meta as $key=>$val ) {
	            //if the post meta key starts with $cache_post_meta_key then delete the post meta
		        if ( strpos( $key, $cache_post_meta_key ) === 0 ) {
			        delete_post_meta( $foogallery_id, $key );
		        }
	        }
        }

        /**
         * Clears any caches for attachments assigned to galleries
         *
         * @param $object_id
         * @param $tt_id
         * @param $taxonomy
         */
		public function change_term_relationship_clear_datasource_cached_attachments( $object_id, $tt_id, $taxonomy ) {
			if ( $taxonomy === $this->taxonomy ) {
				//delete all cached attachments for the taxonomy
				$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $taxonomy;

				delete_post_meta_by_key( $cache_post_meta_key );

				//make sure we clear all galleries that have enhanced caching
				$args = array(
                    'numberposts' => -1,
					'post_type'   => FOOGALLERY_CPT_GALLERY,
					'fields'      => 'ids',
					'meta_query'  => array(
						array(
							'key'     => FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_enhanced',
							'compare' => 'EXISTS'
						)
					)
				);

				$galleries_with_enhanced_cache = get_posts( $args );
				foreach ( $galleries_with_enhanced_cache as $foogallery_id ) {
					$this->clear_enhanced_cache_for_gallery( $foogallery_id, $cache_post_meta_key );
                }
			}
        }

		/**
		 * Returns the number of attachments used from the media library
		 *
		 * @param int $count
		 * @param FooGallery $foogallery
		 *
		 * @return int
		 */
		public function get_gallery_attachment_count( $count, $foogallery ) {
            return count( $this->get_gallery_attachments( array(), $foogallery ) );
		}

	    /**
	     * Returns an array of the attachment ID's for the gallery
	     *
	     * @param $attachment_ids
	     * @param $foogallery
	     *
	     * @return array
	     */
	    public function get_gallery_attachment_ids( $attachment_ids, $foogallery ) {
		    $attachment_ids = array();
		    $attachments = $this->get_gallery_attachments( array(), $foogallery );
		    foreach ( $attachments as $attachment ) {
			    $attachment_ids[] = $attachment->ID;
		    }
		    return $attachment_ids;
	    }

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 *
		 * @param array $attachments
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments( $attachments, $foogallery ) {
			global $foogallery_gallery_preview;
		
			if ( ! empty( $foogallery->datasource_value ) ) {
				$datasource_value = $foogallery->datasource_value;
				$taxonomy = $datasource_value['taxonomy'];
		
				$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $taxonomy;
				$enhanced_cache = false;
		
				if ( array_key_exists( 'enhanced_cache', $datasource_value ) ) {
					$enhanced_cache = true;
					$cache_post_meta_key .= '_values(' . implode( '|', $datasource_value['value'] ) . ')';
				}
		
				// Instantiate the helper class for querying attachments
				$helper = new FooGallery_Datasource_MediaLibrary_Query_Helper();
		
				// Never get the cached attachments if we are doing a preview
				if ( $foogallery_gallery_preview ) {
					$cached_attachments = false;
				} else {
					// Check if there is a cached list of attachments
					$cached_attachments = get_post_meta( $foogallery->ID, $cache_post_meta_key, true );
				}
		
				// Retrieve the selection mode from the gallery metadata
				$selection_mode = isset( $datasource_value['selection_mode'] ) ? $datasource_value['selection_mode'] : '';
				if ( empty( $selection_mode ) ) {
					$selection_mode = 'OR';  // Default to 'OR' if no mode is set
				}
				$operator = ($selection_mode === 'AND') ? 'AND' : 'IN';  // Set operator for the tax_query


				if ( empty( $cached_attachments ) ) {
					$terms = $datasource_value['value'];
					$field = array_key_exists( 'field', $datasource_value ) ? $datasource_value['field'] : 'term_id';
		
					// Query attachments with the correct operator based on the selection mode
					$attachments = $helper->query_attachments( $foogallery, array(
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field'    => $field,
								'terms'    => $terms,
								'operator' => $operator,
							),
						)
					));
		
					$attachment_ids = array();
					foreach ( $attachments as $attachment ) {
						$attachment_ids[] = $attachment->ID;
					}
		
					// Save a cached list of attachments
					update_post_meta( $foogallery->ID, $cache_post_meta_key, $attachment_ids );
		
					if ( $enhanced_cache ) {
						update_post_meta( $foogallery->ID, FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_enhanced', true );
					}
				} else {
					// Query attachments based on the cached attachment IDs
					$attachments = $helper->query_attachments( $foogallery,
						array( 'post__in' => $cached_attachments )
					);
				}
			}
		
			return $attachments;
		}	

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 *
		 * @param FooGalleryAttachment $default
		 * @param FooGallery $foogallery
		 *
		 * @return bool|FooGalleryAttachment
		 */
		public function get_gallery_featured_attachment( $default, $foogallery ) {
            $attachments = $this->get_gallery_attachments( array(), $foogallery );
			if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
				return $attachments[0];
			}

			return false;
		}

		/**
		 * Output the datasource modal content
		 * @param $foogallery_id
		 */
		public function render_datasource_modal_content( $foogallery_id, $datasource_value ) {

			$selected_terms = array();
			if ( is_array( $datasource_value ) && array_key_exists( 'value', $datasource_value ) ) {
				$selected_terms = $datasource_value['value'];
			}
			$selection_mode = '';
			if ( is_array( $datasource_value ) && array_key_exists( 'selection_mode', $datasource_value ) ) {
				$selection_mode = $datasource_value['selection_mode'];
			}
			if ( $selection_mode === 'OR' ) {
				$selection_mode = '';
			}
		
			$datasources = foogallery_gallery_datasources();
			$datasource = $datasources[ $this->datasource_name ];
			$terms = get_terms( $this->taxonomy, array( 'hide_empty' => false ) );
			$term_count = count( $terms );
		
			if ( $term_count > 0 ) {

				?>
				<p><?php printf( esc_html__( 'Select %s from the list below. The gallery will then dynamically load all attachments that are assigned to the selected items.', 'foogallery' ), esc_html( $datasource['name'] ) ); ?></p>
				<ul data-datasource="<?php echo esc_attr( $this->datasource_name ); ?>" data-taxonomy="<?php echo esc_attr( $this->taxonomy ); ?>" style="overflow: auto; clear: both;">
					<?php
		
					foreach ( $terms as $term ) {
						$selected = in_array( $term->term_id, $selected_terms );
						?>
						<li class="datasource-taxonomy <?php echo esc_attr( $this->datasource_name ); ?>" style="display: inline-block; margin-right: 5px;">
						<a href="#" class="button button-small<?php echo $selected ? ' button-primary' : ''; ?>"
						   data-term-id="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</li>
						<?php
					}
		
					?>
				</ul>
		
				<div style="clear: both;"></div> <!-- Clearfix to ensure the next section starts on a new line -->
		
				<!-- Selection Mode Section -->
				<div class="foogallery-taxonomy-selection-mode-<?php echo esc_attr( $this->datasource_name ); ?>" style="margin-top: 20px;">
					<p><?php esc_html_e( 'Selection mode changes which images are included in the gallery based off either a union (OR) or an intersect (AND) mode. If you choose AND, then only the images that have ALL the terms will be included in the gallery.', 'foogallery' ); ?></p>
					
					<h4><?php esc_html_e( 'Select Selection Mode:', 'foogallery' ); ?></h4>
					<label>
						<input type="radio" name="selection_mode" value="" <?php checked( $selection_mode, '' ); ?>> <?php esc_html_e( 'OR (Union)', 'foogallery' ); ?>
					</label>
					<label style="margin-left: 10px;">
						<input type="radio" name="selection_mode" value="AND" <?php checked( $selection_mode, 'AND' ); ?>> <?php esc_html_e( 'AND (Intersect)', 'foogallery' ); ?>
					</label>
				</div>
				<!-- End Selection Mode Section -->
		
				<?php
		
			} else {
				echo '<p>' . sprintf( esc_html__( 'We found no %s for you to choose. You will need to create a few first, by clicking the link below. Once you have created them, you can click the reload button above.', 'foogallery' ), esc_html( $datasource['name'] ) ) . '</p>';
			}
		
			$taxonomy_url = admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy );
			echo '<div style="clear: both;"></div><p><a target="_blank" href="' . esc_url( $taxonomy_url ) . '">' . sprintf( esc_html__( 'Manage your %s', 'foogallery' ), esc_html( $datasource['name'] ) ) . '</a></p>';
		}
				

		/**
		 * Output the html required by the datasource in order to add item(s)
		 * @param FooGallery $gallery
		 */
		function render_datasource_item( $gallery ) {
			$datasources = foogallery_gallery_datasources();
            // Check if the datasource actually exists!
            if ( !array_key_exists( $this->datasource_name, $datasources ) ) {
                return;
            }
			$datasource = $datasources[$this->datasource_name];

			$html = isset( $gallery->datasource_value['html'] ) ? $gallery->datasource_value['html'] : '';
			$show_container = isset( $gallery->datasource_name ) && $this->datasource_name === $gallery->datasource_name;
			$show_media_button = isset( $datasource['show_media_button'] ) && true === $datasource['show_media_button'];

			$selection_mode = isset( $gallery->datasource_value['selection_mode'] ) ? $gallery->datasource_value['selection_mode'] : '';
			if ( empty( $selection_mode ) ) {
				$selection_mode = 'OR';
			}
			?>
			<script type="text/javascript">
				jQuery(function ($) {
					$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
						$('.foogallery-datasource-taxonomy-<?php echo esc_js( $this->taxonomy ); ?>').hide();
						if ( activeDatasource !== '<?php echo esc_js( $this->datasource_name ); ?>' ) {
							$('.foogallery-datasource-modal-container-inner.<?php echo esc_js( $this->datasource_name ); ?>').find('a.button-primary').removeClass('button-primary');
						}
					});

					$(document).on('change', '.foogallery-taxonomy-selection-mode-<?php echo esc_js( $this->datasource_name ); ?> input[name="selection_mode"]', function() {
						$('.foogallery-datasource-taxonomy-selection-mode-<?php echo esc_js( $this->datasource_name ); ?>').html( $(this).val() );

						//Make sure we get the correct data.
						foogallery_datasource_taxonomy_set_data( '<?php echo esc_js( $this->datasource_name ); ?>', '<?php echo esc_js( $this->taxonomy ); ?>' );

						$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );
					});

					$(document).on('foogallery-datasource-changed-<?php echo esc_js( $this->datasource_name ); ?>', function() {
						var $container = $('.foogallery-datasource-taxonomy-<?php echo esc_js( $this->taxonomy ); ?>');

						var value = document.foogallery_datasource_value_temp;
						var selectionMode = $('.foogallery-taxonomy-selection-mode-<?php echo esc_js( $this->datasource_name ); ?> input[name="selection_mode"]:checked').val();
						if ( selectionMode === 'AND' ) {
							value.selection_mode = 'AND';
						} else {
							value.selection_mode = 'OR';
						}

						$container.find( '.foogallery-datasource-taxonomy-selection-mode' ).html( selectionMode );

						//set the datasource value
						$('#_foogallery_datasource_value').val(JSON.stringify(value));

						$container.find('.foogallery-items-html').html(document.foogallery_datasource_value_temp.html);

						$container.find('.foogallery-datasource-taxonomy-selection-mode-<?php echo esc_js( $this->datasource_name ); ?>').html(value.selection_mode)

						$container.show();

						FOOGALLERY.showHiddenAreas(false);

						$('.foogallery-attachments-list-container').addClass('foogallery-hidden');

						$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
					});
				});
			</script>
			<div class="foogallery-datasource-taxonomy foogallery-datasource-taxonomy-<?php echo esc_attr( $this->taxonomy ); ?>" data-media-title="<?php echo esc_attr__( 'Media Library', 'foogallery' ); ?>" data-media-button="<?php echo esc_attr__( 'Close', 'foogallery' ); ?>" <?php echo $show_container ? '' : 'style="display:none" '; ?>>
				<h3><?php echo sprintf( esc_html__( 'Datasource : %s', 'foogallery' ), esc_html( $datasource['name'] ) ); ?></h3>
				<p><?php echo sprintf( esc_html__( 'This gallery will be dynamically populated with all attachments assigned to the following %s:', 'foogallery' ), esc_html( $datasource['name'] ) ); ?></p>
				
				<div class="foogallery-items-html"><?php echo wp_kses_post( $html ); ?></div>

				<p>
					<?php esc_html_e( 'Selection Mode:', 'foogallery' ); ?>
					<strong class="foogallery-datasource-taxonomy-selection-mode-<?php echo esc_attr( $this->datasource_name ); ?>"><?php echo esc_html( $selection_mode ); ?></strong>
				</p>

				<button type="button" class="button edit" data-datasource="<?php echo esc_attr( $this->datasource_name ); ?>">
					<?php echo sprintf( esc_html__( 'Change %s', 'foogallery' ), esc_html( $datasource['name'] ) ); ?>
				</button>
				
				<button type="button" class="button remove">
					<?php echo sprintf( esc_html__( 'Remove All %s', 'foogallery' ), esc_html( $datasource['name'] ) ); ?>
				</button>
				
				<?php if ( $show_media_button ) { ?>
				<button type="button" class="button media">
					<?php esc_html_e( 'Open Media Library', 'foogallery' ); ?>
				</button>
				<?php } ?>
				
				<button type="button" class="button bulk_media_management">
					<?php esc_html_e( 'Bulk Taxonomy Manager', 'foogallery' ); ?>
				</button>
				
				<button type="button" class="button help">
					<?php esc_html_e( 'Show Help', 'foogallery' ); ?>
				</button>

				<div style="display: none" class="foogallery-datasource-taxonomy-help">
					<h4><?php echo sprintf( esc_html__( '%s Datasource Help', 'foogallery' ), esc_html( $datasource['name'] ) ); ?></h4>
					<p><?php echo sprintf( esc_html__( 'You can change which %s are assigned to this gallery by clicking "Change %s".', 'foogallery' ), esc_html( $datasource['name'] ), esc_html( $datasource['name'] ) ); ?></p>
					<p><?php echo sprintf( esc_html__( 'You can remove all %s from this gallery by clicking "Remove All %s".', 'foogallery' ), esc_html( $datasource['name'] ), esc_html( $datasource['name'] ) ); ?></p>
					<?php if ( $show_media_button ) { ?>
					<p><?php echo sprintf( esc_html__( 'You can assign %s to attachments within the WordPress Media Library. Launch by clicking "Open Media Library".', 'foogallery' ), esc_html( $datasource['name'] ) ); ?></p>
					<?php } ?>
					<p><?php echo sprintf( esc_html__( 'When an attachment is assigned to one of the %s, it will automatically be shown in the gallery.', 'foogallery' ), esc_html( $datasource['name'] ) ); ?></p>
					<p><?php esc_html_e( 'Click on the "Gallery Preview" to see which attachments will be loaded into the gallery.', 'foogallery' ); ?></p>
				</div>
			</div>
			<?php
		}
    }
}