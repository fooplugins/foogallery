<?php
/**
 * FooGallery Pro Hover Presets Class
 */
if ( ! class_exists( 'FooGallery_Pro_Hover_Presets' ) ) {

	class FooGallery_Pro_Hover_Presets {

		function __construct() {
			add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_type_choices', array( $this, 'add_preset_type' ) );

			//make sure we can see the presets
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_preset_fields' ), 99, 2 );

			//build up class attributes
			add_filter( 'foogallery_build_class_attribute', array( $this, 'add_presets_class_attributes' ), 20, 2 );

            //remove preset choices from simple portfolio
            add_filter( 'foogallery_override_gallery_template_fields-simple_portfolio', array( $this, 'remove_preset_choices_for_simple_portfolio' ), 10, 2 );
        }

		/**
		 * Adds the preset type for hover effect type
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_preset_type( $choices ) {
			$new_choices = array();

			$choices_before = array_slice( $choices, 0, 1 );
			$choices_after = array_slice( $choices, 1 );

			$new_choices['preset'] = __( 'Preset',   'foogallery' );

			return $choices_before + $new_choices + $choices_after;
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
		 * Return the index of the requested field
		 *
		 * @param $fields
		 * @param $field_id
		 *
		 * @return int
		 */
		private function find_index_of_field( $fields, $field_id ) {
			$index = 0;
			foreach ( $fields as $field ) {
				if ( isset( $field['id'] ) && $field_id === $field['id'] ) {
					return $index;
				}
				$index++;
			}
			return $index;
		}

		/**
		 * Add the fields for presets
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_preset_fields( $fields, $template ) {
			$index_of_hover_effect_preset_field = $this->find_index_of_field( $fields, 'hover_effect_preset' );

			$new_fields[] = array(
				'id'      => 'hover_effect_help',
				'title'   => __( 'Hover Effect Help', 'foogallery' ),
				'desc'    => __( 'A preset provides a stylish, pre-defined look &amp; feel for the effect when you hover over the thumbnails.', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'type'    => 'help',
				'row_data' => array(
					'data-foogallery-hidden'                => true,
					'data-foogallery-show-when-field'       => 'hover_effect_type',
					'data-foogallery-show-when-field-value' => 'preset',
				)
			);

			$new_fields[] = array(
				'id'       => 'hover_effect_preset',
				'title'    => __( 'Preset', 'foogallery' ),
				'section'  => __( 'Hover Effects', 'foogallery' ),
				'default'  => 'fg-preset fg-sadie',
				'type'     => 'radio',
				'choices'  => apply_filters(
					'foogallery_gallery_template_common_thumbnail_fields_hover_effect_preset_choices', array(
						'fg-preset fg-sadie'   => __( 'Sadie',   'foogallery' ),
						'fg-preset fg-layla'   => __( 'Layla',   'foogallery' ),
						'fg-preset fg-oscar'   => __( 'Oscar',   'foogallery' ),
						'fg-preset fg-sarah'   => __( 'Sarah',   'foogallery' ),
						'fg-preset fg-goliath' => __( 'Goliath', 'foogallery' ),
						'fg-preset fg-jazz'    => __( 'Jazz',    'foogallery' ),
						'fg-preset fg-lily'    => __( 'Lily',    'foogallery' ),
						'fg-preset fg-ming'    => __( 'Ming',    'foogallery' ),
						'fg-preset fg-selena'  => __( 'Selena',  'foogallery' ),
						'fg-preset fg-steve'   => __( 'Steve',   'foogallery' ),
						'fg-preset fg-zoe'     => __( 'Zoe',     'foogallery' ),
					)
				),
				'spacer'   => '<span class="spacer"></span>',
				'desc'     => __( 'A preset styling that is used for the hover effect.', 'foogallery' ),
				'row_data' => array(
					'data-foogallery-change-selector'       => 'input:radio',
					'data-foogallery-value-selector'        => 'input:checked',
					'data-foogallery-preview'               => 'shortcode',
					'data-foogallery-hidden'                => true,
					'data-foogallery-show-when-field'       => 'hover_effect_type',
					'data-foogallery-show-when-field-value' => 'preset',
				)
			);

			$new_fields[] = array(
				'id'       => 'hover_effect_preset_size',
				'title'    => __( 'Preset Size', 'foogallery' ),
				'section'  => __( 'Hover Effects', 'foogallery' ),
				'default'  => 'fg-preset-small',
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'choices'  => apply_filters(
					'foogallery_gallery_template_common_thumbnail_fields_hover_effect_preset_size_choices', array(
						'fg-preset-small'  => __( 'Small', 'foogallery' ),
						'fg-preset-medium' => __( 'Medium', 'foogallery' ),
						'fg-preset-large'  => __( 'Large', 'foogallery' ),
					)
				),
				'desc'     => __( 'Choose an appropriate size for the preset hover effects, based on the size of your thumbs. Choose small for thumbs 150-200 wide, medium for thumbs 200-400 wide, and large for thumbs over 400 wide.', 'foogallery' ),
				'row_data' => array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'hover_effect_type',
					'data-foogallery-show-when-field-value'    => 'preset',
					'data-foogallery-preview'                  => 'shortcode'
				)
			);

			array_splice( $fields, $index_of_hover_effect_preset_field, 0, $new_fields );

			return $fields;
		}

		/**
		 * Build up the gallery class attribute for the common fields
		 *
		 * @param $classes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function add_presets_class_attributes( $classes, $gallery ) {

			$template_data = foogallery_get_gallery_template( $gallery->gallery_template );

			//check the template supports common fields
			if ( $template_data && array_key_exists( 'common_fields_support', $template_data ) && true === $template_data['common_fields_support'] ) {
				$hover_effect_type = $this->get_setting_from_gallery( $gallery,'hover_effect_type', '' );

				if ( 'preset' === $hover_effect_type ) {
					$classes[] = $this->get_setting_from_gallery( $gallery,'hover_effect_preset', 'fg-custom' );;
					$classes[] = $this->get_setting_from_gallery( $gallery, 'hover_effect_preset_size', 'fg-preset-small' );
				}
			}

			return $classes;
		}

		/**
		 * Get the setting from the gallery
		 *
		 * @param $gallery
		 * @param $key
		 * @param $default
		 *
		 * @return bool
		 */
		function get_setting_from_gallery( $gallery, $key, $default ) {
			global $current_foogallery;

			if ( isset( $current_foogallery ) && $current_foogallery->ID === $gallery->ID ) {
				return foogallery_gallery_template_setting( $key, $default );
			}

			return $gallery->get_setting( $key, $default );
		}

        /**
         * Remove the preset choices from the simple portfolio template
         *
         * @uses "foogallery_override_gallery_template_fields"
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function remove_preset_choices_for_simple_portfolio( $fields, $template ) {
            foreach ($fields as &$field) {
                if ( 'hover_effect_type' === $field['id'] ) {
                    $new_choices = $field['choices'];
                    foreach ($field['choices'] as $choice => $choice_name) {
                        if ( 'preset' === $choice ) {
                            unset( $new_choices[$choice] );
                        }
                    }
                    $field['choices'] = $new_choices;
                }
            }

            return $fields;
        }
	}
}