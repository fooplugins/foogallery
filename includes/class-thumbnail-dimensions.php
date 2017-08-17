<?php
/**
 * Class to calculate thumb dimensions for a gallery
 * Date: 21/03/2017
 */
if ( ! class_exists( 'FooGallery_Thumbnail_Dimensions' ) ) {

	class FooGallery_Thumbnail_Dimensions {

		function __construct() {
			if ( is_admin() ) {
				add_action( 'foogallery_after_save_gallery', array( $this, 'calculate_thumbnail_dimensions' ), 9, 2 );
			}

			add_filter( 'foogallery_attachment_load', array( $this, 'load_thumbnail_dimensions' ), 10, 2 );
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'include_thumb_dimension_attributes' ), 10, 3 );
		}

		/**
		 * Calculate the exact thumb size for the gallery and save the meta data
		 * @param $post_id
		 * @param $form_post
		 */
		function calculate_thumbnail_dimensions( $post_id, $form_post ) {
			$foogallery = FooGallery::get_by_id( $post_id );

			$gallery_template = $foogallery->gallery_template;

			$setting_key = "{$gallery_template}_thumbnail_dimensions";

			$default_thumbnail_dimensions = $foogallery->get_meta( $setting_key, false );

			$thumbnail_dimensions = apply_filters( 'foogallery_template_thumbnail_dimensions-' . $gallery_template, $default_thumbnail_dimensions, $foogallery );

			if ( isset( $thumbnail_dimensions ) && is_array( $thumbnail_dimensions ) ) {

				//$thumbnail_dimensions
				$thumb_width  = (int) $thumbnail_dimensions['width'];
				$thumb_height = (int) $thumbnail_dimensions['height'];
				$thumb_crop   = (bool) $thumbnail_dimensions['crop'];

				foreach ( $foogallery->attachments() as $attachment ) {

					$size_array = image_resize_dimensions( $attachment->width, $attachment->height, $thumb_width, $thumb_height, $thumb_crop );

					$size = array(
						'width'  => $size_array[4],
						'height' => $size_array[5]
					);

					$existing_size                  = get_post_meta( $attachment->ID, FOOGALLERY_META_THUMB_DIMENSIONS, true );
					$existing_size[$foogallery->ID] = $size;

					//save the post meta against the attachment
					update_post_meta( $attachment->ID, FOOGALLERY_META_THUMB_DIMENSIONS, $existing_size );
				}
			}
		}

		/**
		 * Load the thumb dimension attributes onto the attachment
		 * @param $foogallery_attachment
		 * @param $foogallery
		 *
		 * @return mixed
		 */
		function load_thumbnail_dimensions( $foogallery_attachment, $foogallery ) {
			$size = get_post_meta( $foogallery_attachment->ID, FOOGALLERY_META_THUMB_DIMENSIONS, true );
			if ( isset( $size ) && is_array( $size ) && array_key_exists( $foogallery->ID, $size ) ) {
				$size = $size[$foogallery->ID];

				$foogallery_attachment->foogallery_id = $foogallery->ID;
				$foogallery_attachment->thumb_width = $size['width'];
				$foogallery_attachment->thumb_height = $size['height'];
			}

			return $foogallery_attachment;
		}

		/**
		 * Include the thumb dimension html attributes in the rendered HTML
		 *
		 * @param $attr
		 * @param $args
		 * @param $foogallery_attachment
		 *
		 * @return array
		 */
		function include_thumb_dimension_attributes( $attr, $args, $foogallery_attachment ) {
			//do a check to see if the template has changed
			global $current_foogallery_arguments;
			global $current_foogallery;
			if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments['template'] ) ) {

				//we need to calculate new dynamic dimensions for the thumb
				$thumbnail_dimensions = apply_filters( 'foogallery_calculate_thumbnail_dimensions-' . $current_foogallery_arguments['template'], false, $current_foogallery_arguments );

				if ( $thumbnail_dimensions ) {
					//$thumbnail_dimensions
					$thumb_width  = (int) $thumbnail_dimensions['width'];
					$thumb_height = (int) $thumbnail_dimensions['height'];
					$thumb_crop   = (bool) $thumbnail_dimensions['crop'];

					$size_array                          = image_resize_dimensions( $foogallery_attachment->width, $foogallery_attachment->height, $thumb_width, $thumb_height, $thumb_crop );
					$foogallery_attachment->foogallery_id = $current_foogallery->ID;
					$foogallery_attachment->thumb_width  = $size_array[4];
					$foogallery_attachment->thumb_height = $size_array[5];
				}
			}

			if ( isset( $foogallery_attachment->foogallery_id ) ) {
				if ( $foogallery_attachment->thumb_width > 0 ) {
					$attr['width'] = $foogallery_attachment->thumb_width;
				}
				if ( $foogallery_attachment->thumb_height > 0 ) {
					$attr['height'] = $foogallery_attachment->thumb_height;
				}
			}

			return $attr;
		}
	}
}