<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Taxonomy
 */
namespace FooPlugins\FooGallery\Pro;

use FooPlugins\FooGallery\FooGallery_Datasource_MediaLibrary_Query_Helper;
use FooPlugins\FooGallery\FooGalleryAttachment;

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

                $helper = new FooGallery_Datasource_MediaLibrary_Query_Helper();

                //never get the cached attachments if we doing a preview
                if ( $foogallery_gallery_preview ) {
                    $cached_attachments = false;
                } else {
                    //check if there is a cached list of attachments
                    $cached_attachments = get_post_meta($foogallery->ID, $cache_post_meta_key, true);
                }

				if ( empty( $cached_attachments ) ) {
					$terms            = $datasource_value['value'];
					$field			  = array_key_exists( 'field', $datasource_value ) ? $datasource_value['field'] : 'term_id';
					$attachments      = $helper->query_attachments( $foogallery, array(
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field'    => $field,
								'terms'    => $terms,
							),
						)
					) );

					$attachment_ids = array();
					foreach ( $attachments as $attachment ) {
						$attachment_ids[] = $attachment->ID;
					}
					//save a cached list of attachments
					update_post_meta( $foogallery->ID, $cache_post_meta_key, $attachment_ids );

					if ( $enhanced_cache ) {
						update_post_meta( $foogallery->ID, FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_enhanced', true );
                    }
				} else {
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
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) {

            $selected_terms = array();
            if ( is_array( $datasource_value ) && array_key_exists( 'value', $datasource_value ) ) {
                $selected_terms = $datasource_value['value'];
            }

            $datasources = foogallery_gallery_datasources();
			$datasource = $datasources[$this->datasource_name];
            $terms = get_terms( $this->taxonomy, array('hide_empty' => false) );
            $term_count = count( $terms );

            if ( $term_count > 0 ) {

                ?>
                <p><?php printf(__('Select %s from the list below. The gallery will then dynamically load all attachments that are assigned to the selected items.', 'foogallery'), $datasource['name']); ?></p>
                <ul data-taxonomy="<?php echo $this->taxonomy; ?>">
                    <?php

                    foreach ($terms as $term) {
                        $selected = in_array($term->term_id, $selected_terms);
                        ?>
                        <li class="datasource-taxonomy <?php echo $this->datasource_name; ?>">
                        <a href="#" class="button button-small<?php echo $selected ? ' button-primary' : ''; ?>"
                           data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
                        </li><?php
                    }

                    ?>
                </ul>
                <?php

            } else {
                echo '<p>' . sprintf( __( 'We found no %s for you to choose. You will need to create a few first, by clicking the link below. Once you have created them, you can click the reload button above.', 'foogallery' ), $datasource['name']) . '</p>';
            }

            $taxonomy_url = admin_url( 'edit-tags.php?taxonomy=' . $this->taxonomy );
            echo '<div style="clear: both"></div><p><a target="_blank" href="' . $taxonomy_url . '">' . sprintf( __('Manage your %s', 'foogallery'), $datasource['name'] ) . '</a></p>';
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
			?>
			<script type="text/javascript">
				jQuery(function ($) {
					$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
						$('.foogallery-datasource-taxonomy-<?php echo $this->taxonomy; ?>').hide();
						if ( activeDatasource !== '<?php echo $this->datasource_name; ?>' ) {
							$('.foogallery-datasource-modal-container-inner.<?php echo $this->datasource_name; ?>').find('a.button-primary').removeClass('button-primary');
						}
					});

					$(document).on('foogallery-datasource-changed-<?php echo $this->datasource_name; ?>', function() {
						var $container = $('.foogallery-datasource-taxonomy-<?php echo $this->taxonomy; ?>');

						//set the datasource value
						$('#_foogallery_datasource_value').val(JSON.stringify(document.foogallery_datasource_value_temp));

						$container.find('.foogallery-items-html').html(document.foogallery_datasource_value_temp.html);

						$container.show();

						FOOGALLERY.showHiddenAreas(false);

						$('.foogallery-attachments-list-container').addClass('hidden');

						$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
					});
				});
			</script>
			<div class="foogallery-datasource-taxonomy foogallery-datasource-taxonomy-<?php echo $this->taxonomy; ?>" data-media-title="<?php _e('Media Library', 'foogallery'); ?>" data-media-button="<?php _e('Close', 'foogallery'); ?>" <?php echo $show_container ? '' : 'style="display:none" '; ?>>
				<h3><?php echo sprintf( __('Datasource : %s', 'foogallery'), $datasource['name'] ); ?></h3>
				<p><?php echo sprintf( __('This gallery will be dynamically populated with all attachments assigned to the following %s:', 'foogallery'), $datasource['name'] ); ?></p>
				<div class="foogallery-items-html"><?php echo $html; ?></div>
				<button type="button" class="button edit" data-datasource="<?php echo $this->datasource_name; ?>">
					<?php echo sprintf( __( 'Change %s', 'foogallery' ), $datasource['name'] ); ?>
				</button>
				<button type="button" class="button remove">
					<?php echo sprintf( __( 'Remove All %s', 'foogallery' ), $datasource['name'] ); ?>
				</button>
				<?php if ( $show_media_button ) { ?>
				<button type="button" class="button media">
					<?php _e( 'Open Media Library', 'foogallery' ); ?>
				</button>
				<?php } ?>
				<button type="button" class="button bulk_media_management">
					<?php _e( 'Bulk Taxonomy Manager', 'foogallery' ); ?>
				</button>
				<button type="button" class="button help">
					<?php _e( 'Show Help', 'foogallery' ); ?>
				</button>
				<div style="display: none" class="foogallery-datasource-taxonomy-help">
					<h4><?php echo sprintf( __('%s Datasource Help', 'foogallery'), $datasource['name'] ); ?></h4>
					<p><?php echo sprintf( __('You can change which %s are assigned to this gallery by clicking "Change %s".', 'foogalley' ), $datasource['name'], $datasource['name'] ); ?></p>
					<p><?php echo sprintf( __('You can remove all %s from this gallery by clicking "Remove All %s".', 'foogalley' ), $datasource['name'], $datasource['name'] ); ?></p>
					<?php if ( $show_media_button ) { ?>
					<p><?php echo sprintf( __('You can assign %s to attachments within the WordPress Media Library. Launch by clicking "Open Media Library".', 'foogalley' ), $datasource['name'] ); ?></p>
					<?php } ?>
					<p><?php echo sprintf( __('When an attachment is assigned to one of the %s, it will automatically be shown in the gallery.', 'foogalley' ), $datasource['name'] ); ?></p>
					<p><?php echo __('Click on the "Gallery Preview" to see which attachments will be loaded into the gallery.', 'foogallery'); ?></p>
				</div>
			</div><?php
		}
    }
}