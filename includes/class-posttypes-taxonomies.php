<?php
/*
 * FooGallery Custom Post Types and Custom Taxonomy Registration class
 */

if ( !class_exists( 'FooGallery_PostTypes_Taxonomies' ) ) {

	class FooGallery_PostTypes_Taxonomies {

		function __construct() {
			add_action( 'init', array($this, 'register') );
		}

		function register() {

			$rewrite = $public = $show_in_menu = false;

			if (foogallery_permalinks_enabled()) {
				$public = true;
				$rewrite = array(
					'slug' => foogallery_permalink()
				);
			}

			if ( !foogallery_use_media_menu() ) {
				$show_in_menu = true;
			}

			register_post_type(FOOGALLERY_CPT_GALLERY, array(
				'labels' => array(
					'name' => __('Galleries', 'foogallery'),
					'singular_name' => __('Gallery', 'foogallery'),
					'add_new' => __('Add Gallery', 'foogallery'),
					'add_new_item' => __('Add New Gallery', 'foogallery'),
					'edit_item' => __('Edit Gallery', 'foogallery'),
					'new_item' => __('New Gallery', 'foogallery'),
					'view_item' => __('View Gallery', 'foogallery'),
					'search_items' => __('Search Galleries', 'foogallery'),
					'not_found' => __('No Galleries found', 'foogallery'),
					'not_found_in_trash' => __('No Galleries found in Trash', 'foogallery'),
					'menu_name' => __('FooGallery', 'foogallery'),
					'all_items' => __('Galleries', 'foogallery' )
				),
				'hierarchical' => false,
				'public' => $public,
				'rewrite' => $rewrite,
				'show_ui' => true,
				'show_in_menu' => $show_in_menu,
				'menu_position' => 30,
				'menu_icon' => 'dashicons-format-gallery',
				'supports' => array('title', 'thumbnail')
			));

//			$labels = array(
//				'name'              => __( 'Albums', 'foogallery' ),
//				'singular_name'     => __( 'Album', 'foogallery' ),
//				'search_items'      => __( 'Search Albums', 'foogallery' ),
//				'all_items'         => __( 'All Albums', 'foogallery' ),
//				'parent_item'       => __( 'Parent Album', 'foogallery' ),
//				'parent_item_colon' => __( 'Parent Album:', 'foogallery' ),
//				'edit_item'         => __( 'Edit Album', 'foogallery' ),
//				'update_item'       => __( 'Update Album', 'foogallery' ),
//				'add_new_item'      => __( 'Add New Album', 'foogallery' ),
//				'new_item_name'     => __( 'New Album Name', 'foogallery' ),
//				'menu_name'         => __( 'Albums', 'foogallery' ),
//			);
//
//			$args = array(
//				'hierarchical'      => true,
//				'labels'            => $labels,
//				'show_ui'           => true,
//				'show_admin_column' => true,
//				'query_var'         => true,
//				'rewrite'           => array( 'slug' => 'album' ),
//			);
//
//			register_taxonomy( FOOGALLERY_TAX_ALBUM, array( FOOGALLERY_CPT_GALLERY ), $args );
		}
	}
}