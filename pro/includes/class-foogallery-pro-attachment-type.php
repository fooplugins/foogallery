<?php
if ( ! class_exists( 'FooGallery_Pro_Attachment_Type' ) ) {

	class FooGallery_Pro_Attachment_Type {

		function __construct() {
			//determine the type of the attachment
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'determine_type' ), 99, 2 );

			//add attachment field for custom type
			add_filter( 'foogallery_attachment_custom_fields', array( $this, 'add_override_type_field' ), 50 );

			//add attributes to front-end anchor
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_link_attributes' ), 30, 3 );

			//add type class to fg-item
			add_filter( 'foogallery_attachment_html_item_classes', array( $this, 'add_type_class_to_item' ), 20, 3 );
		}

		/**
		 * Determines the type of the item
		 *
		 * @param $foogallery_attachment FooGalleryAttachment
		 * @param $post WP_Post
		 */
		function determine_type( $foogallery_attachment, $post ) {
			if ( isset( $foogallery_attachment->is_video ) && $foogallery_attachment->is_video === true ) {
				$foogallery_attachment->type = 'video';

				//check if we have set the type to embed
				if ( isset( $foogallery_attachment->is_embed ) && $foogallery_attachment->is_embed === true ) {
					$foogallery_attachment->type = 'embed';
				}
			} else {
				$link = foogallery_gallery_template_setting( 'link', 'image' );
				if ( 'page' === $link || ( 'custom' === $link && !empty( $foogallery_attachment->custom_url ) ) ) {
					$foogallery_attachment->type = 'iframe';
				}
			}

			//check if we have overridden the type
			$override_type = get_post_meta( $foogallery_attachment->ID, '_foogallery_override_type', true );
			if ( ! empty( $override_type ) ) {
				$foogallery_attachment->type = $override_type;
			}
		}

		/**
		 * Adds a override type field to the attachments
		 *
		 * @param $fields array
		 *
		 * @return array
		 */
		function add_override_type_field( $fields ) {
			$fields['foogallery_override_type'] = array(
				'label'       =>  __( 'Override Type', 'foogallery' ),
				'input'       => 'text',
				'helps'       => __( 'Override the type of the attachment used by lightbox', 'foogallery' ),
				'exclusions'  => array( 'audio', 'video' ),
			);

			return $fields;
		}


		/**
		 * @uses "foogallery_attachment_html_link_attributes" filter
		 *
		 * @param                             $attr
		 * @param                             $args
		 * @param object|FooGalleryAttachment $attachment
		 *
		 * @return mixed
		 */
		public function alter_link_attributes( $attr, $args, $attachment ) {
			$attr['data-type'] = $attachment->type;

			return $attr;
		}

		/**
		 * Add type class onto item
		 *
		 * @param $classes
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return mixed
		 */
		function add_type_class_to_item( $classes, $foogallery_attachment, $args ) {
			$classes[] = 'fg-type-' . $foogallery_attachment->type;

			return $classes;
		}
	}
}