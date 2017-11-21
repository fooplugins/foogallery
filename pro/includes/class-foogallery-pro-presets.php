<?php
/**
 * FooGallery Pro Hover Presets Class
 */
if ( ! class_exists( 'FooGallery_Pro_Hover_Presets' ) ) {

	class FooGallery_Pro_Hover_Presets {

		function __construct() {
			add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_preset_choices', array( $this, 'add_pro_hover_presets' ) );

			//make sure we can see the presets
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'show_preset_fields' ), 99, 2 );

             //remove preset choices from simple portfolio
            add_filter( 'foogallery_override_gallery_template_fields-simple_portfolio', array( $this, 'remove_preset_choices_for_simple_portfolio' ), 10, 2 );
        }

		/**
		 * Adds the presets that are available in the PRO version
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_pro_hover_presets( $choices ) {
			$choices['fg-preset fg-sadie'  ]= __( 'Sadie',   'foogallery' );
			$choices['fg-preset fg-layla'  ]= __( 'Layla',   'foogallery' );
			$choices['fg-preset fg-oscar'  ]= __( 'Oscar',   'foogallery' );
			$choices['fg-preset fg-sarah'  ]= __( 'Sarah',   'foogallery' );
			$choices['fg-preset fg-goliath']= __( 'Goliath', 'foogallery' );
			$choices['fg-preset fg-jazz'   ]= __( 'Jazz',    'foogallery' );
			$choices['fg-preset fg-lily'   ]= __( 'Lily',    'foogallery' );
			$choices['fg-preset fg-ming'   ]= __( 'Ming',    'foogallery' );
			$choices['fg-preset fg-selena' ]= __( 'Selena',  'foogallery' );
			$choices['fg-preset fg-steve'  ]= __( 'Steve',   'foogallery' );
			$choices['fg-preset fg-zoe'    ]= __( 'Zoe',     'foogallery' );
			return $choices;
		}

		/**
		 * Removed the preset choices from the simple portfolio template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function show_preset_fields( $fields, $template ) {
			foreach ($fields as &$field) {
				if ( 'hover_effect_help' === $field['id'] ||
					'hover_effect_preset' === $field['id'] ) {

					unset( $field['row_data']['data-foogallery-hidden'] );
				}
			}

			return $fields;
		}

        /**
         * Removed the preset choices from the simple portfolio template
         *
         * @uses "foogallery_override_gallery_template_fields"
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function remove_preset_choices_for_simple_portfolio( $fields, $template ) {
            foreach ($fields as &$field) {
                if ( 'hover_effect_preset' === $field['id'] ) {
                    $new_choices = $field['choices'];
                    foreach ($field['choices'] as $choice => $choice_name) {
                        if ( strpos( $choice, 'fg-preset') !== false ) {
                            unset( $new_choices[$choice] );
                        }
                    }
                    $field['choices'] = $new_choices;
                }
            }

            return $fields;
        }
	}
}