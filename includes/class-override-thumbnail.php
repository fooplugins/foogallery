<?php
/*
 * FooGallery Attachment Override Thumbnail
 */

if ( ! class_exists( 'FooGallery_Override_Thumbnail' ) ) {

    class FooGallery_Override_Thumbnail {

        /**
         * Primary class constructor.
         */
        public function __construct() {
            add_filter( 'foogallery_attachment_custom_fields', array( $this, 'foogallery_add_override_thumbnail_field' ) );
            add_action( 'foogallery_attachment_modal_tab_content_thumbnails', array( $this, 'display_modal_fields' ), 10, 1 );
            add_action( 'wp_ajax_foogallery_remove_alternate_img', array( $this, 'ajax_remove_override' ) );
            add_action( 'foogallery_attachment_modal_after_tab_container', array( $this, 'extra_content_for_override_thumbnail' ), 50, 1 );
        }

        function display_modal_fields( $modal_data ) {
            ?>
            <div class="foogallery-attachments-list-bar">
                <div class="settings">
                    <span class="setting override-thumbnail <?php echo $modal_data['override_class']; ?>" data-setting="override-thumbnail">
                        <label for="attachment-details-two-column-override-thumbnail" class="name"><?php _e('Alternate Thumbnail URL', 'foogallery'); ?></label>
                        <input type="text" id="attachments-foogallery-override-thumbnail" value="<?php echo $modal_data['alternate_img_src']; ?>" readonly>
                        <input type="hidden" name="foogallery[override-thumbnail-id]" id="attachments-foogallery-override-thumbnail-id" value="<?php echo $modal_data['foogallery_override_thumbnail']; ?>">
                    </span>
                    <span class="setting override-thumbnail-preview <?php echo $modal_data['override_class']; ?>" data-setting="override-thumbnail-preview">
                        <label for="attachment-details-two-column-override-thumbnail-preview" class="name"><?php _e('Alternate Thumbnail Preview', 'foogallery'); ?></label>
                        <img id="attachment-details-two-column-override-thumbnail-preview" src="<?php echo $modal_data['alternate_img_src']; ?>" alt="Alternate Thumbnail">
                    </span>
                    <span class="setting alternate-image-upload-settings" data-setting="alternate-image-upload">
                        <div class="alternate-image-upload-wrap">
                            <button type="button" class="button button-primary button-large" id="foogallery-img-modal-alternate-image-upload"
                                    data-uploader-title="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
                                    data-uploader-button-text="<?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>"
                                    data-img-id="<?php echo $modal_data['img_id']; ?>">
                                <?php _e( 'Override Thumbnail Image', 'foogallery' ); ?>
                            </button>
                            <button type="button" class="button button-primary button-large <?php echo $modal_data['override_class']; ?>" id="foogallery-img-modal-alternate-image-delete"
                                    data-uploader-title="<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>"
                                    data-uploader-button-text="<?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>"
                                    data-img-id="<?php echo $modal_data['img_id']; ?>">
                                <?php _e( 'Clear Override Thumbnail', 'foogallery' ); ?>
                            </button>
                            <span id="foogallery_clear_alternate_img_spinner" class="spinner"></span>
                        </div>
                    </span>
                </div>
            </div>
            <?php
        }

        /**
         * Adds a custom field to the attachments for override thumbnail
         *
         * @param $fields array
         *
         * @return array
         */
        public function foogallery_add_override_thumbnail_field( $fields ) {
            $fields['foogallery_override_thumbnail'] = array(
                'label'       =>  __( 'Override Thumbnail', 'foogallery' ),
                'input'       => 'text',
                'helps'       => __( 'Add another image to override this attachment', 'foogallery' ),
            );

            return $fields;
        }

        /**
         * Ajax function to remove override thumbnail from the attachment
         */
        public function ajax_remove_override() {

            // Check for nonce security
            if ( ! wp_verify_nonce( $_POST['nonce'], 'foogallery-modal-nonce' ) ) {
                die ( 'Busted!');
            }

            $img_id = sanitize_text_field( $_POST['img_id'] );

            delete_post_meta( $img_id, '_foogallery_override_thumbnail' );

            wp_die();
        }

        public function extra_content_for_override_thumbnail( $modal_data ) {
            ?>
            <script>
                jQuery( function() {
                    $(document).on('click', '#foogallery-img-modal-alternate-image-upload', function(e) {
                        e.preventDefault();
                        $('#foogallery-image-edit-modal').data('img_type', 'alternate');
                        FOOGALLERY.mediaModalTitle = $(this).data( 'uploader-title' );
                        FOOGALLERY.mediaModalButtonText = $(this).data( 'uploader-button-text' );
                        var img_id = $(this).data('img-id');
                        FOOGALLERY.openMediaModal(img_id);
                    });

                    $(document).on('click', '#foogallery-img-modal-alternate-image-delete', function () {
                        $('#foogallery_clear_alternate_img_spinner').addClass('is-active');
                        var img_id = $(this).attr('data-img-id');
                        var nonce = $('#foogallery-panel-main').data('nonce');
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                'action': 'foogallery_remove_alternate_img',
                                'img_id': img_id,
                                'nonce': nonce
                            },
                            success: function (data) {
                                $('#foogallery_clear_alternate_img_spinner').removeClass('is-active');
                                $('#foogallery-image-edit-modal .tab-panels .settings span.setting.override-thumbnail').removeClass('is-override-thumbnail');
                                $('#foogallery-image-edit-modal .tab-panels .settings span.setting.override-thumbnail-preview').removeClass('is-override-thumbnail');
                                $('#foogallery-image-edit-modal .tab-panels .settings span.setting.alternate-image-delete').removeClass('is-override-thumbnail');
                            }
                        });
                    });
                });
            </script>
            <?php
        }
    }
}