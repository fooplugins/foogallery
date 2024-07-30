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

        /**
         * Registers the custom post type for galleries.
         *
         * This function is responsible for registering the custom post type 'gallery' used by the FooGallery plugin.
         * It now includes custom capabilities and adds filters for custom capability mapping and menu visibility control.
         */
        function register() {
            $gallery_creator_role = foogallery_get_setting( 'gallery_creator_role', 'administrator' );

            $args = array(
                'labels'       => array(
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
                    'all_items'          => __( 'Galleries', 'foogallery' ),
                ),
                'hierarchical' => false,
                'public'       => false, // set to false to make the post type private
                'rewrite'      => false,
                'show_ui'      => true,
                'show_in_menu' => true,
                'menu_icon'    => 'dashicons-format-gallery',
                'supports'     => array( 'title', 'thumbnail' ),
                'capabilities' => array(
                    // Custom capabilities for fine-grained control
                    'edit_post'          => 'edit_foogallery',
                    'read_post'          => 'read_foogallery',
                    'delete_post'        => 'delete_foogallery',
                    'edit_posts'         => 'edit_foogalleries',
                    'edit_others_posts'  => 'edit_others_foogalleries',
                    'publish_posts'      => 'publish_foogalleries',
                    'read_private_posts' => 'read_private_foogalleries',
                    'create_posts'       => 'create_foogalleries',
                ),
                'map_meta_cap' => true, // Allows for fine-grained control over capability mapping
            );

            $args = apply_filters( 'foogallery_gallery_posttype_register_args', $args );
            register_post_type( FOOGALLERY_CPT_GALLERY, $args );

            // Add custom capability check
            add_filter( 'map_meta_cap', array( $this, 'map_gallery_meta_cap' ), 10, 4 );

            // Add a function to control menu visibility
            add_action( 'admin_menu', array( $this, 'control_menu_visibility' ), 999 );
        }

        /**
         * Customize the update messages for a gallery.
         *
         * @global object $post     The current post object.
         *
         * @param array   $messages Array of default post updated messages.
         *
         * @return array $messages Amended array of post updated messages.
         */
        public function update_messages( $messages ) {
            global $post;

            // Add our gallery messages.
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

        /**
         * Custom capability mapping for FooGallery
         *
         * This function implements custom logic for FooGallery capabilities.
         * It ensures that only users with roles equal to or higher than the gallery creator role
         * have access to FooGallery functionalities, implementing proper role inheritance.
         *
         * @param array  $caps    The user's actual capabilities.
         * @param string $cap     The capability being checked.
         * @param int    $user_id The user ID.
         * @param array  $args    Additional arguments passed to the capability check.
         * @return array The user's altered capabilities.
         */
        public function map_gallery_meta_cap( $caps, $cap, $user_id, $args ) {
            $foogallery_caps = array(
                'edit_foogallery',
                'read_foogallery',
                'delete_foogallery',
                'edit_foogalleries',
                'edit_others_foogalleries',
                'publish_foogalleries',
                'read_private_foogalleries',
                'create_foogalleries'
            );

            // Only handle FooGallery-specific capabilities
            if (!in_array($cap, $foogallery_caps)) {
                return $caps;
            }

            $gallery_creator_role = foogallery_get_setting( 'gallery_creator_role', 'administrator' );

            // Define the hierarchy of roles
            $role_hierarchy = array(
                'administrator' => 4,
                'editor'        => 3,
                'author'        => 2,
                'contributor'   => 1,
                'subscriber'    => 0,
            );

            $user = get_userdata( $user_id );
            if ( $user === false ) {
                return $caps;
            }

            $user_roles = $user->roles;
            $user_highest_role = 0;
            foreach ( $user_roles as $role ) {
                if ( isset( $role_hierarchy[$role] ) && $role_hierarchy[$role] > $user_highest_role ) {
                    $user_highest_role = $role_hierarchy[$role];
                }
            }

            // Allow if user's role is equal to or higher than gallery_creator_role
            if ( isset($role_hierarchy[$gallery_creator_role]) && $user_highest_role >= $role_hierarchy[$gallery_creator_role] ) {
                return array('exist');
            }

            // If we reach here, the user doesn't have permission for this FooGallery capability
            return array('do_not_allow');
        }

        /**
         * Controls the visibility of the FooGallery menu item in the admin panel
         *
         * This function removes the FooGallery menu item for users who don't have
         * the appropriate role to access FooGallery functionalities.
         */
        public function control_menu_visibility() {
            $gallery_creator_role = foogallery_get_setting( 'gallery_creator_role', 'administrator' );
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;

            $role_hierarchy = array(
                'administrator' => 4,
                'editor'        => 3,
                'author'        => 2,
                'contributor'   => 1,
                'subscriber'    => 0,
            );

            $user_highest_role = 0;
            foreach ( $user_roles as $role ) {
                if ( isset( $role_hierarchy[$role] ) && $role_hierarchy[$role] > $user_highest_role ) {
                    $user_highest_role = $role_hierarchy[$role];
                }
            }

            if ( !isset($role_hierarchy[$gallery_creator_role]) || $user_highest_role < $role_hierarchy[$gallery_creator_role] ) {
                remove_menu_page( 'edit.php?post_type=' . FOOGALLERY_CPT_GALLERY );
            }
        }
    }
}