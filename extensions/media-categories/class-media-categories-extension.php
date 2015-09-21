<?php
if ( ! class_exists( 'FooGallery_Media_Categories_Extension' ) ) {

	define( 'FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY', 'foogallery_media_category' );
	define( 'FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG', 'foogallery_media_categories' );
	define( 'FOOGALLERY_MEDIA_CATEGORIES_INPUT_TYPE', 'category_checkboxlist' );

	class FooGallery_Media_Categories_Extension {

		/**
		 * Class Constructor
		 */
		function __construct() {
			add_action( 'init', array( $this, 'add_categories_to_attachments' ) );
			if ( is_admin() ) {
				add_filter( 'foogallery_attachment_custom_fields', array( $this, 'add_media_category_field' ) );
				add_filter( 'foogallery_attachment_add_fields', array( $this, 'remove_taxonomy_media_category_field') );
				add_filter( 'foogallery_attachment_field_' . FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG, array( $this, 'customize_media_category_field'), 10, 2 );
				add_filter( 'foogallery_attachment_save_field_' . FOOGALLERY_MEDIA_CATEGORIES_INPUT_TYPE, array( $this, 'save_media_category_field' ), 10, 4 );
				add_action( 'restrict_manage_posts', array( $this, 'add_category_filter' ) );
			}
		}

		/**
		 * Register the category taxonomy for attachments
		 */
		function add_categories_to_attachments() {
			$labels = array(
				'name'              => __( 'Categories', 'foogallery' ),
				'singular_name'     => __( 'Category', 'foogallery' ),
				'search_items'      => __( 'Search Categories', 'foogallery' ),
				'all_items'         => __( 'All Categories', 'foogallery' ),
				'parent_item'       => __( 'Parent Category', 'foogallery' ),
				'parent_item_colon' => __( 'Parent Category:', 'foogallery' ),
				'edit_item'         => __( 'Edit Category', 'foogallery' ),
				'update_item'       => __( 'Update Category', 'foogallery' ),
				'add_new_item'      => __( 'Add New Category', 'foogallery' ),
				'new_item_name'     => __( 'New Category Name', 'foogallery' ),
				'menu_name'         => __( 'Categories', 'foogallery' )
			);

			$args = array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'query_var'         => true,
				'rewrite'           => false,
				'show_admin_column' => 'true'
			);

			register_taxonomy( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY, 'attachment', $args );
		}

		/**
		 * Add a new category field to the attachments
		 *
		 * @param $fields array All fields that will be added to the media modal
		 *
		 * @return mixed
		 */
		function add_media_category_field( $fields ) {
			$args = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false
			);

			//pull all terms
			$terms = get_terms( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY, $args );

			$media_categories = array();

			foreach ( $terms as $term ) {
				$media_categories[ $term->term_id ] = $term->name;
			}

			$fields[FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG] = array(
				'label'   => __( 'Categories', 'foogallery' ),
				'input'   => FOOGALLERY_MEDIA_CATEGORIES_INPUT_TYPE,
				'helps'   => __( 'Categorize your attachments', 'foogallery' ),
				'options' => $media_categories,
				'exclusions'  => array()
			);

			return $fields;
		}

		/**
		 * Remove the automatically added media category field
		 * @param $fields
		 *
		 * @return mixed
		 */
		function remove_taxonomy_media_category_field( $fields ) {
			if ( array_key_exists( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY, $fields ) ) {
				unset( $fields[FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY] );
			}

			return $fields;
		}

		/**
		 * Customize the media category field to make sure we output a checkboxlist
		 * @param $field_values
		 *
		 * @return mixed
		 */
		function customize_media_category_field( $field_values, $post_id ) {

			$media_categories = array();

			//get the terms linked to the attachment
			$terms = get_the_terms( $post_id, FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$media_categories[ $term->term_id ] = $term->name;
				}
			}

			//set to html
			$field_values['input'] = 'html';

			$html = '';
			$i = 0;

			if ( ! empty( $field_values['options'] ) ) {

				foreach ( $field_values['options'] as $k => $v ) {
					if ( array_key_exists( $k, $media_categories ) ) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}

					$html .= '<input' . $checked . ' value="' . $k . '" type="checkbox" name="attachments[' . $post_id . '][' . FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG . '][' . $k . ']" id="' . sanitize_key( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG . '_' . $post_id . '_' . $i ) . '" /> <label for="' . sanitize_key( FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_SLUG . '_' . $post_id . '_' . $i ) . '">' . $v . '</label> ';
					$i++;
				}
			}

			if ( 0 === $i ) {
				$html .= __( 'No Categories Available!', 'foogallery' );
			}

			$html .= '<style>.compat-field-foogallery_media_categories .field input {margin-right: 0px;} .compat-field-foogallery_media_categories .field label {vertical-align: bottom; margin-right: 10px;}</style>';

			$html .= '<br /><a target="_blank" href="' . admin_url( 'edit-tags.php?taxonomy=' . FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY . '&post_type=attachment' ) . '">' . __( 'Manage Categories', 'foogallery' ) . '</a>';

			$field_values['value'] = '';
			$field_values['html'] = $html;

			return $field_values;
		}

		/**
		 * Save the categories for the attachment
		 *
		 * @param $field
		 * @param $values
		 * @param $post
		 * @param $attachment
		 */
		function save_media_category_field($field, $values, $post, $attachment) {
			$post_id = $post['ID'];

			//first clear any categories for the post
			wp_delete_object_term_relationships( $post_id, FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY );

			$category_ids = $attachment[ $field ];

			if ( !empty( $category_ids ) ) {
				//clean category ids
				$category_ids = array_keys( $category_ids );
				$category_ids = array_map( 'intval', $category_ids );
				$category_ids = array_unique( $category_ids );

				$term_taxonomy_ids = wp_set_object_terms( $post_id, $category_ids, FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY );

				if ( is_wp_error( $term_taxonomy_ids ) ) {
					// There was an error somewhere and the terms couldn't be set.
					$post['errors'][ $field ]['errors'][] = __( 'Error saving the categories for the attachment!', 'foogallery' );
				}
			}
		}


		/***
		 *
		 * Add a category filter
		 */
		function add_category_filter() {
			global $pagenow;
			if ( 'upload.php' == $pagenow ) {

				$dropdown_options = array(
					'taxonomy'        => FOOGALLERY_MEDIA_CATEGORIES_EXTENSION_TAXONOMY,
					'show_option_all' => __( 'View all categories' ),
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