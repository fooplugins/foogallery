<?php

/**
 * FooGallery FrontEnd Upload includes
 */
require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php';
require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/class-foogallery-frontend-upload-metaboxes.php';
require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/class-foogallery-upload-shortcode.php';
require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/class-foogallery-frontend-upload-moderation.php';


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
			new Foogallery_FrontEnd_Upload_Shortcode();
            new Foogallery_FrontEnd_Upload_Moderation();
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