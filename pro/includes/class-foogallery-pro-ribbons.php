<?php
/**
 * FooGallery Pro Ribbons Class
 */
if ( ! class_exists( 'FooGallery_Pro_Ribbons' ) ) {

	class FooGallery_Pro_Ribbons {

		function __construct() {
			if ( is_admin() ) {
				// Add attachment custom fields.
				add_filter( 'foogallery_attachment_custom_fields', array( $this, 'attachment_custom_fields' ), 40 );
			}

			// Load ribbon meta after attachment has loaded.
			add_action( 'foogallery_attachment_instance_after_load', array( $this, 'load_ribbon_meta' ), 10, 2 );

			// Append ribbon HTML to the gallery output.
			add_filter( 'foogallery_attachment_html_item_opening', array( $this, 'add_ribbon_html' ), 10, 3 );

			// Add ribbon data to the json output
			add_filter( 'foogallery_build_attachment_json', array( $this, 'add_ribbon_to_json' ), 20, 6 );
		}

		/**
		 * Loads any extra ribbon data for an attachment.
		 *
		 * @param $foogallery_attachment
		 * @param $post
		 */
		public function load_ribbon_meta( $foogallery_attachment, $post ) {
			$ribbon_type = get_post_meta( $post->ID, '_foogallery_ribbon', true );
			if ( !empty( $ribbon_type ) ) {
				$foogallery_attachment->ribbon_type = $ribbon_type;
				$foogallery_attachment->ribbon_text = get_post_meta( $post->ID, '_foogallery_ribbon_text', true );
			}
		}

		/**
		 * Builds up ribbon HTML and adds it to the output.
		 *
		 * @param $html
		 * @param $foogallery_attachment
		 * @param $args
		 *
		 * @return mixed
		 */
		public function add_ribbon_html( $html, $foogallery_attachment, $args ) {
			if ( isset( $foogallery_attachment->ribbon_type ) && isset( $foogallery_attachment->ribbon_text ) ) {
				//Add the ribbon HTML!!!
				$ribbon_html = '<div class="' . $foogallery_attachment->ribbon_type . '"><span>' . esc_html( $foogallery_attachment->ribbon_text ) . '</span></div>';
				$html = str_replace( '<figure class=',  $ribbon_html . '<figure class=', $html );
			}
			return $html;
		}

		/**
		 * Add the ribbon data to the json object.
		 *
		 * @param StdClass $json_object
		 * @param FooGalleryAttachment $foogallery_attachment
		 * @param array $args
		 * @param array $anchor_attributes
		 * @param array $image_attributes
		 * @param array $captions
		 *
		 * @return mixed
		 */
		public function add_ribbon_to_json(  $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions ) {
			if ( isset( $foogallery_attachment->ribbon_type ) && isset( $foogallery_attachment->ribbon_text ) ) {
				$json_object->ribbon = array(
					'type' => $foogallery_attachment->ribbon_type,
                    'text' => $foogallery_attachment->ribbon_text
				);
			}

			return $json_object;
		}

		/**
		 * Add Ribbon specific custom fields.
		 *
		 * @uses "foogallery_attachment_custom_fields" filter
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public function attachment_custom_fields( $fields ) {
			$fields['foogallery_ribbon']  = array(
				'label'       => __( 'Ribbon Type', 'foogallery' ),
				'input'       => 'select',
				'application' => 'image/foogallery',
				'options'     => self::get_ribbon_choices(),
			);

			$fields['foogallery_ribbon_text']  = array(
				'label'       => __( 'Ribbon Text', 'foogallery' ),
				'input'       => 'text',
				'application' => 'image/foogallery',
			);

			return $fields;
		}

		/**
		 * Returns the list of ribbon choices.
		 *
		 * @return array
		 */
		public static function get_ribbon_choices() {
			return array(
				''            => __( 'None', 'foogallery' ),
				'fg-ribbon-5' => __( 'Type 1 (top-right, diagonal, green)', 'foogallery' ),
				'fg-ribbon-3' => __( 'Type 2 (top-left, small, blue)', 'foogallery' ),
				'fg-ribbon-4' => __( 'Type 3 (top, full-width, yellow)', 'foogallery' ),
				'fg-ribbon-6' => __( 'Type 4 (top-right, rounded, pink)', 'foogallery' ),
				'fg-ribbon-2' => __( 'Type 5 (top-left, medium, purple)', 'foogallery' ),
				'fg-ribbon-1' => __( 'Type 6 (top-left, vertical, orange)', 'foogallery' ),
			);
		}
	}
}