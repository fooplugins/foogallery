<?php

//CSS loader helper class

//1.   enqueue CSS files that are needed by the page at any stage in the WordPress page lifecycle
//     eg. from shortcodes or at any hook within the page
//1.1  Just before the wp_enqueue_scripts hook, check the 'css_must_add' post meta value.
//1.2  If there are any items in the post meta value, add them to the queue.
//2.   At wp_enqueue_scripts, if there is anything in the queue, then register it and enqueue
//2.1  Remove these items from the queue
//3    At wp_footer, if there are anymore files in the queue, then add them to the footer
//3.1  Save these files to a 'css_must_add' post_meta value that can be check on the next page load
//3.2  Remove these items from the queue. The queue should now be empty.

//4.   Also hook into the save_post hook and clear the 'css_must_add' post meta value to make sure redundant css files are not added to the posts forever

//5.   Have a global clear all that can clear all 'css_must_add' values on all posts.

if (!class_exists('WP_CSS_Safe_Loader')) {
    class WP_CSS_Safe_Loader {
        function __construct() {
            //add_action( 'wp_enqueue_scripts', array( $this, 'check_before_equeue' ), 1 );
            //add_action( 'wp_enqueue_scripts', array( $this, 'equeue' ), 11 );
        }

        function check_before_enqueue() {
            //wp_enqueue_style()
        }
    }
}