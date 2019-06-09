<?php
/**
 * FooGallery Pro Datasources
 */
if ( ! class_exists( 'FooGallery_Pro_Datasources' ) ) {

    class FooGallery_Pro_Datasources {
        function __construct() {
            //add the datasources
            add_action( 'foogallery_gallery_datasources', array($this, 'add_datasources') );
        }

        /**
         * Add the PRO datasources
         * @param $datasources
         * @return mixed
         */
        function add_datasources( $datasources ) {
            $datasources['media_tags'] = array(
                'id'     => 'media_tags',
                'name'   => __( 'Media Tags', 'foogalery' ),
                'menu'  => __( 'Media Tags', 'foogallery' ),
                'public' => true
            );

            $datasources['media_categories'] = array(
                'id'     => 'media_categories',
                'name'   => __( 'Media Categories', 'foogalery' ),
                'menu'  => __( 'Media Categories', 'foogallery' ),
                'public' => true
            );

            return $datasources;
        }
    }
}
