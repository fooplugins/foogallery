<?php
/**
 * The Gallery Datasource which pulls images from a specific folder on the server
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Folders' ) ) {

    class FooGallery_Pro_Datasource_Folders {

    	public function __construct() {
			add_action( 'foogallery_gallery_datasources', array($this, 'add_datasource'), 6 );
			add_filter( 'foogallery_datasource_folders_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_folders_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_folders_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );

			add_action( 'foogallery-datasource-modal-content_folders', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'wp_ajax_foogallery_datasource_folder_change' , array( $this, 'render_folder_structure' ) );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
		}

		/**
		 * Add the Folders Datasource
		 * @param $datasources
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['folders'] = array(
				'id'     => 'folders',
				'name'   => __( 'Server Folder', 'foogallery' ),
				'menu'  => __( 'Server Folder', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

        /**
         * Clears the cache for the specific folder
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
		 * @param int $count
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
         * @param array $attachments
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

				if ( false === $cached_attachments) {
                    $datasource_value = $foogallery->datasource_value;
                    $folder = $datasource_value['value'];

                    $expiry_hours = apply_filters( 'foogallery_datasource_folder_expiry', 24 );
                    $expiry = $expiry_hours * 60 * 60;

                    //find all image files in the folder
                    $attachments = $this->build_attachments_from_folder( $folder );

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
			return apply_filters( 'foogallery_datasource_folder_supported_image_types', array(
				'gif',
				'jpg',
				'jpeg',
				'png'
			));
		}

		/**
		 * Scans the folder and builds an array of attachments
		 * @param $folder
		 *
		 * @return array(FooGalleryAttachment)
		 */
		private function build_attachments_from_folder( $folder ) {
            global $wp_filesystem;
            $attachments = array();

            if ( ! WP_Filesystem( true ) ) {
                return $attachments;
            }

            if ( empty( $folder ) ) {
                $folder = '/';
            }

            //ensure we are always looking at a folder down from the root folder
            $root = $this->get_root_folder();
            $actual_path = rtrim( $root, '/' ) . $folder;

            if ( $wp_filesystem->exists( $actual_path ) ) {
                $json = false;

                $json_path = trailingslashit( $actual_path ) . $this->image_metadata_file();

                if ( $wp_filesystem->exists( $json_path ) ) {
                    //load json here
                    $json = @json_decode( $wp_filesystem->get_contents( $json_path ), true );
                }

                $files = $wp_filesystem->dirlist($actual_path);

                $supported_image_types = $this->supported_image_types();

                if ( count( $files ) > 0 ) {

                    foreach ($files as $file => $file_info) {
                        if ($file != '.' && $file != '..' && $file_info['type'] == 'f') {
							$ext = strtolower( preg_replace( '/^.*\./', '', $file_info['name'] ) );

							if ( in_array( $ext, $supported_image_types ) ) {
								$filename = trailingslashit( $actual_path ) . $file;
								$url = get_site_url( null, trailingslashit( $folder ) . $file );
								$size = getimagesize( $filename );

								$attachment = new FooGalleryAttachment();
								$attachment->ID = 0;
								$attachment->title = $file;
								$attachment->url = $url;
								$attachment->sort = PHP_INT_MAX;
								if ( $size !== false ) {
									$attachment->width = $size[0];
									$attachment->height = $size[1];
								}

								//extract info from the json config file in the folder
								if ( $json ) {
									$file_json = $this->find_json_data_for_file( $file, $json );

									if ( $file_json !== false ) {
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

			usort( $attachments, array( $this, 'sort_attachments') );

            return $attachments;
        }

		/**
		 * Sort the attachments according to the index
		 * @param FooGalleryAttachment $a
		 * @param FooGalleryAttachment $b
		 *
		 * @return int
		 */
        function sort_attachments( $a, $b ) {
			if ($a->sort == $b->sort) {
				return 0;
			}
			return ($a->sort < $b->sort) ? -1 : 1;
		}

		/**
		 * Sort the metadata according to the index
		 * @param array $a
		 * @param array $b
		 *
		 * @return int
		 */
		function sort_metadata( $a, $b ) {
			if ($a['index'] == $b['index']) {
				return 0;
			}
			return ($a['index'] < $b['index']) ? -1 : 1;
		}

		/**
		 * Extract the correct json data for the file
		 * @param $filename
		 * @param $json_data
		 *
		 * @return bool
		 */
        public function find_json_data_for_file( $filename, $json_data ) {
			if ( array_key_exists( 'items', $json_data ) ) {
				foreach ( $json_data['items'] as $position => $item ) {
					//allow for an index to be specified, otherwise set the index to be the position in the array
					if ( !array_key_exists( 'index', $item ) ) {
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
		 * @param FooGallery $foogallery
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
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) {

            $folder = '';
            if ( is_array( $datasource_value ) && array_key_exists( 'value', $datasource_value ) ) {
                $folder = $datasource_value['value'];
            }
            $files = '';
            if ( is_array( $datasource_value ) && array_key_exists( 'files', $datasource_value ) ) {
                $files = $datasource_value['files'];
            }
            $expiry = '';
            if ( is_array( $datasource_value ) && array_key_exists( 'expiry', $datasource_value ) ) {
                $expiry = int_val( $datasource_value['expiry'] );
            }
			?>
			<style>
				.foogallery-datasource-folder-list ul {
					list-style: none;
				}

                .foogallery-datasource-folder-list ul li a {
                    padding: 4px 12px;
                    font-size: 1.2em;
                    text-decoration: none;
                    text-align: center;
                }

                .foogallery-datasource-folder-list ul li a .spinner {
                    display: inline-block;
                    margin-left: 10px;
                    float: none;
                }

                .foogallery-datasource-folder-list ul li a.active {
					background: #bbb;
				}

                .foogallery-datasource-folder-selected {
                    padding: 3px 6px;
                    background: #efefef;
                    border-radius: 3px;
                }

				.foogallery-datasource-folder-list textarea {
					width: 500px;
					height: 500px;
				}

			</style>
			<script type="text/javascript">
				jQuery(function ($) {
					$('.foogallery-datasource-folder-list').on('click', 'ul li a', function (e) {
						e.preventDefault();

						var $this = $(this),
                            $container = $this.parents('.foogallery-datasource-folder-list:first'),
                            folder = $this.data('folder');

                        $this.append('<span class="is-active spinner"></span>');

                        $('.foogallery-datasource-folder-selected').text(folder);

						//set the selection
						document.foogallery_datasource_value_temp = {
							"value" : folder
						};

						$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

                        var data = 'action=foogallery_datasource_folder_change' +
                            '&folder=' + encodeURIComponent(folder) +
                            '&nonce=<?php echo wp_create_nonce( 'foogallery_datasource_folder_change' ); ?>';

                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function(data) {
                                $this.find('.spinner').remove();
                                $container.html(data);
                            }
                        });
					});
				});
			</script>
			<p><?php _e('Select a folder from the server below. The gallery will then dynamically load all images that are inside the selected folder.', 'foogallery'); ?></p>
            <p><?php _e('Selected Folder : ', 'foogallery'); ?><span class="foogallery-datasource-folder-selected"><?php echo empty($folder) ? __('nothing yet', 'foogallery') : $folder; ?></span></p>
            <div class="foogallery-datasource-folder-list">
            <?php
            $this->render_filesystem_tree( $folder );
            ?>
            </div><?php
		}

		function get_root_folder() {
			return trailingslashit( apply_filters( 'foogallery_filesystem_root', ABSPATH ) );
		}

		function render_folder_structure() {
            if ( check_admin_referer( 'foogallery_datasource_folder_change', 'nonce' ) ) {
                $folder = $_POST['folder'];
                $this->render_filesystem_tree( $folder );
            }

            die();
        }

		function render_filesystem_tree( $path = '/' ) {
            global $wp_filesystem;
            // setup wp_filesystem api
            if ( ! WP_Filesystem( true ) ) {
                return false;
            }

            if ( empty( $path ) ) {
            	$path = '/';
			}

			//ensure we are always looking at a folder down from the root folder
            $root = $this->get_root_folder();
            $actual_path = rtrim( $root, '/' ) . $path;

			echo '<ul>';

			//only show the UP folder if we are not at the root.
			//We do not want the user to be able to go past the root
			if ( $path !== '/' ) {
				$up_folder = substr( $path, 0, strrpos( $path, '/' ) );
				if ( empty( $up_folder ) ) {
					$up_folder = '/';
				}
				echo '<li><a title="' . __( 'Go up a level', 'foogallery' ) . '" href="#" data-folder="' . esc_attr( $up_folder ) . '"><span class="dashicons dashicons-category" />..</a></li>';
			}

			$folder_exists = $wp_filesystem->exists( $actual_path );
			$image_count = 0;
			$image_metadata_count = 0;
			$metadata_file_exists = false;
			$metadata_array = array();
			$json = false;

			if ( $folder_exists ) {
				$json_path = trailingslashit( $actual_path ) . $this->image_metadata_file();

				$json_last_error = false;
				$json_last_error_code = 0;

				if ( $wp_filesystem->exists( $json_path ) ) {
					//load json here
					$metadata_file_exists = true;
					$json = @json_decode( $wp_filesystem->get_contents( $json_path ), true );
					$json_last_error_code = json_last_error();
					$json_last_error = json_last_error_msg();
				}

				$files = $wp_filesystem->dirlist( $actual_path );
				if ( count( $files ) > 0 ) {
					// build separate arrays for folders and files
					$dir_array  = array();

					foreach ( $files as $file => $file_info ) {
						if ( $file != '.' && $file != '..' && $file_info['type'] == 'd' ) {
							$file_string             = strtolower( preg_replace( "[._-]", "", $file ) );
							$dir_array[$file_string] = $file_info;
						} elseif ( $file != '.' && $file != '..' && $file_info['type'] == 'f' ) {
							//dealing with a file.

							//Check if the file is an image
							$ext = strtolower( preg_replace( '/^.*\./', '', $file_info['name'] ) );
							if ( in_array( $ext, $this->supported_image_types() ) ) {
								$image_count ++;

								$metadata = array(
									'file' => $file,
									'caption' => '',
									'description' => '',
									'alt' => '',
									'custom_url' => '',
									'custom_target' => '',
									'index' => PHP_INT_MAX
								);

								//check if we have metadata for the file
								if ( $json ) {
									$file_json = $this->find_json_data_for_file( $file, $json );
									if ( $file_json !== false ) {
										$image_metadata_count++;

										if ( array_key_exists( 'caption', $file_json ) ) {
											$metadata['caption'] = $file_json['caption'];
										}
										if ( array_key_exists( 'description', $file_json ) ) {
											$metadata['description'] = $file_json['description'];
										}
										if ( array_key_exists( 'alt', $file_json ) ) {
											$metadata['alt'] = $file_json['alt'];
										}
										if ( array_key_exists( 'custom_url', $file_json ) ) {
											$metadata['custom_url'] = $file_json['custom_url'];
										}
										if ( array_key_exists( 'custom_target', $file_json ) ) {
											$metadata['custom_target'] = $file_json['custom_target'];
										}
										if ( array_key_exists( 'index', $file_json ) ) {
											$metadata['index'] = intval( $file_json['index'] );
										}
									}
								}

								$metadata_array[] = $metadata;
							}
						}
					}
					// sort the metadata correctly
					usort( $metadata_array, array( $this, 'sort_metadata') );

					// Remove any indexes
					foreach ( $metadata_array as $position => &$metadata ) {
						unset( $metadata['index'] );
					}

					// sort the folders
					ksort( $dir_array );

					// output all folders
					foreach ( $dir_array as $file => $file_info ) {
						$folder = trailingslashit( $path ) . $file_info['name'];
						echo '<li><a href="#" data-folder="' . esc_attr( $folder ) . '"><span class="dashicons dashicons-category" />' . esc_html( $file_info['name'] ) . '</a></li>';
					}
				}
			}
			echo '</ul>';

			if ( $folder_exists ) {
				echo '<p>' . sprintf( __( '%s images found in the folder.', 'foogallery' ), $image_count ) . '</p>';

				if ( $image_count > 0 ) {
					if ( $metadata_file_exists ) {
						if ( $json_last_error_code !== JSON_ERROR_NONE ) {
							echo '<p><strong>' . __( 'ERROR reading metadata file!', 'foogallery' ) . '</strong></p>';
							echo '<p>' . sprintf( __( 'There was a problem reading metadata from %s. Please check that the file contains valid JSON. You can use a website like %s to help validate your JSON data.', 'foogallery' ), $this->image_metadata_file(), '<a href="https://jsonlint.com/" target="_blank">JSONLint</a>') . '</p>';
							echo '<p>' . __( 'Error : ', 'foogallery' ) . $json_last_error . '</p>';
						} else {
							if ( $image_count > $image_metadata_count ) {
								//there is missing metadata
								echo '<p><strong>' . sprintf( __( 'There are %d images with missing metadata!', 'foogallery' ), $image_count - $image_metadata_count ) . '</strong></p>';
							}
						}
					} else {
						echo '<p><strong>' . __( 'NO metadata file found in folder!', 'foogallery' ) . '</strong></p>';
						echo '<p>' . sprintf( __( 'We read JSON metadata information about each image from the file (%s) which you need to save to the same folder.', 'foogallery' ), '<i>' . $this->image_metadata_file() . '</i>' );
						echo '<br />';
						//echo sprintf( __('Save a json file in the folder named %s and we will extract all the image metadata for each image.', 'foogallery' ), $this->image_metadata_file() );
						echo '</p>';
					}

					if ( $image_count > $image_metadata_count ) {
						echo '<p>' .  __( 'Below is the automatically generated JSON metadata for the images found in the folder.', 'foogallery') . '</p>';
						echo '<p>' .  sprintf( __( 'To use it: copy the metadata, change the info for each image, save it to a file named %s, and finally transfer/FTP the file into the same folder on your server.', 'foogallery'), $this->image_metadata_file() ) . '</p>';
					} else {
						echo '<p>' .  sprintf( __( 'Below is the JSON metadata read from %s', 'foogallery'), $this->image_metadata_file() ) . '</p>';
					}
					echo '<textarea>';
					echo json_encode( array( 'items' => $metadata_array ), JSON_PRETTY_PRINT );
					echo '</textarea>';
				}
			}
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
         * @param FooGallery $gallery
         */
		function render_datasource_item( $gallery ) { ?>
            <style type="text/css">
                .foogallery-datasource-folder {
                    padding: 20px;
                    text-align: center;
                }

				.foogallery-datasource-folder .foogallery-items-html {
					background: #efefef;
					border-radius: 5px;
					display: inline-block;
					padding: 4px 12px;
					text-align: center;
					text-decoration: none;
					font-size: 1.2em;
					margin-bottom: 20px;
				}
            </style>
            <script type="text/javascript">


                jQuery(function ($) {
                    $('.foogallery-datasource-folder').on('click', 'button.remove', function (e) {
                        e.preventDefault();

                        //hide the previous info
                        $(this).parents('.foogallery-datasource-folder').hide();

                        //clear the datasource value
                        $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');

                        //clear the datasource
                        $('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val('');

                        //make sure the modal insert button is not active
                        $('.foogallery-datasource-modal-insert').attr('disabled','disabled');

                        FOOGALLERY.showHiddenAreas( true );

                        //ensure the preview will be refreshed
                        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                    });

                    $('.foogallery-datasource-folder').on('click', 'button.edit', function (e) {
                        e.preventDefault();

                        //show the modal
                        $('.foogallery-datasources-modal-wrapper').show();

                        //select the folders datasource
                        $('.foogallery-datasource-modal-selector[data-datasource="folders"]').click();
                    });

					$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
						$('.foogallery-datasource-folder').hide();

						if ( activeDatasource !== 'folders' ) {
							//clear the selected folder
						}
					});

                    $(document).on('foogallery-datasource-changed-folders', function() {
                        var $container = $('.foogallery-datasource-folder');

						$('#_foogallery_datasource_value').val(JSON.stringify(document.foogallery_datasource_value_temp));

						$container.find('.foogallery-items-html').html(document.foogallery_datasource_value_temp.value);

						$container.show();

						FOOGALLERY.showHiddenAreas( false );

						$('.foogallery-attachments-list').addClass('hidden');

						$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                    });
                });
            </script>
        <?php
			$show_container = isset( $gallery->datasource_name) && 'folders' === $gallery->datasource_name;
			$value = ($show_container && isset( $gallery->datasource_value['value'] )) ? $gallery->datasource_value['value'] : '';
			?>
			<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-folder">
				<h3><?php _e('Datasource : Server Folder', 'foogallery'); ?></h3>
				<p><?php _e('This gallery will be dynamically populated with all images within the following folder on your server:', 'foogallery'); ?></p>
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
