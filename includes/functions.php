<?php
/**
 * FooGallery global functions
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2014 FooPlugins LLC
 */

/**
 * Return all the gallery templates used within FooGallery
 *
 * @return array
 */
function foogallery_gallery_templates() {
	return apply_filters( 'foogallery_gallery_templates', array() );
}

/**
 * Return the FooGallery extension API class
 *
 * @return
 */
function foogallery_extensions_api() {
	return new FooGallery_Extensions_API();
}

/**
 * Returns the default gallery template
 *
 * @return string
 */
function foogallery_default_gallery_template() {
	return foogallery_get_setting( 'gallery_template' );
}

/**
 * Returns if gallery permalinks are enabled
 *
 * @return bool
 */
function foogallery_permalinks_enabled() {
	return foogallery_get_setting( 'gallery_permalinks_enabled' );
}

/**
 * Returns the gallery permalink
 *
 * @return string
 */
function foogallery_permalink() {
	return foogallery_get_setting( 'gallery_permalink' );
}

function foogallery_get_setting( $key ) {
	$foogallery = FooGallery_Plugin::get_instance();

	return $foogallery->options()->get( $key, foogallery_get_default( $key ) );
}

/**
 * Builds up a FooGallery gallery shortcode
 *
 * @param $gallery_id
 *
 * @return string
 */
function foogallery_build_gallery_shortcode( $gallery_id ) {
	return '[' . FOOGALLERY_CPT_GALLERY . ' id="' . $gallery_id . '"]';
}

/**
 * Helper method for getting default settings
 *
 * @param string $key   The default config key to retrieve.
 * @return string       Key value on success, false on failure.
 */
function foogallery_get_default( $key ) {

	$defaults = array(
		'gallery_template'              => 'default',
		'gallery_permalinks_enabled'    => false,
		'gallery_permalink'             => 'gallery',
		'lightbox'                      => 'none'
	);

	// A handy filter to override the defaults
	$defaults = apply_filters( 'foogallery_defaults', $defaults );

	// Return the key specified.
	return isset( $defaults[$key] ) ? $defaults[$key] : false;
}

/**
 * Returns the FooGallery help page Url within the admin
 *
 * @return string The Url to the FooGallery help page in admin
 */
function foogallery_admin_help_url() {
	return esc_url( admin_url( add_query_arg( array( 'page' => 'foogallery-help' ), 'index.php' ) ) );
}

/**
 * Returns the FooGallery settings page Url within the admin
 *
 * @return string The Url to the FooGallery settings page in admin
 */
function foogallery_admin_settings_url() {
	return esc_url( admin_url( add_query_arg( array( 'page' => 'foogallery-settings' ), 'index.php' ) ) );
}

/**
 * Returns the FooGallery extensions page Url within the admin
 *
 * @return string The Url to the FooGallery extensions page in admin
 */
function foogallery_admin_extensions_url() {
	return esc_url( admin_url( add_query_arg( array( 'page' => 'foogallery-extensions' ), 'index.php' ) ) );
}