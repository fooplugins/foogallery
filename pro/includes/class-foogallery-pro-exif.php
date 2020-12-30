<?php
/**
 * Class for adding EXIF data attributes
 */
if ( ! class_exists( 'FooGallery_Pro_Exif' ) ) {

    class FooGallery_Pro_Exif {
        /**
         * Constructor for the PM class
         *
         * Sets up all the appropriate hooks and actions
         */
        function __construct() {
            //Add EXIF data
            add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_exif' ), 10, 3 );
            
            //Add lightbox EXIF options
            add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lightbox_data_attributes' ), 20, 2 );
        }

        /**
         * Append the needed data attributes to the container div for the lightbox EXIF setting
         *
         * @param $attributes
         * @param $gallery
         *
         * @return array
         */
        function add_lightbox_data_attributes( $attributes, $gallery ) {
            //If the data-foogallery-lightbox value does not exist, then the lightbox attributes have not been set, and the lightbox is not enabled
            if ( empty( $attributes['data-foogallery-lightbox'] ) ) {
                return $attributes;
            }

            $decode = json_decode( $attributes['data-foogallery-lightbox'] );
            $decode->exif = 'auto';
            $attributes['data-foogallery-lightbox'] = json_encode( $decode );

            return $attributes;
        }

        /**
         * Customize the item anchor EXIF data attributes
         * 
         * @param $attr
         * @param $args
         * @param $foogallery_attachment
         * 
         * @return array
         */
        function add_exif( $attr, $args, $foogallery_attachment ) {
            global $current_foogallery;

            if ( $current_foogallery->lightbox != 'foogallery' ) {
                return $attr;
            }

        	$meta = wp_get_attachment_metadata( $foogallery_attachment->ID ); 

			$attr['data-exif'] = json_encode( $meta['image_meta'] );

			return $attr;
        }
    }
}
