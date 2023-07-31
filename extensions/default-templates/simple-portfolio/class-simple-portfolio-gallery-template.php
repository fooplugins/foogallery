<?php

if ( !class_exists( 'FooGallery_Simple_Portfolio_Gallery_Template' ) ) {

	define('FOOGALLERY_SIMPLE_PORTFOLIO_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Simple_Portfolio_Gallery_Template {

		const template_id = 'simple_portfolio';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-simple_portfolio', array( $this, 'adjust_fields' ), 101, 2 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-simple_portfolio', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-simple_portfolio', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-simple_portfolio', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-simple_portfolio', array( $this, 'build_gallery_template_arguments' ) );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'override_class_attributes' ), 99, 2 );

			//add a style block for the gallery based on the field settings for gutter, align and columnWidth
			add_action( 'foogallery_loaded_template_before', array( $this, 'add_style_block' ), 10, 1 );
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
                'slug'        => self::template_id,
                'name'        => __( 'Simple Portfolio', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-simple_portfolio',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'enqueue_core' => true,
                'fields'	  => array(
                    array(
                        'id'	  => 'help',
                        'section' => __( 'General', 'foogallery' ),
                        'type'	  => 'html',
                        'help'	  => true,
                        'desc'	  => __( 'The Simple Portfolio template works best when you have <strong>captions and descriptions</strong> set for every attachment in the gallery. To change captions and descriptions, simply hover over the thumbnail above and click the "i" icon.', 'foogallery' ),
                    ),
                    array(
                        'id'      => 'thumbnail_dimensions',
                        'title'   => __( 'Thumbnail Size', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'thumb_size_no_crop',
                        'default' => array(
                            'width' => 250,
                            'height' => 200
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
                        'title'   => __( 'Gutter', 'foogallery' ),
                        'desc'    => __( 'The spacing between each thumbnail in the gallery.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 5,
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
		function adjust_fields( $fields, $template ) {
			$new_fields[] = array(
				'id'      => 'caption_position',
				'title' => __('Caption Position', 'foogallery'),
				'desc' => __('Where the captions are displayed in relation to the thumbnail.', 'foogallery'),
				'section' => __( 'Captions', 'foogallery' ),
				'default' => '',
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'choices' => array(
					'' => __( 'Below', 'foogallery' ),
					'fg-captions-top' => __( 'Above', 'foogallery' )
				),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector' => 'input:checked',
					'data-foogallery-preview' => 'shortcode'
				)
			);

			$index_of_captions_field = 0;
			$index = 0;

			//update specific fields
			foreach ($fields as &$field) {

				$field_section = array_key_exists( 'section', $field ) ? $field['section'] : '';

				if ( $index_of_captions_field === 0 && __( 'Captions', 'foogallery' ) === $field_section ) {
					$index_of_captions_field = $index;
				}

				$index++;

				if ( 'hover_effect_caption_visibility' === $field['id'] ) {
					$field['default'] = 'fg-caption-always';
					$field['choices'] = array(
						'fg-caption-always' => __( 'Always Visible', 'foogallery' ),
					);
					$field['row_data'] = array(
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-hidden' => true,
						'data-foogallery-preview' => 'shortcode'
					);
				} else if ( 'hover_effect_help' == $field['id'] ||
                            'captions_help' == $field['id']) {
					$field['row_data'] = array(
						'data-foogallery-hidden' => true
					);
				} else if ( 'theme' === $field['id'] ) {
					$field['default'] = 'fg-dark';
					$field['choices'] = array(
						'fg-light'  => __( 'Light', 'foogallery' ),
						'fg-dark'   => __( 'Dark', 'foogallery' ),
						'fg-transparent' => __( 'Transparent', 'foogallery' ),
						'fg-custom' => __( 'Custom', 'foogallery' )
					);
				}
			}

			array_splice( $fields, $index_of_captions_field, 0, $new_fields );

			return $fields;
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
			$settings['simple_portfolio_hover_effect_caption_visibility'] = 'fg-caption-always';

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
				$dimensions = array(
                    'height' => intval($arguments['thumbnail_dimensions']['height']),
                    'width' => intval($arguments['thumbnail_dimensions']['width']),
                    'crop' => '1'
                );

				if ( 'on' === foogallery_get_setting('enable_legacy_thumb_cropping') ) {
					$dimensions['crop'] = '0';
				}

				return $dimensions;
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
			$dimensions = $foogallery->get_meta( 'simple_portfolio_thumbnail_dimensions', array(
				'width' => 250,
				'height' => 200,
				'crop' => true
			) );

			if ( 'on' === foogallery_get_setting('enable_legacy_thumb_cropping') ) {
				$dimensions['crop'] = false;
			} else {
				$dimensions['crop'] = true;
			}
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
            $args['crop'] = '1'; //we now force thumbs to be cropped
			if ( 'on' === foogallery_get_setting('enable_legacy_thumb_cropping') ) {
				$args['crop'] = '0';
			}
            $args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
            $args['image_attributes'] = array(
                'class'  => 'bf-img',
                'height' => $args['height']
            );
            $args['link_attributes'] = array( 'class' => 'foogallery-thumb' );

            return $args;
        }

		/**
		 * Override the classes for the captions visibility
		 *
		 * @param $classes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function override_class_attributes( $classes, $gallery ) {
			if ( self::template_id === $gallery->gallery_template ) {
				if ( ( $key = array_search( 'fg-caption-hover', $classes ) ) !== false) {
					unset( $classes[$key] );
				}

				$classes[] = 'fg-caption-always';
			}

			return $classes;
		}

		/**
		 * @param $gallery
		 *
		 * @return bool|mixed|void
		 */
		function is_simple_portfolio_gallery_template( $gallery ) {
			if ( self::template_id === $gallery->gallery_template ) {
				return true;
			}

			return apply_filters( 'foogallery_is_simple_portfolio_gallery_template', false, $gallery );
		}

		/**
		 * Add a style block based on the field settings
		 *
		 * @param $gallery FooGallery
		 */
		function add_style_block( $gallery ) {
			//check if the template is a "Simple Portfolio" clone
			if ( !$this->is_simple_portfolio_gallery_template( $gallery ) ) {
				return;
			}

			$id = $gallery->container_id();
			$gutter = intval( foogallery_gallery_template_setting( 'gutter', 5 ) );
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
			?>
			<style>
                #<?php echo $id; ?>.fg-simple_portfolio {
                    justify-content: <?php echo $alignment; ?>;
                }
                #<?php echo $id; ?>.fg-simple_portfolio .fg-item {
                    flex-basis: <?php echo $thumb_width; ?>px;
                    margin: <?php echo $gutter; ?>px;
                }
			</style>
			<?php
		}
	}
}