<?php

if ( ! class_exists( 'FooGallery_Swiper_Template' ) ) {

	class FooGallery_Swiper_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 101 );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			// Set the settings icon for lightbox
			add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

			// Change fields for the template
			add_filter( 'foogallery_override_gallery_template_fields-swiper',
				array( $this, 'change_common_thumbnail_fields' ), 10, 2 );
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_section_icons( $section_slug ) {
			if ( 'swiper' === $section_slug ) {
				return 'dashicons-align-left';
			}

			return $section_slug;
		}

		/**
		 * Add the video gallery template to the list of templates available
		 *
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {

			$gallery_templates[] = array(
				'slug'                  => 'swiper',
				'name'                  => __( 'Swiper PRO', 'foogallery' ),
				'preview_support'       => true,
				'common_fields_support' => true,
				'lazyload_support'      => false,
				'paging_support'        => false,
				'thumbnail_dimensions'  => false,
				'filtering_support'     => true,
				'mandatory_classes'     => 'fg-swiper',
				'embed_support'         => true,
				'panel_support'         => true,
				'enqueue_core'          => true,
				'fields'                => array(
					array(
						'id'         => 'aspect-ratio',
						'section'    => __( 'Swiper', 'foogallery' ),
						'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
						'title'      => __( 'Aspect Ratio', 'foogallery' ),
						'desc'       => __( 'Select the aspect ratio the slider will use, to best suit your content.',
							'foogallery' ),
						'default'    => 'fg-16-9',
						'type'       => 'radio',
						'spacer'     => '<span class="spacer"></span>',
						'choices'    => array(
							'fg-16-9'  => __( '16:9', 'foogallery' ),
							'fg-16-10' => __( '16:10', 'foogallery' ),
							'fg-4-3'   => __( '4:3', 'foogallery' ),
						),
						'row_data'   => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'         => 'swiper_help',
						'title'      => __( 'Help', 'foogallery' ),
						'desc'       => __( 'You can change the layout of the slider by changing the position of the thumbnails.', 'foogallery' ),
						'section'    => __( 'Swiper', 'foogallery' ),
						'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
						'type'       => 'help',
					),
				)
			);

			return $gallery_templates;
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 *
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
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 * @uses "foogallery_override_gallery_template_fields"
		 */
		function change_common_thumbnail_fields( $fields, $template ) {

			$fields_to_remove   = array();
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
			$fields_to_remove[] = 'caption_alignment';
			$fields_to_remove[] = 'lightbox_caption_override';
			$fields_to_remove[] = 'aspect-ratio';
			$fields_to_remove[] = 'lightbox_auto_progress_start';
			$fields_to_remove[] = 'lightbox_mobile_layout';
			$fields_to_remove[] = 'lightbox_button_theme';
			$fields_to_remove[] = 'lightbox_button_highlight';
			$fields_to_remove[] = 'lightbox_buttons_display';
			$fields_to_remove[] = 'lightbox_hover_buttons';
			$fields_to_remove[] = 'lightbox_show_fullscreen_button';
			$fields_to_remove[] = 'lightbox_thumbs_captions';
			$fields_to_remove[] = 'lightbox_thumbs_captions_alignment';
			$fields_to_remove[] = 'lightbox_thumbs_bestfit';
			$fields_to_remove[] = 'lightbox_thumbs_size';

			$indexes_to_remove = array();

			$settings_link    = sprintf( '<a target="blank" href="%s">%s</a>', foogallery_admin_settings_url(), __( 'settings', 'foogallery' ) );
			$captions_choices = array(
				'none'    => __( 'None', 'foogallery' ),
				''        => sprintf( __( 'Default (as per %s)', 'foogallery' ), $settings_link ),
				'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
				'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
				'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
				'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' ),
			);

			$fields[] = [
				'id'         => 'slides_per_view',
				'title'      => __( 'Slides Per View', 'foogallery' ),
				'desc'       => __( 'Number of slides per view (slides visible at the same time on slider\'s container).', 'foogallery' ),
				'section'    => __( 'Swiper', 'foogallery' ),
				'subsection' => [
					'lightbox-general' => __( 'General', 'foogallery' )
				],
				'row_data'   => [
					'data-foogallery-hidden'                   => 1,
					'data-foogallery-show-when-field'          => 'effect',
					'data-foogallery-show-when-field-operator' => 'regex',
					'data-foogallery-show-when-field-value'    => 'default|coverflow',
					'data-foogallery-change-selector'          => 'input',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input'
				],
				'type'       => 'text',
				'default'    => 1,

			];

			$fields[] = [
				'id'         => 'space_between',
				'title'      => __( 'Space Between', 'foogallery' ),
				'desc'       => __( 'Distance between slides in px.', 'foogallery' ),
				'section'    => __( 'Swiper', 'foogallery' ),
				'subsection' => [
					'lightbox-general' => __( 'General', 'foogallery' )
				],
				'type'       => 'text',
				'default'    => 10,
				'row_data'   => [
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input'
				]
			];

			$fields[] = [
				'id'         => 'effect',
				'title'      => __( 'Animation Effect', 'foogallery' ),
				'desc'       => __( 'Animation on change slide', 'foogallery' ),
				'section'    => __( 'Swiper', 'foogallery' ),
				'subsection' => [
					'lightbox-general' => __( 'General', 'foogallery' )
				],
				'type'       => 'radio',
				'default'    => 'fade',
				'spacer'     => '<span class="spacer"></span>',
				'choices'    => [
					'default'   => __( 'Default', 'foogallery' ),
					'fade'      => __( 'Fade', 'foogallery' ),
					'coverflow' => __( 'Coverflow', 'foogallery' ),
					'flip'      => __( 'Flip', 'foogallery' ),
					'cube'      => __( 'Cube', 'foogallery' )
				],
                'row_data' => [
                    'data-foogallery-change-selector' => 'input',
                    'data-foogallery-preview' => 'shortcode',
                    'data-foogallery-value-selector' => 'input:checked',
                    "data-foogallery-hidden" => 1,
                    "data-foogallery-show-when-field" => "lightbox_transition",
                    "data-foogallery-show-when-field-operator" => "===",
                    "data-foogallery-show-when-field-value" => "horizontal",
                ]
			];

			$fields[] = [
				'id'         => 'height_container',
				'title'      => __( 'Swiper Height (vertical)', 'foogallery' ),
				'desc'       => __( 'Height size of container Swiper in px', 'foogallery' ),
				'section'    => __( 'Swiper', 'foogallery' ),
				'subsection' => [
					'lightbox-general' => __( 'General', 'foogallery' )
				],
				'type'       => 'text',
				'default'    => 400,
				'row_data'   => [
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input'
				]
			];

			$fields[] = [
				'id'         => 'height_image_container',
				'title'      => __( 'Image Height', 'foogallery' ),
				'desc'       => __( 'Height size images slides in px', 'foogallery' ),
				'section'    => __( 'Swiper', 'foogallery' ),
				'subsection' => [
					'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' )
				],
				'type'       => 'text',
				'default'    => 200,
				'row_data'   => [
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input'
				]
			];

			$fields[] = [
				'id'         => 'width_image_container',
				'title'      => __( 'Image Width', 'foogallery' ),
				'desc'       => __( 'Width size images slides in px', 'foogallery' ),
				'section'    => __( 'Swiper', 'foogallery' ),
				'subsection' => [
					'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' )
				],
				'type'       => 'text',
				'default'    => 400,
				'row_data'   => [
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input'
				]
			];

			foreach ( $fields as $key => &$field ) {
				if ( $field['section'] === __( 'Panel', 'foogallery' ) ) {
					$field['section'] = __( 'Swiper', 'foogallery' );
				}

				if ( isset( $field['subsection']['lightbox-captions'] ) ) {
					$fields_to_remove[] = $field['id'];
				}

				if ( 'lightbox_auto_progress' === $field['id'] ) {
					$field['subsection'] = [ 'lightbox-controls' => __( 'Controls', 'foogallery' ) ];
				} elseif ( 'lightbox_auto_progress_seconds' === $field['id'] ) {
					$field['subsection'] = [ 'lightbox-controls' => __( 'Controls', 'foogallery' ) ];
                    $field["row_data"] = [
                        "data-foogallery-hidden" => 1,
                        "data-foogallery-show-when-field" => "lightbox_auto_progress",
                        "data-foogallery-show-when-field-operator" => "===",
                        "data-foogallery-show-when-field-value" => "yes",
                        "data-foogallery-change-selector" => "input",
                        "data-foogallery-preview" => "shortcode",
                        "data-foogallery-value-selector" => "input"
                    ];
				} elseif ( 'hover_effect_preset' === $field['id'] ) {
					$field['default']  = 'fg-custom';
					$field['choices']  = array(
						'fg-custom' => __( 'Swiper', 'foogallery' )
					);
					$field['row_data'] = array(
						'data-foogallery-hidden'          => true,
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-value-selector'  => 'input:checked',
						'data-foogallery-preview'         => 'class'
					);
				} elseif ( 'lightbox_transition' === $field['id'] ) {
					$field['default'] = 'horizontal';
					$field['choices'] = array(
						'horizontal' => __( 'Horizontal', 'foogallery' ),
						'vertical'   => __( 'Vertical', 'foogallery' )
					);
				} elseif ( 'lightbox_show_maximize_button' === $field['id'] ) {
					$field['title']    = __( 'Unlimited Scroll', 'foogallery' );
					$field['desc']     = __( 'Loop slider for unlimited scroll after end', 'foogallery' );
					$field['default']  = 'yes';
					$field['choices']  = array(
						'yes' => __( 'Yes', 'foogallery' ),
						'no'  => __( 'No', 'foogallery' )
					);
					$field['row_data'] = [
						'data-foogallery-hidden'                   => 1,
						'data-foogallery-show-when-field'          => 'lightbox_auto_progress',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'no',
						"data-foogallery-change-selector"          => "input",
						"data-foogallery-preview"                  => "shortcode",
						"data-foogallery-value-selector"           => "input:checked"
					];
				} elseif ( 'lightbox_show_caption_button' === $field['id'] ) {
					$field['title']    = __( 'Grab Cursor', 'foogallery' );
					$field['desc']     = __( 'Show hand cursor on slides', 'foogallery' );
					$field['default']  = 'yes';
					$field['choices']  = array(
						'yes' => __( 'Yes', 'foogallery' ),
						'no'  => __( 'No', 'foogallery' ),
					);
					$field["row_data"] = [
						"data-foogallery-change-selector" => "input",
						"data-foogallery-preview"         => "shortcode",
						"data-foogallery-value-selector"  => "input:checked"
					];
				} elseif ( 'lightbox_show_thumbstrip_button' === $field['id'] ) {
					$field['title'] = __( 'Pagination', 'foogallery' );
					$field['desc']  = __( 'Show pagination dots', 'foogallery' );
				} else if ( 'video_autoplay' === $field['id'] ) {
					$field['title'] = __( 'Autoplay', 'foogallery' );
					$field['desc']  = __( 'Try to autoplay the video when selected. This will only work with videos hosted on Youtube or Vimeo.', 'foogallery' );
				} else if ( 'lightbox_buttons_display' === $field['id'] ) {
					$field['default'] = 'yes';
				} else if ( 'lightbox_thumbs' === $field['id'] ) {
					$field['title']   = __( 'Image Feet', 'foogallery' );
					$field['default'] = 'none';
					$field['desc']    = __( 'Change size image in box by fill the box or show default image sizing', 'foogallery' );
					$field['choices'] = array(
						'none'    => __( 'None', 'foogallery' ),
						'cover'   => __( 'Cover', 'foogallery' ),
						'contain' => __( 'Contain', 'foogallery' ),
						'fill'    => __( 'Fill', 'foogallery' )
					);
				} else if ( 'lightbox_thumbs_captions' === $field['id'] ) {
					$field['title']   = __( 'Thumbnail Captions', 'foogallery' );
					$field['default'] = 'yes';
				} else if ( 'lightbox_hover_buttons' === $field['id'] ) {
					$field['default'] = 'yes';
				} else if ( 'lightbox_caption_override_title' === $field['id'] ) {
					$field['title'] = __( 'Caption Title', 'foogallery' );
					unset( $field['desc'] );
					$field['default'] = 'caption';

					$field['choices']  = $captions_choices;
					$field['row_data'] = array(
						'data-foogallery-show-when-field-value' => 'override',
						'data-foogallery-change-selector'       => 'input:radio',
						'data-foogallery-preview'               => 'shortcode',
						'data-foogallery-value-selector'        => 'input:checked',
					);
				} else if ( 'lightbox_caption_override_desc' === $field['id'] ) {
					$field['title'] = __( 'Caption Description', 'foogallery' );
					unset( $field['desc'] );
					$field['default']  = 'desc';
					$field['choices']  = $captions_choices;
					$field['row_data'] = array(
						'data-foogallery-show-when-field-value' => 'override',
						'data-foogallery-change-selector'       => 'input:radio',
						'data-foogallery-preview'               => 'shortcode',
						'data-foogallery-value-selector'        => 'input:checked',
					);
				}

				if ( in_array( $field['id'], $fields_to_remove ) ) {
					$indexes_to_remove[] = $key;
				}
			}

			foreach ( $indexes_to_remove as $index ) {
				unset( $fields[ $index ] );
			}

			return $fields;
		}
	}
}