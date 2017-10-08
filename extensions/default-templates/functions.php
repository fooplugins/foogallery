<?php
/**
 * FooGallery default extensions common functions
 */

/**
 * Enqueue the core FooGallery stylesheet used by all default templates
 */
function foogallery_enqueue_core_gallery_template_style() {
	$filename = foogallery_get_setting( 'enable_debugging', false ) ? '' : '.min';
	$css = apply_filters( 'foogallery_core_gallery_style', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'css/foogallery' . $filename . '.css' );
	foogallery_enqueue_style( 'foogallery-core', $css, array(), FOOGALLERY_VERSION );
}

/**
 * Enqueue the core FooGallery script used by all default templates
 */
function foogallery_enqueue_core_gallery_template_script() {
	$filename = foogallery_get_setting( 'enable_debugging', false ) ? '' : '.min';
	$js = apply_filters( 'foogallery_core_gallery_script', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/foogallery' . $filename . '.js' );
	wp_enqueue_script( 'foogallery-core', $js, array('jquery'), FOOGALLERY_VERSION );
}
