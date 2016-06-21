<?php

if ( !class_exists( 'FooGallery_Image_Viewer_Gallery_Template' ) ) {

	define('FOOGALLERY_IMAGE_VIEWER_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Image_Viewer_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_thumbnail_preview' ), 10, 3 );
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
				'slug'        => 'image-viewer',
				'name'        => __( 'Image Viewer', 'foogallery-image-viewer'),
				'preview_css' => FOOGALLERY_IMAGE_VIEWER_GALLERY_TEMPLATE_URL . 'css/gallery-image-viewer.css',
				'admin_js'	  => FOOGALLERY_IMAGE_VIEWER_GALLERY_TEMPLATE_URL . 'js/admin-gallery-image-viewer.js',
				'fields'	  => array(
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
						'id'      => 'lightbox',
						'title'   => __('Lightbox', 'foogallery-image-viewer'),
						'desc'    => __('Choose which lightbox you want to use in the gallery.', 'foogallery-image-viewer'),
						'type'    => 'lightbox'
					),
					array(
						'id'      => 'theme',
						'title'   => __('Theme', 'foogallery'),
						'default' => '',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'Light', 'foogallery' ),
							'fiv-dark' => __( 'Dark', 'foogallery' ),
							'fiv-custom' => __( 'Custom', 'foogallery' )
						)
					),
					array(
						'id'      => 'theme_custom_bgcolor',
						'title'   => __('Background Color', 'foogallery'),
						'section' => __( 'Custom Theme Colors', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#FFFFFF',
						'opacity' => true
					),
					array(
						'id'      => 'theme_custom_textcolor',
						'title'   => __('Text Color', 'foogallery'),
						'section' => __( 'Custom Theme Colors', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#1b1b1b',
						'opacity' => true
					),
					array(
						'id'      => 'theme_custom_hovercolor',
						'title'   => __('Hover BG Color', 'foogallery'),
						'section' => __( 'Custom Theme Colors', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#F2F2F2',
						'opacity' => true
					),
					array(
						'id'      => 'theme_custom_bordercolor',
						'title'   => __('Border Color', 'foogallery'),
						'section' => __( 'Custom Theme Colors', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#e6e6e6',
						'opacity' => true
					),
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery-image-viewer'),
						'section' => __( 'Thumbnail Settings', 'foogallery' ),
						'desc'    => __('Choose the size of your thumbs.', 'foogallery-image-viewer'),
						'type'    => 'thumb_size',
						'default' => array(
							'width' => 640,
							'height' => 360,
							'crop' => true
						)
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery-image-viewer'),
						'section' => __( 'Thumbnail Settings', 'foogallery' ),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'spacer'  => '<span class="spacer"></span>',
						'desc'	  => __('You can choose to either link each thumbnail to the full size image or to the image\'s attachment page.', 'foogallery-image-viewer')
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
							'hover-effect-none' => __( 'None', 'foogallery' )
						) ),
						'spacer'  => '<span class="spacer"></span>',
						'desc'	  => __( 'The type of hover effect the thumbnails will use.', 'foogallery' )
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
							'hover-effect-circle-plus' => array( 'label' => __( 'Circle Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-circle-plus.png' ),
							'hover-effect-eye' => array( 'label' => __( 'Eye' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-eye.png' )
						),
					),
					array(
						'id'      => 'caption-content',
						'title'   => __( 'Caption Content', 'foogallery' ),
						'section' => __( 'Thumbnail Settings', 'foogallery' ),
						'default' => 'title',
						'type'    => 'radio',
						'choices' => apply_filters( 'foogallery_gallery_template_caption-content', array(
							'none'  => __( 'None', 'foogallery' ),
							'title'  => __( 'Title Only', 'foogallery' ),
							'desc'   => __( 'Description Only', 'foogallery' ),
							'both' => __( 'Title and Description', 'foogallery' )
						) ),
						'spacer'  => '<span class="spacer"></span>'
					),
					array(
						'id' => 'thumb_preview',
						'title' => __( 'Preview', 'foogallery' ),
						'desc' => __( 'This is what your gallery will look like.', 'foogallery' ),
						'section' => __( 'Thumbnail Settings', 'foogallery' ),
						'type' => 'image_viewer_preview',
					)
				)
			);

			return $gallery_templates;
		}

		function render_thumbnail_preview( $field, $gallery, $template ) {
			if ( 'image_viewer_preview' == $field['type'] ) {
				$args = $gallery->get_meta( 'thumbnail_size', array(
						'width' => 640,
						'height' => 360,
						'crop' => true
				) );
				//override the link so that it does not actually open an image
				$args['link'] = 'custom';
				$args['custom_link'] = '#preview';
				$args['link_attributes'] = array(
						'class' => 'fiv-active'
				);

				$hover_effect = $gallery->get_meta( 'image-viewer_hover-effect', 'hover-effect-zoom' );
				$hover_effect_type = $gallery->get_meta( 'image-viewer_hover-effect-type', '' );

				$featured = $gallery->featured_attachment();

				if ( false === $featured ) {
					$featured = new FooGalleryAttachment();
					$featured->url = FOOGALLERY_URL . 'assets/test_thumb_1.jpg';
				}

				?><div class="foogallery-image-viewer-preview <?php echo foogallery_build_class_attribute( $gallery, $hover_effect, $hover_effect_type ); ?>">
				<div class="fiv-inner">
					<div class="fiv-inner-container">
						<?php
						echo $featured->html( $args, true, false );
						echo $featured->html_caption( 'both' );
						echo '</a>';
						?>
					</div>
					<div class="fiv-ctrls">
						<div class="fiv-prev"><span><?php echo __('Prev') ?></span></div>
						<label class="fiv-count"><span class="fiv-count-current">1</span><?php echo __('of') ?><span>1</span></label>
						<div class="fiv-next"><span><?php echo __('Next') ?></span></div>
					</div>
				</div>
				</div><?php
			}
		}

		/**
		 * Image viewer relies on there being no width or height attributes on the IMG element so strip them out here.
		 *
		 * @param $attr
		 * @param $args
		 * @param $attachment
		 *
		 * @return mixed
		 */
		function strip_size($attr, $args, $attachment){
			global $current_foogallery_template;

			if ( 'image-viewer' === $current_foogallery_template ) {
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