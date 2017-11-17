<?php

if ( !class_exists( 'FooGallery_Thumbnail_Gallery_Template' ) ) {

	define('FOOGALLERY_THUMBNAIL_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Thumbnail_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			add_filter( 'foogallery_located_template-thumbnail', array( $this, 'enqueue_dependencies' ) );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-thumbnail', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-thumbnail', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-thumbnail', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//alter the crop value if needed
			add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'alter_field_value'), 10, 4 );
		}

		function alter_field_value( $value, $field, $gallery, $template ) {
		    //only do something if we are dealing with the thumbnail_dimensions field in this template
		    if ( 'thumbnail' === $template['slug'] && 'thumbnail_dimensions' === $field['id'] ) {
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
			$gallery_templates[] = array(
                'slug'        => 'thumbnail',
                'name'        => __( 'Single Thumbnail Gallery', 'foogallery' ),
				'preview_support' => true,
				'common_fields_support' => true,
                'lazyload_support' => true,
				'mandatory_classes' => 'fg-thumbnail',
				'thumbnail_dimensions' => true,
                'fields'	  => array(
                    array(
                        'id'	  => 'help',
                        'type'	  => 'html',
                        'section' => __( 'General', 'foogallery' ),
                        'help'	  => true,
                        'desc'	  => __( 'This gallery template only shows a single thumbnail, but the true power shines through when the thumbnail is clicked, because then the lightbox takes over and the user can view all the images in the gallery.', 'foogallery' ),
                    ),
                    array(
                        'id'      => 'thumbnail_dimensions',
                        'title'   => __( 'Size', 'foogallery' ),
                        'desc'    => __( 'Choose the size of your thumbnail.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'type'    => 'thumb_size',
                        'default' => array(
                            'width' => 250,
                            'height' => 200,
                            'crop' => true
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'position',
                        'title'   => __( 'Position', 'foogallery' ),
                        'desc'    => __( 'The position of the thumbnail related to the content around it.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'position-block',
                        'type'    => 'select',
                        'choices' => array(
                            'fg-center' => __( 'Full Width (block)', 'foogallery' ),
                            'fg-left' => __( 'Float Left', 'foogallery' ),
                            'fg-right' => __( 'Float Right', 'foogallery' ),
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-preview' => 'class'
						)
                    ),
                    array(
                        'id'      => 'link_custom_url',
                        'title'   => __( 'Link To Custom URL', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => '',
                        'type'    => 'checkbox',
                        'desc'	  => __( 'You can link your thumbnails to Custom URL\'s (if they are set on your attachments). Fallback will be to the full size image.', 'foogallery' )
                    ),
                    array(
                        'id'      => 'lightbox',
                        'title'   => __( 'Lightbox', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'desc'    => __( 'Choose which lightbox you want to use.', 'foogallery' ),
                        'type'    => 'lightbox',
                        'default' => 'none',
                    ),
                    array(
                        'id'      => 'caption_title',
                        'title'   => __('Override Title', 'foogallery'),
						'section' => __( 'Captions', 'foogallery' ),
                        'desc'    => __('Leave blank if you do not want a caption title.', 'foogallery'),
                        'type'    => 'text',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'caption_description',
                        'title'   => __('Override Description', 'foogallery'),
						'section' => __( 'Captions', 'foogallery' ),
                        'desc'    => __('Leave blank if you do not want a caption description.', 'foogallery'),
                        'type'    => 'textarea',
						'row_data'=> array(
							'data-foogallery-change-selector' => 'textarea',
							'data-foogallery-preview' => 'shortcode'
						)
                    )
                )
			);

			return $gallery_templates;
		}

		/**
		 * Enqueue scripts that the masonry gallery template relies on
		 */
		function enqueue_dependencies() {
			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_dimensions'] = $post_data[FOOGALLERY_META_SETTINGS]['thumbnail_thumbnail_dimensions'];
			$args['caption_title'] = $post_data[FOOGALLERY_META_SETTINGS]['thumbnail_caption_title'];
			$args['caption_description'] = $post_data[FOOGALLERY_META_SETTINGS]['thumbnail_caption_description'];
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
                    'crop' => $arguments['thumbnail_dimensions']['crop']
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
			$dimensions = $foogallery->get_meta( 'thumbnail_thumbnail_dimensions', array(
				'width' => get_option( 'thumbnail_size_w' ),
				'height' => get_option( 'thumbnail_size_h' ),
                'crop' => true
			) );
			if ( !array_key_exists( 'crop', $dimensions ) ) {
			    $dimensions['crop'] = true;
            }
			return $dimensions;
		}
	}
}