<?php
/**
 * FooGallery_Admin_CSS_Load_Optimizer class
 * Date: 23/06/2015
 */
if (!class_exists('FooGallery_Admin_CSS_Load_Optimizer')) {

    class FooGallery_Admin_CSS_Load_Optimizer {

        private $_locator;

        function __construct() {
            add_action( 'foogallery_start_attach_gallery_to_post', array( $this, 'start_attach_gallery_to_post' ) );
            add_action( 'foogallery_attach_gallery_to_post', array( $this, 'attach_shortcode_to_post' ), 10, 2 );
            add_action( 'foogallery_after_save_gallery', array( $this, 'detach_gallery_from_all_posts' ), 10, 1 );
        }

        function start_attach_gallery_to_post( $post_id ) {
            //Clear any foogallery css that the post might need to include
            delete_post_meta( $post_id, FOOGALLERY_META_POST_USAGE_CSS );

            //create an instance of the template loader that we will use to find CSS files that the different FooGalleries will require to be loaded
            $template_loader = new FooGallery_Template_Loader();
            $this->_locator = $template_loader->create_locator_instance();
        }

        function attach_shortcode_to_post( $post_id, $foogallery_id ) {
            global $foogallery_templates_css;

            if ( !is_array( $foogallery_templates_css ) ) {
                $foogallery_templates_css = array();
            }

            //load the foogallery, so we can get the template that is used
            $foogallery = FooGallery::get_by_id( $foogallery_id );

            if ( false === $foogallery ) return;

            $template = $foogallery->gallery_template;

            //check to see if we have already added this template's stylesheet
            if ( array_key_exists( $template, $foogallery_templates_css ) ) {

                //we have already added the template's stylesheets, so do not try again. Possibly 2 galleries on the page

            } else {
                $foogallery_templates_css[] = $template;

                if ( false !== ( $css_location = $this->_locator->locate_file( "gallery-{$template}.css" ) ) ) {

                    $style = array(
                        'src' => $css_location['url'],
                        'deps' => array(),
                        'ver' => FOOGALLERY_VERSION,
                        'media' => 'all'
                    );

                    add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE_CSS, array( "foogallery-template-{$template}" => $style ), false );
                }
            }
        }

        function detach_gallery_from_all_posts( $post_id ) {
            $gallery = FooGallery::get_by_id( $post_id );
            $posts = $gallery->find_usages();

            foreach ( $posts as $post ) {
                delete_post_meta( $post->ID, FOOGALLERY_META_POST_USAGE_CSS );
            }
        }
    }
}
