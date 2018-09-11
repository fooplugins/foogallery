<?php
/**
 * FooGallery PRO global functions
 */

/**
 * Enqueue the FooGallery Media Views stylesheet
 */
function foogallery_enqueue_media_views_style() {
	wp_enqueue_style( 'foogallery-media-views', FOOGALLERY_PRO_URL . 'css/foogallery.media-views.min.css' );
}

/**
 * Enqueue the FooGallery Media Views script
 */
function foogallery_enqueue_media_views_script() {
	wp_enqueue_script( 'foogallery-media-views', FOOGALLERY_PRO_URL . 'js/foogallery.media-views.min.js', array( 'jquery', 'media-views', 'underscore' ) );
}

/**
 * Include the media views templates used for the video import
 */
function foogallery_include_media_views_templates() {
	include FOOGALLERY_PRO_PATH . 'includes/foogallery-media-views-templates.php';
}


/**
 * Retrieve the Vimeo access code from the foogallery settings
 * @return mixed
 */
function foogallery_settings_get_vimeo_access_token() {
	return foogallery_get_setting( 'vimeo_access_token' );
}

/**
 * Save the Vimeo access token to the foogallery settings
 * @param $access_token
 */
function foogallery_settings_set_vimeo_access_token( $access_token ) {
	$foogallery = FooGallery_Plugin::get_instance();

	$foogallery->options()->save( 'vimeo_access_token', $access_token );
}