<?php
/**
 * The Gallery Datasource which pulls images from a specific folder on the server
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Folders' ) ) {

    class FooGallery_Pro_Datasource_Folders {

    	public function __construct() {
			add_filter( 'foogallery_datasource_folders_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_folders_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_folders_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );
			add_action( 'foogallery-datasource-modal-content_folders', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'wp_ajax_foogallery_datasource_folder_change' , array( $this, 'render_folder_structure' ) );
//			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
//			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_images' ) );
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

            $folder = false;
            if ( is_array( $datasource_value ) && array_key_exists( 'folder', $datasource_value ) ) {
                $folder = $datasource_value['folder'];
            }
            $files = false;
            if ( is_array( $datasource_value ) && array_key_exists( 'files', $datasource_value ) ) {
                $files = $datasource_value['files'];
            }
            $expiry = false;
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
            <p><?php _e('Selected Folder : ', 'foogallery'); ?><span class="foogallery-datasource-folder-selected"><?php _e('nothing yet', 'foogallery'); ?></span></p>
            <div class="foogallery-datasource-folder-list">
            <?php

            $root = apply_filters( 'foogallery_filesystem_root', WP_CONTENT_DIR );

            $this->render_filesystem_tree( $root );

            ?>
            </div><?php
		}

		function render_folder_structure() {
            if ( check_admin_referer( 'foogallery_datasource_folder_change', 'nonce' ) ) {
                $folder = $_POST['folder'];
                $this->render_filesystem_tree( $folder );
            }

            die();
        }

		function render_filesystem_tree( $path ) {
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

            if ( $wp_filesystem->exists( $path ) ) {
                $files = $wp_filesystem->dirlist( $path );

                echo '<ul>';

                $up_folder = substr($path, 0, strrpos( $path, '/') );

                echo '<li><a href="#" data-folder="' . esc_attr( $up_folder ) . '"><span class="dashicons dashicons-category" />..</a></li>';

                if( count( $files ) > 0 ) {
                    // build separate arrays for folders and files
                    $dir_array = array();
                    $file_array = array();
                    foreach ( $files as $file => $file_info ) {
                        if ( $file != '.' && $file != '..' && $file_info['type'] == 'd' ) {
                            $file_string = strtolower( preg_replace( "[._-]", "", $file ) );
                            $dir_array[$file_string] = $file_info;
                        } elseif ( $file != '.' && $file != '..' &&  $file_info['type'] == 'f' ){
                            $file_string = strtolower( preg_replace( "[._-]", "", $file ) );
                            $file_array[$file_string] = $file_info;
                        }
                    }
                    // shot those arrays
                    ksort( $dir_array );
                    ksort( $file_array );
                    // All dirs
                    foreach ( $dir_array as $file => $file_info ) {
                        echo '<li><a href="#" data-folder="' . esc_attr( $path . '/' . $file_info['name'] ) . '"><span class="dashicons dashicons-category" />' . esc_html( $file_info['name'] ) . '</a></li>';
                    }
//                    // All files
//                    foreach ( $file_array as $file => $file_info ) {
//                        $ext = preg_replace( '/^.*\./', '', $file_info['name'] );
//                        echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . esc_attr( $_POST['dir'] . $file_info['name'] ) . "\" draggable=\"false\">" . esc_html( $file_info['name'] ) . "</a></li>";
//                    }
                }
                echo '</ul>';
            }
        }

        /**
         * Output the html required by the datasource in order to add item(s)
         * @param FooGallery $gallery
         */
		function render_datasource_item( $gallery ) { ?>
            <style type="text/css">
                .foogallery-datasource-taxonomy {
                    padding: 20px;
                    text-align: center;
                }

                .foogallery-datasource-taxonomy ul {
                    list-style: none;
                    margin-bottom: 20px;
                }

                .foogallery-datasource-taxonomy ul li {
                    display: inline-block;
                    margin-right: 10px;
                    border-radius: 5px;
                    padding: 4px 12px;
                    text-align: center;
                    text-decoration: none;
                    font-size: 1.2em;
                    background: #bbb;
                }

                .foogallery-datasource-taxonomy-help h4 {
                    font-weight: bold;
                    text-decoration: underline;
                }
            </style>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.remove', function (e) {
                        e.preventDefault();

                        //hide the previous info
                        $(this).parents('.foogallery-datasource-taxonomy').hide();

                        //clear the datasource value
                        $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');

                        //clear the datasource
                        $('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val('');

                        //deselect any media tag buttons in the modal
                        $('.foogallery-datasource-modal-container .datasource-taxonomy a.active').removeClass('active');

                        //make sure the modal insert button is not active
                        $('.foogallery-datasource-modal-insert').attr('disabled','disabled');

                        FOOGALLERY.showHiddenAreas( true );

                        //ensure the preview will be refreshed
                        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.edit', function (e) {
                        e.preventDefault();

                        //show the modal
                        $('.foogallery-datasources-modal-wrapper').show();

                        //select the media tags datasource
                        $('.foogallery-datasource-modal-selector[data-datasource="media_tags"]').click();
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.media', function(e) {
                       e.preventDefault();

                        if (typeof(document.foogallery_media_tags_modal) !== 'undefined'){
                            document.foogallery_media_tags_modal.open();
                            return;
                        }

                        document.foogallery_media_tags_modal = wp.media({
                            frame: 'select',
                            title: '<?php _e('Assign Media Tags', 'foogallery'); ?>',
                            button: {
                                text: '<?php _e('Close', 'foogallery'); ?>'
                            },
                            library: {
                                type: 'image'
                            }
                        }).on( 'open', function() {
                            //ensure the preview will be refreshed
                            $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                        });

                        document.foogallery_media_tags_modal.open();
                    });

                    $('.foogallery-datasource-items-list-media_tags').on('click', 'button.help', function(e) {
                        e.preventDefault();

                        $('.foogallery-datasource-taxonomy-help').toggle();
                    });

                    $(document).on('foogallery-datasource-changed-media_tags', function() {
                        var $container = $('.foogallery-datasource-taxonomy'),
                            datasource_value = $('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val();

                        if ( datasource_value.length > 0 ) {
                            var datasource_value_json = JSON.parse( datasource_value );

                            $container.find('.foogallery-items-html').html(datasource_value_json.html);

                            $container.show();

                            FOOGALLERY.showHiddenAreas( false );

                            $('.foogallery-attachments-list').addClass('hidden');

                            $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                        }
                    });
                });
            </script>
            <ul class="foogallery-datasource-items-list-media_tags">
        <?php
            //if we have a datasource set and its for media tags then output the item
            if ( isset( $gallery->datasource_name) && 'media_tags' === $gallery->datasource_name ) {
                ?>
                <div class="foogallery-datasource-taxonomy">
                    <h3><?php _e('Datasource : Media Tags', 'foogallery'); ?></h3>
                    <p><?php _e('This gallery will be dynamically populated with all attachments assigned to the following Media Tags:', 'foogallery'); ?></p>
                    <div class="foogallery-items-html"><?php echo $gallery->datasource_value['html']; ?></div>
                    <button type="button" class="button button-small edit">
                        <span class="dashicons dashicons-edit"></span><?php _e( 'Change Media Tags', 'foogallery' ); ?>
                    </button>
                    <button type="button" class="button button-small remove">
                        <span class="dashicons dashicons-dismiss"></span><?php _e( 'Remove All Media Tags', 'foogallery' ); ?>
                    </button>
                    <button type="button" class="button button-small media">
                        <span class="dashicons dashicons-admin-media"></span><?php _e( 'Open Media Library', 'foogallery' ); ?>
                    </button>
                    <button type="button" class="button button-small help">
                        <span class="dashicons dashicons-editor-help"></span><?php _e( 'Show Help', 'foogallery' ); ?>
                    </button>
                    <div style="display: none" class="foogallery-datasource-taxonomy-help">
                        <h4><?php _e('Media Tags Datasource Help', 'foogallery'); ?></h4>
                        <p><?php _e('You can change which Media Tags are assigned to this gallery by clicking "Change Media Tags".', 'foogalley' ); ?></p>
                        <p><?php _e('You can remove all Media Tags from this gallery by clicking "Remove All Media Tags".', 'foogalley' ); ?></p>
                        <p><?php _e('You can assign Media Tags to attachments within the WordPress Media Library. Launch by clicking "Open Media Library".', 'foogalley' ); ?></p>
                        <p><?php _e('When an attachment is assigned to one of the Media Tags, it will automatically be shown in the gallery.', 'foogalley' ); ?></p>
                        <p><?php _e('Click on the "Gallery Preview" to see which attachments will be loaded into the gallery.', 'foogallery'); ?></p>
                    </div>
                </div>
                <?php
            } ?>
            </ul><?php
		}
    }
}