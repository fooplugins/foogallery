<?php
/**
 * Class used to handle lazy loading for gallery templates
 * Date: 20/03/2017
 */
if ( ! class_exists( 'FooGallery_LazyLoad' ) ) {

	class FooGallery_LazyLoad {

		function __construct() {
//			if ( is_admin() ) {
//				//add extra fields to the templates that support lazy loading
//				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_lazyload_fields' ), 10, 2 );
//			}

			//adds the lazyload property to a FooGallery
			add_action( 'foogallery_foogallery_instance_after_load', array( $this, 'determine_lazyload' ), 10, 2 );

			//change the image src attribute to data attributes if lazy loading is enabled
			add_filter( 'foogallery_attachment_html_image_attributes', array($this, 'change_src_attributes'), 99, 3);

			//add the lazy load attributes to the gallery container
			add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lazyload_attributes' ), 10, 2 );

			//add the appropriate lazy load class
			add_filter( 'foogallery_build_class_attribute', array( $this, 'add_lazyload_class' ), 10, 2 );
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
					'desc'    => __( 'Lazy loading means images in your gallery are only loaded when they become visible in the browser. This significantly improves page loading times.', 'foogallery' ),
					'section' => __( 'Thumbnails', 'foogallery' ),
					'spacer'  => '<span class="spacer"></span>',
					'type'    => 'radio',
					'choices' => array(
						'yes'  => __( 'Enabled', 'foogallery' ),
						'no'   => __( 'Disabled', 'foogallery' )
					)
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
			//always enable lazyload by default
			$lazy_load = true;

			if ( true === foogallery_get_setting( 'disable_lazy_load', false ) ) {
				$lazy_load = false;
			}

			$foogallery->lazyload = apply_filters( 'foogallery_lazy_load', $lazy_load, $foogallery );
		}

		/**
		 * @param array $attr
		 * @param array $args
		 * @param FooGalleryAttachment $attachment
		 * @return mixed
		 */
		function change_src_attributes($attr, $args, $attachment) {
			global $current_foogallery;

			if ( isset( $current_foogallery->lazyload) && true === $current_foogallery->lazyload) {

				if ( isset( $attr['src'] ) ) {
					//rename src => data-src
					$src = $attr['src'];
					unset( $attr['src'] );
					$attr['data-src'] = $src;
				}

				if ( isset( $attr['srcset'] ) ) {
					//rename srcset => data-srcset
					$src = $attr['srcset'];
					unset( $attr['srcset'] );
					$attr['data-srcset'] = $src;
				}
			}

			return $attr;
		}


		/**
		 * Add the required lazy load attributes onto the gallery container div
		 *
		 * @param $attributes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function add_lazyload_attributes($attributes, $gallery) {
			if ( isset( $gallery->lazyload) && true === $gallery->lazyload) {
				$attributes['data-loader-options'] = '{\'lazy\':true}';
			}
			return $attributes;
		}

		/**
		 * Add the required lazy load class to the gallery
		 *
		 * @param $classes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function add_lazyload_class($classes, $gallery) {
			if ( isset( $gallery->lazyload) && true === $gallery->lazyload) {
				$classes[] = apply_filters( 'foogallery_lazyload_class', 'loaded-fade-in' );
			}
			return $classes;
		}
	}
}