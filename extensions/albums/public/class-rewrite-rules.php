<?php
/**
 * FooGallery Album Rewrite Rules
 */
if (!class_exists('FooGallery_Album_Rewrite_Rules')) {

    class FooGallery_Album_Rewrite_Rules {

        function __construct() {
	        add_action( 'init',  array( $this, 'add_gallery_endpoint' ) );
        }

	    function add_gallery_endpoint() {
		    add_rewrite_endpoint( 'gallery', EP_ALL );
	    }
    }
}