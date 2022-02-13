<?php
/**
 * FooGallery Pro Master Galleries Class
 */
if ( ! class_exists( 'FooGallery_Pro_Master_Galleries' ) ) {

    class FooGallery_Pro_Master_Galleries {

        function __construct() {
            //add the bulk copy metabox
            add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_master_gallery_meta_box_to_gallery' ) );
        }

        /**
         * Add a metabox to the gallery for master galleries
         * @param $post
         */
        function add_master_gallery_meta_box_to_gallery($post) {
            add_meta_box(
                'foogallery_bulk_copy',
                __( 'Master Galleries', 'foogallery' ),
                array( $this, 'render_metabox' ),
                FOOGALLERY_CPT_GALLERY,
                'side',
                'low'
            );
        }

        /**
         * Render the master gallery metabox on the gallery edit page
         * @param $post
         */
        function render_metabox( $post ) {
            ?>
            <script type="text/javascript">
                jQuery(function ($) {

                });
            </script>
            <div>
                <p class="foogallery-help"><?php _e('You can set this gallery to be a master gallery.', 'foogallery'); ?></p>
            </div>
            <br/>
            <div id="foogallery_master_gallery_container">
                <button class="button button-primary button-large" id="foogallery_master_gallery_set"><?php _e( 'Set As Master Gallery', 'foogallery' ); ?></button>
                <?php wp_nonce_field( 'foogallery_master_gallery_set', 'foogallery_master_gallery_set_nonce', false ); ?>
                <span class="foogallery_master_gallery_spinner spinner"></span>
            </div>
            <?php
        }
    }
}