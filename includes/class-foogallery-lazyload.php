<?php
/**
 * Class used to handle lazy loading for gallery templates
 * Date: 20/03/2017
 */
if ( ! class_exists( 'FooGallery_LazyLoad' ) ) {

	class FooGallery_LazyLoad
    {

        function __construct()
        {
            //change the image src attribute to data attributes if lazy loading is enabled
            add_filter('foogallery_attachment_html_image_attributes', array($this, 'change_src_attributes'), 99, 3);

            //add the lazy load attributes to the gallery container
            add_filter('foogallery_build_container_data_options', array($this, 'add_lazyload_options'), 10, 3);

            //add common fields to the templates that support it
            add_filter('foogallery_override_gallery_template_fields', array($this, 'add_lazyload_field'), 100, 2);

            //build up preview arguments
            add_filter( 'foogallery_preview_arguments', array( $this, 'preview_arguments' ), 10, 3 );
        }

        /**
         * Determine if the gallery has lazy loading support
         *
         * @param $foogallery
         * @param $foogallery_template
         */
        function determine_lazyloading_support($foogallery, $foogallery_template)
        {
            //make sure we only do this once for better performance
            if (!isset($foogallery->lazyload)) {

                //load the gallery template
                $template_info = foogallery_get_gallery_template($foogallery_template);

                //check if the template supports lazy loading
                $lazy_load = isset($template_info['lazyload_support']) &&
                    true === $template_info['lazyload_support'];

                $foogallery->lazyload = apply_filters('foogallery_lazy_load', $lazy_load, $foogallery, $foogallery_template);
            }
        }

        /**
         * @param array $attr
         * @param array $args
         * @param FooGalleryAttachment $attachment
         * @return mixed
         */
        function change_src_attributes($attr, $args, $attachment)
        {
            global $current_foogallery;
            global $current_foogallery_template;

            if ($current_foogallery !== null) {

                $this->determine_lazyloading_support($current_foogallery, $current_foogallery_template);

                if (isset($current_foogallery->lazyload) && true === $current_foogallery->lazyload) {

                    if (isset($attr['src'])) {
                        //rename src => data-src
                        $src = $attr['src'];
                        unset($attr['src']);
                        $attr['data-src'] = $src;
                    }

                    if (isset($attr['srcset'])) {
                        //rename srcset => data-srcset
                        $src = $attr['srcset'];
                        unset($attr['srcset']);
                        $attr['data-srcset'] = $src;
                    }
                }
            }

            return $attr;
        }


        /**
         * Add the required lazy load options if needed
         *
         * @param $attributes array
         * @param $gallery FooGallery
         *
         * @return array
         */
        function add_lazyload_options($options, $gallery, $attributes)
        {
            global $current_foogallery_template;

            $this->determine_lazyloading_support($gallery, $current_foogallery_template);

            if (isset($gallery->lazyload) && true === $gallery->lazyload) {
                $lazyloading_enabled = foogallery_gallery_template_setting( 'lazyload', '' ) === '';

                $options['lazy'] = $lazyloading_enabled;
            }
            return $options;
        }

        /**
         * Add lazyload field to the gallery template if supported
         *
         * @param $fields
         * @param $template
         *
         * @return array
         */
        function add_lazyload_field($fields, $template)
        {
            //check if the template supports lazy loading
            if ($template && array_key_exists('lazyload_support', $template) && true === $template['lazyload_support']) {

                $fields[] = array(
                    'id'      => 'lazyload',
                    'title'   => __( 'Lazy Loading', 'foogallery' ),
                    'desc'    => __( 'If you choose to disable lazy loading, then all thumbnails will be loaded at once. This means you will lose the performance improvements that lazy loading gives you.', 'foogallery' ),
                    'section' => __( 'Advanced', 'foogallery' ),
                    'type'     => 'radio',
                    'spacer'   => '<span class="spacer"></span>',
                    'default'  => '',
                    'choices'  => array(
                        '' => __( 'Enable Lazy Loading', 'foogallery' ),
                        'disabled' => __( 'Disable Lazy Loading', 'foogallery' ),
                    ),
                    'row_data' => array(
                        'data-foogallery-change-selector' => 'input:radio',
                        'data-foogallery-preview' => 'shortcode'
                    )
                );
            }

            return $fields;
        }

        /**
         * Build up a arguments used in the preview of the gallery
         * @param $args
         * @param $post_data
         * @param $template
         *
         * @return mixed
         */
        function preview_arguments( $args, $post_data, $template ) {
            if ( isset( $post_data[FOOGALLERY_META_SETTINGS][$template . '_lazyload'] ) ) {
                $args['lazyload'] = $post_data[FOOGALLERY_META_SETTINGS][$template . '_lazyload'];
            }
            return $args;
        }
    }
}