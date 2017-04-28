<?php
/**
 * FooGallery default extensions common functions
 */

/***
 * Enqueue the imagesLoaded script file
 */
function foogallery_enqueue_imagesloaded_script() {
    global $wp_version;
    if ( version_compare( $wp_version, '4.6' ) >= 0 ) {

        wp_enqueue_script('imagesloaded');

    } else {

        //include our own version of imagesLoaded for <4.6
        $js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/imagesloaded.pkgd.min.js';
        wp_enqueue_script( 'foogallery-imagesloaded', $js, array(), FOOGALLERY_VERSION );
    }
}

/**
 * Enqueue the core FooGallery stylesheet used by all default templates
 */
function foogallery_enqueue_core_gallery_template_style() {
	$css = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'css/foogallery.min.css';
	wp_enqueue_style( 'foogallery-core', $css, array(), FOOGALLERY_VERSION );
}

/**
 * Enqueue the core FooGallery script used by all default templates
 */
function foogallery_enqueue_core_gallery_template_script() {
	$js = FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/foogallery.min.js';
	wp_enqueue_script( 'foogallery-core', $js, array(), FOOGALLERY_VERSION );
}

/**
 * Add common thumbnail fields to a gallery template
 *
 * @param $template
 *
 * @return array
 */
function foogallery_get_gallery_template_common_thumbnail_fields($gallery_template) {

	$template = $gallery_template['slug'];

	$border_style_choices = apply_filters( "foogallery_gallery_template_common_thumbnail_fields_border_style_choices-{$template}",  array(
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
		'desc'    => __( 'The border style for each thumbnail in the gallery.', 'foogallery' ),
		'section' => __( 'Look &amp; Feel', 'foogallery' ),
		'type'    => 'icon',
		'default' => 'border-style-square-white',
		'choices' => $border_style_choices
	);

    $fields[] = array(
        'id'      => 'hover-effect-help',
        'title'   => __( 'Hover Effect Help', 'foogallery' ),
        'desc'    => __( 'Captions can be enabled by choosing the "Captions" hover effect below.', 'foogallery' ),
        'section' => __( 'Look &amp; Feel', 'foogallery' ),
        'type'    => 'help'
    );

	$hover_effect_type_choices = apply_filters( "foogallery_gallery_template_common_thumbnail_fields_hover_effect_type_choices-{$template}", array(
		''  => __( 'Icon', 'foogallery' ),
		'hover-effect-tint'   => __( 'Dark Tint', 'foogallery' ),
		'hover-effect-color' => __( 'Colorize', 'foogallery' ),
		'hover-effect-caption' => __( 'Caption', 'foogallery' ),
		'hover-effect-scale' => __( 'Scale', 'foogallery' ),
		'hover-effect-none' => __( 'None', 'foogallery' )
	) );
	$fields[] = array(
		'id'      => 'hover-effect-type',
		'title'   => __( 'Hover Effect Type', 'foogallery' ),
        'section' => __( 'Look &amp; Feel', 'foogallery' ),
		'default' => '',
		'type'    => 'radio',
		'choices' => $hover_effect_type_choices,
		'desc'	  => __( 'The type of hover effect the thumbnails will use.', 'foogallery' ),
	);

	$hover_effect_choices = apply_filters( "foogallery_gallery_template_common_thumbnail_fields_hover_effect_choices-{$template}", array(
		'hover-effect-zoom' => array( 'label' => __( 'Zoom' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom.png' ),
		'hover-effect-zoom2' => array( 'label' => __( 'Zoom 2' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom2.png' ),
		'hover-effect-zoom3' => array( 'label' => __( 'Zoom 3' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-zoom3.png' ),
		'hover-effect-plus' => array( 'label' => __( 'Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-plus.png' ),
		'hover-effect-circle-plus' => array( 'label' => __( 'Cirlce Plus' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-circle-plus.png' ),
		'hover-effect-eye' => array( 'label' => __( 'Eye' , 'foogallery' ), 'img' => FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'img/admin/hover-effect-icon-eye.png' )
	) );
	$fields[] = array(
		'id'      => 'hover-effect',
		'title'   => __( 'Icon Hover Effect', 'foogallery' ),
		'desc'    => __( 'When the hover effect type of Icon is chosen, you can choose which icon is shown when you hover over each thumbnail.', 'foogallery' ),
        'section' => __( 'Look &amp; Feel', 'foogallery' ),
		'type'    => 'icon',
		'default' => 'hover-effect-zoom',
		'choices' => $hover_effect_choices
	);

	$caption_hover_effect_choices = apply_filters( "foogallery_gallery_template_common_thumbnail_fields_caption_hover_effect_choices-{$template}", array(
		'hover-caption-simple'  => __( 'Simple', 'foogallery' ),
		'hover-caption-full-drop'   => __( 'Drop', 'foogallery' ),
		'hover-caption-full-fade' => __( 'Fade In', 'foogallery' ),
		'hover-caption-push' => __( 'Push', 'foogallery' ),
		'hover-caption-simple-always' => __( 'Always Visible', 'foogallery' )
	) );
	$fields[] = array(
		'id'      => 'caption-hover-effect',
		'title'   => __( 'Caption Effect', 'foogallery' ),
        'section' => __( 'Look &amp; Feel', 'foogallery' ),
		'default' => 'hover-caption-simple',
		'type'    => 'radio',
		'choices' => $caption_hover_effect_choices
	);

	$caption_content_choices = apply_filters( "foogallery_gallery_template_common_thumbnail_fields_caption_content_choices-{$template}", array(
		'title'  => __( 'Title Only', 'foogallery' ),
		'desc'   => __( 'Description Only', 'foogallery' ),
		'both' => __( 'Title and Description', 'foogallery' )
	) );
	$fields[] = array(
		'id'      => 'caption-content',
		'title'   => __( 'Caption Content', 'foogallery' ),
        'section' => __( 'Look &amp; Feel', 'foogallery' ),
		'default' => 'title',
		'type'    => 'radio',
		'choices' => $caption_content_choices
	);

	return $fields;
}