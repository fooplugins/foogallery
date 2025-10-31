<?php

if ( !class_exists( 'FooGallery_Product_Gallery_Template' ) ) {

	class FooGallery_Product_Gallery_Template {

		const template_id = 'product';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 101, 1 );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			// Make adjustments to the template fields.
			add_filter( 'foogallery_override_gallery_template_fields-product', array( $this, 'adjust_fields' ), 10, 2 );
			add_filter( 'foogallery_override_gallery_template_fields_remove-product', array( $this, 'remove_fields' ), 10, 1 );
			add_filter( 'foogallery_override_gallery_template_fields_defaults-product', array( $this, 'field_defaults' ), 10, 1 );
			add_filter( 'foogallery_override_gallery_template_fields_hidden-product', array( $this, 'hidden_fields' ), 10, 1 );

			//add the data options needed for product gallery
			add_filter( 'foogallery_build_container_data_options-product', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-product', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-product', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //build up the thumb dimensions on save
            add_filter( 'foogallery_template_thumbnail_dimensions-product', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-product', array( $this, 'build_gallery_template_arguments' ) );

            // Add classes needed for the gallery template.
			add_filter( 'foogallery_build_class_attribute', array( $this, 'append_classes' ), 10, 2 );

			// Ensure we output the inline styling needed for a simple portfolio gallery template
			add_filter( 'foogallery_is_simple_portfolio_gallery_template', array( $this, 'is_simple_portfolio_gallery_template' ), 10, 2 );

			// add a style block for the gallery based on the field settings.
			add_action( 'foogallery_template_style_block-product', array( $this, 'add_css' ), 10, 2 );
        }

		/**
		 * Returns if this is a simple portfolio style gallery
		 *
		 * @param $return bool
		 * @param $gallery
		 *
		 * @return bool
		 */
		function is_simple_portfolio_gallery_template( $return, $gallery ) {
			if ( is_object( $gallery ) && is_a( $gallery, 'FooGallery' ) && self::template_id === $gallery->gallery_template ) {
				return true;
			}
			return $return;
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
			$gallery_templates[self::template_id] = array(
                'slug'        => self::template_id,
                'name'        => __( 'Product Gallery', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-simple_portfolio fg-caption-always',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'enqueue_core' => true,
				'icon' => '<svg viewBox="0 0 24 24">
        <!-- cart basket -->
        <path d="M6 7h13l-2 7H8z"/>
        
        <!-- handle flipped inward -->
        <path d="M6 7V4h-3"/>
        
        <!-- wheels -->
        <circle cx="9" cy="18" r="1.5"/>
        <circle cx="16" cy="18" r="1.5"/>
      </svg>',
                'fields'	  => array(
	                array(
		                'id'	  => 'help',
		                'section' => __( 'General', 'foogallery' ),
		                'type'	  => 'html',
		                'help'	  => true,
						'title'	  => __( 'Product Gallery layout', 'foogallery' ),
		                'desc'	  => __( 'The Product Gallery layout works best with the WooCommerce Products datasource. It is the same as the Simple Portfolio template, but with different defaults to make your life easier.', 'foogallery' ),
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
                        'type'    => 'lightbox',
                    ),
                    array(
                        'id'      => 'gutter',
                        'title'   => __( 'Thumbnail Gap', 'foogallery' ),
                        'desc'    => __( 'The spacing or gap between each thumbnail in the gallery.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'slider',
                        'default' => 20,
                        'step'    => 1,
                        'min'     => 0,
						'max'     => 100,
						'row_data'=> array(
							'data-foogallery-change-selector' => 'range-input',
							'data-foogallery-preview' => 'shortcode',
						)
                    ),
                    array(
                        'id'      => 'align',
                        'title'   => __( 'Alignment', 'foogallery' ),
                        'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'radio',
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
		 * Return an array of the fields to remove from the template
		 *
		 * @param $fields_to_remove
		 *
		 * @return string[]
		 */
		function remove_fields( $fields_to_remove ) {
			return array(
				'captions_help',
				'hover_effect_help',
				'hover_effect_preset'
			);
		}

		/**
		 * Return an array of the fields to hide from the template
		 *
		 * @param $fields_to_hide
		 *
		 * @return string[]
		 */
		function hidden_fields( $fields_to_hide ) {
			return array(
				'hover_effect_caption_visibility'
			);
		}

		/**
		 * Return an array of field defaults for the template
		 *
		 * @param $field_defaults
		 *
		 * @return string[]
		 */
		function field_defaults( $field_defaults ) {
			return array(
				'hover_effect_caption_visibility' => 'fg-caption-always',
				'caption_visibility_no_hover_effect' => 'fg-caption-always',
				'border_size' => 'fg-border-medium',
				'rounded_corners' => 'fg-round-medium',
				'loaded_effect' => 'fg-loaded-flip',
				'caption_invert_color' => 'fg-light-overlays',
				'hover_effect_icon' => 'fg-hover-cart',
				'caption_alignment' => 'fg-c-c',
				'filtering_type' => 'simple',
				'filtering_taxonomy' => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
				'ecommerce_button_add_to_cart' => 'shown',
				'ecommerce_button_variable' => 'shown',
				'ecommerce_lightbox_product_information' => 'right',
				'hover_effect_scale' => 'fg-hover-zoomed',
                'lightbox_info_enabled' => 'disabled',
				'buttons_style' => 'fg-cb-pill',
				'filtering_style' => 'pill-block',
			);
		}

		function adjust_fields( $fields, $template ) {
			//update specific fields
			foreach ($fields as $key => &$field) {
				if ( 'hover_effect_type' === $field['id'] ) {
					unset( $field['choices']['preset'] );
				} else if ( 'hover_effect_caption_visibility' === $field['id'] 
						|| 'caption_visibility_no_hover_effect' === $field['id'] ) {
					$field['choices'] = array(
						'fg-caption-always' => __( 'Always Visible', 'foogallery' ),
					);
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

		/**
		 * Add css to the page for the gallery
		 *
		 * @param $gallery FooGallery
		 */
		function add_css( $css, $gallery ) {
			$id = $gallery->container_id();
			$gutter = intval( foogallery_gallery_template_setting( 'gutter', 20 ) );
			$alignment = foogallery_gallery_template_setting( 'align', 'center' );
			if ( $alignment === 'left' ) {
				$alignment = 'flex-start';
			} else if ( $alignment === 'right' ) {
				$alignment = 'flex-end';
			}
			$thumb_width = 250;
			$thumbnail_dimensions = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
			if ( array_key_exists( 'width', $thumbnail_dimensions ) ) {
				$thumb_width = intval( $thumbnail_dimensions['width'] );
			}
			$css[] = '#' . $id . '.fg-simple_portfolio { justify-content: ' . $alignment . '; }';
			$css[] = '#' . $id . '.fg-simple_portfolio .fg-item { flex-basis: ' . $thumb_width . 'px; margin: ' . $gutter . 'px; }';
			return $css;
		}
	}
}