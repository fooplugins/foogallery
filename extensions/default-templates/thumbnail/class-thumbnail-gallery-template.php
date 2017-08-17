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

			//add extra fields to the templates
			add_filter( 'foogallery_override_gallery_template_fields-thumbnail', array( $this, 'add_common_thumbnail_fields' ), 10, 2 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-thumbnail', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-thumbnail', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );
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
                'lazyload_support' => true,
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
                            'crop' => true,
                        ),
						'row_data'=> array(
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
                    ),
                    array(
                        'id'      => 'caption_style',
                        'title'   => __( 'Caption Style', 'foogallery' ),
                        'section' => __( 'Captions', 'foogallery' ),
                        'desc'    => __( 'Choose which caption style you want to use.', 'foogallery' ),
                        'type'    => 'select',
                        'default' => 'simple',
                        'choices' => array(
                            'caption-simple' => __( 'Simple' , 'foogallery' ),
                            'caption-slideup' => __( 'Slide Up' , 'foogallery' ),
                            'caption-fall' => __( 'Fall Down' , 'foogallery' ),
                            'caption-fade' => __( 'Fade' , 'foogallery' ),
                            'caption-push' => __( 'Push From Left' , 'foogallery' ),
                            'caption-scale' => __( 'Scale' , 'foogallery' ),
                            'caption-none' => __( 'None' , 'foogallery' )
                        ),
                    ),
                    array(
                        'id'      => 'caption_title',
                        'title'   => __('Caption Title', 'foogallery'),
						'section' => __( 'General', 'foogallery' ),
                        'desc'    => __('Leave blank if you do not want a caption title.', 'foogallery'),
                        'type'    => 'text'
                    ),
                    array(
                        'id'      => 'caption_description',
                        'title'   => __('Caption Description', 'foogallery'),
						'section' => __( 'General', 'foogallery' ),
                        'desc'    => __('Leave blank if you do not want a caption description.', 'foogallery'),
                        'type'    => 'textarea'
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
		 * Add thumbnail fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_common_thumbnail_fields( $fields, $template ) {
			return apply_filters( 'foogallery_gallery_template_common_thumbnail_fields', $fields );
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_width'] = $post_data['foogallery_settings']['thumbnail_thumbnail_dimensions']['width'];
			$args['thumbnail_height'] = $post_data['foogallery_settings']['thumbnail_thumbnail_dimensions']['height'];
			$args['thumbnail_crop'] = isset( $post_data['foogallery_settings']['thumbnail_thumbnail_dimensions']['crop'] ) ? '1' : '0';

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
			return array(
				'height' => intval( $arguments['thumbnail_height'] ),
				'width'  => intval( $arguments['thumbnail_width'] ),
				'crop'   => $arguments['thumbnail_crop'] === '1'
			);
		}
	}
}