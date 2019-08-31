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
					$options = array_merge_recursive( $options, $settings_array );
				}
			}

			return $options;
		}
	}
}