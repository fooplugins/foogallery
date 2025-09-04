<?php

if ( !class_exists( 'FooGallery_Polaroid_Gallery_Template' ) ) {

	define('FOOGALLERY_POLAROID_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Polaroid_Gallery_Template {

		const template_id = 'polaroid_new';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 99, 1 );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-polaroid_new', array( $this, 'add_common_thumbnail_fields' ), 10, 2 );

			//add the data options needed for polaroid
			add_filter( 'foogallery_build_container_data_options-polaroid_new', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-polaroid_new', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-polaroid_new', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //build up the thumb dimensions on save
            add_filter( 'foogallery_template_thumbnail_dimensions-polaroid_new', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

            //check if the old Polaroid is installed
            if ( is_admin() ) {
                add_action( 'admin_notices', array( $this, 'display_polaroid_notice') );
            }

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-polaroid_new', array( $this, 'build_gallery_template_arguments' ) );

            // Append classed needed for the gallery template
			add_filter( 'foogallery_build_class_attribute', array( $this, 'append_classes' ), 10, 2 );

			// Ensure we output the inline styling needed for a simple portfolio gallery template
			add_filter( 'foogallery_is_simple_portfolio_gallery_template', array( $this, 'is_simple_portfolio_gallery_template' ), 10, 2 );
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
			$gallery_templates[] = array(
                'slug'        => self::template_id,
                'name'        => __( 'Polaroid PRO', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-simple_portfolio fg-preset fg-polaroid',
				'thumbnail_dimensions' => true,
				'filtering_support' => true,
                'enqueue_core' => true,
				'icon' => '<svg viewBox="0 0 24 24">
        <!-- top left -->
        <g transform="rotate(-5 7 5)">
          <rect x="2" y="2" width="8" height="5"/>
          <rect x="2" y="7" width="8" height="2"/>
        </g>
      
        <!-- top right -->
        <g transform="rotate(4 17 5)">
          <rect x="13" y="3" width="8" height="5"/>
          <rect x="13" y="8" width="8" height="2"/>
        </g>
      
        <!-- bottom left -->
        <g transform="rotate(3 7 15)">
          <rect x="3" y="12" width="8" height="5"/>
          <rect x="3" y="17" width="8" height="2"/>
        </g>
      
        <!-- bottom right -->
        <g transform="rotate(-4 17 15)">
          <rect x="14" y="13" width="8" height="5"/>
          <rect x="14" y="18" width="8" height="2"/>
        </g>
      </svg>',
                'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_dimensions',
                        'title'   => __( 'Thumbnail Size', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => 250,
                            'height' => 200,
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
                        'title'   => __( 'Gutter', 'foogallery' ),
                        'desc'    => __( 'The spacing between each thumbnail in the gallery.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'number',
                        'class'   => 'small-text',
                        'default' => 20,
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
                    ),
                    array(
                        'id'      => 'caption_position',
                        'title' => __('Caption Position', 'foogallery'),
                        'desc' => __('Where the captions are displayed in relation to the thumbnail.', 'foogallery'),
						'section' => __( 'Captions', 'foogallery' ),
                        'default' => '',
                        'type'    => 'radio',
                        'choices' => array(
                            '' => __( 'Below', 'foogallery' ),
                            'fg-captions-top' => __( 'Above', 'foogallery' )
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
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
                'captions_type',
                'hover_effect_preset',
                'hover_effect_caption_visibility',
				'caption_visibility_no_hover_effect',
                'caption_desc_source',
                'caption_desc_length',
				'caption_desc_clamp',
                'caption_alignment'
            );

			$fields_to_remove = array();

			//update specific fields
			foreach ($fields as $key => &$field) {
			    if ( in_array( $field['id'], $field_ids_to_remove ) ) {
				    $fields_to_remove[] = $key;
                }

				if ( 'hover_effect_preset' === $field['id'] ) {
					$field['default'] = 'fg-custom';
					$field['choices'] = array(
						'fg-custom'  => __( 'Polaroid', 'foogallery' )
					);
					$field['row_data'] = array(
						'data-foogallery-hidden' => true,
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-value-selector' => 'input:checked',
						'data-foogallery-preview' => 'class'
					);
				} else if ( 'hover_effect_caption_visibility' === $field['id'] 
					|| 'caption_visibility_no_hover_effect' === $field['id'] ) {
					$field['default'] = 'fg-caption-always';
					$field['choices'] = array(
						'fg-caption-always' => __( 'Always Visible', 'foogallery' ),
					);
					$field['row_data'] = array(
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-hidden' => true,
						'data-foogallery-preview' => 'class'
					);
				} else if ( 'hover_effect_type' == $field['id'] ) {
					unset( $field['choices']['preset'] );
				}
			}

			foreach ($fields_to_remove as $key) {
				unset($fields[$key]);
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
			$settings['polaroid_new_hover_effect_preset'] = 'fg-custom';
			$settings['polaroid_new_hover_effect_caption_visibility'] = 'fg-caption-always';

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
            $dimensions = $foogallery->get_meta( 'polaroid_new_thumbnail_dimensions', array(
                'width' => 250,
                'height' => 200
            ) );
            $dimensions['crop'] = true;
            return $dimensions;
        }

        /**
         * Display a message if the Polaroid extension is also installed
         */
        function display_polaroid_notice() {
            if ( class_exists('FooGallery_Polaroid_Template_Extension') ) {
                ?>
                <div class="notice error">
                    <p>
                        <strong><?php _e('Polaroid Extension Redundant!', 'foogallery'); ?></strong><br/>
                        <?php _e('You have both FooGallery PRO and the old Polaroid extension activated. FooGallery PRO includes the Polaroid PRO gallery template, which makes the free Polaroid extension redundant.', 'foogallery'); ?>
                        <br/>
                        <?php _e('Please edit all galleries that use the old Polaroid gallery template and change them to use the Polaroid PRO gallery template. Once this is done, you can delete the free Polaroid extension.', 'foogallery'); ?>
                        <br/>
                    </p>
                </div>
                <?php
            }
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