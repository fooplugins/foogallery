<?php
if ( ! class_exists( 'FooGallery_Attachment_Taxonomies' ) ) {

    define( 'FOOGALLERY_ATTACHMENT_TAXONOMY_TAG', 'foogallery_attachment_tag' );

    class FooGallery_Attachment_Taxonomies {

        /**
         * Class Constructor
         */function __construct() {
            add_action( 'init', array( $this, 'add_tags_to_attachments' ) );
            if ( is_admin() ) {
                add_filter( 'foogallery_attachment_custom_fields', array( $this, 'add_media_tag_field' ) );
                add_filter( 'foogallery_attachment_add_fields', array( $this, 'remove_taxonomy_media_tag_field') );
                add_filter( 'foogallery_attachment_field_taxonomy_tag', array( $this, 'customize_media_tag_field'), 10, 2 );
                add_filter( 'foogallery_attachment_save_field_taxonomy_tag', array( $this, 'save_media_tag_field' ), 10, 4 );
                add_action( 'restrict_manage_posts', array( $this, 'add_tag_filter' ) );
            }
        }

        /**
         * Register the tag taxonomy for attachments
         */
        function add_tags_to_attachments() {
            $labels = array(
                'name'              => __( 'Tags', 'foogallery' ),
                'singular_name'     => __( 'Tag', 'foogallery' ),
                'search_items'      => __( 'Search Tags', 'foogallery' ),
                'all_items'         => __( 'All Tags', 'foogallery' ),
                'parent_item'       => __( 'Parent Tag', 'foogallery' ),
                'parent_item_colon' => __( 'Parent Tag:', 'foogallery' ),
                'edit_item'         => __( 'Edit Tag', 'foogallery' ),
                'update_item'       => __( 'Update Tag', 'foogallery' ),
                'add_new_item'      => __( 'Add New Tag', 'foogallery' ),
                'new_item_name'     => __( 'New Tag Name', 'foogallery' ),
                'menu_name'         => __( 'Tags', 'foogallery' )
            );

            $args = array(
                'labels'            => $labels,
                'hierarchical'      => false,
                'query_var'         => true,
                'rewrite'           => false,
                'show_admin_column' => false
            );

            register_taxonomy( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, 'attachment', $args );
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
         * Remove the automatically added media tag field
         * @param $fields
         *
         * @return mixed
         */
        function remove_taxonomy_media_tag_field( $fields ) {
            if ( array_key_exists( FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, $fields ) ) {
                unset( $fields[FOOGALLERY_ATTACHMENT_TAXONOMY_TAG] );
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

                    $html .= '<input' . $checked . ' value="' . $k . '" type="checkbox" name="attachments[' . $post_id . '][' . FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG . '][' . $k . ']" id="' . sanitize_key( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG . '_' . $post_id . '_' . $i ) . '" /> <label for="' . sanitize_key( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG . '_' . $post_id . '_' . $i ) . '">' . $v . '</label> ';
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
         * Add a tag filter
         */
        function add_tag_filter() {
            global $pagenow;
            if ( 'upload.php' == $pagenow ) {

                $dropdown_options = array(
                    'taxonomy'        => FOOGALLERY_ATTACHMENT_TAXONOMY_TAG,
                    'show_option_all' => __( 'View all tags' ),
                    'hide_empty'      => false,
                    'hierarchical'    => true,
                    'orderby'         => 'name',
                    'show_count'      => true,
                    //'walker'          => new foogallery_walker_category_dropdown(),
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