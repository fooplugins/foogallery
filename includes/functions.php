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
	$gallery_templates[] = array(
		'key'         => 'default',
		'name'        => 'Responsive Image Gallery',
		'description' => __( 'The default image gallery template : clean and responsive and looks good in any theme.' ),
		'author'      => 'FooPlugins',
		'author_url'  => 'http://fooplugins.com',
		'demo_url'    => 'http://fooplugins.com/plugins/foogallery',
		'thumbnail'   => FOOGALLERY_URL . 'templates/default/thumb.png',
		'fields'	  => array(
			array(
				'id'      => 'link_to_image',
				'title'   => __('Link To Image', 'foogallery'),
				'desc'    => __('Should the images in the gallery link to the full size images. If not set, then the images will link to the attachment page.', 'foogallery'),
				'default' => 'on' ,
				'type'    => 'checkbox',
				//'section' => __('Responsive Image Gallery Settings', 'foogallery'),
			),
			array(
				'id'      => 'lightbox',
				'title'   => __('Lightbox', 'foogallery'),
				'desc'    => __('Choose which lightbox you want to use in the gallery.', 'foogallery'),
				'type'    => 'select',
			),
		)
	);

	$gallery_templates[] = array(
		'key'         => 'masonry',
		'name'        => 'Masonry Image Gallery',
		'description' => __( 'A masonry-style image gallery template' ),
		'author'      => 'FooPlugins',
		'author_url'  => 'http://fooplugins.com',
		'demo_url'    => 'http://fooplugins.com/plugins/foogallery',
		'thumbnail'   => FOOGALLERY_URL . 'templates/masonry/thumb.png',
		'fields'	  => array(
			array(
				'id'      => 'link_to_image',
				'title'   => __('Link To Image2', 'foogallery'),
				'desc'    => __('Should the images in the gallery link to the full size images. If not set, then the images will link to the attachment page.', 'foogallery'),
				'default' => 'on' ,
				'type'    => 'checkbox',
				//'section' => __('Responsive Image Gallery Settings', 'foogallery'),
			),
			array(
				'id'      => 'lightbox',
				'title'   => __('Lightbox2', 'foogallery'),
				'desc'    => __('Choose which lightbox you want to use in the gallery.', 'foogallery'),
				'type'    => 'select',
			),
		)
	);

	return apply_filters( 'foogallery_gallery_templates', $gallery_templates );
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
 * Returns if the menu should move under Media
 *
 * @return bool
 */
function foogallery_use_media_menu() {
	return foogallery_get_setting( 'use_media_menu' );
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
		'use_media_menu'                => false,
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
 * Returns the correct FooGallery help page within the admin
 *
 * @return string The Url to the FooGallery help page in admin
 */
function foogallery_admin_help_url() {
	return esc_url( admin_url( add_query_arg( array( 'page' => 'foogallery-help' ), 'index.php' ) ) );
}

/**
 * Returns the correct FooGallery settings page within the admin
 *
 * @return string The Url to the FooGallery settings page in admin
 */
function foogallery_admin_settings_url() {
	return esc_url( admin_url( add_query_arg( array( 'page' => 'foogallery-settings' ), 'index.php' ) ) );
}