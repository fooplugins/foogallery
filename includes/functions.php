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
 * Return a specific gallery template based on the slug
 * @param $slug
 *
 * @return bool|array
 */
function foogallery_get_gallery_template( $slug ) {
	foreach ( foogallery_gallery_templates() as $template ) {
		if ( $slug == $template['slug'] ) {
			return $template;
		}
	}

	return false;
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

	$value = $foogallery->options()->get( $key, foogallery_get_default( $key, $default ) );

	return apply_filters( 'foogallery_get_setting-' . $key, $value, $default );
}

/**
 * Sets a specific option based on a key
 *
 * @param $key
 * @param $value
 *
 * @return mixed
 */
function foogallery_set_setting( $key, $value ) {
	$foogallery = FooGallery_Plugin::get_instance();

	return $foogallery->options()->save( $key, $value );
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
	$defaults = foogallery_get_default_options();

	// Return the key specified.
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : $default;
}

function foogallery_get_default_options() {
	$defaults = array(
		'gallery_template'           => 'default',
		'gallery_permalinks_enabled' => false,
		'gallery_permalink'          => 'gallery',
		'lightbox'                   => 'foogallery',
		'thumb_jpeg_quality'         => '90',
		'gallery_sorting'            => '',
		'datasource'                 => 'media_library',
        'advanced_attachment_modal'  => 'on',
		'hide_editor_button'         => 'on'
	);

	// A handy filter to override the defaults.
	$defaults = apply_filters( 'foogallery_defaults', $defaults );

	return $defaults;
}

/**
 * Returns the FooGallery Galleries Url within the admin
 *
 * @return string The Url to the FooGallery Gallery listing page in admin
 */
function foogallery_admin_gallery_listing_url() {
	return admin_url( 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
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
	return foogallery_admin_url_for_page( FOOGALLERY_ADMIN_MENU_HELP_SLUG );
}

/**
 * Returns the FooGallery settings page Url within the admin
 *
 * @return string The Url to the FooGallery settings page in admin
 */
function foogallery_admin_settings_url() {
	return foogallery_admin_url_for_page( FOOGALLERY_ADMIN_MENU_SETTINGS_SLUG );
}

/**
 * DEPRECATED!
 *
 * @return string The Url to the FooGallery extensions page in admin
 */
function foogallery_admin_extensions_url() {
	return '';
}

/**
 * Returns the FooGallery features page Url within the admin
 *
 * @return string The Url to the FooGallery extensions page in admin
 */
function foogallery_admin_features_url() {
    return foogallery_admin_url_for_page( FOOGALLERY_ADMIN_MENU_FEATURES_SLUG );
}

/**
 * Returns the FooGallery system info page Url within the admin
 *
 * @return string The Url to the FooGallery system info page in admin
 */
function foogallery_admin_systeminfo_url() {
	return foogallery_admin_url_for_page( FOOGALLERY_ADMIN_MENU_SYSTEMINFO_SLUG );
}

/**
 * Returns the FooGallery pricing page Url within the admin
 *
 * @return string The Url to the FooGallery pricing page in admin
 */
function foogallery_admin_pricing_url() {
	return foogallery_admin_url_for_page( FOOGALLERY_ADMIN_MENU_PRICING_SLUG );
}

/**
 * Returns the FooGallery free trial pricing page Url within the admin
 *
 * @return string The Url to the FooGallery free trial page in admin
 */
function foogallery_admin_freetrial_url() {
	return add_query_arg( 'trial', 'true', foogallery_admin_pricing_url() );
}

/**
 * Returns the FooGallery Url within the admin for a specific page
 *
 * @param string $admin_page The page to get the Url for
 *
 * @return string The Url to the FooGallery system info page in admin
 */
function foogallery_admin_url_for_page( $admin_page ) {
	return admin_url( add_query_arg( array( 'page' => $admin_page ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Get a foogallery template setting for the current foogallery that is being output to the frontend
 * @param string	$key
 * @param string	$default
 *
 * @return mixed
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
 * Returns the gallery template setting for lightbox
 *
 * @return string
 */
function foogallery_gallery_template_setting_lightbox() {
    return foogallery_gallery_template_setting('lightbox', 'foogallery' );
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
        apply_filters( 'foogallery_admin_menu_capability', $capability ),
		$menu_slug,
		$function
	);
}

/**
 * Returns all FooGallery galleries
 *
 * @return FooGallery[] array of FooGallery galleries
 */
function foogallery_get_all_galleries( $excludes = false, $extra_args = false ) {
	$args = array(
		'post_type'     => FOOGALLERY_CPT_GALLERY,
		'post_status'	=> array( 'publish', 'draft' ),
		'cache_results' => false,
		'nopaging'      => true,
	);


	if ( is_array( $excludes ) ) {
		$args['post__not_in'] = $excludes;
	}

	if ( is_array( $extra_args ) ) {
		$args = array_merge( $args, $extra_args );
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

	$classes[] = 'foogallery';
	$classes[] = 'foogallery-container';
	$classes[] = "foogallery-{$gallery->gallery_template}";

	$num_args = func_num_args();

	if ( $num_args > 1 ) {
		$arg_list = func_get_args();
		for ( $i = 1; $i < $num_args; $i++ ) {
			$classes[] = $arg_list[$i];
		}
	}

	$classes = apply_filters( 'foogallery_build_class_attribute', $classes, $gallery );

	//extract any classes from the gallery arguments
	global $current_foogallery_arguments;
	if ( isset( $current_foogallery_arguments ) && is_array( $current_foogallery_arguments ) ) {
		if ( array_key_exists( 'classname', $current_foogallery_arguments ) ) {
			$classes[] = $current_foogallery_arguments['classname'];
		}

		if ( array_key_exists( 'classes', $current_foogallery_arguments ) ) {
			$classes[] = $current_foogallery_arguments['classes'];
		}
	}

	$classes = array_filter( $classes, 'strlen' );

	return implode( ' ', $classes );
}

/**
 * Builds up a SAFE class attribute that can be used in a gallery template
 * @param $gallery FooGallery
 *
 * @return string the classname based on the gallery and any extra attributes
 */
function foogallery_build_class_attribute_safe( $gallery ) {
	$args = func_get_args();
	$result = call_user_func_array("foogallery_build_class_attribute", $args);
	return esc_attr( $result );
}

/**
 * Renders an escaped class attribute that can be used directly by gallery templates
 *
 * @param $gallery FooGallery
 */
function foogallery_build_class_attribute_render_safe( $gallery ) {
	$args = func_get_args();
	$result = call_user_func_array("foogallery_build_class_attribute_safe", $args);
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $result is already escaped via esc_attr() in foogallery_build_class_attribute_safe()
	echo $result;
}

/**
 * Builds up the attributes that are appended to a gallery template container
 *
 * @param $gallery    FooGallery
 * @param $attributes array
 *
 * @return string
 */
function foogallery_build_container_attributes_safe( $gallery, $attributes ) {

	//add the default gallery id
	$attributes['id'] = $gallery->container_id();

	//add the standard data-foogallery attribute so that the JS initializes correctly
    $attributes['data-foogallery'] = foogallery_build_container_data_options( $gallery, $attributes );

	//allow others to add their own attributes globally
	$attributes = apply_filters( 'foogallery_build_container_attributes', $attributes, $gallery );

	//allow others to add their own attributes for a specific gallery template
	$attributes = apply_filters( 'foogallery_build_container_attributes-' . $gallery->gallery_template, $attributes, $gallery );

	//clean up the attributes to make them safe for output
	$html = '';
	foreach( $attributes as $key=>$value) {
		$safe_value = esc_attr( $value );
		$html .= "{$key}=\"{$safe_value}\" ";
	}

	return apply_filters( 'foogallery_build_container_attributes_html', $html, $attributes, $gallery );
}

/**
 * Builds up the data-foogallery attribute options that is used by the core javascript
 *
 * @param $gallery
 * @param $attributes
 *
 * @return string
 */
function foogallery_build_container_data_options( $gallery, $attributes ) {
	$options = apply_filters( 'foogallery_build_container_data_options', array(), $gallery, $attributes );

	$options = apply_filters( 'foogallery_build_container_data_options-'. $gallery->gallery_template, $options, $gallery, $attributes );

	return foogallery_json_encode( $options );
}

/**
 * Render a foogallery
 *
 * @param       $gallery_id int The id of the foogallery you want to render
 * @param array $args
 */
function foogallery_render_gallery( $gallery_id, $args = array()) {
	//create new instance of template engine
	$engine = new FooGallery_Template_Loader();

	$shortcode_args = wp_parse_args( $args, array(
		'id' => $gallery_id
	) );

	$engine->render_template( $shortcode_args );
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
 * @deprecated 1.4.7 Default templates loaded by default and no longer activated via extension
 *
 * Activate the default templates extension when there are no gallery templates loaded
 */
function foogallery_activate_default_templates_extension() {
    //no longer needed but left in case any 3rd party extensions call this function
    _deprecated_function( __FUNCTION__, '1.4.7' );
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
	$src = apply_filters( 'foogallery_enqueue_style_src', $src, $handle );

	//resolve the asset URL to a fingerprinted version if available.
	$src = foogallery_resolve_asset_url( $src );

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
 * @param WP_Post $attachment_post
 * @param bool $source
 *
 * @return string
 */
function foogallery_get_caption_title_for_attachment($attachment_post, $source = false) {
	if ( false === $source ) {
		$source = foogallery_gallery_template_setting( 'caption_title_source', false );
		if ( empty( $source ) || "none" === $source ) {
			$source = foogallery_caption_title_source();
		}
	}

	switch ( $source ) {
		case 'title':
			$caption = trim( $attachment_post->post_title );
			break;
		case 'desc':
			$caption = trim( $attachment_post->post_content );
			break;
		case 'alt':
			$caption = trim( get_post_meta( $attachment_post->ID, '_wp_attachment_image_alt', true ) );
			break;
		default:
			$caption = trim( $attachment_post->post_excerpt );
	}

	return apply_filters( 'foogallery_get_caption_title_for_attachment', $caption, $attachment_post );
}

/**
 * Returns the attachment caption title based on the caption_title_source setting
 *
 * @param FooGalleryAttachment $attachment
 * @param string $source
 * @param string $caption_type The type of caption (title or desc)
 *
 * @return string
 */
function foogallery_get_caption_by_source($attachment, $source, $caption_type) {
	if ( false === $source ) {
		$source = foogallery_gallery_template_setting( 'caption_' . $caption_type . '_source', false );
		if ( empty( $source ) || "none" === $source ) {
			if ( 'title' === $caption_type ) {
				$source = 'caption'; //bad legacy naming!
			} else {
				$source = $caption_type;
			}
		}
	}

	switch ( $source ) {
		case 'title':
			$caption = trim( $attachment->title );
			break;
		case 'desc':
			$caption = trim( $attachment->description );
			break;
		case 'alt':
			$caption = trim( $attachment->alt );
			break;
		case 'caption' :
		default:
			$caption = trim( $attachment->caption );
	}

	return apply_filters( 'foogallery_get_caption_by_source', $caption, $attachment, $source, $caption_type );
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
 * @param WP_Post $attachment_post
 * @param bool $source
 *
 * @return string
 */
function foogallery_get_caption_desc_for_attachment($attachment_post, $source = false) {
	if ( false === $source ) {
		$source = foogallery_gallery_template_setting( 'caption_desc_source', false );
		if ( empty( $source ) || "none" === $source ) {
			$source = foogallery_caption_desc_source();
		}
	}

	if ( is_int( $attachment_post ) ) {
		$attachment_post = get_post( $attachment_post );
	}

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

/**
 * Runs thumbnail tests and outputs results in a table format
 */
function foogallery_output_thumbnail_generation_results() {
	$thumbs = new FooGallery_Thumbnails();
	try {
		$results = $thumbs->run_thumbnail_generation_tests();
        if ( $results['success'] ) {
            echo '<span style="color:#0c0">' . esc_html__('Thumbnail generation test ran successfully.', 'foogallery') . '</span>';
        } else {
            echo '<span style="color:#c00">' . esc_html__('Thumbnail generation test failed!', 'foogallery') . '</span>';
            var_dump( $results['error'] );
			var_dump( $results['file_info'] );
        }
	}
	catch (Exception $e) {
		echo 'Exception: ' . esc_html( $e->getMessage() );
	}
}

/**
 * Returns the URL to the test image
 *
 * @return string
 */
function foogallery_test_thumb_url() {
    return apply_filters( 'foogallery_test_thumb_url', FOOGALLERY_URL . 'assets/logo.png' );
}

/**
 * Return all the gallery datasources used within FooGallery
 *
 * @return array
 */
function foogallery_gallery_datasources() {
	$default_datasource = foogallery_default_datasource();

	$datasources[$default_datasource] = array(
	    'id'     => $default_datasource,
	    'name'   => __( 'Media Library', 'foogalery' ),
        'label'  => __( 'From Media Library', 'foogallery' ),
        'public' => false
    );

	return apply_filters( 'foogallery_gallery_datasources', $datasources );
}

/**
 * Returns the default gallery datasource
 *
 * @return string
 */
function foogallery_default_datasource() {
	return foogallery_get_default( 'datasource', 'media_library' );
}

/**
 * Returns the src to the built-in image placeholder
 * @return string
 */
function foogallery_image_placeholder_src() {
	return apply_filters( 'foogallery_image_placeholder_src', FOOGALLERY_URL . 'assets/image-placeholder.png' );
}

/**
 * Returns the image html for the built-in image placeholder
 *
 * @param array $args
 *
 * @return string
 */
function foogallery_image_placeholder_html( $args ) {
	if ( !isset( $args ) ) {
		$args = array(
			'width' => 150,
			'height' => 150
		);
	}

	$args['src'] = foogallery_image_placeholder_src();
	$args = array_map( 'esc_attr', $args );
	$html = '<img ';
	foreach ( $args as $name => $value ) {
		$html .= " $name=" . '"' . $value . '"';
	}
	$html .= ' />';
	return apply_filters( 'foogallery_image_placeholder_html', $html, $args );
}

/**
 * Returns the thumbnail html for the featured attachment for a gallery.
 * If no featured attachment can be found, then a placeholder image src is returned instead
 *
 * @param FooGallery $gallery
 * @param array $args
 *
 * @return string
 */
function foogallery_find_featured_attachment_thumbnail_html( $gallery, $args = null ){
    if ( !isset( $gallery ) || false === $gallery ) return '';

	if ( !isset( $args ) ) {
		$args = array(
			'width' => 150,
			'height' => 150
		);
	}

	$featuredAttachment = $gallery->featured_attachment();
	if ( $featuredAttachment ) {
		return $featuredAttachment->html_img( $args );
	} else {
		//if we have no featured attachment, then use the built-in image placeholder
		return foogallery_image_placeholder_html( $args );
	}
}

/**
 * Returns the thumbnail src for the featured attachment for a gallery.
 * If no featured attachment can be found, then a placeholder image src is returned instead
 *
 * @param FooGallery $gallery
 * @param array $args
 *
 * @return string
 */
function foogallery_find_featured_attachment_thumbnail_src( $gallery, $args = null ){
	if ( !isset( $gallery ) || false === $gallery ) return '';

	if ( !isset( $args ) ) {
		$args = array(
			'width' => 150,
			'height' => 150
		);
	}

	$featuredAttachment = $gallery->featured_attachment();
	if ( $featuredAttachment ) {
		return $featuredAttachment->html_img_src( $args );
	} else {
		//if we have no featured attachment, then use the built-in image placeholder
		return foogallery_image_placeholder_src();
	}
}

/**
 * Returns the available retina options that can be chosen
 */
function foogallery_retina_options() {
    return apply_filters( 'foogallery_retina_options', array(
        '2x' => __('2x', 'foogallery'),
        '3x' => __('3x', 'foogallery'),
        '4x' => __('4x', 'foogallery')
    ) );
}

/**
 * Does a full uninstall of the plugin including all data and settings!
 */
function foogallery_uninstall() {

	if ( !current_user_can( 'install_plugins' ) ) exit;

	//delete all gallery posts first
	global $wpdb;
	$query = "SELECT p.ID FROM {$wpdb->posts} AS p WHERE p.post_type IN (%s)";
	$gallery_post_ids = $wpdb->get_col( $wpdb->prepare( $query, FOOGALLERY_CPT_GALLERY ) );

	if ( !empty( $gallery_post_ids ) ) {
		$deleted = 0;
		foreach ( $gallery_post_ids as $post_id ) {
			$del = wp_delete_post( $post_id );
			if ( false !== $del ) {
				++$deleted;
			}
		}
	}

	//delete all options
	if ( is_network_admin() ) {
		delete_site_option( FOOGALLERY_SLUG );
	} else {
		delete_option( FOOGALLERY_SLUG );
	}
	delete_option( FOOGALLERY_OPTION_VERSION );
	delete_option( FOOGALLERY_OPTION_THUMB_TEST );
	delete_option( FOOGALLERY_EXTENSIONS_ACTIVATED_OPTIONS_KEY );
	delete_option( FOOGALLERY_EXTENSIONS_ERRORS_OPTIONS_KEY );

	//let any extensions clean up after themselves
	do_action( 'foogallery_uninstall' );
}

/**
 * Returns an attachment field friendly name, based on a field name that is passed in
 *
 * @param $field
 *
 * @return string
 */
function foogallery_get_attachment_field_friendly_name( $field ) {
	switch ( $field ) {
		case 'title':
			return __( 'Attachment Title', 'foogallery' );
		case 'caption':
			return __( 'Attachment Caption', 'foogallery' );
		case 'desc':
			return __( 'Attachment Description', 'foogallery' );
		case 'alt':
			return __( 'Attachment Alt', 'foogallery' );
	}
}

/**
 * Returns the fields for a specific gallery template
 *
 * @param $template mixed
 * @return mixed
 */
function foogallery_get_fields_for_template( $template ) {

    if ( is_string( $template ) ) {
        $template = foogallery_get_gallery_template( $template );
    }

    $fields = $template['fields'];

    // Allow for extensions to override fields for every gallery template.
    // Also passes the $template along so you can inspect and conditionally alter fields based on the template properties
    $fields = apply_filters( 'foogallery_override_gallery_template_fields', $fields, $template );

    // Allow for extensions to override fields for a specific gallery template.
    // Also passes the $template along so you can inspect and conditionally alter fields based on the template properties
    $fields = apply_filters( "foogallery_override_gallery_template_fields-{$template['slug']}", $fields, $template );

    // Easily remove fields.
	$fields_to_remove = apply_filters( 'foogallery_override_gallery_template_fields_remove', array(), $template );
	$fields_to_remove = apply_filters( "foogallery_override_gallery_template_fields_remove-{$template['slug']}", $fields_to_remove );

	// Easily set defaults for fields.
	$override_fields_defaults = apply_filters( 'foogallery_override_gallery_template_fields_defaults', array(), $template );
	$override_fields_defaults = apply_filters( "foogallery_override_gallery_template_fields_defaults-{$template['slug']}", $override_fields_defaults );

	// Easily hide certain fields.
	$fields_to_hide = apply_filters( 'foogallery_override_gallery_template_fields_hidden', array(), $template );
	$fields_to_hide = apply_filters( "foogallery_override_gallery_template_fields_hidden-{$template['slug']}", $fields_to_hide );

	$indexes_to_remove = array();

	foreach ( $fields as $key => &$field ) {
        // Allow for the field to be altered by extensions. Also used by the build-in fields, e.g. lightbox.
        $field = apply_filters( 'foogallery_alter_gallery_template_field', $field, $template['slug'] );

		if ( in_array( $field['id'], $fields_to_remove ) ) {
			$indexes_to_remove[] = $key;
		} else {
			// Last time to set field defaults.
			if ( array_key_exists( $field['id'], $override_fields_defaults ) ) {
				$field['default'] = $override_fields_defaults[ $field['id'] ];
			}

			// Make fields invisible.
			if ( in_array( $field['id'], $fields_to_hide ) ) {

				// Make sure the field is not visible.
				$field['row_data']['data-foogallery-invisible'] = true;

				// Force the field to not be hidden, which means it's values can be used in previews.
				if ( isset( $field['row_data']['data-foogallery-hidden'] ) ) {
					unset( $field['row_data']['data-foogallery-hidden'] );
				}

				// Remove the conditionals to FORCE the field to never be shown.
				if ( isset( $field['row_data']['data-foogallery-show-when-field'] ) ) {
					unset( $field['row_data']['data-foogallery-show-when-field'] );
				}
				if ( isset( $field['row_data']['data-foogallery-show-when-field-value'] ) ) {
					unset( $field['row_data']['data-foogallery-show-when-field-value'] );
				}
			}
		}
    }

	// remove the fields that were marked for removal.
	foreach ( $indexes_to_remove as $index ) {
		unset( $fields[$index] );
	}

    // Finally, sort the fields.
    uasort( $fields, 'foogallery_sort_template_fields' );

    return $fields;
}

/**
 * Used to sort gallery template fields
 *
 * @param mixed $a
 * @param mixed $b
 *
 * @return int
 */
function foogallery_sort_template_fields( $a, $b ) {
    if ( isset( $a['order'] ) && isset( $b['order'] ) ) {
        if ( $a['order'] === $b['order'] ) {
            return 0;
        }
        return ( $a['order'] < $b['order'] ) ? -1 : 1;
    }

    return 0;
}

/**
 * Builds default settings for the supplied gallery template
 *
 * @param $template_name
 * @return array
 */
function foogallery_build_default_settings_for_gallery_template( $template_name ) {
    $fields = foogallery_get_fields_for_template( $template_name );
    $settings = array();

    //loop through the fields and build up an array of keys and default values
    foreach( $fields as $field ) {
        $default = array_key_exists( 'default', $field ) ? $field['default'] : false;
        if ( !empty( $default ) ) {
            $settings["{$template_name}_{$field['id']}"] = $default;
        }
    }

    return $settings;
}

/**
 * Returns the choices used for the thumb link field type
 * @return array
 */
function foogallery_gallery_template_field_thumb_link_choices() {
    return apply_filters( 'foogallery_gallery_template_field_thumb_links', array(
        'image'  => __( 'Full Size Image', 'foogallery' ),
        'page'   => __( 'Image Attachment Page', 'foogallery' ),
        'custom' => __( 'Custom URL', 'foogallery' ),
        'none'   => __( 'Not linked', 'foogallery' ),
    ) );
}

/**
 * Returns the choices used for the lightbox field type
 * @return array
 */
function foogallery_gallery_template_field_lightbox_choices() {
    $lightboxes = apply_filters( 'foogallery_gallery_template_field_lightboxes', array() );
    $lightboxes['none'] = __( 'None', 'foogallery' );
    return $lightboxes;
}


if ( !function_exists('wp_get_raw_referer') ) {
	/**
	 * Retrieves unvalidated referer from '_wp_http_referer' or HTTP referer.
	 *
	 * Do not use for redirects, use {@see wp_get_referer()} instead.
	 *
	 * @since 1.4.9
	 * @return string|false Referer URL on success, false on failure.
	 */
	function wp_get_raw_referer() {
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			return wp_unslash( $_REQUEST['_wp_http_referer'] );
		} else if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			return wp_unslash( $_SERVER['HTTP_REFERER'] );
		}

		return false;
	}
}

/**
 * Return the attachments for the currently displayed gallery
 *
 * @return array
 */
function foogallery_current_gallery_attachments_for_rendering() {
    global $current_foogallery;

    $attachments = apply_filters( 'foogallery_gallery_attachments_override_for_rendering', false, $current_foogallery );

    if ( $attachments !== false) {
        return $attachments;
    }

    // by default, return all attachments.
    return $current_foogallery->attachments();
}



/**
 * Return attachment ID from a URL
 *
 * @param $url String URL to the image we are checking
 *
 * @return null or attachment ID
 */
function foogallery_get_attachment_id_by_url($url) {
	global $wpdb;
	$query = "SELECT ID FROM {$wpdb->posts} WHERE guid=%s";
	$attachment = $wpdb->get_col( $wpdb->prepare( $query, $url ) );
	if ( count( $attachment ) > 0 ) {
		return $attachment[0];
	}
	return null;
}

/**
 * Safer escaping for HTML attributes.
 *
 * @since 1.4.31
 *
 * @param string $text
 * @return string
 */
function foogallery_esc_attr( $text ) {
	$safe_text = wp_check_invalid_utf8( $text );
	$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES, false, true );
	return $safe_text;
}


/**
 * Create a FooGallery and return the ID
 *
 * @param $template
 * @param $attachment_ids
 *
 * @return int
 */
function foogallery_create_gallery( $template, $attachment_ids ) {

	if ( empty( $template ) ) {
		$template = foogallery_default_gallery_template();
	}

	//create an empty foogallery
	$foogallery_args = array(
		'post_title'  => 'Demo Gallery',
		'post_type'   => FOOGALLERY_CPT_GALLERY,
		'post_status' => 'publish',
	);
	$gallery_id = wp_insert_post( $foogallery_args );

	//set a gallery template
	add_post_meta( $gallery_id, FOOGALLERY_META_TEMPLATE, $template, true );

	$settings = array();

	//set default settings if there are any, and also if the template is the same as the default
	if ( foogallery_default_gallery_template() === $template ) {
		$default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
		if ( $default_gallery_id ) {
			$settings = get_post_meta( $default_gallery_id, FOOGALLERY_META_SETTINGS, true );
			add_post_meta( $gallery_id, FOOGALLERY_META_SETTINGS, $settings, true );
		}
	}

	if ( empty( $settings) ) {
		switch ( $template ) {
			case 'masonry':
				$settings = array(
					'foogallery_items_view' => 'preview',
					'masonry_alignment' =>'fg-center',
					'masonry_border_size' =>'fg-border-thin',
					'masonry_caption_desc_source' =>'',
					'masonry_caption_title_source' =>'',
					'masonry_captions_limit_length' =>'',
					'masonry_custom_settings' =>'',
					'masonry_drop_shadow' =>'fg-shadow-outline',
					'masonry_filtering_type' =>'',
					'masonry_gutter_width' =>'10',
					'masonry_hover_effect_caption_visibility' =>'fg-captions-bottom',
					'masonry_hover_effect_color' =>'',
					'masonry_hover_effect_icon' =>'fg-hover-zoom',
					'masonry_hover_effect_preset' =>'fg-custom',
					'masonry_hover_effect_scale' =>'',
					'masonry_hover_effect_transition' =>'fg-hover-fade',
					'masonry_inner_shadow' =>'',
					'masonry_layout' =>'fixed',
					'masonry_lazyload' =>'',
					'masonry_lightbox' =>'foobox',
					'masonry_loaded_effect' =>'fg-loaded-fade-in',
					'masonry_loading_icon' =>'fg-loading-default',
					'masonry_paging_type' =>'',
					'masonry_rounded_corners' =>'',
					'masonry_state' =>'no',
					'masonry_theme' =>'fg-dark',
					'masonry_thumbnail_link' =>'image',
					'masonry_thumbnail_width' =>'250',
					'masonry_video_autoplay' =>'yes',
					'masonry_video_hover_icon' =>'fg-video-default',
					'masonry_video_size' =>'640x360',
					'masonry_video_sticky_icon' =>'',
				);
		}
	}

	add_post_meta( $gallery_id, FOOGALLERY_META_SETTINGS, $settings, true );

	$attachments = explode( ',', $attachment_ids );
	update_post_meta( $gallery_id, FOOGALLERY_META_ATTACHMENTS, $attachments );

	return $gallery_id;
}


/**
 * Returns an array of marketing demos
 * @return array
 */
function foogallery_marketing_demos() {
	$demos = array();

	$demos[] = array(
		'demo'	  => __('Responsive Image Gallery', 'foogallery'),
		'section' => __('Standard Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/responsive-image-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Masonry Image Gallery', 'foogallery'),
		'section' => __('Standard Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/masonry-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Justified Gallery', 'foogallery'),
		'section' => __('Standard Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/justified-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Image Viewer Gallery', 'foogallery'),
		'section' => __('Standard Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/image-viewer-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Simple Portfolio Gallery', 'foogallery'),
		'section' => __('Standard Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/wordpress-portfolio-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Single Thumbnail Gallery', 'foogallery'),
		'section' => __('Standard Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/single-thumbnail-gallery/'
	);

	$demos[] = array(
		'demo'	  => __('Grid PRO Gallery', 'foogallery'),
		'section' => __('PRO Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/grid-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Polaroid PRO Gallery', 'foogallery'),
		'section' => __('PRO Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/polaroid-gallery/'
	);
	$demos[] = array(
		'demo'	  => __('Slider PRO Gallery', 'foogallery'),
		'section' => __('PRO Gallery Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/slider-gallery/'
	);

	$demos[] = array(
		'demo'	  => __('Hover Presets Demo', 'foogallery'),
		'section' => __('PRO Features', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/hover-presets/'
	);
	$demos[] = array(
		'demo'	  => __('Filtering Demos', 'foogallery'),
		'section' => __('PRO Features', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filtering/'
	);
	$demos[] = array(
		'demo'	  => __('Pagination Types Demo', 'foogallery'),
		'section' => __('PRO Features', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pagination/'
	);

	$demos[] = array(
		'demo'	  => __('Video Gallery Demos', 'foogallery'),
		'section' => __('PRO Features', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/video-gallery/'
	);

	$demos[] = array(
		'demo'	  => __('Bulk Copy (admin)', 'foogallery'),
		'section' => __('PRO Features', 'foogallery'),
		'href'	  => 'https://fooplugins.com/bulk-copy-foogallery-pro/'
	);

	$demos[] = array(
		'demo'	  => __('Albums', 'foogallery'),
		'section' => __('Album Demos', 'foogallery'),
		'href'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/wordpress-album-gallery/'
	);

	return $demos;
}


/**
 * Returns an array of the PRO features
 * @return array
 */
function foogallery_marketing_pro_features() {
	$features[] = array(
		'feature' => __( 'Video Galleries', 'foogallery' ),
		'desc'    => __( 'Create beautiful video galleries from YouTube, Vimeo, Facebook, Wistia and more!', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/video-gallery/'
	);
	$features[] = array(
		'feature' => __( 'Media Tags + Filtering', 'foogallery' ),
		'desc'    => __( 'Assign tags to your media, which allows visitors to filter the galleries by tag.', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filtering/'
	);
	$features[] = array(
		'feature' => __( 'More Gallery Templates', 'foogallery' ),
		'desc'    => __( '3 more awesome gallery templates, including Slider, Grid and Polaroid.', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/slider-gallery/'
	);
	$features[] = array(
		'feature' => __( 'Preset Hover Effects', 'foogallery' ),
		'desc'    => __( 'Choose from 11 beautifully designed preset hover effects.', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/hover-presets/'
	);
	$features[] = array(
		'feature' => __( 'Advanced Pagination + Infinite Scroll', 'foogallery' ),
		'desc'    => __( 'Choose from more paging types like numbered, load more or infinite scroll.', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pagination/'
	);
	$features[] = array(
		'feature' => __( 'Animated Loading Effects', 'foogallery' ),
		'desc'    => __( 'Choose from 9 awesome animation effects to display as your galleries load.', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/animated-loaded-effects/'
	);
	$features[] = array(
		'feature' => __( 'Bulk Copy Settings', 'foogallery' ),
		'desc'    => __( 'Bulk copy your gallery settings to other galleries in a flash.', 'foogallery' ),
		'demo'	  => 'https://fooplugins.com/bulk-copy-foogallery-pro/'
	);
	return $features;
}

/**
 * Returns the allowed post types that galleries can be attached to
 * @return array
 */
function foogallery_allowed_post_types_for_usage() {
    $allowed_post_types = apply_filters( 'foogallery_allowed_post_types_for_attachment', array( 'post', 'page' ) );

    // Use foogallery_get_setting to retrieve the selected custom post types.
    $selected_custom_post_types = foogallery_get_setting( 'allowed_custom_post_types', array() );

    if ( ! is_array( $selected_custom_post_types ) ) {
        $selected_custom_post_types = array();
    }

    // Merge the selected custom post types with the default allowed post types.
    $allowed_post_types = array_merge( $allowed_post_types, $selected_custom_post_types );

    return $allowed_post_types;
}

/**
 * Returns true if FooGallery is in debug mode
 * @return bool
 */
function foogallery_is_debug() {
    return foogallery_get_setting( 'enable_debugging', false );
}

/**
 * Get the current gallery in the admin
 * @param $post_gallery
 *
 * @return FooGallery|null
 */
function foogallery_admin_get_current_gallery( $post_gallery ) {
	global $post;
	global $current_foogallery_admin;

	if ( is_admin() && isset( $post ) ) {
		if ( !isset( $current_foogallery_admin ) || $post_gallery->ID !== $post->ID ) {
			$current_foogallery_admin = FooGallery::get( $post_gallery );
		}

		return $current_foogallery_admin;
	}

	return null;
}

/**
 * Takes an RGB string and returns an array of the colors
 * @param string $rgba RBG color string in the format rgb(0,0,0)
 *
 * @return array|int[]
 */
function foogallery_rgb_to_color_array( $rgba ) {
	if ( empty( $rgba ) ) {
		return array(0,0,0);
	}

	preg_match( '/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i', $rgba, $by_color );

	if ( count( $by_color ) >= 3 ) {
		return array( $by_color[1], $by_color[2], $by_color[3] );
	}

	//return black if there was a problem getting the color
	return array(0,0,0);
}

/**
 * Do full sanitization of a string
 *
 * @param string $text
 *
 * @return string
 */
function foogallery_sanitize_full( $text ) {
	return foogallery_sanitize_html( foogallery_sanitize_javascript( $text ) );
}

/**
 * Sanitize HTML to make it safe to output. Used to sanitize potentially harmful HTML used for captions
 *
 * @since 1.9.23
 *
 * @param string $text
 * @return string
 */
function foogallery_sanitize_html( $text ) {
	$safe_text = wp_kses_post( $text );
	return $safe_text;
}


/**
 * Filter out JavaScript-related keywords and inline scripts from an input string
 *
 * @param string $input
 * @return string
 */
function foogallery_sanitize_javascript( $input ) {
    // list of JavaScript-related attributes to filter out
    $javascript_attributes = array(
        'innerHTML',
        'document\.write',
        'eval',
        'Function\(',
        'setTimeout',
        'setInterval',
        'new Function\(',
        'onmouseover',
        'onmouseout',
        'onpointerenter',
        'onclick',
        'onload',
        'onchange',
        'onerror',
        '<script>',
        '<\/script>',
        'encodeURIComponent',
        'decodeURIComponent',
        'JSON\.parse',
        'outerHTML',
        'innerHTML',
        'XMLHttpRequest',
        'createElement',
        'appendChild',
        'RegExp',
        'String\.fromCharCode',
        'encodeURI',
        'decodeURI',
        'javascript:'
    );

    $pattern = '/' . implode( '|', $javascript_attributes ) . '/i';

    // Use regex to replace potentially dangerous strings with an empty string
    $input = preg_replace( $pattern, '', $input );

    return $input;
}


/**
 * Returns true if PRO is in use
 * @return bool
 */
function foogallery_is_pro() {
	$pro = false;

	if ( foogallery_fs()->is__premium_only() ) {
		if ( foogallery_fs()->can_use_premium_code() ) {
			$pro = true;
		}
	}

	return $pro;
}

/**
 * Safe function for encoding objects to json
 *
 * @param $value
 *
 * @return false|string
 */
function foogallery_json_encode( $value ) {
	$flags = JSON_UNESCAPED_SLASHES;

	if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
		$flags = JSON_UNESCAPED_UNICODE | $flags;
	}

	$flags = apply_filters( 'foogallery_json_encode_flags', $flags );

	return json_encode( $value, $flags );
}


/**
 * Get a language array entry which gets a value from settings
 * @param $setting_key
 * @param $default
 *
 * @return string|false
 */
function foogallery_get_language_array_value( $setting_key, $default ) {
	$setting_value = foogallery_get_setting( $setting_key, $default );
	if ( empty( $setting_value ) ) {
		$setting_value = $default;
	}
	if ( $default !== $setting_value ) {
		return $setting_value;
	}

	return false;
}

/**
 * Safely returns the WP Filesystem instance for use in FooGallery
 *
 * @return WP_Filesystem_Base
 */
function foogallery_wp_filesystem() {
	global $wp_filesystem;

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	if ( ! WP_Filesystem( true ) ) {
		return false;
	}

	return $wp_filesystem;
}

/**
 * Returns a formatted date
 *
 * @param        $timestamp
 * @param string $format
 *
 * @return string
 */
function foogallery_format_date( $timestamp, $format = null ) {
	if ( !$format ) {
		$format = get_option('date_format');
	}

	if ( function_exists( 'wp_date' ) ) {
		return wp_date( $format , $timestamp );
	} else {
		$datetime = date_create( '@' . $timestamp );
		$timezone = wp_timezone();
		$datetime->setTimezone( $timezone );
		return $datetime->format( $format );
	}
}

/**
 * Shortcut method to safely check if the current gallery template supports a specific feature
 *
 * e.g. panel_support, preview_support, common_fields_support, lazyload_support, paging_support, filtering_support
 *
 * @param      $feature_to_check
 * @param bool $value_to_check
 *
 * @return bool
 */
function foogallery_current_gallery_check_template_has_supported_feature( $feature_to_check, $value_to_check = true ) {
	global $current_foogallery;

	//get out early if there is no current gallery
	if ( !isset( $current_foogallery ) ) {
		return false;
	}

	//check if we have previously checked before recently
	if ( isset( $current_foogallery->supports ) && is_array( $current_foogallery->supports ) && array_key_exists( $feature_to_check, $current_foogallery->supports ) ) {
		return $current_foogallery->supports[$feature_to_check] === $value_to_check;
	} else {

		//check if we need to init the array
		if ( !isset( $current_foogallery->supports ) || !is_array( $current_foogallery->supports ) ) {
			$current_foogallery->supports = array();
		}

		if ( !array_key_exists( $feature_to_check, $current_foogallery->supports ) ) {
			$template_object = foogallery_get_gallery_template( $current_foogallery->gallery_template );
			if ( $template_object && is_array( $template_object ) && array_key_exists( $feature_to_check, $template_object ) ) {
				$current_foogallery->supports[$feature_to_check] = $template_object[$feature_to_check];
			} else {
				//this is not stored against the template config, so assume it does not have the feature support
				$current_foogallery->supports[$feature_to_check] = false;
			}
		}
		return $current_foogallery->supports[$feature_to_check] === $value_to_check;
	}
}

/**
 * Checks to see if we have a cached value stored against the current gallery
 * Certain values are cached against the gallery if they have to be done multiple times, for example for each item in the gallery
 *
 * @param $cache_key
 *
 * @return bool
 */
function foogallery_current_gallery_has_cached_value( $cache_key ) {
	global $current_foogallery;

	//get out early if there is no current gallery
	if ( !isset( $current_foogallery ) ) {
		return true; //this is to ensure we short-circuit having to calculate the cached value later
	}

	return isset( $current_foogallery->cached_values ) && is_array( $current_foogallery->cached_values ) && array_key_exists( $cache_key, $current_foogallery->cached_values );
}

/**
 * Stores a value against the current gallery
 *
 * @param $cache_key
 * @param $cache_value
 */
function foogallery_current_gallery_set_cached_value( $cache_key, $cache_value ) {
	global $current_foogallery;

	//get out early if there is no current gallery
	if ( !isset( $current_foogallery ) ) {
		return;
	}

	//check if we need to init the array
	if ( !isset( $current_foogallery->cached_values ) || !is_array( $current_foogallery->cached_values ) ) {
		$current_foogallery->cached_values = array();
	}

	//store the value for later use
	$current_foogallery->cached_values[$cache_key] = $cache_value;
}

/**
 * Set the value of a cached value for the current gallery
 *
 * @param $cache_value
 *
 * @return mixed
 */
function foogallery_current_gallery_get_cached_value( $cache_value ) {
	global $current_foogallery;

	//get out early if there is no current gallery
	if ( !isset( $current_foogallery ) ) {
		return false;
	}

	if ( isset( $current_foogallery->cached_values ) && is_array( $current_foogallery->cached_values ) && array_key_exists( $cache_value, $current_foogallery->cached_values ) ) {
		return $current_foogallery->cached_values[ $cache_value ];
	}

	return false;
}

/**
 * functions related to thumbnail generation within FooGallery
 */
/**
 * Returns the array of available engines
 *
 * @return array
 */
function foogallery_thumb_available_engines() {

	$shortpixel_link = '<a href="https://shortpixel.com/otp/af/foowww" target="_blank">' . __( 'ShortPixel Adaptive Images', 'foogallery' ) . '</a>';

    $engines = array(
        'default' => array(
	        'label'       => __( 'Default', 'foogallery' ),
	        'description' => __( 'The default engine used to generate locally cached thumbnails.', 'foogallery' ),
	        'class'       => 'FooGallery_Thumb_Engine_Default',
        ),
        'shortpixel' => array(
	        'label'       => __( 'ShortPixel', 'foogallery' ),
	        'description' => sprintf( __( 'Uses %s to generate all your gallery thumbnails. They will be optimized and offloaded to the ShortPixel global CDN!', 'foogallery' ), $shortpixel_link ),
	        'class'       => 'FooGallery_Thumb_Engine_Shortpixel',
        )
    );

    if ( foogallery_is_debug() ) {
        $engines['dummy'] = array(
            'label'       => __( 'Dummy', 'foogallery' ),
            'description' => __( 'A dummy thumbnail engine that can be used for testing. (uses dummyimage.com)', 'foogallery' ),
            'class'       => 'FooGallery_Thumb_Engine_Dummy',
        );
    }
    return apply_filters( 'foogallery_thumb_available_engines', $engines );
}

/**
 * Returns the active thumb engine, based on settings
 *
 * @return FooGallery_Thumb_Engine
 */
function foogallery_thumb_active_engine() {
    global  $foogallery_thumb_engine ;
    //if we already have an engine, return it early
    if ( isset( $foogallery_thumb_engine ) && is_a( $foogallery_thumb_engine, 'FooGallery_Thumb_Engine' ) ) {
        return $foogallery_thumb_engine;
    }
    $engine = foogallery_get_setting( 'thumb_engine', 'default' );
    $engines = foogallery_thumb_available_engines();
    
    if ( array_key_exists( $engine, $engines ) ) {
        $active_engine = $engines[$engine];
        $foogallery_thumb_engine = new $active_engine['class']();
    } else {
        $foogallery_thumb_engine = new FooGallery_Thumb_Engine_Default();
    }
    
    return $foogallery_thumb_engine;
}

/**
 * Resizes a given image using the active thumb engine.
 *
 * @param       $url
 * @param array $args
 *
 * @return string|void (string) url to the image
 */
function foogallery_thumb( $url, $args = array() ) {
    $engine = foogallery_thumb_active_engine();
    return $engine->generate( $url, $args );
}

/**
 * @param $url string
 *
 * @return string
 */
function foogallery_process_image_url( $url ) {
	return apply_filters( 'foogallery_process_image_url', $url );
}

/**
 * Build up a link to be used in the admin with the correct utm parameters
 *
 * @param      $url             string The original full URL
 * @param      $utm_campaign    string The campaign or page that the link is on
 * @param null $utm_medium      string The medium, so in this case we want to differentiate btw free and pro
 * @param null $utm_content     string Optional extra data that can be used to differentiate between links in the same campaign
 * @param      $utm_source      string The platform where the traffic originates. Should probably always be wp_plugin
 *
 * @return string
 */
function foogallery_admin_url( $url, $utm_campaign, $utm_content = null, $utm_medium = null, $utm_source = 'wp_plugin') {
	if ( is_null( $utm_source ) ) {
		$utm_source = 'wp_plugin';
	}
	if ( is_null( $utm_medium ) ) {
		if ( foogallery_is_pro() ) {
			$utm_medium = 'foogallery_pro';
		} else {
			$utm_medium = 'foogallery_free';
		}
	}
	$params = array(
		'utm_source' => $utm_source,
		'utm_medium' => $utm_medium,
		'utm_campaign' => $utm_campaign
	);

	if ( !is_null( $utm_content ) ) {
		$params['utm_content'] = $utm_content;
	}

	return add_query_arg( $params, $url );
}

/**
 * Returns true if on the plugin activation page
 *
 * @return bool
 */
function foogallery_is_activation_page() {
	$fs = foogallery_fs();

	return $fs->is_activation_page();
}

/**
 * Render an array of debug info
 *
 * @param array $array an array of data to render.
 */
function foogallery_render_debug_array( $array, $level = 0 ) {
	if ( !is_array( $array ) ) {
		return;
	}
    foreach ( $array as $key => $value ) {
		if ( ! empty( $value ) ) {
			if ( $level > 0 ) {
				echo esc_html( str_repeat( '   ', $level ) );
			}
			echo esc_html( $key ) . ' => ';
			if ( is_array( $value ) ) {
				echo "\r\n";
				foogallery_render_debug_array( $value, $level + 1 );
			} else {
				echo esc_html( $value );
				echo "\r\n";
			}
		}
	}
}

/**
 * Insert a new attachment from a URL.
 *
 * @param array $attachment_data The image attachment data.
 *
 * @return false|int|WP_Error
 */
function foogallery_import_attachment( $attachment_data ) {
	// Include image.php so we can call wp_generate_attachment_metadata().
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Get the contents of the picture.
	$response = wp_remote_get( $attachment_data['url'] );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$contents = wp_remote_retrieve_body( $response );

	// Upload and get file data.
	$upload = wp_upload_bits( basename( $attachment_data['url'] ), null, $contents );
	if ( array_key_exists( 'error', $upload ) && false !== $upload['error'] ) {
		return new WP_Error( 'foogallery_import_attachment_upload_fail', $upload['error'] );
	}
	$guid      = $upload['url'];
	$file      = $upload['file'];
	$file_type = wp_check_filetype( basename( $file ), null );

	// Create attachment.
	$attachment_args = array(
		'ID'             => 0,
		'guid'           => $guid,
		'post_title'     => $attachment_data['title'],
		'post_excerpt'   => $attachment_data['caption'],
		'post_content'   => isset( $attachment_data['description'] ) ? $attachment_data['description'] : '',
		'post_date'      => '',
		'post_mime_type' => isset( $attachment_data['mime_type'] ) ? $attachment_data['mime_type'] : $file_type['type'],
	);

	$attachment_args['meta_input'] = array();

	if ( isset( $attachment_data['alt'] ) && ! empty( $attachment_data['alt'] ) ) {
		$attachment_args['meta_input']['_wp_attachment_image_alt'] = $attachment_data['alt'];
	}

	if ( isset( $attachment_data['custom_url'] ) && ! empty( $attachment_data['custom_url'] ) ) {
		$attachment_args['meta_input']['_foogallery_custom_url'] = $attachment_data['custom_url'];
	}

	if ( isset( $attachment_data['custom_target'] ) && ! empty( $attachment_data['custom_target'] ) ) {
		$attachment_args['meta_input']['_foogallery_custom_target'] = $attachment_data['custom_target'];
	}

	if ( isset( $attachment_data['video'] ) && ! empty( $attachment_data['video'] ) ) {
		$attachment_args['meta_input']['_foogallery_video_data'] = array(
			'url' => $attachment_data['video'],
		);
	}

	// Save the original URL, so that we do not import it again!
	$attachment_args['meta_input']['_foogallery_imported_from'] = $attachment_data['url'];

	// Insert the attachment.
	$attachment_id = wp_insert_attachment( $attachment_args, $file, 0, true );
	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}
	$attachment_meta = wp_generate_attachment_metadata( $attachment_id, $file );
	wp_update_attachment_metadata( $attachment_id, $attachment_meta );

	if ( isset( $attachment_data['tags'] ) && is_array( $attachment_data['tags'] ) && count( $attachment_data['tags'] ) > 0 ) {
		if ( taxonomy_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG ) ) {
			// Save tags.
			wp_set_object_terms( $attachment_id, $attachment_data['tags'], FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, false );
		}
	}

	if ( isset( $attachment_data['categories'] ) && is_array( $attachment_data['categories'] ) && count( $attachment_data['categories'] ) > 0 ) {
		if ( taxonomy_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY ) ) {
			// Save categories.
			wp_set_object_terms( $attachment_id, $attachment_data['categories'], FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, false );
		}
	}

	return $attachment_id;
}

/**
 * Returns an array of data associated with the attachment, including full size image URL, full size width and height.
 *
 * @param int $attachment_id The attachment ID.
 *
 * @return array|false
 */
function foogallery_get_full_size_image_data( $attachment_id ) {
	// Get the URL to the full size image.
	$src = wp_get_attachment_url( $attachment_id );

	// If we cannot get an attachment URL, then get out early.
	if ( false === $src ) {
		return false;
	}

	// First try to get the image metadata.
	$image_data = wp_get_attachment_metadata( $attachment_id );

    $width = $height = 0;

    if ( is_array( $image_data ) ) {
        if ( array_key_exists( 'width', $image_data ) ) {
            $width = $image_data['width'];
        }
        if ( array_key_exists( 'height', $image_data ) ) {
            $height = $image_data['height'];
        }
    } else {
		$image_data = wp_get_attachment_image_src( $attachment_id, 'full' );
        $width = $image_data[1];
        $height = $image_data[2];
	}

    // Do a last check for the height and width.
    if ( $width === $height && 0 === $height ) {
		// If nothing is stored in meta, then get the size from the physical file. Not ideal, but might be needed in some cases.
		list( $width, $height ) = wp_getimagesize( $src );
	}

	return array( $src, $width, $height );
}

/**
 * Generate an SVG image placeholder
 *
 * @param $w
 * @param $h
 *
 * @return string
 */
function foogallery_get_svg_placeholder_image( $w, $h ) {
	return 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22' . $w . '%22%20height%3D%22' . $h . '%22%20viewBox%3D%220%200%20' . $w . '%20' . $h . '%22%3E%3C%2Fsvg%3E';
}

/**
 * Extracts the gallery ID from a full gallery ID
 *
 * @param $full_gallery_id
 *
 * @return int
 */
function foogallery_extract_gallery_id( $full_gallery_id ) {
	preg_match_all('/^.*?(\d+?)(?:_|$)/', $full_gallery_id, $matches );
	if ( is_array( $matches ) ) {
		return intval( $matches[1][0] );
	}
	return 0;
}

/**
 * Return the index of a specific section with the gallery template fields array
 *
 * @param $fields
 * @param $section
 *
 * @return int
 */
function foogallery_admin_fields_find_index_of_section( $fields, $section ) {
	$index = 0;
	foreach ( $fields as $field ) {
		if ( isset( $field['section'] ) && $section === $field['section'] ) {
			return $index;
		}
		$index++;
	}
	return $index;
}

/**
 * Return the index of a specific field within the gallery template fields array
 *
 * @param $fields
 * @param $field_id
 *
 * @return int
 */
function foogallery_admin_fields_find_index_of_field( $fields, $field_id ) {
	$index = 0;
	foreach ( $fields as $field ) {
		if ( isset( $field['id'] ) && $field_id === $field['id'] ) {
			return $index;
		}
		$index++;
	}
	return $index;
}

/**
 * Returns true if the field exists in the array
 *
 * @param $fields
 * @param $field_id
 * @return bool
 */
function foogallery_admin_fields_has_field( $fields, $field_id ) {
    foreach ( $fields as $field ) {
        if ( isset( $field['id'] ) && $field_id === $field['id'] ) {
            return true;
        }
    }
    return false;
}

/**
 * Returns the path of the URL
 *
 * @param $url
 *
 * @return false|string
 */
function foogallery_local_url_to_path( $url ) {
	return FooGallery_Thumb_Generator::get_file_path( $url );
}

/**
 * Sanitizes a code field for saving to the database.
 *
 * @param string $text
 *
 * @return false|string
 */
function foogallery_sanitize_code( $text ) {
    if ( !empty( $text ) ){
        $text = wp_check_invalid_utf8( $text, true );
        $text = htmlentities( $text, ENT_NOQUOTES, 'UTF-8', false );
        return apply_filters( 'foogallery_sanitize_code', $text );
    }
    return false;
}

/**
 * Prepares a code field for use after it has been sanitized.
 *
 * @param string $text
 *
 * @return false|string
 */
function foogallery_prepare_code( $text ) {
    if ( !empty( $text ) ){
        $text = html_entity_decode( $text );
        return apply_filters( 'foogallery_prepare_code', $text );
    }
    return false;
}

/**
 * Returns true if the feature is enabled.
 *
 * @param $feature
 * @return bool
 */
function foogallery_feature_enabled( $feature ) {
    global $foogallery_features;

    if ( empty( $foogallery_features ) ) {
        $api = new FooGallery_Extensions_API();
        $foogallery_features = $api->get_all_for_view();
    }

    return array_key_exists( $feature, $foogallery_features ) && $foogallery_features[$feature]['is_active'];
}

/**
 * Returns an array of the pro features available in FooGallery.
 *
 * @return array
 */
function foogallery_pro_features() {
    global $foogallery_pro_features;
    if ( !isset( $foogallery_pro_features ) ) {
        $foogallery_pro_features = include(FOOGALLERY_PATH . 'includes/admin/pro-features.php');
    }
    return $foogallery_pro_features;
}

/**
 * Retrieves the target options for FooGallery attachments.
 *
 * The function retrieves an array of target options that can be used for customizing
 * the behavior of attachment links within the FooGallery plugin.
 *
 * @return array An associative array of target options with keys like '_blank', '_self', etc.
 *
 * @since 1.0.0
 */
function foogallery_get_target_options() {
    /**
     * Filter the target options for FooGallery attachments.
     *
     * @param array $target_options An associative array of target options.
     */
    $target_options = apply_filters( 'foogallery_attachment_field_custom_target_options', array(
        'default' => __( 'Default', 'foogallery' ),
        '_blank'  => __( 'New tab (_blank)', 'foogallery' ),
        '_self'   => __( 'Same tab (_self)', 'foogallery' ),
        'foobox'  => __( 'FooBox', 'foogallery' ),
    ));

    return $target_options;
}

/**
 * If the user can, then create some FooGallery demo content!
 *
 * @return false|int[]
 */
function foogallery_create_demo_content() {

    if ( is_admin() && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
        $importer = new FooGallery_Admin_Demo_Content();
        $results = $importer->import_demo_content();

        foogallery_set_setting('demo_content', 'on');

        return $results;
    }

    return false;
}

/**
 * Returns if Freemius is in anonymous mode.
 *
 * @return false
 */
function foogallery_freemius_is_anonymous() {
    if ( defined( 'FOOPLUGINS_FREEMIUS_ANONYMOUS' ) ) {
        return FOOPLUGINS_FREEMIUS_ANONYMOUS;
    }

    return false;
}

/**
 * Returns the gallery creator role that has been saved in settings.
 *
 * @return string
 */
function foogallery_setting_gallery_creator_role() {
    $gallery_creator_role = foogallery_get_setting( 'gallery_creator_role', 'administrator' );
    if ( empty( $gallery_creator_role ) ) {
        $gallery_creator_role = 'administrator';
    }
    return $gallery_creator_role;
}

/**
 * Returns the role and all roles with higher privileges.
 *
 * @param $role
 * @return array|string[]
 */
function foogallery_get_roles_and_higher( $role ) {
    // Define roles in hierarchical order
    $roles_hierarchy = array(
        'subscriber',
        'contributor',
        'author',
        'editor',
        'administrator',
        'super_admin' // Note: 'super_admin' is used in Multisite networks only
    );

    // Find the index of the input role
    $role_index = array_search( $role, $roles_hierarchy );

    // If the input role is not found, return the input role.
    if ( $role_index === false) {
        // Return the input role, and also admin, as we always want admins to be able to create galleries, when custom roles are set.
        return array( $role, 'administrator', 'super_admin' );
    }

    // Get the roles with the same or higher privileges
    return array_slice( $roles_hierarchy, $role_index );
}

/**
 * Returns a translated string if the 'init' action has been executed.
 *
 * This function acts as a wrapper for the WordPress translation function `__`.
 * If the 'init' action has been triggered, it returns the translated string
 * using the specified domain. Otherwise, it returns the original translation string.
 *
 * @param string $translation The text to be translated.
 * @param string $domain Optional. The text domain. Default 'foogallery'.
 *
 * @return string The translated text if 'init' action has been executed,
 *                otherwise the original text.
 */
function foogallery__( $translation, $domain = 'foogallery' ) {
    if ( did_action( 'init' ) ) {
        return __( $translation, $domain );
    }

    return $translation;
}

/**
 * Formats the caption text for a gallery.
 *
 * @param string $text The caption text to format.
 *
 * @return string The formatted caption text.
 */
function foogallery_format_caption_text( $text ) {
    global $current_foogallery;

	if ( empty( $current_foogallery ) ) {
		return $text;
	}

	//if text contains {{gallery-count}}
	if ( strpos( $text, '{{gallery-count}}' ) !== false ) {
		$text = str_replace( '{{gallery-count}}', $current_foogallery->attachment_count(), $text );
	}

	//if text contains {{gallery-title}}
	if ( strpos( $text, '{{gallery-title}}' ) !== false ) {
		$text = str_replace( '{{gallery-title}}', $current_foogallery->name, $text );
	}

	//if text contains {{gallery-description}}
	if ( strpos( $text, '{{gallery-description}}' ) !== false ) {
		$desc = $current_foogallery->_post->post_content;
		$text = str_replace( '{{gallery-description}}', $desc, $text );
	}

	//if text contains {{attachment
	if ( strpos( $text, '{{attachment' ) !== false ) {
		$featured_attachment = $current_foogallery->featured_attachment();
		if ( $featured_attachment ) {
			//if text contains {{attachment-title}}
			if ( strpos( $text, '{{attachment-title}}' ) !== false ) {
				$text = str_replace( '{{attachment-title}}', $featured_attachment->title, $text );
			}

			//if text contains {{attachment-caption}}
			if ( strpos( $text, '{{attachment-caption}}' ) !== false ) {
				$text = str_replace( '{{attachment-caption}}', $featured_attachment->caption, $text );
			}

			//if text contains {{attachment-alt}}
			if ( strpos( $text, '{{attachment-alt}}' ) !== false ) {
				$text = str_replace( '{{attachment-alt}}', $featured_attachment->alt, $text );
			}

			//if text contains {{attachment-description}}
			if ( strpos( $text, '{{attachment-description}}' ) !== false ) {
				$text = str_replace( '{{attachment-description}}', $featured_attachment->description, $text );
			}
		}
	}

	return $text;
}

/**
 * Safely convert a value to an int.
 *
 * @param $value
 * @param int $default
 *
 * @return int
 */
function foogallery_intval( $value, $default = 0 ) {
	// Already a plain number
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}

	// Backwards compat: extract int
	if ( preg_match( '/\d+$/', $value, $matches ) ) {
		return (int) $matches[0];
	}

	return $default;
}

/**
 * Returns true if we are currently showing a glalery preview.
 */
function foogallery_is_preview() {
	return isset( $GLOBALS['foogallery_gallery_preview'] ) && $GLOBALS['foogallery_gallery_preview'];
}

/**
 * Sort the retrieved attachment posts after the query has executed.
 *
 * @param FooGalleryAttachment[] $attachments Array of attachment objects.
 * @param string $orderby Orderby clause used for the query.
 * @param string $order Order clause used for the query.
 *
 * @return FooGalleryAttachment[] Sorted array of attachment objects.
 */
function foogallery_sort_attachments( $attachments, $orderby, $order ) {
	if ( empty( $attachments ) ) {
		return $attachments;
	}

	$order = ( strtoupper( $order ) === 'ASC' ) ? 'ASC' : 'DESC';

	switch ( $orderby ) {
		case 'date':
			usort( $attachments, function ( $a, $b ) use ( $order ) {
				$first_source  = $a->date ?? '';
				$second_source = $b->date ?? '';
				$first  = strtotime( $first_source ) ?: 0;
				$second = strtotime( $second_source ) ?: 0;
				$comparison = 0;

				if ( $first < $second ) {
					$comparison = -1;
				} elseif ( $first > $second ) {
					$comparison = 1;
				}

				return ( 'ASC' === $order ) ? $comparison : - $comparison;
			} );
			break;
		case 'modified':
			usort( $attachments, function ( $a, $b ) use ( $order ) {
				$first_source  = $a->modified ?? '';
				$second_source = $b->modified ?? '';
				$first  = strtotime( $first_source ) ?: 0;
				$second = strtotime( $second_source ) ?: 0;
				$comparison = 0;

				if ( $first < $second ) {
					$comparison = -1;
				} elseif ( $first > $second ) {
					$comparison = 1;
				}

				return ( 'ASC' === $order ) ? $comparison : - $comparison;
			} );
			break;
		case 'title':
			usort( $attachments, function ( $a, $b ) use ( $order ) {
				$comparison = strnatcasecmp( $a->title ?? '', $b->title ?? '' );
				if ( 'ASC' === $order ) {
					return $comparison;
				}

				return - $comparison;
			} );
			break;
		case 'rand':
			shuffle( $attachments );
			break;
	default:
		// For 'post__in' and any other unsupported orderby values we keep the original order when no sort override is set.

		// Check if the attachments have a sort property, and use that to sort.
		$sortable_attachments = array_filter(
			$attachments,
			static function ( $attachment ) {
				return isset( $attachment->sort ) && '' !== $attachment->sort && null !== $attachment->sort;
			}
		);

		if ( ! empty( $sortable_attachments ) ) {
			usort( $attachments, function ( $a, $b ) use ( $order ) {
				$first  = $a->sort ?? '';
				$second = $b->sort ?? '';

				$first_numeric  = is_numeric( $first );
				$second_numeric = is_numeric( $second );

				$comparison = 0;

				if ( $first_numeric || $second_numeric ) {
					$first  = $first_numeric ? (float) $first : PHP_INT_MAX;
					$second = $second_numeric ? (float) $second : PHP_INT_MAX;

					if ( $first < $second ) {
						$comparison = -1;
					} elseif ( $first > $second ) {
						$comparison = 1;
					}
				} else {
					$comparison = strnatcasecmp( (string) $first, (string) $second );
				}

				return $comparison;
			} );
		}
		break;
	}

	return apply_filters( 'foogallery_sort_attachments', $attachments, $orderby, $order );
}

/**
 * Returns the lightbox name for the plugin, that is whitelable safe.
 *
 * @return string
 */
function foogallery_lightbox_name() {
    return sprintf( __( '%s Lightbox', 'foogallery' ), foogallery_plugin_name() );
}

/**
 * Resolve an asset path to its fingerprinted version (if present).
 * Returns the correct URL for enqueueing.
 *
 * @param string $relative_path  Path relative to the plugin root.
 * @return string                Full URL to the asset (fingerprinted or original).
 */
function foogallery_resolve_asset_url( $relative_path ) {
    static $manifest = null;

    if ( $manifest === null ) {
        $manifest_file = FOOGALLERY_PATH . 'includes/asset-manifest.php';
        $manifest = file_exists( $manifest_file ) ? include $manifest_file : [];
    }

    // Use fingerprinted version if available
    $asset_path = isset( $manifest[ $relative_path ] )
        ? $manifest[ $relative_path ]
        : $relative_path;

    return FOOGALLERY_URL . $asset_path;
}
