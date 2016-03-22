<?php
/**
 * Adds support for Yoast SEO Sitemaps
 *  - so that images in a FooGallery are added to the sitemap
 *
 * Created by brad.
 * Date: 21/12/2015
 */
if ( ! class_exists( 'FooGallery_Yoast_Seo_Sitemap_Support' ) ) {

	class FooGallery_Yoast_Seo_Sitemap_Support {

		function __construct() {
			add_filter( 'wpseo_sitemap_urlimages', array( $this, 'add_images_to_sitemap' ), 10, 2 );
		}

		function add_images_to_sitemap( $images, $post_id ) {
			//check the content for $post_id contains a foogallery shortcode
			$post = get_post( $post_id );

			//get all the foogallery shortcodes in the post
			$gallery_shortcodes = foogallery_extract_gallery_shortcodes( $post->post_content );

			foreach ( $gallery_shortcodes as $gallery_id => $shortcode ) {

				//load each gallery
				$gallery = FooGallery::get_by_id( $gallery_id );

				//add each image to the sitemap image array
				foreach ( $gallery->attachments() as $attachment ) {
					$image = array(
						'src'   => $attachment->url,
						'title' => $attachment->caption,
						'alt'   => $attachment->alt
					);
					$images[] = $image;
				}
			}

			return $images;
		}
	}
}