<?php
/**
 * FooGallery Pro Datasources
 */
if ( ! class_exists( 'FooGallery_Pro_Datasources' ) ) {

    class FooGallery_Pro_Datasources {
        function __construct() {
            //add the datasources
            //add_action( 'foogallery_gallery_datasources', array($this, 'add_datasources') );
        }

        /**
         * Add the PRO datasources
         * @param $datasources
         * @return mixed
         */
        function add_datasources( $datasources ) {




			$datasources['dropbox'] = array(
				'id'     => 'dropbox',
				'name'   => __( 'DropBox', 'foogallery' ),
				'menu'  => __( 'DropBox', 'foogallery' ),
				'public' => true
			);

			$datasources['amazon'] = array(
				'id'     => 'amazon',
				'name'   => __( 'Amazon S3', 'foogallery' ),
				'menu'  => __( 'Amazon S3', 'foogallery' ),
				'public' => true
			);



			$datasources['instagram'] = array(
				'id'     => 'instagram',
				'name'   => __( 'Instagram', 'foogallery' ),
				'menu'  => __( 'Instagram', 'foogallery' ),
				'public' => true
			);

            return $datasources;
        }
    }
}
