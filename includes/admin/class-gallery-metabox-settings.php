<?php
/**
 * Created by PhpStorm.
 * User: bradvin
 * Date: 2017/04/19
 * Time: 1:19 PM
 */


if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Settings' ) ) {

    class FooGallery_Admin_Gallery_MetaBox_Settings {

        /**
         * FooGallery_Admin_Gallery_MetaBox_Settings constructor.
         */
        function __construct() {
            //enqueue assets for the new settings tabs
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

            //set default settings tab icons
            add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons') );

            add_filter( 'foogallery_gallery_template_common_thumbnail_fields', array( $this, 'add_gallery_template_common_thumbnail_fields' ), 8 );
        }

        /***
         * Enqueue the assets needed by the settings
         * @param $hook_suffix
         */
        function enqueue_assets( $hook_suffix ){
            if( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
                $screen = get_current_screen();

                if ( is_object( $screen ) && FOOGALLERY_CPT_GALLERY == $screen->post_type ){

                    // Register, enqueue scripts and styles here
                    wp_enqueue_script( 'foogallery-admin-settings', FOOGALLERY_URL . '/js/foogallery.admin.min.js', array('jquery'), FOOGALLERY_VERSION );
                    wp_enqueue_style( 'foogallery-admin-settings', FOOGALLERY_URL . '/css/foogallery.admin.min.css', array(), FOOGALLERY_VERSION );
                }
            }
        }

        /**
         * Returns the Dashicon that can be used in the settings tabs
         * @param $section_slug
         * @return string
         */
        function add_section_icons( $section_slug ) {
            switch ( $section_slug ) {
                case 'general':
                    return 'dashicons-format-image';
                case 'advanced':
                    return 'dashicons-admin-generic';
                case 'appearance':
                    return 'dashicons-admin-appearance';
                case 'video':
                    return 'dashicons-format-video';
				case 'hover effects':
					return 'dashicons-admin-tools';
            }
            return 'dashicons-admin-tools';
        }

        /**
         * Add common thumbnail fields to a gallery template
         *
         * @return array
         */
        function add_gallery_template_common_thumbnail_fields( $fields ) {

			//region Appearance Fields
			$fields[] = array(
				'id'      => 'theme',
				'title'   => __( 'Theme', 'foogallery' ),
				'desc'    => __( 'The overall appearance of the items in the gallery, affecting the border, background, font and shadow colors.', 'foogallery' ),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'fg-light',
				'spacer'  => '<span class="spacer"></span>',
				'choices' => array(
					'fg-light' => __( 'Light', 'foogallery' ),
					'fg-dark'  => __( 'Dark', 'foogallery' )
				),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'class'
                )
			);

			$fields[] = array(
				'id'      => 'border_size',
				'title'   => __( 'Border Size', 'foogallery' ),
				'desc'    => __( 'The border size applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => 'fg-border-thin',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-border-thin'  => __( 'Thin', 'foogallery' ),
					'fg-border-medium'  => __( 'Medium', 'foogallery' ),
					'fg-border-thick'  => __( 'Thick', 'foogallery' ),
				),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'class'
                )
			);

			$fields[] = array(
				'id'      => 'rounded_corners',
				'title'   => __( 'Rounded Corners', 'foogallery' ),
				'desc'    => __( 'The border radius, or rounded corners applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => '',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-round-small'  => __( 'Small', 'foogallery' ),
					'fg-round-medium'  => __( 'Medium', 'foogallery' ),
					'fg-round-large'  => __( 'Large', 'foogallery' ),
					'fg-round-full'  => __( 'Full', 'foogallery' ),
				),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'class'
                )
			);

			$fields[] = array(
				'id'      => 'drop_shadow',
				'title'   => __( 'Drop Shadow', 'foogallery' ),
				'desc'    => __( 'The outer or drop shadow applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => 'fg-shadow-outline',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-shadow-outline'  => __( 'Outline', 'foogallery' ),
					'fg-shadow-small'  => __( 'Small', 'foogallery' ),
					'fg-shadow-medium'  => __( 'Medium', 'foogallery' ),
					'fg-shadow-large'  => __( 'Large', 'foogallery' ),
				),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'class'
                )
			);

			$fields[] = array(
				'id'      => 'inner_shadow',
				'title'   => __( 'Inner Shadow', 'foogallery' ),
				'desc'    => __( 'The inner shadow applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Appearance', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => '',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-shadow-inset-small'  => __( 'Small', 'foogallery' ),
					'fg-shadow-inset-medium'  => __( 'Medium', 'foogallery' ),
					'fg-shadow-inset-large'  => __( 'Large', 'foogallery' ),
				),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'class'
                )
			);

			$fields[] = array(
				'id'      => 'loading_icon',
				'title'   => __( 'Loading Icon', 'foogallery' ),
				'desc'	  => __( 'An animated loading icon can be shown while the thumbnails are busy loading.', 'foogallery' ),
				'section' => __( 'Appearance', 'foogallery' ),
				'default' => 'fg-loading-default',
				'type'    => 'htmlicon',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_loading_icon_choices', array(
					''                     => array( 'label' => __( 'None', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon"></div>' ),
					'fg-loading-default'   => array( 'label' => __( 'Default', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-default"></div>' ),
					'fg-loading-ellipsis'  => array( 'label' => __( 'Ellipsis', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-ellipsis"></div>' ),
					'fg-loading-gears'     => array( 'label' => __( 'Gears', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-gears"></div>' ),
					'fg-loading-hourglass' => array( 'label' => __( 'Hourglass', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-hourglass"></div>' ),
					'fg-loading-reload'    => array( 'label' => __( 'Reload', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-reload"></div>' ),
					'fg-loading-ripple'    => array( 'label' => __( 'Ripple', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-ripple"></div>' ),
					'fg-loading-bars'      => array( 'label' => __( 'Bars', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-bars"></div>' ),
					'fg-loading-spin'      => array( 'label' => __( 'Spin', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-spin"></div>' ),
					'fg-loading-squares'   => array( 'label' => __( 'Squares', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-squares"></div>' ),
					'fg-loading-cube'      => array( 'label' => __( 'Cube', 'foogallery' ), 'html' => '<div class="foogallery-setting-loading_icon fg-loading-cube"></div>' ),
				)),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview' => 'class'
                )
			);
			//endregion

			//region Hover Effects Fields
			$fields[] = array(
				'id'      => 'hover_effect_help',
				'title'   => __( 'Hover Effect Help', 'foogallery' ),
				'desc'    => __( 'A preset provides a stylish and pre-defined look &amp; feel for when you hover over the thumbnails.', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'type'    => 'help'
			);

			$fields[] = array(
				'id'      => 'hover_effect_preset',
				'title'   => __( 'Preset', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'default' => 'fg-custom',
				'type'    => 'radio',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_preset_choices', array(
					''  => __( 'None', 'foogallery' ),
					'fg-preset fg-sadie'   => __( 'Sadie', 'foogallery' ),
					'fg-preset fg-layla'   => __( 'Layla', 'foogallery' ),
					'fg-preset fg-oscar'   => __( 'Oscar', 'foogallery' ),
					'fg-preset fg-sarah'   => __( 'Sarah', 'foogallery' ),
					'fg-preset fg-goliath' => __( 'Goliath', 'foogallery' ),
					'fg-preset fg-jazz' => __( 'Jazz', 'foogallery' ),
					'fg-preset fg-lily' => __( 'Lily', 'foogallery' ),
					'fg-preset fg-ming' => __( 'Ming', 'foogallery' ),
					'fg-preset fg-selena' => __( 'Selena', 'foogallery' ),
					'fg-preset fg-steve' => __( 'Steve', 'foogallery' ),
					'fg-preset fg-zoe' => __( 'Zoe', 'foogallery' ),

					'fg-custom'  => __( 'Custom', 'foogallery' ),
				) ),
				'spacer'  => '<span class="spacer"></span>',
				'desc'	  => __( 'A preset styling that is used for the captions. If you want to define your own custom captions, then choose Custom.', 'foogallery' ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector' => 'input:checked',
					'data-foogallery-preview' => 'class'
				)
			);

			$fields[] = array(
				'id'      => 'hover_effect_color',
				'title'   => __( 'Color Effect', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'default' => '',
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_color_choices', array(
					'' => __( 'None', 'foogallery' ),
					'fg-hover-colorize' => __( 'Colorize', 'foogallery' ),
					'fg-hover-grayscale' => __( 'Greyscale', 'foogallery' ),
				) ),
				'desc'	  => __( 'Choose an color effect that is applied when you hover over a thumbnail.', 'foogallery' ),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'class'
				)
			);

			$fields[] = array(
				'id'      => 'hover_effect_scale',
				'title'   => __( 'Scaling Effect', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'default' => '',
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_scale_choices', array(
					'' => __( 'None', 'foogallery' ),
					'fg-hover-scale' => __( 'Scaled', 'foogallery' ),
				) ),
				'desc'	  => __( 'Apply a slight scaling effect when hovering over a thumbnail.', 'foogallery' ),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'class'
				)
			);

			$fields[] = array(
				'id'      => 'hover_effect_caption_visibility',
				'title'   => __( 'Caption Visibility', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'default' => 'fg-caption-hover',
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_caption_visibility_choices', array(
					'' => __( 'None', 'foogallery' ),
					'fg-caption-hover' => __( 'On Hover', 'foogallery' ),
					'fg-caption-always' => __( 'Always Visible', 'foogallery' ),
				) ),
				'desc'	  => __( 'Choose how the captions will be displayed.', 'foogallery' ),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'class'
				)
			);

			$fields[] = array(
				'id'      => 'hover_effect_transition',
				'title'   => __( 'Transition', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'default' => 'fg-hover-fade',
				'type'    => 'select',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_transition_choices', array(
					'fg-hover-instant'  => __( 'Instant', 'foogallery' ),
					'fg-hover-fade'  => __( 'Fade', 'foogallery' ),
					'fg-hover-slide-up'   => __( 'Slide Up', 'foogallery' ),
					'fg-hover-slide-down' => __( 'Slide Down', 'foogallery' ),
					'fg-hover-slide-left' => __( 'Slide Left', 'foogallery' ),
					'fg-hover-slide-right' => __( 'Slide Right', 'foogallery' ),
					'fg-hover-push' => __( 'Push', 'foogallery' )
				) ),
				'desc'	  => __( 'Choose what effect is used to show the caption when you hover over a thumbnail', 'foogallery' ),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'select',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'class'
				)
			);

			$fields[] = array(
				'id'      => 'hover_effect_icon',
				'title'   => __( 'Icon', 'foogallery' ),
				'desc'    => __( 'Choose which icon is shown with the caption when you hover over a thumbnail', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'type'    => 'htmlicon',
				'default' => 'fg-hover-zoom',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_icon_choices', array(
					'' => array( 'label' => __( 'None' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon"></div>' ),
					'fg-hover-zoom' => array( 'label' => __( 'Zoom' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-zoom"></div>' ),
					'fg-hover-zoom2' => array( 'label' => __( 'Zoom 2' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-zoom2"></div>' ),
					'fg-hover-zoom3' => array( 'label' => __( 'Zoom 3' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-zoom3"></div>' ),
					'fg-hover-plus' => array( 'label' => __( 'Plus' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-plus"></div>' ),
					'fg-hover-circle-plus' => array( 'label' => __( 'Circle Plus' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-circle-plus"></div>' ),
					'fg-hover-eye' => array( 'label' => __( 'Eye' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-eye"></div>' ),
					'fg-hover-external' => array( 'label' => __( 'External' , 'foogallery' ), 'html' => '<div class="foogallery-setting-caption_icon fg-hover-external"></div>' ),
				) ),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'class'
				)
			);

			$settings_link = sprintf( '<a target="blank" href="%s">%s</a>', foogallery_admin_settings_url(), __('settings', 'foogallery') );

			$fields[] = array(
				'id'      => 'hover_effect_title',
				'title'   => __( 'Title', 'foogallery' ),
				'desc'    => __( 'Decide where caption titles are pulled from. By default, what is saved under general settings will be used, but it can be overridden per gallery', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'type'    => 'radio',
				'default' => '',
				'choices' => array(
					'none'    => __( 'None', 'foogallery' ),
					''        => sprintf( __( 'Default (as per %s)', 'foogallery' ), $settings_link ),
					'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
					'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
					'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
					'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' ),
				),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'shortcode'
				)
			);

			$fields[] = array(
				'id'      => 'hover_effect_desc',
				'title'   => __( 'Description', 'foogallery' ),
				'desc'    => __( 'Decide where captions descriptions are pulled from. By default, the general settings are used, but it can be overridden per gallery', 'foogallery' ),
				'section' => __( 'Hover Effects', 'foogallery' ),
				'type'    => 'radio',
				'default' => '',
				'choices' => array(
					'none'    => __( 'None', 'foogallery' ),
					''        => sprintf( __( 'Default (as per %s)', 'foogallery' ), $settings_link ),
					'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
					'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
					'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
					'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' ),
				),
				'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'hover_effect_preset',
					'data-foogallery-show-when-field-value' => 'fg-custom',
					'data-foogallery-preview' => 'shortcode'
				)
			);
			//endregion Hover Effects Fields

            return $fields;
        }
    }
}