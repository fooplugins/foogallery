<?php
/**
 * FooGallery_CSS_Load_Optimizer class which enqueues CSS in the head
 */
if (!class_exists('class-css-load-optimizer.php')) {

    class FooGallery_CSS_Load_Optimizer {

        function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'include_gallery_css' ) );
            add_action( 'foogallery-template-enqueue-style', array( $this, 'enqueue_foogallery_style' ), 10, 2 );
        }

        /**
         * Get the current post ids for the view that is being shown
         */
        function get_post_ids_from_query() {
            global $wp_query;

            if ( is_singular() ) {
                return array( $wp_query->post->ID );
            } else if ( is_array( $wp_query->posts ) ) {
                return wp_list_pluck( $wp_query->posts, 'ID' );
            }
        }

        /**
         * Checks the post meta for any FooGallery CSS that needs to be added to the head
         *
         */
        function include_gallery_css() {
            global $enqueued_foogallery_styles;

            $enqueued_foogallery_styles = array();

            foreach( $this->get_post_ids_from_query() as $post_id ) {
                $this->include_gallery_stylesheets_for_post( $post_id );
            }
        }

        /**
         * includes any CSS that needs to be added for a post
         *
         * @param $post_id int ID of the post
         */
        function include_gallery_stylesheets_for_post( $post_id ) {
            global $enqueued_foogallery_styles;

            if ( $post_id ) {
                //get any foogallery stylesheets that the post might need to include
                $css = get_post_meta($post_id, FOOGALLERY_META_POST_USAGE_CSS);

                foreach ($css as $css_item) {
                    foreach ($css_item as $template => $url) {
                        //only enqueue the stylesheet once
                        if ( !array_key_exists( $template, $enqueued_foogallery_styles ) ) {
                            wp_enqueue_style("foogallery-template-{$template}", $url);
                            $enqueued_foogallery_styles[$template] = $template;
                        }
                    }
                }
            }
        }

        /**
         * Check to make sure we have added the stylesheets to our custom post meta field,
         * so that on next render the stylesheet will be added to the page header
         *
         * @param $foogallery_template string The foogallery template that is trying to load stylesheets
         * @param $css_location string The location array for the stylesheet
         */
        function enqueue_foogallery_style($foogallery_template, $css_location) {
            global $wp_query, $enqueued_foogallery_styles;

            //we only want to do this if we are looking at a single post
            if ( !is_singular() ) {
                return;
            }

            //first check that the template has not been enqueued before
            if ( is_array( $enqueued_foogallery_styles ) && !array_key_exists( $foogallery_template, $enqueued_foogallery_styles ) ) {
                $post_id = $wp_query->post->ID;

                if ( $post_id ) {
                    add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE_CSS, array( $foogallery_template => $css_location['url'] ), false );
                }
            }
        }
    }
}