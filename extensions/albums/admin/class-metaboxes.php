<?php

/*
 * FooGallery Admin Album MetaBoxes class
 */

if ( ! class_exists( 'FooGallery_Admin_Album_MetaBoxes' ) ) {

	class FooGallery_Admin_Album_MetaBoxes {

		private $_album;

		function __construct() {
			//add our foogallery metaboxes
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

			//save extra post data for a gallery
			add_action( 'save_post', array( $this, 'save_album' ) );

			//whitelist metaboxes for our album posttype
			add_filter( 'foogallery-album_metabox_sanity', array( $this, 'whitelist_metaboxes' ) );

			//add scripts used by metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'include_required_scripts' ) );
		}

		function whitelist_metaboxes() {
			return array(
				FOOGALLERY_CPT_GALLERY => array(
					'whitelist'  => apply_filters( 'foogallery_metabox_sanity_foogallery-album',
						array(
							'submitdiv',
							'slugdiv',
							'postimagediv',
							'foogalleryalbum_galleries',
							'foogalleryalbum_shortcode'
						)
					),
					'contexts'   => array( 'normal', 'advanced', 'side', ),
					'priorities' => array( 'high', 'core', 'default', 'low', ),
				)
			);
		}

		function add_meta_boxes() {
			add_meta_box(
				'foogalleryalbum_galleries',
				__( 'Galleries', 'foogallery' ),
				array( $this, 'render_gallery_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'normal',
				'high'
			);

			add_meta_box(
				'foogalleryalbum_shortcode',
				__( 'Album Shortcode', 'foogallery' ),
				array( $this, 'render_shortcode_metabox' ),
				FOOGALLERY_CPT_ALBUM,
				'side',
				'default'
			);
		}

		function get_album( $post ) {
			if ( ! isset( $this->_album ) ) {
				$this->_album = FooGalleryAlbum::get( $post );
			}

			return $this->_album;
		}

		function save_album( $post_id ) {
			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			// verify nonce
			if ( array_key_exists( FOOGALLERY_CPT_ALBUM . '_nonce', $_POST ) &&
			     wp_verify_nonce( $_POST[ FOOGALLERY_CPT_ALBUM . '_nonce' ], plugin_basename( FOOGALLERY_FILE ) )
			) {
				//if we get here, we are dealing with the Album custom post type

				$galleries = apply_filters( 'foogallery_save_album_galleries', explode( ',', $_POST[ FOOGALLERY_ALBUM_META_GALLERIES ] ) );
				update_post_meta( $post_id, FOOGALLERY_ALBUM_META_GALLERIES, $galleries );

				do_action( 'foogallery_after_save_album', $post_id, $_POST );
			}
		}

		function get_ordered_galleries( $album ) {

			//get all other galleries
			$galleries = foogallery_get_all_galleries( $album->gallery_ids );

			$album_galleries = $album->galleries();

			return array_merge( $album_galleries, $galleries );
		}

		function render_gallery_metabox( $post ) {
			$album = $this->get_album( $post );

			$galleries = $this->get_ordered_galleries( $album );

			?>
			<input type="hidden" name="<?php echo FOOGALLERY_CPT_ALBUM; ?>_nonce"
			       id="<?php echo FOOGALLERY_CPT_ALBUM; ?>_nonce"
			       value="<?php echo wp_create_nonce( plugin_basename( FOOGALLERY_FILE ) ); ?>"/>
			<input type="hidden" name='foogallery_album_galleries' id="foogallery_album_galleries"
			       value="<?php echo $album->gallery_id_csv(); ?>"/>
			<div>
				<ul class="foogallery-album-gallery-list">
					<?php
					foreach ( $galleries as $gallery ) {
						$img_src  = $gallery->featured_image_src( array( 200, 200 ) );
						$images   = $gallery->image_count();
						$selected = $album->includes_gallery( $gallery->ID ) ? ' selected' : '';
						?>
						<li class="foogallery-pile">
							<div class="foogallery-gallery-select attachment-preview landscape<?php echo $selected; ?>"
							     data-foogallery-id="<?php echo $gallery->ID; ?>">
								<div class="thumbnail" style="display: table;">
									<div style="display: table-cell; vertical-align: middle; text-align: center;">
										<img src="<?php echo $img_src; ?>"/>
										<?php

										$title = empty( $gallery->name ) ?
											sprintf( __( '%s #%s', 'foogallery' ), foogallery_plugin_name(), $gallery->ID ) :
											$gallery->name;

										?>
										<h3><?php echo $title; ?>
											<span><?php echo $images; ?></span>
										</h3>
									</div>
								</div>
							</div>
						</li>
					<?php } ?>
				</ul>
				<div style="clear: both;"></div>
			</div>
		<?php
		}

		function render_shortcode_metabox( $post ) {
			$gallery   = $this->get_album( $post );
			$shortcode = $gallery->shortcode();
			?>
			<p class="foogallery-shortcode">
				<code id="foogallery-copy-shortcode" data-clipboard-text="<?php echo htmlspecialchars( $shortcode ); ?>"
				      title="<?php _e( 'Click to copy to your clipboard', 'foogallery' ); ?>"><?php echo $shortcode; ?></code>
			</p>
			<p>
				<?php _e( 'Paste the above shortcode into a post or page to show the album. Simply click the shortcode to copy it to your clipboard.', 'foogallery' ); ?>
			</p>
			<script>
				jQuery(function ($) {
					var $el = $('#foogallery-copy-shortcode');
					ZeroClipboard.config({moviePath: "<?php echo FOOGALLERY_URL; ?>lib/zeroclipboard/ZeroClipboard.swf"});
					var client = new ZeroClipboard($el);

					client.on("load", function (client) {
						client.on("complete", function (client, args) {
							$('.foogallery-shortcode-message').remove();
							$el.after('<p class="foogallery-shortcode-message"><?php _e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>');
						});
					});
				});
			</script>
		<?php
		}

		function include_required_scripts() {
			if ( FOOGALLERY_CPT_ALBUM === foo_current_screen_post_type() ) {
				//include album selection script
				$url = FOOGALLERY_ALBUM_URL . 'js/admin-foogallery-album.js';
				wp_enqueue_script( 'admin-foogallery-album', $url, array( 'jquery', 'jquery-ui-core','jquery-ui-sortable' ), FOOGALLERY_VERSION );

				//include album selection css
				$url = FOOGALLERY_ALBUM_URL . 'css/admin-foogallery-album.css';
				wp_enqueue_style( 'admin-foogallery-album', $url, array(), FOOGALLERY_VERSION );

				//zeroclipboard needed for copy to clipboard functionality
				$url = FOOGALLERY_URL . 'lib/zeroclipboard/ZeroClipboard.min.js';
				wp_enqueue_script( 'foogallery-zeroclipboard', $url, array( 'jquery' ), FOOGALLERY_VERSION );
			}
		}
	}
}
