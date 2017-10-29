<?php

if ( ! class_exists( 'FooGallery_NextGen_Helper' ) ) {

	class FooGallery_NextGen_Helper {

		const NEXTGEN_TABLE_GALLERY          = 'ngg_gallery';
		const NEXTGEN_TABLE_PICTURES         = 'ngg_pictures';
		const NEXTGEN_TABLE_ALBUMS           = 'ngg_album';

		const NEXTGEN_OPTION_IMPORT_CURRENT  = 'foogallery_nextgen_import-current';
		const NEXTGEN_OPTION_IMPORT_PROGRESS = 'foogallery_nextgen_import-progress';
		const NEXTGEN_OPTION_IMPORT_IN_PROGRESS  = 'foogallery_nextgen_import-importing';

		const NEXTGEN_OPTION_IMPORT_CURRENT_ALBUM  = 'foogallery_nextgen_import-current-album';
		const NEXTGEN_OPTION_IMPORT_PROGRESS_ALBUM = 'foogallery_nextgen_import-progress-album';

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

			return $wpdb->get_results( "select gal.gid, gal.name, gal.title, gal.galdesc, count(pic.pid) 'image_count'
from {$gallery_table} gal
   join {$picture_table} pic on gal.gid = pic.galleryid
group by gal.gid, gal.name, gal.title, gal.galdesc" );
		}

		function get_albums() {
			global $wpdb;
			$album_table = $wpdb->prefix . self::NEXTGEN_TABLE_ALBUMS;
			return $wpdb->get_results(" select * from {$album_table}");
		}

		function get_album( $id ) {
			global $wpdb;
			$album_table = $wpdb->prefix . self::NEXTGEN_TABLE_ALBUMS;

			return $wpdb->get_row( $wpdb->prepare( "select * from {$album_table} where id = %d", $id ) );
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

		/**
		 * @param int $nextgen_gallery_id
		 * @param string $foogallery_title
		 */
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
			$importing = $import_has_started && defined( 'DOING_AJAX' ) && DOING_AJAX;
			$current_nextgen_id = get_option( self::NEXTGEN_OPTION_IMPORT_CURRENT, 0 );
			?>
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column">
						<?php if ( ! $importing && $all_imports_completed ) { ?>
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'foogallery' ); ?></label>
						<input id="cb-select-all-1" type="checkbox" <?php echo $importing ? 'disabled="disabled"' : ''; ?> checked="checked" />
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

			require_once(  plugin_dir_path( __FILE__ ) . 'class-nextgen-pagination.php' );

			$url = add_query_arg( 'page', 'foogallery-nextgen-importer' );
			$page = 1;
			if ( defined( 'DOING_AJAX' ) ) {
				if ( isset( $_POST['foogallery_nextgen_import_paged'] ) ) {
					$url = $_POST['foogallery_nextgen_import_url'];
					$page = $_POST['foogallery_nextgen_import_paged'];
				} else {
					$url = wp_get_referer();
					$parts = parse_url($url);
					parse_str( $parts['query'], $query );
					$page = $query['paged'];
				}
			} elseif ( isset( $_GET['paged'] ) ) {
				$page = $_GET['paged'];
			}
			$url = add_query_arg( 'paged', $page, $url );
			$gallery_count = count($galleries);
			$page_size = apply_filters( 'foogallery_nextgen_import_page_size', 10);

			$pagination = new FooGalleryNextGenPagination();
			$pagination->items( $gallery_count );
			$pagination->limit( $page_size ); // Limit entries per page
			$pagination->url = $url;
			$pagination->currentPage( $page );
			$pagination->calculate();

			for ($counter = $pagination->start; $counter <= $pagination->end; $counter++ ) {
				if ( $counter >= $gallery_count ) {
					break;
				}
				$gallery = $galleries[$counter];
				$progress    = $this->get_import_progress( $gallery->gid );
				$done        = $progress->is_completed();
				$edit_link	 = '';
				$foogallery = false;
				if ( $progress->foogallery_id > 0 ) {
					$foogallery = FooGallery::get_by_id( $progress->foogallery_id );
					if ( $foogallery ) {
						$edit_link = '<a href="' . admin_url( 'post.php?post=' . $progress->foogallery_id . '&action=edit' ) . '">' . $foogallery->name . '</a>';
					} else {
						$done = false;
					}
				} ?>
				<tr class="<?php echo ($counter % 2 === 0) ? 'alternate' : ''; ?>">
					<?php if ( ! $importing && ! $done && $all_imports_completed ) { ?>
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
						<?php echo $gallery->gid . '. '; ?>
						<strong><?php echo $gallery->title; ?></strong>
						<?php echo ' ' . sprintf( __( '(%s images)', 'foogallery' ), $gallery->image_count ); ?>
					</td>
					<td>
					<?php if ( $foogallery ) {
						echo $edit_link;
					} else { ?>
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
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php echo $pagination->render(); ?>
				</div>
			</div>

			<?php
			//hidden fields used for pagination
			echo '<input type="hidden" name="foogallery_nextgen_import_paged" value="' . esc_attr( $page ) . '" />';
			echo '<input type="hidden" name="foogallery_nextgen_import_url" value="' . esc_url( $url ) . '" />';

			echo '<input type="hidden" id="nextgen_import_progress" value="' . $overall_progress . '" />';
			wp_nonce_field( 'foogallery_nextgen_import', 'foogallery_nextgen_import' );
			wp_nonce_field( 'foogallery_nextgen_import_refresh', 'foogallery_nextgen_import_refresh', false );
			wp_nonce_field( 'foogallery_nextgen_import_cancel', 'foogallery_nextgen_import_cancel', false );
			wp_nonce_field( 'foogallery_nextgen_import_reset', 'foogallery_nextgen_import_reset', false );
			if ( ! $import_has_started && ! $importing ) {
				?>
				<input type="submit" class="button button-primary start_import"
				       value="<?php _e( 'Start Import', 'foogallery' ); ?>">
			<?php } else if ( $import_has_started && ! $importing ) { ?>
				<input type="submit" class="button button-primary continue_import" value="<?php _e( 'Resume Import', 'foogallery' ); ?>">
			<?php } else { ?>
				<input type="submit" class="button cancel_import" value="<?php _e( 'Stop Import', 'foogallery' ); ?>">
			<?php
			}
			if ( $has_imports && ! $importing ) { ?>
				<input type="submit" name="foogallery_nextgen_reset" class="button reset_import" value="<?php _e( 'Reset All Gallery Imports', 'foogallery' ); ?>">
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

		function render_album_import_form( $albums = false ) {
			if ( false === $albums ) {
				$albums = $this->get_albums();
			}
			$has_imports = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS_ALBUM );
			?>
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
				<tr>
					<th scope="col" class="manage-column">
						<span><?php _e( 'NextGen Album', 'foogallery' ); ?></span>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'Album Name', 'foogallery' ); ?></span>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'NextGen Galleries', 'foogallery' ); ?></span>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'Import Options', 'foogallery' ); ?></span>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$counter = 0;
				foreach ( $albums as $album ) {
					$counter++;
					$progress    = $this->get_import_progress_for_album( $album->id );
					$done        = $progress->is_completed();
					$edit_link	 = '';
					$galleries   = $this->nextgen_unserialize( $album->sortorder );
					$foogallery_album = false;
					if ( $progress->foogallery_album_id > 0 ) {
						$foogallery_album = FooGalleryAlbum::get_by_id( $progress->foogallery_album_id );
						if ( $foogallery_album ) {
							$edit_link = '<a href="' . admin_url( 'post.php?post=' . $progress->foogallery_album_id . '&action=edit' ) . '">' . $foogallery_album->name . '</a>';
						} else {
							$done = false;
						}
					} ?>
					<tr class="<?php echo ($counter % 2 === 0) ? 'alternate' : ''; ?>">
						<td>
							<?php echo $album->name; ?>
							<input type="hidden" class="foogallery-album-id" value="<?php echo $album->id; ?>">
						</td>
						<td>
							<?php if ( $foogallery_album ) {
								echo $edit_link;
							} else { ?>
								<input class="foogallery-album-name" value="<?php echo $album->name; ?>">
							<?php } ?>
						</td>
						<td>
							<ul class="ul-disc" style="margin: 0 0 0 20px;">
							<?php
							$import_gallery_count = 0;
							if ( is_array( $galleries ) ) {
								foreach ( $galleries as $gallery_id ) {
									if ( 'a' === substr( $gallery_id, 0, 1 ) ) {
										//we are dealing with an album inside the album
										$nested_album = $this->get_album( substr( $gallery_id, 1 ) );
										if ( $nested_album ) {
											echo '<li>';
											echo __( '[Album] ', 'foogallery' );
											echo ' <span style="text-decoration:line-through">';
											echo $nested_album->name;
											echo '</span>';
											echo ' (<span class="nextgen-import-progress-' . FooGallery_NextGen_Import_Progress::PROGRESS_ERROR . '">';
											echo __( 'nested albums not supported', 'foogallery' );
											echo '</span>)</li>';
										}
									} else {
										$nextgen_gallery = $this->get_gallery( $gallery_id );
										echo '<li>';
										$gallery_progress  = $this->get_import_progress( $gallery_id );
										$gallery_completed = $gallery_progress->is_completed();
										if ( $gallery_completed ) {
											$import_gallery_count ++;
										}
										echo $gallery_completed ? '' : '<span style="text-decoration:line-through">';
										echo $nextgen_gallery->title;
										echo $gallery_completed ? '' : '</span>';
										echo ' (<span class="nextgen-import-progress-' . $gallery_progress->status . '">';
										echo $gallery_completed ? __( 'imported', 'foogallery' ) : __( 'not imported', 'foogallery' );
										echo '</span>)</li>';
									}
								}
							} else {
								_e('No galleries in album!');
							}
							?>
							</ul>
						</td>
						<td>
							<span class="nextgen-import-progress nextgen-import-progress-<?php echo $progress->status; ?>">
								<?php echo $progress->message(); ?>
							</span>
							<?php

							echo '<br />';
							if ( !$progress->is_completed() ) {
								if ( $import_gallery_count > 0 ) {
									echo '<input type="submit" class="button button-primary start_album_import" value="Import Album">';
									echo '<div class="inline" style="width:20px"><span class="spinner"></span></div>';
									echo '<br />';
									if ( $import_gallery_count === count( $galleries ) ) {
										_e( 'All galleries will be linked', 'foogallery' );
									} else {
										echo sprintf( __( '%d/%d galleries will be linked', 'foogallery' ), $import_gallery_count, count( $galleries ) );
										echo '<br />';
										_e ( '(Only previously imported galleries can be linked)', 'foogallery' );
									}
								} else {
									_e( 'No galleries imported yet!!', 'foogallery' );
								}
							}

							?>
						</td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
			<?php
			wp_nonce_field( 'foogallery_nextgen_album_reset', 'foogallery_nextgen_album_reset', false );
			wp_nonce_field( 'foogallery_nextgen_album_import', 'foogallery_nextgen_album_import', false );

			if ( $has_imports ) { ?>
				<br />
				<input type="submit" name="foogallery_nextgen_reset_album" class="button reset_album_import" value="<?php _e( 'Reset All Album Imports', 'foogallery' ); ?>">
			<?php }
		}

		function get_import_progress_for_album( $nextgen_gallery_album_id ) {
			$progress = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS_ALBUM );
			if ( false !== $progress ) {
				if ( false !== $nextgen_gallery_album_id && array_key_exists( $nextgen_gallery_album_id, $progress ) ) {
					return $progress[ $nextgen_gallery_album_id ];
				}
			}

			return new FooGallery_NextGen_Import_Progress_Album();
		}

		function import_album( $nextgen_gallery_album_id, $foogallery_album_name ) {
			$progress = new FooGallery_NextGen_Import_Progress_Album();
			$progress->nextgen_album_id = $nextgen_gallery_album_id;
			$progress->foogallery_album_title = $foogallery_album_name;

			//create a new foogallery album
			$progress->import();

			$overall_progress = get_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS_ALBUM );
			$overall_progress[ $nextgen_gallery_album_id ] = $progress;
			update_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS_ALBUM, $overall_progress );
		}

		function reset_album_import() {
			delete_option( self::NEXTGEN_OPTION_IMPORT_PROGRESS_ALBUM );
		}

		/**
		 * Unserialize NextGEN data
		 */
		function nextgen_unserialize($value) {
			$retval = NULL;

			if ( is_string( $value ) ) {
				$retval = stripcslashes( $value );

				if ( strlen( $value ) > 1 ) {
					// We can't always rely on base64_decode() or json_decode() to return FALSE as their documentation
					// claims so check if $retval begins with a: as that indicates we have a serialized PHP object.
					if ( strpos( $retval, 'a:' ) === 0 ) {
						$er = error_reporting(0);
						$retval = unserialize($value);
						error_reporting($er);
					} else {
						// We use json_decode() here because PHP's unserialize() is not Unicode safe.
						$retval = json_decode(base64_decode($retval), TRUE);
					}
				}
			}

			return $retval;
		}

	}

}
