<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Category Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_MediaCategories' ) ) {

	class FooGallery_Pro_Datasource_MediaCategories extends FooGallery_Pro_Datasource_Taxonomy_Base {
		public function __construct() {
			parent::__construct( 'media_categories', FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
			add_action( 'foogallery_gallery_datasources', array($this, 'add_datasource'), 6 );
		}

		/**
		 * Add the Media Categories Datasource
		 * @param $datasources
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['media_categories'] = array(
				'id'     => 'media_categories',
				'name'   => __( 'Media Categories', 'foogallery' ),
				'menu'  => __( 'Media Categories', 'foogallery' ),
				'public' => true,
				'show_media_button' => true
			);

			return $datasources;
		}
	}
}