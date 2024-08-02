<?php

if ( ! class_exists( 'FooGallery_Boxslider_Gallery_Template' ) ) {

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
			add_filter( 'foogallery_gallery_template_arguments-boxslider', array( $this, 'build_gallery_template_arguments' ) );
			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'admin_custom_fields' ), 10, 3 );
		}

		/**
		 * Adds custom fields for the Boxslider template in the admin.
		 *
		 * @param string $field The field name.
		 * @param object $gallery The gallery object.
		 * @param string $template The template name.
		 */
		public function admin_custom_fields( $field, $gallery, $template ) {
			// Custom admin fields for Boxslider template.
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
				'name' => __( 'Boxslider', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
				'lazyload_support' => true,
				'enqueue_core' => true,
				'fields' => array(
					array(
						'id' => 'thumbnail_dimensions',
						'title' => __( 'Thumbnail Size', 'foogallery' ),
						'desc' => __( 'Choose the size of your thumbnails.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type' => 'thumb_size_no_crop',
						'default' => array(
							'width' => 200,
							'height' => 200,
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
					// TODO:: Add boxslider fields.
				),
			);
			return $gallery_templates;
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
	}
}
