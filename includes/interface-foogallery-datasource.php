<?php
/**
 * FooGallery Datasource interface
 */
if ( ! interface_exists( 'IFooGalleryDatasource' ) ) {

	interface IFooGalleryDatasource {

		/**
		 * Sets the FooGallery object we are dealing with
		 *
		 * @param $foogallery FooGallery
		 */
		public function setGallery( $foogallery );

		/**
		 * Returns the number of images/videos in the datasource
		 * @return int
		 */
		public function count();

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 * @return array(FooGalleryAttachment)
		 */
		public function getAttachments();

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 * @return FooGalleryAttachment
		 */
		public function getFeaturedAttachment();

		//attachment_count
		//attachment_id_csv
		//attachments
		//find_featured_attachment_id
		//featured_attachment
		//featured_image_html
	}
}