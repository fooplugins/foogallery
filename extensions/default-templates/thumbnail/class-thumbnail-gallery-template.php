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
					'preview_css' => FOOGALLERY_THUMBNAIL_GALLERY_TEMPLATE_URL . 'css/gallery-thumbnail.css',
					'admin_js'	  => FOOGALLERY_THUMBNAIL_GALLERY_TEMPLATE_URL . 'js/admin-gallery-thumbnail.js',
					'fields'	  => array(
							array(
									'id'	  => 'help',
									'type'	  => 'html',
									'help'	  => true,
									'desc'	  => __( 'This gallery template only shows a single thumbnail, but the true power shines through when the thumbnail is clicked, because then the lightbox takes over and the user can view all the images in the gallery.', 'foogallery' ),
							),
							array(
									'id'      => 'thumbnail_dimensions',
									'title'   => __( 'Size', 'foogallery' ),
									'desc'    => __( 'Choose the size of your thumbnail.', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'type'    => 'thumb_size',
									'default' => array(
											'width' => 250,
											'height' => 200,
											'crop' => true,
									),
							),
							array(
									'id'      => 'position',
									'title'   => __( 'Position', 'foogallery' ),
									'desc'    => __( 'The position of the thumbnail related to the content around it.', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'default' => 'position-block',
									'type'    => 'select',
									'choices' => array(
											'position-block' => __( 'Full Width (block)', 'foogallery' ),
											'position-float-left' => __( 'Float Left', 'foogallery' ),
											'position-float-right' => __( 'Float Right', 'foogallery' ),
									)
							),
							array(
									'id'      => 'link_custom_url',
									'title'   => __( 'Link To Custom URL', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'default' => '',
									'type'    => 'checkbox',
									'desc'	  => __( 'You can link your thumbnails to Custom URL\'s (if they are set on your attachments). Fallback will be to the full size image.', 'foogallery' )
							),
							array(
									'id'      => 'caption_style',
									'title'   => __( 'Caption Style', 'foogallery' ),
									'section' => __( 'Caption Settings', 'foogallery' ),
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
									'id'      => 'caption_bgcolor',
									'title'   => __('Caption Background Color', 'foogallery'),
									'section' => __( 'Caption Settings', 'foogallery' ),
									'desc'    => __('The color of the caption background.', 'foogallery'),
									'type'    => 'colorpicker',
									'default' => 'rgba(0, 0, 0, 0.8)',
									'opacity' => true
							),
							array(
									'id'      => 'caption_color',
									'title'   => __('Caption Text Color', 'foogallery'),
									'section' => __( 'Caption Settings', 'foogallery' ),
									'desc'    => __('The color of the caption text.', 'foogallery'),
									'type'    => 'colorpicker',
									'default' => 'rgb(255, 255, 255)'
							),
							array(
									'id'      => 'caption_title',
									'title'   => __('Caption Title', 'foogallery'),
									'section' => __( 'Caption Settings', 'foogallery' ),
									'desc'    => __('Leave blank if you do not want a caption title.', 'foogallery'),
									'type'    => 'text'
							),
							array(
									'id'      => 'caption_description',
									'title'   => __('Caption Description', 'foogallery'),
									'section' => __( 'Caption Settings', 'foogallery' ),
									'desc'    => __('Leave blank if you do not want a caption description.', 'foogallery'),
									'type'    => 'textarea'
							),
							array(
									'id'      => 'lightbox',
									'title'   => __( 'Lightbox', 'foogallery' ),
									'section' => __( 'Gallery Settings', 'foogallery' ),
									'desc'    => __( 'Choose which lightbox you want to use.', 'foogallery' ),
									'type'    => 'lightbox',
							)
					)
			);

			return $gallery_templates;
		}
	}
}