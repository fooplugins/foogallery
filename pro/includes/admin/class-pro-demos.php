<?php

if ( ! class_exists( 'FooGallery_Pro_Demos' ) ) {

	class FooGallery_Pro_Demos {

		function __construct() {
			add_action( 'wp_ajax_foogallery_admin_import_pro_demos', array( $this, 'create_pro_demo_galleries' ) );
			add_action( 'edit_form_after_title', array( $this, 'maybe_render_gallery_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_gallery_notice_assets' ) );
			add_action( 'wp_ajax_foogallery_dismiss_gallery_notice', array( $this, 'ajax_dismiss_gallery_notice' ) );
		}

		/**
		 * Render the gallery notice when the post meta is available.
		 *
		 * @param WP_Post $post The current post object.
		 */
		function maybe_render_gallery_notice( $post ) {
			if ( empty( $post ) || FOOGALLERY_CPT_GALLERY !== $post->post_type ) {
				return;
			}

			$notice = get_post_meta( $post->ID, '_foogallery_notice', true );

			if ( empty( $notice ) ) {
				return;
			}

			$nonce  = wp_create_nonce( 'foogallery_dismiss_gallery_notice' );
			$markup = wpautop( wp_kses_post( $notice ) );

			echo '<div class="' . esc_attr( 'notice notice-info is-dismissible foogallery-gallery-notice' ) . '" data-post-id="' . absint( $post->ID ) . '" data-nonce="' . esc_attr( $nonce ) . '">';
			echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already sanitized above
			echo '</div>';
		}

		/**
		 * Enqueue inline assets so the notice can be dismissed via AJAX.
		 *
		 * @param string $hook The current admin page hook.
		 */
		function enqueue_gallery_notice_assets( $hook ) {
			if ( wp_doing_ajax() ) {
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( empty( $screen ) || 'post' !== $screen->base || FOOGALLERY_CPT_GALLERY !== $screen->post_type ) {
				return;
			}

			wp_enqueue_script( 'jquery' );

			$script = <<<'JS'
jQuery(function($){
	$(document).on('click', '.foogallery-gallery-notice .notice-dismiss', function(){
		var $notice = $(this).closest('.foogallery-gallery-notice');
		var postId = $notice.data('postId');
		var nonce = $notice.data('nonce');

		if (!postId || !nonce) {
			return;
		}

		$.post(ajaxurl, {
			action: 'foogallery_dismiss_gallery_notice',
			post_id: postId,
			nonce: nonce
		});
	});
});
JS;

			wp_add_inline_script( 'jquery', $script );
		}

		/**
		 * Handle AJAX requests to dismiss the gallery notice.
		 */
		function ajax_dismiss_gallery_notice() {
			$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

			check_ajax_referer( 'foogallery_dismiss_gallery_notice', 'nonce' );

			if ( ! $post_id || FOOGALLERY_CPT_GALLERY !== get_post_type( $post_id ) ) {
				wp_send_json_error();
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error();
			}

			delete_post_meta( $post_id, '_foogallery_notice' );

			wp_send_json_success();
		}

		/**
		 * Create PRO demo galleries via AJAX
		 */
		function create_pro_demo_galleries() {
			// Check if user has permission
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'foogallery' ) );
			}

			// Check nonce
			if ( ! check_ajax_referer( 'foogallery_admin_import_pro_demos' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'foogallery' ) );
			}

			// Check if PRO is active
			if ( ! foogallery_is_pro() ) {
				esc_html_e( 'PRO features are not available. Please upgrade to PRO to create demo galleries.', 'foogallery' );
				die();
			}

			$results = $this->create_pro_demo_content();

			if ( $results === false ) {
				esc_html_e( 'There was a problem creating the PRO demo galleries!', 'foogallery' );
			} else {
				printf(
					esc_html__( '%1$d sample images imported, and %2$d PRO demo galleries created!', 'foogallery' ),
					absint( $results['attachments'] ),
					absint( $results['galleries'] )
				);
			}
			die();
		}

		/**
		 * Create PRO demo content
		 * @return array|false
		 */
		function create_pro_demo_content() {
			$fs_instance = foogallery_fs();
			$foogallery_current_plan = $fs_instance->get_plan_name();

			// First, ensure we have the sample images using the existing demo content system
			$image_data = include( FOOGALLERY_PATH . 'includes/admin/demo-content-images.php' );
			$demo_galleries = array();

			if ( $foogallery_current_plan ===  FOOGALLERY_PRO_PLAN_STARTER ) {
				//No new demo images to import
				$demo_galleries = array_merge( $demo_galleries, include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-starter-galleries.php' );
			}

			if ( $foogallery_current_plan ===  FOOGALLERY_PRO_PLAN_EXPERT ) {
				//Import some demo videos.
				$image_data = array_merge( $image_data, include( FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-expert-images.php' ) );
				//Import pro starter galleries
				$demo_galleries = array_merge( $demo_galleries, include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-starter-galleries.php' );
				//Import pro expert galleries
				$demo_galleries = array_merge( $demo_galleries, include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-expert-galleries.php' );
			}

			if ( $foogallery_current_plan ===  FOOGALLERY_PRO_PLAN_COMMERCE ) {
				//Import some demo videos.
				$image_data = array_merge( $image_data, include( FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-expert-images.php' ) );
				//Import some commerce demo images.
				$image_data = array_merge( $image_data, include( FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-commerce-images.php' ) );
				//Import pro starter galleries
				$demo_galleries = array_merge( $demo_galleries, include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-starter-galleries.php' );
				//Import pro expert galleries
				$demo_galleries = array_merge( $demo_galleries, include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-expert-galleries.php' );
				//Import pro commerce galleries
				$demo_galleries = array_merge( $demo_galleries, include FOOGALLERY_PRO_PATH . 'includes/admin/demo-content-pro-commerce-galleries.php' );
			}

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

			if ( ! is_array( $demo_galleries ) ) {
				return false;
			}

			$galleries_created = 0;

			// Create each demo gallery using the existing system
			foreach ( $demo_galleries as $demo_gallery ) {
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
