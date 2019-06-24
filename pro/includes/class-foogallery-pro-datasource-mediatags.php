<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Tag Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_MediaTags' ) ) {

    class FooGallery_Pro_Datasource_MediaTags {

    	public function __construct() {
			add_filter( 'foogallery_datasource_media_tags_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_media_tags_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_media_tags_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );
			add_action( 'foogallery-datasource-modal-content_media_tags', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
			add_action( 'added_term_relationship', array( $this, 'change_term_relationship_clear_datasource_cached_attachments' ), 10, 3 );
			add_action( 'deleted_term_relationships', array( $this, 'change_term_relationship_clear_datasource_cached_attachments' ), 10, 3 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_attachments' ) );
		}

		public function before_save_gallery_datasource_clear_datasource_cached_attachments( $foogallery_id ) {
            //clear any previously cached post meta for the gallery
            $previous_datasource_value = get_post_meta( $foogallery_id, FOOGALLERY_META_DATASOURCE_VALUE, true );

            if ( is_array( $previous_datasource_value ) ) {
                $taxonomy = $previous_datasource_value['taxonomy'];
                $cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $taxonomy;
                delete_post_meta($foogallery_id, $cache_post_meta_key);
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
            //delete all cached attachments for the taxonomy
            $cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $taxonomy;

            delete_post_meta_by_key( $cache_post_meta_key );
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
					$attachments      = $helper->query_attachments( $foogallery, array(
						'tax_query' => array(
							array(
								'taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
								'field'    => 'term_id',
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
				return FooGalleryAttachment::get_by_id( $attachments[0]->ID );
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
			?>
			<style>
				.datasource-taxonomy {
					position: relative;
					float: left;
					margin-right: 10px;
				}

				.datasource-taxonomy a {
					border: 1px solid #ddd;
					border-radius: 5px;
					padding: 4px 8px;
					display: block;
					padding: 10px;
					text-align: center;
					text-decoration: none;
					font-size: 1.2em;
				}

				.datasource-taxonomy a.active {
					color: #fff;
					background: #0085ba;
					border-color: #0073aa #006799 #006799;
				}
			</style>
			<script type="text/javascript">
				jQuery(function ($) {
					$('.foogallery-datasource-modal-container').on('click', '.datasource-taxonomy a', function (e) {
						e.preventDefault();
						$(this).toggleClass('active');
						$selected = $(this).parents('ul:first').find('a.active');

						//validate if the OK button can be pressed.
						if ( $selected.length > 0 ) {
							$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

							var taxonomy_values = [],
								taxonomies = [];

							$selected.each(function() {
								taxonomy_values.push( $(this).data('termId') );
								taxonomies.push( $(this).text() );
							});

							var text = '<strong><?php _e( 'Media Tags', 'foogallery' );?>:</strong><br />' + taxonomies.join(', ');

							//set the selection
							$('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val( JSON.stringify( {
								"taxonomy" : "<?php echo FOOGALLERY_ATTACHMENT_TAXONOMY_TAG; ?>",
								"value" : taxonomy_values,
								"text" : text
							} ) );
						} else {
							$('.foogallery-datasource-modal-insert').attr('disabled','disabled');

							//clear the selection
							$('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');
						}
					});
				});
			</script>
			<p><?php _e('Select a media tag from the list below. The gallery will then dynamically load all attachments that are associated to that tag.', 'foogallery'); ?></p>
			<ul>
				<?php

				$terms = get_terms( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, array('hide_empty' => false) );

				foreach($terms as $term) {
				    $selected = in_array( $term->term_id, $selected_terms );
					?><li class="datasource-taxonomy media_tags">
					<a href="#" <?php echo $selected ? 'class="active"' : ''; ?> data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
					</li><?php
				}

				?>
			</ul>
			<?php
		}

        /**
         * Output the html required by the datasource in order to add item(s)
         * @param FooGallery $gallery
         */
		function render_datasource_item( $gallery ) { ?>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('.foogallery-datasource-items-list-media_tags').on('click', '.datasource-info a.remove', function (e) {
                        e.preventDefault();

                        //clear the items
                        $(this).parents('.datasource-info').hide();

                        //clear the datasource value
                        $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');

                        //clear the datasource
                        $('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val('');

                        //deselect any media tag buttons in the modal
                        $('.foogallery-datasource-modal-container .datasource-taxonomy a.active').removeClass('active');

                        //make sure the modal insert button is not active
                        $('.foogallery-datasource-modal-insert').attr('disabled','disabled');

                        FOOGALLERY.showHiddenAreas( true );

                        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', '.datasource-info a.edit', function (e) {
                        e.preventDefault();

                        $('.foogallery-datasources-modal-wrapper').show();

                        //select the media tags datasource
                        $('.foogallery-datasource-modal-selector[data-datasource="media_tags"]').click();
                    });

                    $(document).on('foogallery-datasource-changed-media_tags', function() {
                        var $template = $($('#foogallery-datasource-template-media_tags').val()),
                            datasource_value = $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val();

                        if ( datasource_value.length > 0 ) {
                            var datasource_value_json = JSON.parse( datasource_value );

                            $template.find('.centered').html(datasource_value_json.text);

                            $('.foogallery-datasource-items-list-media_tags').html($template);

                            FOOGALLERY.showHiddenAreas( false );

                            $('.foogallery-attachments-list').addClass('hidden');

                            $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                        }
                    });
                });
            </script>
            <textarea style="display: none;" id="foogallery-datasource-template-media_tags">
                <li class="datasource-info">
                    <div>
                        <div class="centered"></div>
                        <a class="edit" href="#" title="<?php _e( 'Edit Media Tags', 'foogallery' ); ?>">
                            <span class="dashicons dashicons-info"></span>
                        </a>
                        <a class="remove" href="#" title="<?php _e( 'Remove from gallery', 'foogallery' ); ?>">
                            <span class="dashicons dashicons-dismiss"></span>
                        </a>
                    </div>
                </li>
            </textarea>
            <ul class="foogallery-datasource-items-list-media_tags">
        <?php
            //if we have a datasource set and its for media tags then output the item
            if ( isset( $gallery->datasource_name) && 'media_tags' === $gallery->datasource_name ) {
                ?>
                <li class="datasource-info">
                    <div>
                        <div class="centered"><?php echo $gallery->datasource_value['text']; ?></div>
                        <a class="edit" href="#" title="<?php _e( 'Edit Media Tags', 'foogallery' ); ?>">
                            <span class="dashicons dashicons-info"></span>
                        </a>
                        <a class="remove" href="#" title="<?php _e( 'Remove from gallery', 'foogallery' ); ?>">
                            <span class="dashicons dashicons-dismiss"></span>
                        </a>
                    </div>
                </li>
                <?php
            } ?>
            </ul>
            <div style="clear: both;"></div><?php
		}
    }
}