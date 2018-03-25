<?php
if ( ! class_exists( 'FooGallery_FooGallery_Pro_Video_Import_Handler_Vimeo' ) ) {
	class FooGallery_FooGallery_Pro_Video_Import_Handler_Vimeo {


		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_foo_video_gallery_import_vimeo', array( $this, 'ajax_handler' ) );
			add_filter( 'foo_video_prepare_video_object', array( $this, 'prepare_video_object' ), 10, 2 );
		}

		/**
		 * Perpare single Vimeo video
		 *
		 * @param stdClass $object
		 *
		 * @return array
		 */
		public function prepare_video_object( $video_object, $object ) {
			if ( isset( $object['custom'] ) ) {
				return $video_object;
			}


			if ( isset( $object['video_id'] ) || isset( $object['id'] ) ) {
				if ( !isset( $object['video_id'] ) ) {
					$object['video_id'] = $object['id'];
				}

				if ( !isset( $object['thumbnail_url'] ) ) {
					$object['thumbnail_url'] = $object['thumbnail'];
				}

				return array(
					'type'        => 'vimeo',
					'id'          => $object['video_id'],
					'title'       => $object['title'],
					'url'         => 'https://vimeo.com/' . $object['video_id'],
					'description' => $object['description'],
					'thumbnail'   => $object['thumbnail_url']
				);
			}

			return $video_object;
		}

		/**
		 * The ajax handler
		 */
		public function ajax_handler() {
			if ( isset( $_POST[ 'playlist_id' ] ) && isset( $_POST[ 'foo_video_nonce' ] ) && wp_verify_nonce( $_POST[ 'foo_video_nonce' ], 'foo_video_nonce' ) ) {
				$playlist = json_decode( stripslashes_deep( $_POST[ 'playlist_id'] ) , true );

				$result = $this->do_import( $playlist, $_POST['offset'] );
				if( false !== $result ){
					wp_send_json_success( $result );
				}

				wp_send_json_error( $result );

			}else{
				status_header( 500 );
				echo __( 'Could not import.', 'foogallery' );
				wp_die();
			}
		}

		/**
		 * Do import for whole playlist
		 *
		 * @param string $playlist Playlist to import
		 * @param int $album_id Foo album to import to.
		 *
		 * @return array
		 */
		protected function do_import( $playlist, $offset ) {

			$attachments = false;
			if ( is_array( $playlist ) && !empty( $playlist['clips'] ) ) {
				$total = count( $playlist['clips'] );
				foreach( $playlist['clips'] as $index=>$video ) {

					if( $index < $offset ){ continue; }

					$video['video_id'] = $video['id'];
					$video['thumbnail_url'] = $video['thumbnail'];

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
	}
}
