<?php
/**
 * FooGallery Pro Hover Presets Class
 */
if ( ! class_exists( 'FooGallery_Pro_Hover_Presets' ) ) {

	class FooGallery_Pro_Hover_Presets {

		function __construct() {
			add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_preset_choices', array( $this, 'add_pro_hover_presets' ) );
		}

		/**
		 * Adds the presets that are available in the PRO version
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_pro_hover_presets( $choices ) {
			$choices['fg-preset fg-sadie'  ]= __( 'Sadie', 'foogallery' );
			$choices['fg-preset fg-layla'  ]= __( 'Layla', 'foogallery' );
			$choices['fg-preset fg-oscar'  ]= __( 'Oscar', 'foogallery' );
			$choices['fg-preset fg-sarah'  ]= __( 'Sarah', 'foogallery' );
			$choices['fg-preset fg-goliath']= __( 'Goliath', 'foogallery' );
			$choices['fg-preset fg-jazz'   ]= __( 'Jazz', 'foogallery' );
			$choices['fg-preset fg-lily'   ]= __( 'Lily', 'foogallery' );
			$choices['fg-preset fg-ming'   ]= __( 'Ming', 'foogallery' );
			$choices['fg-preset fg-selena' ]= __( 'Selena', 'foogallery' );
			$choices['fg-preset fg-steve'  ]= __( 'Steve', 'foogallery' );
			$choices['fg-preset fg-zoe'    ]= __( 'Zoe', 'foogallery' );
			return $choices;
		}
	}
}