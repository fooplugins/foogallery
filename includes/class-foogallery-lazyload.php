<?php
/**
 * Class used to handle lazy loading for gallery templates
 * Date: 20/03/2017
 */
if ( ! class_exists( 'FooGallery_LazyLoad' ) ) {

	/**
	 * Class FooGallery_LazyLoad
	 */
	class FooGallery_LazyLoad {

		/**
		 * FooGallery_LazyLoad constructor.
		 */
		public function __construct() {
			// determine lazy loading for the gallery once up front before the template is loaded.
			add_action( 'foogallery_located_template', array( $this, 'determine_lazyloading_for_gallery' ) );

			// force lazy loading for stack album.
			add_action( 'foogallery_located_album_template-stack', array( $this, 'force_lazyloading_for_galleries' ) );

			// change the image src attribute to data attributes if lazy loading is enabled.
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'change_src_attributes' ), 99, 3 );

			// add the lazy load attributes to the gallery container.
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_lazyload_options' ), 10, 3 );

			// add common fields to the templates that support it.
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_lazyload_field' ), 100, 2 );

			// add some settings to allow forcing of the lazy loading to be disabled.
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_settings' ) );

			add_filter( 'foogallery_attachment_html_item_classes', array( $this, 'add_item_classes_for_lazy_loading' ), 10, 3 );
		}

		/**
		 * Add the classes needed for lazy loading onto the items
		 *
		 * @param array                $classes The array of classes to add to the item.
		 * @param FooGalleryAttachment $foogallery_attachment The current attachment we are working with.
		 * @param array                $args Any extra args.
		 *
		 * @return array The array of classes to add to the item.
		 */
		public function add_item_classes_for_lazy_loading( $classes, $foogallery_attachment, $args ) {
			global $current_foogallery;
			if ( $this->gallery_lazyload_enabled( $current_foogallery ) ) {
				$classes[] = 'fg-idle';
			} else {
				$classes[] = 'fg-loaded';
			}

			return $classes;
		}

		/**
		 * Force lazy loading for all galleries in the stack album
		 *
		 * @param FooGalleryAlbum $current_foogallery_album The current album that is being rendered.
		 */
		public function force_lazyloading_for_galleries( $current_foogallery_album ) {
			foreach ( $current_foogallery_album->galleries() as $gallery ) {
				$gallery->lazyload_support         = true;
				$gallery->lazyload_enabled         = true;
				$gallery->lazyload_forced_disabled = false;
			}
		}

		/**
		 * Determine all the lazy loading variables that can be set on a gallery
		 *
		 * @param $foogallery
		 */
		function determine_lazyloading_for_gallery( $foogallery ) {
			global $current_foogallery;
			global $current_foogallery_template;

			if ( $current_foogallery !== null ) {
				//make sure we only do this once for better performance
				if ( ! isset( $current_foogallery->lazyload_support ) ) {

					//load the gallery template
					$template_info = foogallery_get_gallery_template( $current_foogallery_template );

					//check if the template supports lazy loading
					$lazyloading_support = isset( $template_info['lazyload_support'] ) && true === $template_info['lazyload_support'];

					//set if lazy loading is supported for the gallery
					$current_foogallery->lazyload_support = apply_filters( 'foogallery_lazy_load', $lazyloading_support, $current_foogallery, $current_foogallery_template );

					//set if lazy loading is enabled for the gallery
					$lazyloading_default                  = '';
					$lazyloading_enabled                  = foogallery_gallery_template_setting( 'lazyload', $lazyloading_default ) === '';
					$current_foogallery->lazyload_enabled = $lazyloading_enabled;

					//set if lazy loading is forced to disabled for all galleries
					$lazyloading_forced_disabled                  = foogallery_get_setting( 'disable_lazy_loading' ) === 'on';
					$current_foogallery->lazyload_forced_disabled = $lazyloading_forced_disabled;

					//check if we are inside a feed
					if ( is_feed() ) {
						$current_foogallery->is_feed = true;
					}
				}
			}
		}

		/**
		 * Change the src and srcset attributes for lazy loading
		 *
		 * @param array                $attr
		 * @param array                $args
		 * @param FooGalleryAttachment $attachment
		 *
		 * @return mixed
		 */
		function change_src_attributes( $attr, $args, $attachment ) {
			global $current_foogallery;

			$replace_attributes = false;

			if ( null !== $current_foogallery ) {

				// check if we inside a feed and exit early.
				if ( isset( $current_foogallery->is_feed ) && true === $current_foogallery->is_feed ) {
					return $attr;
				}

				if ( $this->gallery_lazyload_enabled( $current_foogallery ) ) {
					$replace_attributes = true;
				}
			} else {
				// if we get here then check we are dealing with an album.
				global $current_foogallery_album;

				if ( null !== $current_foogallery_album ) {
					// check if we have lazy loading disabled.
					if ( foogallery_get_setting( 'disable_lazy_loading' ) !== 'on' ) {
						$replace_attributes = true;
					}
				}
			}

			if ( $replace_attributes ) {
				if ( isset( $attr['src'] ) ) {
					// rename src => data-src-fg.
					$src = $attr['src'];
					unset( $attr['src'] );
					$attr['data-src-fg'] = $src;
				}

				if ( isset( $attr['srcset'] ) ) {
					// rename srcset => data-srcset-fg.
					$src = $attr['srcset'];
					unset( $attr['srcset'] );
					$attr['data-srcset-fg'] = $src;
				}

				// add a placeholder src.
				if ( isset( $attr['width'] ) && isset( $attr['height'] ) ) {
					// set the src to a transparent SVG that has the correct width and height.
					$attr['src'] = foogallery_get_svg_placeholder_image( $attr['width'], $attr['height'] );
				}
			}

			return $attr;
		}

		/**
		 * Add the required lazy load options if needed
		 *
		 * @param $attributes array
		 * @param $gallery    FooGallery
		 *
		 * @return array
		 */
		function add_lazyload_options( $options, $gallery, $attributes ) {
			$lazyload_enabled = $this->gallery_lazyload_enabled( $gallery );
			$options['lazy']  = $lazyload_enabled;
			if ( ! $lazyload_enabled ) {
				$options['src']    = 'src';
				$options['srcset'] = 'srcset';
			}

			return $options;
		}

		/**
		 * @param $gallery FooGallery
		 */
		private function gallery_lazyload_enabled( $gallery ) {
			if ( isset( $gallery->lazyload_support ) && true === $gallery->lazyload_support ) {
				return $gallery->lazyload_enabled && ! $gallery->lazyload_forced_disabled;
			}

			return false;
		}

		/**
		 * Add lazyload field to the gallery template if supported
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_lazyload_field( $fields, $template ) {
			//check if the template supports lazy loading
			if ( $template && array_key_exists( 'lazyload_support', $template ) && true === $template['lazyload_support'] ) {

				$fields[] = array(
					'id'       => 'lazyload',
					'title'    => __( 'Lazy Loading', 'foogallery' ),
					'desc'     => __( 'If you choose to disable lazy loading, then all thumbnails will be loaded at once. This means you will lose the performance improvements that lazy loading gives you.', 'foogallery' ),
					'section'  => __( 'Advanced', 'foogallery' ),
					'type'     => 'radio',
					'spacer'   => '<span class="spacer"></span>',
					'default'  => '',
					'choices'  => array(
						''         => __( 'Enable Lazy Loading', 'foogallery' ),
						'disabled' => __( 'Disable Lazy Loading', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-preview'         => 'shortcode',
					),
				);
			}

			return $fields;
		}

		/**
		 * Add some global settings
		 *
		 * @param $settings
		 *
		 * @return array
		 */
		function add_settings( $settings ) {

			$lazy_settings[] = array(
				'id'      => 'disable_lazy_loading',
				'title'   => __( 'Disable Lazy Loading', 'foogallery' ),
				'desc'    => __( 'This will disable lazy loading for ALL galleries. This is not recommended, but is sometimes needed when there are problems with the galleries displaying on some installs.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'general',
				'section' => __( 'Performance', 'foogallery' ),
			);

			$new_settings = array_merge( $lazy_settings, $settings['settings'] );

			$settings['settings'] = $new_settings;

			return $settings;
		}
	}
}