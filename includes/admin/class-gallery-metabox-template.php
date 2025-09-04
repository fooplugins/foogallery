<?php
/**
 * Class to handle adding the Template metabox to a gallery
 */


if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Template' ) ) {

    class FooGallery_Admin_Gallery_MetaBox_Template {

        /**
         * FooGallery_Admin_Gallery_MetaBox_Template constructor.
         */
        function __construct() {
			add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_template_metabox' ), 6 );

            //enqueue assets for the new settings tabs
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        }

		public function add_template_metabox( $post ) {
			$header_html = '<span class="foogallery-gallery-template-metabox-title hidden">' .
			__( '', 'foogallery' ) . 
			'</span>';

			add_meta_box(
				'foogallery_template',
				__( 'Gallery Layout', 'foogallery' ),
				array( $this, 'render_gallery_template_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'high'
			);
		}

		/**
		 * Render the template metabox
		 */
		public function render_gallery_template_metabox( $post ) {
			$gallery = foogallery_admin_get_current_gallery( $post );
		
			$gallery_templates = foogallery_gallery_templates();
			$current_gallery_template = foogallery_default_gallery_template();
			if ( ! empty( $gallery->gallery_template ) ) {
				$current_gallery_template = $gallery->gallery_template;
			}

			?>
			<div class="foogallery-template-card-selector" data-metabox-message="<?php echo esc_attr( __( 'Once you are happy with your selected layout, you can minimize this section to save space', 'foogallery' ) ); ?>">
				<div class="foogallery-template-cards-container">
					<?php foreach ( $gallery_templates as $template ) {
						$selected_class = ( $current_gallery_template === $template['slug'] ) ? 'selected' : '';
						?>
						<div class="foogallery-template-card <?php echo $selected_class; ?>" 
							data-template="<?php echo esc_attr( $template['slug'] ); ?>">
							
							<?php echo $template['icon']; ?>
							
							<h4><?php echo esc_html( $template['name'] ); ?></h4>
						</div>
					<?php } ?>
				</div>
				
				<!-- Keep the hidden select for form submission -->
				<input type="hidden" id="FooGallerySettings_GalleryTemplate" name="<?php echo FOOGALLERY_META_TEMPLATE; ?>" value="<?php echo esc_attr( $current_gallery_template ); ?>" />
			</div>
		<?php
		}

        /***
         * Enqueue the assets needed by the template metabox
         * @param $hook_suffix
         */
        function enqueue_assets( $hook_suffix ){
			if( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
				$screen = get_current_screen();
		
				if ( is_object( $screen ) && FOOGALLERY_CPT_GALLERY === $screen->post_type ){
		
					// Register, enqueue scripts and styles here
					wp_enqueue_script( 'foogallery-admin-template', FOOGALLERY_URL . 'js/foogallery.admin.template.js', array('jquery'), FOOGALLERY_VERSION );
					wp_enqueue_style( 'foogallery-admin-template', FOOGALLERY_URL . 'css/foogallery.admin.template.css', array(), FOOGALLERY_VERSION );
				}
			}
		}
    }
}
