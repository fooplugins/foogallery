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
			add_action( 'foogallery_render_gallery_template_field_custom', array( $this, 'render_thumbnail_preview' ), 10, 3 );

			add_filter( 'foogallery_template_thumbnail_dimensions-image-viewer', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );
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
				'lazyload_support' => true,
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
						'id'      => 'text-prev',
						'title'   => __( '"Prev" Text', 'foogallery' ),
						'section' => __( 'Language Settings', 'foogallery' ),
						'type'    => 'text',
						'default' =>  __('Prev', 'foogallery')
					),
					array(
						'id'      => 'text-of',
						'title'   => __( '"of" Text', 'foogallery' ),
						'section' => __( 'Language Settings', 'foogallery' ),
						'type'    => 'text',
						'default' =>  __('of', 'foogallery')
					),
					array(
						'id'      => 'text-next',
						'title'   => __( '"Next" Text', 'foogallery' ),
						'section' => __( 'Language Settings', 'foogallery' ),
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
			return apply_filters( 'foogallery_gallery_template_common_thumbnail_fields', $fields );
		}

		/**
		 * Enqueue scripts that the default gallery template relies on
		 */
		function enqueue_dependencies( $gallery ) {
			wp_enqueue_script( 'jquery' );

			//enqueue core files
			foogallery_enqueue_core_gallery_template_style();
			foogallery_enqueue_core_gallery_template_script();

			$css = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'image-viewer/css/foogallery.image-viewer.min.css';
			wp_enqueue_style( 'foogallery-image-viewer', $css, array( 'foogallery-core' ), FOOGALLERY_VERSION );

			$js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'image-viewer/js/foogallery.image-viewer.min.js';
			wp_enqueue_script( 'foogallery-image-viewer', $js, array( 'foogallery-core' ), FOOGALLERY_VERSION );
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
				$text_prev = $gallery->get_meta( 'image-viewer_text-prev', __('Prev', 'foogallery') );
				$text_of = $gallery->get_meta( 'image-viewer_text-of', __('of', 'foogallery') );
				$text_next = $gallery->get_meta( 'image-viewer_text-next', __('Next', 'foogallery') );

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
						<div class="fiv-prev"><span><?php echo $text_prev; ?></span></div>
						<label class="fiv-count"><span class="fiv-count-current">1</span><?php echo $text_of; ?><span>1</span></label>
						<div class="fiv-next"><span><?php echo $text_next; ?></span></div>
					</div>
				</div>
				</div><?php
			}
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
			$dimensions = $foogallery->get_meta( 'thumbnail_size', false );
			return $dimensions;
		}
	}
}