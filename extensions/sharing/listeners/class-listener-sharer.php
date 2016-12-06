<?php
/**
 * FooGallery sharing listener
 */

if ( ! class_exists( 'FooGallery_Sharing_Listener_Sharer' ) ) {

    class FooGallery_Sharing_Listener_Sharer {

        function __construct() {
            add_action( 'template_redirect', array($this, 'listen') );
        }

        function listen() {
            global $wp_query;

            //make sure we are dealing with a share
            if ( empty($wp_query->query_vars[FOOGALLERY_SHARING_ARG]) ) {
                return;
            }

            //save the share info to the DB and return a share ID

            //return the share url using the share ID


        }
    }
}