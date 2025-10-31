<?php
/**
 * Class used to add custom classes to gallery links
 * Date: 04/06/2017
 */
if ( ! class_exists( 'FooGallery_Attachment_Custom_Class' ) ) {

	class FooGallery_Attachment_Custom_Class {

		function __construct() {
			add_filter( 'foogallery_attachment_custom_fields', array( $this, 'add_custom_class_field' ) );
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_class_attributes' ), 99, 3 );
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_custom_class_meta' ), 10, 2 );
		}

		/**
		 * Adds a custom class field to the attachments
		 *
		 * @param $fields array
		 *
		 * @return array
		 */
		function add_custom_class_field( $fields ) {
			$fields['foogallery_custom_class'] = array(
				'label'       =>  __( 'Custom Class', 'foogallery' ),
				'input'       => 'text',
				'helps'       => __( 'Add extra classes to the attachment', 'foogallery' ),
				'exclusions'  => array( 'audio', 'video' ),
			);

			return $fields;
		}

		/**
		 * Alters the actual link output to include the custom class added
		 *
		 * @uses "foogallery_attachment_html_link_attributes" filter
		 *
		 * @param                             $attr
		 * @param                             $args
		 * @param object|FooGalleryAttachment $object
		 *
		 * @return array
		 */
		function alter_class_attributes( $attr, $args, $object ) {
			//if the object is a FooGalleryAttachment and has a custom class, add it to the custom class
			if ( $object instanceof FooGalleryAttachment && !empty( $object->custom_class ) ) {
				if ( !isset( $attr[ 'class' ] ) ) {
					$attr[ 'class' ] = $object->custom_class;
				}else{
					$attr[ 'class' ] .= ' ' . $object->custom_class;
				}

				//check for any special class names and do some magic!
				if ( 'nolink' === $object->custom_class ) {
					unset( $attr['href'] );
				}
			}

			return $attr;
		}

		/**
		 * Loads any extra custom class data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_custom_class_meta( $foogallery_attachment, $post ) {
			$custom_class = get_post_meta( $post->ID, '_foogallery_custom_class', true );
			if ( !empty( $custom_class ) ) {
				$foogallery_attachment->custom_class = $custom_class;
			}
		}
	}
}