<?php
/**
 * FooGallery default extensions common functions
 */

/**
 * Enqueue the core FooGallery stylesheet used by all default templates
 */
function foogallery_enqueue_core_gallery_template_style() {
	$css = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'css/foogallery.min.css';
	wp_enqueue_style( 'foogallery-core', $css, array(), FOOGALLERY_VERSION );
}

/**
 * Enqueue the core FooGallery script used by all default templates
 */
function foogallery_enqueue_core_gallery_template_script() {
	$js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/foogallery.min.js';
	wp_enqueue_script( 'foogallery-core', $js, array('jquery'), FOOGALLERY_VERSION );
}
