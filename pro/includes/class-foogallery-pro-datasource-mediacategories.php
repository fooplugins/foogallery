<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Tag Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_MediaCategories' ) ) {

    class FooGallery_Pro_Datasource_MediaCategories extends FooGallery_Pro_Datasource_Taxonomy_Base {
    	public function __construct() {
			parent::__construct( 'media_categories', FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
		}
    }
}