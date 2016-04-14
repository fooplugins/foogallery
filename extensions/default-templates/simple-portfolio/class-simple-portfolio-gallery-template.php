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
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'strip_size' ), 99, 3 );
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
					'fields'	  => array(
							array(
									'id'	  => 'help',
									'title'	  => __( 'Tip', 'foogallery' ),
									'type'	  => 'html',
									'help'	  => true,
									'desc'	  => __( 'The Simple Portfolio template works best when you have <strong>captions and descriptions</strong> set for every attachment in the gallery.<br />To change captions and descriptions, simply hover over the thumbnail above and click the "i" icon.', 'foogallery' ),
							),
							array(
									'id'      => 'thumbnail_dimensions',
									'title'   => __( 'Thumbnail Size', 'foogallery' ),
									'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
									'type'    => 'thumb_size',
									'default' => array(
											'width' => 250,
											'height' => 200,
											'crop' => true,
									),
							),
							array(
									'id'      => 'thumbnail_link',
									'title'   => __( 'Thumbnail Link', 'foogallery' ),
									'default' => 'image',
									'type'    => 'thumb_link',
									'spacer'  => '<span class="spacer"></span>',
									'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' )
							),
							array(
									'id'      => 'lightbox',
									'title'   => __( 'Lightbox', 'foogallery' ),
									'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
									'type'    => 'lightbox',
							),
							array(
									'id'      => 'gutter',
									'title'   => __( 'Gutter', 'foogallery' ),
									'desc'    => __( 'The spacing between each thumbnail in the gallery.', 'foogallery' ),
									'type'    => 'number',
									'class'   => 'small-text',
									'default' => 40,
									'step'    => '1',
									'min'     => '0',
							),
							array(
								'id'      => 'caption_position',
								'title' => __('Caption Position', 'foogallery'),
								'desc' => __('Where the captions are displayed in relation to the thumbnail.', 'foogallery'),
								'default' => '',
								'type'    => 'radio',
								'spacer'  => '<span class="spacer"></span>',
								'choices' => array(
									'' => __( 'Below', 'foogallery' ),
									'bf-captions-above' => __( 'Above', 'foogallery' )
								)
							)
					),
			);

			return $gallery_templates;
		}

		/**
		 * Simple portfolio relies on there being no width or height attributes on the IMG element so strip them out here.
		 *
		 * @param $attr
		 * @param $args
		 * @param $attachment
		 *
		 * @return mixed
		 */
		function strip_size($attr, $args, $attachment){
			global $current_foogallery_template;

			if ( 'simple_portfolio' === $current_foogallery_template ) {
				if ( isset($attr['width']) ){
					unset($attr['width']);
				}
				if ( isset($attr['height']) ){
					unset($attr['height']);
				}
			}

			return $attr;
		}
	}
}