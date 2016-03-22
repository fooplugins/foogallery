<?php

if ( !class_exists( 'FooGallery_Default_Gallery_Template' ) ) {

	define('FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Default_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_thumbnail_preview' ), 10, 3 );
			add_filter( 'foogallery_located_template-default', array( $this, 'enqueue_dependencies' ) );
			add_filter( 'foogallery_template_load_js-default', array( $this, 'can_enqueue_template_js' ), 10, 2 );
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
					'slug'        => 'default',
					'name'        => __( 'Responsive Image Gallery', 'foogallery' ),
					'preview_css' => FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL . 'css/gallery-default.css',
					'admin_js'	  => FOOGALLERY_DEFAULT_GALLERY_TEMPLATE_URL . 'js/admin-gallery-default.js',
					'fields'	  => array(
							array(
									'id'      => 'lightbox',
									'title'   => __( 'Lightbox', 'foogallery' ),
									'desc'    => __( 'Choose which lightbox you want to use. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
									'type'    => 'lightbox',
							),
							array(
									'id'      => 'spacing',
									'title'   => __( 'Spacing', 'foogallery' ),
									'desc'    => __( 'The spacing or gap between thumbnails in the gallery.', 'foogallery' ),
									'type'    => 'select',
									'default' => 'spacing-width-10',
									'choices' => array(
											'spacing-width-0' => __( '0 pixels', 'foogallery' ),
											'spacing-width-5' => __( '5 pixels', 'foogallery' ),
											'spacing-width-10' => __( '10 pixels', 'foogallery' ),
											'spacing-width-15' => __( '15 pixels', 'foogallery' ),
											'spacing-width-20' => __( '20 pixels', 'foogallery' ),
											'spacing-width-25' => __( '25 pixels', 'foogallery' ),
									),
							),
							array(
									'id'      => 'alignment',
									'title'   => __( 'Alignment', 'foogallery' ),
									'desc'    => __( 'The horizontal alignment of the thumbnails inside the gallery.', 'foogallery' ),
									'default' => 'alignment-center',
									'type'    => 'select',
									'choices' => array(
											'alignment-left' => __( 'Left', 'foogallery' ),
											'alignment-center' => __( 'Center', 'foogallery' ),
											'alignment-right' => __( 'Right', 'foogallery' ),
									)
							),
							array(
									'id'      => 'loading_animation',
									'title'   => __( 'Loading Indicator', 'foogallery' ),
									'default' => 'yes',
									'type'    => 'radio',
									'choices' => array(
											'yes'  => __( 'Show Thumbnail Loading Indicator', 'foogallery' ),
											'no'   => __( 'Disabled', 'foogallery' )
									),
									'spacer'  => '<span class="spacer"></span>',
									'desc'	  => __( 'By default, an animated loading animation indicator is shown before the thumbnails have loaded. You can disable the loader if you want.', 'foogallery' ),
							),
							array(
									'id'      => 'thumbnail_dimensions',
									'title'   => __( 'Size', 'foogallery' ),
									'desc'    => __( 'Choose the size of your thumbnails.', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'type'    => 'thumb_size',
									'default' => array(
											'width' => get_option( 'thumbnail_size_w' ),
											'height' => get_option( 'thumbnail_size_h' ),
											'crop' => true,
									),
							),
							array(
									'id'      => 'thumbnail_link',
									'title'   => __( 'Link', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'default' => 'image',
									'type'    => 'thumb_link',
									'spacer'  => '<span class="spacer"></span>',
									'desc'	  => __( 'You can choose to link each thumbnail to the full size image, the image\'s attachment page, a custom URL, or you can choose to not link to anything.', 'foogallery' ),
							),
							array(
									'id'      => 'border-style',
									'title'   => __( 'Border Style', 'foogallery' ),
									'desc'    => __( 'The border style for each thumbnail in the gallery.', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'type'    => 'icon',
									'default' => 'border-style-square-white',
									'choices' => array(
											'border-style-square-white' => array( 'label' => __( 'Square white border with shadow' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-square-white.png' ),
											'border-style-circle-white' => array( 'label' => __( 'Circular white border with shadow' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-circle-white.png' ),
											'border-style-square-black' => array( 'label' => __( 'Square Black' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-square-black.png' ),
											'border-style-circle-black' => array( 'label' => __( 'Circular Black' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-circle-black.png' ),
											'border-style-inset' => array( 'label' => __( 'Square Inset' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-square-inset.png' ),
											'border-style-rounded' => array( 'label' => __( 'Plain Rounded' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-plain-rounded.png' ),
											'' => array( 'label' => __( 'Plain' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-none.png' ),
									)
							),
							array(
									'id'      => 'hover-effect-type',
									'title'   => __( 'Hover Effect Type', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'default' => '',
									'type'    => 'radio',
									'choices' => apply_filters( 'foogallery_gallery_template_hover-effect-types', array(
											''  => __( 'Icon', 'foogallery' ),
											'hover-effect-tint'   => __( 'Dark Tint', 'foogallery' ),
											'hover-effect-color' => __( 'Colorize', 'foogallery' ),
											'hover-effect-caption' => __( 'Caption', 'foogallery' ),
											'hover-effect-none' => __( 'None', 'foogallery' )
									) ),
									'spacer'  => '<span class="spacer"></span>',
									'desc'	  => __( 'The type of hover effect the thumbnails will use.', 'foogallery' ),
							),
							array(
									'id'      => 'hover-effect',
									'title'   => __( 'Icon Hover Effect', 'foogallery' ),
									'desc'    => __( 'When the hover effect type of Icon is chosen, you can choose which icon is shown when you hover over each thumbnail.', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'type'    => 'icon',
									'default' => 'hover-effect-zoom',
									'choices' => array(
											'hover-effect-zoom' => array( 'label' => __( 'Zoom' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom.png' ),
											'hover-effect-zoom2' => array( 'label' => __( 'Zoom 2' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom2.png' ),
											'hover-effect-zoom3' => array( 'label' => __( 'Zoom 3' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom3.png' ),
											'hover-effect-plus' => array( 'label' => __( 'Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-plus.png' ),
											'hover-effect-circle-plus' => array( 'label' => __( 'Cirlce Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-circle-plus.png' ),
											'hover-effect-eye' => array( 'label' => __( 'Eye' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-eye.png' )
									),
							),
							array(
									'id'      => 'caption-hover-effect',
									'title'   => __( 'Caption Effect', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'default' => 'hover-caption-simple',
									'type'    => 'radio',
									'choices' => apply_filters( 'foogallery_gallery_template_caption-hover-effects', array(
											'hover-caption-simple'  => __( 'Simple', 'foogallery' ),
											'hover-caption-full-drop'   => __( 'Drop', 'foogallery' ),
											'hover-caption-full-fade' => __( 'Fade In', 'foogallery' ),
											'hover-caption-push' => __( 'Push', 'foogallery' ),
											'hover-caption-simple-always' => __( 'Always Visible', 'foogallery' )
									) ),
									'spacer'  => '<span class="spacer"></span>'
							),
							array(
									'id'      => 'caption-content',
									'title'   => __( 'Caption Content', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'default' => 'title',
									'type'    => 'radio',
									'choices' => apply_filters( 'foogallery_gallery_template_caption-content', array(
											'title'  => __( 'Title Only', 'foogallery' ),
											'desc'   => __( 'Description Only', 'foogallery' ),
											'both' => __( 'Title and Description', 'foogallery' )
									) ),
									'spacer'  => '<span class="spacer"></span>'
							),
							array(
									'id' => 'thumb_preview',
									'title' => __( 'Preview', 'foogallery' ),
									'desc' => __( 'This is what your gallery thumbnails will look like.', 'foogallery' ),
									'section' => __( 'Thumbnail Settings', 'foogallery' ),
									'type' => 'default_thumb_preview',
							)
					)
			);

			return $gallery_templates;
		}

		/**
		 * Renders the thumbnail preview field
		 *
		 * @param $field array
		 * @param $gallery FooGallery
		 * @param $template array
		 */
		function render_thumbnail_preview( $field, $gallery, $template ) {
			if ( 'default_thumb_preview' == $field['type'] ) {
				$args = $gallery->get_meta( 'default_thumbnail_dimensions', array(
						'width' => get_option( 'thumbnail_size_w' ),
						'height' => get_option( 'thumbnail_size_h' ),
						'crop' => true
				) );

				//override the link so that it does not actually open an image
				$args['link'] = 'custom';
				$args['custom_link'] = '#preview';

				$hover_effect = $gallery->get_meta( 'default_hover-effect', 'hover-effect-zoom' );
				$border_style = $gallery->get_meta( 'default_border-style', 'border-style-square-white' );
				$hover_effect_type = $gallery->get_meta( 'default_hover-effect-type', '' );
				$caption_hover_effect = $gallery->get_meta( 'default_caption-hover-effect', 'hover-caption-simple' );

				$featured = $gallery->featured_attachment();

				if ( false === $featured ) {
					$featured = new FooGalleryAttachment();
					$featured->url = FOOGALLERY_URL . 'assets/test_thumb_1.jpg';
					$featured->caption = __( 'Caption Title', 'foogallery' );
					$featured->description = __( 'Long Caption Description Text', 'foogallery' );
				}

				echo '<div class="foogallery-default-preview ' . foogallery_build_class_attribute( $gallery, $hover_effect, $border_style, $hover_effect_type, $caption_hover_effect, 'foogallery-thumbnail-preview' ) . '">';
				echo $featured->html( $args, true, false );
				echo $featured->html_caption( 'both' );
				echo '</a>';
				echo '</div>';
			}
		}

		/**
		 * Enqueue scripts that the default gallery template relies on
		 */
		function enqueue_dependencies( $gallery ) {
			if ( 'yes' === $gallery->get_meta( 'default_loading_animation', 'yes' ) ) {
				wp_enqueue_script( 'jquery' );
				foogallery_enqueue_imagesloaded_script();
			}
		}

		/**
		 * @param $include bool By default we will try to include the template JS
		 * @param $gallery FooGallery the gallery instance we are loading
		 *
		 * @return bool if we want to try to include the template JS
		 */
		function can_enqueue_template_js( $include, $gallery ) {
			if ( 'yes' === $gallery->get_meta( 'default_loading_animation', 'yes' ) ) {
				return true;
			}
			return false;
		}
	}
}