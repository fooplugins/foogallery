<?php

if ( !class_exists( 'FooGallery_Slider_Gallery_Template' ) ) {

	class FooGallery_Slider_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 101 );

			add_filter( 'foogallery_override_gallery_template_fields-slider', array( $this, 'add_additional_setting_fields' ), 10, 2 );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_located_template-slider', array( $this, 'enqueue_dependencies' ) );

			//change fields for the template
			add_filter( 'foogallery_override_gallery_template_fields-slider', array( $this, 'change_common_thumbnail_fields' ), 10, 2 );

			//add the data options needed for polaroid
			add_filter( 'foogallery_build_container_data_options-slider', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-slider', array( $this, 'override_settings'), 10, 3 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-slider', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-slider', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-slider', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-slider', array( $this, 'build_gallery_template_arguments' ) );
		}

		/**
		 * Add the video gallery template to the list of templates available
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {

			$gallery_templates[] = array(
				'slug'        => 'slider',
				'name'        => __( 'Slider', 'foogallery'),
				'preview_support' => true,
				'common_fields_support' => true,
				'lazyload_support' => true,
				'paging_support' => true,
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
				'mandatory_classes' => 'fg-slider',
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_dimensions',
						'title'   => __( 'Thumbnail Size', 'foogallery' ),
						'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'thumb_size_no_crop',
						'default' => array(
							'width' => 150,
							'height' => 150
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'layout',
						'title'   => __('Layout', 'foogallery'),
						'desc'    => __( 'You can choose either a horizontal or vertical layout for your responsive video gallery.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'icon',
						'default' => '',
						'choices' => array(
							'' => array( 'label' => __( 'Vertical' , 'foogallery' ), 'img' => plugin_dir_url( __FILE__ ) . 'assets/video-layout-vertical.png' ),
							'fg-horizontal' => array( 'label' => __( 'Horizontal' , 'foogallery' ), 'img' => plugin_dir_url( __FILE__ ) . 'assets/video-layout-horizontal.png' )
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview' => 'shortcode',
						)
					),
					array(
						'id'      => 'viewport',
						'title'   => __('Use Viewport Width', 'foogallery'),
						'desc'    => __('Use the viewport width instead of the parent element width.', 'foogallery'),
						'section' => __( 'General', 'foogallery' ),
						'default' => '',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'No', 'foogallery' ),
							'yes' => __( 'Yes', 'foogallery' )
						)
					),
				)
			);

			return $gallery_templates;
		}

		/**
		 * Add additional fields to the settings
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_additional_setting_fields( $fields, $template ) {
			$fields[] = array(
				'id'      => 'theme_custom_bgcolor',
				'title'   => __('Background Color', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#000000',
				'opacity' => true,
				'row_data' => array(
					'data-foogallery-hidden'          		   => true,
					'data-foogallery-show-when-field'          => 'theme',
					'data-foogallery-show-when-field-value'    => 'fg-custom',
				)
			);
			$fields[] = array(
				'id'      => 'theme_custom_textcolor',
				'title'   => __('Text Color', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#ffffff',
				'opacity' => true,
				'row_data' => array(
					'data-foogallery-hidden'          		   => true,
					'data-foogallery-show-when-field'          => 'theme',
					'data-foogallery-show-when-field-value'    => 'fg-custom',
				)
			);
			$fields[] = array(
				'id'      => 'theme_custom_hovercolor',
				'title'   => __('Hover BG Color', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#222222',
				'opacity' => true,
				'row_data' => array(
					'data-foogallery-hidden'          		   => true,
					'data-foogallery-show-when-field'          => 'theme',
					'data-foogallery-show-when-field-value'    => 'fg-custom',
				)
			);
			$fields[] =	array(
				'id'      => 'theme_custom_dividercolor',
				'title'   => __('Divider Color', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#2e2e2e',
				'opacity' => true,
				'row_data' => array(
					'data-foogallery-hidden'          		   => true,
					'data-foogallery-show-when-field'          => 'theme',
					'data-foogallery-show-when-field-value'    => 'fg-custom',
				)
			);
			$fields[] =	array(
				'id'      => 'highlight',
				'title'   => __('Highlight', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'desc'    => __('The color that is used to highlight the selected video.', 'foogallery'),
				'default' => 'fgs-purple',
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'choices' => array(
					'fgs-purple' => __( 'Purple', 'foogallery' ),
					'fgs-blue' => __( 'Blue', 'foogallery' ),
					'fgs-green' => __( 'Green', 'foogallery' ),
					'fgs-orange' => __( 'Orange', 'foogallery' ),
					'fgs-red' => __( 'Red', 'foogallery' ),
					'fg-custom' => __( 'Custom', 'foogallery' )
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-preview' => 'class',
				)
			);
			$fields[] =	array(
				'id'      => 'highlight_custom_bgcolor',
				'title'   => __('Highlight BG Color', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#7816d6',
				'opacity' => true,
				'row_data' => array(
					'data-foogallery-hidden'          		   => true,
					'data-foogallery-show-when-field'          => 'highlight',
					'data-foogallery-show-when-field-value'    => 'fg-custom',
				)
			);
			$fields[] =	array(
				'id'      => 'highlight_custom_textcolor',
				'title'   => __('Highlight Text Color', 'foogallery'),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => 'rgba(255, 255, 255, 1)',
				'opacity' => true,
				'row_data' => array(
					'data-foogallery-hidden'          		   => true,
					'data-foogallery-show-when-field'          => 'highlight',
					'data-foogallery-show-when-field-value'    => 'fg-custom',
				)
			);

			$fields[] = array(
				'id'      => 'thumbnail_captions',
				'title'   => __('Thumbnail Captions', 'foogallery'),
				'desc'    => __('You can choose to hide the captions for the small thumbnails in the slider.', 'foogallery'),
				'section' => __( 'Captions', 'foogallery' ),
				'default' => '',
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'choices' => array(
					'' => __( 'Show Captions', 'foogallery' ),
					'fgs-no-captions' => __( 'Hide Captions', 'foogallery' )
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-preview' => 'class',
				)
			);

			return $fields;
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 * @param $extensions
		 *
		 * @return array
		 */
		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		/**
		 * Enqueue scripts that the template relies on
		 */
		function enqueue_dependencies() {
			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();
		}

		/**
		 * Remove some common fields
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function change_common_thumbnail_fields( $fields, $template ) {

			$fields_to_remove = array();
			$fields_to_remove[] = 'border_size';
			$fields_to_remove[] = 'rounded_corners';
			$fields_to_remove[] = 'drop_shadow';
			$fields_to_remove[] = 'loaded_effect';
 			$fields_to_remove[] = 'hover_effect_help';
			$fields_to_remove[] = 'theme_custom_help';
			$fields_to_remove[] = 'hover_effect_preset_size';
			$fields_to_remove[] = 'hover_effect_caption_visibility';
			$fields_to_remove[] = 'captions_help';
			$fields_to_remove[] = 'video_size_help';
			$fields_to_remove[] = 'video_size';

			$indexes_to_remove = array();

			foreach ($fields as $key => &$field) {
				if ( 'hover_effect_preset' === $field['id'] ) {
					$field['default'] = 'fg-custom';
					$field['choices'] = array(
						'fg-custom'  => __( 'Slider', 'foogallery' )
					);
					$field['row_data'] = array(
						'data-foogallery-hidden' => true,
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-value-selector' => 'input:checked',
						'data-foogallery-preview' => 'class'
					);
				} else if ( 'video_autoplay' === $field['id'] ) {
					$field['title'] = __( 'Autoplay', 'foogallery' );
					$field['desc'] = __( 'Try to autoplay the video when selected. This will only work with videos hosted on Youtube or Vimeo.', 'foogallery' );
				}

				if ( in_array( $field['id'], $fields_to_remove ) ) {
					$indexes_to_remove[] = $key;
				}
			}

			foreach ($indexes_to_remove as $index) {
				unset( $fields[$index] );
			}

			return $fields;
		}

		/**
		 * Add the required data options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {
			$viewport = foogallery_gallery_template_setting( 'viewport', 40 );
			if ( 'yes' === $viewport ) {
				$options['template']['viewport'] = true;
			}

			return $options;
		}

		/**
		 * Override specific settings so that the gallery template will always work
		 *
		 * @param $settings
		 * @param $post_id
		 * @param $form_data
		 *
		 * @return mixed
		 */
		function override_settings($settings, $post_id, $form_data) {
			$settings['slider_hover_effect_preset'] = 'fg-custom';
			return $settings;
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_dimensions'] = $post_data[FOOGALLERY_META_SETTINGS]['slider_thumbnail_dimensions'];
			$args['layout'] = $post_data[FOOGALLERY_META_SETTINGS]['slider_layout'];
			$args['viewport'] = $post_data[FOOGALLERY_META_SETTINGS]['slider_viewport'];
			return $args;
		}

		/**
		 * Builds thumb dimensions from arguments
		 *
		 * @param array $dimensions
		 * @param array $arguments
		 *
		 * @return mixed
		 */
		function build_thumbnail_dimensions_from_arguments( $dimensions, $arguments ) {
			if ( array_key_exists( 'thumbnail_dimensions', $arguments) ) {
				return array(
					'height' => intval($arguments['thumbnail_dimensions']['height']),
					'width' => intval($arguments['thumbnail_dimensions']['width']),
					'crop' => '1'
				);
			}
			return null;
		}

		/**
		 * Get the thumb dimensions arguments saved for the gallery for this gallery template
		 *
		 * @param array $dimensions
		 * @param FooGallery $foogallery
		 *
		 * @return mixed
		 */
		function get_thumbnail_dimensions( $dimensions, $foogallery ) {
			$dimensions = $foogallery->get_meta( 'slider_thumbnail_dimensions', array(
				'width' => 150,
				'height' => 150
			) );
			$dimensions['crop'] = true;
			return $dimensions;
		}

		/**
		 * Build up the arguments needed for rendering this gallery template
		 *
		 * @param $args
		 * @return array
		 */
		function build_gallery_template_arguments( $args ) {
			$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
			$args['crop'] = '1'; //we now force thumbs to be cropped

			return $args;
		}
	}
}