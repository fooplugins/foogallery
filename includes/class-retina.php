<?php
/*
 * FooGallery Retina Support class
 */

if ( !class_exists( 'FooGallery_Retina' ) ) {

    class FooGallery_Retina {

        function __construct() {
            add_filter('foogallery_attachment_html_image_attributes', array($this, 'add_retina_attributes'), 10, 3);
        }

        /**
         * @param array $attr
         * @param array $args
         * @param FooGalleryAttachment $attachment
         * @return mixed
         */
        function add_retina_attributes($attr, $args, $attachment) {
            global $current_foogallery;

            if ( $current_foogallery && $current_foogallery->gallery_template ) {

                //first check if the gallery template supports Retina thumbs

                //Then get the retina settings, e.g. 2x, 3x, 4x

                //apply scaling to the width and height attributes
                $args['width']  = (int)$args['width'] * 2;
                $args['height'] = (int)$args['height'] * 2;

                //build up the retina attributes
                $attr['srcset'] = $attachment->html_img_src( $args ) . ' ' . $args['width'] . 'w';
            }

            return $attr;
        }
    }
}
