<?php

if ( ! class_exists( 'FooGallery_Pro_Demos' ) ) {

	class FooGallery_Pro_Demos {

		function __construct() {
			add_action( 'wp_ajax_foogallery_admin_import_pro_demos', array( $this, 'create_pro_demo_galleries' ) );
		}

		/**
		 * Create PRO demo galleries via AJAX
		 */
		function create_pro_demo_galleries() {
			// Check if user has permission
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'foogallery' ) );
			}

			// Check nonce
			if ( ! check_admin_referer( 'foogallery_admin_import_pro_demos' ) ) {
				wp_die( __( 'Security check failed.', 'foogallery' ) );
			}

			// Check if PRO is active
			if ( ! foogallery_is_pro() ) {
				echo __( 'PRO features are not available. Please upgrade to PRO to create demo galleries.', 'foogallery' );
				die();
			}

			$results = $this->create_pro_demo_content();

			if ( $results === false ) {
				echo __( 'There was a problem creating the PRO demo galleries!', 'foogallery' );
			} else {
				echo sprintf( __( '%d sample images imported, and %d PRO demo galleries created!', 'foogallery' ), $results['attachments'], $results['galleries'] );
			}
			die();
		}

		/**
		 * Create PRO demo content
		 * @return array|false
		 */
		function create_pro_demo_content() {
			// First, ensure we have the sample images using the existing demo content system
			$image_data = include( FOOGALLERY_PATH . 'includes/admin/demo-content-images.php' );

			$images_imported     = 0;
			$attachment_mappings = array();

			// Import attachments using the existing demo content class
			$demo_content = new FooGallery_Admin_Demo_Content();
			
			foreach ( $image_data as $attachment_data ) {
				$result = $demo_content->import_attachment( $attachment_data );
				if ( $result !== false ) {
					if ( $result['imported'] ) {
						$images_imported++;
					}
					$attachment_mappings[ $result['key'] ] = intval( $result['attachment_id'] );
				}
			}

			// Load the pro demo galleries data
			$pro_demo_galleries = include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-galleries.php';

			if ( ! is_array( $pro_demo_galleries ) ) {
				return false;
			}

			$galleries_created = 0;

			// Create each demo gallery using the existing system
			foreach ( $pro_demo_galleries as $demo_gallery ) {
				$result = $demo_content->import_gallery( $demo_gallery, $attachment_mappings );
				if ( $result !== false ) {
					if ( $result['imported'] ) {
						$galleries_created++;
					}
				}
			}

			return array(
				'attachments' => $images_imported,
				'galleries'   => $galleries_created
			);
		}

	}
}
