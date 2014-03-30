<?php
/*
 * FooGallery Admin Columns class
 */

if (!class_exists('FooGallery_Admin_Columns')) {

	class FooGallery_Admin_Columns {
		function __construct() {
			add_filter( 'manage_upload_columns', array($this, 'setup_media_columns') );
			add_action( 'manage_media_custom_column', array($this, 'media_columns_content'), 10, 2 );
			add_filter( 'manage_edit-' . FOOGALLERY_CPT_GALLERY . '_columns', array($this, 'gallery_custom_columns') );
			add_action( 'manage_posts_custom_column', array($this, 'gallery_custom_column_content' ));
		}

		function setup_media_columns( $columns ) {
			$columns['_galleries'] = __('Galleries', 'foogallery');
			return $columns;
		}

		function media_columns_content( $column_name, $post_id ) {

		}

		function gallery_custom_columns($columns) {
			$new_columns = array(
				FOOGALLERY_CPT_GALLERY . '_count' => __('Images', 'foogallery')
			);
			return array_merge($columns, $new_columns);
		}

		function gallery_custom_column_content($column) {
			global $post;

			if ( $column == FOOGALLERY_CPT_GALLERY . '_count' ) {
				$gallery = FooGallery::get($post);
				echo sizeof( $gallery->attachments(false) );
			}
		}
	}
}