<?php
/**
 * FooGallery default extensions common functions
 */

/***
 * Enqueue the imagesLoaded script file
 */
function foogallery_enqueue_imagesloaded_script() {
	$js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/imagesloaded.pkgd.min.js';
	wp_enqueue_script( 'foogallery-imagesloaded', $js, array(), FOOGALLERY_VERSION );
}