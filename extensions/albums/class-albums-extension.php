<?php
if ( !class_exists( 'FooGallery_Albums_Extension' ) ) {

	define('FOOGALLERY_TAX_ALBUM', 'foogallery-album');

	class FooGallery_Albums_Extension extends FooGallery_Extension_Base {

		protected $slug = 'albums';

		function run() {
			//hook up my album stuff here!
			$this->setup_album_posttypes();
		}

		function setup_album_posttypes() {
			$labels = array(
				'name'              => __( 'Albums', 'foogallery' ),
				'singular_name'     => __( 'Album', 'foogallery' ),
				'search_items'      => __( 'Search Albums', 'foogallery' ),
				'all_items'         => __( 'All Albums', 'foogallery' ),
				'parent_item'       => __( 'Parent Album', 'foogallery' ),
				'parent_item_colon' => __( 'Parent Album:', 'foogallery' ),
				'edit_item'         => __( 'Edit Album', 'foogallery' ),
				'update_item'       => __( 'Update Album', 'foogallery' ),
				'add_new_item'      => __( 'Add New Album', 'foogallery' ),
				'new_item_name'     => __( 'New Album Name', 'foogallery' ),
				'menu_name'         => __( 'Albums', 'foogallery' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => 'album'),
			);

			register_taxonomy( FOOGALLERY_TAX_ALBUM, array(FOOGALLERY_CPT_GALLERY), $args );
		}
	}
}
