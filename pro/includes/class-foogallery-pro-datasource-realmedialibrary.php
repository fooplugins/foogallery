<?php
/**
 * The Gallery Datasource which pulls attachments for a specific Media Category Taxonomy
 */
if ( ! class_exists( 'FooGallery_Pro_Datasource_RealMediaLibrary' ) ) {

	class FooGallery_Pro_Datasource_RealMediaLibrary {
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		}

		public function plugins_loaded() {
			add_action( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ), 6 );
			add_action( 'foogallery-datasource-modal-content_rml', array( $this, 'render_datasource_modal_content' ), 10, 3 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_item' ), 10, 1 );
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'before_save_gallery_datasource_clear_datasource_cached_attachments' ) );
			add_action( 'RML/Folder/Deleted', array( $this, 'rml_folder_cachereset' ) );
			add_action( 'RML/Folder/Deleted', array( $this, 'rml_folder_cachereset' ) );
			add_action( 'RML/Folder/OrderBy', array( $this, 'rml_folder_cachereset' ) );
			add_action( 'RML/Item/DragDrop',  array( $this, 'rml_folder_cachereset' ) );
			add_action( 'RML/Item/MoveFinished', array( $this, 'rml_item_move_finished' ), 10, 5 );

			add_filter( 'foogallery_datasource_rml_item_count', array( $this, 'get_gallery_attachment_count' ), 10, 2 );
			add_filter( 'foogallery_datasource_rml_featured_image', array( $this, 'get_gallery_featured_attachment' ), 10, 2 );
			add_filter( 'foogallery_datasource_rml_attachments', array( $this, 'get_gallery_attachments' ), 10, 2 );
		}

		public function correct_version() {
			return defined( 'RML_VERSION' ) && version_compare( RML_VERSION, '4.5.3', '>=' );
		}

		/**
		 * If a real media folder got deleted or orderby changed, then clear cache.
		 */
		public function rml_folder_cachereset( $fid ) {
			$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $fid;
			delete_post_meta_by_key( $cache_post_meta_key );
		}

		/**
		 * A folder got updated, clear all relevant caches.
		 */
		public function rml_item_move_finished( $fid, $attachments, $folder, $isShortcut, $sourceFolders ) {
			$folders   = $sourceFolders;
			$folders[] = $fid;
			foreach ( $folders as $id ) {
				$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $id;
				delete_post_meta_by_key( $cache_post_meta_key );
			}
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
			// Check if cover image is set
			if ( ! empty( $foogallery->datasource_value ) ) {
				$datasource_value = $foogallery->datasource_value;
				$fid              = $datasource_value['value'];
				$coverImage       = (int) get_media_folder_meta( $fid, "coverImage", true );
				if ( $coverImage > 0 ) {
					return FooGalleryAttachment::get_by_id( $coverImage );
				}
			}

			// Fallback to first image
			if ( $foogallery->attachment_ids ) {
				$attachment_id_values = array_values( $foogallery->attachment_ids );
				$attachment_id        = array_shift( $attachment_id_values );

				return FooGalleryAttachment::get_by_id( $attachment_id );
			}

			return $default;
		}

		/**
		 * Clear the previously saved datasource cache for the gallery
		 *
		 * @param $foogallery_id
		 */
		public function before_save_gallery_datasource_clear_datasource_cached_attachments( $foogallery_id ) {
			// clear any previously cached post meta for the gallery
			$previous_datasource_value = get_post_meta( $foogallery_id, FOOGALLERY_META_DATASOURCE_VALUE, true );

			if ( is_array( $previous_datasource_value ) ) {
				$fid                 = $previous_datasource_value['value'];
				$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $fid;
				delete_post_meta( $foogallery_id, $cache_post_meta_key );
			}
		}

		/**
		 * Returns the number of attachments used from the media library
		 *
		 * @param int        $count
		 * @param FooGallery $foogallery
		 *
		 * @return int
		 */
		public function get_gallery_attachment_count( $count, $foogallery ) {
			return count( $this->get_gallery_attachments( array(), $foogallery ) );
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
			global $foogallery_gallery_preview;

			if ( ! empty( $foogallery->datasource_value ) ) {
				$datasource_value    = $foogallery->datasource_value;
				$fid                 = $datasource_value['value'];
				$cache_post_meta_key = FOOGALLERY_META_DATASOURCE_CACHED_ATTACHMENTS . '_' . $fid;
				$helper              = new FooGallery_Datasource_MediaLibrary_Query_Helper();

				// never get the cached attachments if we doing a preview
				if ( $foogallery_gallery_preview ) {
					$cached_attachments = false;
				} else {
					// check if there is a cached list of attachments
					$cached_attachments = get_post_meta( $foogallery->ID, $cache_post_meta_key, true );
				}

				if ( empty( $cached_attachments ) ) {
					$attachment_posts = get_posts(
						array(
							'post_status'      => 'inherit',
							'post_type'        => 'attachment',
							'posts_per_page'   => - 1,
							'rml_folder'       => $fid,
							'suppress_filters' => false,
							'orderby'          => 'rml'
						)
					);

					// Prepare FooGallery attachments
					$attachments = array();
					foreach ( $attachment_posts as $attachment_post ) {
						$attachments[] = apply_filters( 'foogallery_attachment_load', FooGalleryAttachment::get( $attachment_post ), $foogallery );
					}

					$attachment_ids = array();
					foreach ( $attachments as $attachment ) {
						$attachment_ids[] = $attachment->ID;
					}

					// save a cached list of attachments
					update_post_meta( $foogallery->ID, $cache_post_meta_key, $attachment_ids );
				} else {
					$attachments = $helper->query_attachments(
						$foogallery,
						array( 'post__in' => $cached_attachments )
					);
				}
			}

			return $attachments;
		}

		/**
		 * Add the RML folders Datasource
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		function add_datasource( $datasources ) {
			$datasources['rml'] = array(
				'id'     => 'rml',
				'name'   => __( 'Real Media Library', 'foogallery' ),
				'menu'   => __( 'Real Media Library', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

		/**
		 * Output the datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content( $foogallery_id, $datasource_value ) {
			$rml_url  = 'http://fooplugins.com/refer/real-media-library/';
			$rml_link = '<a href="' . $rml_url . '" target="_blank">' . __( 'Real Media Library', 'foogallery' ) . '</a>';

			//check for plugin version and show message

			if ( ! defined( 'RML_VERSION' ) ) { ?>
				<p><?php echo sprintf( __( 'You need to activate the %s plugin in order to use the Real Media Library datasource', 'foogallery' ), $rml_link ); ?></p>
				<p><?php echo __( 'RML (Real Media Library) is one of the most wanted media wordpress plugins. It is easy to use and it allows you to organize your thousands of images in folders. It is similar to wordpress categories like in the posts.', 'foogallery' ); ?></p>
				<a href="<?php echo $rml_url; ?>" target="_blank"><img src="https://matthias-web.com/wp-content/uploads/Plugins/Real-Media-Library/preview.jpg" width="500" /></a>
			<?php } else {

				if ( !$this->correct_version() ) { ?>
					<p><?php echo sprintf( __( 'You are using an outdated version of %s - please download the latest version of the plugin, which is 100%% datasource compatible.', 'foogallery' ), $rml_link ); ?></p>
				<?php }

				echo '<p>' . __( 'Select a folder below. Your gallery will then be dynamically populated with all the images within the selected folder.', 'foogallery' ) . '</p>';

				// Preselect to edit
				$folder = _wp_rml_root();
				if ( is_array( $datasource_value ) && array_key_exists( 'value', $datasource_value ) && ( $folder = wp_rml_get_object_by_id( $datasource_value['value'] ) ) !== null ) {
					$folder = $folder->getId();
				}

				// Dropdown
				echo wp_rml_selector(
					array(
						"selected" => $folder,
						"title"    => __( 'Select folder', 'foogallery' ),
						"name"     => "foogallery-rml-folder-id"
					)
				);

				// Listen to dropdown changes and fill FooGallery specific value management
				?>
				<script>
					/* global jQuery */
					jQuery(function ($) {
						$('.foogallery-datasource-modal-container-inner [name="foogallery-rml-folder-id"]').on('folderSelected', function (e) {
							// set the selection
							var node = $(this).data('node');
							document.foogallery_datasource_value_temp = {
								"value"   : node.id,
								"absolute": node.path.map(function (o) {
									return o.title;
								}).join("/")
							};
							$('.foogallery-datasource-modal-insert').removeAttr('disabled');
						});
					});
				</script>
				<?php
			}
		}

		/**
		 * Output the html required by the datasource in order to add item(s)
		 *
		 * @param FooGallery $gallery
		 */
		function render_datasource_item( $gallery ) { ?>
			<style type="text/css">
				.foogallery-datasource-rml {
					padding: 20px;
					text-align: center;
				}

				.foogallery-datasource-rml .foogallery-items-html {
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
				/* global jQuery FOOGALLERY */
				jQuery(function ($) {
					// When clicking on the remove button hide the panel
					$('.foogallery-datasource-rml').on('click', 'button.remove', function (e) {
						e.preventDefault();
						FOOGALLERY.showHiddenAreas(true);
						$(this).parents('.foogallery-datasource-rml').hide();
						$('#<?php echo FOOGALLERY_META_DATASOURCE_VALUE; ?>').val('');
						$('#<?php echo FOOGALLERY_META_DATASOURCE; ?>').val('');
						$('.foogallery-datasource-modal-insert').attr('disabled', 'disabled');
						$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
					});

					// When clicking on the edit-button show the modal and preselect the datasource
					$('.foogallery-datasource-rml').on('click', 'button.edit', function (e) {
						e.preventDefault();
						$('.foogallery-datasources-modal-wrapper').show();
						$('.foogallery-datasource-modal-selector[data-datasource="rml"]').click();
					});

					// When changed to a non-RML datasource hide the panel
					$(document).on('foogallery-datasource-changed', function (e, activeDatasource) {
						$('.foogallery-datasource-rml').hide();
					});

					// When changed to RML datasource show the panel where usually items are visible
					$(document).on('foogallery-datasource-changed-rml', function () {
						FOOGALLERY.showHiddenAreas(false);
						$('#_foogallery_datasource_value').val(JSON.stringify(document.foogallery_datasource_value_temp));
						$('.foogallery-datasource-rml').show().find('.foogallery-items-html').html(document.foogallery_datasource_value_temp.absolute);
						$('.foogallery-attachments-list').addClass('hidden');
						$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
					});
				});
			</script>
			<?php
			$show_container = isset( $gallery->datasource_name ) && 'rml' === $gallery->datasource_name;
			$value          = ( $show_container && isset( $gallery->datasource_value['value'] ) ) ? $gallery->datasource_value['value'] : '';
			if ( ! empty( $value ) && ( $folder = wp_rml_get_object_by_id( $value ) ) !== null ) {
				$value = $folder->getPath();
			}
			?>
			<div <?php echo $show_container ? '' : 'style="display:none" '; ?>class="foogallery-datasource-rml">
				<h3><?php _e( 'Datasource : Real Media Library folder', 'foogallery' ); ?></h3>
				<p><?php _e( 'This gallery will be dynamically populated with all images within the following folder:', 'foogallery' ); ?></p>
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