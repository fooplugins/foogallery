<?php
/**
 * FooGallery_AdminBar Class
 * allows for really easy gallery editing from the front-end (when logged in)
 * Date: 30/08/2015
 */

if ( !class_exists( 'FooGallery_AdminBar' ) ) {

	class FooGallery_AdminBar {

		function __construct() {
			// adds the edit galleries menu to the admin bar
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 101 ); // 101 determines the position
		}

		public function admin_bar_menu($wp_admin_bar) {
			global $wp_the_query;

			if ( !is_admin() ) {
				$current_object = $wp_the_query->get_queried_object();

				if ( empty( $current_object ) )
					return;

				if ( ! empty( $current_object->post_type )
				     && current_user_can( 'edit_post', $current_object->ID ) ) {

					$gallery_posts = foogallery_get_galleries_attached_to_post( $current_object->ID );

					if ( !empty( $gallery_posts ) ) {
						$wp_admin_bar->add_menu(array(
							'id'     => 'foogallery',
							'title'  => __( 'Edit Galleries', 'foogallery' )
						));

						foreach ( $gallery_posts as $gallery ) {
							$wp_admin_bar->add_menu( array(
								'parent' => 'foogallery',
								'id'     => $gallery->ID,
								'title'  => esc_html( $gallery->post_title ),
								'href'   => get_edit_post_link( $gallery->ID ),
							) );
						}
					}
				}
			}
		}
	}
}