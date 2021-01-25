<?php
/**
 * FooGallery class that extends WP_Image_Editor_Imagick
 */
if ( ! class_exists( 'FooGallery_Thumb_Image_Editor_Imagick' ) ) {
	class FooGallery_Thumb_Image_Editor_Imagick extends WP_Image_Editor_Imagick {

		public function get_image() {
			return $this->image;
		}

		public function update_image( $image ) {
			$this->image = $image;
		}

		public function update_size( $width = null, $height = null ) {
			return parent::update_size( $width, $height );
		}

		/**
		 * Get the color at a specific coordinate
		 *
		 * @param $x
		 * @param $y
		 *
		 * @return object
		 */
		public function get_pixel_color($x, $y)  {
			$pixel = $this->image->getImagePixelColor($x, $y);

			// Un-normalized values don't give a full range 0-1 alpha channel
			// So we ask for normalized values, and then we un-normalize it ourselves.
			$colorArray = $pixel->getColor(true);

			$color = array(
				'red' => (int) round($colorArray['r'] * 255),
				'green' => (int) round($colorArray['g'] * 255),
				'blue' => (int) round($colorArray['b'] * 255),
				'alpha' => (int) (127 - round($colorArray['a'] * 127))
			);

			return $color;
		}
	}
}
