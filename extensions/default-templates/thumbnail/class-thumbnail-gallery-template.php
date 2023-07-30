<?php

if ( !class_exists( 'FooGallery_Thumbnail_Gallery_Template' ) ) {

	define('FOOGALLERY_THUMBNAIL_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Thumbnail_Gallery_Template {

		const template_id = 'thumbnail';

		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-thumbnail', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//build up the thumb dimensions on save
			add_filter( 'foogallery_template_thumbnail_dimensions-thumbnail', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			//add additional link attributes to each item
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_link_attributes'), 10, 3 );

			//build up the arguments needed for rendering this template
			add_filter( 'foogallery_gallery_template_arguments-thumbnail', array( $this, 'build_gallery_template_arguments' ) );
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

			if ( foogallery_gallery_template_setting( 'link_custom_url', '' ) === 'on' ) {
				$args['link'] = 'custom';
			}

			return $args;
		}

		/**
		 * Add a rel attribute to all image links to make the lightboxes work
		 *
		 * @param $attr
		 * @param $args
		 * @param $foogallery_attachment FooGalleryAttachment
		 *
		 * @return mixed
		 */
        function alter_link_attributes($attr, $args, $foogallery_attachment) {
	        global $current_foogallery;
	        if ( isset( $current_foogallery ) && self::template_id === $current_foogallery->gallery_template ) {

	        	//always set the rel so that lightboxes will group the images
		        $attr['rel'] = 'lightbox[' . $current_foogallery->ID . ']';

		        //check if we must hide the featured image within the lightbox
		        if ( isset( $foogallery_attachment->featured ) && foogallery_gallery_template_setting( 'exclude_featured_image', '' ) === 'on' ) {
			        $attr['class'] = 'fg-panel-hide';
		        }
	        }

			return $attr;
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
                'paging_support' => false,
                'enqueue_core' => true,
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
                        'id'      => 'position',
                        'title'   => __( 'Position', 'foogallery' ),
                        'desc'    => __( 'The position of the thumbnail related to the content around it.', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => 'fg-center',
                        'type'    => 'select',
                        'choices' => array(
                            'fg-center' => __( 'Full Center', 'foogallery' ),
							'fg-left' => __( 'Full Left', 'foogallery' ),
							'fg-right' => __( 'Full Right', 'foogallery' ),
							'fg-float-left' => __( 'Float Left', 'foogallery' ),
                            'fg-float-right' => __( 'Float Right', 'foogallery' ),
                        ),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-preview' => 'shortcode'
						)
                    ),
                    array(
                        'id'      => 'link_custom_url',
                        'title'   => __( 'Link To Custom URL', 'foogallery' ),
                        'section' => __( 'General', 'foogallery' ),
                        'default' => '',
                        'type'    => 'checkbox',
                        'desc'	  => __( 'You can link your thumbnails to Custom URL\'s (if they are set on your attachments). Fallback will be to the full size image.', 'foogallery' ),
                        'row_data'=> array(
	                        'data-foogallery-change-selector' => 'input',
	                        'data-foogallery-preview' => 'shortcode',
	                        'data-foogallery-value-selector' => 'input:checked',
                        )
                    ),
                    array(
                        'id'      => 'lightbox',
                        'type'    => 'lightbox',
                    ),
	                array(
		                'id'      => 'exclude_featured_image',
		                'title'   => __( 'Exclude Featured Image', 'foogallery' ),
		                'section' => __( 'General', 'foogallery' ),
		                'default' => '',
		                'type'    => 'checkbox',
		                'desc'	  => __( 'You can exclude the featured image from the images shown in the lightbox.', 'foogallery' ),
		                'row_data'=> array(
			                'data-foogallery-hidden'                   => true,
			                'data-foogallery-show-when-field'          => 'lightbox',
			                'data-foogallery-show-when-field-operator' => '===',
			                'data-foogallery-show-when-field-value'    => 'foogallery',
			                'data-foogallery-change-selector'          => 'input',
			                'data-foogallery-preview'                  => 'shortcode',
			                'data-foogallery-value-selector'           => 'input:checked',
		                )
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
                    'crop' => true
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