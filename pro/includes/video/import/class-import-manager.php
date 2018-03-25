<?php

final class FooGallery_FooGallery_Pro_Video_Import_Manager {

	public function __construct( ) {
		add_action( 'wp_ajax_foo_video_gallery_import_selection', array( $this, 'ajax_handler' ) );
	}

	public function ajax_handler() {

		if ( !empty( $_POST['selection'] ) && isset( $_POST[ 'foo_video_nonce' ] ) && wp_verify_nonce( $_POST[ 'foo_video_nonce' ], 'foo_video_nonce' ) ) {

			$selection = array();
			foreach( $_POST['selection'] as $video_selection ){
				$video = is_array( $video_selection ) ? $video_selection : json_decode( stripslashes( $video_selection ), true );

				$selection[] = $video;
			}

			$result = $this->do_import( $selection, $_POST['offset'] );
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
	 * Do single/selection video import
	 *
	 * @param $objects
	 * @param $offset
	 *
	 * @return array
	 */
	protected function do_import( $objects, $offset ) {
		$attachments = false;
		if ( is_array( $objects ) ) {
			$total = count( $objects );
			foreach( $objects as $index => $object ){
				if( $index < $offset ){ continue; }

				if ( array_key_exists( 'custom', $object ) ) {
					$item = array(
						'type'        => 'other',
						'id'          => $object['id'],
						'title'       => $object['title'],
						'url'         => $object['embed'],
						'description' => $object['description'],
						'thumbnail'   => $object['thumb_large']
					);
				} else {
					//prepare the video object
					$item = apply_filters( 'foo_video_prepare_video_object', false, $object );
				}

				if ( !empty( $item ) ) {
					$attachment = foogallery_foovideo_import_video_as_attachment( $item );
					$attachments['ids'][] = $attachment;

					if( count( $attachments['ids'] ) >= FOOVIDEO_BATCH_LIMIT && ( $total - 1 ) > $index ){
						$part = true;
						break;
					}
				}
			}
			if( !empty( $part ) ){
				$attachments['offset'] = $index + 1;
			}
			$attachments['percent'] = ( ( $index + 1 ) / $total ) * 100;
		}

		return $attachments;
	}
}
