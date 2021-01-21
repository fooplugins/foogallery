<?php
/**
 * Base class for the thumbnail engine
 */
if ( ! class_exists( 'FooGallery_Thumb_Engine' ) ) {

	abstract class FooGallery_Thumb_Engine {

		/**
		 * Does any initilization needed for the engine
		 */
		abstract function init();

		/**
		 * Generates a thumbnail for an image based on arguments
		 *
		 * @param       $url
		 * @param array $args
		 *
		 * @return string
		 */
		abstract function generate( $url, $args = array() );

		/**
		 * Does the engine use a local cache to store thumbnails
		 * @return bool
		 */
		abstract function has_local_cache();

		/**
		 * Clears the local cach for a file
		 * @param $file
		 */
		abstract function clear_local_cache_for_file( $file );

		/**
		 * Returns the last error encountered when trying to generate a thumbnail
		 * @return mixed
		 */
		abstract function get_last_error();

		/**
		 * Returns true if the engine utilizes WordPress Image Editors under the hood
		 * By default, if the engine has a local cache, then they would also use image editors
		 *
		 * @return bool
		 */
		function uses_image_editors() {
			return $this->has_local_cache();
		}

		/**
		 * Returns true if the engine requires thumb generation tests to be performed
		 * By default, if the engine has a local cache, then they would also use require tests
		 *
		 * @return bool
		 */
		function requires_thumbnail_generation_tests() {
			return $this->has_local_cache();
		}
	}
}