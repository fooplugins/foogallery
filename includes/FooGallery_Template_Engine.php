<?php

/**
 *
 * FooGallery_Template_Engine - The template engine that is used to render the FooGallery gallery
 *
 */

if (!class_exists('FooGallery_Template_Engine')) {
    class FooGallery_Template_Engine {
        protected $_args;
        protected $_template;
        protected $_gallery = false;

        function __construct($args) {
            $this->_args = $args;
            $this->_template = $this->get_arg('template', 'default');
        }

        /**
         * Gets an argument passed into the Template
         *
         * @param $key string
         * @param $default string
         * @return string
         */
        function get_arg($key, $default = '') {
            if (empty($this->_args) || !array_key_exists($key, $this->_args)) {
                return $default;
            }

            return $this->_args[$key];
        }

        /**
         * Lazy load the gallery based on either the id or slug
         *
         * @return bool|FooGallery_Gallery
         */
        function get_gallery() {
            //if we have loaded the gallery object then return it quickly
            if ( $this->_gallery !== false) return $this->_gallery;

            $id = intval( $this->get_arg('id'), 0 );
            if ($id > 0) {
                //load gallery by ID
                $this->_gallery = FooGallery_Gallery::get_by_id( $id );
            } else {
                //take into account the cases where id is passed in via gallery attribute
                $gallery = $this->get_arg('gallery', 0);
                if (intval($gallery) > 0) {
                    //we have an id, so load
                    $this->_gallery = FooGallery_Gallery::get_by_id( intval( $gallery ) );
                }
                //we are dealing with a gallery slug
                $this->_gallery = FooGallery_Gallery::get_by_slug( $gallery );
            }

            return $this->_gallery;
        }

        /**
         * Returns true if we have a valid gallery
         *
         * @return bool
         */
        function is_valid_gallery() {
            $gallery = $this->get_gallery();
            return (isset($gallery) && $gallery !== false && $gallery->ID > 0);
        }

        /**
         * Renders the gallery based on the template name passed in by arguments
         */
        function render() {

            //check if we have a valid gallery
            if (!$this->is_valid_gallery()) return;

            $gallery = $this->get_gallery();

            //check if we have any attachments
            if (!$gallery->has_attachments()) {
                //no attachments!
                do_action("foogallery_template_no_attachments-{$this->_template}", $gallery);
            }

            //load any JS & CSS needed by the gallery
            do_action("foogallery_template_js-{$this->_template}", $gallery);
            do_action("foogallery_template_css-{$this->_template}", $gallery);

            //render gallery start
            $this->render_gallery_start();

            //render each attachment
            foreach ($gallery->attachments() as $attachment_id) {
                $this->render_gallery_attachment($attachment_id);
            }

            //render gallery end
            $this->render_gallery_end();
        }

        /**
         * Generates the CSS class for the gallery
         *
         * @return string
         */
        function generate_gallery_class() {
            return apply_filters(
                "foogallery_template_class-{$this->_template}",
                "foogallery foogallery-{$this->_gallery->ID} foogallery-template-{$this->_template}",
                $this->_gallery, $this->_template, $this->_args
            );
        }

        function render_gallery_start() {
            $class = $this->generate_gallery_class();

            $html = apply_filters(
                "foogallery_template_html_start-{$this->_template}",
                "<div class=\"{$class}\">",
                $this->_gallery, $this->_template, $this->_args
            );

            if ($html !== false)
                echo $html;
        }

        function render_gallery_end() {
            $html = apply_filters(
                "foogallery_template_html_end-{$this->_template}",
                "</div>",
                $this->_gallery, $this->_template, $this->_args
            );

            if ($html !== false)
                echo $html;
        }

		/**
		 * @param $attachment_id
		 */
        function render_gallery_attachment($attachment_id) {
            $size = $this->get_arg('size', 'thumbnail');
            $link_to_image = $this->get_arg('link_to_image', $this->_gallery->link_to_image);

            $html = apply_filters(
                "foogallery_template_html_attachment-{$this->_template}",
                wp_get_attachment_link($attachment_id, $size, !$link_to_image) . "\n",
                $attachment_id, $this->_gallery, $this->_template, $this->_args
            );

            if ($html !== false)
                echo $html;
        }
    }
}