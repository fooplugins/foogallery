<?php

if ( ! class_exists( 'FooGallery_Carousel_Gallery_Template' ) ) {

	class FooGallery_Carousel_Gallery_Template {

		const TEMPLATE_ID = 'carousel';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			// @formatter:off
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-carousel', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-carousel', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-carousel', array( $this, 'build_gallery_template_arguments' ) );

			//add the data options needed for grid pro
			add_filter( 'foogallery_build_container_data_options-carousel', array( $this, 'add_data_options' ), 10, 3 );
			// @formatter:on
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
			$show = foogallery_gallery_template_setting( 'show', 3 );
			$scale = foogallery_gallery_template_setting( 'scale', 0.12 );
			$centerOnClick = foogallery_gallery_template_setting( 'action', 'false' ) === 'true';

			$options['template']['show'] = $show;
			$options['template']['scale'] = floatval( $scale );
			$options['template']['duration'] = 0;
			$options['template']['centerOnClick'] = $centerOnClick;

			return $options;
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 *
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
		 *
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
				'slug'                  => self::TEMPLATE_ID,
				'name'                  => __( 'Carousel', 'foogallery' ),
				'preview_support'       => true,
				'common_fields_support' => true,
				'paging_support'        => true,
				'lazyload_support'      => true,
				'mandatory_classes'     => 'fg-carousel',
				'thumbnail_dimensions'  => true,
				'filtering_support'     => true,
				'enqueue_core'          => true,
				'fields'                => array(
					array(
						'id'       => 'thumbnail_dimensions',
						'title'    => __( 'Thumbnail Size', 'foogallery' ),
						'desc'     => __( 'Choose the size of your thumbnails.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'thumb_size_no_crop',
						'default' => array(
							'width' => 640,
							'height' => 360,
							'crop' => true
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'show',
						'title'    => __( 'Items To Show', 'foogallery' ),
						'desc'     => __( 'The total number of items displayed in the carousel. This should be an ODD number as the active item is the center and the remainder make up each side.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '3',
						'type'     => 'number',
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'scale',
						'title'    => __( 'Scaling', 'foogallery' ),
						'desc'     => __( 'How to scale the items that are not in the centre.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '0.15',
						'type'     => 'select',
						'choices' => array(
							'0' => __( 'None', 'foogallery' ),
							'0.05' => __( 'Less', 'foogallery' ),
							'0.12' => __( 'Normal', 'foogallery' ),
							'0.18' => __( 'More', 'foogallery' ),
							'0.25' => __( 'Most', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'action',
						'title'    => __( 'Side Items Click', 'foogallery' ),
						'desc'     => __( 'What happens when an item in the carousel is clicked.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => 'true',
						'type'     => 'radio',
						'choices' => array(
							'true' => __( 'Centre The Clicked Item', 'foogallery' ),
							'false' => __( 'Open The Item In Lightbox', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:checked',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __( 'Thumbnail Link', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'default' => 'image',
						'type'    => 'thumb_link',
						'desc'    => __( 'You can choose to link each thumbnail to the full size image, the image\'s attachment page, a custom URL, or you can choose to not link to anything.', 'foogallery' ),
					),
					array(
						'id'      => 'lightbox',
						'title'   => __( 'Lightbox', 'foogallery' ),
						'desc'    => __( 'Choose which lightbox you want to use. The lightbox will generally only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'lightbox',
						'default' => 'none'
					),
				)
			);

			return $gallery_templates;
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
			if ( array_key_exists( 'thumbnail_dimensions', $arguments ) ) {
				return array(
					'height' => intval( $arguments['thumbnail_dimensions']['height'] ),
					'width'  => intval( $arguments['thumbnail_dimensions']['width'] ),
					'crop'   => '1'
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
			$dimensions = $foogallery->get_meta( 'carousel_thumbnail_dimensions', array(
				'width'  => 640,
				'height' => 360
			) );
			$dimensions['crop'] = true;

			return $dimensions;
		}

		/**
		 * Build up the arguments needed for rendering this gallery template
		 *
		 * @param $args
		 *
		 * @return array
		 */
		function build_gallery_template_arguments( $args ) {
			$args         = foogallery_gallery_template_setting( 'thumbnail_dimensions', array() );
			$args['crop'] = '1'; //we now force thumbs to be cropped
			$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

			return $args;
		}
	}
}