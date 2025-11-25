<?php
/**
 * FooGallery Pro Filtering Class
 */
if ( ! class_exists( 'FooGallery_Pro_Filtering' ) ) {

	class FooGallery_Pro_Filtering {

		function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_feature' ) );

            add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		function load_feature() {
            if ( foogallery_feature_enabled( 'foogallery-filtering' ) ) {
                if ( is_admin() ) {
                    //add extra fields to the templates that support filtering
                    add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_filtering_fields' ), 10, 2 );
	
					//set the settings icon for filtering
					add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );
	
					//add a global setting to change the All filter
					add_filter( 'foogallery_admin_settings_override', array( $this, 'add_language_settings' ), 30 );
	
                    //output the multi-level filtering custom field
                    add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_multi_field' ), 10, 3 );

                    //enqueue assets needed for the multi-level modal
                    add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
	
					//output the modal
					add_action( 'admin_footer', array( $this, 'render_multi_level_modal' ) );
	
                    //ajax handler to render the modal content
                    add_action( 'wp_ajax_foogallery_multi_filtering_content', array( $this, 'ajax_load_modal_content' ) );
                }
	
				//adds the filtering property to a FooGallery
				add_action( 'foogallery_located_template', array( $this, 'determine_filtering' ), 10, 2 );
	
				//add the filtering attributes to the gallery container
				add_filter( 'foogallery_build_container_data_options', array( $this, 'add_filtering_data_options' ), 10, 3 );
	
				//add attributes to the thumbnail anchors
				add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_tag_attribute' ), 10, 3 );
	
				//add tags to the json output
				add_filter( 'foogallery_build_attachment_json', array( $this, 'add_json_tags' ), 10, 6 );
	
				//add localised text
				add_filter( 'foogallery_il8n', array( $this, 'add_il8n' ) );
	
				//output pagination placeholders
				add_action( 'foogallery_loaded_template_before', array( $this, 'output_filtering_placeholders_before' ), 10, 1 );
				add_action( 'foogallery_loaded_template_after', array( $this, 'output_filtering_placeholders_after' ), 20, 1 );

				add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'alter_old_field_values'), 99, 4 );
            }
        }

		/**
		 * Alter old field values to cater for removing the 'Advanced' option
		 *
		 * @param $value
		 * @param $field
		 * @param $gallery
		 * @param $template
		 *
		 * @return string
		 */
		function alter_old_field_values( $value, $field, $gallery, $template ) {
			//only map filtering_type field to cater for removing the 'Advanced' option
			if ( 'filtering_type' === $field['id'] ) {
				switch ( $value ) {
				case '': // filtering is disabled.
					$value = '';
					break;
				case 'multi': // multi-level filtering.
					$value = 'multi';
					break;
				default: // everything else gets mapped to simple filtering.
					$value = 'simple';
					break;
				}
			}

			return $value;
		}

		function register_extension( $extensions_list ) {
			$pro_features = foogallery_pro_features();

            $extensions_list[] = array(
                'slug' => 'foogallery-filtering',
                'class' => 'FooGallery_Pro_Filtering',
                'categories' => array( 'Premium' ),
                'title' => foogallery__( 'Filtering', 'foogallery' ),
                'description' => $pro_features['filtering']['desc'],
                'external_link_text' => foogallery__( 'Read documentation', 'foogallery' ),
                'external_link_url' => $pro_features['filtering']['link'],
				'dashicon'          => 'dashicons-filter',
                'tags' => array( 'Premium' ),
                'source' => 'bundled',
                'activated_by_default' => true,
                'feature' => true
            );

            return $extensions_list;
        }

		/**
		 * Renders the top filtering placeholder
		 *
		 * @param $foogallery FooGallery
		 */
		function output_filtering_placeholders_before( $foogallery ) {
			$this->output_filtering_placeholder( $foogallery, 'top' );
		}

		/**
		 * Renders the bottom filtering placeholder
		 *
		 * @param $foogallery FooGallery
		 */
		function output_filtering_placeholders_after( $foogallery ) {
			$this->output_filtering_placeholder( $foogallery, 'bottom' );
		}

		/**
		 * render the filtering placeholder
		 *
		 * @param $foogallery
		 * @param $position
		 */
		function output_filtering_placeholder( $foogallery, $position ) {
			if ( foogallery_current_gallery_has_cached_value( 'filtering' ) ) {
				$filtering_options = foogallery_current_gallery_get_cached_value( 'filtering' );
				$filtering_type = $filtering_options['type'];
				
				if ( '' !== $filtering_type && isset( $filtering_options['position'] ) )  {
					$filtering_position = $filtering_options['position'];
					if ( $position === $filtering_position || 'both' === $filtering_position ) {

						//we need to add a nav placeholder.
						$classes = array( 'fg-filtering-container', 'fg-ph-' . $filtering_type );
						if ( !empty( $filtering_options['style'] ) ) {
							$classes[] = 'fg-style-' . $filtering_options['style'];
						}
						if ( isset( $filtering_options['search'] ) && $filtering_options['search'] ) {
							$classes[] = 'fg-search-' . $filtering_options['searchPosition'];
						}

						$nav_id = $foogallery->container_id() . '_filtering-' . $position;
						printf(
							'<nav id="%1$s" class="%2$s"></nav>',
							esc_attr( $nav_id ),
							esc_attr( implode( ' ', $classes ) )
						);
					}
				}
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

			$filtering_all_entry = foogallery_get_language_array_value( 'language_filtering_all', __( 'All', 'foogallery' ) );
			if ( $filtering_all_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'filtering' => array(
						'tags' => array(
							'all' => esc_html( $filtering_all_entry )
						)
					)
				) );
			}

			$filtering_search_entry = foogallery_get_language_array_value( 'language_filtering_search', __( 'Search gallery...', 'foogallery' ) );
			if ( $filtering_search_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'filtering' => array(
						'tags' => array(
							'searchPlaceholder' => esc_html( $filtering_search_entry )
						)
					)
				) );
			}

			$filtering_search_submit_entry = foogallery_get_language_array_value( 'language_filtering_search_submit', __( 'Submit search', 'foogallery' ) );
			if ( $filtering_search_submit_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'filtering' => array(
						'tags' => array(
							'searchSubmit' => esc_html( $filtering_search_submit_entry )
						)
					)
				) );
			}

			$filtering_search_clear_entry = foogallery_get_language_array_value( 'language_filtering_search_clear', __( 'Clear search', 'foogallery' ) );
			if ( $filtering_search_clear_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'filtering' => array(
						'tags' => array(
							'searchClear' => esc_html( $filtering_search_clear_entry )
						)
					)
				) );
			}

			return $il8n;
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_section_icons( $section_slug ) {
			if ( 'filtering' === $section_slug ) {
				return 'dashicons-filter';
			}

			return $section_slug;
		}

		/**
		 * Add filtering fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_filtering_fields( $fields, $template ) {
			if ( $template && array_key_exists( 'filtering_support', $template ) && true === $template['filtering_support'] ) {
				$filtering_fields[] = array(
					'id'       => 'filtering_type',
					'title'    => __( 'Filtering', 'foogallery' ),
					'desc'     => __( 'What type of filtering do you want to use?', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => apply_filters('foogallery_gallery_template_filtering_type_choices', array(
							''       => __( 'Disabled', 'foogallery' ),
							'simple' => __( 'Enabled', 'foogallery' ),
							'multi'  => __( 'Multi-Level', 'foogallery' )
						)
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					)
				);

				$filtering_fields[] = array(
					'id'      => 'filtering_multi_help',
					'title'   => __( 'What is Multi-Level Filtering?', 'foogallery' ),
					'desc'    => __( 'You can setup multiple levels of filters, where each level will filter the next level. This should be used in advanced scenarios where you need to filter by multiple levels of data.', 'foogallery' ),
					'section' => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'    => 'help',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => 'multi',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_multi_override',
					'title'    => __( 'Levels', 'foogallery' ),
					'desc'     => __( 'The filtering levels that will be used for the gallery.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'filtering_multi',
					'default'  => '',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => 'multi',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'      => 'filtering_theme',
					'title'   => __( 'Theme', 'foogallery' ),
					'desc'    => __( 'The theme used for filtering. Default will use the gallery theme.', 'foogallery' ),
					'section' => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'    => 'radio',
					'default' => '',
					'choices' => apply_filters( 'foogallery_gallery_template_filtering_theme_choices', array(
						''          => __( 'Default', 'foogallery' ),
						'fg-light'  => __( 'Light', 'foogallery' ),
						'fg-dark'   => __( 'Dark', 'foogallery' ),
						'fg-custom' => __( 'Custom', 'foogallery' ),
					) ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$taxonomy_objects = get_object_taxonomies( 'attachment', 'objects' );
				$taxonomy_choices = array();
				foreach ( $taxonomy_objects as $taxonomy_object ) {
					$taxonomy_choices[$taxonomy_object->name] = $taxonomy_object->label;
				}

				$filtering_fields[] = array(
					'id'       => 'filtering_taxonomy',
					'title'    => __( 'Filtering Source', 'foogallery' ),
					'desc'     => __( 'What will be used as the source for your gallery filters. All attachment taxonomies will be listed.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
					'choices'  => apply_filters( 'foogallery_gallery_template_filtering_taxonomy_choices', $taxonomy_choices ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_position',
					'title'    => __( 'Position', 'foogallery' ),
					'desc'     => __( 'The position of the filters relative to the gallery.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'top',
					'choices'  => apply_filters( 'foogallery_gallery_template_filtering_position_choices', array(
						'top'    => __( 'Top', 'foogallery' ),
						'bottom' => __( 'Bottom', 'foogallery' ),
						'both'   => __( 'Both', 'foogallery' )
					) ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_style',
					'title'    => __( 'Style', 'foogallery' ),
					'desc'     => __( 'The style of the filters.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => apply_filters( 'foogallery_gallery_template_filtering_style_choices', array(
						''    => __( 'Default', 'foogallery' ),
						'button' => __( 'Button', 'foogallery' ),
						'button-block'   => __( 'Button Block', 'foogallery' ),
						'pill'   => __( 'Pill', 'foogallery' ),
						'pill-block'   => __( 'Pill Block', 'foogallery' ),
						'dropdown'   => __( 'Dropdown', 'foogallery' ),
						'dropdown-block'   => __( 'Dropdown Block', 'foogallery' ),
					) ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					),
					'class' => 'foogallery-radios-8em',
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_alignment',
					'title'    => __( 'Alignment', 'foogallery' ),
					'desc'     => __( 'The alignment of the filters.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => apply_filters( 'foogallery_gallery_template_filtering_alignment_choices', array(
						'left' => __( 'Left', 'foogallery' ),
						'center'   => __( 'Center', 'foogallery' ),
						'right'   => __( 'Right', 'foogallery' )
					) ),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_collapse',
					'title'    => __( 'Collapse', 'foogallery' ),
					'desc'     => __( 'Whether or not to collapse the filters into a dropdown on smaller devices.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'true',
					'choices'  => array(
						'false'  => __( 'Do Nothing', 'foogallery' ),
						'true' => __( 'Collapse', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_hideall',
					'title'    => __( 'Hide "All" Option', 'foogallery' ),
					'desc'     => __( 'You can choose to hide the default "All" filter option.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => array(
						'hide'    => __( 'Hide "All"', 'foogallery' ),
						'' => __( 'Show "All"', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_autoSelected',
					'title'    => __( 'Auto Select First Filter', 'foogallery' ),
					'desc'     => __( 'You can auto select the first filter, if "All" is hidden.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => array(
						''     => __( 'Disabled', 'foogallery' ),
						'true' => __( 'Enabled', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field'          => 'filtering_hideall',
						'data-foogallery-show-when-field-value'    => 'hide',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$mode_help = __( 'The default selection mode is Single, which allows you to choose a single filter at a time. You can also choose to filter by more than 1 filter by selecting Multiple. Multiple supports either a union (OR) or an intersect (AND) mode.', 'foogallery' );
				$mode_help .= '<br>' . __( 'Please Note : the Muliple (OR) and Muliple (AND) modes do not support the dropdown styles!', 'foogallery' );

				$filtering_fields[] = array(
					'id'      => 'filtering_mode_help',
					'title'   => __( 'Filtering Selection Mode Help', 'foogallery' ),
					'desc'    => $mode_help,
					'section' => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'    => 'help',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_mode',
					'title'    => __( 'Selection Mode', 'foogallery' ),
					'desc'     => __( 'The selection mode to use when filtering.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'single',
					'choices'  => apply_filters (
						'foogallery_gallery_template_filtering_mode_choices', array(
							'single'    => __( 'Single', 'foogallery' ),
							'union'     => __( 'Multiple (OR)', 'foogallery' ),
							'intersect' => __( 'Multiple (AND)', 'foogallery' )
						)
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_min',
					'title'    => __( 'Minimum Count', 'foogallery' ),
					'desc'     => __( 'The minimum count before a filter is shown, 0 = disabled and all are shown.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 0,
					'step'     => '1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_limit',
					'title'    => __( 'Limit', 'foogallery' ),
					'desc'     => __( 'The maximum number of filters to show, 0 = disabled and all are shown.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 0,
					'step'     => '1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_show_count',
					'title'    => __( 'Show Count', 'foogallery' ),
					'desc'     => __( 'Whether or not to show the counts within each filter.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => apply_filters(
							'foogallery_gallery_template_filtering_show_count_choices', array(
							''     => __( 'No', 'foogallery' ),
							'true' => __( 'Yes', 'foogallery' ),
						)
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_adjust_size',
					'title'    => __( 'Adjust Size', 'foogallery' ),
					'desc'     => __( 'Whether or not to adjust the size of each filter depending on the count.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'no',
					'choices'  => apply_filters(
							'foogallery_gallery_template_filtering_adjust_size_choices', array(
							'no'  => __( 'No', 'foogallery' ),
							'yes' => __( 'Yes', 'foogallery' ),
						)
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_adjust_size_smallest',
					'title'    => __( 'Smallest Size', 'foogallery' ),
					'desc'     => __( 'The smallest possible font size to use, when Adjust Size is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 12,
					'step'     => '1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_adjust_size',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_adjust_size_largest',
					'title'    => __( 'Largest Size', 'foogallery' ),
					'desc'     => __( 'The largest possible font size to use, when Adjust Size is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 16,
					'step'     => '1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_adjust_size',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_adjust_opacity',
					'title'    => __( 'Adjust Opacity', 'foogallery' ),
					'desc'     => __( 'Whether or not to adjust the opacity of each filter depending on the count.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'no',
					'choices'  => apply_filters(
						'foogallery_gallery_template_filtering_adjust_opacity_choices', array(
							'no'  => __( 'No', 'foogallery' ),
							'yes' => __( 'Yes', 'foogallery' ),
						)
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_adjust_opacity_lightest',
					'title'    => __( 'Lightest Opacity', 'foogallery' ),
					'desc'     => __( 'The lightest or most transparent opacity to use, when Adjust Opacity is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 0.5,
					'step'     => '0.1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_adjust_opacity',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_adjust_opacity_darkest',
					'title'    => __( 'Darkest Opacity', 'foogallery' ),
					'desc'     => __( 'The darkest or most opaque opacity to use, when Adjust Opacity is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => '1',
					'step'     => '0.1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_adjust_opacity',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'yes',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_sort',
					'title'    => __( 'Sort Mode', 'foogallery' ),
					'desc'     => __( 'How do you want to sort your filters? Default is alphabetical order.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-general' => __( 'General', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'value',
					'choices'  => apply_filters (
						'foogallery_gallery_template_filtering_sort_choices', array(
							'value' => __( 'Default (alphabetical)', 'foogallery' ),
							'value_inverse ' => __( 'Reverse', 'foogallery' ),
							'count' => __( 'Count ascending', 'foogallery' ),
							'count_inverse' => __( 'Count descending', 'foogallery' ),
							'none'  => __( 'No sorting', 'foogallery' ),
						)
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_override',
					'title'    => __( 'Filter Override', 'foogallery' ),
					'desc'     => __( 'You can override which filters are shown, by providing a comma-separated list. Leave blank for them to be auto-generated.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-advanced' => __( 'Advanced', 'foogallery' ) ),
					'type'     => 'text',
					'default'  => '',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_search',
					'title'    => __( 'Include Search', 'foogallery' ),
					'desc'     => __( 'Include a search input where users can filter the gallery by typing in a search term.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-search' => __( 'Search', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => '',
					'choices'  => array(
						''     => __( 'Disabled', 'foogallery' ),
						'true' => __( 'Enabled', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					)
				);

				$filtering_fields[] = array(
					'id'       => 'filtering_search_position',
					'title'    => __( 'Search Position', 'foogallery' ),
					'desc'     => __( 'The position of the search input, relative to the other filters.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-search' => __( 'Search', 'foogallery' ) ),
					'type'     => 'radio',
					'default'  => 'above-center',
					'choices'  =>  array(
						'above-center'  => __( 'Above Center', 'foogallery' ),
						'above-right'   => __( 'Above Right', 'foogallery' ),
						'above-left'    => __( 'Above Left', 'foogallery' ),
						'below-center'  => __( 'Below Center', 'foogallery' ),
						'below-right'   => __( 'Below Right', 'foogallery' ),
						'below-left'    => __( 'Below Left', 'foogallery' ),
						'before'        => __( 'Before Filter', 'foogallery' ),
						'before-merged' => __( 'Before &amp; Merged', 'foogallery' ),
						'after'         => __( 'After Filter', 'foogallery' ),
						'after-merged'  => __( 'After &amp; Merged', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => '!==',
						'data-foogallery-show-when-field'          => 'filtering_search',
						'data-foogallery-show-when-field-value'    => '',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
						'data-foogallery-preview'                  => 'shortcode'
					),
					'class' => 'foogallery-radios-12em'
				);

				$filtering_fields[] = array(
					'id'      => 'filtering_merged_help',
					'title'   => __( 'Merging Search Tip', 'foogallery' ),
					'desc'    => __( 'When using "Before &amp; Merged" or "After &amp; Merged" positions, the search input will be merged with the filter tags. If the filter tags wrap to more than one line, the search will not be merged. To avoid this, limit the number of filter shown.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'subsection' => array( 'filtering-search' => __( 'Search', 'foogallery' ) ),
					'type'    => 'help',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field-operator' => 'regex',
						'data-foogallery-show-when-field'          => 'filtering_search_position',
						'data-foogallery-show-when-field-value'    => 'before-merged|after-merged',
					),
				);

				//find the index of the Advanced section
				$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

				array_splice( $fields, $index, 0, $filtering_fields );
			}

			return $fields;
		}

		/**
		 * Determine if the gallery has filtering enabled
		 *
		 * @param $foogallery FooGallery
		 */
		function determine_filtering( $foogallery ) {
			if ( foogallery_current_gallery_check_template_has_supported_feature( 'filtering_support') ) {

				$filtering_options = false;

				$filtering = foogallery_gallery_template_setting( 'filtering_type', '' );

				if ( '' !== $filtering ) {

					$filtering_source = foogallery_gallery_template_setting( 'filtering_taxonomy', FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
					$filtering_hideall = foogallery_gallery_template_setting( 'filtering_hideall', '' ) === 'hide';

					$filtering_options = array(
						'type'     => 'tags',
						'position' => foogallery_gallery_template_setting( 'filtering_position', 'top' ),
						'noAll'    => $filtering_hideall,
					);

					$theme = foogallery_gallery_template_setting( 'filtering_theme', '' );
					if ( '' !== $theme ) {
						$filtering_options['theme'] = $theme;
					}

					$style = foogallery_gallery_template_setting( 'filtering_style', '' );
					if ( '' !== $style ) {
						$filtering_options['style'] = $style;
					}

					$alignment = foogallery_gallery_template_setting( 'filtering_alignment', '' );
					if ( '' !== $alignment ) {
						$filtering_options['align'] = $alignment;
					}

					$collapse = foogallery_gallery_template_setting( 'filtering_collapse', '' );
					if ( '' !== $collapse ) {
						$filtering_options['collapse'] = $collapse === 'true';
					}

					if ( $filtering_hideall ) {
						$filtering_options['autoSelected'] = foogallery_gallery_template_setting( 'filtering_autoSelected', '' ) === 'true';
					}

					if ( $filtering_source !== '') {
						$filtering_options['taxonomy'] = $filtering_source;
					}

					$filtering_limit = intval( foogallery_gallery_template_setting( 'filtering_limit', '0' ) );
					if ( $filtering_limit > 0 ) {
						$filtering_options['limit'] = $filtering_limit;
					}

					//Now add the advnaced settings
					$filtering_mode = foogallery_gallery_template_setting( 'filtering_mode', 'single' );
					if ( 'single' !== $filtering_mode ) {
						$filtering_options['mode'] = $filtering_mode;
					}

					$filtering_min = intval( foogallery_gallery_template_setting( 'filtering_min', '0' ) );
					if ( $filtering_min > 0 ) {
						$filtering_options['min'] = $filtering_min;
					}

					$filtering_show_count = foogallery_gallery_template_setting( 'filtering_show_count', '' ) === 'true';
					if ( $filtering_show_count ) {
						$filtering_options['showCount'] = $filtering_show_count;
					}

					$filtering_adjust_size    = foogallery_gallery_template_setting( 'filtering_adjust_size', 'no' ) === 'yes';
					if ( $filtering_adjust_size ) {
						$filtering_options['adjustSize'] = $filtering_adjust_size;
						$filtering_options['smallest'] = intval( foogallery_gallery_template_setting( 'filtering_adjust_size_smallest', '12' ) );
						$filtering_options['largest']  = intval( foogallery_gallery_template_setting( 'filtering_adjust_size_largest', '16' ) );
					}

					$filtering_adjust_opacity = foogallery_gallery_template_setting( 'filtering_adjust_opacity', 'no' ) === 'yes';
					if ( $filtering_adjust_opacity ) {
						$filtering_options['adjustOpacity'] = $filtering_adjust_opacity;
						$filtering_options['lightest'] = foogallery_gallery_template_setting( 'filtering_adjust_opacity_lightest', '0.5' );
						$filtering_options['darkest']  = foogallery_gallery_template_setting( 'filtering_adjust_opacity_darkest', '1' );
					}

					$filtering_sort = foogallery_gallery_template_setting( 'filtering_sort', 'value' );
					if ( 'value' !== $filtering_sort ) {
						if ( foo_contains( $filtering_sort, '_inverse' ) ) {
							$filtering_sort = str_replace( '_inverse', '', $filtering_sort );
							$filtering_options['sortInvert'] = true;
						}
						$filtering_options['sortBy'] = trim( $filtering_sort );
					}

					$filtering_override = foogallery_gallery_template_setting( 'filtering_override', '' );
					if ( !empty( $filtering_override ) ) {
						$filtering_options['tags'] = explode( ',', $filtering_override );
						$filtering_options['tags'] = array_filter( array_map( 'trim', $filtering_options['tags'] ) ) ;
					}

					// Multi-level
					if ( 'multi' === $filtering ) {
						$filtering_multi_override = foogallery_gallery_template_setting( 'filtering_multi_override', '' );

						if ( !empty( $filtering_multi_override ) ) {
							$filtering_multi_override_array = @json_decode( wp_unslash( $filtering_multi_override ), true );

							if ( isset( $filtering_multi_override_array ) ) {
								$filtering_options['tags'] = $filtering_multi_override_array;
								$filtering_options['sortBy'] = 'none';
							}
						}
					}
				}

				// Lastly, handle search
				$filtering_search = foogallery_gallery_template_setting( 'filtering_search' ) !== '';

				if ( $filtering_search ) {
					$filtering_options['type'] = 'tags';
					$filtering_options['search'] = true;
					$position = foogallery_gallery_template_setting( 'filtering_search_position', 'above-center' );
					if ( '' === $position ) {
						$position = 'above-center';
					}
					$filtering_options['searchPosition'] = $position;
				}

				if ( $filtering_options !== false ) {
					foogallery_current_gallery_set_cached_value( 'filtering', $filtering_options );
				}
			}
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
			if ( foogallery_current_gallery_has_cached_value( 'filtering' ) ) {
				$options['filtering'] = foogallery_current_gallery_get_cached_value( 'filtering' );
			}

			return $options;
		}

		/**
		 * Applies the taxonomy terms to the thumbnail
		 *
		 * @uses     "foogallery_attachment_html_link_attributes" filter
		 *
		 * @param array $attr
		 * @param array $args
		 * @param FooGalleryAttachment $attachment
		 *
		 * @return array
		 */
		public function add_tag_attribute( $attr, $args, $attachment ) {
			if ( foogallery_current_gallery_has_cached_value( 'filtering' ) ) {
				$filtering = foogallery_current_gallery_get_cached_value( 'filtering' );
				if ( array_key_exists( 'taxonomy', $filtering ) ) {
					$taxonomy = $filtering['taxonomy'];

					//allow other plugins to get the terms for the attachment for the particular taxonomy
					$terms = apply_filters( 'foogallery_filtering_get_terms_for_attachment', false, $taxonomy, $attachment );

					//if no terms were returned, then do the default
					if ( false === $terms ) {
						$terms = wp_get_post_terms( $attachment->ID, $taxonomy, array( 'fields' => 'names' ) );
					}

					$attachment->tags = $terms;

					$attr['data-tags'] = json_encode( $terms );
				}
			}

			return $attr;
		}

		/**
		 * Add the tags to the json object
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
		public function add_json_tags(  $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions ) {
			if ( isset( $foogallery_attachment->tags ) ) {
				$json_object->tags = $foogallery_attachment->tags;
			}

			return $json_object;
		}

		/**
		 * Add global setting to override the "All" text used in the filtering
		 * @param $settings
		 *
		 * @return mixed
		 */
		public function add_language_settings( $settings ) {

			$settings['settings'][] = array(
				'id'      => 'language_filtering_all',
				'title'   => __( 'Filtering All Text', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'All', 'foogallery' ),
				'section' => __( 'Filtering', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_filtering_search',
				'title'   => __( 'Search Input Placeholder', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Search gallery...', 'foogallery' ),
				'section' => __( 'Filtering', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_filtering_search_submit',
				'title'   => __( 'Search Submit (accessibility)', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Submit search', 'foogallery' ),
				'section' => __( 'Filtering', 'foogallery' ),
				'tab'     => 'language'
			);

			$settings['settings'][] = array(
				'id'      => 'language_filtering_search_clear',
				'title'   => __( 'Search Clear (accessibility)', 'foogallery' ),
				'type'    => 'text',
				'default' => __( 'Clear search', 'foogallery' ),
				'section' => __( 'Filtering', 'foogallery' ),
				'tab'     => 'language'
			);

// Not implemented in JS yet
//			$settings['settings'][] = array(
//				'id'      => 'language_filtering_no_items',
//				'title'   => __( 'Filtering No Items Text', 'foogallery' ),
//				'type'    => 'text',
//				'default' => __( 'No items found.', 'foogallery' ),
//				'section' => __( 'Filtering', 'foogallery' ),
//				'tab'     => 'language'
//			);

			return $settings;
		}

		/**
		 * Renders the multi field in admin
		 *
		 * @param $field
		 * @param $gallery
		 * @param $template
		 */
		public function render_multi_field( $field, $gallery, $template ) {
			if ( 'filtering_multi' === $field['type'] ) {

			    $has_levels = false;
			    if ( isset( $field['value'] ) ) {
			        $levels = @json_decode( $field['value'], true );
			        if ( isset( $levels ) ) {
				        $has_levels = true;
			            echo '<table style="margin-bottom: 10px;" class="filtering-multi-table wp-list-table striped widefat"><thead><tr>';
			            echo '<th style="width: 10%;">' . esc_html__( 'Level #', 'foogallery' ) . '</th>';
				        echo '<th style="width: 10%;">' . esc_html__( 'All Text', 'foogallery' ) . '</th>';
				        echo '<th>' . esc_html__( 'Terms', 'foogallery' ) . '</th></tr></thead><tbody>';
			            foreach ( $levels as $index => $level ) {
							$level_all = isset( $level['all'] ) ? sanitize_text_field( $level['all'] ) : '';
							$level_tags = array();
							if ( isset( $level['tags'] ) && is_array( $level['tags'] ) ) {
								$level_tags = array_map( 'sanitize_text_field', $level['tags'] );
							}
			                echo '<tr>';
			                echo '<td><strong>' . esc_html( $index + 1 ) . '</strong></td>';
				            echo '<td>' . esc_html( $level_all ) . '</td>';
				            echo '<td><code>' . esc_html( implode( ', ', $level_tags ) ) . '</code></td>';
							echo '</tr>';
                        }
			            echo '</tbody></table>';
                    }
                }

			    if ( !$has_levels ) {
				    echo '<table style="margin-bottom: 10px; display: none" class="filtering-multi-table wp-list-table striped widefat"><thead><tr>';
				    echo '<th style="width: 10%;">' . esc_html__( 'Level #', 'foogallery' ) . '</th>';
				    echo '<th style="width: 10%;">' . esc_html__( 'All Text', 'foogallery' ) . '</th>';
				    echo '<th>' . esc_html__( 'Terms', 'foogallery' ) . '</th></tr></thead><tbody>';
				    echo '</tbody></table>';
                }

				echo '<button class="button button-primary button-small filtering-multi-builder">' . esc_html__( 'Select Levels', 'foogallery' ) . '</button>';
				$value = isset( $field['value'] ) ? $field['value'] : '';
				echo '<input class="filtering-multi-input" type="hidden" name=' . esc_attr( FOOGALLERY_META_SETTINGS . '[' . $template['slug'] . '_filtering_multi_override]' ) . ' value="' . esc_attr( $value ) . '" />';
			}
		}

		/**
		 * Enqueues js assets in admin
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'foogallery.admin.filtering', FOOGALLERY_PRO_URL . 'css/foogallery.admin.filtering.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.filtering', FOOGALLERY_PRO_URL . 'js/foogallery.admin.filtering.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}

		/**
		 * Renders the multi-level modal for use on the gallery edit page
		 */
		public function render_multi_level_modal() {

			global $post;

			//check if the gallery edit page is being shown
			$screen = get_current_screen();
			if ( 'foogallery' !== $screen->id ) {
				return;
			}

			?>
			<div class="foogallery-multi-filtering-modal-wrapper" data-foogalleryid="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'foogallery_multi_filtering_content' ) ); ?>" style="display: none;">
				<div class="media-modal wp-core-ui">
					<button type="button" class="media-modal-close foogallery-multi-filtering-modal-close">
						<span class="media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'foogallery' ); ?></span>
					</button>
					<div class="media-modal-content">
						<div class="media-frame wp-core-ui">
							<div class="foogallery-multi-filtering-modal-title">
								<h1><?php esc_html_e( 'Multi-level Filtering Builder', 'foogallery' ); ?></h1>
								<a class="foogallery-multi-filtering-modal-reload button" href="#"><span style="padding-top: 4px;" class="dashicons dashicons-update"></span> <?php esc_html_e( 'Reload', 'foogallery' ); ?></a>
								<label for="foogallery-multi-filtering-modal-load-all"><?php esc_html_e( 'Load All', 'foogallery' ); ?></label>
									<input type="checkbox" id="foogallery-multi-filtering-modal-load-all" class="foogallery-multi-filtering-modal-load-all" value="1" />
								</label>
							</div>
							<div class="foogallery-multi-filtering-modal-container not-loaded">
								<div class="spinner is-active"></div>
							</div>
							<div class="foogallery-multi-filtering-modal-toolbar">
								<div class="foogallery-multi-filtering-modal-toolbar-inner">
									<div class="media-toolbar-primary">
										<a href="#"
										   class="foogallery-multi-filtering-modal-close button button-large button-secondary"
										   title="<?php esc_attr_e( 'Close', 'foogallery' ); ?>"><?php esc_html_e( 'Close', 'foogallery' ); ?></a>
                                        <a href="#"
                                           class="foogallery-multi-filtering-modal-set button button-large button-primary"
                                           title="<?php esc_attr_e( 'Set Levels', 'foogallery' ); ?>"><?php esc_html_e( 'Set Levels', 'foogallery' ); ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="media-modal-backdrop"></div>
			</div>
			<?php
		}

		/**
		 * Render the attachment container
		 *
		 * @param $taxonomy
		 * @param $levels
		 * @param $foogallery_id
		 * @param $attachments
		 */
		public function render_content( $taxonomy, $levels, $foogallery_id, $attachments ) {
			echo '<div class="foogallery-multi-filtering-modal-content-inner">';

			$terms = array();

			if ( !empty( $attachments ) ) {
				$attachment_ids = array_filter( array_map( 'absint', explode( ',', $attachments ) ) );
				$terms = wp_get_object_terms( $attachment_ids, $taxonomy );
			}

			if ( empty( $terms ) ) {
				//Always make sure we have some terms.
				$terms = get_terms( array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				) );
			}

			$this->render_content_level( 0, array(), $terms, 'foogallery-multi-filtering-modal-content-level-template' );

			foreach ( $levels as $index => $level ) {
				$this->render_content_level( $index + 1, $level, $terms );
			}

			echo '<a href="#" class="button button-primary foogallery-multi-filtering-add-level">' . esc_html__( 'Add Another Level', 'foogallery' ) . '</a>';

			echo '</div>';
		}

		/**
         * Render a level of filters/terms
         *
		 * @param $index
		 * @param $level
		 * @param $terms
		 * @param string $class
		 */
		private function render_content_level( $index, $level, $terms, $class='foogallery-multi-filtering-modal-content-level' ) {
			echo '<div class="' . esc_attr( $class ) . '">';

			echo '<h3>' . esc_html__( 'Level', 'foogallery' ) . ' <span class="foogallery-multi-filtering-modal-content-level-count">' . esc_html( $index ) . '</span>';
			echo '<a href="#" class="foogallery-multi-filtering-modal-content-level-remove" title="' . esc_attr__( 'Remove Level', 'foogallery' ) . '"><span class="dashicons dashicons-no-alt"></span></a>';
			echo '</h3>';

			$label_text = sprintf(
				__( 'Level %s "All" Text : ' , 'foogallery' ),
				'<span class="foogallery-multi-filtering-modal-content-level-count">' . esc_html( $index ) . '</span>'
			);
			echo '<label>' . wp_kses_post( $label_text ) . '</label>';

			$all_value = array_key_exists( 'all', $level ) ? sanitize_text_field( $level['all'] ) : __( 'All', 'foogallery' );

			echo '<input type="text" value="' . esc_html( $all_value ) . '"/>';

			echo '<ul class="foogallery-multi-filtering-modal-content-terms">';

			$terms_added = array();

			if ( array_key_exists( 'tags', $level ) ) {
			    foreach ( $level['tags'] as $tag ) {
                    $found_term = $this->find_term( $tag, $terms );
                    if ( $found_term !== false ) {
	                    echo '<li><a href="#" class="button-primary button button-small foogallery-multi-filtering-select-term" data-term-id="' . esc_attr( $found_term->term_id ) . '">' . esc_html( $found_term->name ) . '</a></li>';
	                    $terms_added[] = $tag;
                    }
			    }
			}

			foreach ($terms as $term) {
			    //check if we have already added a term
			    if ( !in_array( $term->name, $terms_added ) ) {
				    echo '<li><a href="#" class="button button-small foogallery-multi-filtering-select-term" data-term-id="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</a></li>';
			    }
			}

			echo '</ul><div style="clear: both"></div>';

			echo '</div>';
		}

		/**
         * Find a term in the array
         *
		 * @param $name
		 * @param $terms
		 *
		 * @return bool|mixed
		 */
		private function find_term( $name, $terms ) {
			foreach ($terms as $term) {
			    if ( $term->name === $name ) {
			        return $term;
			    }
			}
			return false;
		}


		/**
		 * Outputs the modal content
		 */
		public function ajax_load_modal_content() {
			$nonce = safe_get_from_request( 'nonce' );

			if ( wp_verify_nonce( $nonce, 'foogallery_multi_filtering_content' ) ) {

				$foogallery_id = intval( safe_get_from_request( 'foogallery_id' ) );
				$taxonomy = safe_get_from_request( 'taxonomy' );
				$levels = safe_get_from_request( 'levels' );
				$attachments = safe_get_from_request( 'attachments' );

				if ( empty( $taxonomy ) ) {

					//select the taxonomy that is chosen for the gallery
					$foogallery = FooGallery::get_by_id( $foogallery_id );

					if ( foogallery_default_datasource() !== $foogallery->datasource_name ) {
						
						//force the gallery to load it's attachments. This is needed for non media datasources.
						//also, make the datasource "think" its in a preview to force a fresh load.
						global $foogallery_gallery_preview;
						$foogallery_gallery_preview = true;
						$gallery_attachments = $foogallery->attachments();
						$foogallery_gallery_preview = false;

						//Always force the attachments to be empty for non media datasources, so all terms are loaded.
						$attachments = '';

						if ( isset( $foogallery->taxonomy ) ) {
							$taxonomy = $foogallery->taxonomy;
						}
					}

					if ( empty( $taxonomy ) && !$foogallery->is_new() ) {
						$taxonomy = $foogallery->get_setting( 'filtering_taxonomy', '' );
					}
				}

				if ( empty( $taxonomy ) ) {
					$taxonomy = FOOGALLERY_ATTACHMENT_TAXONOMY_TAG;
				}

				if ( empty( $levels ) ) {
				    //add the first level by default
					$levels = array(
                        array(
                            'all'  => __( 'All', 'foogallery' ),
                            'tags' => array()
                        )
                    );
				}

				echo '<div class="foogallery-multi-filtering-modal-content">';
				$this->render_content( $taxonomy, $levels, $foogallery_id, $attachments );
				echo '</div>';

				echo '<div class="foogallery-multi-filtering-modal-sidebar">';
				echo '<div class="foogallery-multi-filtering-modal-sidebar-inner">';
				echo '<h2>' . esc_html__( 'Multi Level Filtering Help', 'foogallery' ) . '</h2>';
				echo '<p>' . esc_html__( 'To add a new level, click on the "Add Another Level" button on the left.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'For each level that you add, you can override the "All" text for that level.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'Select the terms for each level by clicking on them. They will change to a selected state. To unselect a term, click on it again.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'Once you select a term, it will not be available for the other levels.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'You can sort the terms by dragging and dropping them.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'To remove a level, click on the small "x" button next to the level title.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'If you want to undo any changes, click the "Reload" button at the top.', 'foogallery' ) . '</p>';
				echo '<p>' . esc_html__( 'Once you are happy with your levels, click the "Set Levels" button below.', 'foogallery' ) . '</p>';
				echo '</div>';
				echo '</div>';
			}

			die();
		}
	}
}

