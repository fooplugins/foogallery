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

function foogallery_get_setting($key) {
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
function foogallery_build_gallery_shortcode($gallery_id) {
	return '[' . FOOGALLERY_CPT_GALLERY . ' id="' . $gallery_id . '"]';
}

/**
 * Helper method for getting default settings
 *
 * @param string $key The default config key to retrieve.
 *
 * @return string       Key value on success, false on failure.
 */
function foogallery_get_default($key) {

	$defaults = array(
		'gallery_template'           => 'default',
		'gallery_permalinks_enabled' => false,
		'gallery_permalink'          => 'gallery',
		'lightbox'                   => 'none'
	);

	// A handy filter to override the defaults
	$defaults = apply_filters( 'foogallery_defaults', $defaults );

	// Return the key specified.
	return isset($defaults[$key]) ? $defaults[$key] : false;
}

/**
 * Returns the FooGallery help page Url within the admin
 *
 * @return string The Url to the FooGallery help page in admin
 */
function foogallery_admin_help_url() {
	return esc_url( admin_url( add_query_arg( array('page' => 'foogallery-help'), 'index.php' ) ) );
}

/**
 * Returns the FooGallery settings page Url within the admin
 *
 * @return string The Url to the FooGallery settings page in admin
 */
function foogallery_admin_settings_url() {
	return esc_url( admin_url( add_query_arg( array('page' => 'foogallery-settings'), 'index.php' ) ) );
}

/**
 * Returns the FooGallery extensions page Url within the admin
 *
 * @return string The Url to the FooGallery extensions page in admin
 */
function foogallery_admin_extensions_url() {
	return esc_url( admin_url( add_query_arg( array('page' => 'foogallery-extensions'), 'index.php' ) ) );
}

/**
 * @TODO
 * @param      $key
 * @param bool $default
 *
 * @return bool
 */
function foogallery_gallery_template_setting( $key, $default = false ) {
	global $current_foogallery;
	global $current_foogallery_arguments;
	global $current_foogallery_template;

	$settings_key = "{$current_foogallery_template}_{$key}";

	if ( $current_foogallery_arguments && array_key_exists( $key, $current_foogallery_arguments ) ) {
		//try to get the value from the arguments
		$value = $current_foogallery_arguments[ $key ];

	} else if ( $current_foogallery->settings && array_key_exists( $settings_key, $current_foogallery->settings ) ) {
		//then get the value out of the saved gallery settings
		$value = $current_foogallery->settings[$settings_key];
	} else {
		//otherwise set it to the default
		$value = $default;
	}

	$value = apply_filters( 'foogallery_gallery_template_setting-' . $key, $value );

	return $value;
}

/**
 * @TODO
 * @param        $attachment_id
 * @param string $size
 * @param string $link
 *
 * @return string
 */
function foogallery_get_attachment_html( $attachment_id, $size = 'thumbnail', $link = 'image' ) {
	if ( 'none' === $link ) {
		return wp_get_attachment_image( $attachment_id, $size );
	}
	return wp_get_attachment_link( $attachment_id, $size, ( 'page' === $link ) );
}

/**
 * @TODO
 * @return string
 */
function foogallery_admin_menu_parent_slug() {
	return apply_filters( 'foogallery_menu_parent_slug', FOOGALLERY_ADMIN_MENU_PARENT_SLUG );
}

/**
 * @TODO
 * @param array $extra_args
 *
 * @return string|void
 */
function foogallery_build_admin_menu_url( $extra_args = array() ) {
	$url = admin_url( foogallery_admin_menu_parent_slug() );
	if ( !empty( $extra_args ) ) {
		$url = add_query_arg( $extra_args, $url );
	}
	return $url;
}