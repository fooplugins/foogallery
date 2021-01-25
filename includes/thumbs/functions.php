<?php
/**
 * functions related to thumbnail generation within FooGallery
 */

/**
 * Returns the array of available engines
 *
 * @return array
 */
function foogallery_thumb_available_engines() {
	$engines = array(
		'default' => array(
			'label' => __( 'Default', 'foogallery' ),
			'description' => __( 'The default engine used to generate locally cached thumbnails.', 'foogallery' ),
			'class' => 'FooGallery_Thumb_Engine_Default'
		)
	);

	if ( foogallery_is_debug() ) {
		$engines['dummy'] = array(
			'label' => __( 'Dummy', 'foogallery' ),
			'description' => __( 'A dummy thumbnail engine that can be used for testing. (uses dummyimage.com)', 'foogallery' ),
			'class' => 'FooGallery_Thumb_Engine_Dummy'
		);
	}

	return apply_filters( 'foogallery_thumb_available_engines', $engines );
}

/**
 * Returns the active thumb engine, based on settings
 *
 * @return FooGallery_Thumb_Engine
 */
function foogallery_thumb_active_engine() {
	global $foogallery_thumb_engine;

	//if we already have an engine, return it early
	if ( isset( $foogallery_thumb_engine ) && is_a( $foogallery_thumb_engine, 'FooGallery_Thumb_Engine' ) ) {
		return $foogallery_thumb_engine;
	}

	$engine = foogallery_get_setting( 'thumb_engine', 'default' );
	$engines = foogallery_thumb_available_engines();

	if ( array_key_exists( $engine, $engines ) ) {
		$active_engine = $engines[$engine];
		$foogallery_thumb_engine = new $active_engine['class'];
	} else {
		$foogallery_thumb_engine = new FooGallery_Thumb_Engine_Default();
	}

	return $foogallery_thumb_engine;
}

/**
 * Resizes a given image using the active thumb engine.
 *
 * @param      mixed   absolute path to the image
 * @param int  $width  .
 * @param int  $height .
 * @param bool $crop   . (default: false)
 * @return (string) url to the image
 */
function foogallery_thumb( $url, $args = array() ) {
	$engine = foogallery_thumb_active_engine();
	return $engine->generate( $url, $args );
}