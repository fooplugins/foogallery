<?php
/**
 *
 * FooGallery helper functions for rendering HTML
 * Created by Brad Vincent
 * Date: 11/07/2017
 *
 * @since 1.4.0
 */

/**
 * Returns the attachment image source only
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.0
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
 * @since 1.4.0
 *
 * @return string
 */
function foogallery_attachment_html_image( $foogallery_attachment, $args = array() ) {
	$attr = foogallery_build_attachment_html_image_attributes( $foogallery_attachment, $args );

	$html = '<img ';
	foreach ( $attr as $name => $value ) {
        $name = str_replace(' ', '', $name); //ensure we have no spaces!
		$html .= " $name=" . '"' . foogallery_esc_attr($value) . '"';
	}
	$html .= ' />';

	return apply_filters( 'foogallery_attachment_html_image', $html, $args, $foogallery_attachment );
}

/**
 * Returns the attachment img HTML
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.9
 *
 * @return array
 */
function foogallery_build_attachment_html_image_attributes( $foogallery_attachment, $args = array() ) {
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

	return $attr;
}

/**
 * Returns the attachment anchor HTML opening tag
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.0
 *
 * @return string
 */
function foogallery_attachment_html_anchor_opening( $foogallery_attachment, $args = array() ) {
	$attr = foogallery_build_attachment_html_anchor_attributes( $foogallery_attachment, $args );

    $html = '<a ';
    foreach ( $attr as $name => $value ) {
		$name = str_replace(' ', '', $name); //ensure we have no spaces!
        $html .= " $name=" . '"' . foogallery_esc_attr($value) . '"';
    }
    $html .= '>';

    return apply_filters( 'foogallery_attachment_html_anchor_opening', $html, $args, $foogallery_attachment );
}

/**
 * Returns the array of attributes that will be used on the anchor for a FooGalleryAttachment
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.9
 *
 * @return array
 */
function foogallery_build_attachment_html_anchor_attributes( $foogallery_attachment, $args = array() ) {
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

	//always add the fg-thumb class
	if ( array_key_exists( 'class', $attr ) ) {
		$attr['class'] .= ' fg-thumb';
	} else {
		$attr['class'] = 'fg-thumb';
	}

	return $attr;
}

/**
 * Returns the attachment anchor HTML
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 * @param bool $output_image
 * @param bool $output_closing_tag
 *
 * @since 1.4.0
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
 * Builds up the captions for an attachment
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.9
 *
 * @return array|bool
 */
function foogallery_build_attachment_html_caption( $foogallery_attachment, $args = array() ) {
	$captions = array();

	$preset = foogallery_gallery_template_setting( 'caption_preset', 'fg-custom' );

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
			$captions['title'] = $caption_title;
		}
		if ( $caption_desc && $show_caption_desc ) {
			$captions['desc'] = $caption_desc;
		}

	} else {
		$captions = false;
	}

	return apply_filters( 'foogallery_build_attachment_html_caption', $captions, $foogallery_attachment, $args );
}

/**
 * Returns generic html for captions
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.0
 *
 * @return string
 */
function foogallery_attachment_html_caption( $foogallery_attachment, $args = array() ) {
	$captions = foogallery_build_attachment_html_caption( $foogallery_attachment, $args );
	$html = '';

	if ( $captions !== false ) {
		$html = '<figcaption class="fg-caption"><div class="fg-caption-inner">';
		if ( array_key_exists( 'title', $captions ) ) {
			$html .= '<div class="fg-caption-title">' . $captions['title'] . '</div>';
		}
		if ( array_key_exists( 'desc', $captions ) ) {
			$html .= '<div class="fg-caption-desc">' . $captions['desc'] . '</div>';
		}
		$html .= '</div></figcaption>';
	}

    return apply_filters( 'foogallery_attachment_html_caption', $html, $foogallery_attachment, $args );
}

/**
 * Returns the attachment item opening HTML
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.0
 *
 * @return string
 */
function foogallery_attachment_html_item_opening($foogallery_attachment, $args = array() ) {

	$classes[] = 'fg-item';

	$classes = apply_filters( 'foogallery_attachment_html_item_classes', $classes, $foogallery_attachment, $args );

	$class_list = '';
	if ( is_array( $classes ) ) {
        $class_list = implode( ' ', $classes );
    }

    $attachment_item_figure_class = apply_filters( 'foogallery_attachment_html_item_figure_class', 'fg-item-inner', $foogallery_attachment, $args );
	$html = '<div class="' . $class_list . '"><figure class="'. esc_attr( $attachment_item_figure_class ) . '">';
	return apply_filters( 'foogallery_attachment_html_item_opening', $html, $foogallery_attachment, $args );
}

/**
 * Returns generic html for an attachment
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array $args
 *
 * @since 1.4.0
 *
 * @return string
 */
function foogallery_attachment_html( $foogallery_attachment, $args = array() ) {

    //check if no arguments were passed in, and build them up if so
    if ( empty( $args ) ) {
        $args = foogallery_gallery_template_arguments();
    }

    $html = foogallery_attachment_html_item_opening( $foogallery_attachment, $args );
    $html .= foogallery_attachment_html_anchor_opening( $foogallery_attachment, $args );
    $html .= foogallery_attachment_html_image( $foogallery_attachment, $args );
    $html .= '</a>';
    $html .= foogallery_attachment_html_caption( $foogallery_attachment, $args );
    $html .= '</figure></div>';
    return $html;
}

/**
 * Get the foogallery template arguments for the current foogallery that is being output to the frontend
 *
 * @return array
 */
function foogallery_gallery_template_arguments() {
    global $current_foogallery_template;

    return apply_filters( 'foogallery_gallery_template_arguments-' . $current_foogallery_template, array() );
}

/**
 * Build up an object that will be encoded to JSON for a FooGallery Attachment
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array                $args
 *
 * @since 1.6.0
 *
 * @returns string
 * @return string
 */
function foogallery_build_json_object_from_attachment( $foogallery_attachment, $args = array() ) {
	if ( isset( $foogallery_attachment ) ) {

		//check if no arguments were passed in, and build them up if so
		if ( empty( $args ) ) {
			$args = foogallery_gallery_template_arguments();
		}

		$anchor_attributes = foogallery_build_attachment_html_anchor_attributes( $foogallery_attachment, $args );
		$image_attributes  = foogallery_build_attachment_html_image_attributes( $foogallery_attachment, $args );
		$captions          = foogallery_build_attachment_html_caption( $foogallery_attachment, $args );

		if ( array_key_exists( 'src', $image_attributes ) ) {
			$src = $image_attributes['src'];
		} else if ( array_key_exists( 'data-src-fg', $image_attributes ) ) {
			$src = $image_attributes['data-src-fg'];
		}

		if ( array_key_exists( 'srcset', $image_attributes ) ) {
			$srcset = $image_attributes['srcset'];
		} else if ( array_key_exists( 'data-srcset-fg', $image_attributes ) ) {
			$srcset = $image_attributes['data-srcset-fg'];
		}

		$json_object       = new stdClass();
		$json_object->href = $anchor_attributes['href'];
		if ( isset( $src ) ) {
			$json_object->src = $src;
		}
		if ( isset( $srcset ) ) {
			$json_object->srcset = $srcset;
		}
		if ( array_key_exists( 'width', $image_attributes ) ) {
			$json_object->width = $image_attributes['width'];
		}
		if ( array_key_exists( 'height', $image_attributes ) ) {
			$json_object->height = $image_attributes['height'];
		}

		$json_object->alt = $foogallery_attachment->alt;

		$json_object_attr_anchor                         = new stdClass();
		$json_object_attr_anchor->{'data-attachment-id'} = $foogallery_attachment->ID;

		if ( $captions !== false ) {
			if ( array_key_exists( 'title', $captions ) ) {
				$json_object->caption = $json_object->title = $json_object_attr_anchor->{'data-caption-title'} = $captions['title'];
			}
			if ( array_key_exists( 'desc', $captions ) ) {
				$json_object->description = $json_object_attr_anchor->{'data-caption-desc'} = $captions['desc'];
			}
		}

		$json_object->attr         = new stdClass();
		$json_object->attr->anchor = $json_object_attr_anchor;

		$json_object = apply_filters( 'foogallery_build_attachment_json', $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions );

		return $json_object;
	}

	return false;
}

/**
 * Build up a JSON string for a FooGallery Attachment
 *
 * @param FooGalleryAttachment $foogallery_attachment
 * @param array                $args
 *
 * @since 1.4.9
 *
 * @returns string
 * @return string
 */
function foogallery_build_json_from_attachment( $foogallery_attachment, $args = array() ) {
	if ( isset( $foogallery_attachment ) ) {

		$json_object = foogallery_build_json_object_from_attachment( $foogallery_attachment, $args );

		if ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
			return json_encode( $json_object, JSON_UNESCAPED_UNICODE );
		} else {
			return json_encode( $json_object );
		}
	}

	return '';
}

