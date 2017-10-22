<?php
/**
 * Created by PhpStorm.
 * User: bradvin
 * Date: 2017/04/19
 * Time: 1:19 PM
 */


if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Settings' ) ) {

    class FooGallery_Admin_Gallery_MetaBox_Settings {

        /**
         * FooGallery_Admin_Gallery_MetaBox_Settings constructor.
         */
        function __construct() {
            //enqueue assets for the new settings tabs
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

            //set default settings tab icons
            add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons') );
        }

        /***
         * Enqueue the assets needed by the settings
         * @param $hook_suffix
         */
        function enqueue_assets( $hook_suffix ){
            if( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
                $screen = get_current_screen();

                if ( is_object( $screen ) && FOOGALLERY_CPT_GALLERY == $screen->post_type ){

                    // Register, enqueue scripts and styles here
                    wp_enqueue_script( 'foogallery-admin-settings', FOOGALLERY_URL . '/js/foogallery.admin.min.js', array('jquery'), FOOGALLERY_VERSION );
                    wp_enqueue_style( 'foogallery-admin-settings', FOOGALLERY_URL . '/css/foogallery.admin.min.css', array(), FOOGALLERY_VERSION );
                }
            }
        }

        /**
         * Returns the Dashicon that can be used in the settings tabs
         * @param $section_slug
         * @return string
         */
        function add_section_icons( $section_slug ) {
            switch ( $section_slug ) {
                case 'general':
                    return 'dashicons-format-image';
                case 'advanced':
                    return 'dashicons-admin-generic';
                case 'appearance':
                    return 'dashicons-admin-appearance';
                case 'video':
                    return 'dashicons-format-video';
				case 'hover effects':
					return 'dashicons-admin-tools';
				case 'captions':
					return 'dashicons-testimonial';
                case 'paging':
                    return 'dashicons-admin-page';
            }
            return 'dashicons-admin-tools';
        }
    }
}