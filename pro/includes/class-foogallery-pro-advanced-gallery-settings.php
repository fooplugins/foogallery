<?php
/**
 * Class for adding advanced settings to all gallery templates
 * Date: 11/09/2018
 */
if ( ! class_exists( 'FooGallery_Pro_Advanced_Gallery_Settings' ) ) {

	class FooGallery_Pro_Advanced_Gallery_Settings {

		function __construct() {
			//add fields to all templates
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_advanced_fields' ), 10, 2 );

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
				'id'       => 'state',
				'title'    => __( 'Deep Linking', 'foogallery' ),
				'desc'     => __( 'Enable Deep Linking for the gallery which allows the gallery to keep it\'s state for both paging and filtering.', 'foogallery' ),
				'section'  => __( 'Advanced', 'foogallery' ),
				'type'     => 'radio',
				'default'  => 'no',
				'spacer'   => '<span class="spacer"></span>',
				'choices'  => array(
					'no'  => __( 'Disabled', 'foogallery' ),
					'yes'   => __( 'Enabled', 'foogallery' ),
				),
			);

			$fields[] = array(
				'id'       => 'custom_settings',
				'title'    => __( 'Custom Settings', 'foogallery' ),
				'desc'     => __( 'Add any custom settings to the gallery which will be merged with existing settings.', 'foogallery' ),
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
			$enable_state = foogallery_gallery_template_setting( 'state', 'no' );

			if ( 'yes' === $enable_state ) {
				$options['state']['enabled'] = true;
			}

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