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

            //TODO : refactor this so that the count is cached
            return count( $this->getAttachments() ) > 0;
        }

        /**
         * Returns an array of FooGalleryAttachments from the datasource
         * @return array(FooGalleryAttachment)
         */
        public function getAttachments() {
            $attachments = array();

            if ( ! empty( $this->foogallery->datasouce_value ) ) {

                global $current_foogallery_arguments;

                //check if a sorting override has been applied
                if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments['sort'] ) ) {
                    $this->foogallery->sorting = $current_foogallery_arguments['sort'];
                }

                $datasource_value = json_decode( $this->foogallery->datasouce_value );
                $terms = $datasource_value->value;

                //add_action( 'pre_get_posts', array( $this, 'force_gallery_ordering' ), 99 );
                //add_action( 'pre_get_posts', array( $this, 'force_suppress_filters' ), PHP_INT_MAX );

                $attachment_query_args = apply_filters( 'foogallery_attachment_get_posts_args', array(
                    'post_type'      => 'attachment',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
                            'field'    => 'term_id',
                            'terms'    => $terms,
                        ),
                    ),
                    'orderby'        => foogallery_sorting_get_posts_orderby_arg( $this->foogallery->sorting ),
                    'order'          => foogallery_sorting_get_posts_order_arg( $this->foogallery->sorting )
                ) );

                $attachment_posts = get_posts( $attachment_query_args );

                //remove_action( 'pre_get_posts', array( $this, 'force_gallery_ordering' ), 99 );
                //remove_action( 'pre_get_posts', array( $this, 'force_suppress_filters' ), PHP_INT_MAX );

                $attachments = array_map( array( $this, 'build_attachment' ), $attachment_posts );
            }

            return $attachments;
        }

        function build_attachment( $attachment_post ) {
            $attachment = apply_filters( 'foogallery_attachment_load', FooGalleryAttachment::get( $attachment_post ), $this->foogallery );
            return $attachment;
        }

        /**
         * Returns the featured FooGalleryAttachment from the datasource
         * @return bool|FooGalleryAttachment
         */
        public function getFeaturedAttachment() {
            return false;
        }

        /**
         * Returns a serialized string that represents the media in the datasource.
         * This string is persisted when saving a FooGallery
         *
         * @return string
         */
        public function getSerializedData() {
            return '';
        }
    }
}