<?php
/**
 * Class for applying watermarks to images
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_Watermark' ) ) {
	/**
	 * Class FooGallery_Watermark
	 */
	class FooGallery_Watermark {

		protected $editor;

		/**
		 * FooGallery_Watermark constructor.
		 *
		 * @param WP_Image_Editor $editor The current Image Editor being used.
		 */
		public function __construct( $editor ) {
			$this->editor = $editor;
		}

		/**
		 * Returns and instance of the class from an image path
		 *
		 * @param string $image_path The path to the image.
		 *
		 * @return FooGallery_Watermark
		 */
		public static function from_image_path( $image_path ) {
			$editor = wp_get_image_editor( $image_path, array( 'methods' => array( 'get_image' ) ) );
			return new self( $editor );
		}

		/**
		 * Returns the current extension type of the image editor
		 *
		 * @return string
		 */
		public function get_extension() {
			return '';
		}

		public function apply_watermark_text( $text, $watermark_options = array(
			'image_quality' => 90,
			'transparency'  => 50,
			'font'          => 'arial.ttf',
			'text_color'    => '#ffffff',
			'text_size'     => 24,
			'text_angle'    => 0,
			'mode'          => 'single',        //repeat or single
			'position'      => 'top_center',
			'margins'       => 0,
			'offset_unit'   => 'pixels',       //pixels or percentage
			'offset_x'      => 0,
			'offset_y'      => 0,
		) ) {
			$font = $this->get_font_path( $watermark_options['font'] );
			// Not yet implemented!
		}

		function get_font_path( $font ) {
			$path = FOOGALLERY_EXTRA_PATH . 'fonts/' . $font;

			if ( ! file_exists( $path ) || ! is_file( $path ) ) {
				$path = null;
			}

			return apply_filters( 'foogallery_watermarks_font_path', $path, $font );
		}

		/**
		 * Applies a watermark image.
		 *
		 * @param string $watermark_path
		 * @param array  $watermark_options
		 *
		 * @return WP_Error|WP_Image_Editor
		 */
		function apply_watermark_image( $watermark_path, $watermark_options = array(
			'image_quality'      => 90,
			'transparency'       => 60,
			'mode'               => 'repeat',      // repeat or single.
			'size_type'          => 'scale',       // custom, scale or original.
			'custom_size_width'  => 100,
			'custom_size_height' => 100,
			'scale'              => 50,
			'position'           => 'center,center',
			'margins'            => 10,
			'offset_unit'        => 'pixels',      // pixels or percentage.
			'offset_x'           => 0,
			'offset_y'           => 0,
		) ) {

			// Up the php memory limit.
			@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '256M' ) );

			// set the quality of the watermarked image.
			$this->editor->set_quality( $watermark_options['image_quality'] );

			// get the image object from the editor.
			$image = $this->editor->get_image();

			// get the image size array.
			$image_size = $this->editor->get_size();

			// load the watermark into an image editor.
			$watermark_editor = wp_get_image_editor( $watermark_path, array( 'methods' => array( 'get_image' ) ) );

			// check for an error and return if so.
			if ( is_wp_error( $watermark_editor ) ) {
				return $watermark_editor;
			}

			// get the watermark image object from the editor.
			$watermark = $watermark_editor->get_image();

			// get the watermark image size array.
			$watermark_size = $watermark_editor->get_size();

			// calculate the watermark dimensions.
			list( $new_watermark_width, $new_watermark_height ) = $this->calculate_watermark_dimensions( $watermark_size['width'], $watermark_size['height'], $watermark_options );

			// check to see if we need to resize.
			if ( $new_watermark_width !== $watermark_size['width'] || $new_watermark_height !== $watermark_size['height'] ) {
				$watermark_editor->resize( $new_watermark_width, $new_watermark_height );

				// get the resized watermark size.
				$watermark_size = $watermark_editor->get_size();

				// get the resized watermark image.
				$watermark = $watermark_editor->get_image();
			}

			// check the mode.
			if ( 'repeat' === $watermark_options['mode'] ) {
				$margin         = intval( $watermark_options['margins'] );
				$x_times_actual = $image_size['width'] / ( $watermark_size['width'] + $margin );
				$x_times        = intval( $x_times_actual );
				$y_times_actual = $image_size['height'] / ( $watermark_size['height'] + $margin );
				$y_times        = intval( $y_times_actual );

				if ( $x_times_actual !== $x_times ) {
					$x_times++;
				}
				if ( $y_times_actual !== $y_times ) {
					$y_times++;
				}

				$top = 0;
				for ( $j = 0; $j < $y_times; $j++ ) {
					$left = 0;
					for ( $i = 0; $i < $x_times; $i++ ) {
						$src_w = $watermark_size['width'];
						$src_h = $watermark_size['height'];
						if ( ( $left + $watermark_size['width'] ) > $image_size['width'] ) {
							$src_w = $image_size['width'] - $left;
						}
						if ( ( $top + $watermark_size['height'] ) > $image_size['height'] ) {
							$src_h = $image_size['height'] - $top;
						}
						$this->get_image_editor_helper()->merge_images( $image, $watermark, $left, $top, 0, 0, $src_w, $src_h, $watermark_options['transparency'] );

						$left += $watermark_size['width'] + $margin;
					}
					$top += $watermark_size['height'] + $margin;
				}
			} else {
				// place a single watermark.

				// calculate the watermark coords.
				list( $left, $top ) = $this->calculate_watermark_coordinates( $image_size['width'], $image_size['height'], $watermark_size['width'], $watermark_size['height'], $watermark_options );

				$this->get_image_editor_helper()->merge_images( $image, $watermark, $left, $top, 0, 0, $watermark_size['width'], $watermark_size['height'], $watermark_options['transparency'] );
			}

			$this->editor->update_image( $image );

			$this->get_image_editor_helper()->cleanup( $watermark );
		}

		/**
		 * Calculate watermark dimensions.
		 *
		 * @param int   $watermark_width Watermark width.
		 * @param int   $watermark_height Watermark height.
		 * @param array $options Options.
		 * @return array Watermark new dimensions
		 */
		private function calculate_watermark_dimensions( $watermark_width, $watermark_height, $options ) {
			if ( 'custom' === $options['size_type'] ) {
				$width  = isset( $options['custom_size_width'] ) ? $options['custom_size_width'] : $watermark_width;
				$height = isset( $options['custom_size_height'] ) ? $options['custom_size_height'] : $watermark_height;
			} elseif ( 'scale' === $options['size_type'] ) {
				$scale  = isset( $options['scale'] ) ? intval( $options['scale'] ) : 100;
				$width  = intval( $watermark_width * $scale / 100 );
				$height = intval( $watermark_height * $scale / 100 );
			} else {
				$width  = $watermark_width;
				$height = $watermark_height;
			}

			return array( $width, $height );
		}

		/**
		 * Calculate coordinates for watermark
		 *
		 * @param $image_width
		 * @param $image_height
		 * @param $watermark_width
		 * @param $watermark_height
		 * @param $options
		 * @return array Watermark coordinates
		 */
		private function calculate_watermark_coordinates( $image_width, $image_height, $watermark_width, $watermark_height, $options ) {
			$margin = intval( $options['margins'] );

			switch ( $options['position'] ) {
				case 'left,top':
				case 'top_left':
					$dest_x = $dest_y = $margin;
					break;
				case 'center,top':
				case 'top_center':
					$dest_x = ( $image_width / 2 ) - ( $watermark_width / 2 );
					$dest_y = $margin;
					break;
				case 'right,top':
				case 'top_right':
					$dest_x = $image_width - $margin - $watermark_width;
					$dest_y = $margin;
					break;
				case 'left,center':
				case 'middle_left':
					$dest_x = $margin;
					$dest_y = ( $image_height / 2 ) - ( $watermark_height / 2 );
					break;
				case 'right,center':
				case 'middle_right':
					$dest_x = $image_width - $margin - $watermark_width;
					$dest_y = ( $image_height / 2 ) - ( $watermark_height / 2 );
					break;
				case 'left,bottom':
				case 'bottom_left':
					$dest_x = $margin;
					$dest_y = $image_height - $margin - $watermark_height;
					break;
				case 'center,bottom':
				case 'bottom_center':
					$dest_x = ( $image_width / 2 ) - ( $watermark_width / 2 );
					$dest_y = $image_height - $margin - $watermark_height;
					break;
				case 'right,bottom':
				case 'bottom_right':
					$dest_x = $image_width - $margin - $watermark_width;
					$dest_y = $image_height - $margin - $watermark_height;
					break;
				case 'center,center':
				case 'middle_center':
				default:
					$dest_x = ( $image_width / 2 ) - ( $watermark_width / 2 );
					$dest_y = ( $image_height / 2 ) - ( $watermark_height / 2 );
			}

			$offset_x = intval( isset( $options['offset_x'] ) ? $options['offset_x'] : 0 );
			$offset_y = intval( isset( $options['offset_y'] ) ? $options['offset_y'] : 0 );

			if ( 'pixels' === $options['offset_unit'] ) {
				$dest_x += $offset_x;
				$dest_y += $offset_y;
			} else {
				$dest_x += intval( $image_width * $offset_x / 100 );
				$dest_y += intval( $image_height * $offset_y / 100 );
			}

			return array( $dest_x, $dest_y );
		}

		/**
		 * Returns the correct image editor helper, based on the current image editor in use
		 */
		public function get_image_editor_helper() {
			$editor_type = get_class( $this->editor );
			if ( strpos( $editor_type, 'Imagick' ) !== false ) {
				return new FooGallery_Image_Editor_Helper_Imagick();
			}

			return new FooGallery_Image_Editor_Helper_GD();
		}
	}
}
