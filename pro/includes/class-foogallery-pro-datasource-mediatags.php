<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Tag Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_MediaTags' ) ) {

    class FooGallery_Pro_Datasource_MediaTags extends FooGallery_Pro_Datasource_Taxonomy_Base {
    	public function __construct() {
			parent::__construct( 'media_tags', FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
		}
    }
}