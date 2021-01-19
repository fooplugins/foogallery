<?php

/**
 * Class for the default thumbnail engine in FooGallery
 */
if ( ! class_exists( 'FooGallery_Thumb_Engine_Default' ) ) {

	class FooGallery_Thumb_Engine_Default extends FooGallery_Thumb_Engine {

		public function init() {
			add_filter( 'wp_image_editors', array( $this, 'override_image_editors' ), 999 );
			add_filter( 'deleted_post', array( $this, 'delete_cache_folder_for_attachment' ), 10, 2 );

			//add background fill functionality
			new FooGallery_Thumb_Generator_Background_Fill();
		}

		/**
		 * The default engine uses a local cache to store thumbnails
		 *
		 * @return bool
		 */
		public function has_local_cache() {
			return true;
		}

		/**
		 * Generates the thumbnail and returns the thumb URL
		 *
		 * @param       $url
		 * @param array $args
		 *
		 * @return string|void
		 */
		function generate( $url, $args = array() ) {
			$generator = new FooGallery_Thumb_Generator( $url, $args );
			return $generator->generate();
		}

		/**
		 * Delete the cache directory for a file
		 *
		 * @param $file
		 */
		public function clear_local_cache_for_file( $file ) {
			$thumb = new FooGallery_Thumb_Generator( $file );
			$directory = $thumb->get_cache_file_directory();

			if ( false === $directory ) {
				return;
			}

			//use the WP FileSystem to remove the folder recursively
			$fs = foogallery_wp_filesystem();
			if ( $fs !== false ) {
				$fs->rmdir( $directory, true );
			}
		}

		/**
		 * Overrides the editors to make sure the FooGallery thumb editors are included
		 *
		 * @param $editors
		 * @return array
		 */
		function override_image_editors( $editors ) {

			require_once( FOOGALLERY_PATH . '/includes/thumbs/default/class-foogallery-thumb-image-editor-gd.php' );
			require_once( FOOGALLERY_PATH . '/includes/thumbs/default/class-foogallery-thumb-image-editor-imagick.php' );

			$image_editors = array();

			//replace the default image editors with the FooGallery Thumb image editors
			foreach ( $editors as $editor ) {
				switch ( $editor ) {
					case 'WP_Image_Editor_Imagick':
						$image_editors[] = 'FooGallery_Thumb_Image_Editor_Imagick';
						break;
					case 'WP_Image_Editor_GD':
						$image_editors[] = 'FooGallery_Thumb_Image_Editor_GD';
						break;
					default:
						$image_editors[] = $editor;
				}
			}

			//Make sure the order is correct
			if ( foogallery_get_setting( 'force_gd_library', false ) ) {
				array_splice( $image_editors, 0, 0, array('FooGallery_Thumb_Image_Editor_GD') );
			}

			//make sure we have a unique list of editors
			return array_unique( $image_editors );
		}

		/**
		 * Hook into deleted_post and delete the associated cache file folder for an attachment
		 *
		 * @param string $post_id
		 *
		 * @return string
		 */
		function delete_cache_folder_for_attachment( $post_id, $post ) {
			$url = wp_get_attachment_url( $post_id );

			if ( $url !== false ) {
				$this->clear_local_cache_for_file( $url );
			}
		}
	}
}