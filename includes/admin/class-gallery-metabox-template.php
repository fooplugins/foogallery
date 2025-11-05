<?php
/**
 * Class to handle adding the Template metabox to a gallery
 */

// TODO : remove phpcs:disable comment and work through plugin check warnings.


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

			$message = __( 'Once you are happy with your selected layout, you can minimize this section to save space', 'foogallery' );
			$hide_help = 'on' == foogallery_get_setting( 'hide_gallery_template_help' );
			if ( $hide_help ) {
				$message = '';
			}

			// Allow SVG tags for icons (wp_kses_post doesn't support SVG)
			$svg_allowed = array(
				'svg' => array(
					'viewbox' => true,
					'viewBox' => true,
					'xmlns' => true,
					'width' => true,
					'height' => true,
					'class' => true,
				),
				'g' => array(
					'transform' => true,
					'class' => true,
				),
				'rect' => array(
					'x' => true,
					'y' => true,
					'width' => true,
					'height' => true,
					'class' => true,
				),
				'polygon' => array(
					'points' => true,
					'class' => true,
				),
				'circle' => array(
					'cx' => true,
					'cy' => true,
					'r' => true,
					'class' => true,
				),
				'path' => array(
					'd' => true,
					'class' => true,
				),
				'polyline' => array(
					'points' => true,
					'class' => true,
				),
			);

			?>
			<div class="foogallery-template-card-selector" data-metabox-message="<?php echo esc_attr( $message ); ?>">
				<div class="foogallery-template-cards-container">
					<?php foreach ( $gallery_templates as $template ) {
						$selected_class = ( $current_gallery_template === $template['slug'] ) ? 'selected' : '';
						$extra_class = $template['class'] ?? '';
						$extra_html = $template['html'] ?? '';
						?>
						<div class="foogallery-template-card <?php echo esc_attr( $selected_class ); ?> <?php echo esc_attr( $extra_class ); ?>" 
							data-template="<?php echo esc_attr( $template['slug'] ); ?>">
							<?php echo wp_kses( $template['icon'], $svg_allowed ); ?>
							<h4><?php echo esc_html( $template['name'] ); ?></h4>
							<?php echo wp_kses_post( $extra_html ); ?>
						</div>
					<?php } ?>
				</div>
				
				<!-- Keep the hidden select for form submission -->
				<input type="hidden" id="FooGallerySettings_GalleryTemplate" name="<?php echo esc_attr( FOOGALLERY_META_TEMPLATE ); ?>" value="<?php echo esc_attr( $current_gallery_template ); ?>" />
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
