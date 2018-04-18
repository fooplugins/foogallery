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

function foogallery_include_media_views_templates() {
	include FOOGALLERY_PRO_PATH . 'includes/foogallery-media-views-templates.php';
}