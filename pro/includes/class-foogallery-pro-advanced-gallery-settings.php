<?php
/**
 * Class for adding advanced settings to all gallery templates
 * Date: 11/09/2018
 */
namespace FooPlugins\FooGallery\Pro;

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
				'default'  => 'yes',
				'spacer'   => '<span class="spacer"></span>',
				'choices'  => array(
					'no'  => __( 'Disabled', 'foogallery' ),
					'yes'   => __( 'Enabled', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-preview'         => 'shortcode'
				)
			);

			$fields[] = array(
				'id'       => 'state_mask',
				'title'    => __( 'Deep Linking Mask', 'foogallery' ),
				'desc'     => __( 'Override the mask used in the URL for Deep Linking.', 'foogallery' ),
				'section'  => __( 'Advanced', 'foogallery' ),
				'type'     => 'text',
				'default'  => 'foogallery-{id}',
				'row_data' => array(
					'data-foogallery-change-selector'       => 'input',
					'data-foogallery-hidden'                => true,
					'data-foogallery-show-when-field'       => 'state',
					'data-foogallery-show-when-field-value' => 'yes',
					'data-foogallery-preview'               => 'shortcode'
				)
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

				$state_mask = foogallery_gallery_template_setting( 'state_mask', 'foogallery-{id}' );
				$options['state']['mask'] = $state_mask;
			}

			return $options;
		}
	}
}