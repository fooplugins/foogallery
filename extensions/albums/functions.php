<?php

/**
 * Builds up a FooGallery album shortcode
 *
 * @param $album_id
 *
 * @return string
 */
function foogallery_build_album_shortcode( $album_id ) {
	return '[' . foogallery_album_shortcode_tag() . ' id="' . $album_id . '"]';
}

/**
 * Returns the album shortcode tag
 *
 * @return string
 */
function foogallery_album_shortcode_tag() {
	return apply_filters( 'foogallery_album_shortcode_tag', FOOGALLERY_CPT_ALBUM );
}

/**
 * Return all the album templates used within FooGallery
 *
 * @return array
 */
function foogallery_album_templates() {
	$album_templates[] = array(
		'slug'        => 'default',
		'name'        => __( 'Responsive Album Layout', 'foogallery' ),
		'fields'	  => array(
			array(
				'id'      => 'thumbnail_dimensions',
				'title'   => __( 'Thumbnail Size', 'foogallery' ),
				'desc'    => __( 'Choose the size of your gallery thumbnails.', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'type'    => 'thumb_size',
				'default' => array(
					'width' => get_option( 'thumbnail_size_w' ),
					'height' => get_option( 'thumbnail_size_h' ),
					'crop' => true,
				),
			),
			array(
				'id'      => 'title_bg',
				'title'   => __( 'Title Background Color', 'foogallery' ),
				'desc'    => __( 'The color of the title that overlays the album thumbnails', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#fff'
			),
			array(
				'id'      => 'title_font_color',
				'title'   => __( 'Title Text Color', 'foogallery' ),
				'desc'    => __( 'The color of the title text that overlays the album thumbnails', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'type'    => 'colorpicker',
				'default' => '#000000'
			),
			array(
				'id'      => 'alignment',
				'title'   => __( 'Alignment', 'foogallery' ),
				'desc'    => __( 'The horizontal alignment of the gallery thumbnails inside the album.', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'default' => 'alignment-left',
				'type'    => 'select',
				'choices' => array(
					'alignment-left' => __( 'Left', 'foogallery' ),
					'alignment-center' => __( 'Center', 'foogallery' ),
					'alignment-right' => __( 'Right', 'foogallery' ),
				)
			),
			array(
				'id'      => 'gallery_link',
				'title'   => __( 'Gallery Link', 'foogallery' ),
				'section' => __( 'URL Settings', 'foogallery' ),
				'default' => '',
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'choices' =>  array(
						'' =>  __('Default', 'foogallery'),
						'custom_url' => __('Custom URL', 'foogallery')
				),
				'desc'	  => __( 'You can choose to link each gallery to the default embedded gallery, or you can choose to link to the gallery custom URL (if set).', 'foogallery' ),
			),
			array(
				'id'      => 'gallery_link_format',
				'title'   => __( 'Gallery Link Format', 'foogallery' ),
				'desc'    => __( 'The format of the URL for each individual gallery in the album.', 'foogallery' ),
				'section' => __( 'URL Settings', 'foogallery' ),
				'type'    => 'radio',
				'choices' =>  array(
					'default' =>  __('Pretty, e.g. ', 'foogallery') . '<code>/page-with-album/' . foogallery_album_gallery_url_slug() . '/some-gallery</code>',
					'querystring' => __('Querystring e.g. ', 'foogallery') . '<code>/page-with-album?' . foogallery_album_gallery_url_slug() . '=some-gallery</code>'
				),
				'default' => foogallery_determine_best_link_format_default()
			),
			array(
				'id'	  => 'url_help',
				'title'	  => __( 'Please Note', 'foogallery' ),
				'section' => __( 'URL Settings', 'foogallery' ),
				'type'	  => 'help',
				'help'	  => true,
				'desc'	  => __( 'If you are getting 404\'s when clicking on the album galleries, then change to the querystring format. To force your rewrite rules to flush, simply deactivate and activate the albums extension again.', 'foogallery' ),
			),
			array(
				'id'      => 'album_hash',
				'title'   => __( 'Remember Scroll Position', 'foogallery' ),
				'desc'    => __( 'When a gallery is loaded in your album, the page is refreshed which means the scroll position will be lost .', 'foogallery' ),
				'section' => __( 'URL Settings', 'foogallery' ),
				'type'    => 'radio',
				'choices' =>  array(
					'none' =>  __('Don\'t Remember', 'foogallery'),
					'remember' => __('Remember Scroll Position', 'foogallery')
				),
				'default' => 'none'
			),
			array(
				'id'      => 'gallery_title_size',
				'title'   => __( 'Gallery Title Size', 'foogallery' ),
				'desc'    => __( 'The size of the title when displaying a gallery page.', 'foogallery' ),
				'section' => __( 'Gallery Settings', 'foogallery' ),
				'default' => 'h2',
				'type'    => 'select',
				'choices' => array(
					'h2' => __( 'H2', 'foogallery' ),
					'h3' => __( 'H3', 'foogallery' ),
					'h4' => __( 'H4', 'foogallery' ),
					'h5' => __( 'H5', 'foogallery' ),
					'h6' => __( 'H6', 'foogallery' ),
				)
			),
		)
	);

	$album_templates[] = array(
		'slug'        => 'stack',
		'name'        => __( 'All-In-One Stack Album', 'foogallery' ),
		'fields'	  => array(
			array(
				'id'      => 'lightbox',
				'title'   => __( 'Lightbox', 'foogallery' ),
				'desc'    => __( 'Choose which lightbox you want to use to display images.', 'foogallery' ),
				'type'    => 'lightbox',
			),

			array(
				'id'      => 'thumbnail_dimensions',
				'title'   => __( 'Thumbnail Size', 'foogallery' ),
				'desc'    => __( 'Choose the size of your image stack thumbnails.', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'type'    => 'thumb_size_no_crop',
				'default' => array(
					'width' => get_option( 'thumbnail_size_w' ),
					'height' => get_option( 'thumbnail_size_h' ),
					'crop' => true,
				),
			),

			array(
				'id'      => 'random_angle',
				'title'   => __( 'Thumbnail Rotation', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'desc'    => __( 'Choose how thumbnails in each gallery are shown when clicking an image stack.', 'foogallery' ),
				'type'    => 'radio',
				'default' => 'false',
				'choices' =>  array(
					'false' => __( 'Normal', 'foogallery' ),
					'true' => __( 'Random Angles', 'foogallery' )
				)
			),

			array(
				'id'      => 'gutter',
				'title'   => __( 'Thumbnail Gutter', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'desc'    => __( 'The spacing between each image stack.', 'foogallery' ),
				'type'    => 'number',
				'default' => 50
			),

			array(
				'id'      => 'pile_angles',
				'title'   => __( 'Image Stack Angles', 'foogallery' ),
				'section' => __( 'Thumbnail Settings', 'foogallery' ),
				'desc'    => __( 'The angle of the images behind the thumbnail in each image stack.', 'foogallery' ),
				'type'    => 'radio',
				'default' => '1',
				'choices' =>  array(
					'1' => __( 'Low', 'foogallery' ),
					'2' => __( 'Normal', 'foogallery' ),
					'3' => __( 'More Than Normal', 'foogallery' ),
					'5' => __( 'High', 'foogallery' ),
				)
			)
		)
	);

	return apply_filters( 'foogallery_album_templates', $album_templates );
}

function foogallery_determine_best_link_format_default() {
	global $wp_rewrite;
	if ( '' === $wp_rewrite->permalink_structure ) {
		//we are using ?page_id
		return 'querystring';
	}

	//we are using permalinks
	return 'default';
}

/**
 * Returns the default album template
 *
 * @return string
 */
function foogallery_default_album_template() {
	return foogallery_get_setting( 'album_template' );
}

/**
 * Returns the gallery link url for an album
 *
 * @param $album FooGalleryAlbum
 * @param $gallery FooGallery
 *
 * @return string
 */
function foogallery_album_build_gallery_link( $album, $gallery ) {
	//first check if we want to use custom URL's
	$gallery_link = $album->get_meta( 'default_gallery_link', '' );

	if ( 'custom_url' === $gallery_link ) {
		//check if the gallery has a custom url, and if so, then use it
		$url = get_post_meta( $gallery->ID, 'custom_url', true );
	}

	if ( empty( $url ) ) {
		$slug   = foogallery_album_gallery_url_slug();
		$format = $album->get_meta( 'default_gallery_link_format', 'default' );

		if ( 'default' === $format && 'default' === foogallery_determine_best_link_format_default() ) {
			$url = untrailingslashit( trailingslashit( get_permalink() ) . $slug . '/' . $gallery->slug );
		} else {
			$url = add_query_arg( $slug, $gallery->slug );
		}

		$use_hash = $album->get_meta( 'default_album_hash', 'remember' );

		if ( 'remember' === $use_hash ) {
			//add the album hash if required
			$url .= '#' . $album->slug;
		}
	}

	return apply_filters( 'foogallery_album_build_gallery_link', $url );
}

/**
 * Returns the gallery slug used when generating gallery URL's
 *
 * @return string
 */
function foogallery_album_gallery_url_slug() {
	$slug = foogallery_get_setting( 'album_gallery_slug', 'gallery' );
	return apply_filters( 'foogallery_album_gallery_url_slug', $slug );
}

/**
 * Returns the gallery link target for an album
 *
 * @param $album FooGalleryAlbum
 * @param $gallery FooGallery
 *
 * @return string
 */
function foogallery_album_build_gallery_link_target( $album, $gallery ) {
	//first check if we want to use custom URL's
	$gallery_link = $album->get_meta( 'default_gallery_link', '' );

	if ( 'custom_url' === $gallery_link ) {
		//check if the gallery has a custom target, and if so, then use it
		$target = get_post_meta( $gallery->ID, 'custom_target', true );

		//check if the $target is 'default' and set to '_self'
		if ( 'default' === $target ) {
			$target = '_self';
		}
	}

	if ( empty( $target ) ) {
		$target = '_self';
	}

	return apply_filters( 'foogallery_album_build_gallery_link_target', $target );
}

function foogallery_album_get_current_gallery() {
	$slug = foogallery_album_gallery_url_slug();

	$gallery = get_query_var( $slug );

	if ( empty( $gallery ) ) {
		$gallery = safe_get_from_request( $slug );
	}

	return apply_filters( 'foogallery_album_get_current_gallery', $gallery );
}

function foogallery_album_remove_gallery_from_link() {
	$gallery = foogallery_album_get_current_gallery();
	$slug = foogallery_album_gallery_url_slug();

	$url = untrailingslashit( remove_query_arg( $slug ) );

	return str_replace( $slug . '/' . $gallery, '', $url);
}

/**
 * Get a foogallery album template setting for the current foogallery that is being output to the frontend
 * @param string	$key
 * @param string	$default
 *
 * @return bool
 */
function foogallery_album_template_setting( $key, $default = '' ) {
	global $current_foogallery_album;
	global $current_foogallery_album_arguments;
	global $current_foogallery_album_template;

	$settings_key = "{$current_foogallery_album_template}_{$key}";

	if ( $current_foogallery_album_arguments && array_key_exists( $key, $current_foogallery_album_arguments ) ) {
		//try to get the value from the arguments
		$value = $current_foogallery_album_arguments[ $key ];

	} else if ( $current_foogallery_album->settings && array_key_exists( $settings_key, $current_foogallery_album->settings ) ) {
		//then get the value out of the saved gallery settings
		$value = $current_foogallery_album->settings[ $settings_key ];
	} else {
		//otherwise set it to the default
		$value = $default;
	}

	$value = apply_filters( 'foogallery_album_template_setting-' . $key, $value );

	return $value;
}

/**
 * uninstall all albums and setting for albums
 */
function foogallery_album_uninstall() {
	if ( !current_user_can( 'install_plugins' ) ) exit;

	//delete all albums posts
	global $wpdb;
	$query = "SELECT p.ID FROM {$wpdb->posts} AS p WHERE p.post_type IN (%s)";
	$gallery_post_ids = $wpdb->get_col( $wpdb->prepare( $query, FOOGALLERY_CPT_ALBUM ) );

	if ( !empty( $gallery_post_ids ) ) {
		$deleted = 0;
		foreach ( $gallery_post_ids as $post_id ) {
			$del = wp_delete_post( $post_id );
			if ( false !== $del ) {
				++$deleted;
			}
		}
	}
}

/**
 * Render a foogallery album
 *
 * @param       $album_id int The id of the foogallery album you want to render
 * @param array $args
 */
if (! function_exists( 'foogallery_render_album') ) {
	function foogallery_render_album( $album_id, $args = array() ) {
		//create new instance of template engine
		$engine = new FooGallery_Album_Template_Loader();

		$shortcode_args = wp_parse_args( $args, array(
			'id' => $album_id
		) );

		$engine->render_template( $shortcode_args );
	}
}