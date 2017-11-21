<?php
/**
 * Class to calculate thumb dimensions for a gallery. The default gallery templates
 *  require width and height attributes on the thumb img tags. In some cases these need to be
 *  calculated based on the aspect ratio of the individual thumbs.
 *
 * Date: 21/03/2017
 */


if ( ! class_exists( 'FooGallery_Thumbnail_Dimensions' ) ) {

    class FooGallery_Thumbnail_Dimensions
    {
        function __construct()
        {
            //hook into the filter that build up the img attributes
            // and add the width and height attributes if the gallery requires them
            add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'include_thumb_dimension_attributes' ), 10, 3 );

            //calculate the thumbnail dimensions for all attachments in the gallery
            // for the specific gallery template that is being used.
            add_action( 'foogallery_located_template', array( $this, 'calculate_all_thumbnail_dimensions' ) );
        }

        /**
         * Helper function to check if the gallery requires thumbnail dimensions
         * @param $gallery_template string
         * @return bool
         */
        function does_gallery_template_use_thumbnail_dimensions( $gallery_template ) {
            if ( empty( $gallery_template ) ) return false;

            //first do a check if the template needs thumbnail dimensions calculated
            $template_data = foogallery_get_gallery_template( $gallery_template );

            if ( $template_data && array_key_exists( 'thumbnail_dimensions', $template_data ) && true === $template_data['thumbnail_dimensions'] ) {
                //this template requires thumb dimensions to be provided
                return true;
            }

            return false;
        }

        function empty_dimensions( $thumbnail_dimensions ) {
            if ( isset( $thumbnail_dimensions ) && is_array( $thumbnail_dimensions ) ) {
                $thumb_width = (int)$thumbnail_dimensions['width'];
                $thumb_height = (int)$thumbnail_dimensions['height'];

                return $thumb_width === 0 && $thumb_height === 0;
            }
            return true;
        }

        /**
         * Calculates all the thumbnail dimensions for the gallery
         * @param $foogallery FooGallery
         */
        function calculate_all_thumbnail_dimensions( $foogallery ) {
            global $current_foogallery;
            global $current_foogallery_template;
            global $current_foogallery_arguments;

            //check if we are dealing with a gallery. This check ensures this is not done for albums
            if ( isset( $current_foogallery ) && isset( $current_foogallery_template ) ) {

                //first do a check if the template needs thumbnail dimensions calculated
                if ($this->does_gallery_template_use_thumbnail_dimensions( $current_foogallery_template ) ) {

                    //load the thumbnail dimensions specific to the gallery, taking preference to arguments
                    $thumbnail_dimensions = apply_filters( 'foogallery_calculate_thumbnail_dimensions-' . $current_foogallery_template, array(), $current_foogallery_arguments );

                    //if we have no dimensions then load them from the gallery settings
                    if ( $this->empty_dimensions( $thumbnail_dimensions ) ) {
                        $thumbnail_dimensions = apply_filters( 'foogallery_template_thumbnail_dimensions-' . $current_foogallery_template, $thumbnail_dimensions, $current_foogallery );
                    }

                    if ( isset( $thumbnail_dimensions ) && is_array( $thumbnail_dimensions ) ) {

                        //$thumbnail_dimensions
                        $thumb_width = (int)$thumbnail_dimensions['width'];
                        $thumb_height = (int)$thumbnail_dimensions['height'];
                        $thumb_crop = (bool)$thumbnail_dimensions['crop'];

                        //set the appropriate arguments on the attachments so that they can be
                        // picked up and used in the 'include_thumb_dimension_attributes' function below
                        foreach ($foogallery->attachments() as $attachment) {
                            if ( $thumb_crop && $thumb_width > 0 && $thumb_height > 0 ) {
                                //we have set width and height and crop = true
                                //we do not need to calculate the dimensions
                                $calculated_thumb_width = $thumb_width;
                                $calculated_thumb_height = $thumb_height;
                            } else {
                                $size_array = image_resize_dimensions($attachment->width, $attachment->height, $thumb_width, $thumb_height, $thumb_crop);
                                $calculated_thumb_width = $size_array[4];
                                $calculated_thumb_height = $size_array[5];
                            }

                            $attachment->has_thumbnail_dimensions = true;
                            $attachment->thumb_width = $calculated_thumb_width;
                            $attachment->thumb_height = $calculated_thumb_height;
                        }
                    }
                }
            }
        }

        /**
         * Include the thumb dimension html attributes in the rendered HTML
         *
         * @param $attr
         * @param $args
         * @param $foogallery_attachment
         *
         * @return array
         */
        function include_thumb_dimension_attributes( $attr, $args, $foogallery_attachment ) {
            global $current_foogallery;

            //check if we are dealing with a gallery. This check ensures this is not done for albums
            if ( isset( $current_foogallery ) ) {

                //check if we have anything set
                if ( isset( $foogallery_attachment->has_thumbnail_dimensions ) ) {
                    if ( $foogallery_attachment->thumb_width > 0 ) {
                        $attr['width'] = $foogallery_attachment->thumb_width;
                    }
                    if ( $foogallery_attachment->thumb_height > 0 ) {
                        $attr['height'] = $foogallery_attachment->thumb_height;
                    }
                }
            }

            return $attr;
        }

    }
}