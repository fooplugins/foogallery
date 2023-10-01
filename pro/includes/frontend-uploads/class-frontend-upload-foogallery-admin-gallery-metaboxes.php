<?php
// Include the necessary file.
require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php';

/**
 * Class FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes
 *
 * @package fooplugins
 */
class FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes extends FooGallery_Admin_Gallery_MetaBoxes {
	private $gallery_id;

	/**
	 * Constructor for the FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes class.
	 * Initializes the necessary actions and filters.
	 */
	public function __construct() {
		parent::__construct();
		$this->gallery_id = isset( $_POST['gallery_id'] ) ? intval( $_POST['gallery_id'] ) : null;

		// Hook to save metadata checkboxes.
		add_action( 'save_post', array( $this, 'save_metadata_checkboxes' ) );
	}

	/**
	 * Add meta boxes to the gallery post type.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function add_meta_boxes_to_gallery( $post ) {
		parent::add_meta_boxes_to_gallery( $post );

		add_meta_box(
			'custom_metabox_id',
			__( 'Front End Upload', 'foogallery' ),
			array( $this, 'render_frontend_upload_metabox' ),
			FOOGALLERY_CPT_GALLERY,
			'side',
			'low'
		);
	}

	/**
	 * Save metadata checkboxes when the gallery post is saved.
	 *
	 * @param int $post_id The ID of the saved post.
	 */
	public function save_metadata_checkboxes( $post_id ) {
		if ( get_post_type( $post_id ) === FOOGALLERY_CPT_GALLERY ) {
			// Update post meta for the metadata checkboxes.
			$metafields = array( 'caption', 'description', 'alt', 'custom_url', 'custom_target' );
			foreach ( $metafields as $metafield ) {
				$metafield_value = isset( $_POST[ "display_$metafield" ] ) ? 'on' : 'off';
				update_post_meta( $post_id, "_display_$metafield", $metafield_value );
			}
		}
	}

	/**
	 * Render the frontend upload metabox.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function render_frontend_upload_metabox( $post ) {
		$gallery = $this->get_gallery( $post );
		$shortcode = $gallery->shortcode();

		// Use preg_match to find the ID within the shortcode.
		if ( preg_match( '/\[foogallery id="(\d+)"\]/', $shortcode, $matches ) ) {
			$gallery_id = $matches[1];
			?>
			<p class="" style="display: flex; justify-content:center; align-items:center;" >
				<input style="border: 0; padding: 7px 10px;" type="text" id="Upload_Form_copy_shortcode" size="<?php echo strlen( $shortcode ) + 2; ?>" value="<?php echo esc_attr( htmlspecialchars( '[foogallery_upload id="' . $gallery_id . '"]' ) ); ?>" readonly="readonly" />
				<input type="hidden" id="gallery_id" value="<?php echo esc_attr( $gallery_id ); ?>" />
			</p>

			<p>
				<?php esc_html_e( 'Paste the above shortcode into a post or page to show the Image Upload Form.', 'foogallery' ); ?>
			</p>

			<div id="metadata-settings">
				<h4><?php esc_html_e( 'Check to display the metadata fields in the upload form.', 'foogallery' ); ?></h4>
				<?php
				$metafields = array( 'caption', 'description', 'alt', 'custom_url', 'custom_target' );
				foreach ( $metafields as $metafield ) {
					$option_name     = "_display_$metafield";
					$metafield_value = get_post_meta( $gallery_id, $option_name, true );
					?>
					<label>
						<input type="checkbox" id="display_<?php echo esc_attr( $metafield ); ?>" name="display_<?php echo esc_attr( $metafield ); ?>" <?php checked( $metafield_value, 'on' ); ?> />
						<?php esc_html_e( "Display $metafield", 'foogallery' ); ?>
					</label>
					<br />
				<?php } ?>
			</div>

			<script>
				jQuery( function($) {
					var shortcodeInput = document.querySelector( '#Upload_Form_copy_shortcode' );
					shortcodeInput.addEventListener( 'click', function () {
						try {
							// select the contents
							shortcodeInput.select();
							// copy the selection
							document.execCommand( 'copy' );
							// show the copied message
							$( '.foogallery-shortcode-message' ).remove();
							$( shortcodeInput ).after( '<p class="foogallery-shortcode-message"><?php esc_html_e( 'Shortcode copied to clipboard :)','foogallery' ); ?></p>' );
						} catch(err) {
							console.log( 'Oops, unable to copy!' );
						}
					}, false );

					const galleryIdInput = document.getElementById( 'gallery_id' );
					const metadataSettings = document.getElementById( 'metadata-settings' );

					galleryIdInput.addEventListener( 'change', function () {
						const newGalleryId = galleryIdInput.value;
						const newShortcode = `[Upload_Form id="${newGalleryId}"]`;
						shortcodeInput.value = newShortcode;
					});
				});
			</script>
			<?php
		} else {
			// No ID found.
			echo esc_html__( 'No ID found in the shortcode.', 'foogallery' );
		}
	}
}

$custom_foogallery_meta_boxes = new FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes();
