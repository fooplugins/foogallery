<?php

if ( ! class_exists( 'FooGallery_Boxslider_Gallery_Template' ) ) {

	define('FOOGALLERY_BOXSLIDER_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	/**
	 * Class FooGallery_Boxslider_Gallery_Template
	 * Handles the Boxslider gallery template for FooGallery.
	 */
	class FooGallery_Boxslider_Gallery_Template {

		const TEMPLATE_ID = 'boxslider';

		/**
		 * Constructor to initialize the template.
		 */
		public function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//get thumbnail dimensions
			add_filter( 'foogallery_template_thumbnail_dimensions-boxslider', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			add_filter( 'foogallery_build_class_attribute', array( $this, 'append_classes' ), 10, 2 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-boxslider', array( $this, 'build_gallery_template_arguments' ) );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-boxslider', array( $this, 'override_settings'), 10, 3 );

			// add the data options needed for boxslider
			add_filter( 'foogallery_build_container_data_options-boxslider', array( $this, 'add_data_options' ), 10, 3 );

			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_language_settings' ), 50 );

			// Build up the thumb dimensions from some arguments.
			add_filter( 'foogallery_calculate_thumbnail_dimensions-boxslider', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );
		}

		/**
		 * Registers the template file.
		 *
		 * @param array $extensions The array of template files.
		 * @return array Updated array of template files.
		 */
		public function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		/**
		 * Adds the Boxslider template to the list of available templates.
		 *
		 * @param array $gallery_templates The array of gallery templates.
		 * @return array Updated array of gallery templates.
		 */
		public function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
				'slug' => self::TEMPLATE_ID,
				'name' => __( 'Boxslider Pro', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
				'filtering_support' => true,
				'embed_support' => true,
				'panel_support' => true,
				'enqueue_core' => true,
				'fields' => array(
					array(
						'id' => 'thumbnail_dimensions',
						'title' => __( 'Thumbnail Size', 'foogallery' ),
						'desc' => __( 'Choose the size of your thumbnails.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type' => 'thumb_size_no_crop',
						'default' => array(
							'width' => 500,
							'height' => 300,
							'crop' => true,
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						),
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
						'id' => 'effect',
						'title' => __( 'Effect', 'foogallery' ),
						'desc' => __( 'Determines the type of transition effect between slides.', 'foogallery' ),
						'type' => 'select',
						'default' => 'fade',
						'choices' => array(
							'fade' => __( 'Fade Slider', 'foogallery' ),
							'tile' => __( 'Tile Slider', 'foogallery' ),
							'carousel' => __( 'Carousel Slider', 'foogallery' ),
							'cube' => __( 'Cube Slider', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						),
					),

					// CONDITIONAL FIELDS FOR SLIDER EFFECTS BELOW THIS LINE.
					// IF FADESLIDER IS CHOSEN SHOW THESE FIELDS.
					array(
						'id' => 'timing-function',
						'title' => __( 'Timing function', 'foogallery' ),
						'desc' => __( 'The CSS transition timing function to use when fading slide opacity.', 'foogallery' ),
						'type' => 'select',
						'default' => 'ease-in',
						'choices' => array(
							'ease-in' => __( 'Ease-in', 'foogallery' ),
							'ease-out' => __( 'Ease-out', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'effect',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'fade',
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						),
					),

					// IF TILESLIDER IS CHOSEN SHOW THESE FIELDS.
					array(
						'id' => 'tile-effect',
						'title' => __( 'Tile Effect', 'foogallery' ),
						'desc' => __( 'The transition effect for animating the tiles during slide transitions.', 'foogallery' ),
						'type' => 'select',
						'default' => 'flip',
						'choices' => array(
							'flip' => __( 'Flip', 'foogallery' ),
							'fade' => __( 'Fade', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'effect',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'tile',
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						),
					),
					array(
						'id' => 'rows',
						'title' => __( 'Rows', 'foogallery' ),
						'desc' => __( 'Specifies the time interval in milliseconds within which the slide animation will complete.', 'foogallery' ),
						'type' => 'number',
						'default' => 8,
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'effect',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'tile',
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						),
					),
					array(
						'id' => 'rowOffset',
						'title' => __( 'Row Offset', 'foogallery' ),
						'desc' => __( 'The time offset for starting to animate the tiles in a row.', 'foogallery' ),
						'type' => 'number',
						'default' => 50,
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'effect',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'tile',
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						),
					),

					// IF CUBESLIDER IS CHOSEN SHOW THESE FIELDS.
					array(
						'id' => 'direction',
						'title' => __( 'Direction', 'foogallery' ),
						'desc' => __( 'The direction in which the cube should rotate to the next slide.', 'foogallery' ),
						'type' => 'select',
						'default' => 'horizontal',
						'choices' => array(
							'horizontal' => __( 'Horizontal', 'foogallery' ),
							'vertical' => __( 'Vertical', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'effect',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'cube',
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						),
					),

					// IF CAROUSELSLIDER IS CHOSEN SHOW THESE FIELDS.
					array(
						'id' => 'cover',
						'title' => __( 'Cover', 'foogallery' ),
						'desc' => __( 'If true sets the slide effect to cover over the previous slide.', 'foogallery' ),
						'type' => 'select',
						'default' => 'false',
						'choices' => array(
							'false' => __( 'False', 'foogallery' ),
							'true' => __( 'True', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'effect',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'carousel',
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						),
					),

					// COMMON FIELDS FOR ALL SLIDER EFFECTS BELOW THIS LINE.

					array(
						'id' => 'speed',
						'title' => __( 'Speed of Transition', 'foogallery' ),
						'desc' => __( 'The time interval in milliseconds within which the slide animation will complete', 'foogallery' ),
						'type' => 'number',
						'default' => 800,
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode',
						),
					),
					array(
						'id' => 'swipe',
						'title' => __( 'swipe', 'foogallery' ),
						'desc' => __( 'Enable swiping the box to navigate to the next or previous slide.', 'foogallery' ),
						'type' => 'radio',
						'default' => 'true',
						'choices' => array(
							'true' => __( 'True', 'foogallery' ),
							'false' => __( 'false', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode',
						),
					),
					array(
						'id' => 'autoScroll',
						'title' => __( 'Auto-Scrolling', 'foogallery' ),
						'desc' => __( 'Set true to automatically transition through the slides.', 'foogallery' ),
						'type' => 'radio',
						'default' => 'true',
						'choices' => array(
							'true' => __( 'True', 'foogallery' ),
							'false' => __( 'False', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode',
						),
					),
					// Shown when autoscroll is set to true.
					array(
						'id' => 'timeout',
						'title' => __( 'Timeout', 'foogallery' ),
						'desc' => __( ' Sets the time interval between slide transitions.', 'foogallery' ),
						'type' => 'number',
						'default' => 5000,
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'autoScroll',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'true',
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						),
					),
					array(
						'id' => 'pauseOnHover',
						'title' => __( 'Pause On Hover', 'foogallery' ),
						'desc' => __( 'Pause an auto-scrolling slider when the users mouse hovers over it. For use with autoScroll or a slider in play mode.', 'foogallery' ),
						'type' => 'radio',
						'default' => 'false',
						'choices' => array(
							'true' => __( 'True', 'foogallery' ),
							'false' => __( 'false', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-hidden'                   => true,
							'data-foogallery-show-when-field'          => 'autoScroll',
							'data-foogallery-show-when-field-operator' => '===',
							'data-foogallery-show-when-field-value'    => 'true',
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'shortcode',
						)
					),
					array(
						'id'      => 'language-help',
						'desc'    => __( 'You can change the "Prev", "Next", "Play" and "pause" text used in the gallery from the settings page, under the Language tab.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'help',
					),
				),
			);
			return $gallery_templates;
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
			return $settings;
		}

		/**
		 * Builds the gallery template arguments.
		 *
		 * @param array $args The array of arguments.
		 * @return array Updated array of arguments.
		 */
		public function build_gallery_template_arguments( $args ) {
			$args = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
			$args['crop'] = '1';
			$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
			return $args;
		}

		/**
		 * Get the thumb dimensions arguments saved for the gallery for this gallery template
		 *
		 * @param array $dimensions
		 * @param FooGallery $foogallery
		 *
		 * @return mixed
		 */
		public function get_thumbnail_dimensions( $dimensions, $foogallery ) {
			$dimensions = $foogallery->get_meta( 'boxslider_thumbnail_dimensions', false );
			return $dimensions;
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
		public function add_data_options( $options, $gallery, $attributes ) {
			$effect = foogallery_gallery_template_setting( 'effect', 'fade' );
			$timing_function = foogallery_gallery_template_setting( 'timing-function', 'ease-in' );
			$tile_effect = foogallery_gallery_template_setting( 'tile-effect', 'flip' );
			$rows = foogallery_gallery_template_setting( 'rows', 8 );
			$row_offset = foogallery_gallery_template_setting( 'rowOffset', 50 );
			$speed = foogallery_gallery_template_setting( 'speed', 800 );
			$timeout = foogallery_gallery_template_setting( 'timeout', 5000 );
			$direction = foogallery_gallery_template_setting( 'direction', 'horizontal' );
			$cover = foogallery_gallery_template_setting( 'cover', 'false' );
			$swipe = foogallery_gallery_template_setting( 'swipe', 'false' );
			$autoScroll = foogallery_gallery_template_setting( 'autoScroll', 'true' );
			$pause_on_hover = foogallery_gallery_template_setting( 'pauseOnHover', 'false' );

			$options['template']['effect'] = $effect;
			$options['template']['timing-function'] = $timing_function;
			$options['template']['tile-effect'] = $tile_effect;
			$options['template']['rows'] = intval( $rows );
			$options['template']['rowOffset'] = intval( $row_offset );
			$options['template']['speed'] = intval( $speed );
			$options['template']['timeout'] = intval( $timeout );
			$options['template']['direction'] = $direction;
			$options['template']['cover'] = $cover;
			$options['template']['swipe'] = $swipe;
			$options['template']['autoScroll'] = $autoScroll;
			$options['template']['pauseOnHover'] = $pause_on_hover;

			return $options;
		}

		/**
		 * Add language settings to the provided settings array.
		 *
		 * This function adds language-related settings for the foogallery Box Slider section.
		 *
		 * @param array $settings An array of existing settings.
		 *
		 * @return array The modified settings array with added language settings.
		 */
		public function add_language_settings( $settings ) {

			$settings['settings'][] = array(
				'id'      => 'language_boxslider_prev_text',
				'title'   => __( 'Boxslider Prev Text', 'foogallery_user_uploads' ),
				'type'    => 'text',
				'default' => __( 'Prev', 'foogallery_user_uploads' ),
				'section' => __( 'Box Slider Template', 'foogallery_user_uploads' ),
				'tab'     => 'language',
			);

			$settings['settings'][] = array(
				'id'      => 'language_boxslider_next_text',
				'title'   => __( 'Boxslider Next Text', 'foogallery_user_uploads' ),
				'type'    => 'text',
				'default' => __( 'Next', 'foogallery_user_uploads' ),
				'section' => __( 'Box Slider Template', 'foogallery_user_uploads' ),
				'tab'     => 'language',
			);

			$settings['settings'][] = array(
				'id'      => 'language_boxslider_play_text',
				'title'   => __( 'Boxslider Play Text', 'foogallery_user_uploads' ),
				'type'    => 'text',
				'default' => __( 'Play', 'foogallery_user_uploads' ),
				'section' => __( 'Box Slider Template', 'foogallery_user_uploads' ),
				'tab'     => 'language',
			);

			$settings['settings'][] = array(
				'id'      => 'language_boxslider_pause_text',
				'title'   => __( 'Boxslider Pause Text', 'foogallery_user_uploads' ),
				'type'    => 'text',
				'default' => __( 'Pause', 'foogallery_user_uploads' ),
				'section' => __( 'Box Slider Template', 'foogallery_user_uploads' ),
				'tab'     => 'language',
			);

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
		public function build_thumbnail_dimensions_from_arguments( $dimensions, $arguments ) {
			if ( array_key_exists( 'thumbnail_dimensions', $arguments ) ) {
				$thumbnail_dimensions = $arguments['thumbnail_dimensions'];
				return array(
					'height' => intval( $thumbnail_dimensions['height'] ),
					'width' => intval( $thumbnail_dimensions['width'] ),
					'crop' => $thumbnail_dimensions['crop'] === '1'
				);
			}
			return null;
		}

		/**
		 * Adds the classes onto the container
		 *
		 * @param $classes
		 * @param $foogallery FooGallery
		 *
		 * @return array
		 */
		public function append_classes( $classes, $foogallery ) {
			if ( isset( $foogallery ) && isset( $foogallery->gallery_template ) && $foogallery->gallery_template === self::TEMPLATE_ID ) {

				// Add a class for effects in the panel.
				$effect = foogallery_gallery_template_setting( 'effect', 'fade' );
				if ( $effect !== '' ) {
					$classes[] = 'fg-boxslider-effect-' . $effect;
				}
			}

			return $classes;
		}
	}
}
