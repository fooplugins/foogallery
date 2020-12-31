<?php
/**
 * Class for adding EXIF data attributes
 */
if ( ! class_exists( 'FooGallery_Pro_Exif' ) ) {

    class FooGallery_Pro_Exif {
        /**
         * Constructor for the PM class
         *
         * Sets up all the appropriate hooks and actions
         */
        function __construct() {
            //Add EXIF data
            add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_exif' ), 10, 3 );
            
            //Add lightbox EXIF options
            add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lightbox_data_attributes' ), 20, 2 );

            if ( is_admin() ) {
                //add extra fields to the templates that support exif
                add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_exif_fields' ), 20, 2 );
            }

            //set the settings icon for Exif
            add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

            //add some settings for EXIF
            add_filter( 'foogallery_admin_settings_override', array( $this, 'add_exif_settings' ) );

            //add the filtering attributes to the gallery container
            //add_filter( 'foogallery_build_container_data_options', array( $this, 'add_filtering_data_options' ), 10, 3 );
        }

        /**
         * Add the required filtering data options if needed
         *
         * @param $attributes array
         * @param $gallery    FooGallery
         *
         * @return array
         */
        function add_filtering_data_options( $options, $gallery, $attributes ) {
            $filtering_all_text = foogallery_get_setting( 'language_filtering_all', 'All' );
            pmpr($filtering_all_text); die;
        }

        /**
         * Add some WP/LR settings
         * @param $settings
         *
         * @return array
         */
        function add_exif_settings( $settings ) {
            //region EXIF Tab
            $settings['tabs']['exif'] = __( 'Exif', 'foogallery' );

            $settings['settings'][] = array(
                'id'      => 'exif_attributes',
                'title'   => __( 'Allowed EXIF Attributes', 'foogallery' ),
                'type'    => 'text',
                'default' => 'aperture,camera,date,exposure,focalLength,iso,orientation',
                'desc'    => __('Add value separated by comma', 'foogallery'),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_aperture_text',
                'title'   => __( 'Aperture Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_camera_text',
                'title'   => __( 'Camera Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_date_text',
                'title'   => __( 'Date Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_exposure_text',
                'title'   => __( 'Exposure Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_focal_length_text',
                'title'   => __( 'Focal Length Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_iso_text',
                'title'   => __( 'ISO Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_orientation_text',
                'title'   => __( 'Orientation Text', 'foogallery' ),
                'type'    => 'text',
                'tab'     => 'exif'
            );

            return $settings;
        }

        /**
         * Returns the Dashicon that can be used in the settings tabs
         *
         * @param $section_slug
         *
         * @return string
         */
        function add_section_icons( $section_slug ) {

            if ( 'exif' === strtolower( $section_slug ) ) {
                return 'dashicons-info-outline';
            }

            return $section_slug;
        }

        /**
         * Return the index of the requested section
         *
         * @param $fields
         * @param $section
         *
         * @return int
         */
        private function find_index_of_section( $fields, $section ) {
            $index = 0;
            foreach ( $fields as $field ) {
                if ( isset( $field['section'] ) && $section === $field['section'] ) {
                    return $index;
                }
                $index++;
            }
            return $index;
        }

        /**
         * Add paging fields to the gallery template
         *
         * @uses "foogallery_override_gallery_template_fields"
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function add_exif_fields( $fields, $template ) {

            $exif_fields = [];

            $exif_fields[] = array(
                'id'      => 'exif_help',
                'title'   => __( 'Exif Help', 'foogallery' ),
                'desc'    => __( 'EXIF data is only supported when the FooGallery PRO Lightbox is enabled, or with the Slider PRO and Grid PRO templates.', 'foogallery' ),
                'section' => 'Exif',
                'type'    => 'help'
            );

            $exif_fields[] = array(
                'id'      => 'exif_view_status',
                'title'   => __( 'Data View', 'foogallery' ),
                'desc'    => __( 'EXIF data view control', 'foogallery' ),
                'section' => __( 'Exif', 'foogallery' ),
                'spacer'  => '<span class="spacer"></span>',
                'type'    => 'radio',
                'default' => 'no',
                'choices' => apply_filters( 'foogallery_gallery_template_exif_view_choices', array(
                    'yes'   => __( 'Enable', 'foogallery' ),
                    'no'  => __( 'Disabled', 'foogallery' ),
                ) ),
                'row_data'=> array(
                    'data-foogallery-change-selector'          => 'input:radio',
                    'data-foogallery-preview'                  => 'shortcode',
                    'data-foogallery-value-selector'           => 'input:checked',
                )
            );

            $exif_fields[] = array(
                'id'       => 'exif_icon_position',
                'title'    => __( 'Thumbnail Icon Position', 'foogallery' ),
                'section'  => __( 'Exif', 'foogallery' ),
                'default'  => 'fg-exif-bottom-right',
                'type'     => 'select',
                'choices'  => apply_filters( 'foogallery_gallery_template_exif_icon_position_choices', array(
                    'fg-exif-bottom-right' => __( 'Bottom Right', 'foogallery' ),
                    'fg-exif-bottom-left'  => __( 'Bottom Left', 'foogallery' ),
                    'fg-exif-top-right'    => __( 'Top Right', 'foogallery' ),
                    'fg-exif-top-left'     => __( 'Top Left', 'foogallery' ),
                ) ),
                'desc'     => __( 'Choose where do you want to show the EXIF icon in your image', 'foogallery' ),
                'row_data'=> array(
                    'data-foogallery-hidden'                   => true,
                    'data-foogallery-show-when-field'          => 'exif_view_status',
                    'data-foogallery-show-when-field-operator' => '===',
                    'data-foogallery-show-when-field-value'    => 'yes',
                    'data-foogallery-change-selector'          => 'input:radio',
                    'data-foogallery-preview'                  => 'shortcode',
                    'data-foogallery-value-selector'           => 'input:checked',
                )
            );

            $exif_fields[] = array(
                'id'       => 'exif_icon_theme',
                'title'    => __( 'Thumbnail Icon Theme', 'foogallery' ),
                'section'  => __( 'Exif', 'foogallery' ),
                'default'  => 'fg-exif-dark',
                'type'     => 'select',
                'choices'  => apply_filters( 'foogallery_gallery_template_exif_icon_theme_choices', array(
                    'fg-exif-dark' => __( 'Light', 'foogallery' ),
                    'fg-exif-light'  => __( 'Dark', 'foogallery' ),
                ) ),
                'desc'     => __( 'Choose EXIF icon theme', 'foogallery' ),
                'row_data'=> array(
                    'data-foogallery-hidden'                   => true,
                    'data-foogallery-show-when-field'          => 'exif_view_status',
                    'data-foogallery-show-when-field-operator' => '===',
                    'data-foogallery-show-when-field-value'    => 'yes',
                    'data-foogallery-change-selector'          => 'input:radio',
                    'data-foogallery-preview'                  => 'shortcode',
                    'data-foogallery-value-selector'           => 'input:checked',
                )
            );

            $exif_fields[] = array(
                'id'      => 'exif_display_layout',
                'title'   => __( 'Display layout', 'foogallery' ),
                'desc'    => __( 'EXIF display layout control', 'foogallery' ),
                'section' => __( 'Exif', 'foogallery' ),
                'spacer'  => '<span class="spacer"></span>',
                'type'    => 'radio',
                'default' => 'auto',
                'choices' => apply_filters( 'foogallery_gallery_template_exif_display_layout_choices', array(
                    'auto'   => __( 'Auto', 'foogallery' ),
                    'full'  => __( 'Full', 'foogallery' ),
                    'partial'  => __( 'Partial', 'foogallery' ),
                    'minimal'  => __( 'Minimal', 'foogallery' ),
                ) ),
                'row_data'=> array(
                    'data-foogallery-hidden'                   => true,
                    'data-foogallery-show-when-field'          => 'exif_view_status',
                    'data-foogallery-show-when-field-operator' => '===',
                    'data-foogallery-show-when-field-value'    => 'yes',
                    'data-foogallery-change-selector'          => 'input:radio',
                    'data-foogallery-preview'                  => 'shortcode',
                    'data-foogallery-value-selector'           => 'input:checked',
                )
            );

            //find the index of the first Hover Effect field
            $index = $this->find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

            array_splice( $fields, $index, 0, $exif_fields );

            return $fields;
        }

        /**
         * Append the needed data attributes to the container div for the lightbox EXIF setting
         *
         * @param $attributes
         * @param $gallery
         *
         * @return array
         */
        function add_lightbox_data_attributes( $attributes, $gallery ) {
            //If the data-foogallery-lightbox value does not exist, then the lightbox attributes have not been set, and the lightbox is not enabled
            if ( empty( $attributes['data-foogallery-lightbox'] ) ) {
                return $attributes;
            }

            $decode = json_decode( $attributes['data-foogallery-lightbox'] );
            $decode->exif = 'auto';
            $attributes['data-foogallery-lightbox'] = json_encode( $decode );

            return $attributes;
        }

        /**
         * Customize the item anchor EXIF data attributes
         * 
         * @param $attr
         * @param $args
         * @param $foogallery_attachment
         * 
         * @return array
         */
        function add_exif( $attr, $args, $foogallery_attachment ) {
            global $current_foogallery;

            if ( empty( $current_foogallery->lightbox ) || $current_foogallery->lightbox != 'foogallery' ) {
                return $attr;
            }

        	$meta = wp_get_attachment_metadata( $foogallery_attachment->ID ); 

			$attr['data-exif'] = json_encode( $meta['image_meta'] );

			return $attr;
        }
    }
}
