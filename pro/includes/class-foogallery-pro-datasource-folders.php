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
            return count( $this->get_images_from_folder( $foogallery ) );
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_images_from_folder( $foogallery ) {
            global $foogallery_gallery_preview;

            $attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
                $transient_key = '_foogallery_datasource_folder_' . $foogallery->ID;

                $cached_attachments = get_transient( $transient_key );

				if ( false === $cached_attachments) {
                    $datasource_value = $foogallery->datasource_value;
                    $folder = $datasource_value['folder'];
                    $files = $datasource_value['files'];
                    $expiry_minutes = int_val( $datasource_value['expiry'] );

                    //find all image files in the folder

					//save a cached list of attachments
					set_transient( $transient_key, $attachments, $expiry_minutes * 60 );
				} else {
					$attachments = $cached_attachments;
				}
			}

			return $attachments;
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
            $attachments = $this->get_images_from_folder( $foogallery );
			if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
				return FooGalleryAttachment::get_by_id( $attachments[0]->ID );
			}

			return false;
		}

		/**
		 * Output the datasource modal content
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) {

            $folder = '';
            if ( is_array( $datasource_value ) && array_key_exists( 'folder', $datasource_value ) ) {
                $folder = $datasource_value['folder'];
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
						$('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val( JSON.stringify( {
							"value" : folder
						} ) );

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
            $this->render_filesystem_tree();
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
            $url         = wp_nonce_url( '/', 'foogallery-datasource-folders' );
            $creds       = request_filesystem_credentials( $url, FS_METHOD, false, false, null );
            if ( false === $creds ) {
                // no credentials yet, just produced a form for the user to fill in
                return true; // stop the normal page form from displaying
            }
            if ( ! WP_Filesystem( $creds ) ) {
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
				echo '<li><a href="#" data-folder="' . esc_attr( $up_folder ) . '"><span class="dashicons dashicons-category" />..</a></li>';
			}

			$file_array = array();

			$folder_exists = $wp_filesystem->exists( $actual_path );

			if ( $folder_exists ) {
				$files = $wp_filesystem->dirlist( $actual_path );
				if ( count( $files ) > 0 ) {
					// build separate arrays for folders and files
					$dir_array  = array();

					foreach ( $files as $file => $file_info ) {
						if ( $file != '.' && $file != '..' && $file_info['type'] == 'd' ) {
							$file_string             = strtolower( preg_replace( "[._-]", "", $file ) );
							$dir_array[$file_string] = $file_info;
						} elseif ( $file != '.' && $file != '..' && $file_info['type'] == 'f' ) {
							$file_string              = strtolower( preg_replace( "[._-]", "", $file ) );
							$file_array[$file_string] = $file_info;
						}
					}
					// shot those arrays
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
				$image_count = 0;

				$supported_image = array(
					'gif',
					'jpg',
					'jpeg',
					'png'
				);

				//see if there are any images in the selected folder
				foreach ( $file_array as $file => $file_info ) {
					$ext = preg_replace( '/^.*\./', '', $file_info['name'] );

					if ( in_array( $ext, $supported_image ) ) {
						$image_count ++;
					}
				}

				echo __( 'Images found in folder : ', 'foogallery' ) . $image_count;
			}
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
                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.remove', function (e) {
                        e.preventDefault();

                        //hide the previous info
                        $(this).parents('.foogallery-datasource-folder').hide();

                        //clear the datasource value
                        $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');

                        //clear the datasource
                        $('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val('');

                        //deselect current folder
                        $('.foogallery-datasource-folder-selected').val('');

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

                    $(document).on('foogallery-datasource-changed-folders', function() {
                        var $container = $('.foogallery-datasource-folder'),
                            datasource_value = $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val();

                        if ( datasource_value.length > 0 ) {
                            var datasource_value_json = JSON.parse( datasource_value );

                            $container.find('.foogallery-items-html').html(datasource_value_json.value);

                            $container.show();

                            FOOGALLERY.showHiddenAreas( false );

                            $('.foogallery-attachments-list').addClass('hidden');

                            $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                        }
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
