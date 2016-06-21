<?php
/**
 * FooGallery global functions
 *
 * @package   FooGallery
 * @author    Brad Vincent <brad@fooplugins.com>
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 * @copyright 2014 FooPlugins LLC
 */

/**
 * Returns the name of the plugin. (Allows the name to be overridden from extensions or functions.php)
 * @return string
 */
function foogallery_plugin_name() {
	return apply_filters( 'foogallery_plugin_name', 'FooGallery' );
}

/**
 * Return all the gallery templates used within FooGallery
 *
 * @return array
 */
function foogallery_gallery_templates() {
	return apply_filters( 'foogallery_gallery_templates', array() );
}

/**
 * Return the FooGallery extension API class
 *
 * @return FooGallery_Extensions_API
 */
function foogallery_extensions_api() {
	return new FooGallery_Extensions_API();
}

/**
 * Returns the default gallery template
 *
 * @return string
 */
function foogallery_default_gallery_template() {
	return foogallery_get_setting( 'gallery_template' );
}

/**
 * Returns if gallery permalinks are enabled
 *
 * @return bool
 */
function foogallery_permalinks_enabled() {
	return foogallery_get_setting( 'gallery_permalinks_enabled' );
}

/**
 * Returns the gallery permalink
 *
 * @return string
 */
function foogallery_permalink() {
	return foogallery_get_setting( 'gallery_permalink' );
}

/**
 * Return the FooGallery saved setting, or a default value
 *
 * @param string $key The key for the setting
 *
 * @param bool $default The default if no value is saved or found
 *
 * @return mixed
 */
function foogallery_get_setting( $key, $default = false ) {
	$foogallery = FooGallery_Plugin::get_instance();

	return $foogallery->options()->get( $key, foogallery_get_default( $key, $default ) );
}

/**
 * Builds up a FooGallery gallery shortcode
 *
 * @param $gallery_id
 *
 * @return string
 */
function foogallery_build_gallery_shortcode( $gallery_id ) {
	return '[' . foogallery_gallery_shortcode_tag() . ' id="' . $gallery_id . '"]';
}

/**
 * Returns the gallery shortcode tag
 *
 * @return string
 */
function foogallery_gallery_shortcode_tag() {
	return apply_filters( 'foogallery_gallery_shortcode_tag', FOOGALLERY_CPT_GALLERY );
}

/**
 * Helper method for getting default settings
 *
 * @param string $key The default config key to retrieve.
 *
 * @param bool $default The default if no default is set or found
 *
 * @return string Key value on success, false on failure.
 */
function foogallery_get_default( $key, $default = false ) {

	$defaults = array(
		'gallery_template'           => 'default',
		'gallery_permalinks_enabled' => false,
		'gallery_permalink'          => 'gallery',
		'lightbox'                   => 'none',
		'thumb_jpeg_quality'         => '80',
		'thumb_resize_animations'    => true,
		'gallery_sorting'            => ''
	);

	// A handy filter to override the defaults
	$defaults = apply_filters( 'foogallery_defaults', $defaults );

	// Return the key specified.
	return isset($defaults[ $key ]) ? $defaults[ $key ] : $default;
}

/**
 * Returns the FooGallery Add Gallery Url within the admin
 *
 * @return string The Url to the FooGallery Add Gallery page in admin
 */
function foogallery_admin_add_gallery_url() {
	return admin_url( 'post-new.php?post_type=' . FOOGALLERY_CPT_GALLERY );
}

/**
 * Returns the FooGallery help page Url within the admin
 *
 * @return string The Url to the FooGallery help page in admin
 */
function foogallery_admin_help_url() {
	return admin_url( add_query_arg( array( 'page' => 'foogallery-help' ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Returns the FooGallery settings page Url within the admin
 *
 * @return string The Url to the FooGallery settings page in admin
 */
function foogallery_admin_settings_url() {
	return admin_url( add_query_arg( array( 'page' => 'foogallery-settings' ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Returns the FooGallery extensions page Url within the admin
 *
 * @return string The Url to the FooGallery extensions page in admin
 */
function foogallery_admin_extensions_url() {
	return admin_url( add_query_arg( array( 'page' => 'foogallery-extensions' ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Returns the FooGallery system info page Url within the admin
 *
 * @return string The Url to the FooGallery system info page in admin
 */
function foogallery_admin_systeminfo_url() {
	return admin_url( add_query_arg( array( 'page' => 'foogallery-systeminfo' ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Get a foogallery template setting for the current foogallery that is being output to the frontend
 * @param string	$key
 * @param string	$default
 *
 * @return bool
 */
function foogallery_gallery_template_setting( $key, $default = '' ) {
	global $current_foogallery;
	global $current_foogallery_arguments;
	global $current_foogallery_template;

	$settings_key = "{$current_foogallery_template}_{$key}";

	if ( $current_foogallery_arguments && array_key_exists( $key, $current_foogallery_arguments ) ) {
		//try to get the value from the arguments
		$value = $current_foogallery_arguments[ $key ];

	} else if ( !empty( $current_foogallery ) && $current_foogallery->settings && array_key_exists( $settings_key, $current_foogallery->settings ) ) {
		//then get the value out of the saved gallery settings
		$value = $current_foogallery->settings[ $settings_key ];
	} else {
		//otherwise set it to the default
		$value = $default;
	}

	$value = apply_filters( 'foogallery_gallery_template_setting-' . $key, $value );

	return $value;
}

/**
 * Get the admin menu parent slug
 * @return string
 */
function foogallery_admin_menu_parent_slug() {
	return apply_filters( 'foogallery_admin_menu_parent_slug', FOOGALLERY_ADMIN_MENU_PARENT_SLUG );
}

/**
 * Helper function to build up the admin menu Url
 * @param array $extra_args
 *
 * @return string|void
 */
function foogallery_build_admin_menu_url( $extra_args = array() ) {
	$url = admin_url( foogallery_admin_menu_parent_slug() );
	if ( ! empty( $extra_args ) ) {
		$url = add_query_arg( $extra_args, $url );
	}
	return $url;
}

/**
 * Helper function for adding a foogallery sub menu
 *
 * @param $menu_title
 * @param string $capability
 * @param string $menu_slug
 * @param $function
 */
function foogallery_add_submenu_page( $menu_title, $capability, $menu_slug, $function ) {
	add_submenu_page(
		foogallery_admin_menu_parent_slug(),
		$menu_title,
		$menu_title,
		$capability,
		$menu_slug,
		$function
	);
}

/**
 * Returns all FooGallery galleries
 *
 * @return FooGallery[] array of FooGallery galleries
 */
function foogallery_get_all_galleries( $excludes = false ) {
	$args = array(
		'post_type'     => FOOGALLERY_CPT_GALLERY,
		'post_status'	=> array( 'publish', 'draft' ),
		'cache_results' => false,
		'nopaging'      => true,
	);

	if ( is_array( $excludes ) ) {
		$args['post__not_in'] = $excludes;
	}

	$gallery_posts = get_posts( $args );

	if ( empty( $gallery_posts ) ) {
		return array();
	}

	$galleries = array();

	foreach ( $gallery_posts as $post ) {
		$galleries[] = FooGallery::get( $post );
	}

	return $galleries;
}

/**
 * Parse some content and return an array of all gallery shortcodes that are used inside it
 *
 * @param $content The content to search for gallery shortcodes
 *
 * @return array An array of all the foogallery shortcodes found in the content
 */
function foogallery_extract_gallery_shortcodes( $content ) {
	$shortcodes = array();

	$regex_pattern = foogallery_gallery_shortcode_regex();
	if ( preg_match_all( '/' . $regex_pattern . '/s', $content, $matches ) ) {
		for ( $i = 0; $i < count( $matches[0] ); ++$i ) {
			$shortcode = $matches[0][$i];
			$args = $matches[3][$i];
			$attribure_string = str_replace( ' ', '&', trim( $args ) );
			$attribure_string = str_replace( '"', '', $attribure_string );
			$attributes = wp_parse_args( $attribure_string );
			if ( array_key_exists( 'id', $attributes ) ) {
				$id = intval( $attributes['id'] );
				$shortcodes[ $id ] = $shortcode;
			}
		}
	}

	return $shortcodes;
}

/**
 * Build up the FooGallery shortcode regex
 *
 * @return string
 */
function foogallery_gallery_shortcode_regex() {
	$tag = foogallery_gallery_shortcode_tag();

	return
		'\\['                              	 // Opening bracket
		. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
		. "($tag)"                     		 // 2: Shortcode name
		. '(?![\\w-])'                       // Not followed by word character or hyphen
		. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
		.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
		.     '(?:'
		.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
		.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
		.     ')*?'
		. ')'
		. '(?:'
		.     '(\\/)'                        // 4: Self closing tag ...
		.     '\\]'                          // ... and closing bracket
		. '|'
		.     '\\]'                          // Closing bracket
		.     '(?:'
		.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
		.             '[^\\[]*+'             // Not an opening bracket
		.             '(?:'
		.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
		.                 '[^\\[]*+'         // Not an opening bracket
		.             ')*+'
		.         ')'
		.         '\\[\\/\\2\\]'             // Closing shortcode tag
		.     ')?'
		. ')'
		. '(\\]?)';                          // 6: Optional second closing bracket for escaping shortcodes: [[tag]]
}

/**
 * Builds up a class attribute that can be used in a gallery template
 * @param $gallery FooGallery
 *
 * @return string the classname based on the gallery and any extra attributes
 */
function foogallery_build_class_attribute( $gallery ) {
	$classes[] = 'foogallery-container';
	$classes[] = "foogallery-{$gallery->gallery_template}";
	$num_args = func_num_args();

	if ( $num_args > 1 ) {
		$arg_list = func_get_args();
		for ( $i = 1; $i < $num_args; $i++ ) {
			$classes[] = $arg_list[$i];
		}
	}

	$classes = apply_filters( 'foogallery_build_class_attribute', $classes );

	return implode( ' ', $classes );
}

/**
 * Renders an escaped class attribute that can be used directly by gallery templates
 *
 * @param $gallery FooGallery
 */
function foogallery_build_class_attribute_render_safe( $gallery ) {
	$args = func_get_args();
	$result = call_user_func_array("foogallery_build_class_attribute", $args);
	echo esc_attr( $result );
}

/**
 * Render a foogallery
 *
 * @param $gallery_id int The id of the foogallery you want to render
 */
function foogallery_render_gallery( $gallery_id ) {
	//create new instance of template engine
	$engine = new FooGallery_Template_Loader();

	$engine->render_template( array(
		'id' => $gallery_id
	) );
}

/**
 * Returns the available sorting options that can be chosen for galleries and albums
 */
function foogallery_sorting_options() {
	return apply_filters( 'foogallery_sorting_options', array(
		'' => __('Default', 'foogallery'),
		'date_desc' => __('Date created - newest first', 'foogallery'),
		'date_asc' => __('Date created - oldest first', 'foogallery'),
		'modified_desc' => __('Date modified - most recent first', 'foogallery'),
		'modified_asc' => __('Date modified - most recent last', 'foogallery'),
		'title_asc' => __('Title - alphabetically', 'foogallery'),
		'title_desc' => __('Title - reverse', 'foogallery'),
		'rand' => __('Random', 'foogallery')
	) );
}

function foogallery_sorting_get_posts_orderby_arg( $sorting_option ) {
	$orderby_arg = 'post__in';

	switch ( $sorting_option ) {
		case 'date_desc':
		case 'date_asc':
			$orderby_arg = 'date';
			break;
		case 'modified_desc':
		case 'modified_asc':
			$orderby_arg = 'modified';
			break;
		case 'title_asc':
		case 'title_desc':
			$orderby_arg = 'title';
			break;
		case 'rand':
			$orderby_arg = 'rand';
			break;
	}

	return apply_filters( 'foogallery_sorting_get_posts_orderby_arg', $orderby_arg, $sorting_option );
}

function foogallery_sorting_get_posts_order_arg( $sorting_option ) {
	$order_arg = 'DESC';

	switch ( $sorting_option ) {
		case 'date_asc':
		case 'modified_asc':
		case 'title_asc':
		$order_arg = 'ASC';
			break;
	}

	return apply_filters( 'foogallery_sorting_get_posts_order_arg', $order_arg, $sorting_option );
}

/**
 * Activate the default templates extension when there are no gallery templates loaded
 */
function foogallery_activate_default_templates_extension() {
	$api = foogallery_extensions_api();
	$api->activate( 'default_templates' );
}

/**
 * Allow FooGallery to enqueue stylesheet and allow them to be enqueued in the head on the next page load
 *
 * @param $handle string
 * @param $src string
 * @param array $deps
 * @param bool $ver
 * @param string $media
 */
function foogallery_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	do_action( 'foogallery_enqueue_style', $handle, $src, $deps, $ver, $media );
}


/**
 * Returns all foogallery post objects that are attached to the post
 *
 * @param $post_id int The ID of the post
 *
 * @return array List of foogallery posts.
 */
function foogallery_get_galleries_attached_to_post( $post_id ) {
	$gallery_ids = get_post_meta( $post_id, FOOGALLERY_META_POST_USAGE, false );

	if ( !empty( $gallery_ids ) ) {
		return get_posts( array(
			'post_type'      => array( FOOGALLERY_CPT_GALLERY, ),
			'post_status'    => array( 'draft', 'publish' ),
			'posts_per_page' => -1,
			'include'        => $gallery_ids
		) );
	}

	return array();
}

/**
 * Clears all css load optimization post meta
 */
function foogallery_clear_all_css_load_optimizations() {
	delete_post_meta_by_key( FOOGALLERY_META_POST_USAGE_CSS );
}

/**
 * Performs a check to see if the plugin has been updated, and perform any housekeeping if necessary
 */
function foogallery_perform_version_check() {
	$checker = new FooGallery_Version_Check();
	$checker->perform_check();
}

/**
 * Returns the JPEG quality used when generating thumbnails
 *
 * @return int The quality value stored in settings
 */
function foogallery_thumbnail_jpeg_quality() {
	$quality = intval( foogallery_get_setting( 'thumb_jpeg_quality' ) );

	//check if we get an invalid value for whatever reason and if so return a default of 80
	if ( $quality <= 0 ) {
		$quality = 80;
	}

	return $quality;
}

/**
 * Returns the caption title source setting
 *
 * @return string
 */
function foogallery_caption_title_source() {
	$source = foogallery_get_setting( 'caption_title_source', 'caption' );

	if ( empty( $source ) ) {
		$source = 'caption';
	}

	return $source;
}

/**
 * Returns the attachment caption title based on the caption_title_source setting
 *
 * @param $attachment_post WP_Post
 *
 * @return string
 */
function foogallery_get_caption_title_for_attachment($attachment_post) {
	$source = foogallery_caption_title_source();

	if ( 'title' === $source ) {
		$caption = trim( $attachment_post->post_title );
	} else {
		$caption = trim( $attachment_post->post_excerpt );
	}

	return apply_filters( 'foogallery_get_caption_title_for_attachment', $caption, $attachment_post );
}

/**
 * Returns the caption description source setting
 *
 * @return string
 */
function foogallery_caption_desc_source() {
	$source = foogallery_get_setting( 'caption_desc_source', 'desc' );

	if ( empty( $source ) ) {
		$source = 'desc';
	}

	return $source;
}

/**
 * Returns the attachment caption description based on the caption_desc_source setting
 *
 * @param $attachment_post WP_Post
 *
 * @return string
 */
function foogallery_get_caption_desc_for_attachment($attachment_post) {
	$source = foogallery_caption_desc_source();

	switch ( $source ) {
		case 'title':
			$caption = trim( $attachment_post->post_title );
			break;
		case 'caption':
			$caption = trim( $attachment_post->post_excerpt );
			break;
		case 'alt':
			$caption = trim( get_post_meta( $attachment_post->ID, '_wp_attachment_image_alt', true ) );
			break;
		default:
			$caption = trim( $attachment_post->post_content );
	}

	return apply_filters( 'foogallery_get_caption_desc_for_attachment', $caption, $attachment_post );
}