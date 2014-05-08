<?php
/*
 * FooGallery Admin Columns class
 */

if ( ! class_exists( 'FooGallery_Admin_Columns' ) ) {

	class FooGallery_Admin_Columns {

		function __construct() {
			//add_filter( 'manage_upload_columns', array($this, 'setup_media_columns') );
			//add_action( 'manage_media_custom_column', array($this, 'media_columns_content'), 10, 2 );
			add_filter( 'manage_edit-' . FOOGALLERY_CPT_GALLERY . '_columns', array(
				$this,
				'gallery_custom_columns'
			) );
			add_action( 'manage_posts_custom_column', array( $this, 'gallery_custom_column_content' ) );
		}

		function setup_media_columns( $columns ) {
			$columns['_galleries'] = __( 'Galleries', 'foogallery' );

			return $columns;
		}

		function media_columns_content( $column_name, $post_id ) {

		}

		function gallery_custom_columns( $columns ) {
			return array_slice( $columns, 0, 1, true ) +
			       array( 'icon' => '' ) +
			       array_slice( $columns, 1, null, true ) +
			       array(
				       FOOGALLERY_CPT_GALLERY . '_count' => __( 'Media', 'foogallery' ),
				       FOOGALLERY_CPT_GALLERY . '_shortcode' => __( 'Shortcode', 'foogallery' )
			       );
		}

		function gallery_custom_column_content( $column ) {
			global $post;

			switch ( $column ) {
				case FOOGALLERY_CPT_GALLERY . '_count':
					$gallery = FooGallery::get( $post );
					$count = sizeof( $gallery->attachments() );
					switch ($count) {
						case 0:
							_e( 'No images yet!', 'foogallery' );
							break;
						case 1:
							_e( '1 image', 'foogallery' );
							break;
						default:
							echo sprintf( __( '%s images', 'foogallery' ), $count );
							break;
					}
					break;
				case FOOGALLERY_CPT_GALLERY . '_shortcode':
					echo '<code>' . foogallery_build_gallery_shortcode( $post->ID ) . '</code>';
					break;
				case 'icon':
					$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
					if ( $post_thumbnail_id && $thumb = wp_get_attachment_image( $post_thumbnail_id, array(80, 60), true ) ) {
						echo $thumb;
					}
					break;
			}
		}
	}
}