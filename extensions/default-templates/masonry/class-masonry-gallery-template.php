<?php

if ( !class_exists( 'FooGallery_Masonry_Gallery_Template' ) ) {

	define('FOOGALLERY_MASONRY_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Masonry_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_filter( 'foogallery_located_template-masonry', array( $this, 'enqueue_dependencies' ) );
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
					'slug'        => 'masonry',
					'name'        => __( 'Masonry Image Gallery', 'foogallery' ),
					'admin_js'	  => FOOGALLERY_MASONRY_GALLERY_TEMPLATE_URL . 'js/admin-gallery-masonry.js',
					'fields'	  => array(
							array(
									'id'      => 'thumbnail_width',
									'title'   => __( 'Thumbnail Width', 'foogallery' ),
									'desc'    => __( 'Choose the width of your thumbnails. Thumbnails will be generated on the fly and cached once generated.', 'foogallery' ),
									'type'    => 'number',
									'class'   => 'small-text',
									'default' => 150,
									'step'    => '1',
									'min'     => '0',
							),
							array(
									'id'      => 'layout',
									'title'   => __( 'Masonry Layout', 'foogallery' ),
									'desc'    => __( 'Choose a fixed width thumb layout, or responsive columns.', 'foogallery' ),
									'type'    => 'radio',
									'choices' => array(
											'fixed'  => __( 'Fixed Width', 'foogallery' ),
											'2col'   => __( '2 Columns', 'foogallery' ),
											'3col'   => __( '3 Columns', 'foogallery' ),
											'4col'   => __( '4 Columns', 'foogallery' ),
											'5col'   => __( '5 Columns', 'foogallery' )
									),
									'spacer'  => '<span class="spacer"></span>',
									'default' => 'fixed'
							),
							array(
									'id'      => 'gutter_width',
									'title'   => __( 'Gutter Width', 'foogallery' ),
									'desc'    => __( 'The spacing between your thumbnails. Only applicable when using a fixed layout!', 'foogallery' ),
									'type'    => 'number',
									'class'   => 'small-text',
									'default' => 10,
									'step'    => '1',
									'min'     => '0',
							),
							array(
									'id'      => 'center_align',
									'title'   => __( 'Image Alignment', 'foogallery' ),
									'desc'    => __( 'You can choose to center align your images or leave them at the default. Only applicable when using a fixed layout!', 'foogallery' ),
									'type'    => 'radio',
									'choices' => array(
											'default'  => __( 'Left Alignment', 'foogallery' ),
											'center'   => __( 'Center Alignment', 'foogallery' )
									),
									'spacer'  => '<span class="spacer"></span>',
									'default' => 'default'
							),
							array(
									'id'      => 'gutter_percent',
									'title'   => __( 'Gutter Size', 'foogallery' ),
									'desc'    => __( 'Choose a gutter size when using responsive columns.', 'foogallery' ),
									'type'    => 'radio',
									'choices' => array(
											'no-gutter'   => __( 'No Gutter', 'foogallery' ),
											''  => __( 'Normal Size Gutter', 'foogallery' ),
											'large-gutter'   => __( 'Larger Gutter', 'foogallery' )
									),
									'spacer'  => '<span class="spacer"></span>',
									'default' => ''
							),
							array(
									'id'      => 'hover_zoom',
									'title'   => __( 'Hover Zoom', 'foogallery' ),
									'desc'    => __( 'The effect that is applied to images when you move your mouse over them.', 'foogallery' ),
									'type'    => 'radio',
									'choices' => array(
											'default'  => __( 'Zoom Slightly', 'foogallery' ),
											'none'   => __( 'No Zoom', 'foogallery' )
									),
									'spacer'  => '<span class="spacer"></span>',
									'default' => 'default'
							),
							array(
									'id'      => 'thumbnail_link',
									'title'   => __( 'Thumbnail Link', 'foogallery' ),
									'default' => 'image' ,
									'type'    => 'thumb_link',
									'spacer'  => '<span class="spacer"></span>',
									'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' ),
							),
							array(
									'id'      => 'lightbox',
									'title'   => __( 'Lightbox', 'foogallery' ),
									'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
									'type'    => 'lightbox',
							),
					),
			);


			return $gallery_templates;
		}

		/**
		 * Enqueue scripts that the masonry gallery template relies on
		 */
		function enqueue_dependencies() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'masonry' );
			foogallery_enqueue_imagesloaded_script();
		}
	}
}