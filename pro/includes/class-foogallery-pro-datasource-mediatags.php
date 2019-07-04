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
                    background: #efefef;
					border-radius: 5px;
					padding: 4px 12px;
					display: block;
					text-align: center;
					text-decoration: none;
					font-size: 1.2em;
				}

				.datasource-taxonomy a.active {
					background: #bbb;
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
								taxonomies = [],
                                html = '';

							$selected.each(function() {
								taxonomy_values.push( $(this).data('termId') );
								taxonomies.push( $(this).text() );
                                html += '<li>' + $(this).text() + '</li>';
							});

							//set the selection
							$('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val( JSON.stringify( {
								"taxonomy" : "<?php echo FOOGALLERY_ATTACHMENT_TAXONOMY_TAG; ?>",
								"value" : taxonomy_values,
								"html" : '<ul>' + html + '</ul>'
							} ) );
						} else {
							$('.foogallery-datasource-modal-insert').attr('disabled','disabled');

							//clear the selection
							$('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');
						}
					});
				});
			</script>
			<p><?php _e('Select one or more Media Tags from the list below. The gallery will then dynamically load all attachments that are associated to the selected Media Tags.', 'foogallery'); ?></p>
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
            <style type="text/css">
                .foogallery-datasource-taxonomy {
                    padding: 20px;
                    text-align: center;
                }

                .foogallery-datasource-taxonomy ul {
                    list-style: none;
                    margin-bottom: 20px;
                }

                .foogallery-datasource-taxonomy ul li {
                    display: inline-block;
                    margin-right: 10px;
                    border-radius: 5px;
                    padding: 4px 12px;
                    text-align: center;
                    text-decoration: none;
                    font-size: 1.2em;
                    background: #bbb;
                }

                .foogallery-datasource-taxonomy-help h4 {
                    font-weight: bold;
                    text-decoration: underline;
                }
            </style>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.remove', function (e) {
                        e.preventDefault();

                        //hide the previous info
                        $(this).parents('.foogallery-datasource-taxonomy').hide();

                        //clear the datasource value
                        $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');

                        //clear the datasource
                        $('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val('');

                        //deselect any media tag buttons in the modal
                        $('.foogallery-datasource-modal-container .datasource-taxonomy a.active').removeClass('active');

                        //make sure the modal insert button is not active
                        $('.foogallery-datasource-modal-insert').attr('disabled','disabled');

                        FOOGALLERY.showHiddenAreas( true );

                        //ensure the preview will be refreshed
                        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.edit', function (e) {
                        e.preventDefault();

                        //show the modal
                        $('.foogallery-datasources-modal-wrapper').show();

                        //select the media tags datasource
                        $('.foogallery-datasource-modal-selector[data-datasource="media_tags"]').click();
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.media', function(e) {
                       e.preventDefault();

                        if (typeof(document.foogallery_media_tags_modal) !== 'undefined'){
                            document.foogallery_media_tags_modal.open();
                            return;
                        }

                        document.foogallery_media_tags_modal = wp.media({
                            frame: 'select',
                            title: '<?php _e('Assign Media Tags', 'foogallery'); ?>',
                            button: {
                                text: '<?php _e('Close', 'foogallery'); ?>'
                            },
                            library: {
                                type: 'image'
                            }
                        }).on( 'open', function() {
                            //ensure the preview will be refreshed
                            $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                        });

                        document.foogallery_media_tags_modal.open();
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.help', function(e) {
                        e.preventDefault();

                        $('.foogallery-datasource-taxonomy-help').toggle();
                    });

                    $(document).on('foogallery-datasource-changed-media_tags', function() {
                        var $container = $('.foogallery-datasource-taxonomy'),
                            datasource_value = $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val();

                        if ( datasource_value.length > 0 ) {
                            var datasource_value_json = JSON.parse( datasource_value );

                            $container.find('.foogallery-items-html').html(datasource_value_json.html);

                            $container.show();

                            FOOGALLERY.showHiddenAreas( false );

                            $('.foogallery-attachments-list').addClass('hidden');

                            $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                        }
                    });
                });
            </script>
            <ul class="foogallery-datasource-items-list-media_tags">
        <?php
			$html = isset( $gallery->datasource_value['html'] ) ? $gallery->datasource_value['html'] : '';
			$show_container = isset( $gallery->datasource_name) && 'media_tags' === $gallery->datasource_name; ?>
                <div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-taxonomy">
                    <h3><?php _e('Datasource : Media Tags', 'foogallery'); ?></h3>
                    <p><?php _e('This gallery will be dynamically populated with all attachments assigned to the following Media Tags:', 'foogallery'); ?></p>
                    <div class="foogallery-items-html"><?php echo $html; ?></div>
                    <button type="button" class="button edit">
                        <?php _e( 'Change Media Tags', 'foogallery' ); ?>
                    </button>
                    <button type="button" class="button remove">
                        <?php _e( 'Remove All Media Tags', 'foogallery' ); ?>
                    </button>
                    <button type="button" class="button media">
                        <?php _e( 'Open Media Library', 'foogallery' ); ?>
                    </button>
                    <button type="button" class="button help">
                        <?php _e( 'Show Help', 'foogallery' ); ?>
                    </button>
                    <div style="display: none" class="foogallery-datasource-taxonomy-help">
                        <h4><?php _e('Media Tags Datasource Help', 'foogallery'); ?></h4>
                        <p><?php _e('You can change which Media Tags are assigned to this gallery by clicking "Change Media Tags".', 'foogalley' ); ?></p>
                        <p><?php _e('You can remove all Media Tags from this gallery by clicking "Remove All Media Tags".', 'foogalley' ); ?></p>
                        <p><?php _e('You can assign Media Tags to attachments within the WordPress Media Library. Launch by clicking "Open Media Library".', 'foogalley' ); ?></p>
                        <p><?php _e('When an attachment is assigned to one of the Media Tags, it will automatically be shown in the gallery.', 'foogalley' ); ?></p>
                        <p><?php _e('Click on the "Gallery Preview" to see which attachments will be loaded into the gallery.', 'foogallery'); ?></p>
                    </div>
                </div>
                <?php
            } ?>
            </ul><?php
		}
    }
}