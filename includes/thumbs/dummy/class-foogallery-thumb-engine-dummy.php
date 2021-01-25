<?php

/**
 * Class for the dummy thumbnail engine in FooGallery
 */
if ( ! class_exists( 'FooGallery_Thumb_Engine_Dummy' ) ) {

	class FooGallery_Thumb_Engine_Dummy extends FooGallery_Thumb_Engine {

		/**
		 * Do nothing on init
		 */
		function init() {
			//do nothing
		}

		/**
		 * Generate the dummyimage.com thumbnail URL
		 * @param       $url
		 * @param array $args
		 *
		 * @return string
		 */
		function generate( $url, $args = array() ) {
			$width  = (int) $args['width'];
			$height = (int) $args['height'];
			$attachment_id = (int) $args['foogallery_attachment_id'];
			$colors = array(
				array( 'background' => '000000', 'text' => 'ffffff' ),
				array( 'background' => '001eff', 'text' => 'ffffff' ),
				array( 'background' => '5400ff', 'text' => 'ffffff' ),
				array( 'background' => '4c1616', 'text' => 'ffffff' ),
				array( 'background' => '663e00', 'text' => 'ffffff' ),
				array( 'background' => '376600', 'text' => 'ffffff' ),
				array( 'background' => '00663e', 'text' => 'ffffff' ),
				array( 'background' => '006566', 'text' => 'ffffff' ),
				array( 'background' => '003c66', 'text' => 'ffffff' ),
				array( 'background' => '290066', 'text' => 'ffffff' ),
				array( 'background' => '660062', 'text' => 'ffffff' ),
				array( 'background' => '66000e', 'text' => 'ffffff' ),
				array( 'background' => 'cccccc', 'text' => '000000' ),
				array( 'background' => 'ff0000', 'text' => '000000' ),
				array( 'background' => 'ff6c00', 'text' => '000000' ),
				array( 'background' => 'ffe400', 'text' => '000000' ),
				array( 'background' => '66ff00', 'text' => '000000' ),
				array( 'background' => '00fcff', 'text' => '000000' ),
				array( 'background' => 'f000ff', 'text' => '000000' ),
				array( 'background' => 'dc7f8c', 'text' => '000000' ),
				array( 'background' => 'db7fdc', 'text' => '000000' ),
				array( 'background' => '7f81dc', 'text' => '000000' ),
				array( 'background' => '7fdbdc', 'text' => '000000' ),
				array( 'background' => '7fdca6', 'text' => '000000' ),
				array( 'background' => 'b8dc7f', 'text' => '000000' ),
				array( 'background' => 'dcd07f', 'text' => '000000' ),
				array( 'background' => 'dc8a7f', 'text' => '000000' )
			);

			$color = $colors[ array_rand( $colors ) ];

			return sprintf( 'https://dummyimage.com/%dx%d/%s/%s&text=Item+%s+(%s×%s)',
				$width, $height, $color['background'], $color['text'], $attachment_id, $width, $height );
		}

		function clear_local_cache_for_file( $file ) {
			return; //do nothing
		}

		function has_local_cache() {
			return false;
		}

		function get_last_error() {
			return null;
		}
	}
}

//example : https://dummyimage.com/1920x1200/b8dc7f/000000&text=Item+0+(1920x720)