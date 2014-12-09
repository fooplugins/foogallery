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

		function start() {
//			$this->status = self::PROGRESS_STARTED;
//
//			//create an empty foogallery
//			$foogallery_args = array(
//				'post_title'  => $this->foogallery_title,
//				'post_type'   => FOOGALLERY_CPT_GALLERY,
//				'post_status' => 'publish',
//			);
//			$this->foogallery_id   = wp_insert_post( $foogallery_args );
//
//			//set a default gallery template
//			add_post_meta( $this->foogallery_id, FOOGALLERY_META_TEMPLATE, foogallery_default_gallery_template(), true );
		}

		function can_import() {
			if ( $this->status === self::PROGRESS_NOT_STARTED ) {
				return count( $this->nextgen_galleries ) > 0;
			}

			return false;
		}
	}
}
