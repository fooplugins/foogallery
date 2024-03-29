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
            add_action( 'plugins_loaded', array( $this, 'load_feature' ) );

            add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

        function load_feature(){
            if ( foogallery_feature_enabled( 'foogallery-exif' ) ) {

                //Add EXIF data attributes
                add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_exif_data_attributes' ), 10, 3 );

                //Add lightbox EXIF options
                add_filter( 'foogallery_lightbox_data_attributes', array( $this, 'add_lightbox_data_attributes' ), 20 );

                //Add container class
                add_filter( 'foogallery_build_class_attribute', array( $this, 'add_container_class' ), 10,  2 );

                //add localised text
                add_filter( 'foogallery_il8n', array( $this, 'add_il8n' ) );

                //add class to fg-item
                add_filter( 'foogallery_attachment_html_item_classes', array( $this, 'add_class_to_item' ), 10, 3 );

                //add exif to the json output
                add_filter( 'foogallery_build_attachment_json', array( $this, 'add_exif_to_json' ), 10, 6 );

                if ( is_admin() ) {                    
                    //add extra fields to the templates that support exif
                    add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_exif_fields' ), 50, 2 );
    
                    //set the settings icon for Exif
                    add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );
    
                    //add some settings for EXIF
                    add_filter( 'foogallery_admin_settings_override', array( $this, 'add_exif_settings' ) );
    
                    // Attachment modal actions:
                    add_action( 'foogallery_attachment_modal_tabs_view', array( $this, 'attachment_modal_display_tab' ), 50 );
                    add_action( 'foogallery_attachment_modal_tab_content', array( $this, 'attachment_modal_display_tab_content' ), 50, 1 );
                    add_action( 'foogallery_attachment_save_data', array( $this, 'attachment_modal_save_data' ), 50, 2 );
                }

            }
        }

		function register_extension( $extensions_list ) {
			$pro_features = foogallery_pro_features();

            $extensions_list[] = array(
                'slug' => 'foogallery-exif',
                'class' => 'FooGallery_Pro_Exif',
                'categories' => array( 'Premium' ),
                'title' => __( 'EXIF', 'foogallery' ),
                'description' => $pro_features['exif']['desc'],
                'external_link_text' => __( 'Read documentation', 'foogallery' ),
                'external_link_url' => $pro_features['exif']['link'],
                'dashicon'          => 'dashicons-camera',
                'tags' => array( 'Premium' ),
                'source' => 'bundled',
                'activated_by_default' => true,
                'feature' => true
            );

            return $extensions_list;
        }

	    /**
	     * Add exif class onto item
	     *
	     * @param $classes
	     * @param $foogallery_attachment
	     * @param $args
	     *
	     * @return mixed
	     */
        function add_class_to_item( $classes, $foogallery_attachment, $args ) {
	        if ( $this->is_exif_enabled() ) {
		        if ( isset( $foogallery_attachment->exif ) ) {
			        $classes[] = 'fg-item-exif';
		        }
	        }

        	return $classes;
        }

	    /**
	     * Add the exif data to the json object
	     *
	     * @param StdClass $json_object
	     * @param FooGalleryAttachment $foogallery_attachment
	     * @param array $args
	     * @param array $anchor_attributes
	     * @param array $image_attributes
	     * @param array $captions
	     *
	     * @return mixed
	     */
	    public function add_exif_to_json(  $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions ) {
		    if ( isset( $foogallery_attachment->exif ) ) {
			    $json_object->exif = $foogallery_attachment->exif;
		    }

		    return $json_object;
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
						            'aperture' => esc_html( $aperture_entry )
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
								    'camera' => esc_html( $camera_entry )
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
								    'created_timestamp' => esc_html( $date_entry )
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
								    'shutter_speed' => esc_html( $exposure_entry )
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
								    'focal_length' => esc_html( $focal_entry )
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
								    'iso' => esc_html( $iso_entry )
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
								    'orientation' => esc_html( $orientation_entry )
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
         * Checking the EXIF enabled status
         *  
         * @return Boolean    
         */ 
        function is_exif_enabled() {
        	if ( !foogallery_current_gallery_has_cached_value('exif') ) {

        		//check if the template has panel_support (is either Slider PRO or Grid PRO)
        		if ( foogallery_current_gallery_check_template_has_supported_feature( 'panel_support' ) ) {
        			//we therefore dont care about the lightbox
			        $exif_enabled = 'yes' === foogallery_gallery_template_setting( 'exif_view_status' );
		        } else {
			        $exif_enabled = 'foogallery' === foogallery_gallery_template_setting( 'lightbox' ) &&
			                        'yes' === foogallery_gallery_template_setting( 'exif_view_status' );
		        }

        		//set the toggle
		        foogallery_current_gallery_set_cached_value( 'exif', $exif_enabled );
	        }

        	return foogallery_current_gallery_get_cached_value( 'exif' );
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
         * Add EXIF fields to the gallery template
         *
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function add_exif_fields( $fields, $template ) {

            $exif_fields = array();

            $exif_fields[] = array(
                'id'      => 'exif_help',
                'title'   => __( 'PLEASE NOTE!', 'foogallery' ),
                'desc'    => __( 'EXIF data is only supported when the FooGallery Lightbox is enabled, or with the Slider PRO and Grid PRO templates.', 'foogallery' ),
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

            //find the index of the Advanced section
            $index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

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
                $text = esc_html( foogallery_get_setting( $field_name, '' ) );

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
	            $foogallery_attachment->exif = $exif_data_attributes;
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
			        $attribute_value = foogallery_format_date( $attribute_value );
		        }
	        } else if ( 'shutter_speed' === $attribute_key ) {
		        $attribute_value = $this->format_shutter_speed( $attribute_value );
	        }

        	return apply_filters( 'foogallery_format_exif_value', $attribute_value, $attribute_key );
        }

	    /**
	     * Format the shutterspeed value
	     *
	     * @param $value
	     *
	     * @return string
	     */
        function format_shutter_speed( $value ) {
        	if ( empty( $value ) || strpos( $value, '/' ) > 0 ) {
        		return $value;
	        }

	        if ( floatval( $value ) > 0 ) {
		        return $this->convert_to_fraction( floatval( $value ) ) . 's';
	        }

	        return $value;
        }

	    /**
	     * Convert a float to a fraction
	     *
	     * @param       $n
	     * @param float $tolerance
	     *
	     * @return string
	     */
	    function convert_to_fraction($n, $tolerance = 1.e-6) {
		    $h1=1; $h2=0;
		    $k1=0; $k2=1;
		    $b = 1/$n;
		    do {
			    $b = 1/$b;
			    $a = floor($b);
			    $aux = $h1; $h1 = $a*$h1+$h2; $h2 = $aux;
			    $aux = $k1; $k1 = $a*$k1+$k2; $k2 = $aux;
			    $b = $b-$a;
		    } while (abs($n-$h1/$k1) > $n*$tolerance);

		    return "$h1/$k1";
	    }

        /**
         * Image modal EXIF tab title
         */
        public function attachment_modal_display_tab() { ?>
            <div class="foogallery-img-modal-tab-wrapper" data-tab_id="foogallery-panel-exif">
                <input type="radio" name="tabset" id="foogallery-tab-exif" aria-controls="foogallery-panel-exif">
                <label for="foogallery-tab-exif"><?php _e('EXIF', 'foogallery'); ?></label>
            </div>
        <?php }

        private function get_meta_value( $array, $key, $empty_value = '', $default = '') {
            $value = $default;

            if ( array_key_exists( $key, $array ) ) {
                $value = $array[$key];
                if ( $value === $empty_value ) {
                    $value = $default;
                }
            }
            return $value;
        }

        /**
         * Image modal EXIF tab content
         */
        public function attachment_modal_display_tab_content( $modal_data ) {
            if ( is_array( $modal_data ) && !empty ( $modal_data ) ) {
                if ( $modal_data['img_id'] > 0 ) {
                    if ( is_array ( $modal_data['meta'] ) && !empty ( $modal_data['meta'] ) ) {
                        $keywords = '';
                        $image_meta = array_key_exists( 'image_meta', $modal_data['meta'] ) ? $modal_data['meta']['image_meta'] : '';
                        if ( is_array( $image_meta ) && !empty ( $image_meta ) ) {
                            $keywords_str = array_key_exists( 'keywords', $image_meta ) ? implode( ',', $modal_data['meta']['image_meta']['keywords'] ) : '';
                            $keywords = rtrim( $keywords_str, ',' );
                        }
                        $aperture = $this->get_meta_value( $modal_data['meta']['image_meta'], 'aperture', '0' );
                        $camera = $this->get_meta_value( $modal_data['meta']['image_meta'], 'camera' );
                        $created_timestamp = $this->get_meta_value( $modal_data['meta']['image_meta'], 'created_timestamp', '0' );
                        $shutter_speed = $this->get_meta_value( $modal_data['meta']['image_meta'], 'shutter_speed', '0' );
                        $focal_length = $this->get_meta_value( $modal_data['meta']['image_meta'], 'focal_length', '0' );
                        $iso = $this->get_meta_value( $modal_data['meta']['image_meta'], 'iso', '0' );
                        $orientation = $this->get_meta_value( $modal_data['meta']['image_meta'], 'orientation', '0' );
                        ?>
                        <section id="foogallery-panel-exif" class="tab-panel">
                            <div class="settings">
                                <span class="setting has-description" data-setting="camera">
									<label for="attachment-details-two-column-camera" class="name"><?php esc_html_e('Camera', 'foogallery'); ?></label>
									<input placeholder="eg. Nikon D750" type="text" name="foogallery[camera]" id="attachment-details-two-column-camera" value="<?php echo esc_attr( $camera ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The camera that was used to take the photo.', 'foogallery' ); ?>
								</p>

								<span class="setting has-description" data-setting="aperture">
									<label for="attachment-details-two-column-aperture" class="name"><?php esc_html_e('Aperture', 'foogallery'); ?></label>
									<input placeholder="eg. f/2.8" type="text" name="foogallery[aperture]" id="attachment-details-two-column-aperture" value="<?php echo esc_attr( $aperture ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The aperture setting that was used, such as f/1.8, f/4, f/8, etc.', 'foogallery' ); ?>
								</p>

								<span class="setting has-description" data-setting="shutter-speed">
									<label for="attachment-details-two-column-shutter-speed" class="name"><?php esc_html_e('Shutter Speed', 'foogallery'); ?></label>
									<input placeholder="eg. 1/500s" type="text" name="foogallery[shutter-speed]" id="attachment-details-two-column-shutter-speed" value="<?php echo esc_attr( $shutter_speed ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The shutter speed setting (or exposure time) that was used, such as 1/30s, 1/500s, 1/1000s, etc.', 'foogallery' ); ?>
								</p>

								<span class="setting has-description" data-setting="iso">
									<label for="attachment-details-two-column-iso" class="name"><?php esc_html_e('ISO', 'foogallery'); ?></label>
									<input placeholder="eg. ISO 400" type="text" name="foogallery[iso]" id="attachment-details-two-column-iso" value="<?php echo esc_attr( $iso ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The ISO sensitivity setting that was used, such as ISO 100, ISO 400, ISO 800, etc.', 'foogallery' ); ?>
								</p>

								<span class="setting has-description" data-setting="focal-length">
									<label for="attachment-details-two-column-focal-length" class="name"><?php esc_html_e('Focal Length', 'foogallery'); ?></label>
									<input placeholder="eg. 50mm" type="text" name="foogallery[focal-length]" id="attachment-details-two-column-focal-length" value="<?php echo esc_attr( $focal_length ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The focal length setting that was used, typically measured in millimeters (mm).', 'foogallery' ); ?>
								</p>

								<span class="setting has-description" data-setting="orientation">
									<label for="attachment-details-two-column-orientation" class="name"><?php esc_html_e('Orientation', 'foogallery'); ?></label>
									<input placeholder="eg. Normal" type="text" name="foogallery[orientation]" id="attachment-details-two-column-orientation" value="<?php echo esc_attr( $orientation ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The orientation or rotation of the image, as intended by the photographer when the photo was taken.', 'foogallery' ); ?>
								</p>

                                <span class="setting has-description" data-setting="created-timestamp">
									<label for="attachment-details-two-column-created-timestamp" class="name"><?php esc_html_e('Created Timestamp', 'foogallery'); ?></label>
									<input placeholder="eg. May 27 2023, 2:30 PM" type="text" name="foogallery[created-timestamp]" id="attachment-details-two-column-created-timestamp" value="<?php echo esc_attr( $created_timestamp ); ?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'The timestamp of when the photo was taken.', 'foogallery' ); ?>
								</p>

                                <span class="setting has-description" data-setting="keywords">
									<label for="attachment-details-two-column-keywords" class="name"><?php esc_html_e('Keywords', 'foogallery'); ?></label>
									<input placeholder="eg. portrait, architecture, nature, food, travel" type="text" name="foogallery[keywords]" id="attachment-details-two-column-keywords" value="<?php echo esc_attr( $keywords );?>">
								</span>
								<p class="description">
									<?php esc_html_e( 'Additional keywords or tags to describe the photo.', 'foogallery' ); ?>
								</p>
                            </div>
                        </section>
                        <?php
                    }
                }
            }
        }

        /**
         * Save EXIF tab data content
         *
         * @param $img_id int attachment id to update data
         *
         * @param $foogallery array of form post data
         *
         */
        public function attachment_modal_save_data( $img_id, $foogallery ) {

            if ( is_array( $foogallery ) && !empty( $foogallery ) ) {

                $image_meta = wp_get_attachment_metadata( $img_id );
                foreach( $foogallery as $key => $val ) {
                    if ( $key === 'aperture' ) {
                        $image_meta['image_meta']['aperture'] = $val;
                    }
                    if ( $key === 'camera' ) {
                        $image_meta['image_meta']['camera'] = $val;
                    }
                    if ( $key === 'created-timestamp' ) {
                        $image_meta['image_meta']['created_timestamp'] = $val;
                    }
                    if ( $key === 'shutter-speed' ) {
                        $image_meta['image_meta']['shutter_speed'] = $val;
                    }
                    if ( $key === 'focal-length' ) {
                        $image_meta['image_meta']['focal_length'] = $val;
                    }
                    if ( $key === 'iso' ) {
                        $image_meta['image_meta']['iso'] = $val;
                    }
                    if ( $key === 'orientation' ) {
                        $image_meta['image_meta']['orientation'] = $val;
                    }
                    if ( $key === 'keywords' ) {
                        $keywords = explode(',', $val);
                        $image_meta['image_meta']['keywords'] = $keywords;
                    }
                }

                wp_update_attachment_metadata( $img_id, $image_meta );
            }
        }
    }
}
