<?php
/**
 * FooGallery Import Export Class that registers the extension.
 */

if ( ! class_exists('FooGallery_Import_Export_Extension') ) {

    require_once 'class-foogallery-import-export.php';

    class FooGallery_Import_Export_Extension {

        /**
         * FooGallery_Import_Export_Extension constructor.
         */
        function __construct() {
            add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
        }

        /**
         * Register the extension
         *
         * @param $extensions_list
         *
         * @return array
         */
        function register_extension( $extensions_list ) {
            $extensions_list[] = array(
                'slug' => 'foogallery-import-export',
                'class' => 'FooGallery_Import_Export',
                'categories' => array( 'Utilities' ),
                'title' => __( 'Import Export', 'foogallery' ),
                'description' => __( 'Export your galleries, and then import them into another WordPress install.', 'foogallery' ),
                'external_link_text' => 'view documentation',
                'external_link_url' => 'https://fooplugins.com/documentation/foogallery/getting-started-foogallery/import-export/',
                'dashicon'          => 'dashicons-update',
                'tags' => array( 'utils', 'Free', ),
                'source' => 'bundled'
            );

            return $extensions_list;
        }
    }
}