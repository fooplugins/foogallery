<?php
/**
 * Create some template specific filters for overriding html attributes when rendering galleries
 *
 * So, instead of hooking into 'foogallery_attachment_html_image_attributes' and checking that the gallery template being used is 'default',
 *  now rather just hook into 'foogallery_attachment_html_image_attributes-default'.
 */

if ( ! class_exists( 'FooGallery_Attachment_Filters' ) ) {
	class FooGallery_Attachment_Filters {

		function __construct() {
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'attachment_html_image_attributes' ), 20, 3 );
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'attachment_html_link_attributes' ), 20, 3 );
		}

		/**
		 * Apply a template-specific filter for overriding the image attributes
		 *
		 * @param $attr array
		 * @param $args array
		 * @param $attachment FooGalleryAttachment
		 *
		 * @return array
		 */
		function attachment_html_image_attributes( $attr, $args, $attachment ) {
			global $current_foogallery;

			if ( $current_foogallery && $current_foogallery->gallery_template ) {
				$attr = apply_filters( "foogallery_attachment_html_image_attributes-{$current_foogallery->gallery_template}", $attr, $args, $attachment );
			}

			return $attr;
		}

		/**
		 * Apply a template-specific filter for overriding the link attributes
		 *
		 * @param $attr array
		 * @param $args array
		 * @param $attachment FooGalleryAttachment
		 *
		 * @return array
		 */
		function attachment_html_link_attributes( $attr, $args, $attachment ) {
			global $current_foogallery;

			if ( $current_foogallery && $current_foogallery->gallery_template ) {
				$attr = apply_filters( "foogallery_attachment_html_link_attributes-{$current_foogallery->gallery_template}", $attr, $args, $attachment );
			}

			return $attr;
		}
	}
}
