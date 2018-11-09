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

			$galleries = foogallery_get_all_galleries();

			$result = array();

			if ( !empty( $galleries ) ) {
				foreach ( $galleries as $gallery ) {
					$args = array(
						'width' => 150,
						'height' => 150
					);

					$featuredAttachment = $gallery->featured_attachment();
					if ( $featuredAttachment ) {
						$img = $featuredAttachment->html_img_src( $args );
					} else {
						//if we have no featured attachment, then use the built-in image placeholder
						$img = foogallery_image_placeholder_src();
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
					'id' => array(
						'description' => __( 'The FooGallery ID.', 'foogallery' ),
						'type'        => 'int',
						'required'    => true,
						'context'     => array( 'edit' ),
					),
					'name' => array(
						'description' => __( 'The FooGallery Name.', 'foogallery' ),
						'type'        => 'string',
						'required'    => false,
						'context'     => array( 'edit' ),
					),
					'thumbnail' => array(
						'description' => __( 'The FooGallery Thumbnail.', 'foogallery' ),
						'type'        => 'string',
						'required'    => false,
						'context'     => array( 'edit' ),
					),
				),
			);
		}
	}
}
