<?php
/**
 *
 * FooGallery helper functions for rendering HTML
 * Created by Brad Vincent
 * Date: 11/07/2017
 *
 * @since 2.0.0
 */

/**
 * Returns the attachment image source only
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 2.0.0
 *
 * @return string
 */
function foogallery_attachment_html_image_src( $foogallery_attachment, $args = array() ) {
	return apply_filters( 'foogallery_attachment_resize_thumbnail', $foogallery_attachment->url, $args, $foogallery_attachment );
}

/**
 * Returns the attachment img HTML
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 2.0.0
 *
 * @return string
 */
function foogallery_attachment_html_image_full( $foogallery_attachment, $args = array() ) {
	$attr['data-src'] = foogallery_attachment_html_image_src( $foogallery_attachment, $args );

	if ( ! empty( $this->alt ) ) {
		$attr['alt'] = $this->alt;
	}

	//pull any custom attributes out the args
	if ( isset( $args['image_attributes'] ) && is_array( $args['image_attributes'] ) ) {
		$attr = array_merge( $attr, $args['image_attributes'] );
	}

	//check for width and height args and add those to the image
	if ( isset( $args['width'] ) && intval( $args['width'] ) > 0 ) {
		$attr['width'] = $args['width'];
	}
	if ( isset( $args['height'] ) && intval( $args['height'] ) > 0 ) {
		$attr['height'] = $args['height'];
	}

	$attr = apply_filters( 'foogallery_attachment_html_image_attributes', $attr, $args, $foogallery_attachment );
	$attr = array_map( 'esc_attr', $attr );
	$html = '<img ';
	foreach ( $attr as $name => $value ) {
		$html .= " $name=" . '"' . $value . '"';
	}
	$html .= ' />';

	return apply_filters( 'foogallery_attachment_html_image', $html, $args, $foogallery_attachment );
}

/**
 * Returns the attachment anchor HTML
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 * @param bool $output_image
 * @param bool $output_closing_tag
 *
 * @return string
 */
function foogallery_attachment_html_anchor( $foogallery_attachment, $args = array(), $output_image = true, $output_closing_tag = true ) {
	if ( empty ( $this->url ) )  {
		return '';
	}

	$arg_defaults = array(
		'link' => 'image',
		'custom_link' => $this->custom_url
	);

	$args = wp_parse_args( $args, $arg_defaults );

	$link = $args['link'];

	$img = $this->html_img( $args );

	if ( 'page' === $link ) {
		//get the URL to the attachment page
		$url = get_attachment_link( $this->ID );
	} else if ( 'custom' === $link ) {
		$url = $args['custom_link'];
	} else {
		$url = $this->url;
	}

	//fallback for images that might not have a custom url
	if ( empty( $url ) ) {
		$url = $this->url;
	}

	$attr = array();

	//only add href and target attributes to the anchor if the link is NOT set to 'none'
	if ( $link !== 'none' ){
		$attr['href'] = $url;
		if ( ! empty( $this->custom_target ) && 'default' !== $this->custom_target ) {
			$attr['target'] = $this->custom_target;
		}
	}

	if ( ! empty( $this->caption ) ) {
		$attr['data-caption-title'] = $this->caption;
	}

	if ( !empty( $this->description ) ) {
		$attr['data-caption-desc'] = $this->description;
	}

	$attr['data-attachment-id'] = $this->ID;

	//pull any custom attributes out the args
	if ( isset( $args['link_attributes'] ) && is_array( $args['link_attributes'] ) ) {
		$attr = array_merge( $attr, $args['link_attributes'] );
	}

	$attr = apply_filters( 'foogallery_attachment_html_link_attributes', $attr, $args, $foogallery_attachment );
	$attr = array_map( 'esc_attr', $attr );
	$html = '<a ';
	foreach ( $attr as $name => $value ) {
		$html .= " $name=" . '"' . $value . '"';
	}
	$html .= '>';
	if ( $output_image ) {
		$html .= $img;
	}
	if ( $output_closing_tag ) {
		$html .= '</a>';
	};

	return apply_filters( 'foogallery_attachment_html_link', $html, $args, $foogallery_attachment );
}

/**
 * Returns generic html for captions
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param $caption_content string Include title, desc, or both
 *
 * @return string
 */
function foogallery_attachment_html_caption( $foogallery_attachment, $caption_content ) {
	$html = '';
	$caption_html = array();
	if ( $foogallery_attachment->caption && ( 'title' === $caption_content || 'both' === $caption_content ) ) {
		$caption_html[] = '<div class="foogallery-caption-title">' . $this->caption . '</div>';
	}
	if ( $foogallery_attachment->description && ( 'desc' === $caption_content || 'both' === $caption_content ) ) {
		$caption_html[] = '<div class="foogallery-caption-desc">' . $this->description . '</div>';
	}

	if ( count($caption_html) > 0 ) {
		$html = '<div class="foogallery-caption"><div class="foogallery-caption-inner">';
		$html .= implode( $caption_html );
		$html .= '</div></div>';
	}

	return apply_filters( 'foogallery_attachment_html_caption', $html, $caption_content, $this );
}