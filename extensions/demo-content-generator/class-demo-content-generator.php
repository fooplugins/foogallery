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

		static function generate( $query ) {
			require_once 'includes/class-pixabay.php';
			require_once 'includes/class-lorem-ipsum.php';
			// Include image.php so we can call wp_generate_attachment_metadata()
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$client = new FooGallery_PixabayClient();
			$key = apply_filters( 'foogallery_pixabay_key', '1843003-12be68cf2726df47797f19cd7' );

			$results = $client->search( $key, $query );

			$hits = $results->hits;

			$lorem = new FooGallery_LoremIpsum();

			//get 10 random images
			$random_image_indexes = array_rand($hits, 10);
			foreach ( $random_image_indexes as $image_index ) {
				$image = $hits[$image_index];
				if ( array_key_exists( 'largeImageURL', $image ) ) {
					$url = $image->largeImageURL;
				} else {
					$url = $image->webformatURL;
				}

				$title = 'Demo Image ' . $image->id;
				$caption_title = $lorem->words( rand(3,5) );
				$caption_desc = $lorem->words( rand(8, 15) );

				// check if attachment already exists
				$attachment_args = array(
					'posts_per_page' => 1,
					'post_type'      => 'attachment',
					'name'           => $title
				);
				$attachment_check = new WP_Query( $attachment_args );

				if ( !$attachment_check->have_posts() ) {
					$attachment_id = self::create_attachment( $url, $title, $caption_title, $caption_desc );
				}
			}

			//largeImageURL

			return 'found ' . count($hits);
		}

		static function create_attachment( $image_url, $title, $caption_title, $caption_description ) {
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

			return $attachment_id;
		}
	}
}