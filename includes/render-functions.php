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

	$html = foogallery_html_opening_tag( 'img', $attr );

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
	$attr['src'] = foogallery_process_image_url( foogallery_attachment_html_image_src( $foogallery_attachment, $args ) );

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

	// Always output the loading attribute on the img tags.
	$attr['loading'] = 'eager';

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

	$html = foogallery_html_opening_tag( 'a', $attr );

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
		// get the URL to the attachment page.
		$url = get_attachment_link( $foogallery_attachment->ID );
	} elseif ( 'custom' === $link ) {
		$url = $args['custom_link'];
	} else {
		$url = $foogallery_attachment->url;
	}

	// fallback for images that might not have a custom url.
	if ( empty( $url ) ) {
		$url = $foogallery_attachment->url;
	}

	$attr = array();

	// only add href and target attributes to the anchor if the link is NOT set to 'none'.
	if ( 'none' !== $link ) {
		$attr['href'] = foogallery_process_image_url( $url );
		if ( ! empty( $foogallery_attachment->custom_target ) && 'default' !== $foogallery_attachment->custom_target ) {
			$attr['target'] = $foogallery_attachment->custom_target;
		}
	}

	if ( ! empty( $foogallery_attachment->caption ) ) {
		$attr['data-caption-title'] = foogallery_sanitize_full( $foogallery_attachment->caption );
	}

	if ( ! empty( $foogallery_attachment->description ) ) {
		$attr['data-caption-desc'] = foogallery_sanitize_full( $foogallery_attachment->description );
	}

	if ( isset( $foogallery_attachment->caption_title ) ) {
		$attr['data-caption-title'] = foogallery_sanitize_full( $foogallery_attachment->caption_title );
	}

	if ( isset( $foogallery_attachment->caption_desc ) ) {
		$attr['data-caption-desc'] = foogallery_sanitize_full( $foogallery_attachment->caption_desc );
	}

	// set the ID attribute for the attachment.
	if ( $foogallery_attachment->ID > 0 ) {
		$attribute_key          = foogallery_get_setting( 'attachment_id_attribute', 'data-attachment-id' );
		$attr[ $attribute_key ] = $foogallery_attachment->ID;
	}

	// pull any custom attributes out the args.
	if ( isset( $args['link_attributes'] ) && is_array( $args['link_attributes'] ) ) {
		$attr = array_merge( $attr, $args['link_attributes'] );
	}

	$attr = apply_filters( 'foogallery_attachment_html_link_attributes', $attr, $args, $foogallery_attachment );

	// always add the fg-thumb class.
	if ( array_key_exists( 'class', $attr ) && ! empty( $attr['class'] ) ) {
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
function foogallery_build_attachment_html_caption( &$foogallery_attachment, $args = array() ) {

	//allow for custom captions to be set
	$captions = apply_filters( 'foogallery_build_attachment_html_caption_custom', false, $foogallery_attachment, $args);

	if ( false === $captions ) {

        $captions = array();

        $preset = foogallery_gallery_template_setting( 'caption_preset', 'fg-custom' );

        if ( 'none' !== $preset ) {

            $show_caption_title = false;
            $show_caption_desc = false;

            $caption_title_source = foogallery_gallery_template_setting( 'caption_title_source', '' );

            //if we need to use the settings, then make sure our source is false
            if ( empty( $caption_title_source ) ) {
                $caption_title_source = false;
            }

            if ( 'fg-custom' === $preset ) {
                $show_caption_title = $caption_title_source !== 'none';
            } else {
                //always show both title and desc for the presets
                $show_caption_title = true;
            }

            //get the correct captions
            if ( $foogallery_attachment->_post ) {
                $caption_title = foogallery_get_caption_title_for_attachment($foogallery_attachment->_post, $caption_title_source);
            } else {
                $caption_title = foogallery_get_caption_by_source($foogallery_attachment, $caption_title_source, 'title');
            }

            $caption_desc_source = foogallery_gallery_template_setting('caption_desc_source', '');

            //if we need to use the settings, then make sure our source is false
            if ( empty( $caption_desc_source ) ) {
                $caption_desc_source = false;
            }

            if ( 'fg-custom' === $preset ) {
                $show_caption_desc = $caption_desc_source !== 'none';
            } else {
                //always show both title and desc for the presets
                $show_caption_desc = true;
            }

            if ( $foogallery_attachment->_post ) {
                $caption_desc = foogallery_get_caption_desc_for_attachment($foogallery_attachment->_post, $caption_desc_source);
            } else {
	            $caption_desc = foogallery_get_caption_by_source( $foogallery_attachment, $caption_desc_source, 'desc' );
            }

            if ( $caption_title && $show_caption_title ) {
                $captions['title'] = $foogallery_attachment->caption_title = $caption_title;
            }
            if ( $caption_desc && $show_caption_desc ) {
                $captions['desc'] = $foogallery_attachment->caption_desc = $caption_desc;
            }

        } else {
            $captions = false;
        }
    }

	//extra sanitization for HTML captions
	if ( isset( $args['override_caption_title'] ) ) {
		$captions['override_title'] = foogallery_sanitize_full( $args['override_caption_title'] );
	}
	if ( isset( $args['override_caption_desc']) ) {
		$captions['override_desc'] = foogallery_sanitize_full( $args['override_caption_desc'] );
	}

	//extra sanitization for HTML captions
	if ( !empty( $captions['title']) ) {
		$captions['title'] = foogallery_sanitize_full( $captions['title'] );
	}
	if ( !empty( $captions['desc']) ) {
		$captions['desc'] = foogallery_sanitize_full( $captions['desc'] );
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

		$caption_title = null;
		$caption_desc = null;

		$html = '<figcaption class="fg-caption"><div class="fg-caption-inner">';

		if ( array_key_exists( 'override_title', $captions ) ) {
			$caption_title = $captions['override_title'];
		} else if ( array_key_exists( 'title', $captions ) ) {
			$caption_title = $captions['title'];
		}
		if ( array_key_exists( 'override_desc', $captions ) ) {
			$caption_desc = $captions['override_desc'];
		} else if ( array_key_exists( 'desc', $captions ) ) {
			$caption_desc = $captions['desc'];
		}

		if ( !empty( $caption_title ) ) {
			$html .= '<div class="fg-caption-title">' . $caption_title . '</div>';
		}
		if ( !empty( $caption_desc ) ) {
			$html .= '<div class="fg-caption-desc">' . $caption_desc . '</div>';
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

	//set the type of an item
	$classes['type'] = 'fg-type-' . $foogallery_attachment->type;

	//let others add to the item classes
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

    //allow width and height arguments to be overridden
    $override_width = foogallery_gallery_template_setting( 'override_width', false );
    if ( $override_width !== false && intval( $override_width ) > 0 ) {
        $args['width'] = $override_width;
    }
    $override_height = foogallery_gallery_template_setting( 'override_height', false );
    if ( $override_height !== false && intval( $override_height ) > 0 ) {
        $args['height'] = $override_height;
    }

    $caption = foogallery_attachment_html_caption( $foogallery_attachment, $args );

    $html = foogallery_attachment_html_item_opening( $foogallery_attachment, $args );
    $html .= foogallery_attachment_html_anchor_opening( $foogallery_attachment, $args );
    $html .= '<span class="fg-image-wrap">';
    $html .= foogallery_attachment_html_image( $foogallery_attachment, $args );
	$html .= '</span>';
	$html .= '<span class="fg-image-overlay"></span>';
    $html .= '</a>';
    $html .= $caption;
    $html .= '</figure><div class="fg-loader"></div></div>';
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

		$captions          = foogallery_build_attachment_html_caption( $foogallery_attachment, $args );
		$anchor_attributes = foogallery_build_attachment_html_anchor_attributes( $foogallery_attachment, $args );
		$image_attributes  = foogallery_build_attachment_html_image_attributes( $foogallery_attachment, $args );

		if ( array_key_exists( 'data-src-fg', $image_attributes ) ) {
			$src = $image_attributes['data-src-fg'];
		} else if (array_key_exists( 'src', $image_attributes ) ) {
			$src = $image_attributes['src'];
		}

		if ( array_key_exists( 'data-srcset-fg', $image_attributes ) ) {
			$srcset = $image_attributes['data-srcset-fg'];
		} else if ( array_key_exists( 'srcset', $image_attributes ) ) {
			$srcset = $image_attributes['srcset'];
		}

		$json_object       = new stdClass();

		if ( array_key_exists( 'href', $anchor_attributes ) ) {
			$json_object->href = $anchor_attributes['href'];
		}
		if ( array_key_exists( 'data-type', $anchor_attributes ) ) {
			$json_object->type = $anchor_attributes['data-type'];
		}
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

		if ( $foogallery_attachment->ID > 0 ) {
			$json_object->id = $foogallery_attachment->ID;
		}

		if ( $captions !== false ) {
			if ( array_key_exists( 'title', $captions ) ) {
				$json_object->caption = $json_object->title = $captions['title'];
			}
			if ( array_key_exists( 'desc', $captions ) ) {
				$json_object->description = $captions['desc'];
			}
		}

		$json_object->attr         = new stdClass();
		$json_object->attr->anchor = foogallery_create_anchor_for_json_object( $anchor_attributes );

		$json_object = apply_filters( 'foogallery_build_attachment_json', $json_object, $foogallery_attachment, $args, $anchor_attributes, $image_attributes, $captions );

		return $json_object;
	}

	return false;
}

/**
 * Build up the anchor object that is used within the json object
 *
 * @param $anchor_attributes
 *
 * @return stdClass
 */
function foogallery_create_anchor_for_json_object( $anchor_attributes ) {
	//unset a number of keys in the array that are already set on the high-level item
	unset( $anchor_attributes['href'] );
	unset( $anchor_attributes['data-type'] );

	//create the anchor object
	$object = new stdClass();

	//loop through the anchor attributes and set them on the object
	foreach ( $anchor_attributes as $key => $value ) {
		$object->{$key} = $value;
	}
	return $object;
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

		return foogallery_json_encode( $json_object );
	}

	return '';
}

/**
 * Renders a script block with the JSON output for the attachments in a gallery
 *
 * @param $gallery FooGallery
 * @param $attachments FooGalleryAttachment[]
 */
function foogallery_render_script_block_for_json_items( $gallery, $attachments ) {
	if ( count( $attachments ) > 0 ) {
		$attachments_json = array_map( 'foogallery_build_json_from_attachment', $attachments );
		echo '<script type="text/javascript">';
		echo '  window["' . $gallery->container_id() . '_items"] = [';
		echo implode( ', ', $attachments_json );
		echo '  ];';
		echo '</script>';
	}
}

/**
 * Generates the HTML for a tag
 *
 * @param $tag
 * @param $attributes
 *
 * @return string
 */
function foogallery_html_opening_tag( $tag, $attributes ) {
	$html = '<' . $tag;
	foreach ( $attributes as $name => $value ) {
		if ( empty( $name ) || empty( $value ) ) continue;
		$name = str_replace(' ', '', $name); //ensure we have no spaces!
		$html .= " $name=" . '"' . foogallery_esc_attr($value) . '"';
	}
	$html .= '>';
	return $html;
}