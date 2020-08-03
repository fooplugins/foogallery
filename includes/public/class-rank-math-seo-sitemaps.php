<?php
/**
 * Adds support for Rank Math SEO Sitemaps
 *
 * Created by Rank Math.
 * Date: 27/09/2019
 */
if ( ! class_exists( 'FooGallery_RankMath_Seo_Sitemap_Support' ) ) {

	class FooGallery_RankMath_Seo_Sitemap_Support {

		function __construct() {
			add_filter( 'rank_math/sitemap/urlimages', array( $this, 'add_images_to_sitemap' ), 10, 2 );
		}

		function add_images_to_sitemap( $images, $post_id ) {
			//check the content for $post_id contains a foogallery shortcode

			//get all the foogalleries used in the posts
			$galleries = get_post_meta( $post_id, FOOGALLERY_META_POST_USAGE );
			if ( is_array( $galleries ) ) {
				foreach ( $galleries as $gallery_id ) {

					//load each gallery
					$gallery = FooGallery::get_by_id( $gallery_id );

					if ( false === $gallery ) {
						continue;
					}

					//add each image to the sitemap image array
					foreach ( $gallery->attachments() as $attachment ) {
						$image    = array(
							'src'   => $attachment->url,
							'title' => $attachment->caption,
							'alt'   => $attachment->alt
						);
						$images[] = $image;
					}
				}
			}

			return $images;
		}
	}
}
