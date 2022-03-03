<?php
/**
 * FooGallery class that extends WP_Image_Editor_GD
 */
if ( ! class_exists( 'FooGallery_Thumb_Image_Editor_GD' ) ) {
	class FooGallery_Thumb_Image_Editor_GD extends WP_Image_Editor_GD {

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
			$rgba = imagecolorat($this->image, $x, $y);
			$color = imagecolorsforindex($this->image, $rgba);

			return $color;
		}
	}
}