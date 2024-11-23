<?php
/**
 * Class that performs all thumbnail generation within FooGallery
 */
if ( ! class_exists( 'FooGallery_Thumb_Generator' ) ) {
	class FooGallery_Thumb_Generator {

		/**
		 * Array of resize arguments
		 *
		 * @var array
		 * @access private
		 */
		private $args;

		/**
		 * The file path to the original image
		 *
		 * @var string
		 * @access private
		 */
		private $file_path;

		/**
		 * The original image URL
		 *
		 * @var string
		 * @access private
		 */
		private $image_url;

		/**
		 * Any errors we encountered
		 *
		 * @var WP_Error
		 * @access private
		 */
		private $error;

		/**
		 * Is there an error
		 *
		 * @access public
		 * @return null
		 */
		public function errored() {
			return ! empty( $this->error );
		}

		/**
		 * Returns the error
		 *
		 * @access public
		 * @return null
		 */
		public function error() {
			return empty( $this->error ) ? null : $this->error;
		}

		/**
		 * Constructor to parse the args and determine cache folder
		 *
		 * @access public
		 *
		 * @param string $image_url
		 * @param array  $args      . (default: array())
		 * @param bool   $override_args
		 */
		public function __construct( $image_url, $args = array(), $override_args = false ) {
			$this->image_url = $image_url;

			//converts URL's to paths if needed
			$this->set_file_path( $image_url );

			if ( $args ) {
				$this->set_args( $args, $override_args );
			}
		}

		/**
		 * Returns the base WordPress path
		 * @return string
		 */
		private static function get_home_path() {
			$url = str_replace( home_url(), '', site_url() );
			$url = str_replace( $url, '', ABSPATH );
			return $url;
		}

		/**
		 * Returns the path of an image URL
		 *
		 * @param $image_url
		 *
		 * @return string|false
		 */
		public static function get_file_path( $image_url ) {

			//check if the $file_path is already a path within the site
			if ( strpos( $image_url, self::get_home_path() ) === 0 ) {
				return $image_url;
			} else {
				//we are dealing with a URL

				$upload_dir = wp_upload_dir();

				$base_url = set_url_scheme( $upload_dir['baseurl'] );

				if ( strpos( $image_url, $base_url ) !== false ) {
					//it's in the uploads folder
					$image_path = str_replace( $base_url, $upload_dir['basedir'], $image_url );
				} else {

					$image_path = str_replace( trailingslashit( home_url() ), self::get_home_path(), $image_url );
				}

				//check if the file is local
				if ( strpos( $image_url, trailingslashit( home_url() ) ) === 0 ) {
					//strip all querystring params
					$image_path = strtok( $image_path, '?' );

					//check it exists
					if ( !file_exists( $image_path ) ) {
						return false;
					}
				}
			}

			return $image_path;
		}

		/**
		 * Set the correct file path of the original image from the image URL
		 *
		 * @param string $image_url
		 */
		public function set_file_path( $image_url ) {
			$file_path = self::get_file_path( $image_url );
			if ( false === $file_path ) {
				$this->error = new WP_Error( 'file-not-found' );
			} else {
				$this->file_path = $file_path;
			}
		}

		/**
		 * Parse the args and merge with defaults
		 *
		 * @param array $args
		 * @param bool  $override_args
		 */
		public function set_args( $args, $override_args = false ) {
			if ( $override_args ) {
				// Just set the args and do no more.
				$this->args = $args;
			} else {

				//these are the default arguments
				$arg_defaults = apply_filters( 'foogallery_thumb_default_args', array(
					'width'                   => 0,
					'height'                  => 0,
					'crop'                    => false,
					'crop_from_position'      => foogallery_get_setting( 'default_crop_position', FooGallery_Crop_Position::CROP_POSITION_DEFAULT ),
					'resize'                  => true,
					'watermark_options'       => array(),
					'cache'                   => true,
					'skip_remote_check'       => false,
					//not used. Only kept in to preserve the generated cache file name
					'default'                 => null,
					'jpeg_quality'            => 90,
					'resize_animations'       => true,
					'return'                  => 'url',
					//not used. Only kept in to preserve the generated cache file name
					'custom'                  => false,
					//not used. Only kept in to preserve the generated cache file name
					'background_fill'         => null,
					'output_file'             => false,
					'cache_with_query_params' => false
					//not used. Only kept in to preserve the generated cache file name
				) );

				$args = wp_parse_args( $args, $arg_defaults );

				// Cast some args
				$args['crop']   = (bool) $args['crop'];
				$args['resize'] = (bool) $args['resize'];
				$args['cache']  = (bool) $args['cache'];
				$args['width']  = (int) $args['width'];
				$args['height'] = (int) $args['height'];

				// Format the crop from position arg
				if ( is_string( $args['crop_from_position'] ) && $args['crop_from_position'] ) {
					$args['crop_from_position'] = explode( ',', $args['crop_from_position'] );
				}

				$this->args = apply_filters( 'foogallery_thumb_args', $args );
			}
		}

		/**
		 * Get a specific arg
		 *
		 * @access public
		 *
		 * @param string $arg
		 * @return mixed
		 */
		public function get_arg( $arg, $default = false ) {

			if ( isset( $this->args[ $arg ] ) ) {
				return $this->args[ $arg ];
			}

			return $default;
		}

		/**
		 * Get the full path to the cache file
		 *
		 * @access public
		 * @return string
		 */
		public function get_cache_file_path() {
			//check we have a path first
			if ( ! $this->file_path ) {
				return '';
			}

			$path = trailingslashit( $this->get_cache_file_directory() ) . $this->get_cache_filename();

			return apply_filters( 'foogallery_thumb_cache_file_path', $path, $this );
		}

		/**
		 * Get the directory that the cache file should be saved too
		 *
		 * @return string
		 */
		public function get_cache_file_directory() {
			if ( $this->get_arg( 'output_file' ) ) {
				return dirname( $this->get_arg( 'output_file' ) );
			}

			// check we have a path first.
			if ( ! $this->file_path ) {
				return '';
			}

			// get a safe filename.
			$original_filename = basename( $this->file_path );
			$parts             = explode( '.', $original_filename );
			array_pop( $parts );
			$filename_nice = implode( '_', $parts );
			if ( $this->get_arg( 'override_directory' ) ) {
				$filename_nice = $this->get_arg( 'override_directory' );
			}

			$upload_dir = wp_upload_dir();

			if ( strpos( $this->file_path, $upload_dir['basedir'] ) === 0 ) {

				$sub_dir = dirname( str_replace( $upload_dir['basedir'], '', $this->file_path ) );
				$new_dir = $upload_dir['basedir'] . '/cache' . trailingslashit( $sub_dir ) . $filename_nice;

			} elseif ( strpos( $this->file_path, WP_CONTENT_DIR ) === 0 ) {

				$sub_dir = dirname( str_replace( WP_CONTENT_DIR, '', $this->file_path ) );
				$new_dir = $upload_dir['basedir'] . '/cache' . trailingslashit( $sub_dir ) . $filename_nice;

			} elseif ( strpos( $this->file_path, self::get_home_path() ) === 0 ) {

				$new_dir = $upload_dir['basedir'] . '/cache/local';

			} else {

				$parts = wp_parse_url( $this->file_path );

				if ( ! empty( $parts['host'] ) ) {
					$new_dir = $upload_dir['basedir'] . '/cache/remote/' . sanitize_title( $parts['host'] );
				} else {
					$new_dir = $upload_dir['basedir'] . '/cache/remote';
				}
			}

			return str_replace( '/cache/cache', '/cache', $new_dir );
		}

		/**
		 * Get the filename of the cache file
		 *
		 * @return string
		 */
		public function get_cache_filename() {
			if ( $this->get_arg( 'output_file' ) ) {
				return basename( $this->get_arg( 'output_file' ) );
			}

			//check we have a path first
			if ( ! $this->file_path ) {
				return '';
			}

			// Generate a short unique filename
			$serialize = crc32( serialize( array_merge( $this->args, array( $this->file_path ) ) ) );

			// Get the image extension
			$ext = $this->get_image_extension();

			// Gifs are converted to pngs
			if ( $ext === 'gif' ) {
				$ext = 'png';
			}

			return $serialize . '.' . $ext;
		}

		/**
		 * Get the extension of the original image
		 *
		 * @return string
		 */
		public function get_image_extension() {

			$filename = parse_url( $this->image_url, PHP_URL_PATH );

			$ext = pathinfo( $filename, PATHINFO_EXTENSION );

			if ( ! $ext ) {
				// Seems like we dont have an ext, lets guess at JPG
				$ext = 'jpg';
			}

			return strtolower( $ext );
		}

		/**
		 * Returns the URL of the generated file.
		 *
		 * @return string
		 */
		public function get_cache_file_url() {
			return $this->convert_path_to_url( $this->get_cache_file_path() );
		}

		/**
		 * Generates the thumbnail based on the args and returns the thumbnail URL
		 */
		public function generate() {
            //if we have any errors to begin with, then the file does not exist, so get out early.
            if ( $this->errored() ) {
                return $this->image_url;
            }

			$must_generate = !$this->get_arg( 'cache' );

			if ( $must_generate || !file_exists( $this->get_cache_file_path() )) {
				$this->generate_cache_file();
			}

			//if we had any errors then return the original
			if ( $this->errored() ) {
				return $this->image_url;
			}

			return $this->convert_path_to_url( $this->get_cache_file_path() );
		}

		/**
		 * Convert a path into a url
		 *
		 * @param string $path
		 * @return string url
		 */
		private function convert_path_to_url( $path ) {

			$upload_dir = wp_upload_dir();

			if ( strpos( $path, $upload_dir['basedir'] ) !== false ) {
				return str_replace( $upload_dir['basedir'], set_url_scheme( $upload_dir['baseurl'] ), $path );
			} else {
				return str_replace( self::get_home_path(), trailingslashit( home_url() ), $path );
			}
		}

		/**
		 * Generate the new cache file using the original image and args
		 *
		 * @return string new filepath
		 */
		public function generate_cache_file() {

			$new_filepath = $this->get_cache_file_path();
			$file_path = $this->file_path;

            if ( is_null( $file_path ) ) {
                return;
            }

			// Up the php memory limit
			@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '256M' ) );

			// Create the image
			$editor = wp_get_image_editor( $file_path, array( 'methods' => array( 'get_image' ) ) );

			if ( is_wp_error( $editor ) ) {
				$this->error = $editor;
				return;
			}

			wp_mkdir_p( $this->get_cache_file_directory() );

			// Convert gif images to png before resizing
			if ( $this->get_image_extension() === 'gif' ) {

				// Save the converted image
				$editor->save( $new_filepath, 'image/png' );

				// Pass the new file back through the function so they are resized
				$gif = new self( $new_filepath, array_merge( $this->args, array(
					'output_file' => $new_filepath,
					'cache'       => false
				) ) );

				$gif->generate();
				return;
			}

			// Apply JPEG quality settings args
			$editor->set_quality( $this->args['jpeg_quality'] );

			apply_filters( 'foogallery_thumb_image_pre', $editor, $this->args );

			//extract the values from args
			$crop = $this->get_arg( 'crop', true );
			$crop_from_position = $this->get_arg( 'crop_from_position', array( 'center', 'center' ) );
			$width = $this->get_arg( 'width', 150 );
			$height = $this->get_arg( 'height', 150 );
			$resize = $this->get_arg( 'resize', true );

			// Cropping
			if ( $crop && $crop_from_position && $crop_from_position !== array( 'center', 'center' ) ) {
				$this->crop_from_position( $editor, $width, $height, $crop_from_position, $resize );
			} elseif ( $crop === true && $resize === true ) {
				$editor->resize( $width, $height, true );
			} elseif ( $crop === true && $resize === false ) {
				$this->crop_from_center( $editor, $width, $height );
			} else {
				$editor->resize( $width, $height );
			}

			apply_filters( 'foogallery_thumb_image_post', $editor, $this->args );

			$editor->save( $new_filepath );

			do_action( 'foogallery_thumb_saved_cache_image', $this, $new_filepath );
		}

		/**
		 * Crop the image to the specified width and height from the centre of the image, no resize
		 *
		 * @param $editor
		 * @param $width
		 * @param $height
		 *
		 * @return mixed
		 */
		private function crop_from_center( $editor, $width, $height ) {

			$size = $editor->get_size();

			$crop = array( 'x' => 0, 'y' => 0, 'width' => $size['width'], 'height' => $size['height'] );

			if ( $width < $size['width'] ) {
				$crop['x'] = intval( ( $size['width'] - $width ) / 2 );
				$crop['width'] = $width;
			}

			if ( $height < $size['height'] ) {
				$crop['y'] = intval( ( $size['height'] - $height ) / 2 );
				$crop['height'] = $height;
			}

			return $editor->crop( $crop['x'], $crop['y'], $crop['width'], $crop['height'] );
		}

		/**
		 * Crop an image to the specified width and height from specific coordinates in the image
		 *
		 * @param      $editor
		 * @param      $width
		 * @param      $height
		 * @param      $position
		 * @param bool $resize
		 *
		 * @return mixed
		 */
		private function crop_from_position( $editor, $width, $height, $position, $resize = true ) {

			$size = $editor->get_size();

			// resize to the largest dimension
			if ( $resize ) {

				$ratio1 = $size['width'] / $size['height'];
				$ratio2 = $width / $height;

				if ( $ratio1 < $ratio2 ) {
					$_width = $width;
					$_height = $width / $ratio1;
				} else {
					$_height = $height;
					$_width = $height * $ratio1;
				}

				$editor->resize( $_width, $_height );
			}

			$size = $editor->get_size();
			$crop = array( 'x' => 0, 'y' => 0 );

			if ( $position[0] == 'right' )
				$crop['x'] = absint( $size['width'] - $width );
			else if ( $position[0] == 'center' )
				$crop['x'] = intval( absint( $size['width'] - $width ) / 2 );

			if ( $position[1] == 'bottom' )
				$crop['y'] = absint( $size['height'] - $height );
			else if ( $position[1] == 'center' )
				$crop['y'] = intval( absint( $size['height'] - $height ) / 2 );


			return $editor->crop( $crop['x'], $crop['y'], $width, $height );
		}
	}
}