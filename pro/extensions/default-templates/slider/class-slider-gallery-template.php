<?php

if ( !class_exists( 'FooGallery_Slider_Gallery_Template' ) ) {

	class FooGallery_Slider_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 101 );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//change fields for the template
			add_filter( 'foogallery_override_gallery_template_fields-slider', array( $this, 'change_common_thumbnail_fields' ), 10, 2 );

			//add the data options needed for slider
			add_filter( 'foogallery_build_container_data_options-slider', array( $this, 'add_data_options' ), 20, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-slider', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-slider', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-slider', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-slider', array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'adjust_classes' ), 99, 2 );

			//set the settings icon for lightbox
			add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

			add_filter( 'foogallery_gallery_template_setting-lightbox_caption_override', array( $this, 'override_lightbox_caption_override' ) );
		}

		function override_lightbox_caption_override( $value ) {
			global $current_foogallery;
			global $current_foogallery_arguments;
			global $current_foogallery_template;

			if ( isset( $current_foogallery ) && isset( $current_foogallery_template ) && $current_foogallery_template === 'slider' ) {
				return 'override';
			}

			return $value;
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_section_icons( $section_slug ) {
			if ( 'slider' === $section_slug ) {
				return 'dashicons-align-left';
			}

			return $section_slug;
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
				'name'        => __( 'Slider PRO', 'foogallery'),
				'preview_support' => true,
				'common_fields_support' => true,
				'lazyload_support' => true,
				'paging_support' => false,
				'thumbnail_dimensions' => false,
				'filtering_support' => true,
				'mandatory_classes' => 'fg-slider',
				'embed_support' => true,
				'panel_support' => true,
				'enqueue_core' => true,
				'fields'	  => array(
					array(
						'id'      => 'aspect-ratio',
						'section' => __( 'Slider', 'foogallery' ),
						'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
						'title'   => __('Aspect Ratio', 'foogallery'),
						'desc' => __('Select the aspect ratio the slider will use, to best suit your content.', 'foogallery'),
						'default' => 'fg-16-9',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'fg-16-9' => __( '16:9', 'foogallery' ),
							'fg-16-10' => __( '16:10', 'foogallery' ),
							'fg-4-3' => __( '4:3', 'foogallery' ),
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'slider_help',
						'title'   => __('Help', 'foogallery'),
						'desc'    => __( 'You can change the layout of the slider by changing the position of the thumbnails.', 'foogallery' ),
						'section' => __( 'Slider', 'foogallery' ),
						'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
						'type'    => 'help',
					),
				)
			);

			return $gallery_templates;
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
			$fields_to_remove[] = 'lightbox_theme';
			$fields_to_remove[] = 'lightbox_no_scrollbars';
			$fields_to_remove[] = 'lightbox_caption_override';
			$fields_to_remove[] = 'captions_help';
			$fields_to_remove[] = 'caption_title_source';
			$fields_to_remove[] = 'caption_desc_source';
			$fields_to_remove[] = 'captions_limit_length';
			$fields_to_remove[] = 'caption_title_length';
			$fields_to_remove[] = 'caption_desc_length';
			$fields_to_remove[] = 'captions_type';
			$fields_to_remove[] = 'caption_custom_template';
			$fields_to_remove[] = 'caption_custom_help';

			//$fields_to_remove[] = 'lightbox_caption_override';

			$indexes_to_remove = array();

			$settings_link = sprintf( '<a target="blank" href="%s">%s</a>', foogallery_admin_settings_url(), __( 'settings', 'foogallery' ) );
			$captions_choices = array(
				'none'    => __( 'None', 'foogallery' ),
				''        => sprintf( __( 'Default (as per %s)', 'foogallery' ), $settings_link ),
				'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
				'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
				'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
				'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' ),
			);

			foreach ($fields as $key => &$field) {
				if ( $field['section'] === __( 'Panel', 'foogallery' ) ) {
					$field['section'] = __( 'Slider', 'foogallery' );
				}

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
				} else if ( 'lightbox_fit_media' === $field['id'] ) {
					$field['default'] = 'yes';
				} else if ( 'lightbox_buttons_display' === $field['id'] ) {
					$field['default'] = 'yes';
				} else if ( 'lightbox_thumbs' === $field['id'] ) {
					$field['title'] = __( 'Thumbnails', 'foogallery' );
					$field['default'] = 'right';
				} else if ( 'lightbox_thumbs_captions' === $field['id'] ) {
					$field['title'] = __( 'Thumbnail Captions', 'foogallery' );
					$field['default'] = 'yes';
				} else if ( 'lightbox_info_position' === $field['id'] ) {
					$field['default'] = 'top';
				} else if ( 'lightbox_hover_buttons' === $field['id'] ) {
					$field['default'] = 'yes';
				} else if ( 'lightbox_thumbs_bestfit' === $field['id'] ) {
					$field['default'] = 'yes';
				} else if ( 'lightbox_caption_override_title' === $field['id'] ) {
					$field['title'] = __( 'Caption Title', 'foogallery' );
					unset( $field['desc'] );
					$field['default'] = 'caption';

					$field['choices'] = $captions_choices;
					$field['row_data'] = array(
						'data-foogallery-show-when-field-value'    => 'override',
						'data-foogallery-change-selector'          => 'input:radio',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					);
				} else if ( 'lightbox_caption_override_desc' === $field['id'] ) {
					$field['title'] = __( 'Caption Description', 'foogallery' );
					unset( $field['desc'] );
					$field['default'] = 'desc';
					$field['choices'] = $captions_choices;
					$field['row_data'] = array(
						'data-foogallery-show-when-field-value'    => 'override',
						'data-foogallery-change-selector'          => 'input:radio',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-value-selector'           => 'input:checked',
					);
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

			$old_layout = foogallery_gallery_template_setting( 'layout', false );
			if ( $old_layout !== false ) {
				//we are dealing with an older version of the slider PRO, so we need to set some defaults
				$defaultFitMedia = foogallery_gallery_template_setting( 'lightbox_fit_media', 'yes' ) === 'yes';
				$defaultPreserveButtonSpace = foogallery_gallery_template_setting( 'lightbox_buttons_display', 'yes' ) === 'no';
				$defaultHoverButtons = foogallery_gallery_template_setting( 'lightbox_hover_buttons', 'yes' ) === 'yes';
				$defaultInfoPosition = foogallery_gallery_template_setting( 'lightbox_info_position', 'top' );

				$options['template']['fitMedia'] = $defaultFitMedia;
				$options['template']['preserveButtonSpace'] = $defaultPreserveButtonSpace;
				$options['template']['hoverButtons'] = $defaultHoverButtons;
				$options['template']['info'] = $defaultInfoPosition;

				if ( 'fgs-horizontal' === $old_layout || 'horizontal' === $old_layout ) {
					$options['template']['thumbs'] = 'bottom';
				} else if ( '' === $old_layout || 'fgs-vertical' === $old_layout || 'vertical' === $old_layout ) {
					$options['template']['thumbs'] = 'right';
				}
			}

			$old_thumbnail_captions = foogallery_gallery_template_setting( 'thumbnail_captions', false );
			if ( 'fgs-no-captions' === $old_thumbnail_captions || 'none' === $old_thumbnail_captions ) {
				$options['template']['thumbsCaptions'] = false;
			} else if ( '' === $old_thumbnail_captions || 'show' === $old_thumbnail_captions) {
				$options['template']['thumbsCaptions'] = true;
			}

			$old_highlight = foogallery_gallery_template_setting( 'highlight', false );
			if ( 'fgs-purple' === $old_highlight ) {
				$options['template']['button'] = 'fg-button-purple';
			} else if ( 'fgs-blue' === $old_highlight ) {
				$options['template']['button'] = 'fg-button-blue';
			} else if ( 'fgs-green' === $old_highlight ) {
				$options['template']['button'] = 'fg-button-green';
			} else if ( 'fgs-orange' === $old_highlight ) {
				$options['template']['button'] = 'fg-button-orange';
			} else if ( 'fgs-red' === $old_highlight ) {
				$options['template']['button'] = 'fg-button-red';
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
			$args['thumbnail_dimensions'] = array(
				'width' => 150,
				'height' => 150,
				'crop' => true
			);
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
			return array(
				'width' => 150,
				'height' => 150,
				'crop' => true
			);
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
			return array(
				'width' => 150,
				'height' => 150,
				'crop' => true
			);
		}

		/**
		 * Build up the arguments needed for rendering this gallery template
		 *
		 * @param $args
		 * @return array
		 */
		function build_gallery_template_arguments( $args ) {
			$args = array(
				'width' => 150,
				'height' => 150,
				'crop' => true
			);

			return $args;
		}

		/**
		 * Adjust classes for the slider
		 *
		 * Removes certain classes from the container
		 * Adds an aspect ratio setting class
		 *
		 *
		 * @param $classes
		 * @param $gallery
		 *
		 * @return array
		 */
		function adjust_classes( $classes, $gallery ) {
			if ( 'slider' === $gallery->gallery_template ) {

				//remove unneeded classes
				if ( ( $key = array_search( 'slider', $classes ) ) !== false ) {
					unset( $classes[$key] );
				}
				if ( ( $key = array_search( 'fg-border-thin', $classes ) ) !== false ) {
					unset( $classes[$key] );
				}
				if ( ( $key = array_search( 'fg-loaded-fade-in', $classes ) ) !== false ) {
					unset( $classes[$key] );
				}
				if ( ( $key = array_search( 'video-icon-default', $classes ) ) !== false ) {
					unset( $classes[$key] );
				}
				if ( ( $key = array_search( 'fgs-no-captions', $classes ) ) !== false ) {
					//only remove the caption hover class if no captions is enabled
					if ( ( $key = array_search( 'fg-caption-hover', $classes ) ) !== false ) {
						unset( $classes[$key] );
					}
				}

				//add a class for aspect ratio
				$aspect_ratio = foogallery_gallery_template_setting( 'aspect-ratio', '' );

				if ( $aspect_ratio !== '' ) {
					$classes[] = $aspect_ratio;
				}
			}

			return $classes;
		}
	}
}