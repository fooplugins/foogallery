<?php
/*
 * FooGallery Custom Post Types and Custom Taxonomy Registration class
 */

if ( ! class_exists( 'FooGallery_PostTypes' ) ) {

	class FooGallery_PostTypes {

		function __construct() {
			//register the post types
			add_action( 'init', array( $this, 'register' ) );

			//update post type messages
			add_filter( 'post_updated_messages', array( $this, 'update_messages' ) );

			//update post bulk messages
			add_filter( 'bulk_post_updated_messages', array( $this, 'update_bulk_messages' ), 10, 2 );
		}

		function register() {
			//allow extensions to override the gallery post type
			$args = apply_filters( 'foogallery_gallery_posttype_register_args',
				array(
					'labels'        => array(
						'name'               => __( 'Galleries', 'foogallery' ),
						'singular_name'      => __( 'Gallery', 'foogallery' ),
						'add_new'            => __( 'Add Gallery', 'foogallery' ),
						'add_new_item'       => __( 'Add New Gallery', 'foogallery' ),
						'edit_item'          => __( 'Edit Gallery', 'foogallery' ),
						'new_item'           => __( 'New Gallery', 'foogallery' ),
						'view_item'          => __( 'View Gallery', 'foogallery' ),
						'search_items'       => __( 'Search Galleries', 'foogallery' ),
						'not_found'          => __( 'No Galleries found', 'foogallery' ),
						'not_found_in_trash' => __( 'No Galleries found in Trash', 'foogallery' ),
						'menu_name'          => foogallery_plugin_name(),
						'all_items'          => __( 'Galleries', 'foogallery' )
					),
					'hierarchical'  => false,
					'public'        => false,
					'rewrite'       => false,
					'show_ui'       => true,
					'show_in_menu'  => true,
					'menu_icon'     => 'dashicons-format-gallery',
					'supports'      => array( 'title', 'thumbnail', ),
				)
			);

			register_post_type( FOOGALLERY_CPT_GALLERY, $args );
		}

		/**
		 * Customize the update messages for a gallery
		 *
		 * @global object $post     The current post object.
		 *
		 * @param array   $messages Array of default post updated messages.
		 *
		 * @return array $messages Amended array of post updated messages.
		 */
		public function update_messages( $messages ) {

			global $post;

			// Add our gallery messages
			$messages[FOOGALLERY_CPT_GALLERY] = apply_filters( 'foogallery_gallery_posttype_update_messages',
				array(
					0  => '',
					1  => __( 'Gallery updated.', 'foogallery' ),
					2  => __( 'Gallery custom field updated.', 'foogallery' ),
					3  => __( 'Gallery custom field deleted.', 'foogallery' ),
					4  => __( 'Gallery updated.', 'foogallery' ),
					5  => isset($_GET['revision']) ? sprintf( __( 'Gallery restored to revision from %s.', 'foogallery' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
					6  => __( 'Gallery published.', 'foogallery' ),
					7  => __( 'Gallery saved.', 'foogallery' ),
					8  => __( 'Gallery submitted.', 'foogallery' ),
					9  => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>.', 'foogallery' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
					10 => __( 'Gallery draft updated.', 'foogallery' )
				)
			);

			return $messages;

		}

		/**
		 * Customize the bulk update messages for a gallery
		 *
		 * @param array $bulk_messages Array of default bulk updated messages.
		 * @param array $bulk_counts   Array containing count of posts involved in the action.
		 *
		 * @return array mixed            Amended array of bulk updated messages.
		 */
		function update_bulk_messages( $bulk_messages, $bulk_counts ) {

			$bulk_messages[FOOGALLERY_CPT_GALLERY] = apply_filters( 'foogallery_gallery_posttype_bulk_update_messages',
				array(
					'updated'   => _n( '%s Gallery updated.', '%s Galleries updated.', $bulk_counts['updated'], 'foogallery' ),
					'locked'    => _n( '%s Gallery not updated, somebody is editing it.', '%s Galleries not updated, somebody is editing them.', $bulk_counts['locked'], 'foogallery' ),
					'deleted'   => _n( '%s Gallery permanently deleted.', '%s Galleries permanently deleted.', $bulk_counts['deleted'], 'foogallery' ),
					'trashed'   => _n( '%s Gallery moved to the Trash.', '%s Galleries moved to the Trash.', $bulk_counts['trashed'], 'foogallery' ),
					'untrashed' => _n( '%s Gallery restored from the Trash.', '%s Galleries restored from the Trash.', $bulk_counts['untrashed'], 'foogallery' ),
				)
			);

			return $bulk_messages;
		}
	}
}
