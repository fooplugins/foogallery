<?php
/**
 * FooGallery Bricks Compatibility
 * 
 * @package FooGallery
 * @author FooPlugins
 */

if ( ! class_exists( 'FooGallery_Bricks_Compatibility' ) ) {

    /**
     * Class FooGallery_Bricks_Compatibility
     */
    class FooGallery_Bricks_Compatibility {

        public function __construct() {
            add_action( 'init', [ $this, 'init' ], 11 );
            
            // Enqueue assets conditionally based on builder mode
            add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_assets' ] );
            
            // Disable lazy loading for FooGallery when used in Bricks builder
            add_filter( 'foogallery_lazy_load', [ $this, 'disable_lazy_loading_for_bricks_builder' ], 10, 3 );
        }

        public function init() {
            // Only load if Bricks is active.
            if ( ! class_exists( '\Bricks\Elements' ) ) {
                return;
            }

            require_once FOOGALLERY_PATH . 'includes/compatibility/bricks/class-foogallery-bricks-element.php';

            // Register element file with Bricks
            \Bricks\Elements::register_element(
                FOOGALLERY_PATH . 'includes/compatibility/bricks/class-foogallery-bricks-element.php',
                'foogallery',
                'FooGallery_Bricks_Element'
            );

            // Add FooPlugins category
            add_filter( 'bricks/builder/i18n', [ $this, 'add_fooplugins_category' ] );
        }

        /**
         * Add FooPlugins category to Bricks builder
         */
        public function add_fooplugins_category( $i18n ) {
            $i18n['fooplugins'] = esc_html__( 'FooPlugins', 'foogallery' );
            return $i18n;
        }

        /**
         * Check if we should enqueue assets (only in Bricks builder main)
         */
        public function maybe_enqueue_assets() {
            // Only enqueue in Bricks builder main (sidebar/controls), not in iframe
            if ( ! function_exists( 'bricks_is_builder_main' ) || ! bricks_is_builder_main() ) {
                return;
            }
            
            $this->enqueue_assets();
        }

        /**
         * Enqueue assets for Bricks builder
         */
        private function enqueue_assets() {
            // Enqueue FooGallery core assets
            if ( function_exists( 'foogallery_enqueue_core_gallery_template_script' ) ) {
                foogallery_enqueue_core_gallery_template_script();
            }
            if ( function_exists( 'foogallery_enqueue_core_gallery_template_style' ) ) {
                foogallery_enqueue_core_gallery_template_style();
            }

            // Enqueue Bricks-specific CSS
            wp_enqueue_style( 
                'foogallery-bricks', 
                FOOGALLERY_URL . 'css/foogallery-bricks.css', 
                array( 'foogallery-core' ), 
                FOOGALLERY_VERSION 
            );

            // Enqueue Bricks-specific JavaScript
            wp_enqueue_script( 
                'foogallery-bricks', 
                FOOGALLERY_URL . 'js/admin-foogallery-bricks.js', 
                array( 'jquery' ), 
                FOOGALLERY_VERSION 
            );
            
            // Pass admin URLs to JS
            wp_localize_script( 'foogallery-bricks', 'FooGalleryBricks', [
                'editUrlBase' => admin_url( 'post.php?action=edit&post=' ),
                'newUrl'      => function_exists( 'foogallery_admin_add_gallery_url' ) ? foogallery_admin_add_gallery_url() : admin_url( 'post-new.php?post_type=foogallery' ),
            ] );
        }

        /**
         * Disable lazy loading for FooGallery when used in Bricks builder iframe
         * 
         * @param bool   $lazyload_support Whether lazy loading is supported
         * @param object $gallery The gallery object
         * @param string $template The template name
         * @return bool
         */
        public function disable_lazy_loading_for_bricks_builder( $lazyload_support, $gallery, $template ) {
            // Disable lazy loading only in Bricks builder iframe (canvas preview)
            if ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) {
                return false;
            }
            
        }

    }
}