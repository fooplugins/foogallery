<?php

if ( !class_exists( 'FooGallery_Justified_Gallery_Template' ) ) {

	define('FOOGALLERY_JUSTIFIED_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));

	class FooGallery_Justified_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ) );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 * @param $extensions
		 *
		 * @return array
		 */
		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		/**
		 * Add our gallery template to the list of templates available for every gallery
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {
			$gallery_templates[] = array(
					'slug'        => 'justified',
					'name'        => __( 'Justified Gallery', 'foogallery' ),
					'fields'	  => array(
							array(
									'id'	  => 'help',
									'title'	  => __( 'Tip', 'foogallery' ),
									'type'	  => 'html',
									'help'	  => true,
									'desc'	  => __( 'The Justified Gallery template uses the popular <a href="http://miromannino.com/projects/justified-gallery/" target="_blank">Justified Gallery jQuery Plugin</a> under the hood. You can specify thumbnail captions by setting the alt text for your attachments. Use a small thumb height for lightning fast page loading, larger thumbnails will be loaded afterwards depening on chosen alternates.<br><br>Make sure to have thumbnails in your theme with a width of 9999 to be able to choose an Initial thumb.', 'foogallery' ),
							),
							array(
									'id'      => 'thumb_initial',
									'title'   => __( 'Initial Thumb', 'foogallery' ),
									'desc'    => __( 'Choose initial thumbnails to be loaded. Should be roughly matching your row height or smaller. Needs to have a width of 9999.', 'foogallery' ),
									'type'    => 'select',
									'choices' => $this->get_thumb_size_choices_filtered()
							),
							array(
									'id'		=> 'thumb_sizes',
									'title'		=> __( 'Alternate thumb sizes', 'foogallery' ),
									'desc'		=> __( 'Alternate thumbnail sizes that will be loaded after loading the initial thumbnail. Will be used if larger thumbnails could be used in your gallery. Will only display thumbnails with a width of 9999', 'foogallery' ),
									'type'		=> 'checkboxlist',
									'choices' => $this->get_thumb_size_choices_filtered()
							),
							array(
									'id'      => 'row_height',
									'title'   => __( 'Row Height', 'foogallery' ),
									'desc'    => __( 'The preferred height of your gallery rows. This can be different from the thumbnail height, but should roughly match it.', 'foogallery' ),
									'type'    => 'number',
									'class'   => 'small-text',
									'default' => 150,
									'step'    => '10',
									'min'     => '0',
							),
							array(
									'id'      => 'fixed_height',
									'title'   => __( 'Fixed Row height', 'foogallery' ),
									'desc'    => __( 'Fix rows at the specified row height ("Max Row Height" is not used). Depending on aspect ratio the images could be cropped.', 'foogallery' ),
									'type'    => 'checkbox',
									'default' => 'off',
							),
							array(
									'id'      => 'max_row_height',
									'title'   => __( 'Max Row Height', 'foogallery' ),
									'desc'    => __( 'A number (e.g 200) which specifies the maximum row height in pixels. A negative value for no limits. Alternatively, use a percentage (e.g. 200% which means that the row height cannot exceed 2 * rowHeight)', 'foogallery' ),
									'type'    => 'text',
									'class'   => 'small-text',
									'default' => '200%'
							),
							array(
									'id'      => 'last_row',
									'title'   => __( 'Last Row', 'foogallery' ),
									'desc'    => __( 'Sets display options for the last row if not enough images exist to fill the row.', 'foogallery' ),
									'type'    => 'radio',
									'default' => 'justify',
									'spacer'  => '<span class="spacer"></span>',
									'choices' => array(
											'justify'  => __( 'Justify', 'foogallery' ),
											'nojustify'   => __( 'No Justify', 'foogallery' ),
											'hide'   => __( 'Hide', 'foogallery' )
									)
							),
							array(
									'id'      => 'margins',
									'title'   => __( 'Margins', 'foogallery' ),
									'desc'    => __( 'The spacing between your thumbnails.', 'foogallery' ),
									'type'    => 'number',
									'class'   => 'small-text',
									'default' => 1,
									'step'    => '1',
									'min'     => '0',
							),
							array(
									'id'      => 'captions',
									'title'   => __( 'Show Captions', 'foogallery' ),
									'desc'    => __( 'Show a caption when hovering over your thumbnails. (Set captions by adding either a title or alt text to an attachment)', 'foogallery' ),
									'type'    => 'checkbox',
									'default' => 'on',
							),
							array(
									'id'      => 'caption_source',
									'title'   => __( 'Caption Source', 'foogallery' ),
									'desc'    => __( 'Pull captions from either the attachment Title, Caption or Alt Text.', 'foogallery' ),
									'type'    => 'radio',
									'default' => 'title',
									'spacer'  => '<span class="spacer"></span>',
									'choices' => array(
											'title'  => __( 'Attachment Title', 'foogallery' ),
											'caption'   => __( 'Attachment Caption', 'foogallery' ),
											'alt'   => __( 'Attachment Alt Text', 'foogallery' )
									)
							),
							array(
									'id'      => 'thumbnail_link',
									'title'   => __( 'Thumbnail Link', 'foogallery' ),
									'default' => 'image' ,
									'type'    => 'thumb_link',
									'spacer'  => '<span class="spacer"></span>',
									'desc'	  => __( 'You can choose to link each thumbnail to the full size image, or to the image\'s attachment page, or you can choose to not link to anything.', 'foogallery' ),
							),
							array(
									'id'      => 'lightbox',
									'title'   => __( 'Lightbox', 'foogallery' ),
									'desc'    => __( 'Choose which lightbox you want to display images with. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
									'type'    => 'lightbox',
							),
					),
			);

			return $gallery_templates;
		}
		
		function get_thumb_size_choices_filtered() {
			global $_wp_additional_image_sizes;
			$sizes = array();
			foreach( get_intermediate_image_sizes() as $s ){
				if ( in_array( $s, array( 'thumbnail', 'medium', 'large', ) ) ){
					$sizes[ $s ]['width'] = get_option( $s . '_size_w' );
					$sizes[ $s ]['height'] = get_option( $s . '_size_h' );
					$sizes[ $s ]['crop'] = (bool) get_option( $s . '_crop' );
				} elseif ( isset( $_wp_additional_image_sizes[ $s ] ) ) {
						$sizes[ $s ] = array(
										'width' => $_wp_additional_image_sizes[ $s ]['width'],
										'height' => $_wp_additional_image_sizes[ $s ]['height'],
										'crop' => (bool) $_wp_additional_image_sizes[ $s ]['crop'],
										);
				}
			}
			
			//order by height
			if (!function_exists('sortbyheight')) {
				function sortbyheight($a, $b) {
					if ($a['height'] == $b['height']) {
						return 0;
					}
					return ($a['height'] < $b['height']) ? -1 : 1;
				}
			}
			uasort($sizes, "sortbyheight");
			
			$sizesfiltered = array();
			
			//filter sizes to only include non-cropped and width 9999
			foreach ($sizes as $key => $value) {
				if ( !$value['crop'] == 1 && $value['width'] == '9999') {
					$sizesfiltered[$key] = ucwords($key) . " (" . $value['width'] . " x " . $value['height'] . ") ";
				}
			}
			
			return $sizesfiltered;
		}
	}
}