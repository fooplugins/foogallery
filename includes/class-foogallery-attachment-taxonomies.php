<?php
if ( ! class_exists( 'FooGallery_Attachment_Taxonomies' ) ) {

    define( 'FOOGALLERY_ATTACHMENT_TAXONOMY_TAG', 'foogallery_attachment_tag' );
    define( 'FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION', 'foogallery_attachment_collection' );

    class FooGallery_Attachment_Taxonomies {

        /**
         * Class Constructor
         */function __construct() {
            add_action( 'init', array( $this, 'add_taxonomies' ) );

            if ( is_admin() ) {
                add_action( 'admin_menu', array( $this, 'add_menu_items' ), 1 );
                add_filter( 'parent_file', array( $this, 'set_current_menu' ) );
                add_filter( 'manage_media_columns', array( $this, 'change_attachment_column_names' ) );
                add_filter( 'manage_edit-foogallery_attachment_tag_columns', array( $this, 'clean_column_names' ), 999 );
                add_filter( 'manage_edit-foogallery_attachment_collection_columns', array( $this, 'clean_column_names' ), 999 );
                add_filter( 'foogallery_attachment_add_fields', array( $this, 'remove_taxonomy_fields') );
                add_action( 'restrict_manage_posts', array( $this, 'add_collection_filter' ) );

                add_filter( 'foogallery_attachment_custom_fields', array( $this, 'add_media_tag_field' ) );
                add_filter( 'foogallery_attachment_field_taxonomy_tag', array( $this, 'customize_media_tag_field'), 10, 2 );
                add_filter( 'foogallery_attachment_save_field_taxonomy_tag', array( $this, 'save_media_tag_field' ), 10, 4 );



            }
        }

        function change_attachment_column_names( $columns ) {

             if ( array_key_exists( 'taxonomy-foogallery_attachment_collection', $columns ) ) {
                 $columns['taxonomy-foogallery_attachment_collection'] = __('Collections', 'foogallery');
             }

             return $columns;
        }

        /**
         * Clean up the taxonomy columns
         *
         * @param $columns
         * @return mixed
         */
        function clean_column_names( $columns ) {

             //cleanup wpseo columns!
             if ( array_key_exists( 'wpseo_score', $columns ) ) {
                 unset( $columns['wpseo_score'] );
             }
            if ( array_key_exists( 'wpseo_score_readability', $columns ) ) {
                unset( $columns['wpseo_score_readability'] );
            }
             return $columns;
        }

        /**
         * Add the menu items under the FooGalleru main menu
         */
        function add_menu_items() {
            foogallery_add_submenu_page(
                __( 'Media Tags', 'foogallery' ),
                'manage_options',
                'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '&post_type=' . FOOGALLERY_CPT_GALLERY,
                null
            );

            foogallery_add_submenu_page(
                __( 'Media Collections', 'foogallery' ),
                'manage_options',
                'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION . '&post_type=' . FOOGALLERY_CPT_GALLERY,
                null
            );
        }

        /**
         * Make sure the tqaxonomy menu items are highlighted
         * @param $parent_file
         * @return mixed
         */
        function set_current_menu( $parent_file ) {
            global $submenu_file, $current_screen, $pagenow;

            if ( $current_screen->post_type == FOOGALLERY_CPT_GALLERY ) {

                if ( 'edit-foogallery_attachment_tag' === $current_screen->id ) {
                    $submenu_file = 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '&post_type=' . FOOGALLERY_CPT_GALLERY;
                }

                if ( 'edit-foogallery_attachment_collection' === $current_screen->id ) {
                    $submenu_file = 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION . '&post_type=' . FOOGALLERY_CPT_GALLERY;
                }
            }
            return $parent_file;
        }

        /**
         * Register the taxonomies for attachments
         */
        function add_taxonomies() {

            $tag_args = array(
                'labels'            => array(
                    'name'              => __( 'Media Tags', 'foogallery' ),
                    'singular_name'     => __( 'Tag', 'foogallery' ),
                    'search_items'      => __( 'Search Tags', 'foogallery' ),
                    'all_items'         => __( 'All Tags', 'foogallery' ),
                    'parent_item'       => __( 'Parent Tag', 'foogallery' ),
                    'parent_item_colon' => __( 'Parent Tag:', 'foogallery' ),
                    'edit_item'         => __( 'Edit Tag', 'foogallery' ),
                    'update_item'       => __( 'Update Tag', 'foogallery' ),
                    'add_new_item'      => __( 'Add New Tag', 'foogallery' ),
                    'new_item_name'     => __( 'New Tag Name', 'foogallery' ),
                    'menu_name'         => __( 'Media Tags', 'foogallery' )
                ),
                'hierarchical'      => false,
                'query_var'         => true,
                'rewrite'           => false,
                'show_admin_column' => false,
                'show_in_menu'      => false,
                'update_count_callback' => '_update_post_term_count' //array( $this, 'update_taxonomy_tag_count' )
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, 'attachment', $tag_args );

            $collection_args = array(
                'labels'            => array(
                    'name'              => __( 'Media Collections', 'foogallery' ),
                    'singular_name'     => __( 'Collection', 'foogallery' ),
                    'search_items'      => __( 'Search Collections', 'foogallery' ),
                    'all_items'         => __( 'All Collections', 'foogallery' ),
                    'parent_item'       => __( 'Parent Collection', 'foogallery' ),
                    'parent_item_colon' => __( 'Parent Collection:', 'foogallery' ),
                    'edit_item'         => __( 'Edit Collection', 'foogallery' ),
                    'update_item'       => __( 'Update Collection', 'foogallery' ),
                    'add_new_item'      => __( 'Add New Collection', 'foogallery' ),
                    'new_item_name'     => __( 'New Collection Name', 'foogallery' ),
                    'menu_name'         => __( 'Media Collections', 'foogallery' )
                ),
                'hierarchical'      => true,
                'query_var'         => true,
                'rewrite'           => false,
                'show_admin_column' => true,
                'show_in_menu'      => false,
                'update_count_callback' => '_update_post_term_count'
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, 'attachment', $collection_args );
        }

        /**
         * Function for updating the 'tag' taxonomy count.  What this does is update the count of a specific term
         * by the number of attachments that have been given the term.
         * We're just updating the count with no specifics for simplicity.
         *
         * See the _update_post_term_count() function in WordPress for more info.
         *
         * @param array $terms List of Term taxonomy IDs
         * @param object $taxonomy Current taxonomy object of terms
         */
        function update_taxonomy_tag_count( $terms, $taxonomy ) {
            global $wpdb;

            foreach ( (array) $terms as $term ) {

                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );

                do_action( 'edit_term_taxonomy', $term, $taxonomy );
                $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
                do_action( 'edited_term_taxonomy', $term, $taxonomy );
            }
        }

        /**
         * Add a new tag field to the attachments
         *
         * @param $fields array All fields that will be added to the media modal
         *
         * @return mixed
         */
        function add_media_tag_field( $fields ) {
            $args = array(
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false
            );

            //pull all terms
            $terms = get_terms( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $args );

            $media_tags = array();

            foreach ( $terms as $term ) {
                $media_tags[ $term->term_id ] = $term->name;
            }

            $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] = array(
                'label'   => __( 'Tags', 'foogallery' ),
                'input'   => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
                'helps'   => __( 'Tag your attachments', 'foogallery' ),
                'options' => $media_tags,
                'exclusions'  => array()
            );

            return $fields;
        }

        /**
         * Remove the automatically added attachments fields
         * @param $fields
         *
         * @return mixed
         */
        function remove_taxonomy_fields( $fields ) {
            if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $fields ) ) {
                unset( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] );
            }

            if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION, $fields ) ) {
                unset( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION] );
            }

            return $fields;
        }

        /**
         * Customize the media tag field to make sure we output a checkboxlist
         * @param $field_values
         *
         * @return mixed
         */
        function customize_media_tag_field( $field_values, $post_id ) {

            $media_tags = array();

            //get the terms linked to the attachment
            $terms = get_the_terms( $post_id, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );
            if ( $terms && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    $media_tags[ $term->term_id ] = $term->name;
                }
            }

            //set to html
            $field_values['input'] = 'html';

            $html = '';
            $i = 0;

            if ( ! empty( $field_values['options'] ) ) {

                foreach ( $field_values['options'] as $k => $v ) {
                    if ( array_key_exists( $k, $media_tags ) ) {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '';
                    }

                    $html .= '<input' . $checked . ' value="' . $k . '" type="checkbox" name="attachments[' . $post_id . '][' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '][' . $k . ']" id="' . sanitize_key( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '_' . $post_id . '_' . $i ) . '" /> <label for="' . sanitize_key( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '_' . $post_id . '_' . $i ) . '">' . $v . '</label> ';
                    $i++;
                }
            }

            if ( 0 === $i ) {
                $html .= __( 'No Tags Available!', 'foogallery' );
            }

            $html .= '<style>.compat-field-foogallery_media_tags .field input {margin-right: 0px;} .compat-field-foogallery_media_tags .field label {vertical-align: bottom; margin-right: 10px;}</style>';

            $html .= '<br /><a target="_blank" href="' . admin_url( 'edit-tags.php?taxonomy=' . FOOGALLERY_ATTACHMENT_TAXONOMY_TAG . '&post_type=attachment' ) . '">' . __( 'Manage Tags', 'foogallery' ) . '</a>';

            $field_values['value'] = '';
            $field_values['html'] = $html;

            return $field_values;
        }

        /**
         * Save the tags for the attachment
         *
         * @param $field
         * @param $values
         * @param $post
         * @param $attachment
         */
        function save_media_tag_field($field, $values, $post, $attachment) {
            $post_id = $post['ID'];

            //first clear any tags for the post
            wp_delete_object_term_relationships( $post_id, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );

            $tag_ids = $attachment[ $field ];

            if ( !empty( $tag_ids ) ) {
                //clean tag ids
                $tag_ids = array_keys( $tag_ids );
                $tag_ids = array_map( 'intval', $tag_ids );
                $tag_ids = array_unique( $tag_ids );

                $term_taxonomy_ids = wp_set_object_terms( $post_id, $tag_ids, FOOGALLERY_ATTACHMENT_TAXONOMY_TAG );

                if ( is_wp_error( $term_taxonomy_ids ) ) {
                    // There was an error somewhere and the terms couldn't be set.
                    $post['errors'][ $field ]['errors'][] = __( 'Error saving the tags for the attachment!', 'foogallery' );
                }
            }
        }


        /***
         *
         * Add a tag filter to the attachments listing page
         */
        function add_collection_filter() {
            global $pagenow;
            if ( 'upload.php' == $pagenow ) {

                $dropdown_options = array(
                    'taxonomy'        => FOOGALLERY_ATTACHMENT_TAXONOMY_COLLECTION,
                    'show_option_all' => __( 'All Collections' ),
                    'hide_empty'      => false,
                    'hierarchical'    => true,
                    'orderby'         => 'name',
                    'show_count'      => true,
                    'walker'          => new foogallery_walker_category_dropdown(),
                    'value'           => 'slug'
                );

                wp_dropdown_categories( $dropdown_options );
            }
        }
    }
}

/** Custom walker for wp_dropdown_categories, based on https://gist.github.com/stephenh1988/2902509 */
class foogallery_walker_category_dropdown extends Walker_CategoryDropdown{

    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $pad = str_repeat( '&nbsp;', $depth * 3 );
        $cat_name = apply_filters( 'list_cats', $category->name, $category );

        if( ! isset( $args['value'] ) ) {
            $args['value'] = ( $category->taxonomy != 'category' ? 'slug' : 'id' );
        }

        $value = ( $args['value']=='slug' ? $category->slug : $category->term_id );
        if ( 0 == $args['selected'] && isset( $_GET['category_media'] ) && '' != $_GET['category_media'] ) {
            $args['selected'] = $_GET['category_media'];
        }

        $output .= '<option class="level-' . $depth . '" value="' . $value . '"';
        if ( $value === (string) $args['selected'] ) {
            $output .= ' selected="selected"';
        }
        $output .= '>';
        $output .= $pad . $cat_name;
        if ( $args['show_count'] )
            $output .= '&nbsp;&nbsp;(' . $category->count . ')';

        $output .= "</option>\n";
    }
}