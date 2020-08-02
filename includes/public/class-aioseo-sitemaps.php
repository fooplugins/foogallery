<?php
/**
 * Adds support for All In One SEO Sitemaps
 *
 * Date: 02/08/2020
 */
if ( ! class_exists( 'FooGallery_All_In_One_Seo_Sitemap_Support' ) ) {

	class FooGallery_All_In_One_Seo_Sitemap_Support {

		function __construct() {
			add_filter( 'aiosp_sitemap_prio_item_filter', array( $this, 'add_images_to_sitemap' ), 10, 3 );
		}

		function add_images_to_sitemap( $pr_info, $post, $args ) {
			//check the content for $post_id contains a foogallery shortcode

			$images = array();

			//get all the foogalleries used in the post
			$galleries = get_post_meta( $post->ID, FOOGALLERY_META_POST_USAGE );
			foreach ( $galleries as $gallery_id ) {

				//load each gallery
				$gallery = FooGallery::get_by_id( $gallery_id );

				if ( false === $gallery ) continue;

				//add each image to the sitemap image array
				foreach ( $gallery->attachments() as $attachment ) {
					$images[] = array(
						'image:loc'     => $attachment->url,
						'image:caption' => $attachment->alt,
						'image:title'   => $attachment->caption,
					);
				}
			}

			if ( count( $images ) > 0 ) {
				if ( !is_array( $pr_info['image:image'] ) ) {
					$pr_info['image:image'] = array();
				}
				$pr_info['image:image'] = array_merge( $pr_info['image:image'], $images );
			}

			return $pr_info;
		}
	}
}
