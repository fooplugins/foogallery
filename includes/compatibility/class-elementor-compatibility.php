<?php
/**
 * Elementor Compatibility Class
 * Date: 23/09/2019
 */
if ( ! class_exists( 'FooGallery_Elementor_Compatibility' ) ) {

    class FooGallery_Elementor_Compatibility {
        function __construct() {
            add_action( 'elementor/editor/after_save', array( $this, 'save_elementor_data' ), 10, 2 );
            add_action( 'plugins_loaded', array( $this, 'init' ) );
        }

        function init() {
            if ( did_action( 'elementor/loaded' ) ) {
                add_action( 'elementor/widgets/widgets_registered', array( $this, 'init_widget' ) );
                add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_assets') );
            }
        }

        public function init_widget() {

            // Include Widget files
            require_once( FOOGALLERY_PATH . 'includes/compatibility/elementor/class-elementor-foogallery-widget.php' );

            // Register widget
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Elementor_FooGallery_Widget() );

        }

        public function enqueue_assets() {
            foogallery_enqueue_core_gallery_template_script();
            foogallery_enqueue_core_gallery_template_style();
        }

        function save_elementor_data( $post_id, $editor_data) {
            //loop through the $editor_data and find any FooGallery widgets or shortcodes

            $gallery_ids = $this->find_galleries_recursive( $editor_data );

            if ( is_array( $gallery_ids ) && count( $gallery_ids ) > 0 ) {

                foreach ( $gallery_ids as $gallery_id) {
                    //if the content contains the foogallery shortcode then add a custom field
                    add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE, $gallery_id, false );
                    do_action( 'foogallery_attach_gallery_to_post', $post_id, $gallery_id );
                }
            }
        }

        function find_galleries_recursive( $array ) {
            $found = array();
            if ( is_array( $array ) ) {
                foreach ( $array as $element ) {
                    if (array_key_exists('widgetType', $element) && $element['widgetType'] === 'shortcode') {

                        $shortcode = $element['settings']['shortcode'];

                        $gallery_ids = foogallery_extract_gallery_shortcodes($shortcode);

                        if (count($gallery_ids) > 0) {
                            $found = array_merge($found, array_keys($gallery_ids));
                        }
                    } else if ( array_key_exists( 'widgetType', $element) && $element['widgetType'] === 'foogallery' ) {

                        $found[] = intval( $element['settings']['gallery_id'] );

                    } else if ( array_key_exists( 'elements', $element ) && count( $element['elements'] ) > 0 ) {
                        $found = array_merge($found, $this->find_galleries_recursive( $element['elements'] ) );
                    }
                }
            }
            return $found;
        }
    }
}