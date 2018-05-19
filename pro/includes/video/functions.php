<?php
/**
 * Useful functions for FooGallery PRO Videos
 */

/**
 * Returns the number of videos for a specific gallery
 * @param $post_id
 *
 * @return int
 */
function foogallery_get_gallery_video_count( $post_id ) {
	$video_count = get_post_meta( $post_id , FOOGALLERY_VIDEO_POST_META_VIDEO_COUNT, true );

	if ( !empty( $video_count ) ) {
		return absint( $video_count );
	}

	return 0;
}

/**
 * Sets the number of videos for a gallery
 *
 * @param $post_id
 */
function foogallery_set_gallery_video_count( $post_id ) {
	$video_count = foogallery_calculate_gallery_video_count( $post_id );
	if ( $video_count > 0 ) {
		update_post_meta( $post_id, FOOGALLERY_VIDEO_POST_META_VIDEO_COUNT, $video_count );
	} else {
		delete_post_meta( $post_id, FOOGALLERY_VIDEO_POST_META_VIDEO_COUNT );
	}
}

/**
 * Calculates the number of videos for a gallery
 * @param $post_id
 *
 * @return int
 */
function foogallery_calculate_gallery_video_count( $post_id ) {
	$video_count = 0;
	$gallery = FooGallery::get_by_id( $post_id );

	foreach ( $gallery->attachments() as $attachment ) {
		if ( foogallery_is_attachment_video( $attachment ) ) {
			//this attachment is a video
			$video_count++;
		}
	}
	return $video_count;
}

/**
 * Determines if an attachment is a video
 *
 * @param      $attachment_id
 *
 * @param FooGalleryAttachment $foogallery_attachment
 *
 * @return bool
 */
function foogallery_is_attachment_video( $foogallery_attachment ) {
	if ( isset( $foogallery_attachment ) && isset( $foogallery_attachment->_post ) ) {
		if ( 'image/foogallery' === $foogallery_attachment->_post->post_mime_type ) {
			//we are definitely dealing with a video
			return true;
		}
	}

	//allow legacy to override
	return apply_filters( 'foogallery_is_attachment_video', false, $foogallery_attachment );
}

/**
 * Returns the video and image count for a gallery
 *
 * @param $total_count
 * @param $image_count
 * @param $video_count
 *
 * @return string|void
 */
function foogallery_gallery_image_count_text( $total_count, $image_count, $video_count ) {
	//get image count text strings
	$images_single_text = foogallery_get_setting( 'language_images_count_single_text', __( '1 image', 'foogallery' ) );
	$images_plural_text = foogallery_get_setting( 'language_images_count_plural_text', __( '%s images', 'foogallery' ) );

	//get video count text strings
	$videos_none_text   = foogallery_get_setting( 'language_video_count_none_text',   __( 'No images or videos', 'foogallery' ) );
	$videos_single_text = foogallery_get_setting( 'language_video_count_single_text', __( '1 video', 'foogallery' ) );
	$videos_plural_text = foogallery_get_setting( 'language_video_count_plural_text', __( '%s videos', 'foogallery' ) );

	if ( 0 == $total_count ) {
		return $videos_none_text;
	} else if ( 0 == $video_count ) {
		//return the original text
		return sprintf( 1 == $image_count ? $images_single_text : $images_plural_text, $image_count );
	} else if ( 0 == $image_count ) {
		//we only have videos
		return sprintf( 1 == $video_count ? $videos_single_text : $videos_plural_text, $video_count );
	} else {
		//we have a mix of images and videos
		return sprintf( 1 == $image_count ? $images_single_text : $images_plural_text, $image_count )
		       . '; ' .
		       sprintf( 1 == $video_count ? $videos_single_text : $videos_plural_text, $video_count );
	}
}

///**
// * Custom encoding for JSON so that special characters are handled correctly
// *
// * @param $data
// *
// * @return string
// */
//function foogallery_esc_json_encode( $data ) {
//	if ( defined( 'JSON_HEX_AMP' ) ) {
//		// This is nice to have, but not strictly necessary since we use _wp_specialchars() below
//		$data = json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
//	} else {
//		$data = json_encode( $data );
//	}
//	return _wp_specialchars( $data, ENT_QUOTES, false, true );
//}

/**
 * Cleans the video URL before sending it to the client
 *
 * @param String $url The URL of the video in question
 *
 * @return String the cleaned up video URL
 */
function foogallery_clean_video_url( $url ) {
	//make the video URL Protocol-relative to cater for sites that are HTTPS
	$url = str_replace( 'http://', '//', $url );

	return apply_filters( 'foogallery_clean_video_url', $url );
}

/**
 * Build up the URL to the video URL from a FooGalleryAttachment object
 *
 * @param $attachment FooGalleryAttachment The attachment we want to extract the URL from
 *
 * @return String the video URL for the attachment
 */
function foogallery_get_video_url_from_attachment( $attachment ) {
	if ( empty( $attachment ) ) return '';

	$url = $attachment->custom_url;

	//append autoplay querystring
	$autoplay = foogallery_gallery_template_setting( 'foovideo_autoplay', 'yes' );
	if ( 'yes' === $autoplay ) {
		$url = add_query_arg( 'autoplay', '1', $url );
	}

	return foogallery_clean_video_url( $url );
}

function foogallery_get_video_thumbnail_from_attachment( $attachment ) {
	$args = array(
		'width'  => 90,
		'height' => 90,
		'crop'   => true
	);

	return apply_filters( 'foogallery_attachment_resize_thumbnail', $attachment->url, $args, $attachment );
}

/**
 * Return an API key that youtube can use to request video info
 * @return mixed|void
 */
function foogallery_youtubekey() {
	return apply_filters( 'foogallery_youtubekey', 'AIzaSyBMT07ftYs1dGnguTdI8I_fXazRyrnZcEA' );
}
