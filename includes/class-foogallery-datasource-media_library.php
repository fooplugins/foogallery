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

			add_filter( 'foogallery_datasource_attachment_count-media_library', array( $this, 'attachment_count' ), 10, 2 );

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
		public function count() {
			// TODO: Implement count() method.
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 * @return array(FooGalleryAttachment)
		 */
		public function getAttachments() {
			// TODO: Implement getAttachments() method.
		}

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 * @return FooGalleryAttachment
		 */
		public function getFeaturedAttachment() {
			// TODO: Implement getFeaturedAttachment() method.
		}
	}
}