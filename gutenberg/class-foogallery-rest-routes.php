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

			$query_args = array(
				'post_type'     => FOOGALLERY_CPT_GALLERY,
				'post_status'   => array( 'publish', 'draft' ),
				'cache_results' => false,
				'nopaging'      => true,
			);

			$limit = absint( foogallery_get_setting( 'limit_gallery_selector_block_editor', 0 ) );

			if ( $limit > 0 ) {
				$query_args['posts_per_page'] = $limit;
				$query_args['nopaging'] = false;
			}

			$switched = false;
			if ( is_multisite() ) {
				$current_blog_id = get_current_blog_id();
				if ( $current_blog_id ) {
					$switched = switch_to_blog( $current_blog_id );
				}
			}

			$gallery_posts = get_posts( $query_args );

			if ( $switched ) {
				restore_current_blog();
			}

			$result = array();

			if ( ! empty( $gallery_posts ) ) {
				foreach ( $gallery_posts as $gallery_post ) {
					if ( ! current_user_can( 'read_post', $gallery_post->ID ) ) {
						continue;
					}

					$gallery = FooGallery::get( $gallery_post );

					$image_args = array(
						'width'  => 150,
						'height' => 150
					);

					$img = foogallery_image_placeholder_src();

					$featured_attachment = $gallery->featured_attachment();
					if ( $featured_attachment && method_exists( $featured_attachment, 'html_img_src' ) ) {
						$img = $featured_attachment->html_img_src( $image_args );
					}

					$result[] = array(
						'id' => absint( $gallery->ID ),
						'name' => sanitize_text_field( $gallery->name ),
						'thumbnail' => esc_url_raw( $img )
					);
				}
			}

			return rest_ensure_response( $result );
		}
	}
}
