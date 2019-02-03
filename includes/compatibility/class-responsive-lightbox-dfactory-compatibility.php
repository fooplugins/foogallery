<?php
/**
 * Builds in support for the Responsive Lightbox plugin by dFactory
 * Created by brad.
 * Date: 13/05/2017
 *
 * @since 1.2.21
 */
if ( ! class_exists( 'FooGallery_Responsive_Lightbox_dFactory_Compatibility' ) ) {

	class FooGallery_Responsive_Lightbox_dFactory_Compatibility {

		function __construct() {
			add_filter( 'foogallery_gallery_template_field_lightboxes', array( $this, 'add_lightbox' ), 99 );
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_attributes' ), 10, 3 );
		}

		/**
		 * Add the dFactory lightbox to the available FooGallery lightboxes
		 * @param $lightboxes
		 *
		 * @return mixed
		 * @since 1.2.21
		 */
		function add_lightbox($lightboxes) {
			if ( class_exists( 'Responsive_Lightbox' ) ) {
				$option_text = __( 'Responsive Lightbox by dFactory', 'foogallery' );
				$lightboxes['dfactory'] = $option_text;
			}

			return $lightboxes;
		}

		/**
		 * If the dFactory lightbox is selected for the gallery, then make the integration work
		 *
		 * @param $attr
		 * @param $args
		 * @param $attachment
		 *
		 * @return mixed
		 * @since 1.2.21
		 */
		function add_attributes($attr, $args, $attachment) {
			if ( class_exists( 'Responsive_Lightbox' ) ) {
				$lightbox = foogallery_gallery_template_setting( 'lightbox', 'unknown' );

				//only add attributes if the dfactory lightbox is in use
				if ( 'dfactory' === $lightbox ) {
					$attr['data-rel'] = 'lightbox';

					if ( !empty( $attachment->caption ) ) {
						$attr['title'] = $attachment->caption;
					}
				}
			}

    		return $attr;
		}
	}
}