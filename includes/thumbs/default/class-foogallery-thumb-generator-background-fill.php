<?php
/**
 * Adds background fill functionality to the default FooGallery thumb generator
 */
if ( ! class_exists( 'FooGallery_Thumb_Generator_Background_Fill' ) ) {
	class FooGallery_Thumb_Generator_Background_Fill {

		private $args;
		private $editor;

		public function __construct() {
			add_filter( 'foogallery_thumb_image_post', array( $this, 'add_background_fill'), 10, 2 );
		}

		/**
		 * @param $editor WP_Image_Editor
		 * @param $args array
		 *
		 * @return WP_Image_Editor
		 */
		function add_background_fill( $editor, $args ) {
			// currently only supports GD
			if ( !is_a( $editor, 'FooGallery_Thumb_Image_Editor_GD' ) ) {
				return $editor;
			}

			$this->editor = $editor;
			$this->args = $args;

			if ( !array_key_exists( 'background_fill', $args ) ) {
				//get out early if we do not have specific arguments
				return $editor;
			}

			$color = $this->args['background_fill'];

			if ( is_null( $color ) ) {
				//get out early if we do not have specific arguments
				return $editor;
			}

			if ( $color === 'auto' ) {
				$color = $this->get_background_colors();
			} else if ( $color === 'transparent' ) {
				$color = '255255255127';
			}

			//check for short color
			if ( !is_array( $color ) && strlen( $color ) == 3 ) {
				$color = (float) str_pad( (string) $color, 9, $color ) . '000';
			}

			//convert to an array if needed
			if ( !is_array( $color ) ) {
				$color = array( 'top' => $color, 'bottom' => $color, 'left' => $color, 'right' => $color );
			}

			$this->fill_color( $color );

			return $editor;
		}

		/**
		 * Background fill an image using the provided colors
		 *
		 * @param array $colors The desired pad colors in RGB format, array should be array( 'top' => '', 'bottom' => '', 'left' => '', 'right' => '' );
		 */
		private function fill_color( array $colors ) {

			$current_size = $this->editor->get_size();

			$size = array( 'width' => $this->args['width'], 'height' => $this->args['height'] );

			$offsetLeft = ( $size['width'] - $current_size['width'] ) / 2;
			$offsetTop = ( $size['height'] - $current_size['height'] ) / 2;

			$new_image = imagecreatetruecolor( $size['width'], $size['height'] );

			// This is needed to support alpha
			imagesavealpha( $new_image, true );
			imagealphablending( $new_image, false );

			// Check if we are padding vertically or horizontally
			if ( $current_size['width'] != $size['width'] ) {

				$colorToPaint = imagecolorallocatealpha( $new_image,
					substr( $colors['left'], 0, 3 ),
					substr( $colors['left'], 3, 3 ),
					substr( $colors['left'], 6, 3 ),
					substr( $colors['left'], 9, 3 ) );

				// Fill left color
				imagefilledrectangle( $new_image, 0, 0, $offsetLeft, $size['height'], $colorToPaint );

				$colorToPaint = imagecolorallocatealpha( $new_image,
					substr( $colors['right'], 0, 3 ),
					substr( $colors['right'], 3, 3 ),
					substr( $colors['right'], 6, 3 ),
					substr( $colors['left'], 9, 3 ) );

				// Fill right color
				imagefilledrectangle( $new_image, $offsetLeft + $current_size['width'], 0, $size['width'], $size['height'], $colorToPaint );
			}

			if ( $current_size['height'] != $size['height'] ) {

				$colorToPaint = imagecolorallocatealpha( $new_image,
					substr( $colors['top'], 0, 3 ),
					substr( $colors['top'], 3, 3 ),
					substr( $colors['top'], 6, 3 ),
					substr( $colors['top'], 9, 3 ) );

				// Fill top color
				imagefilledrectangle( $new_image, 0, 0, $size['width'], $offsetTop - 1, $colorToPaint );

				$colorToPaint = imagecolorallocatealpha( $new_image,
					substr( $colors['bottom'], 0, 3 ),
					substr( $colors['bottom'], 3, 3 ),
					substr( $colors['bottom'], 6, 3 ),
					substr( $colors['bottom'], 9, 3 ) );

				// Fill bottom color
				imagefilledrectangle( $new_image, 0, $offsetTop + $current_size['height'], $size['width'], $size['height'], $colorToPaint );
			}

			imagecopy( $new_image, $this->editor->get_image(), $offsetLeft, $offsetTop, 0, 0, $current_size['width'], $current_size['height'] );

			$this->editor->update_image( $new_image );
			$this->editor->update_size();
		}

		/**
		 * Return the background colors for the edges of an image
		 *
		 * @return array
		 */
		public function get_background_colors() {

			$current_size = $this->editor->get_size();
			$midway_x = intval($current_size['width'] / 2);
			$midway_y = intval($current_size['height'] / 2);

			$coords = array(
				'top' => array( $midway_x, 0 ),
				'bottom' => array( $midway_x, $current_size['height'] - 1 ),
				'left' => array( 0, $midway_y ),
				'right' => array( $current_size['width'] - 1, $midway_y )
			);

			$colors = array();

			foreach ( $coords as $position => $coord ) {
				$c = $this->editor->get_pixel_color( $coord[0], $coord[1] );
				$colors[$position] = str_pad( $c['red'], 3, '0', STR_PAD_LEFT ) . str_pad( $c['green'], 3, '0', STR_PAD_LEFT ) . str_pad( $c['blue'], 3, '0', STR_PAD_LEFT ) . str_pad( $c['alpha'], 3, '0', STR_PAD_LEFT );
			}

			return max( $colors );

			//return $colors;
		}
	}
}