<?php
/**
 * Adds support for All In One SEO Sitemaps
 *
 * Date: 02/08/2020
 */
if ( ! class_exists( 'FooGallery_All_In_One_Seo_Sitemap_Support' ) ) {

	class FooGallery_All_In_One_Seo_Sitemap_Support {

		function __construct() {
			//version 4+
			add_filter( 'aioseo_sitemap_posts', array( $this, 'add_images_to_sitemap' ), 10, 2 );
		}

		/**
		 * Add sitemap entries for AIOSEO v4+
		 *
		 * @param $entries
		 * @param $post_type
		 *
		 * @return mixed
		 */
		function add_images_to_sitemap( $entries, $post_type ) {
			if ( is_array( $entries ) ) {
				foreach ( $entries as &$entry ) {
					$post_permalink = $entry['loc'];
					$post_id = url_to_postid( $post_permalink );
					$images = isset( $entry['images'] ) ? $entry['images'] : array();
					if ( $post_id > 0 ) {
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
									$images[] = (object) array(
										'image:loc'     => $attachment->url,
										'image:caption' => $attachment->alt,
										'image:title'   => $attachment->caption,
									);
								}
							}

							$entry['images'] = $images;
						}
					}
				}
			}

			return $entries;
		}
	}
}
