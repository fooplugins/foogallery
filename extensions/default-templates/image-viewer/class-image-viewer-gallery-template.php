<?php

if ( !class_exists( 'FooGallery_Image_Viewer_Gallery_Template' ) ) {

	define('FOOGALLERY_IMAGE_VIEWER_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Image_Viewer_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-image-viewer', array( $this, 'add_common_thumbnail_fields' ), 10, 2 );

			add_action( 'foogallery_located_template-image-viewer', array( $this, 'enqueue_dependencies' ) );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_template_thumbnail_dimensions-image-viewer', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-image-viewer', array( $this, 'override_settings'), 10, 3 );
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
				'slug'        => 'image-viewer',
				'name'        => __( 'Image Viewer', 'foogallery' ),
				'lazyload_support' => true,
				'fields'	  => array(
                    array(
                        'id'      => 'thumbnail_size',
                        'title'   => __( 'Thumb Size', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnails', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => 640,
                            'height' => 360,
                            'crop' => true
                        )
                    ),
                    array(
                        'id'      => 'thumbnail_link',
                        'title'   => __( 'Thumb Link', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'image' ,
                        'type'    => 'thumb_link',
                        'desc'	  => __( 'You can choose to either link each thumbnail to the full size image or to the image\'s attachment page', 'foogallery')
                    ),
                    array(
                        'id'      => 'lightbox',
                        'title'   => __( 'Lightbox', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'Choose which lightbox you want to use in the gallery', 'foogallery' ),
                        'type'    => 'lightbox'
                    ),
                    array(
                        'id'      => 'alignment',
                        'title'   => __( 'Alignment', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery', 'foogallery' ),
                        'default' => 'fg-center',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
                        'choices' => array(
                            'fg-left' => __( 'Left', 'foogallery' ),
                            'fg-center' => __( 'Center', 'foogallery' ),
                            'fg-right' => __( 'Right', 'foogallery' ),
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-preview' => 'class'
						)
                    ),
                    array(
                        'id'      => 'language-help',
                        'title'   => __( 'Language Help', 'foogallery' ),
                        'desc'    => __( 'This gallery template shows the below items of text. Change them to suit your preference or language.', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
                        'type'    => 'help'
                    ),
					array(
						'id'      => 'text-prev',
						'title'   => __( '"Prev" Text', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'text',
						'default' =>  __('Prev', 'foogallery')
					),
					array(
						'id'      => 'text-of',
						'title'   => __( '"of" Text', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'text',
						'default' =>  __('of', 'foogallery')
					),
					array(
						'id'      => 'text-next',
						'title'   => __( '"Next" Text', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'text',
						'default' =>  __('Next', 'foogallery')
					)
				)
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
			$updated_fields = apply_filters( 'foogallery_gallery_template_common_thumbnail_fields', $fields );

			//update specific fields
			foreach ($updated_fields as &$field) {
				if ( 'rounded_corners' === $field['id'] ) {
					$field['choices'] = array(
						''  => __( 'None', 'foogallery' ),
						'fg-round-small'  => __( 'Small', 'foogallery' ),
						'fg-round-medium'  => __( 'Medium', 'foogallery' ),
						'fg-round-large'  => __( 'Large', 'foogallery' ),
					);
				}
			}

			return $updated_fields;
		}

		/**
		 * Enqueue scripts that the default gallery template relies on
		 */
		function enqueue_dependencies( $gallery ) {
			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();
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
			$dimensions = $foogallery->get_meta( 'image-viewer_thumbnail_size', false );
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
			if ( 'fg-round-full' === $settings['image-viewer_rounded_corners'] ) {
				$settings['image-viewer_rounded_corners'] = 'fg-round-large';
			}

			return $settings;
		}
	}
}