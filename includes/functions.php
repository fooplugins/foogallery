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
		'gallery_sorting'            => '',
		'datasource'				 => 'media_library'
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
	return admin_url( add_query_arg( array( 'page' => FOOGALLERY_ADMIN_MENU_HELP_SLUG ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Returns the FooGallery settings page Url within the admin
 *
 * @return string The Url to the FooGallery settings page in admin
 */
function foogallery_admin_settings_url() {
	return admin_url( add_query_arg( array( 'page' => FOOGALLERY_ADMIN_MENU_SETTINGS_SLUG ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Returns the FooGallery extensions page Url within the admin
 *
 * @return string The Url to the FooGallery extensions page in admin
 */
function foogallery_admin_extensions_url() {
	return admin_url( add_query_arg( array( 'page' => FOOGALLERY_ADMIN_MENU_EXTENSIONS_SLUG ), foogallery_admin_menu_parent_slug() ) );
}

/**
 * Returns the FooGallery system info page Url within the admin
 *
 * @return string The Url to the FooGallery system info page in admin
 */
function foogallery_admin_systeminfo_url() {
	return admin_url( add_query_arg( array( 'page' => FOOGALLERY_ADMIN_MENU_SYSTEMINFO_SLUG ), foogallery_admin_menu_parent_slug() ) );
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
	$attributes['id'] = 'foogallery-gallery-' . $gallery->ID;

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

	return $html;
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

	return json_encode( $options );
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
		$source = foogallery_caption_title_source();
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
		$source = foogallery_caption_desc_source();
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
            echo '<span style="color:#0c0">' . __('Thumbnail generation test ran successfully.', 'foogallery') . '</span>';
        } else {
            echo '<span style="color:#c00">' . __('Thumbnail generation test failed!', 'foogallery') . '</span>';
            var_dump( $results['error'] );
			var_dump( $results['file_info'] );
        }
	}
	catch (Exception $e) {
		echo 'Exception: ' . $e->getMessage();
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

	$datasources[$default_datasource] = 'FooGalleryDatasource_MediaLibrary';

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
 * Instantiates a FooGallery datasource based on a datasource name
 *
 * @param $datasource_name string
 *
 * @return IFooGalleryDatasource
 */
function foogallery_instantiate_datasource( $datasource_name ) {
	$datasources = foogallery_gallery_datasources();
	if ( array_key_exists( $datasource_name, $datasources ) ) {
		return new $datasources[$datasource_name];
	}

	return new FooGalleryDatasource_MediaLibrary();
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
	delete_option( FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY );
	delete_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS );
	delete_option( FOOGALLERY_EXTENSIONS_LOADING_ERRORS_RESPONSE );
	delete_option( FOOGALLERY_EXTENSIONS_SLUGS_OPTIONS_KEY );
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

    foreach ( $fields as &$field ) {
        //allow for the field to be altered by extensions. Also used by the build-in fields, e.g. lightbox
        $field = apply_filters( 'foogallery_alter_gallery_template_field', $field, $template['slug'] );
    }

    return $fields;
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
        'image'  => __( 'Full Size Image (Lightbox)', 'foogallery' ),
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