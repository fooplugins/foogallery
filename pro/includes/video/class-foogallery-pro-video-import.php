<?php

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 4/17/2018
 * Time: 10:09 PM
 */

if ( ! class_exists( "FooGallery_Pro_Video_Import" ) ) {

	require_once dirname( __FILE__ ) . '/class-foogallery-pro-video-base.php';

	class FooGallery_Pro_Video_Import extends FooGallery_Pro_Video_Base {

		function __construct() {

			add_action( 'wp_ajax_fgi_import', array( $this, 'ajax' ) );

		}

		public function get_args() {
			return array(
				"videos" => $this->get_videos_arg(),
				"nonce"  => ! empty( $_POST["fgi_nonce"] ) ? $_POST["fgi_nonce"] : null
			);
		}

		private function get_videos_arg() {
			$videos = array();
			if ( empty( $_POST["videos"] ) ) {
				return $videos;
			}
			foreach ( $_POST["videos"] as $video ) {
				$videos[] = is_array( $video ) ? $video : json_decode( stripslashes( $video ), true );
			}

			return $videos;
		}

		public function ajax() {
			$args = $this->get_args();
			if ( wp_verify_nonce( $args["nonce"], "fgi_nonce" ) ) {
				if ( empty( $args["videos"] ) || ! is_array( $args["videos"] ) ) {
					wp_send_json_error( "The 'videos' argument is required and must be an array." );

					return;
				}
				$response = $this->handle_import( $args["videos"] );
				wp_send_json_success( $response );
			}
			die();
		}

		public function handle_import( $videos ) {
			$response = array(
				"mode"     => "import-result",
				"imported" => array(),
				"failed"   => array()
			);

			$video_types = array(
				'youtube',
				'vimeo',
				'wistia-inc',
				'dailymotion'
			);

			foreach ( $videos as $video ) {
				//set the default type to "video"
				$video["type"] = 'embed';

				if ( in_array( $video['provider'], $video_types ) ) {
					$video["type"] = 'video';
				}

				if ( isset( $video["urls"] ) ) {
					// handle self-hosted import
					$video["type"] = 'video';
					$url = "";
					foreach ( $video["urls"] as $type => $value ) {
						if ( ! empty( $value ) ) {
							if ( ! empty( $url ) ) {
								$url .= ",";
							}
							$url .= $value;
						}
					}
					if ( ! empty( $url ) ) {
						$video["url"] = $url;
					} else {
						$response["failed"][] = $video;
						continue;
					}
				}
				$attachment_id = $this->create_attachment( $video );
				if ( $attachment_id === false ) {
					$response["failed"][] = $video;
				} else {
					$response["imported"][] = $attachment_id;
					// Save alt text in the post meta
					update_post_meta( $attachment_id, "_wp_attachment_image_alt", $video["title"] );
					// Save the URL that we will be opening
					update_post_meta( $attachment_id, "_foogallery_custom_url", $video["url"] );
					// Make sure we open in new tab by default
					update_post_meta( $attachment_id, "_foogallery_custom_target", foogallery_get_setting( "video_default_target", "_blank" ) );
					//save video object
					update_post_meta( $attachment_id, FOOGALLERY_VIDEO_POST_META, $video );
				}
			}

			return $response;
		}

		private function create_attachment( &$video ) {
			//allow for really big imports that take a really long time!
			@set_time_limit( 300 );

			$video["thumbnail"] = $this->get_thumbnail_url( $video );
			$response           = wp_remote_get( $video["thumbnail"] );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$thumbnail          = wp_remote_retrieve_body( $response );
			$thumbnail_filename = $this->get_thumbnail_filename( $video, $response );
			if ($thumbnail_filename === false){
				return false;
			}

			$upload = wp_upload_bits( $thumbnail_filename, null, $thumbnail );
			if ($upload["error"] === true){
				return false;
			}

			$guid   = $upload["url"];
			$file   = $upload["file"];

			// Create attachment
			$attachment = array(
				'ID'             => 0,
				'guid'           => $guid,
				'post_title'     => $video["title"],
				'post_excerpt'   => $video["title"],
				'post_content'   => $video["description"],
				'post_date'      => '',
				'post_mime_type' => 'image/foogallery'
			);

			// Include image.php so we can call wp_generate_attachment_metadata()
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Insert the attachment
			$attachment_id   = wp_insert_attachment( $attachment, $file, 0 );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			$thumbnail_details  = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ); // Yes we have the data, but get the thumbnail URL anyway, to be safe
			$video["thumbnail"] = $thumbnail_details[0];

			return $attachment_id;
		}

		private function get_thumbnail_url( $video ) {
			if ( ! empty( $video["provider"] ) ) {
				switch ( $video["provider"] ) {
					case "youtube":
						$format = "http://img.youtube.com/vi/%1s/%2s.jpg";
						/**
						 * Possible filenames for images, in order of desirability. Should only ever use first one.
						 * @see http://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
						 */
						$sizes = array(
							'maxresdefault',
							'hqdefault',
							'sddefault',
							'default',
							'0'
						);
						foreach ( $sizes as $size ) {
							$url = sprintf( $format, $video["id"], $size );
							if ( $this->url_exists( $url ) ) {
								return $url;
							}
						}
						break;
				}
			}

			return $video["thumbnail"];
		}

		private $image_mimes = array(
			"png" => array("image/png", "image/x-png"),
			"bmp" => array("image/bmp", "image/x-bmp", "image/x-bitmap", "image/x-xbitmap", "image/x-win-bitmap", "image/x-windows-bmp", "image/ms-bmp", "image/x-ms-bmp", "application/bmp", "application/x-bmp","application/x-win-bitmap"),
			"gif" => array("image/bmp", "image/x-bmp"),
			"jpeg" => array("image/jpeg", "image/pjpeg"),
			"svg" => array("image/svg+xml"),
			"jp2" => array("image/jp2", "image/jpx", "image/jpm"),
			"tiff" => array("image/tiff"),
			"ico" => array("image/x-icon", "image/x-ico", "image/vnd.microsoft.icon")
		);

		private function mime2ext($mime){
			foreach ($this->image_mimes as $key => $value) {
				if(array_search($mime,$value) !== false) return $key;
			}
			return false;
		}

		private function url2ext($url){
			$parts = parse_url($url);
			$ext = pathinfo( basename( $parts["path"] ), PATHINFO_EXTENSION );
			return array_key_exists($ext, $this->image_mimes) ? $ext : false;
		}

		private function get_thumbnail_filename( $video, $response ) {
			$headers = $response["headers"];
			if ( isset($headers["content-type"]) ){
				$ext = $this->mime2ext($headers["content-type"]);
			} else {
				$ext = $this->url2ext($video["thumbnail"]);
			}
			return $ext === false ? false : $video["id"] . '.' . $ext;
		}
	}
}
