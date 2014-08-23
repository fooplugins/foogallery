<?php

/**
 * Builds up a FooGallery album shortcode
 *
 * @param $album_id
 *
 * @return string
 */
function foogallery_build_album_shortcode( $album_id ) {
	return '[' . foogallery_album_shortcode_tag() . ' id="' . $album_id . '"]';
}

/**
 * Returns the album shortcode tag
 *
 * @return string
 */
function foogallery_album_shortcode_tag() {
	return apply_filters( 'foogallery_album_shortcode_tag', FOOGALLERY_CPT_ALBUM );
}