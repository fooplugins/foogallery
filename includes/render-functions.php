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
function foogallery_attachment_html_image( $foogallery_attachment, $args = array() ) {
	$attr['src'] = foogallery_attachment_html_image_src( $foogallery_attachment, $args );

	if ( ! empty( $foogallery_attachment->alt ) ) {
		$attr['alt'] = $foogallery_attachment->alt;
	}

    if ( ! empty( $foogallery_attachment->caption ) ) {
        $attr['title'] = $foogallery_attachment->caption;
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

    if ( array_key_exists( 'class', $attr ) ) {
        $attr['class'] .= ' fg-image';
    } else {
        $attr['class'] = 'fg-image';
    }

	$html = '<img ';
	foreach ( $attr as $name => $value ) {
        $name = str_replace(' ', '', $name); //ensure we have no spaces!
		$html .= " $name=" . '"' . esc_attr($value) . '"';
	}
	$html .= ' />';

	return apply_filters( 'foogallery_attachment_html_image', $html, $args, $foogallery_attachment );
}

/**
 * Returns the attachment anchor HTML opening tag
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 * @param bool $output_image
 * @param bool $output_closing_tag
 *
 * @return string
 */
function foogallery_attachment_html_anchor_opening( $foogallery_attachment, $args = array() ) {
    $arg_defaults = array(
        'link' => 'image',
        'custom_link' => $foogallery_attachment->custom_url
    );

    $args = wp_parse_args( $args, $arg_defaults );

    $link = $args['link'];

    if ( 'page' === $link ) {
        //get the URL to the attachment page
        $url = get_attachment_link( $foogallery_attachment->ID );
    } else if ( 'custom' === $link ) {
        $url = $args['custom_link'];
    } else {
        $url = $foogallery_attachment->url;
    }

    //fallback for images that might not have a custom url
    if ( empty( $url ) ) {
        $url = $foogallery_attachment->url;
    }

    $attr = array();

    //only add href and target attributes to the anchor if the link is NOT set to 'none'
    if ( $link !== 'none' ){
        $attr['href'] = $url;
        if ( ! empty( $foogallery_attachment->custom_target ) && 'default' !== $foogallery_attachment->custom_target ) {
            $attr['target'] = $foogallery_attachment->custom_target;
        }
    }

    if ( ! empty( $foogallery_attachment->caption ) ) {
        $attr['data-caption-title'] = $foogallery_attachment->caption;
    }

    if ( !empty( $foogallery_attachment->description ) ) {
        $attr['data-caption-desc'] = $foogallery_attachment->description;
    }

    $attr['data-attachment-id'] = $foogallery_attachment->ID;

    //pull any custom attributes out the args
    if ( isset( $args['link_attributes'] ) && is_array( $args['link_attributes'] ) ) {
        $attr = array_merge( $attr, $args['link_attributes'] );
    }

    $attr = apply_filters( 'foogallery_attachment_html_link_attributes', $attr, $args, $foogallery_attachment );

    if ( array_key_exists( 'class', $attr ) ) {
        $attr['class'] .= ' fg-thumb';
    } else {
        $attr['class'] = 'fg-thumb';
    }

    $attr = array_map( 'esc_attr', $attr );
    $html = '<a ';
    foreach ( $attr as $name => $value ) {
        $html .= " $name=" . '"' . $value . '"';
    }
    $html .= '>';

    return apply_filters( 'foogallery_attachment_html_anchor_opening', $html, $args, $foogallery_attachment );
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
	if ( empty ( $foogallery_attachment->url ) )  {
		return '';
	}

    $html = foogallery_attachment_html_anchor_opening( $foogallery_attachment, $args );

	if ( $output_image ) {
		$html .= foogallery_attachment_html_image( $foogallery_attachment, $args );;
	}

	if ( $output_closing_tag ) {
		$html .= '</a>';
	}

	return apply_filters( 'foogallery_attachment_html_anchor', $html, $args, $foogallery_attachment );
}

/**
 * Returns generic html for captions
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @return string
 */
function foogallery_attachment_html_caption( $foogallery_attachment, $args = array() ) {

	$preset = foogallery_gallery_template_setting( 'caption_preset', 'fg-custom' );

	$html = '';

	if ( 'none' !== $preset ) {
		$caption_html = array();

		$show_caption_title = false;
		$show_caption_desc = false;

		//check if we have provided overrides for the caption title
		if ( isset( $args['override_caption_title'] ) ) {
			$caption_title = $args['override_caption_title'];
			$show_caption_title = true;
		} else {
			$caption_title_source = foogallery_gallery_template_setting( 'caption_title_source', '' );

			//if we need to use the settings, then make sure our source is false
			if ( empty( $caption_title_source ) ) { $caption_title_source = false; }

			if ( 'fg-custom' === $preset ) {
				$show_caption_title = $caption_title_source !== 'none';
			} else {
				//always show both title and desc for the presets
				$show_caption_title = true;
			}

			//get the correct captions
			$caption_title = foogallery_get_caption_title_for_attachment( $foogallery_attachment->_post, $caption_title_source );
		}

		//check if we have provided overrides for the caption description
		if ( isset( $args['override_caption_desc'] ) ) {
			$caption_desc = $args['override_caption_desc'];
			$show_caption_desc = true;
		} else {

			$caption_desc_source = foogallery_gallery_template_setting( 'caption_desc_source', '' );

			//if we need to use the settings, then make sure our source is false
			if ( empty( $caption_desc_source ) ) { $caption_desc_source = false; }

			if ( 'fg-custom' === $preset ) {
				$show_caption_desc = $caption_desc_source !== 'none';
			} else {
				//always show both title and desc for the presets
				$show_caption_desc = true;
			}

			$caption_desc = foogallery_get_caption_desc_for_attachment( $foogallery_attachment->_post, $caption_desc_source );
		}

		if ( $caption_title && $show_caption_title ) {
			$caption_html[] = '<div class="fg-caption-title">' . $caption_title . '</div>';
		}
		if ( $caption_desc && $show_caption_desc ) {
			$caption_html[] = '<div class="fg-caption-desc">' . $caption_desc . '</div>';
		}

		$html = '<figcaption class="fg-caption"><div class="fg-caption-inner">';
		if ( count( $caption_html ) > 0 ) {
			$html .= implode( $caption_html );
		}
		$html .= '</div></figcaption>';
	}

    return apply_filters( 'foogallery_attachment_html_caption', $html, $foogallery_attachment, $args );
}

function foogallery_attachment_html_item_opening($foogallery_attachment, $args = array() ) {

	$classes[] = 'fg-item';

	$classes = apply_filters( 'foogallery_attachment_html_item_classes', $classes, $foogallery_attachment, $args );

	$class_list = '';
	if ( is_array( $classes ) ) {
        $class_list = implode( ' ', $classes );
    }

	$html = '<div class="' . $class_list . '"><figure class="fg-item-inner">';
	return apply_filters( 'foogallery_attachment_html_item_opening', $html, $foogallery_attachment, $args );
}

/**
 * Returns generic html for an attachment
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 * @param $caption_content string Include title, desc, or both
 *
 * @return string
 */
function foogallery_attachment_html( $foogallery_attachment, $args = array() ) {
    $html = foogallery_attachment_html_item_opening( $foogallery_attachment, $args );
    $html .= foogallery_attachment_html_anchor_opening( $foogallery_attachment, $args );
    $html .= foogallery_attachment_html_image( $foogallery_attachment, $args );
    $html .= '</a>';
    $html .= foogallery_attachment_html_caption( $foogallery_attachment, $args );
    $html .= '</figure></div>';
    return $html;
}

