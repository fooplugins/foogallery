<?php
/*
 * FooGallery Thumbnail Resizing class
 */

if ( ! class_exists( 'FooGallery_Thumbnails' ) ) {

	class FooGallery_Thumbnails {

		function __construct() {
			//generate thumbs using WPThumb
			add_filter( 'foogallery_attachment_resize_thumbnail', array( $this, 'resize' ), 10, 3 );
		}

		function resize( $original_image_src, $args, $thumbnail_object ) {

			$arg_defaults = array(
				'width'  => 0,
				'height' => 0,
				'crop'   => true,
			);

			$args = wp_parse_args( $args, $arg_defaults );

			$width = (int)$args['width'];
			$height = (int)$args['height'];
			$crop = (bool)$args['crop'];

			//check if we are trying to get back the default thumbnail that we already have
			if ( $thumbnail_object->ID > 0 &&
				$width == get_option( 'thumbnail_size_w' ) &&
				$height == get_option( 'thumbnail_size_h' ) &&
				$crop == get_option( 'thumbnail_crop' ) ) {
				$thumbnail_attributes = wp_get_attachment_image_src( $thumbnail_object->ID );
				return $thumbnail_attributes[0];
			}

			//we need either a width or a height. If nothing is given then default to the thumb width setting in Settings->Media
			if ( 0 == $width && 0 == $height ) {
				$args['width'] = (int)get_option( 'thumbnail_size_w' );
			}

			return wpthumb( $original_image_src, $args );
		}
	}
}