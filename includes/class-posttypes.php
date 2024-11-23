<?php
/*
 * FooGallery Custom Post Types and Custom Taxonomy Registration class
 */

if ( ! class_exists( 'FooGallery_PostTypes' ) ) {

    class FooGallery_PostTypes {

        const GALLERY_CAPABILITIES = array(
            'edit_post'          => 'edit_foogallery',
            'read_post'          => 'read_foogallery',
            'delete_post'        => 'delete_foogallery',
            'edit_posts'         => 'edit_foogalleries',
            'edit_others_posts'  => 'edit_others_foogalleries',
            'delete_posts'       => 'delete_foogalleries',
            'publish_posts'      => 'publish_foogalleries',
            'read_private_posts' => 'read_private_foogalleries',
            'create_posts'       => 'create_foogalleries'
        );

        function __construct() {
            //register the post types
            add_action( 'init', array( $this, 'register' ) );

            //register the custom capabilities.
            add_action( 'admin_init', array( $this, 'add_capabilities' ) );

            //update post type messages
            add_filter( 'post_updated_messages', array( $this, 'update_messages' ) );

            //update post bulk messages
            add_filter( 'bulk_post_updated_messages', array( $this, 'update_bulk_messages' ), 10, 2 );

            //clear capabilities after option update.
            add_action( 'update_option_foogallery', array( $this, 'clear_capabilities' ), 10, 3 );
        }

        /**
         * Registers the custom post type for galleries.
         *
         * This function is responsible for registering the custom post type 'gallery' used by the FooGallery plugin.
         * It now includes custom capabilities and adds filters for custom capability mapping and menu visibility control.
         */
        function register() {

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
                'capabilities' => FooGallery_PostTypes::GALLERY_CAPABILITIES
            );

            $args = apply_filters( 'foogallery_gallery_posttype_register_args', $args );
            register_post_type( FOOGALLERY_CPT_GALLERY, $args );
        }

        /**
         * Adds capabilities to the allowed roles, based on the gallery creator role that is set.
         *
         * @return void
         */
        function add_capabilities( $force = false ) {
            global $foogallery_adding_capabilities;

            $gallery_creator_role = foogallery_setting_gallery_creator_role();
            $previous_capabilities = get_option('foogallery_capabilities_set' );

            if ( $force || $gallery_creator_role !== $previous_capabilities ) {

                $foogallery_adding_capabilities = true;
                update_option( 'foogallery_capabilities_set', $gallery_creator_role );
                $foogallery_adding_capabilities = false;

                // Get the roles
                $roles = foogallery_get_roles_and_higher( $gallery_creator_role );

                foreach ( $roles as $the_role ) {
                    $role = get_role( $the_role );

                    if ( !is_null( $role ) ) {

                        foreach ( FooGallery_PostTypes::GALLERY_CAPABILITIES as $cap_key => $cap ) {
                            $role->add_cap( $cap );
                        }
                    }
                }
            }
        }

        /**
         * Clears the capabilities based on the new value, old value, and option.
         *
         * @param mixed $old_value The old value.
         * @param mixed $value The new value.
         * @param string $option The option.
         */
        function clear_capabilities( $old_value, $value, $option ) {
            global $foogallery_adding_capabilities;
            // Get out early, if we are busy updating capabilities.
            if ( $foogallery_adding_capabilities ) {
                return;
            }

            if ( $old_value === $value ) {
                return;
            }

            $gallery_creator_role = foogallery_setting_gallery_creator_role();
            $previous_capabilities = get_option('foogallery_capabilities_set' );

            if ( $gallery_creator_role !== $previous_capabilities ) {

                // Get all roles
                $roles = wp_roles()->get_names();

                // Loop through each role and remove the capabilities
                foreach ( $roles as $role => $name ) {
                    $role_obj = get_role($role);
                    if (!is_null($role_obj)) {
                        foreach ( FooGallery_PostTypes::GALLERY_CAPABILITIES as $cap_key => $cap ) {
                            $role_obj->remove_cap($cap);
                        }
                    }
                }

                do_action('foogallery_gallery_posttype_clear_capabilities');
                $this->add_capabilities( true );
            }
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
    }
}