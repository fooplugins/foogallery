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
                if ( $current_foogallery->retina ) {
                    $srcset = array();

                    $original_width = intval( $args['width'] );
                    $original_height = intval( $args['height'] );

                    foreach ( foogallery_retina_options() as $pixel_density ) {
                        $pixel_density_supported = array_key_exists( $pixel_density, $current_foogallery->retina ) ? ('true' === $current_foogallery->retina[$pixel_density]) : false;

                        if ( $pixel_density_supported ) {
                            $pixel_density_int = intval( str_replace( 'x', '', $pixel_density ) );

                            //apply scaling to the width and height attributes
                            $retina_width  = $original_width * $pixel_density_int;
                            $retina_height = $original_height * $pixel_density_int;

                            //if the new dimensions are smaller than the full size image dimensions then allow the retina thumb
                            if ( $retina_width < $attachment->width &&
                                $retina_height < $attachment->height ) {
                                $args['width'] = $retina_width;
                                $args['height'] = $retina_height;

                                //build up the retina attributes
                                $srcset[] = $attachment->html_img_src( $args ) . ' ' . $retina_width . 'w';
                            }
                        }
                    }

                    if ( count( $srcset ) ) {
                        $attr['srcset'] = implode( ',', $srcset );
                    }
                }
            }

            return $attr;
        }
    }
}
