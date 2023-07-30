<?php

if ( ! class_exists( 'FooGallery_Default_Gallery_Template' ) ) {

	define( 'FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ) );

	class FooGallery_Default_Gallery_Template {

		const TEMPLATE_ID = 'default';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			// @formatter:off
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-default', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-default', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-default', array( $this, 'build_gallery_template_arguments' ) );

			// add a style block for the gallery based on the thumbnail width.
			add_action( 'foogallery_loaded_template_before', array( $this, 'add_width_style_block' ), 10, 1 );
			// @formatter:on
		}

		/**
		 * Add a style block based on the width thumbnail size
		 *
		 * @param $gallery FooGallery
		 */
		function add_width_style_block( $gallery ) {
			if ( self::TEMPLATE_ID !== $gallery->gallery_template ) {
				return;
			}

			$id         = $gallery->container_id();
			$dimensions = foogallery_gallery_template_setting('thumbnail_dimensions');
			if ( is_array( $dimensions ) && array_key_exists( 'width', $dimensions ) && intval( $dimensions['width'] ) > 0 ) {
				$width      = intval( $dimensions['width'] );

				// @formatter:off
				?>
<style type="text/css">
	<?php echo '#' . $id; ?> .fg-image {
        width: <?php echo $width; ?>px;
    }
</style>
				<?php
				// @formatter:on
			}
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
				'name'                  => __( 'Responsive Image Gallery', 'foogallery' ),
				'preview_support'       => true,
				'common_fields_support' => true,
				'paging_support'        => true,
				'lazyload_support'      => true,
				'mandatory_classes'     => 'fg-default',
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
						'default'  => array(
							'width'  => get_option( 'thumbnail_size_w' ),
							'height' => get_option( 'thumbnail_size_h' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'mobile_columns',
						'title'    => __( 'Mobile Layout', 'foogallery' ),
						'desc'     => __( 'Number of columns to show on mobile (screen widths less than 600px)', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => '',
						'type'     => 'radio',
						'choices'  => array(
							''   => __( 'Default', 'foogallery' ),
							'fg-m-col1'   => __( '1 Column', 'foogallery' ),
							'fg-m-col2' => __( '2 Columns', 'foogallery' ),
							'fg-m-col3'  => __( '3 Columns', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
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
						'type'    => 'lightbox',
					),
					array(
						'id'       => 'spacing',
						'title'    => __( 'Spacing', 'foogallery' ),
						'desc'     => __( 'The spacing or gap between thumbnails in the gallery.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'type'     => 'select',
						'default'  => 'fg-gutter-10',
						'choices'  => array(
							'fg-gutter-0'  => __( 'none', 'foogallery' ),
							'fg-gutter-5'  => __( '5 pixels', 'foogallery' ),
							'fg-gutter-10' => __( '10 pixels', 'foogallery' ),
							'fg-gutter-15' => __( '15 pixels', 'foogallery' ),
							'fg-gutter-20' => __( '20 pixels', 'foogallery' ),
							'fg-gutter-25' => __( '25 pixels', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-preview'         => 'shortcode'
						)
					),
					array(
						'id'       => 'alignment',
						'title'    => __( 'Alignment', 'foogallery' ),
						'desc'     => __( 'The horizontal alignment of the thumbnails inside the gallery.', 'foogallery' ),
						'section'  => __( 'General', 'foogallery' ),
						'default'  => 'fg-center',
						'type'     => 'radio',
						'spacer'   => '<span class="spacer"></span>',
						'choices'  => array(
							'fg-left'   => __( 'Left', 'foogallery' ),
							'fg-center' => __( 'Center', 'foogallery' ),
							'fg-right'  => __( 'Right', 'foogallery' ),
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview'         => 'shortcode'
						)
					)
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
			$dimensions         = $foogallery->get_meta( 'default_thumbnail_dimensions', array(
				'width'  => get_option( 'thumbnail_size_w' ),
				'height' => get_option( 'thumbnail_size_h' )
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