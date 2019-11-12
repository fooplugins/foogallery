<?php
/**
 * FooGallery Pro Lightbox class
 */
if ( ! class_exists( 'FooGallery_Pro_Lightbox' ) ) {

	class FooGallery_Pro_Lightbox {

		function __construct() {
			//add lightbox custom fields
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'lightbox_custom_fields' ), 10, 2 );

			//add the data options needed for lightbox
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_data_options' ), 10, 3 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments', array( $this, 'preview_arguments' ), 10, 3 );

			//set the settings icon for lightbox
			add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

			//add the FooGallery lightbox option
			add_filter( 'foogallery_gallery_template_field_lightboxes', array($this, 'add_lightbox') );

			//alter the default lightbox to be FooGallery PRO Lightbox
			add_filter( 'foogallery_alter_gallery_template_field', array( $this, 'make_foogallery_default_lightbox' ), 99, 2 );

			//add specific lightbox data attribute to the container div
			add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lightbox_data_attributes' ), 10, 2 );
		}

		/**
		 * Add fields to all galleries.
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return mixed
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 */
		public function lightbox_custom_fields( $fields, $template ) {
			$use_lightbox = true;
			if ( $template && array_key_exists( 'panel_support', $template ) && true === $template['panel_support'] ) {
				$use_lightbox = false;
			}

			$section = $use_lightbox ? __( 'Lightbox', 'foogallery' ) : __( 'Panel', 'foogallery' );

			$field[] = array(
				'id'      => 'lightbox_theme',
				'title'   => __( 'Theme', 'foogallery' ),
				'desc'    => __( 'The overall appearance including background and button color.', 'foogallery' ),
				'section' => $section,
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_theme_choices', array(
					''  => __( 'Inherit', 'foogallery' ),
					'fg-light'  => __( 'Light', 'foogallery' ),
					'fg-dark'   => __( 'Dark', 'foogallery' )
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_button_theme',
				'title'   => __( 'Button Color', 'foogallery' ),
				'desc'    => __( 'You can override the button color.', 'foogallery' ),
				'section' => $section,
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_button_theme_choices', array(
					''  => __( 'Default', 'foogallery' ),
					'fg-button-light'  => __( 'Light', 'foogallery' ),
					'fg-button-dark'   => __( 'Dark', 'foogallery' ),
					'fg-button-blue'  => __( 'Blue', 'foogallery' ),
					'fg-button-purple'   => __( 'Purple', 'foogallery' ),
					'fg-button-green'   => __( 'Green', 'foogallery' ),
					'fg-button-red'   => __( 'Red', 'foogallery' ),
					'fg-button-orange'   => __( 'Orange', 'foogallery' )
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_button_highlight',
				'title'   => __( 'Button Hover Color', 'foogallery' ),
				'desc'    => __( 'You can override the button hover color.', 'foogallery' ),
				'section' => $section,
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_button_highlight_choices', array(
					''  => __( 'Default', 'foogallery' ),
					'fg-highlight-light'  => __( 'Light', 'foogallery' ),
					'fg-highlight-dark'   => __( 'Dark', 'foogallery' ),
					'fg-highlight-blue'  => __( 'Blue', 'foogallery' ),
					'fg-highlight-purple'   => __( 'Purple', 'foogallery' ),
					'fg-highlight-green'   => __( 'Green', 'foogallery' ),
					'fg-highlight-red'   => __( 'Red', 'foogallery' ),
					'fg-highlight-orange'   => __( 'Orange', 'foogallery' )
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_info',
				'title'   => __( 'Show Caption', 'foogallery' ),
				'desc'    => __( 'You can show or hide captions within the lightbox.', 'foogallery' ),
				'section' => $section,
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'yes',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_info_choices', array(
					'yes'  => __( 'Visible', 'foogallery' ),
					'no'  => __( 'Hidden', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_info_position',
				'title'   => __( 'Caption Position', 'foogallery' ),
				'desc'    => __( 'The position of the captions within the lightbox.', 'foogallery' ),
				'section' => $section,
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'bottom',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_info_choices', array(
					'bottom' => __( 'Bottom', 'foogallery' ),
					'top'    => __( 'Top', 'foogallery' ),
					'left'   => __( 'Left', 'foogallery' ),
					'right'  => __( 'Right', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_info',
					'data-foogallery-show-when-field-operator' => 'indexOf',
					'data-foogallery-show-when-field-value'    => 'yes',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_info_overlay',
				'title'   => __( 'Caption Overlay', 'foogallery' ),
				'desc'    => __( 'Whether or not the caption is overlaid on top of the content.', 'foogallery' ),
				'section' => $section,
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'yes',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_info_choices', array(
					'yes' => __( 'Overlaid', 'foogallery' ),
					'no'  => __( 'Inline', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_info',
					'data-foogallery-show-when-field-operator' => 'indexOf',
					'data-foogallery-show-when-field-value'    => 'yes',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			if ( $use_lightbox ) {
				$field[] = array(
					'id'       => 'lightbox_thumbs',
					'title'    => __( 'Show Thumbs', 'foogallery' ),
					'desc'     => __( 'You can enable or disable thumbnails within the lightbox.', 'foogallery' ),
					'section'  => $section,
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => 'enabled',
					'choices'  => apply_filters( 'foogallery_gallery_template_lightbox_thumbs_choices', array(
						'enabled'  => __( 'Enabled', 'foogallery' ),
						'disabled' => __( 'Disabled', 'foogallery' )
					) ),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					)
				);
			}

			//find the index of the first Hover Effect field
			$index = $this->find_index_of_section( $fields, __( 'Hover Effects', 'foogallery' ) );

			array_splice( $fields, $index, 0, $field );

			return $fields;
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
			$template = foogallery_get_gallery_template( $gallery->gallery_template );
			if ( $template && array_key_exists( 'panel_support', $template ) && true === $template['panel_support'] ) {

				$options['template'] = $this->get_options_from_settings();

			}

			return $options;
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data, $template ) {
			if ( array_key_exists( $template . '_lightbox_theme', $post_data[FOOGALLERY_META_SETTINGS] ) ) {
				$args['lightbox_theme'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_lightbox_theme'];
			}

			if ( array_key_exists( $template . '_lightbox_button_theme', $post_data[FOOGALLERY_META_SETTINGS] ) ) {
				$args['lightbox_button_theme'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_lightbox_button_theme'];
			}

			if ( array_key_exists( $template . '_lightbox_button_highlight', $post_data[FOOGALLERY_META_SETTINGS] ) ) {
				$args['lightbox_button_highlight'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_lightbox_button_highlight'];
			}

			if ( array_key_exists( $template . '_lightbox_thumbs', $post_data[FOOGALLERY_META_SETTINGS] ) ) {
				$args['lightbox_thumbs'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_lightbox_thumbs'];
			}

			return $args;
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_section_icons( $section_slug ) {
			if ( 'lightbox' === $section_slug || 'panel' === $section_slug ) {
				return 'dashicons-grid-view';
			}

			return $section_slug;
		}

		/**
		 * Add the FooGallery PRO lightbox
		 * @param $lightboxes
		 *
		 * @return mixed
		 */
		function add_lightbox($lightboxes) {
			$lightboxes['foogallery'] = __( 'FooGallery PRO Lightbox', 'foogallery' );
			return $lightboxes;
		}

		/**
		 * Change the default for lightbox
		 *
		 * @param $field
		 * @param $gallery_template
		 * @return mixed
		 */
		function make_foogallery_default_lightbox( $field, $gallery_template ) {
			if (isset($field['lightbox']) && true === $field['lightbox']) {
				$field['default'] = 'foogallery';
			}

			return $field;
		}

		/**
		 * Append the needed data attributes to the container div for the lightbox settings
		 *
		 * @param $attributes
		 * @param $gallery
		 *
		 * @return array
		 */
		function add_lightbox_data_attributes( $attributes, $gallery ) {
			$template = foogallery_get_gallery_template( $gallery->gallery_template );
			if ( $template && !array_key_exists( 'panel_support', $template ) ) {

				$options = $this->get_options_from_settings();

				if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
					$encoded_options = json_encode( $options, JSON_UNESCAPED_UNICODE );
				} else {
					$encoded_options = json_encode( $options );
				}

				$attributes['data-foogallery-lightbox'] = $encoded_options;
			}

			return $attributes;
		}

		/**
		 * return the options for the lightbox/panel
		 *
		 * @return array
		 */
		private function get_options_from_settings() {
			$options = array();

			$theme = foogallery_gallery_template_setting( 'lightbox_theme', '' );
			if ( !empty( $theme ) ) {
				$options['theme'] = $theme;
			}

			$button_theme = foogallery_gallery_template_setting( 'lightbox_button_theme', '' );
			if ( !empty( $button_theme ) ) {
				$options['button'] = $button_theme;
			}

			$button_highlight = foogallery_gallery_template_setting( 'lightbox_button_highlight', '' );
			if ( !empty( $button_highlight ) ) {
				$options['highlight'] = $button_highlight;
			}

			$show_thumbs = foogallery_gallery_template_setting( 'lightbox_thumbs', 'enabled' ) === 'enabled';
			$options['thumbsVisible'] = $show_thumbs;
			$options['thumbs'] = 'bottom';
			//$options['thumbsSmall'] = true;
			$options['thumbsCaptions'] = false;

			$show_captions = foogallery_gallery_template_setting( 'lightbox_info', 'yes' ) === 'yes';


			return $options;
		}
	}
}