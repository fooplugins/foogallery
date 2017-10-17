<?php
/*
 * FooGallery Retina Support class
 */

if ( !class_exists( 'FooGallery_Retina' ) ) {

    class FooGallery_Retina {

        function __construct() {
            add_filter('foogallery_attachment_html_image_attributes', array($this, 'add_retina_attributes'), 10, 3);
        }

        /**
         * @param array $attr
         * @param array $args
         * @param FooGalleryAttachment $attachment
         * @return mixed
         */
        function add_retina_attributes($attr, $args, $attachment) {
            global $current_foogallery;

            if ( $current_foogallery && $current_foogallery->gallery_template ) {

                //first check if the gallery has saved Retina settings
                if ( isset($current_foogallery->retina) && is_array( $current_foogallery->retina ) ) {
                    $srcset = array();

					//get the original thumb dimensions
					$original_thumb_width = array_key_exists( 'width', $args ) ? intval( $args['width'] ) : 0;
					$original_thumb_height = array_key_exists( 'height', $args ) ? intval( $args['height'] ) : 0;

					//get the original full size image dimensions
					$original_width = $attachment->width;
					$original_height = $attachment->height;

					//if we do not have a width, we need to calculate one
					if ( 0 === $original_thumb_width ) {
						//find closest ratio multiple to image size
						if( $original_width > $original_height ) {
							//landscape
							$ratio = $original_width / $original_height;
							$original_thumb_width = intval( $original_thumb_height * $ratio );
						}else{
							//portrait
							$ratio = $original_height / $original_width;
							$original_thumb_width = intval( $original_thumb_height / $ratio );
						}
					}

                    foreach ( foogallery_retina_options() as $pixel_density ) {
                        $pixel_density_supported = array_key_exists( $pixel_density, $current_foogallery->retina ) ? ('true' === $current_foogallery->retina[$pixel_density]) : false;

                        if ( $pixel_density_supported ) {
                            $pixel_density_int = intval( str_replace( 'x', '', $pixel_density ) );

                            //apply scaling to the width and height attributes
                            $retina_width  = $original_thumb_width * $pixel_density_int;
                            $retina_height = $original_thumb_height * $pixel_density_int;

                            //if the new dimensions are smaller than the full size image dimensions then allow the retina thumb
                            if ( $retina_width < $original_width &&
                                $retina_height < $original_height ) {
                                $args['width'] = $retina_width;
                                $args['height'] = $retina_height;

                                //build up the retina attributes
                                $srcset[] = $attachment->html_img_src( $args ) . ' ' . $retina_width . 'w';
                            }
                        }
                    }

                    if ( count( $srcset ) ) {
                        $attr['srcset'] = implode( ',', $srcset );
                    }
                }
            }

            return $attr;
        }
    }
}
