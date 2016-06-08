<?php
/**
 * Simple class to override settings necessary to work with WordPress thumbnails instead of generated ones
 */
if ( !class_exists( 'JustifiedOverrides' ) ) {

	class JustifiedOverrides {

		function __construct() {
			//override thumbnail resizing
			add_filter( 'foogallery_attachment_resize_thumbnail', array( $this, 'get_thumb' ), 99, 3 );
			//add additional parameters to image args
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'fill_sizes' ), 99, 3 );
		}

        function get_thumb($original_image_src, $args, $image) {
			//of course the height is merely the label of the chosen thumbnail            
            $thumb_url = $image->sizes[$args['height']]['url'];            
            return $thumb_url;
        }
        
        function fill_sizes($attr, $args, $image) {
            $thumbnail_label = foogallery_gallery_template_setting("thumb_initial");

            //get selected alternate image sizes from settings
            $image_sizes = foogallery_gallery_template_setting("thumb_sizes");
			
			//create width/height array and string
			if (is_array($image_sizes)) {
				foreach ($image_sizes as $image_size) {
					$size_str[] = '{"width" : "' . $image->sizes[$image_size]['width'] . '", "height" : "' . $image->sizes[$image_size]['height'] . '"}';
				}
			
				if (isset($size_str) && !is_null($size_str)) {
					$img_sizes = "[" . implode(',', $size_str) . "]";
					$attr['data-sizes'] = $img_sizes;
				}
			
			}            
            
            return $attr;
        }
	}
}
