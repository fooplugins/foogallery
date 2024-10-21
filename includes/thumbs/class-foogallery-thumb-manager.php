<?php

namespace FooPlugins\FooGallery\Thumbs;

/**
 * Class that manages all thumbnail generation within FooGallery
 */
if ( ! class_exists( 'FooGallery_Thumb_Manager' ) ) {

    class FooGallery_Thumb_Manager {

        function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init_active_engine' ) );
        }

        /**
         * Make sure the active thumb engine initializes
         */
        function init_active_engine() {
            $engine = foogallery_thumb_active_engine();
            $engine->init();
            add_filter( 'wp_image_editors', array( $this, 'override_image_editors' ), 999 );
        }

        /**
         * Overrides the editors to make sure the FooGallery thumb editors are included
         *
         * @param array $editors
         * @return array
         */
        function override_image_editors( $editors ) {

            $image_editors = array();

            // Replace the default image editors with the FooGallery Thumb image editors
            foreach ( $editors as $editor ) {
                switch ( $editor ) {
                    case 'WP_Image_Editor_Imagick':
                        $image_editors[] = 'FooPlugins\\FooGallery\\Thumbs\\FooGallery_Thumb_Image_Editor_Imagick';
                        break;
                    case 'WP_Image_Editor_GD':
                        $image_editors[] = 'FooPlugins\\FooGallery\\Thumbs\\FooGallery_Thumb_Image_Editor_GD';
                        break;
                    default:
                        $image_editors[] = $editor;
                }
            }

            // Make sure the order is correct
            if ( foogallery_get_setting( 'force_gd_library', false ) ) {
                array_splice( $image_editors, 0, 0, array('FooPlugins\\FooGallery\\Thumbs\\FooGallery_Thumb_Image_Editor_GD') );
            }

            // Make sure we have a unique list of editors
            return array_unique( $image_editors );
        }
    }
}
