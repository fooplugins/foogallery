<?php
/**
 * Class for adding advanced settings to all gallery templates
 */
if ( ! class_exists( 'FooGallery_Advanced_Gallery_Settings' ) ) {

	class FooGallery_Advanced_Gallery_Settings {

		function __construct() {
			//add fields to all templates
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_advanced_fields' ), 20, 2 );

			//add data options
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_data_options' ), 30, 3 );

			//add custom attributes
			add_filter( 'foogallery_build_container_attributes_html', array( $this, 'add_container_attributes' ), 10, 3 );

			//add custom class to container
			add_filter( 'foogallery_build_class_attribute', array( $this, 'add_custom_class' ), 10, 2 );
		}

		/**
		 * Add fields to the gallery template
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_advanced_fields( $fields, $template ) {
			$fields[] = array(
				'id'       => 'custom_settings',
				'title'    => __( 'Custom Settings', 'foogallery' ),
				'desc'     => __( 'Add any custom settings to the gallery which will be merged with existing settings. To be used by developers only!', 'foogallery' ),
				'section'  => __( 'Advanced', 'foogallery' ),
				'type'     => 'textarea',
				'default'  => '',
			);

			$fields[] = array(
				'id'       => 'custom_attributes',
				'title'    => __( 'Custom Attributes', 'foogallery' ),
				'desc'     => __( 'Add any custom attributes to the gallery container. To be used by developers only!', 'foogallery' ),
				'section'  => __( 'Advanced', 'foogallery' ),
				'type'     => 'textarea',
				'default'  => '',
			);

			$fields[] = array(
				'id'       => 'custom_class',
				'title'    => __( 'Custom Gallery Class', 'foogallery' ),
				'desc'     => __( 'Add a custom class to the gallery container.', 'foogallery' ),
				'section'  => __( 'Advanced', 'foogallery' ),
				'type'     => 'text',
				'default'  => '',
			);

			return $fields;
		}

		/**
		 * Add the required data options
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {
			$custom_settings = foogallery_gallery_template_setting( 'custom_settings', '' );

			if ( !empty( $custom_settings ) ) {
				$settings_array = @json_decode($custom_settings, true);

				if ( isset( $settings_array ) ) {
					$options = array_replace_recursive( $options, $settings_array );
				}
			}

			return $options;
		}

		/**
		 * Adds any custom attributes added to the gallery container attributes html
		 *
		 * @param $html
		 * @param $attributes
		 * @param $gallery
		 *
		 * @return mixed
		 */
		function add_container_attributes( $html, $attributes, $gallery ) {
			global $current_foogallery;

			if ( $current_foogallery === $gallery ) {
				$custom_attributes = foogallery_gallery_template_setting( 'custom_attributes', '' );

				if ( !empty( $custom_attributes ) ) {
					$html .= ' ' . $custom_attributes;
				}
			}

			return $html;
		}


		/**
		 * Add the custom class to the array of classes
		 *
		 * @param $classes
		 * @param $gallery
		 *
		 * @return array
		 */
		function add_custom_class( $classes, $gallery ) {
			global $current_foogallery;

			if ( $current_foogallery === $gallery ) {
				$custom_class = foogallery_gallery_template_setting( 'custom_class', '' );

				if ( !empty( $custom_class ) ) {
					$classes[] = $custom_class;
				}
			}

			return $classes;
		}
	}
}