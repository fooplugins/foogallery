<?php

if ( ! class_exists( 'FooGallery_NextGen_Import_Progress_Album' ) ) {

	class FooGallery_NextGen_Import_Progress_Album extends stdClass {

		const PROGRESS_NOT_STARTED = 'not_started';
		const PROGRESS_COMPLETED = 'completed';
		const PROGRESS_ERROR = 'error';

		function __construct() {
			$this->nextgen_album_id = 0;
			$this->foogallery_album_id = 0;
			$this->foogallery_album_title = '';
			$this->galleries_count = 0;
			$this->nextgen_album = false;
			$this->status = self::PROGRESS_NOT_STARTED;
		}

		function message() {
			switch ( $this->status ) {
				case self::PROGRESS_NOT_STARTED:
					return __( 'Album not imported', 'foogallery' );
					break;
				case self::PROGRESS_COMPLETED:
					return sprintf( __( 'Done! %d galleries(s) linked', 'foogallery' ), $this->galleries_count );
					break;
			}

			return __( 'Unknown status!', 'foogallery' );
		}

		function is_completed() {
			return $this->status === self::PROGRESS_COMPLETED;
		}

		function not_started() {
			return $this->status === self::PROGRESS_NOT_STARTED;
		}

		function import() {
			//create an empty foogallery album
			$foogallery_album_args = array(
				'post_title'  => $this->foogallery_album_title,
				'post_type'   => FOOGALLERY_CPT_ALBUM,
				'post_status' => 'publish',
			);
			$this->foogallery_album_id   = wp_insert_post( $foogallery_album_args );

			//set a default gallery template
			add_post_meta( $this->foogallery_album_id, FOOGALLERY_ALBUM_META_TEMPLATE, foogallery_default_album_template(), true );

			$nextgen = new FooGallery_NextGen_Helper();

			//link all galleries that can be linked
			$album = $nextgen->get_album( $this->nextgen_album_id );
			$galleries = $nextgen->nextgen_unserialize( $album->sortorder );
			$gallery_ids = array();
			foreach ( $galleries as $gallery_id ) {
				$gallery_progress = $nextgen->get_import_progress( $gallery_id );
				if ( $gallery_progress->is_completed() ) {
					$gallery_ids[] = $gallery_progress->foogallery_id;
					$this->galleries_count++;
				}
			}

			//link all galleries to the foogallery album
			add_post_meta( $this->foogallery_album_id, FOOGALLERY_ALBUM_META_GALLERIES, $gallery_ids );

			$this->status = self::PROGRESS_COMPLETED;
		}

		function can_import() {
			if ( $this->status === self::PROGRESS_NOT_STARTED ) {
				return count( $this->nextgen_galleries ) > 0;
			}

			return false;
		}
	}
}
