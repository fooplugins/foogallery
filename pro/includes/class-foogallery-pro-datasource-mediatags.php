<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Tag Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_MediaTags' ) ) {

    class FooGallery_Pro_Datasource_MediaTags extends FooGallery_Pro_Datasource_Taxonomy_Base {
    	public function __construct() {
			parent::__construct( 'media_tags', FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
			add_action( 'foogallery_gallery_datasources', array($this, 'add_datasource'), 5 );
		}

		/**
		 * Add the Media Tag Datasource
		 * @param $datasources
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['media_tags'] = array(
				'id'                => 'media_tags',
				'name'              => __( 'Media Tags', 'foogallery' ),
				'menu'              => __( 'Media Tags', 'foogallery' ),
				'public'            => true,
				'show_media_button' => true
			);

			return $datasources;
		}
    }
}