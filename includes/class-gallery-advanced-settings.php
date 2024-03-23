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
			add_filter( 'foogallery_build_container_attributes', array( $this, 'add_container_attributes' ), 10, 3 );

			//add custom class to container
			add_filter( 'foogallery_build_class_attribute', array( $this, 'add_custom_class' ), 10, 2 );

			//remove the title attribute from the image
			add_filter('foogallery_attachment_html_image_attributes', array($this, 'remove_title_attribute'), 99, 3);
		}

		/**
		 * @param array $attr
		 * @param array $args
		 * @param FooGalleryAttachment $attachment
		 * @return mixed
		 */
		function remove_title_attribute($attr, $args, $attachment) {
			//make sure we use a cached value
			if ( !foogallery_current_gallery_has_cached_value( 'include_title') ) {
				foogallery_current_gallery_set_cached_value( 'include_title', foogallery_gallery_template_setting( 'include_title', '' ) );
			}

			if ( 'disabled' === foogallery_current_gallery_get_cached_value( 'include_title' ) ) {
				if ( array_key_exists( 'title', $attr ) ) {
					unset( $attr['title'] );
				}
			}

			return $attr;
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

            $custom_attribute_desc = __( 'Even though the Custom Attributes setting is useful in some scenarios, due to numerous security concerns, we have decided to disable it. It will be completely removed in a future update. We are keeping it for now, to make it easier to migrate to the newer and safer Custom Attribute Key and Value settings below.', 'foogallery' );
            $custom_attribute_desc_link = '<a href="https://fooplugins.com/support" target="_blank">' . __( 'contact us', 'foogallery' ) . '</a>';
            $custom_attribute_desc .= '</br>' . sprintf( __( 'Please %s for any questions or help.', 'foogallery' ), $custom_attribute_desc_link );

            $fields[] = array(
                'id'      => 'custom_attributes_help',
                'title'   => __( 'Custom Attributes Setting No Longer Works!', 'foogallery' ),
                'desc'    => $custom_attribute_desc,
                'section'  => __( 'Advanced', 'foogallery' ),
                'type'    => 'help'
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
                'id'       => 'custom_attribute_key',
                'title'    => __( 'Custom Attribute Key', 'foogallery' ),
                'desc'     => __( 'Used in combination with "Custom Attribute Value" to add a custom attribute to the gallery container. To be used by developers only!', 'foogallery' ),
                'section'  => __( 'Advanced', 'foogallery' ),
                'type'     => 'text',
                'default'  => '',
            );

            $fields[] = array(
                'id'       => 'custom_attribute_value',
                'title'    => __( 'Custom Attribute Value', 'foogallery' ),
                'desc'     => __( 'Used in combination with "Custom Attribute Key" to add a custom attribute to the gallery container. To be used by developers only!', 'foogallery' ),
                'section'  => __( 'Advanced', 'foogallery' ),
                'type'     => 'text',
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

			$fields[] = array(
				'id'      => 'include_title',
				'title'   => __( 'Image Title Attribute', 'foogallery' ),
				'desc'    => __( 'You can choose to include a title attribute on the thumbnail image or not.', 'foogallery' ),
				'section' => __( 'Advanced', 'foogallery' ),
				'type'     => 'radio',
				'spacer'   => '<span class="spacer"></span>',
				'default'  => '',
				'choices'  => array(
					'' => __( 'Enabled', 'foogallery' ),
					'disabled' => __( 'Disabled', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'shortcode'
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
		 * Adds a custom attribute to the gallery container attributes
		 *
		 * @param $attributes
		 * @param $gallery
		 *
		 * @return mixed
		 */
		function add_container_attributes( $attributes, $gallery ) {
			global $current_foogallery;

			if ( $current_foogallery === $gallery ) {
                $custom_attribute_key = sanitize_title( foogallery_gallery_template_setting( 'custom_attribute_key', '' ) );
                $custom_attribute_value = sanitize_html_class( foogallery_gallery_template_setting( 'custom_attribute_value', '' ) );

                if ( !empty( $custom_attribute_key ) && !empty( $custom_attribute_value ) ) {

                    //do further cleaning!
                    $custom_attribute_key = foogallery_sanitize_javascript( $custom_attribute_key );
                    $custom_attribute_value = foogallery_sanitize_javascript( $custom_attribute_value );

                    if ( !empty( $custom_attribute_key ) && !empty( $custom_attribute_value ) ) {
                        $attributes[$custom_attribute_key] = $custom_attribute_value;
                    }
                }
            }

			return $attributes;
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
                $custom_class = sanitize_title( foogallery_gallery_template_setting( 'custom_class', '' ) );

				if ( !empty( $custom_class ) ) {
					$classes[] = $custom_class;
				}
			}

			return $classes;
		}
	}
}