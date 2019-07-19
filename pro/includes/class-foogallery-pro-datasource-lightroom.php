<?php
/**
 * The Gallery Datasource which pulls images using WP/LR Sync
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Lightroom' ) ) {

    class FooGallery_Pro_Datasource_Lightroom {

    	public function __construct() {
			add_action( 'foogallery_gallery_datasources', array($this, 'add_datasource'), 6 );
			add_filter( 'foogallery_datasource_lightroom_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_lightroom_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_lightroom_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );

			add_action( 'foogallery-datasource-modal-content_lightroom', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );

			add_action( 'wp_ajax_foogallery_datasource_lightroom_select' , array( $this, 'get_collection_info' ) );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_transient' ) );
		}

		/**
		 * Add the Lightroom Datasource
		 * @param $datasources
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['lightroom'] = array(
				'id'     => 'lightroom',
				'name'   => __( 'Adobe Lightroom', 'foogallery' ),
				'menu'   => __( 'Adobe Lightroom', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

		/**
		 * Clears the cache for the specific folder
		 * @param $foogallery_id
		 */
		public function before_save_gallery_datasource_clear_datasource_transient( $foogallery_id ) {
            $this->clear_gallery_transient( $foogallery_id );
		}

        public function clear_gallery_transient( $foogallery_id ) {
		    $transient_key = '_foogallery_datasource_lightroom_' . $foogallery_id;
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
            return count( $this->get_gallery_attachments_from_lightroom( $foogallery ) );
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
            return $this->get_gallery_attachments_from_lightroom( $foogallery );
        }

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments_from_lightroom( $foogallery ) {
            global $foogallery_gallery_preview;

            $attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
                $transient_key = '_foogallery_datasource_lightroom_' . $foogallery->ID;

                //never get the cached results if we are doing a preview
                if ( isset( $foogallery_gallery_preview ) ) {
                    $cached_attachments = false;
                } else {
                    $cached_attachments = get_transient( $transient_key );
                }

				if ( false === $cached_attachments) {
                    $datasource_value = $foogallery->datasource_value;
					$collectionId = $datasource_value['collectionId'];

					$expiry = 24 * 60 * 60; //24 hours

                    //find all image files in the lightroom collection
					$helper = new FooGallery_Datasource_MediaLibrary_Query_Helper();

					global $wplr;
					$media = $wplr->get_media_from_collection( $collectionId );

					$attachments = $helper->query_attachments( $foogallery,
						array( 'post__in' => $media )
					);

					//save a cached list of attachments
					set_transient( $transient_key, $attachments, $expiry );
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
            $attachments = $this->get_gallery_attachments_from_lightroom( $foogallery );
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

			$collection = '';
            if ( is_array( $datasource_value ) && array_key_exists( 'collection', $datasource_value ) ) {
				$collection = $datasource_value['collection'];
            }

            $wplr_url = 'http://fooplugins.com/refer/wp-lr-sync/';
            $wplr_link = '<a href="' . $wplr_url . '" target="_blank">' . __('WP/LR Sync', 'foogallery') . '</a>';

			?>
			<style>
				.foogallery-datasource-lightroom-list ul {
					list-style: none;
				}

                .foogallery-datasource-lightroom-list ul li {
                    padding: 4px 0 0 20px;
                }

				.foogallery-datasource-lightroom-list .dashicons {
					vertical-align: bottom;
					padding-right: 5px;
				}

				.foogallery-datasource-lightroom-list ul li a {
					text-decoration: none;
				}

				.foogallery-datasource-lightroom-list ul li a:focus {
					box-shadow: none;
					outline: none;
				}

                .foogallery-datasource-lightroom-list ul li a .spinner {
                    display: inline-block;
                    margin-left: 10px;
                    float: none;
					vertical-align: middle;
                }

                .foogallery-datasource-lightroom-list ul li a.active {
					background: #bbb;
				}

				.foogallery-datasource-lightroom-list .spacer {
					padding-left: 10px;
				}

                .foogallery-datasource-lightroom-selected {
                    padding: 3px 6px;
                    background: #efefef;
                    border-radius: 3px;
                }
			</style>
			<script type="text/javascript">
				jQuery(function ($) {
					$('.foogallery-datasource-lightroom-list').on('click', 'ul li a', function (e) {
						e.preventDefault();

						var $this = $(this),
                            $collectionInfo = $('.foogallery-datasource-lightroom-collection-info'),
                            collection = $this.data('collection'),
							collectionId = $this.data('collectionId');

                        $this.append('<span class="is-active spinner"></span>');

                        $('.foogallery-datasource-lightroom-selected').text(collection);

						//set the selection
						document.foogallery_datasource_value_temp = {
							"collectionId" : collectionId,
							"collection" : collection
						};

						$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

                        var data = 'action=foogallery_datasource_lightroom_select' +
                            '&collectionId=' + encodeURIComponent(collectionId) +
                            '&nonce=<?php echo wp_create_nonce( 'foogallery_datasource_lightroom_select' ); ?>';

                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function(data) {
                                $this.find('.spinner').remove();
								$collectionInfo.html(data);
                            }
                        });
					});
				});
			</script>
			<?php if ( class_exists( 'Meow_WPLR_Sync_API' ) ) {?>
				<p><?php _e('Select a lightroom collection from the list below. The gallery will then dynamically load all images that are inside the selected collection.', 'foogallery'); ?></p>
				<p><?php _e('Selected Collection : ', 'foogallery'); ?><span class="foogallery-datasource-lightroom-selected"><?php echo empty($collection) ? __('nothing yet', 'foogallery') : $collection; ?></span></p>
				<div class="foogallery-datasource-lightroom-list">
					<?php
						global $wplr;
						$hierarchy = $wplr->get_hierarchy();
						$this->output_lightroom_hierarchy( $hierarchy );
					?>
					<div class="foogallery-datasource-lightroom-collection-info"></div>
				</div>
			<?php } else { ?>
				<p><?php echo sprintf( __('You need to purchase the %s plugin in order to sync your Adobe Lightroom collections with your WordPress Media Library.','foogallery'), $wplr_link ); ?></p>
				<p><?php echo __('WP/LR Sync is a Lightroom Publishing Service for WordPress. It exports your photos to WordPress, the folders and collections from Adobe Lightroom and keeps it all synchronized.', 'foogallery'); ?></p>
				<a href="<?php echo $wplr_url; ?>" target="_blank"><img src="https://store.meowapps.com/wp-content/uploads/2017/03/meow-apps.png" width="500" /></a>
			<?php } ?>
            <?php
		}

		private function output_lightroom_hierarchy( $hierarchy ) {
			if ( is_array( $hierarchy ) ) {
				echo '<ul>';
				foreach ( $hierarchy as $item ) {
					if ( $item['type'] === 'collection' ) {
						echo '<li><a href="#" data-collection="' . esc_attr( $item['name'] ) . '" data-collection-id="' . esc_attr( $item['id'] ) . '"><span class="dashicons dashicons-images-alt2" />' . esc_html( $item['name'] ) . '</a></li>';
					} elseif ( $item['type'] === 'folder' ) {
						echo '<li><span class="dashicons dashicons-category" />';
						echo $item['name'];
						if ( array_key_exists( 'children', $item ) ) {
							$children = $item['children'];
							$this->output_lightroom_hierarchy( $children );
						}
						echo '</li>';
					}
				}
				echo '</ul>';
			}
		}

		function get_collection_info() {
			if ( check_admin_referer( 'foogallery_datasource_lightroom_select', 'nonce' ) ) {
				$collectionId = $_POST['collectionId'];
				global $wplr;
				$collection = $wplr->get_collection( $collectionId );
				$media = $wplr->get_media_from_collection( $collectionId );
				echo sprintf( __('%s contains %d images.', 'foogallery'), $collection->name, count( $media ) );
			}

			die();
		}

        /**
         * Output the html required by the datasource in order to add item(s)
         * @param FooGallery $gallery
         */
		function render_datasource_item( $gallery ) { ?>
            <style type="text/css">
                .foogallery-datasource-lightroom {
                    padding: 20px;
                    text-align: center;
                }

				.foogallery-datasource-lightroom .foogallery-items-html {
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
                    $('.foogallery-datasource-lightroom').on('click', 'button.remove', function (e) {
                        e.preventDefault();

                        //hide the previous info
                        $(this).parents('.foogallery-datasource-lightroom').hide();

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

                    $('.foogallery-datasource-lightroom').on('click', 'button.edit', function (e) {
                        e.preventDefault();

                        //show the modal
                        $('.foogallery-datasources-modal-wrapper').show();

                        //select the lightroom datasource
                        $('.foogallery-datasource-modal-selector[data-datasource="lightroom"]').click();
                    });

					$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
						$('.foogallery-datasource-lightroom').hide();

						if ( activeDatasource !== 'lightroom' ) {
							//clear the selected
						}
					});

                    $(document).on('foogallery-datasource-changed-lightroom', function() {
                        var $container = $('.foogallery-datasource-lightroom');

						$('#_foogallery_datasource_value').val(JSON.stringify(document.foogallery_datasource_value_temp));

						$container.find('.foogallery-items-html').html(document.foogallery_datasource_value_temp.collection);

						$container.show();

						FOOGALLERY.showHiddenAreas( false );

						$('.foogallery-attachments-list').addClass('hidden');

						$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
                    });
                });
            </script>
        <?php
			$show_container = isset( $gallery->datasource_name) && 'lightroom' === $gallery->datasource_name;
			$value = ($show_container && isset( $gallery->datasource_value['collection'] )) ? $gallery->datasource_value['collection'] : '';
			?>
			<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-lightroom">
				<h3><?php _e('Datasource : Lightroom Collection', 'foogallery'); ?></h3>
				<p><?php _e('This gallery will be dynamically populated with all images within the following collection in Adobe Lightroom:', 'foogallery'); ?></p>
				<div class="foogallery-items-html"><?php echo $value ?></div>
				<br />
				<button type="button" class="button edit">
					<?php _e( 'Change Collection', 'foogallery' ); ?>
				</button>
				<button type="button" class="button remove">
					<?php _e( 'Remove Collection', 'foogallery' ); ?>
				</button>
			</div><?php
		}
    }
}
