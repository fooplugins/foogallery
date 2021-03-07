<?php

/**
 * Class for the shortpixel adaptive image thumbnails.
 *
 * https://help.shortpixel.com/article/201-shortpixel-adaptive-images-api-parameters
 */
if ( ! class_exists( 'FooGallery_Thumb_Engine_Shortpixel' ) ) {

	class FooGallery_Thumb_Engine_Shortpixel extends FooGallery_Thumb_Engine {

		function init() {
			add_filter( 'foogallery_admin_settings_override', array( $this, 'add_shortpixel_settings' ), 10, 1 );
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_href_attribute' ), 10, 3);
		}

		/**
		 * Generates the ShortPixel URL
		 *
		 * @param       $url
		 * @param array $args
		 *
		 * @return string
		 */
		function generate( $url, $args = array() ) {

			//make sure the url is the full url
			$url = $this->normalize_url( $url );

			$result = 'https://cdn.shortpixel.ai/spai/';

			$params = array();

			if ( array_key_exists( 'width', $args ) ) {
				$width  = (int) $args['width'];
				if ( $width > 0 ) {
					$params[] = 'w_' . $width;
				}
			}
			if ( array_key_exists( 'height', $args ) ) {
				$height = (int) $args['height'];
				if ( $height > 0 ) {
					$params[] = 'h_' . $height;
				}
			}

			//TODO : get image quality from settings
			$quality = foogallery_get_setting( 'shortpixel_quality', 'lossy'); //lqip, lossless, glossy or lossy
			if ( !empty( $quality ) ) {
				$params[] = 'q_' . $quality;
			}

			$return_type = foogallery_get_setting( 'shortpixel_return', ''); //blank, img, wait
			if ( !empty( $return_type ) ) {
				$params[] = 'ret_' . $return_type;
			}

			$conversion = foogallery_get_setting( 'shortpixel_conversion', ''); //webp or avif
			if ( !empty( $conversion ) ) {
				$params[] = 'to_' . $conversion;
			}

			return $result . implode( '+', $params ) . '/' . $url;
		}

		/**
		 * Normalizes a URL to ensure it is the full URL
		 *
		 * @param $url
		 *
		 * @return mixed|string|void
		 */
		function normalize_url( $url ) {
			if ( ! empty( $url ) ) {
				$parsed_site_url = parse_url( site_url() );

				$result = $url;

				if ( 0 === strpos( $url, '//' ) ) { //check if the url is protocol-relative
					return $parsed_site_url['scheme'] . ':' . $url;
				} elseif ( 0 === strpos( $url, '/' ) ) { //check if the url is root-relative
					$result = $parsed_site_url['scheme'] . '://' . $parsed_site_url['host'];
					// Add the path for subfolder installs.
					if ( isset( $parsed_site_url['path'] ) ) {
						$result .= $parsed_site_url['path'];
					}
					$result .= $url;
				}

				return apply_filters('foogallery_thumb_engine_shortpixel_normalize_url', $result );
			}
			return $url;
		}

		/**
		 * Alters the href for a gallery item to use the ShortPixel URL rather
		 *
		 * @param $attr
		 * @param $args
		 * @param $foogallery_attachment
		 *
		 * @return array|mixed
		 */
		function alter_href_attribute( $attr, $args, $foogallery_attachment ) {

			if ( is_array( $attr) && array_key_exists('href', $attr ) ) {
				$attr['href'] = $this->generate( $attr['href'] );
			}

			return $attr;
		}

		/**
		 * Adds ShortPixel settings to the admin page
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function add_shortpixel_settings( $settings ) {
			$settings['settings'][] = array(
				'id'      => 'shortpixel_quality',
				'title'   => __( 'ShortPixel Quality', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'lossy',
				'section' => __( 'ShortPixel Settings', 'foogallery' ),
				'choices' => array(
					'lossy' => __( 'Lossy (recommended) - offers the best compression rate', 'foogallery'),
					'glossy' => __( 'Glossy - creates images that are almost pixel-perfect identical to the originals', 'foogallery'),
					'lossless' => __( 'Lossless - the resulting image is pixel-identical with the original image', 'foogallery'),
					'lqip' => __( 'LQIP (not recommended) - low quality SVG placeholders', 'foogallery'),
				),
				'tab'     => 'thumb'
			);

			return $settings;
		}

		function has_local_cache() {
			return false;
		}

		function clear_local_cache_for_file( $file ) { }

		function get_last_error() { }
	}
}