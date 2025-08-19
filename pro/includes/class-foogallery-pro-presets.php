<?php
/**
 * FooGallery Pro Hover Presets Class
 */
if ( ! class_exists( 'FooGallery_Pro_Hover_Presets' ) ) {

	class FooGallery_Pro_Hover_Presets {

		function __construct() {
			add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_preset_choices', array( $this, 'add_preset_choices' ), 10, 1 );
        }

		/**
		 * Add the preset choices to the hover effect preset field
		 *
		 * @param $choices
		 *
		 * @return array
		 */
		function add_preset_choices( $choices ) {
			return array_merge( $choices, array(
				'fg-preset fg-sadie'   => __( 'Sadie',   'foogallery' ),
				'fg-preset fg-layla'   => __( 'Layla',   'foogallery' ),
				'fg-preset fg-oscar'   => __( 'Oscar',   'foogallery' ),
				'fg-preset fg-sarah'   => __( 'Sarah',   'foogallery' ),
				'fg-preset fg-goliath' => __( 'Goliath', 'foogallery' ),
				'fg-preset fg-jazz'    => __( 'Jazz',    'foogallery' ),
				'fg-preset fg-lily'    => __( 'Lily',    'foogallery' ),
				'fg-preset fg-ming'    => __( 'Ming',    'foogallery' ),
				'fg-preset fg-selena'  => __( 'Selena',  'foogallery' ),
				'fg-preset fg-steve'   => __( 'Steve',   'foogallery' ),
				'fg-preset fg-zoe'     => __( 'Zoe',     'foogallery' ),
			) );
		}
	}
}