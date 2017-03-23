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

			$thumbnail_dimensions = apply_filters( 'foogallery_template_thumbnail_dimensions-' . $gallery_template, $foogallery->get_meta( $setting_key, false ), $foogallery );

			foreach ( $foogallery->attachments() as $attachment ) {
				//$thumbnail_dimensions
				$thumb_width = (int) $thumbnail_dimensions['width'];
				$thumb_height = (int) $thumbnail_dimensions['height'];
				$thumb_crop = (bool) $thumbnail_dimensions['crop'];

				$size_array = image_resize_dimensions( $attachment->width, $attachment->height, $thumb_width, $thumb_height, $thumb_crop );

				$size = array(
					'width'  => $size_array[4],
					'height' => $size_array[5]
				);

				$existing_size = get_post_meta( $attachment->ID, FOOGALLERY_META_THUMB_DIMENSIONS, true );
				$existing_size[$foogallery->ID] = $size;

				//save the post meta against the attachment
				update_post_meta( $attachment->ID, FOOGALLERY_META_THUMB_DIMENSIONS, $existing_size );
			}
		}

		function load_thumbnail_dimensions( $foogallery_attachment, $foogallery ) {
			$size = get_post_meta( $foogallery_attachment->ID, FOOGALLERY_META_THUMB_DIMENSIONS, true );
			if ( $size && array_key_exists( $foogallery->ID, $size ) ) {
				$size = $size[$foogallery->ID];

				$foogallery_attachment->foogallery_id = $foogallery->ID;
				$foogallery_attachment->thumb_width = $size['width'];
				$foogallery_attachment->thumb_height = $size['width'];
			}

			return $foogallery_attachment;
		}
	}
}