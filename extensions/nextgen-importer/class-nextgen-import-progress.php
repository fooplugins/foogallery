<?php

if ( ! class_exists( 'FooGallery_NextGen_Import_Progress' ) ) {

	class FooGallery_NextGen_Import_Progress extends stdClass {

		const PROGRESS_NOT_STARTED = 'not_started';
		const PROGRESS_QUEUED = 'queued';
		const PROGRESS_STARTED = 'started';
		const PROGRESS_COMPLETED = 'completed';
		const PROGRESS_ERROR = 'error';

		function __construct() {
			$this->nextgen_gallery_id = 0;
			$this->percentage_complete = 0;
			$this->foogallery_id = 0;
			$this->foogallery_title = '';
			$this->import_count = 0;
			$this->attachments = array();
			$this->nextgen_gallery = false;
			$this->nextgen_pictures = array();
			$this->status = self::PROGRESS_NOT_STARTED;
			$this->is_part_of_current_import = false;
		}

		function init( $nextgen_gallery_id, $foogallery_title ) {
			$this->nextgen_gallery_id = $nextgen_gallery_id;
			$this->foogallery_title = $foogallery_title;
			$this->status = self::PROGRESS_QUEUED;

			$nextgen = new FooGallery_NextGen_Helper();

			//load the gallery and pictures
			$this->nextgen_gallery  = $nextgen->get_gallery( $this->nextgen_gallery_id );
			$this->nextgen_pictures = $nextgen->get_gallery_images( $this->nextgen_gallery_id );

			$this->import_count = count( $this->nextgen_pictures );

			//check for zero images
			if ( 0 === $this->import_count ) {
				$this->status = self::PROGRESS_ERROR;
			}

			$this->is_part_of_current_import = true;
		}

		function message() {
			switch ( $this->status ) {
				case self::PROGRESS_NOT_STARTED:
					return __( 'Not imported', 'foogallery' );
					break;
				case self::PROGRESS_QUEUED:
					return __( 'Queued for import', 'foogallery' );
					break;
				case self::PROGRESS_STARTED:
					return sprintf( __( 'Imported %d of %d image(s)', 'foogallery' ),
						count( $this->attachments ), $this->import_count );
					break;
				case self::PROGRESS_COMPLETED:
					return sprintf( __( 'Done! %d image(s) imported', 'foogallery' ), $this->import_count );
					break;
				case self::PROGRESS_ERROR:
					if ( 0 === $this->import_count ) {
						return __( 'No images to import!', 'foogallery' );
					} else {
						return __( 'Error while importing!', 'foogallery' );
					}
					break;
			}

			return __( 'Unknown status!', 'foogallery' );
		}

		function queued_for_import() {
			return $this->status === self::PROGRESS_QUEUED;
		}

		function has_started() {
			return $this->status === self::PROGRESS_STARTED;
		}

		function is_completed() {
			return $this->status === self::PROGRESS_COMPLETED;
		}

		function not_started() {
			return $this->status === self::PROGRESS_NOT_STARTED;
		}

		function start() {
			$this->status = self::PROGRESS_STARTED;

			//create an empty foogallery
			$foogallery_args = array(
				'post_title'  => $this->foogallery_title,
				'post_type'   => FOOGALLERY_CPT_GALLERY,
				'post_status' => 'publish',
			);
			$this->foogallery_id = wp_insert_post( $foogallery_args );

			//set a default gallery template
			add_post_meta( $this->foogallery_id, FOOGALLERY_META_TEMPLATE, foogallery_default_gallery_template(), true );

			//set default settings if there are any
            $default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
            if ( $default_gallery_id ) {
                $settings = get_post_meta( $default_gallery_id, FOOGALLERY_META_SETTINGS, true );
                add_post_meta( $this->foogallery_id, FOOGALLERY_META_SETTINGS, $settings, true );
            }
		}

		function can_import() {
			if ( $this->status === self::PROGRESS_QUEUED ) {
				return true;
			} else if ( $this->status === self::PROGRESS_STARTED ) {
				return count( $this->nextgen_pictures ) > 0;
			}

			return false;
		}

		function import_next_picture() {
			$picture = array_pop( $this->nextgen_pictures );

			$nextgen = new FooGallery_NextGen_Helper();

			$attachment_id = $nextgen->import_picture( $this->nextgen_gallery->path, $picture );

			$attachment_ids = get_post_meta( $this->foogallery_id, FOOGALLERY_META_ATTACHMENTS, true );

			if ( empty( $attachment_ids ) ) {
				$attachment_ids = array();
			}

			$attachment_ids[] = $attachment_id;

			//link all attachments to foogallery
			update_post_meta( $this->foogallery_id, FOOGALLERY_META_ATTACHMENTS, $attachment_ids );

			//update our list of imported attachments
			$this->attachments[] = $attachment_id;

			//update our percentage complete
			if ( $this->import_count > 0 ) {
				$this->percentage_complete = count( $this->attachments ) / $this->import_count * 100;
			}

			//update our status if 100%
			if ( 100 === $this->percentage_complete ) {
				$this->status = self::PROGRESS_COMPLETED;
			}
		}
	}
}
