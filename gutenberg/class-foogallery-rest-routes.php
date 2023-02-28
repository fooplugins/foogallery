<?php
/**
 * class for Rest API Routes within FooGallery
 *
 * @since 1.6.0
 */
if ( ! class_exists( 'FooGallery_Rest_Routes' ) ) {

	class FooGallery_Rest_Routes {
		/**
		 * Constructs the class.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Registers the necessary REST API routes for FooGallery
		 *
		 * @access public
		 */
		public function register_routes() {
			if ( !apply_filters( 'foogallery_gutenberg_enabled', true ) ) {
				return;
			}

			register_rest_route(
				'foogallery/v1',
				'galleries',
				array(
					'methods'  			  => WP_REST_Server::READABLE,
					'callback' 			  => array( $this, 'get_galleries' ),
					'permission_callback' => array( $this, 'get_galleries_permissions_check' )
				)
			);
		}

		/**
		 * Checks if a given request has access to get galleries.
		 *
		 * @access public
		 *
		 * @param WP_REST_Request $request Request.
		 *
		 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
		 */
		public function get_galleries_permissions_check( $request ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				return new WP_Error(
					'foogallery_galleries_cannot_read',
					__( 'Sorry, you are not allowed to read galleries as this user.', 'foogallery' ),
					array(
						'status' => rest_authorization_required_code(),
					)
				);
			}

			return true;
		}

		/**
		 * Returns a list of all galleries.
		 *
		 * @since  2.8.0
		 * @access public
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 *
		 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
		 */
		public function get_galleries( $request ) {

			$galleries = foogallery_get_all_galleries();

			$result = array();

			if ( !empty( $galleries ) ) {
				foreach ( $galleries as $gallery ) {
					$args = array(
						'width' => 150,
						'height' => 150
					);

					$img = foogallery_image_placeholder_src();

					$featuredAttachment = @$gallery->featured_attachment();
					if ( $featuredAttachment ) {
						$img = @$featuredAttachment->html_img_src( $args );
					}

					$result[] = array(
						'id' => $gallery->ID,
						'name' => $gallery->name,
						'thumbnail' => $img
					);
				}
			}

			return rest_ensure_response( $result );
		}
	}
}
