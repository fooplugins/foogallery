<?php
/**
 * FooGallery Pro Gallery Shortcode Override Class
 */
namespace FooPlugins\FooGallery\Pro;

use FooPlugins\FooGallery\Public\FooGallery_Template_Loader;

if ( ! class_exists( 'FooGallery_Pro_Gallery_Shortcode_Override' ) ) {

    class FooGallery_Pro_Gallery_Shortcode_Override {

        function __construct() {
            add_filter( 'foogallery_admin_settings_override', array( $this, 'gallery_shortcode_override_settings' ) );
            add_filter( 'post_gallery', array( $this, 'override_gallery_output' ), 10, 3 );
        }

        /**
         * Create the override
         * @return array
         */
        function gallery_shortcode_override_settings($settings) {
            $settings['settings'][] = array(
                'id' => 'override_gallery_shortcode',
                'title' => __('Override Gallery Shortcode', 'foogallery'),
                'desc' => sprintf(__('This will allow you to override all default gallery shortcodes to rather use a %s template. The defaults above will be used when displaying the gallery.', 'foogallery'), foogallery_plugin_name()),
                'type' => 'checkbox',
                'tab' => 'general',
                'section' => __('Shortcodes', 'foogallery')
            );

            return $settings;
        }

        /*
         * Override the gallery shortcode output if enabled
         * @param $output
         * @param $attr
         * @param $instance
         * @return string
         */
        function override_gallery_output( $output, $attr, $instance) {
            $override_enabled = foogallery_get_setting( 'override_gallery_shortcode');

            if ($override_enabled  === 'on') {
                $attr['attachment_ids'] = $attr['ids'];

				//create new instance of template engine
				$engine = new FooGallery_Template_Loader();

				ob_start();

				$engine->render_template( $attr );

				$output_string = ob_get_contents();
				ob_end_clean();
				return $output_string;
            }

            return '';
        }
    }
}