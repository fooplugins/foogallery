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
			add_action( 'foogallery-datasource-modal-content_media_tags', array($this, 'render_datasource_modal_content'), 10, 2 );
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
			$cached_attachments = get_post_meta( $foogallery->ID, FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS, true );

			if ( is_array( $cached_attachments ) ) {
				return count( $cached_attachments );
			}

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
			if ( ! empty( $foogallery->datasource_value ) ) {

				$helper = new FooGallery_Datasource_MediaLibrary_Query_Helper();

				//check if there is a cached list of attachments
				$cached_attachments = get_post_meta( $foogallery->ID, FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS, true );

				if ( empty( $cached_attachments ) ) {
					$datasource_value = json_decode( $foogallery->datasource_value );
					$terms            = $datasource_value->value;
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
					update_post_meta( $foogallery->ID, FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS, $attachment_ids );
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
			$cached_attachments = get_post_meta( $foogallery->ID, FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS, true );

			if ( is_array( $cached_attachments ) && count( $cached_attachments ) > 0 ) {
				return FooGalleryAttachment::get_by_id( $cached_attachments[0] );
			}

			return false;
		}

		/**
		 * Output the datasource modal content
		 * @param $datasource
		 */
		function render_datasource_modal_content( $foogallery_id ) {
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
							$('#foogallery_datasource_value').val( JSON.stringify( {
								"datasource" : "media_tags",
								"value" : taxonomy_values,
								"text" : text
							} ) );
						} else {
							$('.foogallery-datasource-modal-insert').attr('disabled','disabled');

							//clear the selection
							$('#foogallery_datasource_value').val('');
						}
					});
				});
			</script>
			<p><?php _e('Select a media tag from the list below. The gallery will then dynamically load all attachments that are associated to that tag.', 'foogallery'); ?></p>
			<ul>
				<?php

				$terms = get_terms( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, array('hide_empty' => false) );

				foreach($terms as $term) {
					?><li class="datasource-taxonomy media_tags">
					<a href="#" data-term-id="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
					</li><?php
				}

				?>
			</ul>
			<?php
		}
    }
}