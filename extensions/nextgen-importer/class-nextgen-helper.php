<?php

if ( ! class_exists( 'FooGallery_NextGen_Helper' ) ) {

	class FooGallery_NextGen_Helper {

		const NEXTGEN_TABLE_GALLERY          = 'ngg_gallery';
		const NEXTGEN_TABLE_PICTURES         = 'ngg_pictures';
		const NEXTGEN_OPTION_IMPORT_CURRENT  = 'foogallery_nextgen_import-current';
		const NEXTGEN_OPTION_IMPORT_PROGRESS = 'foogallery_nextgen_import-progress';
		const NEXTGEN_OPTION_IMPORT_IN_PROGRESS  = 'foogallery_nextgen_import-importing';

		/**
		 * @TODO
		 */
		function is_nextgen_installed() {
			return class_exists( 'C_NextGEN_Bootstrap' ) || class_exists( 'nggLoader' );
		}

		function get_galleries() {
			global $wpdb;
			$gallery_table = $wpdb->prefix . self::NEXTGEN_TABLE_GALLERY;
			$picture_table = $wpdb->prefix . self::NEXTGEN_TABLE_PICTURES;

			return $wpdb->get_results( "select gid, name, title, galdesc,
(select count(*) from {$picture_table} where galleryid = gid) 'image_count'
from {$gallery_table}" );
		}

		function get_gallery( $id ) {
			global $wpdb;
			$gallery_table = $wpdb->prefix . self::NEXTGEN_TABLE_GALLERY;
			$picture_table = $wpdb->prefix . self::NEXTGEN_TABLE_PICTURES;

			return $wpdb->get_row( $wpdb->prepare( "select gid, name, title, galdesc, path, author,
(select count(*) from {$picture_table} where galleryid = gid) 'image_count'
from {$gallery_table}
where gid = %d", $id ) );
		}

		function get_gallery_images( $id ) {
			global $wpdb;
			$picture_table = $wpdb->prefix . self::NEXTGEN_TABLE_PICTURES;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$picture_table} WHERE galleryid = %d", $id ) );
		}

		/**
		 * @param bool $id
		 *
		 * @return FooGallery_NextGen_Import_Progress
		 */
		function get_import_progress( $nextgen_gallery_id ) {
			$progress = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS );
			if ( false !== $progress ) {
				if ( false !== $nextgen_gallery_id && array_key_exists( $nextgen_gallery_id, $progress ) ) {
					return $progress[ $nextgen_gallery_id ];
				}
			}

			return new FooGallery_NextGen_Import_Progress();
		}

		function set_import_progress( $nextgen_gallery_id, FooGallery_NextGen_Import_Progress $progress ) {
			$all_progress                        = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, array() );
			$all_progress[ $nextgen_gallery_id ] = $progress;
			update_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, $all_progress );
		}

		function init_import_progress( $nextgen_gallery_id, $foogallery_title ) {
			$progress = new FooGallery_NextGen_Import_Progress();
			$progress->init( $nextgen_gallery_id, $foogallery_title );
			$this->set_import_progress( $nextgen_gallery_id, $progress );
		}

		function start_import() {
			delete_option( self::NEXTGEN_OPTION_IMPORT_CURRENT );
			update_option( self::NEXTGEN_OPTION_IMPORT_IN_PROGRESS, true );
		}

		function cancel_import() {
			delete_option( self::NEXTGEN_OPTION_IMPORT_CURRENT );
			delete_option( self::NEXTGEN_OPTION_IMPORT_IN_PROGRESS );
		}

		function reset_import() {
			delete_option( self::NEXTGEN_OPTION_IMPORT_CURRENT );
			delete_option( self::NEXTGEN_OPTION_IMPORT_IN_PROGRESS );
			delete_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS );
		}

		function import_in_progress() {
			return true == get_option( self::NEXTGEN_OPTION_IMPORT_IN_PROGRESS );
		}

		function continue_import() {
			//get the current gallery being imported
			$current_nextgen_id = get_option( self::NEXTGEN_OPTION_IMPORT_CURRENT, 0 );

			if ( 0 === $current_nextgen_id ) {
				//try and get the next gallery to import
				$current_nextgen_id = $this->get_next_gallery_to_import();

				//if we still have no current then do nothing
				if ( 0 === $current_nextgen_id ) {
					$this->cancel_import();
					return;
				} else {
					update_option( self::NEXTGEN_OPTION_IMPORT_CURRENT, $current_nextgen_id );
				}
			}

			$progress = $this->get_import_progress( $current_nextgen_id );

			if ( ! $progress->has_started() ) {
				$progress->start();
			}

			//import the next picture
			$progress->import_next_picture();

			//update our progress
			$this->set_import_progress( $current_nextgen_id, $progress );

			//if the percentage complete is 100 then clear the current gallery
			if ( $progress->is_completed() ) {
				delete_option( self::NEXTGEN_OPTION_IMPORT_CURRENT );
			}
		}

		function get_overall_progress() {
			$all_progress       = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, array() );
			$total = 0;
			$imported = 0;
			foreach ( $all_progress as $id => $progress ) {
				if ( $progress->is_part_of_current_import ) {
					$total += $progress->import_count;
					$imported += count( $progress->attachments );
				}
			}
			if ( 0 === $total ) {
				return 100;
			}
			return  $imported / $total * 100;
		}

		function get_next_gallery_to_import() {
			$all_progress       = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, array() );

			foreach ( $all_progress as $id => $progress ) {
				if ( $progress->can_import() ) {
					return $id;
				}
			}

			return 0;
		}

		function ignore_previously_imported_galleries() {
			$all_progress = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, array() );
			foreach ( $all_progress as $id => $progress ) {
				if ( $progress->is_completed() ) {
					$progress->is_part_of_current_import = false;
				}
			}
			update_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS, $all_progress );
		}

		function import_picture( $nextgen_gallery_path, $picture ) {
			$picture_url = trailingslashit( site_url() ) . trailingslashit( $nextgen_gallery_path ) . $picture->filename;

			// Get the contents of the picture
			$response = wp_remote_get( $picture_url );
			$contents = wp_remote_retrieve_body( $response );

			// Upload and get file data
			$upload    = wp_upload_bits( basename( $picture_url ), null, $contents );
			$guid      = $upload['url'];
			$file      = $upload['file'];
			$file_type = wp_check_filetype( basename( $file ), null );

			// Create attachment
			$attachment = array(
				'ID'             => 0,
				'guid'           => $guid,
				'post_title'     => $picture->alttext != '' ? $picture->alttext : $picture->image_slug,
				'post_excerpt'   => $picture->description,
				'post_content'   => $picture->description,
				'post_date'      => '',
				'post_mime_type' => $file_type['type'],
			);

			// Include image.php so we can call wp_generate_attachment_metadata()
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// Insert the attachment
			$attachment_id   = wp_insert_attachment( $attachment, $file, 0 );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			// Save alt text in the post meta
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $picture->alttext );

			return $attachment_id;
		}

		function render_import_form( $galleries = false ) {
			if ( false === $galleries ) {
				$galleries = $this->get_galleries();
			}
			$has_imports = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS );
			$overall_progress = $this->get_overall_progress();
			$all_imports_completed = 100 === $overall_progress;
			$import_has_started = $this->import_in_progress();
			$importing = $import_has_started && defined('DOING_AJAX') && DOING_AJAX;
			$current_nextgen_id = get_option( self::NEXTGEN_OPTION_IMPORT_CURRENT, 0 );
			?>
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column">
						<?php if ( ! $importing && $all_imports_completed ) { ?>
						<label class="screen-reader-text"
						       for="cb-select-all-1"><?php _e( 'Select All', 'foogallery' ); ?></label>
						<input id="cb-select-all-1" type="checkbox" <?php echo $importing ? 'disabled="disabled"' : ''; ?> checked="checked">
						<?php } ?>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'NextGen Gallery', 'foogallery' ); ?></span>
					</th>
					<th scope="col" id="title" class="manage-column">
						<span><?php printf( __( '%s Name', 'foogallery' ), foogallery_plugin_name() ); ?></span>
					</th>
					<th scope="col" id="title" class="manage-column">
						<span><?php _e( 'Import Progress', 'foogallery' ); ?></span>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $galleries as $gallery ) {
					$progress    = $this->get_import_progress( $gallery->gid );
					$done        = $progress->is_completed();
					$edit_link	 = '';
					if ( $progress->foogallery_id > 0 ) {
						$foogallery = FooGallery::get_by_id( $progress->foogallery_id );
						$edit_link  = '<a href="' . admin_url( 'post.php?post=' . $progress->foogallery_id . '&action=edit' ) . '">' . $foogallery->name . '</a>';
					} ?>
					<tr>
						<?php if ( ! $importing && !$done && $all_imports_completed ) { ?>
							<th scope="row" class="column-cb check-column">
								<input name="nextgen-id[]" type="checkbox" checked="checked" value="<?php echo $gallery->gid; ?>">
							</th>
						<?php } else if ( $importing && $gallery->gid == $current_nextgen_id ) { ?>
							<th>
								<div class="dashicons dashicons-arrow-right"></div>
							</th>
						<?php } else { ?>
							<th>
							</th>
						<?php } ?>
						<td>
							<?php echo $gallery->title . sprintf( __( ' (%s images)', 'foogallery' ), $gallery->image_count ); ?>
						</td>
						<td>
							<?php if ( $progress->foogallery_id > 0 ) {
								echo $edit_link;
							} else {
								?>
								<input name="foogallery-name-<?php echo $gallery->gid; ?>" value="<?php echo $gallery->title; ?>">
							<?php } ?>
						</td>
						<td class="nextgen-import-progress nextgen-import-progress-<?php echo $progress->status; ?>">
							<?php echo $progress->message(); ?>
						</td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
			<br/>

			<?php
			echo '<input type="hidden" id="nextgen_import_progress" value="' . $overall_progress . '" />';
			wp_nonce_field( 'foogallery_nextgen_import', 'foogallery_nextgen_import' );
			wp_nonce_field( 'foogallery_nextgen_import_refresh', 'foogallery_nextgen_import_refresh', false );
			wp_nonce_field( 'foogallery_nextgen_import_cancel', 'foogallery_nextgen_import_cancel', false );
			wp_nonce_field( 'foogallery_nextgen_import_reset', 'foogallery_nextgen_import_reset', false );
			if ( ! $import_has_started && !$importing ) {
				?>
				<input type="submit" class="button button-primary start_import"
				       value="<?php _e( 'Start Import', 'foogallery' ); ?>">
			<?php } else if ( $import_has_started && !$importing ) { ?>
				<input type="submit" class="button button-primary continue_import" value="<?php _e( 'Resume Import', 'foogallery' ); ?>">
			<?php } else { ?>
				<input type="submit" class="button cancel_import" value="<?php _e( 'Stop Import', 'foogallery' ); ?>">
			<?php
			}
			if ( $has_imports && ! $importing ) { ?>
				<input type="submit" name="foogallery_nextgen_reset" class="button reset_import" value="<?php _e( 'Reset All Imports', 'foogallery' ); ?>">
			<?php }
			?>
			<div id="import_spinner" style="width:20px">
				<span class="spinner"></span>
			</div>
			<?php if ( $importing ) { ?>
				<div class="nextgen-import-progressbar">
					<span style="width:<?php echo $overall_progress; ?>%"></span>
				</div>
				<?php echo intval( $overall_progress ); ?>%
				<div style="width:20px; display: inline-block;">
					<span class="spinner shown"></span>
				</div>
			<?php }
		}
	}
}