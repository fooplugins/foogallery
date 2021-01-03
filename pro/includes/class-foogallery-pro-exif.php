<?php
/**
 * Class for Exchangeable image file format (EXIF) 
 */
if ( ! class_exists( 'FooGallery_Pro_Exif' ) ) {

    class FooGallery_Pro_Exif {
        /**
         * Constructor for the class
         *
         * Sets up all the appropriate hooks and actions
         */
        function __construct() {
            //Add EXIF data attributes
            add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_exif_data_attributes' ), 10, 3 );
            
            //Add lightbox EXIF options
            add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lightbox_data_attributes' ), 20, 2 );

            if ( is_admin() ) {
                //add extra fields to the templates that support exif
                add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_exif_fields' ), 20, 2 );

                //set the settings icon for Exif
                add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

                //add some settings for EXIF
                add_filter( 'foogallery_admin_settings_override', array( $this, 'add_exif_settings' ) );
            }

            //Add container class
            add_filter( 'foogallery_build_class_attribute', array( $this, 'add_container_class' ), 10,  2 );
        }

        /** 
         * Customize the item container class   
         *  
         * @param $classes  
         * @param $gallery  
         *  
         * @return array    
         */ 
        function add_container_class( $classes, $gallery ) { 
            global $current_foogallery;

            if ( ! $this->is_enable_exif() ) {
                return $classes;
            }

            $classes[] = empty( $current_foogallery->settings['default_exif_icon_position'] ) ? 'fg-exif-bottom-right' : $current_foogallery->settings['default_exif_icon_position'];
            $classes[] = empty( $current_foogallery->settings['default_exif_icon_theme'] ) ? 'fg-exif-dark' : $current_foogallery->settings['default_exif_icon_theme'];

            return $classes;    
        }

        /** 
         * Checking the EXIF enable status   
         *  
         * @return Boolean    
         */ 
        function is_enable_exif() { 
            global $current_foogallery;

            //Checking active status for FooGallery PRO Lightbox
            if ( empty( $current_foogallery->settings['default_lightbox'] ) || $current_foogallery->settings['default_lightbox'] != 'foogallery' ) {
                return false;
            }

            //Checking EXIF Data View status
            if ( empty( $current_foogallery->settings['default_exif_view_status'] ) || $current_foogallery->settings['default_exif_view_status'] != 'yes' ) {
                return false;
            }   

            return true; 
        }

        /**
         * Add some EXIF settings
         * 
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
                'default' => __( 'Aperture', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_camera_text',
                'title'   => __( 'Camera Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Camera', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_data_text',
                'title'   => __( 'Date Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Date', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_exposure_text',
                'title'   => __( 'Exposure Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Exposure', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_focal_length_text',
                'title'   => __( 'Focal Length Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Focal Length', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_iso_text',
                'title'   => __( 'ISO Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'ISO', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_orientation_text',
                'title'   => __( 'Orientation Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Orientation', 'foogallery' ),
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
         * Add EXIF fields to the gallery template
         *
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
                    'fg-exif-light' => __( 'Light', 'foogallery' ),
                    'fg-exif-dark'  => __( 'Dark', 'foogallery' ),
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
            global $current_foogallery;
            
            if ( ! $this->is_enable_exif() ) {
                return $attributes;
            }

            //If the data-foogallery-lightbox value does not exist, then the lightbox attributes have not been set, and the lightbox is not enabled.
            if ( empty( $attributes['data-foogallery-lightbox'] ) ) {
                return $attributes;
            }

            $decode_lightbox_attrs       = json_decode( $attributes['data-foogallery-lightbox'] );
            $decode_lightbox_attrs->exif = empty( $current_foogallery->settings['default_exif_display_layout'] ) ? 'auto' : $current_foogallery->settings['default_exif_display_layout'];

            //Get il8n text for EXif attributes label.
            $exif_il8n_labels = $this->get_exif_labels();

            if ( ! empty( $exif_il8n_labels ) ) {
                $decode_lightbox_attrs->il8n = array (
                    'exif' => $exif_il8n_labels
                );
            }
            
            $attributes['data-foogallery-lightbox'] = json_encode( $decode_lightbox_attrs );

            return $attributes;
        }

        /**
         * Get EXIF global setting's text
         * 
         * @return array
         */
        function get_exif_labels() {
            $labels = array();
            
            //Mapping default EXIF label key with global settings.
            $label_keys = array(
                'aperture'    => 'exif_aperture_text',
                'camera'      => 'exif_camera_text',
                'date'        => 'exif_data_text',
                'exposure'    => 'exif_exposure_text',
                'focalLength' => 'exif_focal_length_text',
                'iso'         => 'exif_iso_text',
                'orientation' => 'exif_orientation_text',
            );

            //Filter default EXIF label with global settings EXIF label.
            foreach ( $label_keys as $label_key => $field_name ) {
                $text = foogallery_get_setting( $field_name, '' );

                if ( ! empty( $text ) ) {
                    $labels[$label_key] = $text;
                }
            }
            
            return $labels;
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
        function add_exif_data_attributes( $attr, $args, $foogallery_attachment ) {
            if ( ! $this->is_enable_exif() ) {
                return $attr; 
            }

        	$meta = wp_get_attachment_metadata( $foogallery_attachment->ID ); 

            $exif_data_attributes = array();

            //Mapping with default EXIF attributes and image meta attributes
            $exif_attributes = array(
                'aperture'    => empty( $meta['image_meta']['aperture'] ) ? null : $meta['image_meta']['aperture'],
                'camera'      => empty( $meta['image_meta']['camera'] ) ? null : $meta['image_meta']['camera'],
                'date'        => empty( $meta['image_meta']['created_timestamp'] ) ? null : $meta['image_meta']['created_timestamp'],
                'exposure'    => empty( $meta['image_meta']['shutter_speed'] ) ? null : $meta['image_meta']['shutter_speed'],
                'focalLength' => empty( $meta['image_meta']['focal_length'] ) ? null : $meta['image_meta']['focal_length'],
                'iso'         => empty( $meta['image_meta']['iso'] ) ? null : $meta['image_meta']['iso'],
                'orientation' => empty( $meta['image_meta']['orientation'] ) ? null : $meta['image_meta']['orientation'],
            );

            //Get global setting EXIF data attributes
            $settings_attrs = foogallery_get_setting( 'exif_attributes', 'aperture,camera,date,exposure,focalLength,iso,orientation' );
            $settings_attrs = str_replace( ' ', '', trim( $settings_attrs ) );
            $settings_attrs = explode( ',', $settings_attrs );
            
            //Fiter default EXIF attributes accorind global settngs 
            foreach ( $settings_attrs as $settings_attr ) {
                if ( empty( $settings_attr ) ) {
                    continue;
                }

                if ( ! array_key_exists( $settings_attr, $exif_attributes ) ) {
                    continue;
                }

                $exif_data_attributes[$settings_attr] = $exif_attributes[$settings_attr];
            }

            if ( ! empty( $exif_data_attributes ) ) {
                $attr['data-exif'] = json_encode( $exif_data_attributes );
            }

			return $attr;
        }
    }
}
