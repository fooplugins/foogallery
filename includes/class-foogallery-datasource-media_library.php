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
			add_filter( 'foogallery_attachment_get_posts_args', array( $this, 'apply_query_args' ) );
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

				$attachments = array_map( array( $this, 'build_attachment' ), $attachment_posts );
			}

			return $attachments;
		}

		function apply_query_args( $query_args ) {
			global $current_foogallery_arguments;

			//check if a limit has been applied
			if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments['limit'] ) ) {
				$query_args['posts_per_page'] = $current_foogallery_arguments['limit'];
			}

			//check if an offset has been applied
			if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments['offset'] ) ) {
				$query_args['offset'] = $current_foogallery_arguments['offset'];
			}

			return $query_args;
		}

		function build_attachment( $attachment_post ) {
			$attachment = apply_filters( 'foogallery_attachment_load', FooGalleryAttachment::get( $attachment_post ), $this->foogallery );
			return $attachment;
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
            $attachment_id = $this->find_featured_attachment_id();

            if ( $attachment_id ) {
                return FooGalleryAttachment::get_by_id( $attachment_id );
            }

            return false;
		}

        private function find_featured_attachment_id() {
            $attachment_id = get_post_thumbnail_id( $this->foogallery->ID );

            //if no featured image could be found then get the first image
            if ( ! $attachment_id && $this->foogallery->attachment_ids ) {
                $attachment_id_values = array_values( $this->foogallery->attachment_ids );
                $attachment_id = array_shift( $attachment_id_values );
            }
            return $attachment_id;
        }
	}
}