<?php

/**
 * FooGallery Album Custom Post Types
 *
 * @package FooGallery
 */

if ( ! class_exists( 'FooGallery_Albums_PostTypes' ) ) {
	/**
	 * Class FooGallery_Post_Type
	 *
	 * Handles the registration and management of custom post types for FooGallery.
	 */
	class FooGallery_Albums_PostTypes {

		/**
		 * Constructor method.
		 */
		public function __construct() {
			// register the post types.
			add_action( 'init', array( $this, 'register_posttype' ) );

			// update post type messages.
			add_filter( 'post_updated_messages', array( $this, 'update_messages' ) );

			// update post bulk messages.
			add_filter( 'bulk_post_updated_messages', array( $this, 'update_bulk_messages' ), 10, 2 );
		}

		/**
		 * Registers the custom post types.
		 */
		public function register_posttype() {
			$album_creator_role   = foogallery_get_setting( 'album_creator_role', 'inherit' );
			$gallery_creator_role = foogallery_get_setting( 'gallery_creator_role', '' );

			$args = array(
				'labels'       => array(
					'name'               => __( 'Albums', 'foogallery' ),
					'singular_name'      => __( 'Album', 'foogallery' ),
					'add_new'            => __( 'Add Album', 'foogallery' ),
					'add_new_item'       => __( 'Add New Album', 'foogallery' ),
					'edit_item'          => __( 'Edit Album', 'foogallery' ),
					'new_item'           => __( 'New Album', 'foogallery' ),
					'view_item'          => __( 'View Album', 'foogallery' ),
					'search_items'       => __( 'Search Albums', 'foogallery' ),
					'not_found'          => __( 'No Albums found', 'foogallery' ),
					'not_found_in_trash' => __( 'No Albums found in Trash', 'foogallery' ),
					'all_items'          => __( 'Albums', 'foogallery' ),
				),
				'hierarchical' => false,
				'public'       => false,
				'rewrite'      => false,
				'show_ui'      => true,
				'supports'     => array( 'title' ),
                'show_in_menu' => foogallery_admin_menu_parent_slug()
			);

			if ( 'inherit' !== $album_creator_role && '' !== $album_creator_role ) {
				$args['capabilities'] = array(
					'create_posts'         => $album_creator_role,
					'create_post'          => $album_creator_role,
					'edit_posts'           => $album_creator_role,
					'edit_post'            => $album_creator_role,
					'delete_posts'         => $album_creator_role,
					'delete_post'          => $album_creator_role,
					'read_post'            => $album_creator_role,
				);
			} elseif ( '' !== $gallery_creator_role ) {
				$args['capabilities'] = array(
					'create_posts' => $gallery_creator_role,
					'create_post'  => $gallery_creator_role,
					'edit_posts'   => $gallery_creator_role,
					'edit_post'    => $gallery_creator_role,
					'delete_post'  => $gallery_creator_role,
					'delete_posts' => $gallery_creator_role,
					'read-post'    => $gallery_creator_role,
				);
			}

			$args = apply_filters( 'foogallery_album_posttype_register_args', $args );
			register_post_type( FOOGALLERY_CPT_ALBUM, $args );
		}

		/**
		 * Customize the update messages for a album
		 *
		 * @global object $post     The current post object.
		 *
		 * @param array $messages Array of default post updated messages.
		 *
		 * @return array $messages Amended array of post updated messages.
		 */
		public function update_messages( $messages ) {

			global $post;

			// Add our album messages.
			$messages[ FOOGALLERY_CPT_ALBUM ] = apply_filters(
				'foogallery_album_posttype_update_messages',
				array(
					0  => '',
					1  => __( 'Album updated.', 'foogallery' ),
					2  => __( 'Album custom field updated.', 'foogallery' ),
					3  => __( 'Album custom field deleted.', 'foogallery' ),
					4  => __( 'Album updated.', 'foogallery' ),
					5  => isset( $_GET['revision'] ) ? sprintf( __( 'Album restored to revision from %s.', 'foogallery' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
					6  => __( 'Album published.', 'foogallery' ),
					7  => __( 'Album saved.', 'foogallery' ),
					8  => __( 'Album submitted.', 'foogallery' ),
					9  => sprintf( __( 'Album scheduled for: <strong>%1$s</strong>.', 'foogallery' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
					10 => __( 'Album draft updated.', 'foogallery' ),
				)
			);

			return $messages;

		}

		/**
		 * Customize the bulk update messages for a album
		 *
		 * @param array $bulk_messages Array of default bulk updated messages.
		 * @param array $bulk_counts   Array containing count of posts involved in the action.
		 *
		 * @return array mixed            Amended array of bulk updated messages.
		 */
		public function update_bulk_messages( $bulk_messages, $bulk_counts ) {

			$bulk_messages[ FOOGALLERY_CPT_ALBUM ] = apply_filters(
				'foogallery_album_posttype_bulk_update_messages',
				array(
					'updated'   => _n( '%s Album updated.', '%s Albums updated.', $bulk_counts['updated'], 'foogallery' ),
					'locked'    => _n( '%s Album not updated, somebody is editing it.', '%s Albums not updated, somebody is editing them.', $bulk_counts['locked'], 'foogallery' ),
					'deleted'   => _n( '%s Album permanently deleted.', '%s Albums permanently deleted.', $bulk_counts['deleted'], 'foogallery' ),
					'trashed'   => _n( '%s Album moved to the Trash.', '%s Albums moved to the Trash.', $bulk_counts['trashed'], 'foogallery' ),
					'untrashed' => _n( '%s Album restored from the Trash.', '%s Albums restored from the Trash.', $bulk_counts['untrashed'], 'foogallery' ),
				)
			);

			return $bulk_messages;
		}
	}
}
