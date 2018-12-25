<?php
/**
 * Created by Brad Vincent.
 * Date: 04/03/2018
 */
if ( ! class_exists( 'FooGallery_Demo_Content_Generator' ) ) {

	class FooGallery_Demo_Content_Generator {
		function __construct() {
			//always show the menu
			add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );
			add_action( 'foogallery_extension_activated-demo-content', array( $this, 'add_menu' ) );
		}

		function add_menu() {
			foogallery_add_submenu_page( __( 'Demo Content', 'foogallery' ), 'manage_options', 'foogallery-demo-content', array(
				$this,
				'render_view',
			) );
		}

		function render_view() {
			require_once 'view-demo-content.php';
		}

		static function search( $query, $count = 20 ) {
			require_once 'includes/class-pixabay.php';
			require_once 'includes/class-lorem-ipsum.php';

			$client = new FooGallery_PixabayClient();
			$key = apply_filters( 'foogallery_pixabay_key', '1843003-12be68cf2726df47797f19cd7' );

			$results = $client->search( $key, $query, $count );

			$hits = $results->hits;

			$lorem = new FooGallery_LoremIpsum();

			$results = array();

			//get random images
			$random_image_indexes = array_rand($hits, $count);
			foreach ( $random_image_indexes as $image_index ) {
				$image = $hits[$image_index];
				if ( array_key_exists( 'largeImageURL', $image ) ) {
					$url = $image->largeImageURL;
				} else {
					$url = $image->webformatURL;
				}

				$thumb = $image->previewURL;

				$title = 'FooGallery Demo Image ' . $image->id;
				$caption_title = self::build_caption_title( $image->tags );
				if ( false === $caption_title ) {
					$caption_title = $lorem->words( rand(3,5) );
				}
				$caption_desc = $lorem->words( rand(8, 15) );

				$results[] = array(
					'title'		   => $title,
					'caption'      => $caption_title,
					'description'  => $caption_desc,
					'src'		   => $thumb,
					'href'		   => $url,
					'width'		   => 150,
					'height'	   => 150,
					'tags'		   => self::clean_up_tags($image->tags)
				);
			}

			return $results;
		}

		static function clean_up_tags( $tag_source ) {
			if ( empty( $tag_source ) ) {
				return '';
			}

			$tag_source = str_replace( ', ', ',', $tag_source );
			$tag_source = str_replace( ' ', ',', $tag_source );

			$tags = explode( ',', $tag_source );

			$tags = array_unique( $tags );

			$tags = array_splice( $tags, 0, 5, true );

			$tag_output = implode(',', $tags );

			return $tag_output;
		}

		static function build_caption_title( $tag_source ) {
			if ( empty( $tag_source ) ) {
				return false;
			}

			$tag_source = str_replace( ', ', ',', $tag_source );
			$tag_source = str_replace( ' ', ',', $tag_source );

			$tags = explode( ',', $tag_source );

			$tags = array_unique( $tags );

			$tags = array_splice( $tags, 0, 4, true );

			$tag_output = implode(' ', $tags );

			return ucwords( $tag_output );
		}

		static function generate( $query, $count = 20 ) {
			$images = self::search( $query, $count );

			foreach ( $images as $image ) {

				$title = foo_convert_to_key($image['caption']);
				$caption_title = $image['caption'];
				$caption_desc = $image['description'];
				$src = $image['href'];
				$tags = $image['tags'];

				// check if attachment already exists
				$attachment_args = array(
					'posts_per_page' => 1,
					'post_type'      => 'attachment',
					'name'           => $title
				);

				$attachment_check = new WP_Query( $attachment_args );

				if ( !$attachment_check->have_posts() ) {
					$attachments[] = self::create_attachment( $src, $title, $caption_title, $caption_desc, $tags );
				}

			}

			if ( !empty( $attachments ) ) {
				return foogallery_create_gallery( 'masonry', implode( ',', $attachments ) );
			}

			return false;
		}

		static function create_attachment( $image_url, $title, $caption_title, $caption_description, $tags ) {
			// Include image.php so we can call wp_generate_attachment_metadata()
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Get the contents of the picture
			$response = wp_remote_get( $image_url );
			$contents = wp_remote_retrieve_body( $response );

			// Upload and get file data
			$upload    = wp_upload_bits( basename( $image_url ), null, $contents );
			$guid      = $upload['url'];
			$file      = $upload['file'];
			$file_type = wp_check_filetype( basename( $file ), null );

			// Create attachment
			$attachment = array(
				'ID'             => 0,
				'guid'           => $guid,
				'post_title'     => $title,
				'post_excerpt'   => $caption_title,
				'post_content'   => $caption_description,
				'post_date'      => '',
				'post_mime_type' => $file_type['type'],
			);

			// Insert the attachment
			$attachment_id   = wp_insert_attachment( $attachment, $file, 0 );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			if ( defined( 'FOOGALLERY_ATTACHMENT_TAXONOMY_TAG' ) ) {
				// Save tags
				wp_set_object_terms( $attachment_id, array_map( 'trim', preg_split( '/,+/', $tags ) ), FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, false );
			}

			return $attachment_id;
		}
	}
}