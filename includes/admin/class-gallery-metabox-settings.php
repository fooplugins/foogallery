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

            add_filter( 'foogallery_gallery_template_common_thumbnail_fields', array( $this, 'add_gallery_template_common_thumbnail_fields' ) );
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
                    wp_enqueue_script( 'foogallery-admin-settings', FOOGALLERY_URL . '/js/foogallery.admin.min.js', FOOGALLERY_VERSION, 'jquery' );

                    wp_enqueue_style( 'foogallery-admin-settings', FOOGALLERY_URL . '/css/foogallery.admin.min.css', FOOGALLERY_VERSION );
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
                    return 'dashicons-admin-tools';
                    break;
                case 'advanced':
                    return 'dashicons-admin-generic';
                    break;
                case 'look &amp; feel':
                    return 'dashicons-images-alt2';
                    break;
                case 'video':
                    return 'dashicons-format-video';
            }
            return 'dashicons-admin-tools';
        }

        /**
         * Add common thumbnail fields to a gallery template
         *
         * @return array
         */
        function add_gallery_template_common_thumbnail_fields( $fields ) {

			//region Style Fields
			$fields[] = array(
				'id'      => 'theme',
				'title'   => __( 'Theme', 'foogallery' ),
				'desc'    => __( 'The overall appearance of the items in the gallery, affecting the border, background, font and shadow colors.', 'foogallery' ),
				'section' => __( 'Style', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'fg-light',
				'spacer'  => '<span class="spacer"></span>',
				'choices' => array(
					'fg-light' => __( 'Light', 'foogallery' ),
					'fg-dark'  => __( 'Dark', 'foogallery' )
				)
			);

			$fields[] = array(
				'id'      => 'border-size',
				'title'   => __( 'Border Size', 'foogallery' ),
				'desc'    => __( 'The border size applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Style', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => 'fg-border-thin',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-border-thin'  => __( 'Thin', 'foogallery' ),
					'fg-border-medium'  => __( 'Medium', 'foogallery' ),
					'fg-border-thick'  => __( 'Thick', 'foogallery' ),
				)
			);

			$fields[] = array(
				'id'      => 'rounded-corners',
				'title'   => __( 'Rounded Corners', 'foogallery' ),
				'desc'    => __( 'The border radius, or rounded corners applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Style', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => '',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-round-small'  => __( 'Small', 'foogallery' ),
					'fg-round-medium'  => __( 'Medium', 'foogallery' ),
					'fg-round-large'  => __( 'Large', 'foogallery' ),
					'fg-round-full'  => __( 'Full', 'foogallery' ),
				)
			);

			$fields[] = array(
				'id'      => 'drop-shadow',
				'title'   => __( 'Drop Shadow', 'foogallery' ),
				'desc'    => __( 'The outer or drop shadow applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Style', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => 'fg-shadow-inset-outline',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-shadow-inset-outline'  => __( 'Outline', 'foogallery' ),
					'fg-shadow-inset-small'  => __( 'Small', 'foogallery' ),
					'fg-shadow-inset-medium'  => __( 'Medium', 'foogallery' ),
					'fg-shadow-inset-large'  => __( 'Large', 'foogallery' ),
				)
			);

			$fields[] = array(
				'id'      => 'inset-shadow',
				'title'   => __( 'Inner Shadow', 'foogallery' ),
				'desc'    => __( 'The inner shadow applied to each thumbnail', 'foogallery' ),
				'section' => __( 'Style', 'foogallery' ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'default' => '',
				'choices' => array(
					''  => __( 'None', 'foogallery' ),
					'fg-shadow-inset-small'  => __( 'Small', 'foogallery' ),
					'fg-shadow-inset-medium'  => __( 'Medium', 'foogallery' ),
					'fg-shadow-inset-large'  => __( 'Large', 'foogallery' ),
				)
			);

			$fields[] = array(
				'id'      => 'loading_animation',
				'title'   => __( 'Loading Icon', 'foogallery' ),
				'default' => 'fg-loading-default',
				'type'    => 'select',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_loading_animation_choices', array(
					'fg-loading-default'  => __( 'Default', 'foogallery' ),
					'fg-loading-ellipsis'  => __( 'Ellipsis', 'foogallery' ),
					'fg-loading-gears'  => __( 'Gears', 'foogallery' ),
					'fg-loading-hourglass'  => __( 'Hourglass', 'foogallery' ),
					'fg-loading-reload'  => __( 'Reload', 'foogallery' ),
					'fg-loading-ripple'  => __( 'Ripple', 'foogallery' ),
					'fg-loading-bars'  => __( 'Bars', 'foogallery' ),
					'fg-loading-spin'  => __( 'Spin', 'foogallery' ),
					'fg-loading-squares'  => __( 'Squares', 'foogallery' ),
					'fg-loading-cube'  => __( 'Cube', 'foogallery' ),
					''   => __( 'None', 'foogallery' )
				)),
				'desc'	  => __( 'An animated loading icon can be shown while the thumbnails are busy loading.', 'foogallery' ),
				'section' => __( 'Style', 'foogallery' )
			);
			//endregion

//            $fields[] = array(
//                'id'      => 'hover-effect-help',
//                'title'   => __( 'Hover Effect Help', 'foogallery' ),
//                'desc'    => __( 'Captions can be enabled by choosing the "Caption" hover effect below.', 'foogallery' ),
//                'section' => __( 'Look &amp; Feel', 'foogallery' ),
//                'type'    => 'help'
//            );

			$fields[] = array(
				'id'      => 'caption_theme',
				'title'   => __( 'Caption Theme', 'foogallery' ),
				'section' => __( 'Captions', 'foogallery' ),
				'default' => 'fg-custom',
				'type'    => 'radio',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_theme_choices', array(
					''  => __( 'None', 'foogallery' ),
					'fg-custom'  => __( 'Custom', 'foogallery' ),
					'fg-sadie'   => __( 'Sadie', 'foogallery' ),
					'fg-layla'   => __( 'Layla', 'foogallery' ),
					'fg-oscar'   => __( 'Oscar', 'foogallery' ),
					'fg-sarah'   => __( 'Sarah', 'foogallery' ),
					'fg-goliath' => __( 'Goliath', 'foogallery' ),
				) ),
				'spacer'  => '<span class="spacer"></span>',
				'desc'	  => __( 'The theme that is applied to the captions.', 'foogallery' ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector' => 'input:checked'
				)
			);

			$fields[] = array(
				'id'      => 'caption_type',
				'title'   => __( 'Caption Visibility', 'foogallery' ),
				'desc'    => __( 'Choose when your captions will be shown.', 'foogallery' ),
				'spacer'  => '<span class="spacer"></span>',
				'section' => __( 'Captions', 'foogallery' ),
				'default' => 'fg-caption-hover',
				'type'    => 'radio',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_type_choices', array(
					''  => __( 'None', 'foogallery' ),
					'fg-caption-hover'   => __( 'On Hover', 'foogallery' ),
					'fg-caption-always' => __( 'Always Visible', 'foogallery' )
				) )
			);

			$fields[] = array(
				'id'      => 'caption_content',
				'title'   => __( 'Caption Content', 'foogallery' ),
				'desc'    => __( 'Choose what is used for your caption content', 'foogallery' ),
				'section' => __( 'Captions', 'foogallery' ),
				'default' => 'title',
				'type'    => 'checkboxlist',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_content_choices', array(
					'icon' => __( 'Icon', 'foogallery' ),
					'title'  => __( 'Title', 'foogallery' ),
					'desc'   => __( 'Description', 'foogallery' )
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:checkbox',
					'data-foogallery-value-selector' => 'input:checked',
					'data-foogallery-value-attribute' => 'data-value',
				)
			);

			$fields[] = array(
				'id'      => 'caption_hover_icon',
				'title'   => __( 'Hover Icon', 'foogallery' ),
				'desc'    => __( 'Choose which icon is shown when you hover over a thumbnail', 'foogallery' ),
				'section' => __( 'Captions', 'foogallery' ),
				'type'    => 'icon',
				'default' => 'fg-hover-zoom',
				'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_hover_icon_choices', array(
					'' => array( 'label' => __( 'None' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-none.png' ),
					'fg-hover-zoom' => array( 'label' => __( 'Zoom' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom.png' ),
					'fg-hover-zoom2' => array( 'label' => __( 'Zoom 2' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom2.png' ),
					'fg-hover-zoom3' => array( 'label' => __( 'Zoom 3' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom3.png' ),
					'fg-hover-plus' => array( 'label' => __( 'Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-plus.png' ),
					'fg-hover-circle-plus' => array( 'label' => __( 'Cirlce Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-circle-plus.png' ),
					'fg-hover-eye' => array( 'label' => __( 'Eye' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-eye.png' ),
					'fg-hover-external' => array( 'label' => __( 'External' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-external.png' )
				) ),
				'row_data'=> array(
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'caption_content',
					'data-foogallery-show-when-field-value' => 'icon'
				)
			);

            $fields[] = array(
                'id'      => 'caption_hover_effect',
                'title'   => __( 'Hover Effect', 'foogallery' ),
                'section' => __( 'Captions', 'foogallery' ),
                'default' => 'fg-hover-fade',
                'type'    => 'select',
                'choices' => apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_hover_effect_choices', array(
					'fg-hover-fade'  => __( 'Fade', 'foogallery' ),
					'fg-hover-slide-up'   => __( 'Slide Up', 'foogallery' ),
					'fg-hover-slide-down' => __( 'Slide Down', 'foogallery' ),
					'fg-hover-slide-left' => __( 'Slide Left', 'foogallery' ),
					'fg-hover-slide-right' => __( 'Slide Right', 'foogallery' ),
					'fg-hover-push' => __( 'Push', 'foogallery' ),
					'fg-hover-colorize' => __( 'Colorize', 'foogallery' ),
					'fg-hover-grayscale' => __( 'Greyscale', 'foogallery' ),
					'fg-hover-scale' => __( 'Scale', 'foogallery' ),
				) ),
                'desc'	  => __( 'Choose what will happen when you hover over a thumbnail', 'foogallery' ),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
                    'data-foogallery-value-selector' => 'input:checked'
                )
            );

			$settings_link = sprintf( '<a target="blank" href="%s">%s</a>', foogallery_admin_settings_url(), __('settings', 'foogallery') );
			$caption_title_source = foogallery_get_setting( 'caption_title_source', 'caption' );

			$fields[] = array(
				'id'      => 'caption_source',
				'title'   => __( 'Caption Title Source', 'foogallery' ),
				'desc'    => __( 'Decide where caption titles are pulled from. By default, what is saved under general settings will be used, but it can be overridden per gallery', 'foogallery' ),
				'section' => __( 'Captions', 'foogallery' ),
				'type'    => 'select',
				'default' => '',
				'choices' => array(
					''        => __( 'Default (as per settings)', 'foogallery' ),
					'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
					'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
					'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
					'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' )
				),
				'row_data'=> array(
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'caption_content',
					'data-foogallery-show-when-field-value' => 'title'
				)
			);

			$caption_desc_source = foogallery_get_setting( 'caption_desc_source', 'desc' );

			$fields[] = array(
				'id'      => 'caption_desc_source',
				'title'   => __( 'Caption Desc. Source', 'foogallery' ),
				'desc'    => __( 'Decide where captions descriptions are pulled from. By default, the general settings are used, but it can be overridden per gallery', 'foogallery' ),
				'section' => __( 'Captions', 'foogallery' ),
				'type'    => 'select',
				'default' => '',
				'choices' => array(
					''        => __( 'Default (as per settings)', 'foogallery' ),
					'title'   => foogallery_get_attachment_field_friendly_name( 'title' ),
					'caption' => foogallery_get_attachment_field_friendly_name( 'caption' ),
					'alt'     => foogallery_get_attachment_field_friendly_name( 'alt' ),
					'desc'    => foogallery_get_attachment_field_friendly_name( 'desc' )
				),
				'row_data'=> array(
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'caption_content',
					'data-foogallery-show-when-field-value' => 'desc'
				)
			);

            return $fields;
        }
    }
}