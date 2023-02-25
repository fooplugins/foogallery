<?php
/**
 * FooGallery default extensions common functions
 */

/**
 * Enqueue the core FooGallery stylesheet used by all default templates
 */
function foogallery_enqueue_core_gallery_template_style() {
	$filename = foogallery_is_debug() ? '' : '.min';
	$css = apply_filters( 'foogallery_core_gallery_style', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'css/foogallery' . $filename . '.css' );
	foogallery_enqueue_style( 'foogallery-core', $css, array(), FOOGALLERY_VERSION );

	if ( foogallery_get_setting( 'custom_css', '' ) !== '' ) {
		$custom_assets = get_option( FOOGALLERY_OPTION_CUSTOM_ASSETS );
		if ( is_array( $custom_assets ) && array_key_exists( 'style', $custom_assets ) ) {
			foogallery_enqueue_style( 'foogallery-custom', $custom_assets['style'], array('foogallery-core'), FOOGALLERY_VERSION );
		}
	}
}

/**
 * Enqueue the core FooGallery script used by all default templates
 *
 * @param string[] $deps
 */
function foogallery_enqueue_core_gallery_template_script( $deps = null ) {
	if ( isset( $deps ) ) {
		//ensure we deregister the previous one
		wp_deregister_script( 'foogallery-core' );
		do_action( 'foogallery_dequeue_script-core' );
	} else {
		//set the default
		$deps = array( 'jquery' );
	}

	$filename = foogallery_is_debug() ? '' : '.min';
	$js = apply_filters( 'foogallery_core_gallery_script', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/foogallery' . $filename . '.js' );
	$deps = apply_filters( 'foogallery_core_gallery_script_deps', $deps );

    if ( foogallery_get_setting( 'enqueue_polyfills', false ) ) {
        foogallery_enqueue_polyfills();
        $deps[] = 'foogallery-polyfills';
    }

	wp_enqueue_script( 'foogallery-core', $js, $deps, FOOGALLERY_VERSION );
	do_action( 'foogallery_enqueue_script-core', $js );

	if ( foogallery_get_setting( 'custom_js', '' ) !== '' ) {
		$custom_assets = get_option( FOOGALLERY_OPTION_CUSTOM_ASSETS );
		if ( is_array( $custom_assets ) && array_key_exists( 'script', $custom_assets ) ) {
			wp_enqueue_script( 'foogallery-custom', $custom_assets['script'], array('foogallery-core'), FOOGALLERY_VERSION );
		}
	}
}

/**
 * @return void
 *
 */
function foogallery_enqueue_polyfills() {
    $suffix = foogallery_is_debug() ? '' : '.min';
    $src    = apply_filters( 'foogallery_polyfills_src', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/foogallery.polyfills' . $suffix . '.js', $suffix );
    wp_enqueue_script( 'foogallery-polyfills', $src, array(), FOOGALLERY_VERSION );
}
