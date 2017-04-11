<?php
/**
 * Class used to handle lazy loading for gallery templates
 * Date: 20/03/2017
 */
if ( ! class_exists( 'FooGallery_LazyLoad' ) ) {

	class FooGallery_LazyLoad {

		function __construct() {
			if ( is_admin() ) {
				//add extra fields to the templates that support lazy loading
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_lazyload_fields' ), 10, 2 );
			}

			add_action( 'foogallery_foogallery_instance_after_load', array( $this, 'determine_lazyload' ), 10, 2 );

			//change the image src attribute to data attributes if lazy loading is enabled
			add_filter('foogallery_attachment_html_image_attributes', array($this, 'change_src_attributes'), 99, 3);
		}

		/**
		 * Add lazy load fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_lazyload_fields( $fields, $template ) {
			if ( $template && array_key_exists( 'lazyload_support', $template ) && true === $template['lazyload_support'] ) {
				$fields[] = array(
					'id'      => 'lazyload',
					'title'   => __( 'Lazy Loading', 'foogallery' ),
					'desc'    => __( 'Lazy loading means images in your gallery are only loaded when they are visible in browser. This significantly improves page loading times.', 'foogallery' ),
					'section' => __( 'Gallery Settings', 'foogallery' ),
					'type'    => 'radio',
					'choices' => array(
						'yes'  => __( 'Enable Lazy Loading', 'foogallery' ),
						'no'   => __( 'Disabled', 'foogallery' )
					),
					'spacer'  => '<span class="spacer"></span>'
				);
			}

			return $fields;
		}

		/**
		 * Determine if the gallery has lazy loading enabled
		 *
		 * @param $foogallery
		 * @param $post
		 */
		function determine_lazyload( $foogallery, $post ) {
			$gallery_template = $foogallery->gallery_template;

			$setting_key = "{$gallery_template}_lazyload";

			if ( 'yes' === $foogallery->get_meta( $setting_key, 'yes' ) ) {
				$foogallery->lazyload = true;
			}
		}

		/**
		 * @param array $attr
		 * @param array $args
		 * @param FooGalleryAttachment $attachment
		 * @return mixed
		 */
		function change_src_attributes($attr, $args, $attachment) {
			global $current_foogallery;

			if ( true === $current_foogallery->lazyload) {
				//rename src => data-src
				$src = $attr['src'];
				unset( $attr['src'] );
				$attr['data-src'] = $src;

				//rename srcset => data-srcset
				$src = $attr['srcset'];
				unset( $attr['srcset'] );
				$attr['data-srcset'] = $src;
			}

			return $attr;
		}
	}
}