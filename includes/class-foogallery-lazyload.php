<?php
/**
 * Class used to handle lazy loading for gallery templates
 * Date: 20/03/2017
 */
if ( ! class_exists( 'FooGallery_LazyLoad' ) ) {

	class FooGallery_LazyLoad {

		function __construct() {
			//change the image src attribute to data attributes if lazy loading is enabled
			add_filter( 'foogallery_attachment_html_image_attributes', array($this, 'change_src_attributes'), 99, 3);

			//add the lazy load attributes to the gallery container
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_lazyload_options' ), 10, 3 );
		}

		/**
		 * Determine if the gallery has lazy loading support
		 *
		 * @param $foogallery
		 * @param $foogallery_template
		 */
		function determine_lazyloading_support( $foogallery, $foogallery_template ) {

			//make sure we only do this once for better performance
			if ( !isset( $foogallery->lazyload ) ) {

				//load the gallery template
				$template_info = foogallery_get_gallery_template( $foogallery_template );

				//check if the template supports lazy loading
				$lazy_load = isset($template_info['lazyload_support']) &&
					true === $template_info['lazyload_support'];

				$foogallery->lazyload = apply_filters( 'foogallery_lazy_load', $lazy_load, $foogallery, $foogallery_template );
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
			global $current_foogallery_template;

			if ( $current_foogallery !== null ) {

				$this->determine_lazyloading_support( $current_foogallery, $current_foogallery_template );

				if ( isset( $current_foogallery->lazyload ) && true === $current_foogallery->lazyload ) {

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
			}

			return $attr;
		}


		/**
		 * Add the required lazy load options if needed
		 *
		 * @param $attributes array
		 * @param $gallery FooGallery
		 *
		 * @return array
		 */
		function add_lazyload_options($options, $gallery, $attributes) {
			global $current_foogallery_template;

			$this->determine_lazyloading_support( $gallery, $current_foogallery_template );

			if ( isset( $gallery->lazyload) && true === $gallery->lazyload) {
				$options['lazy'] = true;
			}
			return $options;
		}
	}
}