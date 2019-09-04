<?php
/**
 * The Gallery Datasource which pulls images using WP/LR Sync
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_Instagram' ) ) {

    class FooGallery_Pro_Datasource_Instagram {

    	public function __construct() {
			add_action( 'foogallery_gallery_datasources', array($this, 'add_datasource'), 6 );
			add_filter( 'foogallery_datasource_instagram_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_instagram_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_instagram_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );

			add_action( 'foogallery-datasource-modal-content_instagram', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );

			add_action( 'wp_ajax_foogallery_datasource_instagram_select' , array( $this, 'get_instagram_authentication' ) );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_transient' ) );
		}

		/**
		 * Add the Instagram Datasource
		 * @param $datasources
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['instagram'] = array(
				'id'     => 'instagram',
				'name'   => __( 'Instagram', 'foogallery' ),
				'menu'   => __( 'Instagram', 'foogallery' ),
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
		    $transient_key = '_foogallery_datasource_instagram_' . $foogallery_id;
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
            return count( $this->get_gallery_attachments_from_instagram( $foogallery ) );
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
            return $this->get_gallery_attachments_from_instagram( $foogallery );
        }

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments_from_instagram( $foogallery ) {
            global $foogallery_gallery_preview;

            $attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
                $transient_key = '_foogallery_datasource_instagram_' . $foogallery->ID;

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

                    //find all image files in the instagram collection
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
            $attachments = $this->get_gallery_attachments_from_instagram( $foogallery );
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

		}

		private function output_instagram_hierarchy( $hierarchy ) {
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
							$this->output_instagram_hierarchy( $children );
						}
						echo '</li>';
					}
				}
				echo '</ul>';
			}
		}

		function get_instagram_authentication() {
			if ( check_admin_referer( 'foogallery_datasource_instagram_select', 'nonce' ) ) {
					$client_id = sanitize_text_field($_POST['clientID']);
					$client_secret = sanitize_text_field($_POST['clientSecret']);
					//$client_id = sanitize_text_field($_POST['clientID']);
					echo $client_id;
					echo "<br>";
					echo
					$fields = array(
				           'client_id'     => $client_id,
				           'client_secret' => $client_secret,
				           'grant_type'    => 'authorization_code',
				           'redirect_uri'  => home_url(),
				           'code'          => 'insta'
				    );
				    $url = 'https://api.instagram.com/oauth/access_token';
				    $response = wp_remote_post( $url, array(
				    
				    	
				    	'body' => $fields,
				    	
				        )
				    );
				    echo "<pre>";
				    print_r($response);
				    
				    /*$ch = curl_init();
				    curl_setopt($ch, CURLOPT_URL, $url);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				    curl_setopt($ch,CURLOPT_POST,true); 
				    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				    $result = curl_exec($ch);
				    curl_close($ch); 
				    $result = json_decode($result);
				    print_r($result);*/
			}

			die();
		}

        /**
         * Output the html required by the datasource in order to add item(s)
         * @param FooGallery $gallery
         */
		function render_datasource_item( $gallery ) { ?>
            <style type="text/css">
                .foogallery-datasource-instagram {
                    padding: 20px;
                    text-align: center;
                }

				.foogallery-datasource-instagram .foogallery-items-html {
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
                    $('.foogallery-datasource-instagram').on('click', 'button.remove', function (e) {
                        e.preventDefault();

                        //hide the previous info
                        $(this).parents('.foogallery-datasource-instagram').hide();

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

                    $('.foogallery-datasource-instagram').on('click', 'button.edit', function (e) {
                        e.preventDefault();

                        //show the modal
                        $('.foogallery-datasources-modal-wrapper').show();

                        //select the instagram datasource
                        $('.foogallery-datasource-modal-selector[data-datasource="instagram"]').click();
                    });

					$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
						$('.foogallery-datasource-instagram').hide();

						if ( activeDatasource !== 'instagram' ) {
							//clear the selected
						}
					});

                    $(document).on('foogallery-datasource-changed-instagram', function() {
                        var $container = $('.foogallery-datasource-instagram');

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
			$show_container = isset( $gallery->datasource_name) && 'instagram' === $gallery->datasource_name;
			$value = ($show_container && isset( $gallery->datasource_value['collection'] )) ? $gallery->datasource_value['collection'] : '';
			?>
			<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-instagram">
				<h3><?php _e('Datasource : Instagram Collection', 'foogallery'); ?></h3>
				<p><?php _e('This gallery will be dynamically populated with all images within the following collection in Adobe Instagram:', 'foogallery'); ?></p>
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
