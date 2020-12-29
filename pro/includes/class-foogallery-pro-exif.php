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
            
            //Add container class
            add_filter( 'foogallery_build_class_attribute', array( $this, 'add_container_class' ), 10,  2 );
            
            //Add lightbox EXIF options
            add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lightbox_data_attributes' ), 10, 2 );
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
            if ( empty( $attributes['data-foogallery-lightbox'] ) ) {
                return $attributes;
            }

            $decode = json_decode( $attributes['data-foogallery-lightbox'] );
            $decode->exif = 'auto';
            $attributes['data-foogallery-lightbox'] = json_encode( $decode );

            return $attributes;
        }

        /**
         * Customize the item container class
         *
         * @param $classes
         * @param $gallery
         * 
         * @return array
         */
        function add_container_class( $classes, $gallery ) {
            $classes[] = 'fg-exif-top-right fg-exif-dark fg-exif-rounded';

            return $classes;
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
        	$meta = wp_get_attachment_metadata( $foogallery_attachment->ID ); 

			$attr['data-exif'] = json_encode( $meta['image_meta'] );

			return $attr;
        }
    }
}
