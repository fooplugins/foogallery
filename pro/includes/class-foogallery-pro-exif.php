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
            add_filter( 'foogallery_lightbox_data_attributes', array( $this, 'add_lightbox_data_attributes' ), 20 );

	        //Add container class
	        add_filter( 'foogallery_build_class_attribute', array( $this, 'add_container_class' ), 10,  2 );

	        //add localised text
	        add_filter( 'foogallery_il8n', array( $this, 'add_il8n' ) );

            if ( is_admin() ) {
                //add extra fields to the templates that support exif
                add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_exif_fields' ), 20, 2 );

                //set the settings icon for Exif
                add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

                //add some settings for EXIF
                add_filter( 'foogallery_admin_settings_override', array( $this, 'add_exif_settings' ) );
            }
        }

	    /**
	     * Add localisation settings
	     *
	     * @param $il8n
	     *
	     * @return string
	     */
	    function add_il8n( $il8n ) {

		    $aperture_entry = foogallery_get_language_array_value( 'exif_aperture_text', __( 'Aperture', 'foogallery' ) );
		    if ( $aperture_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
					    	'item' => array(
					    		'exif' => array(
						            'aperture' => $aperture_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    $camera_entry = foogallery_get_language_array_value( 'exif_camera_text', __( 'Camera', 'foogallery' ) );
		    if ( $camera_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
						    'item' => array(
							    'exif' => array(
								    'camera' => $camera_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    $date_entry = foogallery_get_language_array_value( 'exif_date_text', __( 'Date', 'foogallery' ) );
		    if ( $date_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
						    'item' => array(
							    'exif' => array(
								    'created_timestamp' => $date_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    $exposure_entry = foogallery_get_language_array_value( 'exif_exposure_text', __( 'Exposure', 'foogallery' ) );
		    if ( $exposure_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
						    'item' => array(
							    'exif' => array(
								    'shutter_speed' => $exposure_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    $focal_entry = foogallery_get_language_array_value( 'exif_focal_length_text', __( 'Focal Length', 'foogallery' ) );
		    if ( $focal_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
						    'item' => array(
							    'exif' => array(
								    'focal_length' => $focal_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    $iso_entry = foogallery_get_language_array_value( 'exif_iso_text', __( 'ISO', 'foogallery' ) );
		    if ( $iso_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
						    'item' => array(
							    'exif' => array(
								    'iso' => $iso_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    $orientation_entry = foogallery_get_language_array_value( 'exif_orientation_text', __( 'Orientation', 'foogallery' ) );
		    if ( $orientation_entry !== false ) {
			    $il8n = array_merge_recursive( $il8n, array(
				    'template' => array(
					    'core' => array(
						    'item' => array(
							    'exif' => array(
								    'orientation' => $orientation_entry
							    )
						    )
					    )
				    )
			    ) );
		    }

		    return $il8n;
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
            if ( $this->is_exif_enabled() ) {
	            //Set EXIF view button position class
	            $classes[] = foogallery_gallery_template_setting( 'exif_icon_position', 'fg-exif-bottom-right' );

	            //Set EXIF view button theme class
	            $classes[] = foogallery_gallery_template_setting( 'exif_icon_theme', 'fg-exif-dark' );
            }

            return $classes;    
        }

        /** 
         * Checking the EXIF enable status   
         *  
         * @return Boolean    
         */ 
        function is_exif_enabled() {
            //Checking active status for FooGallery PRO Lightbox and EXIF Data View status
            return 'foogallery' === foogallery_gallery_template_setting( 'lightbox' ) &&
                   'yes' === foogallery_gallery_template_setting( 'exif_view_status' );
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
            $settings['tabs']['exif'] = __( 'EXIF', 'foogallery' );

            $settings['settings'][] = array(
                'id'      => 'exif_attributes',
                'title'   => __( 'Allowed EXIF Attributes', 'foogallery' ),
                'type'    => 'text',
                'default' => 'aperture,camera,created_timestamp,shutter_speed,focal_length,iso,orientation',
                'desc'    => __('The allowed EXIF attributes that will be displayed in the lightbox. This is a comma-separated list e.g. aperture,camera,date', 'foogallery'),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_aperture_text',
                'title'   => __( 'Aperture Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Aperture', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_camera_text',
                'title'   => __( 'Camera Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Camera', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_date_text',
                'title'   => __( 'Date Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Date', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_exposure_text',
                'title'   => __( 'Exposure Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Exposure', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_focal_length_text',
                'title'   => __( 'Focal Length Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Focal Length', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_iso_text',
                'title'   => __( 'ISO Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'ISO', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
                'tab'     => 'exif'
            );

            $settings['settings'][] = array(
                'id'      => 'exif_orientation_text',
                'title'   => __( 'Orientation Text', 'foogallery' ),
                'type'    => 'text',
                'default' => __( 'Orientation', 'foogallery' ),
                'section' => __( 'Language', 'foogallery' ),
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
                'title'   => __( 'EXIF Help', 'foogallery' ),
                'desc'    => __( 'EXIF data is only supported when the FooGallery PRO Lightbox is enabled, or with the Slider PRO and Grid PRO templates.', 'foogallery' ),
                'section' => 'EXIF',
                'type'    => 'help'
            );

            $exif_fields[] = array(
                'id'      => 'exif_view_status',
                'title'   => __( 'Show EXIF info', 'foogallery' ),
                'desc'    => __( 'Do you want to show EXIF info in this gallery?', 'foogallery' ),
                'section' => __( 'EXIF', 'foogallery' ),
                'spacer'  => '<span class="spacer"></span>',
                'type'    => 'radio',
                'default' => 'no',
                'choices' => apply_filters( 'foogallery_gallery_template_exif_view_choices', array(
                    'yes'   => __( 'Enabled', 'foogallery' ),
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
                'section'  => __( 'EXIF', 'foogallery' ),
                'default'  => 'fg-exif-bottom-right',
                'type'     => 'select',
                'choices'  => apply_filters( 'foogallery_gallery_template_exif_icon_position_choices', array(
                    'fg-exif-bottom-right' => __( 'Bottom Right', 'foogallery' ),
                    'fg-exif-bottom-left'  => __( 'Bottom Left', 'foogallery' ),
                    'fg-exif-top-right'    => __( 'Top Right', 'foogallery' ),
                    'fg-exif-top-left'     => __( 'Top Left', 'foogallery' ),
                    ''                     => __( 'None', 'foogallery' ),
                ) ),
                'desc'     => __( 'Choose where you want to show the EXIF icon in your thumbnails.', 'foogallery' ),
                'row_data'=> array(
                    'data-foogallery-hidden'                   => true,
                    'data-foogallery-show-when-field'          => 'exif_view_status',
                    'data-foogallery-show-when-field-operator' => '===',
                    'data-foogallery-show-when-field-value'    => 'yes',
                    'data-foogallery-change-selector'          => 'select',
                    'data-foogallery-preview'                  => 'shortcode',
                    'data-foogallery-value-selector'           => 'select',
                )
            );

            $exif_fields[] = array(
                'id'       => 'exif_icon_theme',
                'title'    => __( 'Thumbnail Icon Theme', 'foogallery' ),
                'section'  => __( 'EXIF', 'foogallery' ),
                'default'  => 'fg-exif-dark',
                'spacer'  => '<span class="spacer"></span>',
                'type'    => 'radio',
                'choices'  => apply_filters( 'foogallery_gallery_template_exif_icon_theme_choices', array(
                    'fg-exif-light' => __( 'Light', 'foogallery' ),
                    'fg-exif-dark'  => __( 'Dark', 'foogallery' ),
                ) ),
                'desc'     => __( 'The EXIF color theme', 'foogallery' ),
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
                'title'   => __( 'Attribute layout', 'foogallery' ),
                'desc'    => __( 'Choose the EXIF attribute layout when showing the different EXIF metadata.', 'foogallery' ),
                'section' => __( 'EXIF', 'foogallery' ),
                'type'    => 'radio',
                'default' => 'auto',
                'choices' => apply_filters( 'foogallery_gallery_template_exif_display_layout_choices', array(
                    'auto'   => __( 'Auto (adjusts to screen size)', 'foogallery' ),
                    'full'  => __( 'Full (Icon + Word + Value)', 'foogallery' ),
                    'partial'  => __( 'Partial (Icon + Value)', 'foogallery' ),
                    'minimal'  => __( 'Minimal (Icon only)', 'foogallery' ),
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
        function add_lightbox_data_attributes( $attributes ) {
            if ( ! $this->is_exif_enabled() ) {
                return $attributes;
            }

	        $attributes['exif'] = foogallery_gallery_template_setting( 'exif_display_layout', 'auto' );

            $exif_il8n_enabled = false;
            if ( $exif_il8n_enabled ) {

	            //Get il8n text for EXIF attributes label.
	            $attributes['exif']['il8n'] = $this->get_exif_labels();
            }

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
            if ( ! $this->is_exif_enabled() ) {
                return $attr; 
            }

        	$meta = wp_get_attachment_metadata( $foogallery_attachment->ID );

            //get out early if there is no image_meta
            if ( empty( $meta['image_meta'] ) ) {
            	return $attr;
            }

            $exif_data_attributes = array();

            $exif_attributes = $meta['image_meta'];

            //Get global setting EXIF data attributes
            $settings_attrs = foogallery_get_setting( 'exif_attributes', 'aperture,camera,created_timestamp,shutter_speed,focal_length,iso,orientation' );
            $settings_attrs = str_replace( ' ', '', trim( $settings_attrs ) );
            $settings_attrs = explode( ',', $settings_attrs );
            
            //Filter default EXIF attributes according global settings
            foreach ( $settings_attrs as $settings_attr ) {
                if ( empty( $settings_attr ) ) {
                    continue;
                }

                if ( ! array_key_exists( $settings_attr, $exif_attributes ) ) {
                    continue;
                }

                if ( empty( $exif_attributes[$settings_attr] ) ) {
                	continue;
                }

                $exif_data_attributes[$settings_attr] = $this->format_exif_value( $settings_attr, $exif_attributes[$settings_attr] );
            }

            if ( ! empty( $exif_data_attributes ) ) {
                $attr['data-exif'] = foogallery_json_encode( $exif_data_attributes );
            }

			return $attr;
        }

	    /**
	     * Format the value of the exif attribute
	     * @param $attribute_key
	     * @param $attribute_value
	     *
	     * @return string
	     */
        function format_exif_value( $attribute_key, $attribute_value ) {
        	if ( 'created_timestamp' === $attribute_key || 'date' === $attribute_key ) {
        		if ( (string)(int)$attribute_value == $attribute_value ) {
        			return foogallery_format_date( $attribute_value );
		        }
	        }

        	if ( 'shutter_speed' === $attribute_key && is_string( $attribute_value ) && strlen( $attribute_value) >= 10 ) {
        		return substr( $attribute_value, 0, 9 );
	        }

        	return apply_filters( 'foogallery_format_exif_value', $attribute_value, $attribute_key );
        }
    }
}
