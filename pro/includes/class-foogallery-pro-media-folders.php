<?php
/**
 * Media modal folder sidebar and drag-drop assignment support for FooGallery PRO.
 */

if ( ! class_exists( 'FooGallery_Pro_Media_Folders' ) ) {

    class FooGallery_Pro_Media_Folders {

        /**
         * FooGallery_Pro_Media_Folders constructor.
         */
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_modal_assets' ) );
            add_filter( 'ajax_query_attachments_args', array( $this, 'filter_attachments_by_folder' ) );
            add_action( 'wp_ajax_foogallery_assign_media_categories', array( $this, 'ajax_assign_media_categories' ) );
            add_action( 'wp_ajax_foogallery_reorder_media_categories', array( $this, 'ajax_reorder_media_categories' ) );
        }

        /**
         * Enqueue scripts and styles needed for the media modal folder tree.
         *
         * @param string $hook Current admin hook.
         */
        public function enqueue_media_modal_assets( $hook ) {
            if ( ! taxonomy_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY ) ) {
                return;
            }

            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
            if ( ! $screen ) {
                return;
            }

            $allowed_screens = array( 'upload', 'media', 'post', 'post-new', FOOGALLERY_CPT_GALLERY );
            if ( ! in_array( $screen->base, $allowed_screens, true ) && ! in_array( $screen->id, $allowed_screens, true ) ) {
                return;
            }

            $taxonomy = get_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
            if ( ! $taxonomy ) {
                return;
            }

            wp_enqueue_media();

            $script_handle = 'foogallery-pro-admin-media-folders';
            $style_handle  = 'foogallery-pro-admin-media-folders-css';

            wp_register_script(
                $script_handle,
                FOOGALLERY_PRO_URL . 'js/foogallery.admin.media-folders.js',
                array( 'jquery', 'media-views', 'wp-util' ),
                FOOGALLERY_VERSION,
                true
            );

            wp_register_style(
                $style_handle,
                FOOGALLERY_PRO_URL . 'css/foogallery.admin.media-folders.css',
                array(),
                FOOGALLERY_VERSION
            );

            wp_enqueue_script( $script_handle );
            wp_enqueue_style( $style_handle );

            $terms = get_terms(
                array(
                    'taxonomy'   => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
                    'hide_empty' => false,
                )
            );

            if ( is_wp_error( $terms ) ) {
                return;
            }

            $term_data = array();
            foreach ( $terms as $term ) {
                $term_data[] = array(
                    'id'     => (int) $term->term_id,
                    'name'   => $term->name,
                    'parent' => (int) $term->parent,
                    'count'  => (int) $term->count,
                    'order'  => (int) get_term_meta( $term->term_id, '_foogallery_folder_order', true ),
                );
            }

            $capabilities  = isset( $taxonomy->cap->assign_terms ) ? $taxonomy->cap->assign_terms : 'upload_files';
            $can_assign    = current_user_can( $capabilities );
            $localizedData = array(
                'terms'     => $term_data,
                'taxonomy'  => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( 'foogallery-assign-media-categories' ),
                'canAssign' => $can_assign,
                'strings'   => array(
                    'foldersHeading'     => __( 'Folders', 'foogallery' ),
                    'allFolders'         => __( 'All Folders', 'foogallery' ),
                    'dropHere'           => __( 'Drop to move selected items here', 'foogallery' ),
                    'assigning'          => __( 'Assigning…', 'foogallery' ),
                    'assignmentSuccess'  => __( 'Folder assignment saved.', 'foogallery' ),
                    'assignmentFailure'  => __( 'Could not update folder. Please try again.', 'foogallery' ),
                    'dragToFolder'       => __( 'Drag selected items to a folder to assign it.', 'foogallery' ),
                    'helpHtml'           => wp_kses_post( $this->build_html_help() ),
                ),
			);

            wp_localize_script( $script_handle, 'FOOGALLERY_MEDIA_FOLDERS', $localizedData );
        }

		private function build_html_help() {
			return '<ul><li>' . __( 'Select a folder in the list to show all attachments in that folder.', 'foogallery' ) . '</li>' .
			 '<li>' . __( 'Drag attachments into a folder to assign them.', 'foogallery' ) . '</li>' .
			 '<li>' . sprintf( __( 'Drag attachments onto %s to remove them from the folder.', 'foogallery' ), '<em>' . __( 'Unassign', 'foogallery' ) . '</em>' ) . '</li>' .
			 '<li>' . sprintf( __( 'Use the %s button to rename, delete or reorder folders.', 'foogallery' ), '<i class="dashicons dashicons-admin-generic"></i>' ) . '</li>' .
			 '<li>' . __( 'Drag folders to reorder or nest them.', 'foogallery' ) . '</li>' .
			 '<li>' . __( 'Click + to add a new folder.', 'foogallery' ) . '</li></ul>';
		}

        /**
         * Add a tax_query for media categories when a folder filter is present.
         *
         * @param array $query The attachment query arguments.
         *
         * @return array
         */
        public function filter_attachments_by_folder( $query ) {
            if ( ! taxonomy_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY ) ) {
                return $query;
            }

            $request = isset( $_REQUEST['query'] ) ? wp_unslash( $_REQUEST['query'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $folder  = isset( $request['foogallery_folder'] ) ? absint( $request['foogallery_folder'] ) : 0;

            if ( $folder <= 0 ) {
                return $query;
            }

            if ( ! isset( $query['tax_query'] ) || ! is_array( $query['tax_query'] ) ) {
                $query['tax_query'] = array();
            }

            $query['tax_query'][] = array(
                'taxonomy'         => FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY,
                'field'            => 'term_id',
                'terms'            => $folder,
                'include_children' => true,
            );

            return $query;
        }

        /**
         * Ajax handler for assigning media categories to a set of attachments.
         */
        public function ajax_assign_media_categories() {
            if ( ! check_ajax_referer( 'foogallery-assign-media-categories', 'nonce', false ) ) {
                wp_send_json_error( array( 'message' => __( 'Security check failed.', 'foogallery' ) ), 403 );
            }

            if ( ! taxonomy_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY ) ) {
                wp_send_json_error( array( 'message' => __( 'Folders are unavailable.', 'foogallery' ) ), 400 );
            }

            $taxonomy = get_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
            if ( ! $taxonomy ) {
                wp_send_json_error( array( 'message' => __( 'Folders are unavailable.', 'foogallery' ) ), 400 );
            }

            $term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $ids     = isset( $_POST['attachment_ids'] ) ? (array) $_POST['attachment_ids'] : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $ids     = array_filter( array_map( 'absint', $ids ) );
            $source_term_id = isset( $_POST['source_term_id'] ) ? absint( $_POST['source_term_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ( $term_id < 0 || empty( $ids ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid folder or attachments.', 'foogallery' ) ), 400 );
            }

            if ( ! current_user_can( $taxonomy->cap->assign_terms ) ) {
                wp_send_json_error( array( 'message' => __( 'You cannot assign folders.', 'foogallery' ) ), 403 );
            }

            // term_id 0 means unassign all folder terms.
            if ( 0 === $term_id ) {
                $updated = 0;
                foreach ( $ids as $attachment_id ) {
                    if ( current_user_can( 'edit_post', $attachment_id ) ) {
                        if ( $source_term_id > 0 ) {
                            wp_remove_object_terms( $attachment_id, array( $source_term_id ), FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
                            $updated++;
                        } else {
                            wp_set_object_terms( $attachment_id, array(), FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, false );
                            $updated++;
                        }
                    }
                }
                wp_send_json_success( array( 'updated' => $updated ) );
            }

            $term = get_term( $term_id, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
            if ( ! $term || is_wp_error( $term ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid folder or attachments.', 'foogallery' ) ), 400 );
            }

            $updated = 0;
            foreach ( $ids as $attachment_id ) {
                if ( current_user_can( 'edit_post', $attachment_id ) ) {
                    wp_set_object_terms( $attachment_id, $term_id, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, true );
                    $updated++;
                }
            }

            wp_send_json_success( array( 'updated' => $updated ) );
        }

        /**
         * Ajax handler to save folder order for siblings.
         */
        public function ajax_reorder_media_categories() {
            if ( ! check_ajax_referer( 'foogallery-assign-media-categories', 'nonce', false ) ) {
                wp_send_json_error( array( 'message' => __( 'Security check failed.', 'foogallery' ) ), 403 );
            }

            if ( ! taxonomy_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY ) ) {
                wp_send_json_error( array( 'message' => __( 'Folders are unavailable.', 'foogallery' ) ), 400 );
            }

            $taxonomy = get_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
            if ( ! $taxonomy ) {
                wp_send_json_error( array( 'message' => __( 'Folders are unavailable.', 'foogallery' ) ), 400 );
            }

            if ( ! current_user_can( $taxonomy->cap->assign_terms ) ) {
                wp_send_json_error( array( 'message' => __( 'You cannot reorder folders.', 'foogallery' ) ), 403 );
            }

            $parent_id   = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $ordered_ids = isset( $_POST['ordered_ids'] ) ? (array) $_POST['ordered_ids'] : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $ordered_ids = array_filter( array_map( 'absint', $ordered_ids ) );

            if ( empty( $ordered_ids ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid order.', 'foogallery' ) ), 400 );
            }

            $updated = 0;
            foreach ( $ordered_ids as $index => $term_id ) {
                $term = get_term( $term_id, FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY );
                if ( ! $term || is_wp_error( $term ) || (int) $term->parent !== $parent_id ) {
                    continue;
                }
                if ( update_term_meta( $term_id, '_foogallery_folder_order', $index ) ) {
                    $updated++;
                }
            }

            wp_send_json_success( array( 'updated' => $updated ) );
        }
    }
}
