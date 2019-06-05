<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Tag Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_MediaTags' ) ) {

    class FooGallery_Pro_Datasource_MediaTags implements IFooGalleryDatasource {

        /**
         * @var FooGallery
         */
        private $foogallery;

        /**
         * Sets the FooGa llery object we are dealing with
         *
         * @param $foogallery FooGallery
         */
        public function setGallery($foogallery) {
            $this->foogallery = $foogallery;
        }

        /**
         * Returns the number of images/videos in the datasource
         * @return int
         */
        public function getCount() {
            // TODO: Implement getCount() method.
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
         * @return bool|FooGalleryAttachment
         */
        public function getFeaturedAttachment() {
            // TODO: Implement getFeaturedAttachment() method.
        }

        /**
         * Returns a serialized string that represents the media in the datasource.
         * This string is persisted when saving a FooGallery
         *
         * @return string
         */
        public function getSerializedData() {
            // TODO: Implement getSerializedData() method.
        }
    }
}