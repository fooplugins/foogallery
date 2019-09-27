<?php
/**
 * The Gallery Datasource which pulls images from a specific folder on the server
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Folders' ) ) {

	class FooGallery_Pro_Datasource_Folders {

		public function __construct() {
			add_action( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ), 6 );
			add_filter( 'foogallery_datasource_folders_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_folders_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_folders_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );
			add_action( 'foogallery-datasource-modal-content_folders', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'wp_ajax_foogallery_datasource_folder_change', array( $this, 'render_folder_structure' ) );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
		}

		/**
		 * Add the Folders Datasource
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['folders'] = array(
				'id'     => 'folders',
				'name'   => __( 'Server Folder', 'foogallery' ),
				'menu'   => __( 'Server Folder', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

		/**
		 * Enqueues folders-specific assets
		 */
		public function enqueue_scripts_and_styles() {
			wp_enqueue_style( 'foogallery.admin.datasources.folders', FOOGALLERY_PRO_URL . 'css/foogallery.admin.datasources.folders.css', array(), FOOGALLERY_VERSION );
			wp_enqueue_script( 'foogallery.admin.datasources.folders', FOOGALLERY_PRO_URL . 'js/foogallery.admin.datasources.folders.js', array( 'jquery' ), FOOGALLERY_VERSION );
		}

		/**
		 * Clears the cache for the specific folder
		 *
		 * @param $foogallery_id
		 */
		public function before_save_gallery_datasource_clear_datasource_cached_images( $foogallery_id ) {
			$this->clear_gallery_transient( $foogallery_id );
		}

		public function clear_gallery_transient( $foogallery_id ) {
			$transient_key = '_foogallery_datasource_folder_' . $foogallery_id;
			delete_transient( $transient_key );
		}

		/**
		 * Returns the number of attachments used for the gallery
		 *
		 * @param int        $count
		 * @param FooGallery $foogallery
		 *
		 * @return int
		 */
		public function get_gallery_attachment_count( $count, $foogallery ) {
			return count( $this->get_gallery_attachments_from_folder( $foogallery ) );
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 *
		 * @param array      $attachments
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments( $attachments, $foogallery ) {
			return $this->get_gallery_attachments_from_folder( $foogallery );
		}

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments_from_folder( $foogallery ) {
			global $foogallery_gallery_preview;

			$attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
				$transient_key = '_foogallery_datasource_folder_' . $foogallery->ID;

				//never get the cached results if we are doing a preview
				if ( isset( $foogallery_gallery_preview ) ) {
					$cached_attachments = false;
				} else {
					$cached_attachments = get_transient( $transient_key );
				}

				if ( false === $cached_attachments ) {
					$datasource_value = $foogallery->datasource_value;
					$folder           = $datasource_value['value'];
					if ( array_key_exists( 'metadata', $datasource_value ) ) {
						$metadata = $datasource_value['metadata'];
					} else {
						$metadata = 'file'; //set the default metadata source to be the server file
					}

					$expiry_hours = apply_filters( 'foogallery_datasource_folder_expiry', 24 );
					$expiry       = $expiry_hours * 60 * 60;

					//find all image files in the folder
					$attachments = $this->build_attachments_from_folder( $folder, $metadata );

					//save a cached list of attachments
					set_transient( $transient_key, $attachments, $expiry );
				} else {
					$attachments = $cached_attachments;
				}
			}

			return $attachments;
		}

		/**
		 * returns the supported image types that will be pulled from a folder
		 *
		 * @return array
		 */
		public function supported_image_types() {
			return apply_filters(
				'foogallery_datasource_folder_supported_image_types', array(
				'gif',
				'jpg',
				'jpeg',
				'png'
			)
			);
		}

		/**
		 * Generates the option key used to store the metadata for a folder
		 *
		 * @param $folder
		 *
		 * @return string
		 */
		private function build_database_options_key( $folder ) {
			return 'foogallery_folder_metadata' . foo_convert_to_key( $folder );
		}

		/**
		 * Scans the folder and builds an array of attachments
		 *
		 * @param        $folder
		 *
		 * @param string $metadata_source
		 *
		 * @return array(FooGalleryAttachment)
		 */
		private function build_attachments_from_folder( $folder, $metadata_source = 'file' ) {
			global $wp_filesystem;
			$attachments = array();

			if ( ! WP_Filesystem( true ) ) {
				return $attachments;
			}

			if ( empty( $folder ) ) {
				$folder = '/';
			}

			//ensure we are always looking at a folder down from the root folder
			$root        = $this->get_root_folder();
			$actual_path = rtrim( $root, '/' ) . $folder;

			if ( $wp_filesystem->exists( $actual_path ) ) {
				$json = false;

				if ( 'database' === $metadata_source ) {
					//load metadata from the database
					$option_key = $this->build_database_options_key( $folder );
					$json       = get_option( $option_key );
				} else {
					//load json from the file on the server
					$json_path = trailingslashit( $actual_path ) . $this->image_metadata_file();

					if ( $wp_filesystem->exists( $json_path ) ) {
						//load json here
						$json = @json_decode( $wp_filesystem->get_contents( $json_path ), true );
					}
				}

				$files = $wp_filesystem->dirlist( $actual_path );

				$supported_image_types = $this->supported_image_types();

				if ( count( $files ) > 0 ) {

					foreach ( $files as $file => $file_info ) {
						if ( $file != '.' && $file != '..' && $file_info['type'] == 'f' ) {
							$ext = strtolower( preg_replace( '/^.*\./', '', $file_info['name'] ) );

							if ( in_array( $ext, $supported_image_types ) ) {
								$filename = trailingslashit( $actual_path ) . $file;
								$url      = get_site_url( null, trailingslashit( $folder ) . $file );
								$size     = getimagesize( $filename );

								$attachment               = new FooGalleryAttachment();
								$attachment->ID           = 0;
								$attachment->title        = $file;
								$attachment->url          = $url;
								$attachment->has_metadata = false;
								$attachment->sort         = PHP_INT_MAX;
								if ( $size !== false ) {
									$attachment->width  = $size[0];
									$attachment->height = $size[1];
								}

								//extract info from the json config file in the folder
								if ( $json ) {
									$file_json = $this->find_json_data_for_file( $file, $json );

									if ( $file_json !== false ) {
										$attachment->has_metadata = true;

										if ( array_key_exists( 'missing', $file_json ) ) {
											$attachment->has_metadata = false;
										}
										if ( array_key_exists( 'caption', $file_json ) ) {
											$attachment->caption = $file_json['caption'];
										}
										if ( array_key_exists( 'description', $file_json ) ) {
											$attachment->description = $file_json['description'];
										}
										if ( array_key_exists( 'alt', $file_json ) ) {
											$attachment->alt = $file_json['alt'];
										}
										if ( array_key_exists( 'custom_url', $file_json ) ) {
											$attachment->custom_url = $file_json['custom_url'];
										}
										if ( array_key_exists( 'custom_target', $file_json ) ) {
											$attachment->custom_target = $file_json['custom_target'];
										}
										if ( array_key_exists( 'index', $file_json ) ) {
											$attachment->sort = intval( $file_json['index'] );
										}
									}
								}

								$attachments[] = $attachment;
							}
						}
					}
				}
			}

			usort( $attachments, array( $this, 'sort_attachments' ) );

			return $attachments;
		}

		/**
		 * Sort the attachments according to the index
		 *
		 * @param FooGalleryAttachment $a
		 * @param FooGalleryAttachment $b
		 *
		 * @return int
		 */
		function sort_attachments( $a, $b ) {
			if ( $a->sort == $b->sort ) {
				return 0;
			}

			return ( $a->sort < $b->sort ) ? - 1 : 1;
		}

		/**
		 * Sort the metadata according to the index
		 *
		 * @param array $a
		 * @param array $b
		 *
		 * @return int
		 */
		function sort_metadata( $a, $b ) {
			if ( $a['index'] == $b['index'] ) {
				return 0;
			}

			return ( $a['index'] < $b['index'] ) ? - 1 : 1;
		}

		/**
		 * Extract the correct json data for the file
		 *
		 * @param $filename
		 * @param $json_data
		 *
		 * @return bool
		 */
		public function find_json_data_for_file( $filename, $json_data ) {
			if ( array_key_exists( 'items', $json_data ) ) {
				foreach ( $json_data['items'] as $position => $item ) {
					//allow for an index to be specified, otherwise set the index to be the position in the array
					if ( ! array_key_exists( 'index', $item ) ) {
						$item['index'] = $position;
					}

					if ( array_key_exists( 'file', $item ) && $item['file'] === $filename ) {
						return $item;
					}
				}
			}

			return false;
		}

		/**
		 * Returns the featured FooGalleryAttachment from the datasource
		 *
		 * @param FooGalleryAttachment $default
		 * @param FooGallery           $foogallery
		 *
		 * @return bool|FooGalleryAttachment
		 */
		public function get_gallery_featured_attachment( $default, $foogallery ) {
			$attachments = $this->get_gallery_attachments_from_folder( $foogallery );
			if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
				return $attachments[0];
			}

			return false;
		}

		/**
		 * Output the datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) {

			$folder = '';
			if ( is_array( $datasource_value ) && array_key_exists( 'value', $datasource_value ) ) {
				$folder = $datasource_value['value'];
			}
			$metadata_source = 'file';
			if ( is_array( $datasource_value ) && array_key_exists( 'metadata', $datasource_value ) ) {
				$metadata_source = $datasource_value['metadata'];
			}
			?>
			<script type="text/javascript">
				document.foogalleryDatasourceFolderNonce = '<?php echo wp_create_nonce( "foogallery_datasource_folder_change" ); ?>';
			</script>
			<p><?php _e( 'Select which folder on the server you want to load images from. You also need to choose the source of the image metadata. Image metadata allows you to change the caption title, caption description, alt text and the order of the images. This metadata can be read from a file on the server (metadata.json) or from the WordPress database.', 'foogallery' ); ?></p>
			<p class="foogallery-datasource-folder-metadata-selector">
				<?php _e( 'Image Metadata : ', 'foogallery' ); ?>
				<input type="radio" name="foogallery-datasource-folder-metadata" id="foogallery-datasource-folder-metadata-file" value="file" <?php echo $metadata_source === 'file' ? 'checked="checked"' : ''; ?>>
				<label for="foogallery-datasource-folder-metadata-file"><?php _e( 'Load from file on server', 'foogallery' ); ?></label>
				<input type="radio" name="foogallery-datasource-folder-metadata" id="foogallery-datasource-folder-metadata-database" value="database" <?php echo $metadata_source === 'database' ? 'checked="checked"' : ''; ?>>
				<label for="foogallery-datasource-folder-metadata-database"><?php _e( 'Load from WordPress database', 'foogallery' ); ?></label>
				<span class="spinner"></span>
			</p>
			<p style="clear: both"><?php _e( 'Selected Folder : ', 'foogallery' ); ?>
				<span class="foogallery-datasource-folder-selected"><?php echo empty( $folder ) ? __( 'nothing yet', 'foogallery' ) : $folder; ?></span>
			</p>
			<div class="foogallery-datasource-folder-container">
				<?php
				$this->render_folder( $folder, $metadata_source );
				?>
			</div>
			<?php
		}

		function get_root_folder() {
			return trailingslashit( apply_filters( 'foogallery_filesystem_root', ABSPATH ) );
		}

		function render_folder_structure() {
			if ( check_admin_referer( 'foogallery_datasource_folder_change', 'nonce' ) ) {
				$folder   = urldecode( $_POST['folder'] );
				$metadata = $_POST['metadata'];

				if ( array_key_exists( 'json', $_POST ) ) {
					$json = $_POST['json'];
					//save the json for the folder
					$option_key = $this->build_database_options_key( $folder );
					update_option( $option_key, $json );
				}

				if ( array_key_exists( 'clear', $_POST ) ) {
					$option_key = $this->build_database_options_key( $folder );
					delete_option( $option_key );
				}

				$this->render_folder( $folder, $metadata );
			}

			die();
		}

		/**
		 * Renders the metadata that is found for the folder
		 *
		 * @param array $attachments
		 *
		 * @return void
		 */
		function render_metadata( $attachments, $folder, $metadata_source ) {

			

			$image_count          = count( $attachments );
			$image_metadata_count = 0;

			if ( $image_count === 0 ) {
				return;
			}

			$metadata_array = array();

			foreach ( $attachments as $attachment ) {
				$metadata = array(
					'file'          => $attachment->title,
					'caption'       => '',
					'description'   => '',
					'alt'           => '',
					'custom_url'    => '',
					'custom_target' => ''
				);

				if ( isset( $attachment->has_metadata ) && $attachment->has_metadata ) {
					$image_metadata_count ++;
					$metadata['caption']       = $attachment->caption;
					$metadata['description']   = $attachment->description;
					$metadata['alt']           = $attachment->alt;
					$metadata['custom_url']    = $attachment->custom_url;
					$metadata['custom_target'] = $attachment->custom_target;
				}
				$metadata_array[] = $metadata;
			}

			if ( 'database' === $metadata_source ) {
				$option_key    = $this->build_database_options_key( $folder );
				$metadata_data = get_option( $option_key );
				if ( false !== $metadata_data ) {
					echo '<p>' . sprintf( __( 'Loading metadata from WordPress database option %s', 'foogallery' ), '<code>' . $option_key . '</code>' );
					echo ' <a href="#" class="foogallery-server-image-metadata-clear">' . __( 'Clear Metadata', 'foogallery' ) . '</a>';
					echo '</p>';
				}

				if ( $image_count > $image_metadata_count ) {
					//there is missing metadata
					echo '<p><strong>' . sprintf( __( 'There are %d images with missing metadata!', 'foogallery' ), $image_count - $image_metadata_count ) . '</strong></p>';
				}

				echo '<p>' . __( 'Change the sort order of the images by drag and drop. Click the "i" icon to change the caption and other metadata for each image.', 'foogallery' ) . '</p>';
				echo '<div class="foogallery-server-image-metadata-form"><form><h2>' . __( 'Edit Image Metadata', 'foogallery' ) . '</h2>';
				echo '<p><label>' . __( 'File', 'foogallery' ) . '</label><span id="foogallery-server-image-metadata-form-file">filename.jpg</span></p>';
				echo '<p><label for="foogallery-server-image-metadata-form-caption">' . __( 'Caption', 'foogallery' ) . '</label><textarea name="caption" id="foogallery-server-image-metadata-form-caption" /></p>';
				echo '<p><label for="foogallery-server-image-metadata-form-description">' . __( 'Description', 'foogallery' ) . '</label><textarea name="description" id="foogallery-server-image-metadata-form-description" /></p>';
				echo '<p><label for="foogallery-server-image-metadata-form-alt">' . __( 'Alt Text', 'foogallery' ) . '</label><input type="text" name="alt" id="foogallery-server-image-metadata-form-alt" /></p>';
				echo '<p><label for="foogallery-server-image-metadata-form-custom_url">' . __( 'Custom URL', 'foogallery' ) . '</label><input type="text" name="custom_url" id="foogallery-server-image-metadata-form-custom_url" /></p>';
				echo '<p><label for="foogallery-server-image-metadata-form-custom_target">' . __( 'Custom Target', 'foogallery' ) . '</label><input type="text" name="custom_target" id="foogallery-server-image-metadata-form-custom_target" /></p>';
				echo '<p style="text-align: center">';
				echo '<a href="#" class="foogallery-server-image-metadata-form-button-cancel button button-large">' . __( 'Cancel', 'foogallery' ) . '</a>';
				echo '<a href="#" class="foogallery-server-image-metadata-form-button-save button button-large button-primary">' . __( 'Save', 'foogallery' ) . '</a>';
				echo '<a href="#" class="foogallery-server-image-metadata-form-button-next button button-large button-primary">' . __( 'Save &amp; Next', 'foogallery' ) . '</a>';
				echo '</p>';
				echo '</form></div>';
				echo '<a style="display: none;" href="#" class="foogallery-server-image-metadata-save button button-large button-primary">' . __( 'Save Metadata', 'foogallery' ) . '</a>';
			} else {
				global $wp_filesystem;
				// setup wp_filesystem api
				if ( ! WP_Filesystem( true ) ) {
					return false;
				}

				if ( empty( $folder ) ) {
					$folder = '/';
				}

				//ensure we are always looking at a folder down from the root folder
				$root                 = $this->get_root_folder();
				$actual_path          = rtrim( $root, '/' ) . $folder;
				$folder_exists        = $wp_filesystem->exists( $actual_path );
				$metadata_file_exists = false;

				if ( $folder_exists ) {
					$json_path = trailingslashit( $actual_path ) . $this->image_metadata_file();

					$json_last_error      = false;
					$json_last_error_code = 0;

					if ( $wp_filesystem->exists( $json_path ) ) {
						echo '<p>' . sprintf( __( 'Loading metadata from %s', 'foogallery' ), '<code>' . trailingslashit( $folder ) . $this->image_metadata_file() . '</code>' ) . '</p>';

						//load json here
						$metadata_file_exists = true;
						$json                 = @json_decode( $wp_filesystem->get_contents( $json_path ), true );
						$json_last_error_code = json_last_error();
						$json_last_error      = json_last_error_msg();
					}

					if ( $metadata_file_exists ) {
						if ( $json_last_error_code !== JSON_ERROR_NONE ) {
							echo '<p><strong>' . __( 'ERROR reading metadata file!', 'foogallery' ) . '</strong></p>';
							echo '<p>' . sprintf( __( 'There was a problem reading metadata from %s. Please check that the file contains valid JSON. You can use a website like %s to help validate your JSON data.', 'foogallery' ), $this->image_metadata_file(), '<a href="https://jsonlint.com/" target="_blank">JSONLint</a>' ) . '</p>';
							echo '<p>' . __( 'Error : ', 'foogallery' ) . $json_last_error . '</p>';
						} else {
							if ( $image_count > $image_metadata_count ) {
								//there is missing metadata
								echo '<p><strong>' . sprintf( __( 'There are %d images with missing metadata!', 'foogallery' ), $image_count - $image_metadata_count ) . '</strong></p>';
							} else {
								echo '<p><strong>' . __( 'Woohoo! There is no missing metadata!', 'foogallery' ) . '</strong></p>';
							}
						}
					} else {
						echo '<p><strong>' . __( 'NO metadata file found in folder!', 'foogallery' ) . '</strong></p>';
						echo '<p>' . sprintf( __( 'We read JSON metadata information about each image from the file (%s) which you need to save to the same folder.', 'foogallery' ), '<i>' . $this->image_metadata_file() . '</i>' );
						echo '<br />';
						echo '</p>';
					}

					if ( $image_metadata_count === 0 ) {
						echo '<p>' . __( 'Below is the automatically generated JSON metadata for all images found in the folder.', 'foogallery' ) . '</p>';
						echo '<p>' . sprintf( __( 'To use it: copy the JSON data below, change it to your liking, save it to a file named %s, and finally upload/transfer/FTP the file into the same folder on your server.', 'foogallery' ), $this->image_metadata_file() ) . '</p>';
					} else if ( $image_count > $image_metadata_count ) {
						echo '<p>' . __( 'Below is the automatically generated JSON metadata, merged together with the existing metadata read from file:', 'foogallery' ) . '</p>';
					} else {
						echo '<p>' . __( 'Below is the JSON metadata read from file:', 'foogallery' ) . '</p>';
					}

					echo '<textarea>';
					echo json_encode( array( 'items' => $metadata_array ), JSON_PRETTY_PRINT );
					echo '</textarea>';

					echo '<p>' . __( 'Please note : you can change the sort order of the images by changing the order of the metadata within the metadata file.', 'foogallery' ) . '</p>';
				}
			}
		}

		/**
		 * Renders the images that are found for the folder
		 *
		 * @param array $attachments
		 *
		 * @return void
		 */
		function render_images( $attachments, $metadata_source ) {
			$image_count = count( $attachments );
			$editable    = $metadata_source === 'database';

			if ( $image_count > 0 ) {
				echo '<p>' . sprintf( __( '%s images found in the folder.', 'foogallery' ), $image_count ) . '</p>';
				echo '<ul class="foogallery-server-image-list' . ( $editable ? ' sortable' : '' ) . '">';
				foreach ( $attachments as $attachment ) {
					$has_metadata = isset( $attachment->has_metadata ) && $attachment->has_metadata;
					echo '<li title="' . esc_attr( $attachment->title ) . '" class="' . ( $has_metadata ? '' : 'has_missing_metadata' ) . '">';
					if ( $editable ) {
						echo '<a href="#" class="foogallery-server-image-list-edit" title="' . __( 'Edit the metadata for this image', 'foogallery' ) . '"><span class="dashicons dashicons-info"></span></a>';
					}
					echo '<span title="' . __( 'Missing Metadata!', 'foogallery' ) . '" class="missing dashicons dashicons-warning"></span>';

					echo '<img width="100" height="100" src="' . esc_url( $attachment->url ) . '" ';
					echo 'data-file="' . esc_attr( $attachment->title ) . '" ';
					echo 'data-caption="' . esc_attr( $attachment->caption ) . '" ';
					echo 'data-description="' . esc_attr( $attachment->description ) . '" ';
					echo 'data-alt="' . esc_attr( $attachment->alt ) . '" ';
					echo 'data-custom-url="' . esc_url( $attachment->custom_url ) . '" ';
					echo 'data-custom-target="' . esc_attr( $attachment->custom_target ) . '" ';
					echo '/></li>';
				}
				echo '</ul><div style="clear: both"></div>';
			} else {
				echo '<p>' . __( 'No images found in the folder.', 'foogallery' ) . '</p>';
			}
		}

		/**
		 * Renders the server folder tree structure to navigate
		 *
		 * @param string $folder
		 *
		 * @return void
		 */
		function render_filesystem_tree( $folder = '/' ) {
			global $wp_filesystem;
			// setup wp_filesystem api
			if ( ! WP_Filesystem( true ) ) {
				return false;
			}

			if ( empty( $folder ) ) {
				$folder = '/';
			}

			//ensure we are always looking at a folder down from the root folder
			$root        = $this->get_root_folder();
			$actual_path = rtrim( $root, '/' ) . $folder;

			echo '<ul>';

			//only show the UP folder if we are not at the root.
			//We do not want the user to be able to go past the root
			if ( $folder !== '/' ) {
				$up_folder = substr( $folder, 0, strrpos( $folder, '/' ) );
				if ( empty( $up_folder ) ) {
					$up_folder = '/';
				}
				echo '<li><a title="' . __( 'Go up a level', 'foogallery' ) . '" href="#" data-folder="' . esc_attr( $up_folder ) . '"><span class="dashicons dashicons-category" />..</a></li>';
			}

			$folder_exists = $wp_filesystem->exists( $actual_path );

			if ( $folder_exists ) {
				$files = $wp_filesystem->dirlist( $actual_path );
				if ( count( $files ) > 0 ) {
					// build separate arrays for folders and files
					$dir_array = array();

					foreach ( $files as $file => $file_info ) {
						if ( $file != '.' && $file != '..' && $file_info['type'] == 'd' ) {
							$file_string             = strtolower( preg_replace( "[._-]", "", $file ) );
							$dir_array[$file_string] = $file_info;
						}
					}

					// sort the folders
					ksort( $dir_array );

					// output all folders
					foreach ( $dir_array as $file => $file_info ) {
						$dir = trailingslashit( $folder ) . $file_info['name'];
						echo '<li><a href="#" data-folder="' . esc_attr( $dir ) . '"><span class="dashicons dashicons-category" />' . esc_html( $file_info['name'] ) . '</a></li>';
					}
				}
			}
			echo '</ul>';
		}

		/**
		 * Renders all the details for a server folder
		 *
		 * @param string $folder
		 * @param string $metadata
		 */
		function render_folder( $folder = '/', $metadata_source = 'file' ) {
			$attachments = $this->build_attachments_from_folder( $folder, $metadata_source );
			?>
			<div class="foogallery-datasource-folder-list">
				<?php $this->render_filesystem_tree( $folder ); ?>
			</div>
			<div class="foogallery-datasource-folder-images">
				<?php $this->render_images( $attachments, $metadata_source ); ?>
			</div>
			<div class="foogallery-datasource-folder-metadata">
				<?php $this->render_metadata( $attachments, $folder, $metadata_source ); ?>
			</div>
			<?php
		}

		/**
		 * Return the filename for the images metadata
		 * @return string
		 */
		private function image_metadata_file() {
			return apply_filters( 'foogallery_datasource_folders_json', 'metadata.json' );
		}

		/**
		 * Output the html required by the datasource in order to add item(s)
		 *
		 * @param FooGallery $gallery
		 */
		function render_datasource_item( $gallery ) { ?>
			<?php
			$show_container = isset( $gallery->datasource_name ) && 'folders' === $gallery->datasource_name;
			$value          = ( $show_container && isset( $gallery->datasource_value['value'] ) ) ? $gallery->datasource_value['value'] : '';
			?>
		<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-folder">
			<h3><?php _e( 'Datasource : Server Folder', 'foogallery' ); ?></h3>
			<p><?php _e( 'This gallery will be dynamically populated with all images within the following folder on your server:', 'foogallery' ); ?></p>
			<div class="foogallery-items-html"><?php echo $value ?></div>
			<br />
			<button type="button" class="button edit">
				<?php _e( 'Change Folder', 'foogallery' ); ?>
			</button>
			<button type="button" class="button remove">
				<?php _e( 'Remove Folder', 'foogallery' ); ?>
			</button>
			</div><?php
		}
	}
}

