<?php
/**
 * Class to calculate thumb dimensions for a gallery
 * Date: 21/03/2017
 */
if ( ! class_exists( 'FooGallery_Thumbnail_Dimensions' ) ) {

	class FooGallery_Thumbnail_Dimensions {

		function __construct() {
			if ( is_admin() ) {
				add_action( 'foogallery_before_save_gallery', array( $this, 'start_watching_for_thumbnail_generation_calls' ), 10, 2 );
				add_action( 'foogallery_after_save_gallery', array( $this, 'stop_watching_for_thumbnail_generation_calls' ), 99, 2 );
			}
		}

		/**
		 * Start watching for calls to the thumbnail generator so we can intercept and store info about the thumbs
		 *
		 * @param $post_id
		 * @param $form_post
		 */
		function start_watching_for_thumbnail_generation_calls( $post_id, $form_post ) {
			global $foogallery_thumb_dimensions;

			//clear the array
			if ( empty( $foogallery_thumb_dimensions ) ) {
				$foogallery_thumb_dimensions[$post_id] = array();
			}

			//and hook up actions
			add_filter( 'wpthumb_image_post', array( $this, 'intercept_thumb_generation' ), 10, 2 );
			add_filter( 'foogallery_thumbnail_resize_args', array( $this, 'change_thumbnail_resize_args' ), 10, 3 );
		}

		/**
		 * Stop watching for calls to the thumbnail generator
		 *
		 * @param $post_id
		 * @param $form_post
		 */
		function stop_watching_for_thumbnail_generation_calls( $post_id, $form_post ) {
			global $foogallery_thumb_dimensions;

			//if we have nothing to store then get out
			if ( empty( $foogallery_thumb_dimensions ) || !array_key_exists( $post_id, $foogallery_thumb_dimensions ) ) {
				return;
			}

			//check if we have data and store it against the foogallery
			update_post_meta( $post_id, FOOGALLERY_META_THUMB_DIMENSIONS, $foogallery_thumb_dimensions[$post_id] );

			//unhook the action
			remove_filter( 'wpthumb_image_post', array( $this, 'intercept_thumb_generation' ), 10 );
			remove_filter( 'foogallery_thumbnail_resize_args', array( $this, 'change_thumbnail_resize_args' ), 10 );
		}

		/**
		 * Intercept calls to the thumb generation and store dimensions values
		 *
		 * @param $editor
		 * @param $args
		 */
		function intercept_thumb_generation( $editor, $args ) {
			global $foogallery_thumb_dimensions;

			//if we have nothing stored then get out
			if ( empty( $foogallery_thumb_dimensions ) ) {
				return;
			}

			//if we are not generating a thumb for a foogallery then get out
			if ( !array_key_exists( 'foogallery_id', $args ) ) {
				return;
			}

			//if we are not generating a thumb for a foogallery attachment then get out
			if ( !array_key_exists( 'foogallery_attachment_id', $args ) ) {
				return;
			}

			$foogallery_id = $args['foogallery_id'];
			$foogallery_attachment_id = $args['foogallery_attachment_id'];
			$size = $editor->get_size();

			$foogallery_thumb_dimensions[$foogallery_id][$foogallery_attachment_id] = $size;
		}

		/**
		 * Change any arguments passed to the thumb generation class
		 *
		 * @param $args
		 * @param $original_image_src
		 * @param $thumbnail_object
		 *
		 * @return mixed
		 */
		function change_thumbnail_resize_args($args, $original_image_src, $thumbnail_object) {
			//we only want to force the thumb to be generated even if done so before
			$args['cache'] = false;
			return $args;
		}
	}
}