<?php
if ( ! class_exists( 'FooGallery_FooGallery_Pro_Video_Import_Handler_YouTube' ) ) {
	class FooGallery_FooGallery_Pro_Video_Import_Handler_YouTube {


		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_foo_video_gallery_import_youtube', array( $this, 'ajax_handler' ) );
			add_filter( 'foo_video_prepare_video_object', array( $this, 'prepare_video_object' ), 10, 2 );
		}

		/**
		 * Prepare single Youtube video
		 *
		 * @param stdClass $object
		 *
		 * @return array
		 */
		public function prepare_video_object( $video_object, $object ) {
			if ( isset( $object['encrypted_id'] ) ) {
				return array(
					'type'        => 'youtube',
					'id'          => $object['encrypted_id'],
					'title'       => $object['title'],
					'url'         => "http://www.youtube.com/watch?v=" . $object['encrypted_id'],
					'description' => $object['description'],
					'thumbnail'   => $this->thumbnail( $object['encrypted_id'] )
					//'thumbnail'   => dirname( $object['thumbnail'] ) . '/maxresdefault.jpg'
				);
			}

			return $video_object;
		}

		/**
		 * The ajax handler
		 */
		public function ajax_handler() {
			if ( isset( $_POST[ 'playlist_id' ] ) && isset( $_POST[ 'foo_video_nonce' ] ) && wp_verify_nonce( $_POST[ 'foo_video_nonce' ], 'foo_video_nonce' ) ) {
				$playlist_id = strip_tags( trim( $_POST[ 'playlist_id'] ) );
				$result = $this->do_import( $playlist_id, $_POST['offset'] );
				if( false !== $result ){
					wp_send_json_success( $result );
				}

				wp_send_json_error( $result );

			} else {
				status_header( 500 );
				echo __( 'Could not import.', 'foogallery' );
				wp_die();
			}
		}

		/**
		 * Do import for whole playlist
		 *
		 * @param string $playlist_id Playlist to import
		 * @param int $album_id Foo album to import to.
		 *
		 * @return array
		 */
		protected function do_import( $playlist_id, $offset ) {
			$feed = $this->request( $playlist_id );
			$attachments = false;
			if ( is_array( $feed ) && !empty( $feed['video'] ) ) {
				$total = count( $feed['video'] );
				foreach( $feed['video'] as $index => $video ) {

					if( $index < $offset ){ continue; }

					$item = $this->prepare_video_object( null, $video );
					$attachment = foogallery_foovideo_import_video_as_attachment( $item );
					$attachments['ids'][] = $attachment;

					if( count( $attachments['ids'] ) >= FOOVIDEO_BATCH_LIMIT && ( $total - 1 ) > $index ){
						$part = true;
						break;
					}
				}
				if( !empty( $part ) ){
					$attachments['offset'] = $index + 1;
				}
				$attachments['percent'] = ( $index / $total ) * 100;
			}

			return $attachments;
		}

		/**
		 * Get playlist data
		 *
		 * @param $playlist_id
		 *
		 * @return array|mixed|object
		 */
		protected function request( $playlist_id ) {
			$url = 'https://www.youtube.com/list_ajax?style=json&action_get_list=true&list=' . $playlist_id;
			$r = wp_remote_request( $url );
			if ( ! is_wp_error( $r ) ) {
				$body = wp_remote_retrieve_body( $r );
				$feed = json_decode( $body, true );
				return $feed;
			}
		}


		/**
		 * Find the thumbnail.
		 *
		 * Note: Could not get the thumbnail by parsing the feed, so had to make separate request.
		 *
		 * @param string $id The video ID.
		 *
		 * @return string Thumb URL
		 */
		protected function thumbnail( $id ) {
			$pattern = 'http://img.youtube.com/vi/%1s/%2s.jpg';

			/**
			 * Possible filenames for images, in order of desirability. Should only ever use first one.
			 * @see http://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
			 */
			$thumbnames = array(
				'maxresdefault',
				'hqdefault',
				'sddefault',
				'default',
				'0'
			);

			foreach( $thumbnames as $name ) {
				$url = sprintf( $pattern, $id, $name );
				$response = wp_safe_remote_get( $url );
				if ( ! is_wp_error( $response )  && 200 === wp_remote_retrieve_response_code( $response ) ) {
					return $url;
				}
			}
		}
	}
}
