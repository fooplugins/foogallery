<?php

if ( !class_exists( 'FooGallery_Spotlight_Gallery_Template' ) ) {

	define('FOOGALLERY_SPOTLIGHT_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Spotlight_Gallery_Template {

		const TEMPLATE_ID = 'spotlight';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-spotlight', array( $this, 'adjust_fields' ), 10, 2 );
			add_filter( 'foogallery_override_gallery_template_fields_defaults-spotlight', array( $this, 'field_defaults' ), 10, 1 );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_template_thumbnail_dimensions-spotlight', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-spotlight', array( $this, 'override_settings'), 10, 3 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-spotlight', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

            //alter the crop value if needed
            add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'alter_field_value'), 10, 4 );

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-spotlight', array( $this, 'build_gallery_template_arguments' ) );

			//add the data options needed for image viewer
			add_filter( 'foogallery_build_container_data_options-spotlight', array( $this, 'add_data_options' ), 10, 3 );

			// add a style block for the gallery based on the thumbnail width.
			add_action( 'foogallery_template_style_block-spotlight', array( $this, 'add_css' ), 10, 2 );
        }

        function alter_field_value( $value, $field, $gallery, $template ) {
            //only do something if we are dealing with the thumbnail_dimensions field in this template
            if ( self::TEMPLATE_ID === $template['slug'] && 'thumbnail_size' === $field['id'] ) {
                if ( !array_key_exists( 'crop', $value ) ) {
                    $value['crop'] = true;
                }
            }

            return $value;
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

			$gallery_templates[self::TEMPLATE_ID] = array(
				'slug'        => self::TEMPLATE_ID,
				'name'        => __( 'Spotlight PRO', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
				'lazyload_support' => true,
				'mandatory_classes' => 'fg-image-viewer fg-spotlight fg-overlay-controls',
				'thumbnail_dimensions' => true,
				'enqueue_core' => true,
				'admin_css' => FOOGALLERY_SPOTLIGHT_GALLERY_TEMPLATE_URL . 'css/admin-gallery-spotlight.css',
				'icon' => '<svg viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="17"/>

        <!-- arrows (solid triangles) -->
        <polygon points="5,11 7,9 7,13" />
        <polygon points="19,11 17,9 17,13" />
        <!-- dots (more spaced out) -->
        <circle cx="8" cy="17" r="0.8"/>
        <circle cx="12" cy="17" r="0.8"/>
        <circle cx="16" cy="17" r="0.8"/>
      </svg>',
				'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_size',
                        'title'   => __( 'Thumb Size', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => 640,
                            'height' => 500,
                            'crop' => true
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
                        'default' => 'image' ,
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to either link each thumbnail to the full size image or to the image\'s attachment page', 'foogallery'),
                    ),
                    array(
                        'id'      => 'lightbox',
                        'desc'    => __( 'Choose which lightbox you want to use in the gallery', 'foogallery' ),
                        'type'    => 'lightbox',
                    ),
                    array(
                        'id'      => 'alignment',
                        'title'   => __( 'Alignment', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'The horizontal alignment of the gallery', 'foogallery' ),
                        'default' => 'fg-center',
						'type'    => 'radio',
                        'choices' => array(
                            'fg-left' => __( 'Left', 'foogallery' ),
                            'fg-center' => __( 'Center', 'foogallery' ),
                            'fg-right' => __( 'Right', 'foogallery' ),
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
                        'id'      => 'dots_position',
                        'title'   => __( 'Dots Position', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'Dots tell a visitor how many items are in the gallery. Choose where the dots are displayed.', 'foogallery' ),
                        'default' => 'fg-dots-center',
						'type'    => 'radio',
                        'choices' => array(
							'fg-dots-none' => __( 'Hidden', 'foogallery' ),
							'fg-dots-left'   => __( 'Left', 'foogallery' ),
                            'fg-dots-center' => __( 'Center', 'foogallery' ),
                            'fg-dots-right'  => __( 'Right', 'foogallery' )
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
					array(
						'id'       => 'arrow_icon',
						'title'    => __( 'Navigation Arrow Icon', 'foogallery' ),
						'desc'     => __( 'Which arrow icon to use for the navigation arrows.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => 'fg-nav-icon-line',
						'type'     => 'htmlicon',
						'choices'  => array(
							''                   => array( 'label' => __( 'Rounded', 'foogallery' ), 'html' => '<div class="foogallery-setting-spotlight-arrow_icon"></div>' ),
							'fg-nav-icon-square' => array( 'label' => __( 'Square', 'foogallery' ), 'html' => '<div class="foogallery-setting-spotlight-arrow_icon fg-nav-icon-square"></div>' ),
							'fg-nav-icon-dashed' => array( 'label' => __( 'Dashed', 'foogallery' ), 'html' => '<div class="foogallery-setting-spotlight-arrow_icon fg-nav-icon-dashed"></div>' ),
							'fg-nav-icon-arrowhead' => array( 'label' => __( 'Arrowhead', 'foogallery' ), 'html' => '<div class="foogallery-setting-spotlight-arrow_icon fg-nav-icon-arrowhead"></div>' ),
							'fg-nav-icon-line' => array( 'label' => __( 'Line', 'foogallery' ), 'html' => '<div class="foogallery-setting-spotlight-arrow_icon fg-nav-icon-svg">' . self::get_arrow_svg( 'fg-nav-icon-line' ) . '</div>' ),
							'fg-nav-icon-chevron' => array( 'label' => __( 'Chevron', 'foogallery' ), 'html' => '<div class="foogallery-setting-spotlight-arrow_icon fg-nav-icon-svg">' . self::get_arrow_svg( 'fg-nav-icon-chevron' ) . '</div>' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'      => 'background_color',
						'title'   => __( 'Background Color', 'foogallery' ),
						'desc'	  => __( 'Choose a background color for the gallery.', 'foogallery '),
						'section'  => __( 'General', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-preview'               => 'shortcode'
						)
					)
				),
			);

			return $gallery_templates;
		}

		public static function get_arrow_svg( $arrow_icon ) {
			switch ( $arrow_icon ) {
				case 'fg-nav-icon-line':
					return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="40" height="40" fill="none">
    <path d="M12 24h24M24 12l12 12-12 12" 
          stroke="currentColor" 
          stroke-width="3" 
          stroke-linecap="butt" 
          stroke-linejoin="miter"/>
  </svg>';
				case 'fg-nav-icon-chevron':
					return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="40" height="40" fill="none">
  <path d="M18 12l16 12-16 12" 
        stroke="currentColor" 
        stroke-width="3" 
        stroke-linecap="butt" 
        stroke-linejoin="miter"/>
</svg>';
				default:
					return '<span></span>';
			}
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

			//update specific fields
			foreach ($fields as &$field) {
				if ( 'rounded_corners' === $field['id'] ) {
					unset( $field['choices']['fg-round-full'] );
				}
			}

			return $fields;
		}

		/**
		 * Return an array of field defaults for the template
		 *
		 * @param $field_defaults
		 *
		 * @return string[]
		 */
		function field_defaults( $field_defaults ) {
			return array_merge( $field_defaults, array(
				'hover_effect_caption_visibility' => '',
				'caption_visibility_no_hover_effect' => '',
				'drop_shadow' => '',
				'hover_effect_icon' => '',
				'hover_effect_scale' => '',
				'inner_shadow' => '',
				'rounded_corners' => 'fg-round-large',
				'theme' => 'fg-light',
				'lightbox' => 'none',
				'thumbnail_link' => 'none'
			) );
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
			$dimensions = $foogallery->get_meta( 'spotlight_thumbnail_size', array(
				'width' => 640,
				'height' => 500,
                'crop' => true
			) );
            if ( !array_key_exists( 'crop', $dimensions ) ) {
                $dimensions['crop'] = true;
            }
			return $dimensions;
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
			if ( 'fg-round-full' === $settings['spotlight_rounded_corners'] ) {
				$settings['spotlight_rounded_corners'] = 'fg-round-large';
			}

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
            if ( array_key_exists( 'thumbnail_size', $arguments) ) {
                return array(
                    'height' => intval($arguments['thumbnail_size']['height']),
                    'width' => intval($arguments['thumbnail_size']['width']),
                    'crop' => $arguments['thumbnail_size']['crop']
                );
            }
            return null;
		}

        /**
         * Build up the arguments needed for rendering this gallery template
         *
         * @param $args
         * @return array
         */
        function build_gallery_template_arguments( $args ) {
            $args = foogallery_gallery_template_setting( 'thumbnail_size', array(
	            'width' => 640,
	            'height' => 500,
	            'crop' => true
            ) );
            if ( !array_key_exists( 'crop', $args ) ) {
                $args['crop'] = '1'; //we now force thumbs to be cropped by default
            }
            $args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

            return $args;
        }

		/**
		 * Add the required options
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {
			$options['template']['loop'] = true;
			return $options;
		}

		/**
		 * Add css to the page for the gallery
		 *
		 * @param $gallery FooGallery
		 */
		function add_css( $css, $gallery ) {

			$id         = $gallery->container_id();
			$dimensions = foogallery_gallery_template_setting('thumbnail_size');
			if ( is_array( $dimensions ) && array_key_exists( 'width', $dimensions ) && intval( $dimensions['width'] ) > 0 ) {
				$width = intval( $dimensions['width'] );
				$css[] = '#' . $id . ' .fg-image { width: ' . $width . 'px; }';
			}

			$arrow_icon = foogallery_gallery_template_setting( 'arrow_icon', '' );
			if ( 'fg-nav-icon-line' === $arrow_icon  || 'fg-nav-icon-chevron' === $arrow_icon ) {
				$css[] = '#' . $id . ' .fiv-ctrls .fiv-next::before, #' . $id . ' .fiv-ctrls .fiv-prev::before { content: ""; }';
				$css[] = '#' . $id . ' .fiv-ctrls .fiv-prev { transform: translateY(-50%) rotate(180deg); }';
			} else if ( 'fg-nav-icon-square' === $arrow_icon ) {
				$css[] = '#' . $id . ' .fiv-ctrls .fiv-next::before, #' . $id . ' .fiv-ctrls .fiv-prev::before { content: "\2B95"; }';
			} else if ( 'fg-nav-icon-dashed' === $arrow_icon ) {
				$css[] = '#' . $id . ' .fiv-ctrls .fiv-next::before, #' . $id . ' .fiv-ctrls .fiv-prev::before { content: "\21E2"; }';
			} else if ( 'fg-nav-icon-arrowhead' === $arrow_icon ) {
				$css[] = '#' . $id . ' .fiv-ctrls .fiv-next::before, #' . $id . ' .fiv-ctrls .fiv-prev::before { content: "\25BA"; }';
			}

			$background_color = foogallery_gallery_template_setting( 'background_color', '' );
			if ( !empty( $background_color ) ) {
				$css[] = '#' . $id . '.foogallery .fg-item { background-color: ' . $background_color . '; }';
			}

			return $css;
		}
	}
}