<?php
/**
 * FooGallery Extensions Compatibility Class
 * Date: 20 Sep 2017
 *
 * This class is used to make any overrides that are needed in extensions when updating FooGallery.
 * These are "hacks" that will make the upgrade process easier, and not have the requirement to upgrade the individual extensions.
 *
 */
if ( ! class_exists( 'FooGallery_Extensions_Compatibility' ) ) {

    class FooGallery_Extensions_Compatibility {

        function __construct() {
			add_filter( 'foogallery_attachment_html_item_classes', array( $this, 'add_video_class_to_item' ), 10, 3 );
        }

		/**
		 * @param $classes
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return mixed
		 */
		public function add_video_class_to_item( $classes, $foogallery_attachment, $args ) {
			if ( class_exists( 'Foo_Video' ) ) {
				$video_info = get_post_meta( $foogallery_attachment->ID, FOO_VIDEO_POST_META, true );
				if ( $video_info && isset( $video_info['id'] ) ) {
					//we are dealing with a video
					$classes[] = 'fg-video';

					//include a specific css file to override issues with video hover effects
					$css = FOOGALLERY_URL . 'css/foogallery-foovideo-overrides.css';
					foogallery_enqueue_style( 'foogallery_foovideo_overrides', $css, array(), FOOGALLERY_VERSION );
				}
			}

            return $classes;
		}
    }
}
