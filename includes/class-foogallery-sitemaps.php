<?php
/**
 * Class for managing sitemaps within FooGallery
 */

if ( ! class_exists( 'FooGallery_Sitemaps' ) ) {
	class FooGallery_Sitemaps {
		public function __construct() {
			//add_action('init', array( $this, 'register_sitemaps' ) );
			//add_filter( 'wp_sitemaps_posts_entry', array( $this, 'add_images_to_sitemap_entry'), 10, 3 );
		}

		public function register_sitemaps() {
			//check if WordPress sitemaps are supported
			if ( function_exists( 'wp_register_sitemap_provider' ) && class_exists( 'WP_Sitemaps_Provider' ) ) {
				$provider = new FooGallery_Sitemaps_Provider();
				wp_register_sitemap_provider( 'foogallery', $provider );
			}
		}

		public function add_images_to_sitemap_entry( $sitemap_entry, $post, $post_type ) {
			$galleries = get_post_meta( $post->ID, FOOGALLERY_META_POST_USAGE );
			foreach ( $galleries as $gallery_id ) {

				//load each gallery
				$gallery = FooGallery::get_by_id( $gallery_id );

				if ( false === $gallery ) continue;

				//add each image to the sitemap image array
				foreach ( $gallery->attachments() as $attachment ) {
					$sitemap_entry['image:image'][] = array(
						'image:loc' => $attachment->url,
						'image:title' => $attachment->caption,
						'image:caption' => $attachment->alt
					);
				}
			}

			return $sitemap_entry;
		}
	}
}

if ( ! class_exists( 'FooGallery_Sitemaps_Provider' ) ) {
	class FooGallery_Sitemaps_Provider extends WP_Sitemaps_Provider {

		/**
		 * WP_Sitemaps_My_Plugin constructor.
		 *
		 * @since 5.5.0 (use your plugin version)
		 */
		public function __construct() {
			$this->name        = 'foogallery';
			$this->object_type = 'image';
		}

		public function get_url_list( $page_num, $object_subtype = '' ) {
			$url_list = array();

			//get all galleries
			$galleries = foogallery_get_all_galleries();

			//find all pages or posts that have galleries
			foreach ( $galleries as $gallery ) {
				$gallery_usages = $gallery->find_usages();

				foreach ( $gallery_usages as $post ) {
					$sitemap_entry = array(
						'loc' => get_permalink( $post ),
					);

					$url_list[] = $sitemap_entry;
				}
			}

			return $url_list;
		}

		/**
		 * Gets the max number of pages available for the object type.
		 *
		 * @since 5.5.0 (use your plugin version)
		 *
		 * @see WP_Sitemaps_Provider::max_num_pages
		 *
		 * @param string $object_subtype Optional. Default empty.
		 *
		 * @return int Total page count.
		 */
		public function get_max_num_pages( $object_subtype = '' ) {
			// again, use a function from your own plugin to fetch this data.
			//$pages = plugin_prefix_get_my_pagination();

			//return count( $pages );

			return 0;
		}
	}
}