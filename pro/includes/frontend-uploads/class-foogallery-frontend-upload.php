<?php

/**
 * Foogallery FrontEnd Upload Class
 */
if ( ! class_exists( 'Foogallery_FrontEnd_Upload' ) ) {

	/**
	 * Class Foogallery FrontEnd Upload 
	 */
	class Foogallery_FrontEnd_Upload {

		/**
		 * Foogallery_FrontEnd_Upload constructor.
		 */
		public function __construct() {			
            add_action( 'admin_menu', array( $this, 'add_image_moderation_submenu' ) );            
		}        

        // Add a sub-menu to the FooGallery menu
        public function add_image_moderation_submenu() {
            $parent_slug = foogallery_admin_menu_parent_slug();
            
            add_submenu_page(
                $parent_slug,
                'Moderation',
                'Moderation',
                'manage_options',
                'image-moderation',
                array( $this, 'render_image_moderation_page' )
            );
        }

        // Callback function to render the page content
        public function render_image_moderation_page() {
            require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/view-image-moderation.php';
        }
	}
}