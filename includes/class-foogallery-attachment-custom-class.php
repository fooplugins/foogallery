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
				'helps'       => __( 'Add extra classed to your attachment', 'foogallery' ),
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
			$custom_class = get_post_meta( $object->ID, '_foogallery_custom_class', true );

			if ( ! isset( $attr[ 'class' ] ) ) {
				$attr[ 'class' ] = $custom_class;
			}else{
				$attr[ 'class' ] .= ' ' . $custom_class;
			}

			//check for any special class names and do some magic!
			if ( 'nolink' === $custom_class ) {
				unset( $attr['href'] );
			}

			return $attr;
		}
	}
}