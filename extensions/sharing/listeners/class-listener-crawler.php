<?php
/**
 * FooGallery sharing network crawler listener
 */

if ( ! class_exists( 'FooGallery_Sharing_Listener_Crawler' ) ) {

    class FooGallery_Sharing_Listener_Crawler {

        function __construct() {
            add_action( 'template_redirect', array($this, 'listen') );
        }

        function listen() {
            global $wp_query;

            //make sure we are dealing with a share
            if ( empty($wp_query->query_vars[FOOGALLERY_SHARING_PARAM]) ) {
                return;
            }

            if ( strtolower( $_SERVER['REQUEST_METHOD'] ) === 'get' ) { // crawlers only make GET requests

                //get the share ID
                $id = foogallery_sharing_extract_share_request();

                //if we have a share ID...
                if ( $id !== false ) {

                    //check if a network crawler is active
                    foreach ( foogallery_sharing_supported_networks() as $name => $attributes ) {
                        if ( array_key_exists( 'ua_regex', $attributes ) ) {
                            //check for a user agent match
                            if ( preg_match( $attributes['ua_regex'], $_SERVER['HTTP_USER_AGENT'] ) ) {
                                do_action( 'foogallery_sharing_crawler_handle_request', $id, $this );
                                return;
                            }
                        }
                    }
                }
            }
        }
    }
}
