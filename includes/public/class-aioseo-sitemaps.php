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

			//pre version 4
			add_filter( 'aiosp_sitemap_prio_item_filter', array( $this, 'add_images_to_sitemap_old' ), 10, 3 );
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

		/**
		 * Add images to the sitemap for AIOSEO before version 4
		 *
		 * @param $pr_info
		 * @param $post
		 * @param $args
		 *
		 * @return mixed
		 */
		function add_images_to_sitemap_old( $pr_info, $post, $args ) {
			//check the content for $post_id contains a foogallery shortcode

			$images = array();

			//get all the foogalleries used in the post
			$galleries = get_post_meta( $post->ID, FOOGALLERY_META_POST_USAGE );
			if ( is_array( $galleries ) ) {
				foreach ( $galleries as $gallery_id ) {

					//load each gallery
					$gallery = FooGallery::get_by_id( $gallery_id );

					if ( false === $gallery ) {
						continue;
					}

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
					if ( ! is_array( $pr_info['image:image'] ) ) {
						$pr_info['image:image'] = array();
					}
					$pr_info['image:image'] = array_merge( $pr_info['image:image'], $images );
				}
			}

			return $pr_info;
		}
	}
}
