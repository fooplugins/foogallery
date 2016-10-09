<?php
/**
 * The default Gallery Datasource which pulls attachments from the WP media library
 */
if ( ! class_exists( 'FooGalleryDatasource_MediaLibrary' ) ) {

	class FooGalleryDatasource_MediaLibrary implements IFooGalleryDatasource {

		/**
		 * @var FooGallery
		 */
		private $foogallery;

		/**
		 * Sets the FooGallery object we are dealing with
		 *
		 * @param $foogallery FooGallery
		 */
		public function setGallery( $foogallery ) {
			$this->foogallery = $foogallery;
		}

		function __construct() {
			//attachment_count
			//attachment_id_csv
			//attachments
			//find_featured_attachment_id
			//featured_attachment
			//featured_image_html
		}

		/**
		 * Returns the number of attachments used from the media library
		 * @return int
		 */
		public function getCount() {
			return sizeof( $this->foogallery->attachment_ids );
		}

		/**
		 * Returns a serialized string that represents the media in the datasource.
		 * This string is persisted when saving a FooGallery
		 *
		 * @return string
		 */
		public function getSerializedData() {
			if ( is_array( $this->foogallery->attachment_ids ) ) {
				return implode( ',', $this->foogallery->attachment_ids );
			}

			return '';
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 * @return array(FooGalleryAttachment)
		 */
		public function getAttachments() {
			$attachments = array();

			if ( ! empty( $this->foogallery->attachment_ids ) ) {

				add_action( 'pre_get_posts', array( $this, 'force_gallery_ordering' ), 99 );

				$attachment_query_args = apply_filters( 'foogallery_attachment_get_posts_args', array(
					'post_type'      => 'attachment',
					'posts_per_page' => -1,
					'post__in'       => $this->foogallery->attachment_ids,
					'orderby'        => foogallery_sorting_get_posts_orderby_arg( $this->foogallery->sorting ),
					'order'          => foogallery_sorting_get_posts_order_arg( $this->foogallery->sorting )
				) );

				$attachment_posts = get_posts( $attachment_query_args );

				remove_action( 'pre_get_posts', array( $this, 'force_gallery_ordering' ), 99 );

				$attachments = array_map( array( 'FooGalleryAttachment', 'get' ), $attachment_posts );
			}

			return $attachments;
		}

		/**
		 * This forces the attachments to be fetched using the correct ordering.
		 * Some plugins / themes override this globally for some reason, so this is a preventative measure to ensure sorting is correct
		 * @param $query WP_Query
		 */
		public function force_gallery_ordering( $query ) {
			//only care about attachments
			if ( array_key_exists( 'post_type', $query->query ) &&
				'attachment' === $query->query['post_type'] ) {
				$query->set( 'orderby', foogallery_sorting_get_posts_orderby_arg( $this->foogallery->sorting ) );
				$query->set( 'order', foogallery_sorting_get_posts_order_arg( $this->foogallery->sorting ) );
			}
		}

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 * @return bool|FooGalleryAttachment
		 */
		public function getFeaturedAttachment() {
			// TODO: Implement getFeaturedAttachment() method.
		}


	}
}