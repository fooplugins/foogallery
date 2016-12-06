<?php
/**
 * WPThumb Enhancements Class
 * Date: 7 Nov 2016
 *
 * Some themes include a filter to override the ORDER of the image editors to be used,
 * so that the GD library is used as a preference over the Imagick library.
 * This is acceptable, but WPThumb requires it's own overrides of the WP_Image_Editor_GD
 * and WP_Image_Editor_Imagick classes (set in wpthumb.php 'wpthumb_add_image_editors'
 * function). An example of this filter code is:
 *
 *   add_filter( 'wp_image_editors', 'change_graphic_lib' );
 *   function change_graphic_lib($array) {
 *     return array( 'WP_Image_Editor_GD', 'WP_Image_Editor_Imagick' );
 *   }
 *
 * The theme's filter runs after the WPThumb filter, so the override classes needed
 * by WPThumb are ignored. This stops WPThumb from working altogether. To get around
 * this we need to override the image editors later (priority 999) and "force" the
 * usage of the WPThumb override classes, while still preserving the order set by the
 * theme author, or server administrator.
 *
 * The hosting provider's decision to use GD over Imagick is usually due to a timeout
 * that occurs when large images are uploaded to the media library. The PHP setting
 * for memory on the server could be too low, and this causes Imagick to timeout.
 * Switching to GD usually fixes the problem, without needing to change memory limits.
 *
 */
if ( ! class_exists( 'FooGallery_WPThumb_Enhancements' ) ) {

    class FooGallery_WPThumb_Enhancements {

        function __construct() {
            add_filter( 'wp_image_editors', array( $this, 'override_image_editors' ), 999 );
        }

        /**
         * Overrides the editors to make sure the WPThumb editors are included
         *
         * @param $editors
         * @return array
         */
        function override_image_editors($editors) {

            $wpthumb_editors = array();

            //replace the default image editors with the WPThumb image editors.
            // also preserve the order so that certain hosts work as expected
            foreach ($editors as $editor) {
                switch ($editor) {
                    case 'WP_Image_Editor_Imagick':
                        $wpthumb_editors[] = 'WP_Thumb_Image_Editor_Imagick';
                        break;
                    case 'WP_Image_Editor_GD':
                        $wpthumb_editors[] = 'WP_Thumb_Image_Editor_GD';
                        break;
                    default:
                        $wpthumb_editors[] = $editor;
                }
            }

            //make sure we have a unique list of editors
            return array_unique( $wpthumb_editors );
        }
    }
}
