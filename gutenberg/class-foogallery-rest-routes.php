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
			$nonce = wp_create_nonce( 'wp_rest' );


			register_rest_route(
				'foogallery/v1',
				'galleries',
				array(
					'methods'  			  => WP_REST_Server::READABLE,
					'callback' 			  => array( $this, 'get_galleries' ),
					'permission_callback' => array( $this, 'get_galleries_permissions_check' ),
					'schema' 			  => array( $this, 'get_galleries_schema' ),
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
			$args = array(
				'post_type'     => FOOGALLERY_CPT_GALLERY,
				'post_status'	=> array( 'publish', 'draft' ),
				'cache_results' => false,
				'nopaging'      => true,
			);

			$gallery_posts = get_posts( $args );

			$galleries = array();

			if ( !empty( $gallery_posts ) ) {
				foreach ( $gallery_posts as $post ) {
					$galleries[] = array(
						'ID' => $post->ID,
						'Name' => $post->post_title
					);
				}
			}

			return rest_ensure_response( $galleries );
		}

		/**
		 * Retrieves block's output schema, conforming to JSON Schema.
		 *
		 * @since  2.8.0
		 * @access public
		 *
		 * @return array Item schema data.
		 */
		public function get_item_schema() {
			return array(
				'$schema'    => 'http://json-schema.org/schema#',
				'title'      => 'foogallery',
				'type'       => 'object',
				'properties' => array(
					'ID' => array(
						'description' => __( 'The FooGallery ID.', 'foogallery' ),
						'type'        => 'int',
						'required'    => true,
						'context'     => array( 'edit' ),
					),
					'Name' => array(
						'description' => __( 'The FooGallery Name.', 'foogallery' ),
						'type'        => 'string',
						'required'    => false,
						'context'     => array( 'edit' ),
					),
				),
			);
		}
	}
}
