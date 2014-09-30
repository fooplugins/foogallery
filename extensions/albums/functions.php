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

/**
 * Return all the album templates used within FooGallery
 *
 * @return array
 */
function foogallery_album_templates() {
	$album_templates[] = array(
		'slug'        => 'default',
		'name'        => __( 'Default Album Layout', 'foogallery' ),
		'fields'	  => array(
			array(
				'id'      => 'back_to_album_text',
				'title'   => __( '"Back To Album" Text', 'foogallery' ),
				'desc'    => __( 'The text that is shown at the top of the album when a gallery is shown', 'foogallery' ),
				'type'    => 'text',
				'default' => '&laquo; back to album',
			)
		)
	);

	return apply_filters( 'foogallery_album_templates', $album_templates );
}

/**
 * Returns the default album template
 *
 * @return string
 */
function foogallery_default_album_template() {
	return foogallery_get_setting( 'album_template' );
}