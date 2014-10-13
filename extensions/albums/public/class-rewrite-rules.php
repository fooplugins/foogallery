<?php
/**
 * FooGallery Album Rewrite Rules
 */
if (!class_exists('FooGallery_Album_Rewrite_Rules')) {

    class FooGallery_Album_Rewrite_Rules {

        function __construct() {
	        add_action( 'init',  array( $this, 'add_gallery_endpoint' ) );
	        //add_action( 'template_redirect', array( $this, 'template_redirect' ) );
        }

	    function add_gallery_endpoint() {
		    add_rewrite_endpoint( 'gallery', EP_ALL );
	    }

//	    function template_redirect() {
//		    global $foogallery_album_gallery;
//		    $foogallery_album_gallery = get_query_var( 'gallery' );
//	    }
    }
}