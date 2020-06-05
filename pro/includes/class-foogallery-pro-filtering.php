<?php
/**
 * FooGallery Pro Filtering Class
 */
if ( ! class_exists( 'FooGallery_Pro_Filtering' ) ) {

	class FooGallery_Pro_Filtering {

		function __construct() {
			if ( is_admin() ) {
				//add extra fields to the templates that support filtering
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_filtering_fields' ), 10, 2 );

				//set the settings icon for filtering
				add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

				//add a global setting to change the All filter
				add_filter( 'foogallery_admin_settings_override', array( $this, 'add_language_settings' ) );

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
				$fields[] = array(
					'id'       => 'filtering_type',
					'title'    => __( 'Filtering', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => '',
					'choices'  => apply_filters(
							'foogallery_gallery_template_filtering_type_choices', array(
							''        => __( 'None', 'foogallery' ),
							'simple' => __( 'Simple', 'foogallery' ),
							'advanced'    => __( 'Advanced', 'foogallery' ),
							'multi'    => __( 'Multi-level', 'foogallery' )
						)
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					)
				);

				$fields[] = array(
					'id'      => 'filtering_theme',
					'title'   => __( 'Theme', 'foogallery' ),
					'desc'    => __( 'The theme used for filtering.', 'foogallery' ),
					'section' => __( 'Filtering', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'default' => 'fg-light',
					'choices' => apply_filters( 'foogallery_gallery_template_filtering_theme_choices', array(
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

				$fields[] = array(
					'id'       => 'filtering_taxonomy',
					'title'    => __( 'Filtering Source', 'foogallery' ),
					'desc'     => __( 'What will be used as the source for your gallery filters. All attachment taxonomies will be listed.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
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

				$fields[] = array(
					'id'       => 'filtering_position',
					'title'    => __( 'Position', 'foogallery' ),
					'desc'     => __( 'The position of the filters relative to the gallery.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
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

				$fields[] = array(
					'id'       => 'filtering_multi_override',
					'title'    => __( 'Levels', 'foogallery' ),
					'desc'     => __( 'The filtering levels that will be used for the gallery.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
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

				$fields[] = array(
					'id'      => 'filtering_mode_help',
					'title'   => __( 'Selection Mode Help', 'foogallery' ),
					'desc'    => __( 'The default selection mode is Single, which allows you to choose a single filter at a time. You can also choose to filter by more than 1 filter by selecting Multiple. Multiple supports either a union (OR) or an intersect (AND) mode.', 'foogallery' ),
					'section' => __( 'Filtering', 'foogallery' ),
					'type'    => 'help',
					'row_data' => array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);

				$fields[] = array(
					'id'       => 'filtering_mode',
					'title'    => __( 'Selection Mode', 'foogallery' ),
					'desc'     => __( 'The selection mode to use when filtering.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
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
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => 'advanced',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$fields[] = array(
					'id'       => 'filtering_min',
					'title'    => __( 'Minimum', 'foogallery' ),
					'desc'     => __( 'The minimum count before a filter is shown, 0 = disabled and all are shown.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 0,
					'step'     => '1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);

				$fields[] = array(
					'id'       => 'filtering_limit',
					'title'    => __( 'Limit', 'foogallery' ),
					'desc'     => __( 'The maximum number of filters to show, 0 = disabled and all are shown.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'type'     => 'number',
					'class'    => 'small-text',
					'default'  => 0,
					'step'     => '1',
					'min'      => '0',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);

				$fields[] = array(
					'id'       => 'filtering_show_count',
					'title'    => __( 'Show Count', 'foogallery' ),
					'desc'     => __( 'Whether or not to show the counts within each filter.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => '',
					'choices'  => apply_filters(
						'foogallery_gallery_template_filtering_show_count_choices', array(
						''     => __( 'No', 'foogallery' ),
						'true' => __( 'Yes', 'foogallery' ),
					)
					),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);

				$fields[] = array(
					'id'       => 'filtering_adjust_size',
					'title'    => __( 'Adjust Size', 'foogallery' ),
					'desc'     => __( 'Whether or not to adjust the size of each filter depending on the count.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => 'no',
					'choices'  => apply_filters(
						'foogallery_gallery_template_filtering_adjust_size_choices', array(
						'no'  => __( 'No', 'foogallery' ),
						'yes' => __( 'Yes', 'foogallery' ),
					)
					),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);

				$fields[] = array(
					'id'       => 'filtering_adjust_size_smallest',
					'title'    => __( 'Smallest Size', 'foogallery' ),
					'desc'     => __( 'The smallest possible font size to use, when Adjust Size is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
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

				$fields[] = array(
					'id'       => 'filtering_adjust_size_largest',
					'title'    => __( 'Largest Size', 'foogallery' ),
					'desc'     => __( 'The largest possible font size to use, when Adjust Size is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
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

				$fields[] = array(
					'id'       => 'filtering_adjust_opacity',
					'title'    => __( 'Adjust Opacity', 'foogallery' ),
					'desc'     => __( 'Whether or not to adjust the opacity of each filter depending on the count.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => 'no',
					'choices'  => apply_filters(
						'foogallery_gallery_template_filtering_adjust_opacity_choices', array(
						'no'  => __( 'No', 'foogallery' ),
						'yes' => __( 'Yes', 'foogallery' ),
					)
					),
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-value-selector'           => 'input:checked',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);

				$fields[] = array(
					'id'       => 'filtering_adjust_opacity_lightest',
					'title'    => __( 'Lightest Opacity', 'foogallery' ),
					'desc'     => __( 'The lightest or most transparent opacity to use, when Adjust Opacity is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
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

				$fields[] = array(
					'id'       => 'filtering_adjust_opacity_darkest',
					'title'    => __( 'Darkest Opacity', 'foogallery' ),
					'desc'     => __( 'The darkest or most opaque opacity to use, when Adjust Opacity is used.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
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

				$fields[] = array(
					'id'       => 'filtering_sort',
					'title'    => __( 'Sort Mode', 'foogallery' ),
					'desc'     => __( 'How do you want to sort your filters? Default is by the filter name.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
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
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-value'    => 'advanced',
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode'
					)
				);

				$fields[] = array(
					'id'       => 'filtering_override',
					'title'    => __( 'Override', 'foogallery' ),
					'desc'     => __( 'You can override which filters are shown, by providing a comma-separated list. Leave blank for them to be auto-generated.', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'type'     => 'text',
					'default'  => '',
					'row_data' => array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'filtering_type',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'advanced',
					)
				);
			}

			return $fields;
		}

		/**
		 * Determine if the gallery has filtering enabled
		 *
		 * @param $foogallery FooGallery
		 */
		function determine_filtering( $foogallery ) {
			$template_data = foogallery_get_gallery_template( $foogallery->gallery_template );

			//check the template supports filtering
			$filtering = $template_data && array_key_exists( 'filtering_support', $template_data ) && true === $template_data['filtering_support'];

			$foogallery->filtering = apply_filters( 'foogallery_filtering', $filtering, $foogallery );
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
			if ( isset( $gallery->filtering ) && true === $gallery->filtering ) {

				//check if we have arguments from the shortcode and override the saved settings
				$filtering = $this->get_foogallery_argument( $gallery, 'filtering_type', 'filtering_type', '' );

				if ( '' !== $filtering ) {

					$filtering_options = array(
						'type'     => 'tags',
						'position' => $this->get_foogallery_argument( $gallery, 'filtering_position', 'filtering_position', 'top' ),
						'theme'    => $this->get_foogallery_argument( $gallery, 'filtering_theme', 'filtering_theme', 'fg-light' ),
					);

					if ( 'advanced' === $filtering ) {

						$filtering_show_count = $this->get_foogallery_argument( $gallery, 'filtering_show_count', 'filtering_show_count', '' ) === 'true';

						$filtering_options['mode'         ] = $this->get_foogallery_argument( $gallery, 'filtering_mode', 'filtering_mode', 'single' );
						$filtering_options['min'          ] = intval( $this->get_foogallery_argument( $gallery, 'filtering_min', 'filtering_min', '0' ) );
						$filtering_options['limit'        ] = intval( $this->get_foogallery_argument( $gallery, 'filtering_limit', 'filtering_limit', '0' ) );
						$filtering_options['showCount'    ] = $filtering_show_count;

						$filtering_adjust_size    = $this->get_foogallery_argument( $gallery, 'filtering_adjust_size', 'filtering_adjust_size', 'no' ) === 'yes';
						if ( $filtering_adjust_size ) {
							$filtering_options['adjustSize'] = $filtering_adjust_size;
							$filtering_options['smallest'] = intval( $this->get_foogallery_argument( $gallery, 'filtering_adjust_size_smallest', 'filtering_adjust_size_smallest', '12' ) );
							$filtering_options['largest']  = intval( $this->get_foogallery_argument( $gallery, 'filtering_adjust_size_largest', 'filtering_adjust_size_largest', '16' ) );
						}

						$filtering_adjust_opacity = $this->get_foogallery_argument( $gallery, 'filtering_adjust_opacity', 'filtering_adjust_opacity', 'no' ) === 'yes';
						if ( $filtering_adjust_opacity ) {
							$filtering_options['adjustOpacity'] = $filtering_adjust_opacity;
							$filtering_options['lightest'] = $this->get_foogallery_argument( $gallery, 'filtering_adjust_opacity_lightest', 'filtering_adjust_opacity_lightest', '0.5' );
							$filtering_options['darkest']  = intval( $this->get_foogallery_argument( $gallery, 'filtering_adjust_opacity_darkest', 'filtering_adjust_opacity_darkest', '1' ) );
						}

						$filtering_sort = $this->get_foogallery_argument( $gallery, 'filtering_sort', 'filtering_sort', 'value' );
						if ( 'value' !== $filtering_sort ) {
							if ( foo_contains( $filtering_sort, '_inverse' ) ) {
								$filtering_sort = str_replace( '_inverse', '', $filtering_sort );
								$filtering_options['sortInvert'] = true;
							}
							$filtering_options['sortBy'] = trim( $filtering_sort );
						}

						$filtering_override = $this->get_foogallery_argument( $gallery, 'filtering_override', 'filtering_override', '' );
						if ( !empty( $filtering_override ) ) {
							$filtering_options['tags'] = explode( ',', $filtering_override );
							$filtering_options['tags'] = array_filter( array_map( 'trim', $filtering_options['tags'] ) ) ;
						}
					} else if ( 'multi' === $filtering ) {

						$filtering_multi_override = $this->get_foogallery_argument( $gallery, 'filtering_multi_override', 'filtering_multi_override', '' );

						if ( !empty( $filtering_multi_override ) ) {
							$filtering_multi_override_array = @json_decode( wp_unslash( $filtering_multi_override ), true );

							if ( isset( $filtering_multi_override_array ) ) {
								$filtering_options['tags'] = $filtering_multi_override_array;
							}
						}
                    }

					$options['filtering']        = $gallery->filtering_options = $filtering_options;
					$gallery->filtering_taxonomy = $this->get_foogallery_argument( $gallery, 'filtering_taxonomy', 'filtering_taxonomy', FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );

					$filtering_all_text_default = __( 'All', 'foogallery' );
					$filtering_all_text = foogallery_get_setting( 'language_filtering_all', $filtering_all_text_default );
					if ( empty( $filtering_all_text ) ) {
						$filtering_all_text = $filtering_all_text_default;
					}
					if ( $filtering_all_text_default !== $filtering_all_text ) {
						if ( !array_key_exists( 'il8n', $options ) ) {
							$options['il8n'] = array();
						}

						$options['il8n']['filtering'] = array(
							'all' => $filtering_all_text
						);
					}
				}
			}

			return $options;
		}

		/**
		 * Private helper function to get the value of a setting for a gallery
		 * @param $gallery
		 * @param $setting_id
		 * @param $argument_name
		 * @param $default_value
		 *
		 * @return mixed
		 */
		private function get_foogallery_argument( $gallery, $setting_id, $argument_name, $default_value ) {
			global $current_foogallery_arguments;

			if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments[$argument_name] ) ) {
				return $current_foogallery_arguments[$argument_name];
			} else {
				return $gallery->get_setting( $setting_id, $default_value );
			}
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
			global $current_foogallery;

			if ( isset( $current_foogallery->filtering_taxonomy ) && isset( $current_foogallery->filtering ) && true === $current_foogallery->filtering ) {
				$taxonomy = $current_foogallery->filtering_taxonomy;

				//allow other plugins to get the terms for the attachment for the particular taxonomy
				$terms = apply_filters( 'foogallery_filtering_get_terms_for_attachment', false, $taxonomy, $attachment );

				//if no terms were returned, then do the default
				if ( false === $terms ) {
					$terms = wp_get_post_terms( $attachment->ID, $taxonomy, array( 'fields' => 'names' ) );
				}

				$attachment->tags = $terms;

				$attr['data-tags'] = json_encode($terms);
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
				'tab'     => 'language'
			);

			return $settings;
		}

		public function render_multi_field( $field, $gallery, $template ) {
			if ( 'filtering_multi' === $field['type'] ) {
			    if ( isset( $field['value'] ) ) {
			        $levels = @json_decode( $field['value'], true );
			        if ( isset( $levels ) ) {
			            echo '<table style="margin-bottom: 10px;" class="wp-list-table striped widefat"><thead><tr>';
			            echo '<th style="width: 10%;">' . __( 'Level #', 'foogallery' ) . '</th>';
				        echo '<th style="width: 10%;">' . __( 'All Text', 'foogallery' ) . '</th>';
				        echo '<th>' . __( 'Terms', 'foogallery' ) . '</th></th></thead><tbody>';
			            foreach ( $levels as $index => $level ) {
			                echo '<tr>';
			                echo '<td><strong>' . ($index + 1) . '</strong></td>';
				            echo '<td>' . $level['all'] . '</td>';
				            echo '<td><code>' . implode(', ', $level['tags'] ) . '</code></td>';
                        }
			            echo '</tbody></table>';
                    }
                }

				echo '<button class="button button-primary button-small filtering-multi-builder">' . __( 'Select Levels', 'foogallery' ) . '</button>';
				echo '<input class="filtering-multi-input" type="hidden" name=' . esc_attr( FOOGALLERY_META_SETTINGS . '[' . $template['slug'] . '_filtering_multi_override]' ) . ' value="' . esc_html( $field['value'] ) . '" />';
			}
		}

		/**
		 * Enqueues js assets
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
			<div class="foogallery-multi-filtering-modal-wrapper" data-foogalleryid="<?php echo $post->ID; ?>" data-nonce="<?php echo wp_create_nonce( 'foogallery_multi_filtering_content' ); ?>" style="display: none;">
				<div class="media-modal wp-core-ui">
					<button type="button" class="media-modal-close foogallery-multi-filtering-modal-close">
						<span class="media-modal-icon"><span class="screen-reader-text"><?php _e( 'Close', 'foogallery' ); ?></span>
					</button>
					<div class="media-modal-content">
						<div class="media-frame wp-core-ui">
							<div class="foogallery-multi-filtering-modal-title">
								<h1><?php _e('Multi-level Filtering Builder', 'foogallery'); ?></h1>
								<a class="foogallery-multi-filtering-modal-reload button" href="#"><span style="padding-top: 4px;" class="dashicons dashicons-update"></span> <?php _e('Reload', 'foogallery'); ?></a>
							</div>
							<div class="foogallery-multi-filtering-modal-container not-loaded">
								<div class="spinner is-active"></div>
							</div>
							<div class="foogallery-multi-filtering-modal-toolbar">
								<div class="foogallery-multi-filtering-modal-toolbar-inner">
									<div class="media-toolbar-primary">
										<a href="#"
										   class="foogallery-multi-filtering-modal-close button button-large button-secondary"
										   title="<?php esc_attr_e('Close', 'foogallery'); ?>"><?php _e('Close', 'foogallery'); ?></a>
                                        <a href="#"
                                           class="foogallery-multi-filtering-modal-set button button-large button-primary"
                                           title="<?php esc_attr_e('Set Levels', 'foogallery'); ?>"><?php _e('Set Levels', 'foogallery'); ?></a>
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
		 * @param $foogallery_id
		 * @param $attachments
		 */
		public function render_content( $taxonomy, $levels ) {
			echo '<div class="foogallery-multi-filtering-modal-content-inner">';

			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			) );

			$this->render_content_level( 0, array(), $terms, 'foogallery-multi-filtering-modal-content-level-template' );

			foreach ( $levels as $index => $level ) {
				$this->render_content_level( $index + 1, $level, $terms );
			}

			echo '<a href="#" class="button button-primary foogallery-multi-filtering-add-level">' . __('Add Another Level') . '</a>';

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

			echo '<h3>' . __( 'Level', 'foogallery' ) . ' <span class="foogallery-multi-filtering-modal-content-level-count">' . esc_html( $index ) . '</span>';
			echo '<a href="#" class="foogallery-multi-filtering-modal-content-level-remove" title="' . __('Remove Level', 'foogallery') . '"><i class="dashicons dashicons-no-alt" /></a>';
			echo '</h3>';

			echo '<label>' . sprintf( __( 'Level %s "All" Text : ' , 'foogallery' ), '<span class="foogallery-multi-filtering-modal-content-level-count">' . esc_html( $index ) . '</span>' ) . '</label>';

			$all_value = array_key_exists( 'all', $level ) ? $level['all'] : __('All', 'foogallery');

			echo '<input type="text" value="' . esc_html( $all_value ) . '" />';

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

			echo '</ul><div style="clear: both"/>';

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

				if ( empty( $taxonomy ) ) {
					//select the taxonomy that is chosen for the gallery
					$foogallery = FooGallery::get_by_id( $foogallery_id );
					if ( !$foogallery->is_new() ) {
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
				$this->render_content( $taxonomy, $levels );
				echo '</div>';

				echo '<div class="foogallery-multi-filtering-modal-sidebar">';
				echo '<div class="foogallery-multi-filtering-modal-sidebar-inner">';
				echo '<h2>' . __( 'Multi Level Filtering Help', 'foogallery' ) . '</h2>';
				echo '<p>' . __( 'To add a new level, click on the "Add Another Level" button on the left.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'For each level that you add, you can override the "All" text for that level.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'Select the terms for each level by clicking on them. They will change to a selected state. To unselect a term, click on it again.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'Once you select a term, it will not be available for the other levels.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'You can sort the terms by dragging and dropping them.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'To remove a level, click on the small "x" button next to the level title.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'If you want to undo any changes, click the "Reload" button at the top.', 'foogallery' ) . '</p>';
				echo '<p>' . __( 'Once you are happy with your levels, click the "Set Levels" button below.', 'foogallery' ) . '</p>';
				echo '</div>';
				echo '</div>';
			}

			die();
		}
	}
}

