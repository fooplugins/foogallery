<?php
/**
 * Class for adding advanced thumb settings to all gallery templates
 * Date: 22/02/2020
 */
if ( ! class_exists( 'FooGallery_Pro_Advanced_Thumbnails' ) ) {

    class FooGallery_Pro_Advanced_Thumbnails {

        function __construct() {
            //add fields to all templates
            add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_advanced_thumb_fields' ), 100, 2 );

            add_filter( 'foogallery_thumbnail_resize_args_final', array( $this, 'add_arguments' ), 10, 3 );
        }

        /**
         * Add arguments for the resize
         *
         * @param $args
         * @param $original_image_src
         * @param $thumbnail_object
         * @return array
         */
        function add_arguments($args, $original_image_src, $thumbnail_object) {
            $thumb_cropping_options = foogallery_gallery_template_setting( 'thumb_cropping_options', '' );

            if ( 'background_fill' === $thumb_cropping_options ) {
                $background_fill_color = foogallery_gallery_template_setting( 'thumb_background_fill', 'rgb(0,0,0)' );
                $colors = foogallery_rgb_to_color_array( $background_fill_color );
                $args['background_fill'] = sprintf( "%03d%03d%03d000", $colors[0], $colors[1], $colors[2] );
                $args['crop'] = false;
            }

            return $args;
        }

        /**
         * Add thumb fields to the gallery template
         *
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function add_advanced_thumb_fields( $fields, $template ) {

            $fields[] = array(
                'id'       => 'thumb_cropping_options',
                'title'    => __( 'Thumbnail Cropping', 'foogallery' ),
                'desc'     => __( 'Additional options to change how thumbnails are cropped.', 'foogallery' ),
                'section'  => __( 'Advanced', 'foogallery' ),
                'type'     => 'radio',
                'default'  => '',
                'spacer'   => '<span class="spacer"></span>',
                'choices'  => array(
                    ''  => __( 'Default', 'foogallery' ),
                    'background_fill'   => __( 'Background Fill (No crop)', 'foogallery' ),
                ),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
                    'data-foogallery-preview' => 'shortcode',
                    'data-foogallery-value-selector'  => 'input:checked',
                )
            );

            $fields[] = array(
                'id'      => 'thumb_background_fill',
                'title'   => __( 'Background Fill Color', 'foogallery' ),
                'desc'	  => __( 'Choose a color for the background fill.', 'foogallery '),
                'section' => __( 'Advanced', 'foogallery' ),
                'type'    => 'colorpicker',
                'default' => '',
                'row_data' => array(
                    'data-foogallery-hidden'                => true,
                    'data-foogallery-show-when-field'       => 'thumb_cropping_options',
                    'data-foogallery-show-when-field-value' => 'background_fill',
                    'data-foogallery-preview'               => 'shortcode'
                )
            );

            return $fields;
        }
    }
}