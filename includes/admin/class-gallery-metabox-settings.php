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

            $border_style_choices = apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_border_style_choices',  array(
                'border-style-square-white' => array( 'label' => __( 'Square white border with shadow' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-square-white.png' ),
                'border-style-circle-white' => array( 'label' => __( 'Circular white border with shadow' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-circle-white.png' ),
                'border-style-square-black' => array( 'label' => __( 'Square Black' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-square-black.png' ),
                'border-style-circle-black' => array( 'label' => __( 'Circular Black' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-circle-black.png' ),
                'border-style-inset' => array( 'label' => __( 'Square Inset' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-square-inset.png' ),
                'border-style-rounded' => array( 'label' => __( 'Plain Rounded' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-plain-rounded.png' ),
                '' => array( 'label' => __( 'Plain' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/border-style-icon-none.png' ),
            ) );
            $fields[] = array(
                'id'      => 'border-style',
                'title'   => __( 'Border Style', 'foogallery' ),
                'desc'    => __( 'The border style applied to each thumbnail', 'foogallery' ),
                'section' => __( 'Look &amp; Feel', 'foogallery' ),
                'type'    => 'icon',
                'default' => 'border-style-square-white',
                'choices' => $border_style_choices
            );

            $fields[] = array(
                'id'      => 'hover-effect-help',
                'title'   => __( 'Hover Effect Help', 'foogallery' ),
                'desc'    => __( 'Captions can be enabled by choosing the "Caption" hover effect below.', 'foogallery' ),
                'section' => __( 'Look &amp; Feel', 'foogallery' ),
                'type'    => 'help'
            );

            $hover_effect_type_choices = apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_type_choices', array(
                ''  => __( 'Icon', 'foogallery' ),
                'hover-effect-tint'   => __( 'Dark Tint', 'foogallery' ),
                'hover-effect-color' => __( 'Colorize', 'foogallery' ),
                'hover-effect-caption' => __( 'Caption', 'foogallery' ),
                'hover-effect-scale' => __( 'Scale', 'foogallery' ),
                'hover-effect-none' => __( 'None', 'foogallery' )
            ) );
            $fields[] = array(
                'id'      => 'hover-effect-type',
                'title'   => __( 'Hover Effect', 'foogallery' ),
                'section' => __( 'Look &amp; Feel', 'foogallery' ),
                'default' => '',
                'type'    => 'radio',
                'choices' => $hover_effect_type_choices,
                'desc'	  => __( 'Choose what will happen when you hover over a thumbnail', 'foogallery' ),
            );

            $hover_effect_choices = apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_choices', array(
                'hover-effect-zoom' => array( 'label' => __( 'Zoom' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom.png' ),
                'hover-effect-zoom2' => array( 'label' => __( 'Zoom 2' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom2.png' ),
                'hover-effect-zoom3' => array( 'label' => __( 'Zoom 3' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom3.png' ),
                'hover-effect-plus' => array( 'label' => __( 'Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-plus.png' ),
                'hover-effect-circle-plus' => array( 'label' => __( 'Cirlce Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-circle-plus.png' ),
                'hover-effect-eye' => array( 'label' => __( 'Eye' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-eye.png' )
            ) );
            $fields[] = array(
                'id'      => 'hover-effect',
                'title'   => __( 'Hover Icon', 'foogallery' ),
                'desc'    => __( 'Choose which icon is shown when you hover over a thumbnail', 'foogallery' ),
                'section' => __( 'Look &amp; Feel', 'foogallery' ),
                'type'    => 'icon',
                'default' => 'hover-effect-zoom',
                'choices' => $hover_effect_choices
            );

            $caption_hover_effect_choices = apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_hover_effect_choices', array(
                'hover-caption-simple'  => __( 'Simple', 'foogallery' ),
                'hover-caption-full-drop'   => __( 'Drop', 'foogallery' ),
                'hover-caption-full-fade' => __( 'Fade In', 'foogallery' ),
                'hover-caption-push' => __( 'Push', 'foogallery' ),
                'hover-caption-simple-always' => __( 'Always Visible', 'foogallery' )
            ) );
            $fields[] = array(
                'id'      => 'caption-hover-effect',
                'title'   => __( 'Caption Type', 'foogallery' ),
                'desc'    => __( 'Choose what the captions will look like and how they will work', 'foogallery' ),
                'section' => __( 'Look &amp; Feel', 'foogallery' ),
                'default' => 'hover-caption-simple',
                'type'    => 'radio',
                'choices' => $caption_hover_effect_choices
            );

            $caption_content_choices = apply_filters( 'foogallery_gallery_template_common_thumbnail_fields_caption_content_choices', array(
                'title'  => __( 'Title Only', 'foogallery' ),
                'desc'   => __( 'Description Only', 'foogallery' ),
                'both' => __( 'Title and Description', 'foogallery' )
            ) );
            $fields[] = array(
                'id'      => 'caption-content',
                'title'   => __( 'Caption Content', 'foogallery' ),
                'desc'    => __( 'Choose what is used for your caption content', 'foogallery' ),
                'section' => __( 'Look &amp; Feel', 'foogallery' ),
                'default' => 'title',
                'type'    => 'radio',
                'choices' => $caption_content_choices
            );

            return $fields;
        }
    }
}