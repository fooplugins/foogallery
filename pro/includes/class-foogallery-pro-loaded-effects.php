<?php
/**
 * FooGallery Pro Loaded Effects Class
 */
if ( ! class_exists( 'FooGallery_Pro_Loaded_Effects' ) ) {

    class FooGallery_Pro_Loaded_Effects {

        function __construct() {
            //add the effects only available in pro for the loaded effects
            add_filter( 'foogallery_gallery_template_common_thumbnail_fields_loaded_effect_choices', array( $this, 'add_pro_hover_presets' ) );
        }

        /**
         * Adds the effects that are available in the PRO version
         *
         * @param $choices
         *
         * @return mixed
         */
        function add_pro_hover_presets( $choices ) {
            $choices['fg-loaded-slide-up'    ]= __( 'Slide Up', 'foogallery' );
            $choices['fg-loaded-slide-down'  ]= __( 'Slide Down', 'foogallery' );
            $choices['fg-loaded-slide-left'  ]= __( 'Slide Left', 'foogallery' );
            $choices['fg-loaded-slide-right' ]= __( 'Slide Right', 'foogallery' );
            $choices['fg-loaded-scale-up'    ]= __( 'Scale Up', 'foogallery' );
            $choices['fg-loaded-swing-down'  ]= __( 'Swing Down', 'foogallery' );
            $choices['fg-loaded-drop'        ]= __( 'Drop', 'foogallery' );
            $choices['fg-loaded-fly'         ]= __( 'Fly', 'foogallery' );
            $choices['fg-loaded-flip'        ]= __( 'Flip', 'foogallery' );
            return $choices;
        }
    }
}