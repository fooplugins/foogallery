<?php

if ( !class_exists( 'FooGallery_Product_Gallery_Template' ) ) {

	class FooGallery_Product_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 101, 1 );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-product', array( $this, 'add_common_thumbnail_fields' ), 10, 2 );

			//add the data options needed for polaroid
			add_filter( 'foogallery_build_container_data_options-product', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-product', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-product', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //build up the thumb dimensions on save
            add_filter( 'foogallery_template_thumbnail_dimensions-product', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-product', array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'append_classes' ), 10, 2 );
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
		 * Add our gallery template to the list of templates available for every gallery
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
                'slug'        => 'product',
                'name'        => __( 'Product Gallery', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-simple_portfolio fg-caption-always',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'enqueue_core' => true,
                'fields'	  => array(
	                array(
		                'id'	  => 'help',
		                'title'	  => __( 'Tip', 'foogallery' ),
		                'section' => __( 'General', 'foogallery' ),
		                'type'	  => 'html',
		                'help'	  => true,
		                'desc'	  => __( 'The Product Gallery template works best with the WooCommerce Products datasource. It is the same as the Simple Portfolio template, but with different defaults to make your life easier.', 'foogallery' ),
	                ),
                    array(
                        'id'      => 'thumbnail_dimensions',
                        'title'   => __( 'Thumbnail Size', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => 250,
                            'height' => 250,
                            'crop' => true,
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumbnail Link', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'image',
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' ),
                    ),
                    array(
                        'id'      => 'lightbox',
                        'title'   => __( 'Lightbox', 'foogallery' ),
                        'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'lightbox',
                    ),
                    array(
                        'id'      => 'gutter',
                        'title'   => __( 'Gutter', 'foogallery' ),
                        'desc'    => __( 'The spacing between each thumbnail in the gallery.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 40,
                        'step'    => '1',
                        'min'     => '0',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						)
                    ),
                    array(
                        'id'      => 'align',
                        'title'   => __( 'Alignment', 'foogallery' ),
                        'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
                        'spacer'  => '<span class="spacer"></span>',
                        'default' => 'center',
                        'choices' => array(
                            'left' => __( 'Left', 'foogallery' ),
                            'center' => __( 'Center', 'foogallery' ),
                            'right' => __( 'Right', 'foogallery' ),
                        ),
                        'row_data'=> array(
                            'data-foogallery-change-selector' => 'input:radio',
                            'data-foogallery-value-selector' => 'input:checked',
                            'data-foogallery-preview' => 'shortcode',
                        )
                    )
                ),
			);

			return $gallery_templates;
		}

		/**
		 * Add thumbnail fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_common_thumbnail_fields( $fields, $template ) {
			$field_ids_to_remove = array(
				'captions_help',
				'hover_effect_help',
				'hover_effect_scale',
				'hover_effect_preset',
				'hover_effect_caption_visibility'
			);

			$fields_to_remove = array();

			//update specific fields
			foreach ($fields as $key => &$field) {
				if ( in_array( $field['id'], $field_ids_to_remove ) ) {
					$fields_to_remove[] = $key;
				}

				if ( 'hover_effect_type' === $field['id'] ) {
					unset( $field['choices']['preset'] );
				} else if ( 'hover_effect_caption_visibility' === $field['id'] ) {
					$field['default'] = 'fg-caption-always';
					$field['choices'] = array(
						'fg-caption-always' => __( 'Always Visible', 'foogallery' ),
					);
//					$field['row_data'] = array(
//						'data-foogallery-change-selector' => 'input:radio',
//						'data-foogallery-hidden' => true,
//						'data-foogallery-preview' => 'shortcode'
//					);
				} else if ( 'border_size' === $field['id'] ) {
					$field['default'] = 'fg-border-medium';
				} else if ( 'rounded_corners' === $field['id'] ) {
					$field['default'] = 'fg-round-medium';
				} else if ( 'loaded_effect' === $field['id'] ) {
					$field['default'] = 'fg-loaded-flip';
				} else if ( 'caption_invert_color' === $field['id'] ) {
					$field['default'] = 'fg-light-overlays';
				} else if ( 'hover_effect_icon' === $field['id'] ) {
					$field['default'] = 'fg-hover-cart';
				} else if ( 'caption_alignment' === $field['id'] ) {
					$field['default'] = 'fg-c-c';
				} else if ( 'filtering_type' === $field['id'] ) {
					$field['default'] = 'simple';
				} else if ( 'filtering_taxonomy' === $field['id'] ) {
					$field['default'] = FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY;
				} else if ( 'ecommerce_button_add_to_cart' === $field['id'] ) {
					$field['default'] = 'shown';
				} else if ( 'ecommerce_button_variable' === $field['id'] ) {
					$field['default'] = 'shown';
				} else if ( 'ecommerce_lightbox_product_information' === $field['id'] ) {
					$field['default'] = 'right';
				} else if ( 'hover_effect_scale' == $field['id'] ) {
					$field['default'] = 'fg-hover-zoomed';
				}
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
			$gutter = foogallery_gallery_template_setting( 'gutter', 40 );
			$options['template']['gutter'] = intval($gutter);

            $align = foogallery_gallery_template_setting( 'align', 'center' );
            $options['template']['align'] = $align;
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
			$settings['product_hover_effect_preset'] = 'fg-custom';
			$settings['product_hover_effect_caption_visibility'] = 'fg-caption-always';

			return $settings;
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
            $dimensions = $foogallery->get_meta( 'product_thumbnail_dimensions', array(
                'width' => 250,
                'height' => 200
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
            $args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
            $args['image_attributes'] = array(
                'class'  => 'bf-img',
                'height' => $args['height']
            );
            $args['link_attributes'] = array( 'class' => 'foogallery-thumb' );

            return $args;
        }

		/**
		 * Adds the classes onto the container
		 *
		 * @param $classes
		 * @param $foogallery
		 *
		 * @return array
		 */
		function append_classes( $classes, $foogallery ) {

			$position = foogallery_gallery_template_setting( 'caption_position', '' );

			if ( $position !== '' ) {
				$classes[] = $position;
			}

			return $classes;
		}
	}
}