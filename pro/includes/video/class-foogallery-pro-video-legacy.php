<?php
/**
 * All Legacy FooVideo Code lives in this class
 * Date: 19/05/2018
 */
if ( ! class_exists( 'FooGallery_Pro_Video_Legacy' ) ) {

	class FooGallery_Pro_Video_Legacy {

		function __construct() {
			add_filter( 'foogallery_is_attachment_video', array( $this, 'foogallery_is_attachment_video_legacy' ) );

			add_filter( 'foogallery_clean_video_url', array( $this, 'foogallery_clean_video_url_legacy_filter' ) );
			add_filter( 'foogallery_youtubekey', array( $this, 'foogallery_youtubekey_legacy_filter' ) );
		}

		/**
		 * Legacy way of knowing if an attachment is a video
		 *
		 * @param $is_video
		 * @param $attachment_id
		 * @param $video_info
		 *
		 * @return bool
		 */
		function foogallery_is_attachment_video_legacy( $is_video, $attachment_id, $video_info ) {
			return $is_video || isset( $video_info ) && isset( $video_info['id'] );
		}

		/**
		 * Applies the legacy filter for backwards compatibility
		 * @param $url
		 *
		 * @return string
		 */
		function foogallery_clean_video_url_legacy_filter( $url ) {
			return apply_filters( 'foogallery_foovideo_clean_video_url', $url );
		}

		/**
		 * Applies the legacy filter for backwards compatibility
		 * @param $key
		 *
		 * @return string
		 */
		function foogallery_youtubekey_legacy_filter( $key ) {
			return apply_filters( 'foogallery_foovideo_youtubekey', $key );
		}
	}
}