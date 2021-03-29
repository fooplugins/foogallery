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
			add_action( 'foogallery_build_container_data_options', array( $this, 'add_shortpixel_data_options' ), 20, 3 );
		}

		/**
		 * Add shortpixel options to the container for webp support
		 *
		 * @param            $options
		 * @param FooGallery $gallery
		 * @param            $attributes
		 *
		 * @return array
		 */
		function add_shortpixel_data_options( $options, $gallery, $attributes ) {
			$settings = $this->get_settings();
			if ( 'webp' === $settings['to'] ) {
				$options['shortpixel'] = true;
			}

			return $options;
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

			$settings = $this->get_settings();

			$result = trailingslashit( $settings['url'] );

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

			$quality = $settings['level']; //lqip, lossless, glossy or lossy
			if ( !empty( $quality ) ) {
				$params[] = 'q_' . $quality;
			}

			$return_type = $settings['ret']; //blank, img, wait
			if ( !empty( $return_type ) ) {
				$params[] = 'ret_' . $return_type;
			}

			$conversion = $settings['to']; //webp or avif
			if ( !empty( $conversion ) ) {
				$params[] = 'to_' . $conversion;
			}

			//allow for adjustments
			$params = apply_filters( 'foogallery_thumb_engine_shortpixel_params', $params, $url, $args );

			return $result . implode( ',', $params ) . '/' . $url;
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
				$attr['data-spai-upd'] = 1;
			}

			return $attr;
		}

		/**
		 * Returns true if SPAI plugin is activated
		 * @return bool
		 */
		function is_spai_active() {
			return class_exists( 'ShortPixel\AI\Options\Option' );
		}

		/**
		 * Returns ShortPixel settings from either SPAI plugin (if activated) or from FooGallery settings
		 *
		 * @return array
		 */
		function get_settings() {
			global $foogallery_shortpixel_settings;

			if ( isset( $foogallery_shortpixel_settings ) ) {
				return $foogallery_shortpixel_settings;
			}

			if ( $this->is_spai_active() ) {
				//get settings from SPAI settings object
				$options = ShortPixel\AI\Options::_();
				$foogallery_shortpixel_settings = array(
					'url' => $options->settings->behaviour->api_url,
					'level' => $options->settings->compression->level,
					'ret' => 'wait',
					'to' => $options->settings->compression->webp ? 'webp' : ''
				);

			} else {
				//get settings from foogallery
				$foogallery_shortpixel_settings = array(
					'url' => 'https://cdn.shortpixel.ai/client/',
					'level' => foogallery_get_setting( 'shortpixel_quality', 'lossy'),
					'ret' => foogallery_get_setting( 'shortpixel_return', 'wait'),
					'to' => foogallery_get_setting( 'shortpixel_conversion', '')
				);
			}

			return $foogallery_shortpixel_settings;
		}

		/**
		 * Adds ShortPixel settings to the admin page
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function add_shortpixel_settings( $settings ) {
			if ( $this->is_spai_active() ) {
				$spai_settings_link = '<a href="' . admin_url( 'options-general.php?page=shortpixel-ai-settings' ) . '">' . __( 'ShortPixel Adaptive Images settings' , 'foogallery' ) . '</a>';

				$settings['settings'][] = array(
					'id'      => 'shortpixel_settings',
					'title'   => __( 'ShortPixel AI Detected!', 'foogallery' ),
					'type'    => 'html',
					'section' => __( 'ShortPixel Settings', 'foogallery' ),
					'desc'    => sprintf( __( 'Settings will be inherited from the %s.', 'foogallery' ), $spai_settings_link ),
					'tab'     => 'thumb'
				);
			} else {

				$settings['settings'][] = array(
					'id'      => 'shortpixel_quality',
					'title'   => __( 'ShortPixel Quality', 'foogallery' ),
					'type'    => 'radio',
					'default' => 'lossy',
					'section' => __( 'ShortPixel Settings', 'foogallery' ),
					'choices' => array(
						'lossy'    => __( 'Lossy (recommended) - offers the best compression rate', 'foogallery' ),
						'glossy'   => __( 'Glossy - creates images that are almost pixel-perfect identical to the originals', 'foogallery' ),
						'lossless' => __( 'Lossless - the resulting image is pixel-identical with the original image', 'foogallery' ),
						'lqip'     => __( 'LQIP (not recommended) - low quality SVG placeholders', 'foogallery' ),
					),
					'tab'     => 'thumb'
				);

				$settings['settings'][] = array(
					'id'      => 'shortpixel_return',
					'title'   => __( 'ShortPixel Return', 'foogallery' ),
					'type'    => 'radio',
					'default' => 'wait',
					'section' => __( 'ShortPixel Settings', 'foogallery' ),
					'choices' => array(
						'blank' => __( 'Blank - will immediately return a blank placeholder', 'foogallery' ),
						'img'   => __( 'Image - redirect to the original image while the image is being processed', 'foogallery' ),
						'wait'  => __( 'Wait - will make the image wait to be displayed until the new processed image is ready', 'foogallery' ),
					),
					'tab'     => 'thumb'
				);

				$settings['settings'][] = array(
					'id'      => 'shortpixel_conversion',
					'title'   => __( 'ShortPixel Conversion', 'foogallery' ),
					'type'    => 'radio',
					'default' => '',
					'section' => __( 'ShortPixel Settings', 'foogallery' ),
					'choices' => array(
						''     => __( 'Default - will not convert the image', 'foogallery' ),
						'webp' => __( 'WebP - convert to WebP', 'foogallery' ),
						//'avif' => __( 'AVIF - convert to AVIF', 'foogallery' ), not supported yet
					),
					'tab'     => 'thumb'
				);
			}

			return $settings;
		}

		function has_local_cache() {
			return false;
		}

		function clear_local_cache_for_file( $file ) { }

		function get_last_error() { }
	}
}