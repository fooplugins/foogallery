<?php
/*
 * FooGallery Admin Demo Content class
 */

if ( ! class_exists( 'FooGallery_Admin_Demo_Content' ) ) {

	class FooGallery_Admin_Demo_Content {

		/**
		 * Import attachments and galleries
		 *
		 * @return int[]
		 */
		function import_demo_content() {
			//import all the images first, so that we can get attachment ID's
			$image_data = include( FOOGALLERY_PATH . 'includes/admin/demo-content-images.php' );

			$images_imported     = 0;
			$attachment_mappings = array();

			foreach ( $image_data as $attachment_data ) {
				$result = $this->import_attachment( $attachment_data );
				if ( $result !== false ) {
					if ( $result['imported'] ) {
						$images_imported++;
					}
					$attachment_mappings[ $result['key'] ] = intval( $result['attachment_id'] );
				}
			}

			$gallery_data = include( FOOGALLERY_PATH . 'includes/admin/demo-content-galleries.php' );

			$galleries_imported = 0;

			foreach ( $gallery_data as $post_data ) {
				//create the post
				$result = $this->import_gallery( $post_data, $attachment_mappings );
				if ( $result !== false ) {
					if ( $result['imported'] ) {
						$galleries_imported++;
					}
				}
			}

			return array(
				'attachments' => $images_imported,
				'galleries' => $galleries_imported
			);
		}

		function import_gallery( $gallery_data, $attachment_mappings ) {
			$imported_galleries = get_option( FOOGALLERY_OPTION_DEMO_CONTENT_GALLERIES, array() );

			$key = $gallery_data['key'];

			//check to see if the gallery has already been imported
			if ( array_key_exists( $key, $imported_galleries ) ) {
				$gallery_id = $imported_galleries[ $key ];
				//check that the gallery actually exists
				if ( get_post_status ( $gallery_id ) ) {
					return array(
						'id'       => $gallery_id,
						'imported' => false
					);
				}
			}

			$items = $gallery_data['items'];
			unset( $gallery_data['items'] );
			unset( $gallery_data['key'] );

			$gallery_id = wp_insert_post( $gallery_data, true );
			$imported = true;

			if ( !is_wp_error( $gallery_id ) ) {

				if ( $imported ) {
					//save the gallery to options so we can delete easily it later
					$imported_galleries                = get_option( FOOGALLERY_OPTION_DEMO_CONTENT_GALLERIES, array() );
					$imported_galleries[ $key ] = $gallery_id;
					update_option( FOOGALLERY_OPTION_DEMO_CONTENT_GALLERIES, $imported_galleries );
				}

				$attachments = array();

				//get the attachment ID's and set the attachment metadata
				foreach ( $items as $item ) {
					if ( array_key_exists( $item, $attachment_mappings ) ) {
						$attachments[] = $attachment_mappings[ $item ];
					}
				}

				update_post_meta( $gallery_id, FOOGALLERY_META_ATTACHMENTS, $attachments );

				return array(
					'id' => $gallery_id,
					'imported' => $imported
				);
			}

			return false;
		}

		/**
		 * Import an attachment into the media library
		 *
		 * @param $attachment_data
		 *
		 * @return array|bool
		 */
		function import_attachment( $attachment_data ) {

			$imported_attachments = get_option( FOOGALLERY_OPTION_DEMO_CONTENT_ATTACHMENTS, array() );

			//check to see if the image has already been imported
			if ( array_key_exists( $attachment_data['key'], $imported_attachments ) ) {
				$attachment_id = $imported_attachments[ $attachment_data['key'] ];
				//check that the attachment actually exists
				if ( get_post_status ( $attachment_id ) ) {
					return array(
						'key'           => $attachment_data['key'],
						'attachment_id' => $attachment_id,
						'imported'      => false
					);
				}
			}

			// Include image.php so we can call wp_generate_attachment_metadata()
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Get the contents of the picture
			$response = wp_remote_get( $attachment_data['url'] );
			if ( is_wp_error( $response ) ) {
				return false;
			}
			$contents = wp_remote_retrieve_body( $response );

			// Upload and get file data
			$upload = wp_upload_bits( basename( $attachment_data['url'] ), null, $contents );
			if ( array_key_exists( 'error', $upload ) && $upload['error'] === true ) {
				return false;
			}
			$guid      = $upload['url'];
			$file      = $upload['file'];
			$file_type = wp_check_filetype( basename( $file ), null );

			// Create attachment
			$attachment_args = array(
				'ID'             => 0,
				'guid'           => $guid,
				'post_title'     => $attachment_data['title'],
				'post_excerpt'   => $attachment_data['caption'],
				'post_content'   => $attachment_data['desc'],
				'post_date'      => '',
				'post_mime_type' => isset( $attachment_data['mime_type'] ) ? $attachment_data['mime_type'] : $file_type['type'],
			);

			if ( isset( $attachment_data['alt'] ) ) {
				$attachment_args['meta_input'] = array(
					'_wp_attachment_image_alt' => $attachment_data['alt']
				);
			}

			// Insert the attachment
			$attachment_id   = wp_insert_attachment( $attachment_args, $file, 0 );
			$attachment_meta = wp_generate_attachment_metadata( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $attachment_meta );

			if ( isset( $attachment_data['tags'] ) ) {
				// Save tags
				wp_set_object_terms( $attachment_id, $attachment_data['tags'], FOOGALLERY_ATTACHMENT_TAXONOMY_TAG, false );
			}

			if ( isset( $attachment_data['categories'] ) ) {
				// Save categories
				wp_set_object_terms( $attachment_id, $attachment_data['categories'], FOOGALLERY_ATTACHMENT_TAXONOMY_CATEGORY, false );
			}

			$imported_attachments[$attachment_data['key']] = $attachment_id;

			update_option( FOOGALLERY_OPTION_DEMO_CONTENT_ATTACHMENTS, $imported_attachments );

			return array(
				'key' => $attachment_data['key'],
				'attachment_id' => $attachment_id,
				'imported' => true
			);
		}
	}
}

