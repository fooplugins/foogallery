<?php
/**
 * FooGallery_CSS_Load_Optimizer class which enqueues CSS in the head
 */
if (!class_exists('class-css-load-optimizer.php')) {

    class FooGallery_CSS_Load_Optimizer {

        function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'include_gallery_css' ) );
            add_action( 'foogallery_enqueue_style', array( $this, 'persist_enqueued_style' ), 10, 5 );
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
            } else {
                return array();
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
                    if (!$css_item) continue;
                    foreach ($css_item as $handle => $style) {
                        //only enqueue the stylesheet once
                        if ( !array_key_exists( $handle, $enqueued_foogallery_styles ) ) {
                            if ( is_array( $style ) ) {
                                wp_enqueue_style( $handle, $style['src'], $style['deps'], $style['ver'], $style['media'] );
                            } else {
                                wp_enqueue_style( $handle, $style );
                            }

                            $enqueued_foogallery_styles[$handle] = $handle;
                        }
                    }
                }
            }
        }

        /**
         * Check to make sure we have added the stylesheets to our custom post meta field,
         * so that on next render the stylesheet will be added to the page header
         *
         * @param $style_handle string The stylesheet handle
         * @param $src string The location for the stylesheet
         * @param array $deps
         * @param bool $ver
         * @param string $media
         */
        function persist_enqueued_style($style_handle, $src, $deps = array(), $ver = false, $media = 'all') {
            global $wp_query, $enqueued_foogallery_styles;

            //we only want to do this if we are looking at a single post
            if ( !is_singular() ) {
                return;
            }

            //first check that the template has not been enqueued before
            if ( is_array( $enqueued_foogallery_styles ) && !array_key_exists( $style_handle, $enqueued_foogallery_styles ) ) {
                $post_id = $wp_query->post->ID;

                if ( $post_id ) {
                    $style = array(
                        'src' => $src,
                        'deps' => $deps,
                        'ver' => $ver,
                        'media' => $media
                    );

                    add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE_CSS, array( $style_handle => $style ), false );
                }
            }
        }
    }
}