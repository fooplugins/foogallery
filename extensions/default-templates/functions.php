<?php
/**
 * FooGallery default extensions common functions
 */

/***
 * Enqueue the imagesLoaded script file
 */
function foogallery_enqueue_imagesloaded_script() {
    global $wp_version;
    if ( version_compare( $wp_version, '4.6' ) >= 0 ) {

        wp_enqueue_script('imagesloaded');

    } else {

        //include our own version of imagesLoaded for <4.6
        $js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/imagesloaded.pkgd.min.js';
        wp_enqueue_script( 'foogallery-imagesloaded', $js, array(), FOOGALLERY_VERSION );
    }
}