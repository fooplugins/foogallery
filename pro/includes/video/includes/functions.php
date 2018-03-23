<?php
/**
 * Useful functions for FooGallery Videos Extension
 */

/**
 * Import Video as a WP attachment and return attachment info
 *
 * @param array $video_object The Video Object
 *
 * @return array
 */
function foogallery_foovideo_import_video_as_attachment( $video_object ) {

	//allow for really big imports that take a really long time!
	@set_time_limit(300);

	$thumbnail = $video_object['thumbnail'];
	$attachment_id = foogallery_foovideo_get_attachment_id_by_url( $thumbnail );

	if ( empty( $attachment_id ) ) {

		// Get the contents of the picture
		$response = wp_remote_get( $thumbnail );
		if ( is_wp_error( $response ) ) {
			//@todo throw error??
			return;
		}

		$contents = wp_remote_retrieve_body( $response );

		$thumbnail_filename = $video_object['id'] . '.' . pathinfo( basename( $thumbnail ), PATHINFO_EXTENSION );
		// Upload and get file data
		$upload    = wp_upload_bits( $thumbnail_filename, null, $contents );
		$guid      = $upload['url'];
		$file      = $upload['file'];
		$file_type = wp_check_filetype( basename( $file ), null );

		// Create attachment
		$attachment = array(
			'ID'             => 0,
			'guid'           => $guid,
			'post_title'     => $video_object['title'],
			'post_excerpt'   => $video_object['title'],
			'post_content'   => $video_object['description'],
			'post_date'      => '',
			'post_mime_type' => $file_type['type'],
		);

		// Include image.php so we can call wp_generate_attachment_metadata()
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Insert the attachment
		$attachment_id   = wp_insert_attachment( $attachment, $file, 0 );
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		$thumbnail_details = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ); // Yes we have the data, but get the thumbnail URL anyway, to be safe
		$thumbnail = $thumbnail_details[0];
	} else {
		//we are using an existing attachment, so do not upload another attachment, just update the info
		$attachment = array(
			'ID'             => $attachment_id,
			'post_title'     => $video_object['title'],
			'post_excerpt'   => $video_object['title'],
			'post_content'   => $video_object['description'],
		);
		wp_update_post( $attachment );
	}

	// Save alt text in the post meta
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $video_object['title'] );

	// Save the URL that we will be opening
	update_post_meta( $attachment_id, '_foogallery_custom_url', $video_object['url'] );

	// Make sure we open in new tab by default
	update_post_meta( $attachment_id, '_foogallery_custom_target', foogallery_get_setting( 'video_default_target', '_blank' ) );

	//save video object
	update_post_meta( $attachment_id, FOOVIDEO_POST_META, $video_object );

	// return the data
	return array(
		'id'  => $attachment_id,
		'src' => $thumbnail
	);

}

/**
 * Returns the number of videos for a specific gallery
 * @param $post_id
 *
 * @return int
 */
function foogallery_foovideo_get_gallery_video_count( $post_id ) {
	$video_count = get_post_meta( $post_id , FOOVIDEO_POST_META_VIDEO_COUNT, true );

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
function foogallery_foovideo_set_gallery_video_count( $post_id ) {
	$video_count = foogallery_foovideo_calculate_gallery_video_count( $post_id );
	if ( $video_count > 0 ) {
		update_post_meta( $post_id, FOOVIDEO_POST_META_VIDEO_COUNT, $video_count );
	} else {
		delete_post_meta( $post_id, FOOVIDEO_POST_META_VIDEO_COUNT );
	}
}

/**
 * Calculates the number of videos for a gallery
 * @param $post_id
 *
 * @return int
 */
function foogallery_foovideo_calculate_gallery_video_count( $post_id ) {
	$video_count = 0;
	$gallery = FooGallery::get_by_id( $post_id );
	if ( ! empty( $gallery->attachment_ids ) ) {
		foreach ( $gallery->attachment_ids as $id ) {
			$video_info = get_post_meta( $id, FOOVIDEO_POST_META, true );
			if ( isset( $video_info['id'] ) ) {
				//this attachment is a video
				$video_count++;
			}
		}
	}
	return $video_count;
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
function foogallery_foovideo_gallery_image_count_text( $total_count, $image_count, $video_count ) {
	//get image count text strings
	$images_single_text = foogallery_get_setting( 'language_images_count_single_text', __( '1 image', 'foogallery' ) );
	$images_plural_text = foogallery_get_setting( 'language_images_count_plural_text', __( '%s images', 'foogallery' ) );

	//get video count text strings
	$videos_none_text   = foogallery_get_setting( 'language_video_count_none_text',   __( 'No images or videos', 'foo-video' ) );
	$videos_single_text = foogallery_get_setting( 'language_video_count_single_text', __( '1 video', 'foo-video' ) );
	$videos_plural_text = foogallery_get_setting( 'language_video_count_plural_text', __( '%s videos', 'foo-video' ) );

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

/**
 * Custom encoding for JSON so that special characters are handled correctly
 *
 * @param $data
 *
 * @return string
 */
function foogallery_foovideo_esc_json_encode( $data ) {
	if ( defined( 'JSON_HEX_AMP' ) ) {
		// This is nice to have, but not strictly necessary since we use _wp_specialchars() below
		$data = json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
	} else {
		$data = json_encode( $data );
	}
	return _wp_specialchars( $data, ENT_QUOTES, false, true );
}

/**
 * Cleans the video URL before sending it to the client
 *
 * @param String $url The URL of the video in question
 *
 * @return String the cleaned up video URL
 */
function foogallery_foovideo_clean_video_url( $url ) {
	//make the video URL Protocol-relative to cater for sites that are HTTPS
	$url = str_replace( 'http://', '//', $url );

	return apply_filters( 'foogallery_foovideo_clean_video_url', $url );
}

/**
 * Build up the URL to the video URL from a FooGalleryAttachment object
 *
 * @param $attachment FooGalleryAttachment The attachment we want to extract the URL from
 *
 * @return String the video URL for the attachment
 */
function foogallery_foovideo_get_video_url_from_attachment( $attachment ) {
	if ( empty( $attachment ) ) return '';

	$url = $attachment->custom_url;

	//append autoplay querystring
	$autoplay = foogallery_gallery_template_setting( 'foovideo_autoplay', 'yes' );
	if ( 'yes' === $autoplay ) {
		$url = add_query_arg( 'autoplay', '1', $url );
	}

	return foogallery_foovideo_clean_video_url( $url );
}

function foogallery_foovideo_get_video_thumbnail_from_attachment( $attachment ) {
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
function foogallery_foovideo_youtubekey() {
	return apply_filters( 'foogallery_foovideo_youtubekey', 'AIzaSyBMT07ftYs1dGnguTdI8I_fXazRyrnZcEA' );
}

/**
 * Return attachment ID from a URL
 *
 * @param $url String URL to the image we are checking
 *
 * @return null or attachment ID
 */
function foogallery_foovideo_get_attachment_id_by_url($url) {
	global $wpdb;
	$query = "SELECT ID FROM {$wpdb->posts} WHERE guid=%s";
	$attachment = $wpdb->get_col( $wpdb->prepare( $query, $url ) );
	if ( count( $attachment ) > 0 ) {
		return $attachment[0];
	}
	return null;
}