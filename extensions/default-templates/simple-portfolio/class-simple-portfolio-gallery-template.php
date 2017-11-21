<?php

if ( !class_exists( 'FooGallery_Simple_Portfolio_Gallery_Template' ) ) {

	define('FOOGALLERY_SIMPLE_PORTFOLIO_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Simple_Portfolio_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_located_template-simple_portfolio', array( $this, 'enqueue_dependencies' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-simple_portfolio', array( $this, 'add_common_thumbnail_fields' ), 10, 2 );

			//add the data options needed for simple portfolio
			add_filter( 'foogallery_build_container_data_options-simple_portfolio', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-simple_portfolio', array( $this, 'override_settings'), 10, 3 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-simple_portfolio', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-simple_portfolio', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-simple_portfolio', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );
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
                'slug'        => 'simple_portfolio',
                'name'        => __( 'Simple Portfolio', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'paging_support' => true,
				'mandatory_classes' => 'fg-simple_portfolio',
				'thumbnail_dimensions' => true,
                'fields'	  => array(
                    array(
                        'id'	  => 'help',
                        'title'	  => __( 'Tip', 'foogallery' ),
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
                        'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' )
                    ),
                    array(
                        'id'      => 'lightbox',
                        'title'   => __( 'Lightbox', 'foogallery' ),
                        'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'lightbox',
                        'default' => 'none',
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
                    ),
                    array(
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
							'data-foogallery-preview' => 'class'
						)
                    ),
                ),
			);

			return $gallery_templates;
		}

		/**
		 * Enqueue scripts that the simple portfolio template relies on
		 */
		function enqueue_dependencies() {
			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();
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
			//update specific fields
			foreach ($fields as &$field) {
				if ( 'hover_effect_caption_visibility' === $field['id'] ) {
					$field['default'] = 'fg-caption-always';
					$field['choices'] = array(
						'fg-caption-always' => __( 'Always Visible', 'foogallery' ),
					);
					$field['row_data'] = array(
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-hidden' => true,
						'data-foogallery-preview' => 'class'
					);
				} else if ( 'hover_effect_help' == $field['id'] ||
                            'captions_help' == $field['id']) {
					$field['row_data'] = array(
						'data-foogallery-hidden' => true
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
			$settings['simple_portfolio_hover_effect_preset'] = 'fg-custom';
			$settings['simple_portfolio_hover_effect_caption_visibility'] = 'fg-caption-always';

			return $settings;
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_dimensions'] = $post_data[FOOGALLERY_META_SETTINGS]['simple_portfolio_thumbnail_dimensions'];
			$args['gutter'] = $post_data[FOOGALLERY_META_SETTINGS]['simple_portfolio_gutter'];
            $args['align'] = $post_data[FOOGALLERY_META_SETTINGS]['simple_portfolio_align'];
			return $args;
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
			$dimensions = $foogallery->get_meta( 'simple_portfolio_thumbnail_dimensions', array(
				'width' => 250,
				'height' => 200
			) );
			$dimensions['crop'] = true;
			return $dimensions;
		}
	}
}