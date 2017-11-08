<?php
/**
 * FooGallery Pro Bulk Copy Class
 */
if ( ! class_exists( 'FooGallery_Pro_Bulk_Copy' ) ) {

    class FooGallery_Pro_Bulk_Copy
    {
        function __construct()
        {
            //add the bulk copy metabox
            add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_bulk_copy_meta_box_to_gallery' ) );

            // Ajax call for starting the bulk copy
            add_action( 'wp_ajax_foogallery_bulk_copy_start', array( $this, 'ajax_bulk_copy_start' ) );

            // Ajax call for running the bulk copy
            add_action( 'wp_ajax_foogallery_bulk_copy_run', array( $this, 'ajax_bulk_copy_run' ) );
        }

        public function ajax_bulk_copy_run()
        {
            if (check_admin_referer('foogallery_bulk_copy_run', 'foogallery_bulk_copy_run_nonce')) {
                $data = $_POST['foogallery_bulk_copy'];


            }
        }

        public function ajax_bulk_copy_start()
        {
            if (check_admin_referer('foogallery_bulk_copy_start', 'foogallery_bulk_copy_start_nonce')) {

                $foogallery_id = $_POST['foogallery'];
                $exclude[] = intval($foogallery_id);
                ?>
                <style>
                    .foogallery_bulk_copy_custom_selection {
                        display: inline-block;
                        width: 40%;
                        margin-bottom:5px;
                    }

                    .foogallery_bulk_copy_custom_selection label {
                        vertical-align: top;
                    }
                </style>
                <form class=".foogallery-bulk-copy-settings">
                <table class="foogallery-metabox-settings">
                    <tr class="foogallery_template_field">
                        <th>
                            <label for="FooGallerySettings_default_lightbox"><?php _e('What do you want to copy?', 'foogallery'); ?></label>
                            <span data-balloon-length="large" data-balloon-pos="right" data-balloon="Choose which settings you wish to bulk copy to other galleries."><i class="dashicons dashicons-editor-help"></i></span>
                        </th>
                        <td>
                            <div class="foogallery_metabox_field-radio">
                                <input checked="checked" type="checkbox" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Source_Settings" value="settings">
                                <label for="FooGalleryBulkCopy_Source_Settings"><?php _e('Gallery Settings', 'foogallery'); ?></label><br>

                                <input checked="checked" type="checkbox" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Source_Retina" value="retina">
                                <label for="FooGalleryBulkCopy_Source_Retina"><?php _e('Retina Settings', 'foogallery'); ?></label><br>

                                <input checked="checked" type="checkbox" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Source_Sorting" value="sorting">
                                <label for="FooGalleryBulkCopy_Source_Sorting"><?php _e('Sorting Settings', 'foogallery'); ?></label><br>

                                <input checked="checked" type="checkbox" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Source_CustomCSS" value="customcss">
                                <label for="FooGalleryBulkCopy_Source_CustomCSS"><?php _e('Custom CSS', 'foogallery'); ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="foogallery_template_field">
                        <th>
                            <label for="FooGallerySettings_default_lightbox"><?php _e('Which galleries do you want to copy to?', 'foogallery'); ?></label>
                            <span data-balloon-length="large" data-balloon-pos="right" data-balloon="Choose which galleries you want to copy the settings to."><i class="dashicons dashicons-editor-help"></i></span>
                        </th>
                        <td>
                            <div class="foogallery_metabox_field-radio">
                                <input type="radio" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Destination_All" value="all">
                                <label for="FooGalleryBulkCopy_Destination_All"><?php _e('All Galleries', 'foogallery'); ?></label><br>

<!--                                <input type="radio" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Destination_Mine" value="mine">-->
<!--                                <label for="FooGalleryBulkCopy_Destination_Mine">--><?php //_e('All Of My Galleries', 'foogallery'); ?><!--</label><br>-->

                                <input type="radio" name="foogallery_bulk_copy[source]" id="FooGalleryBulkCopy_Destination_Custom" value="custom">
                                <label for="FooGalleryBulkCopy_Destination_Custom"><?php _e('Custom Selection', 'foogallery'); ?></label><br>
                            </div>
                        </td>
                    </tr>
                    <tr class="foogallery_template_field ">
                        <th>
                            <label for="FooGallerySettings_default_lightbox"><?php _e('Select the galleries', 'foogallery'); ?></label>
                        </th>
                        <td>
                            <div class="foogallery_metabox_field-radio bulk_copy_custom">
                                <?php foreach ( foogallery_get_all_galleries($exclude) as $foogallery ) { ?>
                                    <div class="foogallery_bulk_copy_custom_selection">
                                        <input type="checkbox" name="foogallery_bulk_copy[custom]" id="FooGalleryBulkCopy_Custom_<?php echo $foogallery->ID; ?>" value="<?php echo $foogallery->ID; ?>">
                                        <label for="FooGalleryBulkCopy_Custom_<?php echo $foogallery->ID; ?>"><?php echo $foogallery->name . ' [' . $foogallery->ID . ']'; ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="foogallery_bulk_copy[foogallery]" value="<?php echo $foogallery_id; ?>" />
                <button class="button button-primary button-large" id="foogallery_bulk_copy_run"><?php _e( 'Run Bulk Copy', 'foogallery' ); ?></button>
                <?php wp_nonce_field( 'foogallery_bulk_copy_run', 'foogallery_bulk_copy_run_nonce', false ); ?>
                </form>
                <?php
            }

            die();
        }

        /**
         * Add a metabox to the gallery for bulk copying
         * @param $post
         */
        function add_bulk_copy_meta_box_to_gallery($post) {
            add_meta_box(
                'foogallery_bulk_copy',
                __( 'Bulk Copy', 'foogallery' ),
                array( $this, 'render_gallery_bulk_copy_metabox' ),
                FOOGALLERY_CPT_GALLERY,
                'normal',
                'low'
            );
        }

        /**
         * Render the bulk copy metabox on the gallery edit page
         * @param $post
         */
        function render_gallery_bulk_copy_metabox( $post ) {
            ?>
            <script>
                jQuery(function ($) {
                    $('#foogallery_bulk_copy_container').on('click', '#foogallery_bulk_copy_start', function(e) {
                        e.preventDefault();

                        $('#foogallery_bulk_copy_spinner').addClass('is-active');
                        var data = 'action=foogallery_bulk_copy_start' +
                            '&foogallery=<?php echo $post->ID; ?>' +
                            '&foogallery_bulk_copy_start_nonce=' + $('#foogallery_bulk_copy_start_nonce').val() +
                            '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function(data) {
                                $('#foogallery_bulk_copy_container').html(data);
                                $('#foogallery_bulk_copy_spinner').removeClass('is-active');
                            }
                        });
                    });

                    $('#foogallery_bulk_copy_container').on('click', '#foogallery_bulk_copy_run', function(e) {
                        e.preventDefault();

                        $('#foogallery_bulk_copy_spinner').addClass('is-active');
                        var data = 'action=foogallery_bulk_copy_run' +
                            '&bulk_copy_settings=' + $('.foogallery-bulk-copy-settings').serialize() +
                            '&foogallery_bulk_copy_run_nonce=' + $('#foogallery_bulk_copy_run_nonce').val() +
                            '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function(data) {
                                $('#foogallery_bulk_copy_container').html(data);
                                $('#foogallery_bulk_copy_spinner').removeClass('is-active');
                            }
                        });
                    });
                });
            </script>
            <p class="foogallery-help">
                <?php _e('You can bulk copy the settings from this gallery to other galleries in a few easy steps. To get started, click the button below. Please be sure to save your gallery before you start the copy, as only the saved settings stored in the database will be copied across.', 'foogallery'); ?>
            </p>
            <p>
                <div class="foogallery_metabox_actions">
                    <div id="foogallery_bulk_copy_container">
                        <button class="button button-primary button-large" id="foogallery_bulk_copy_start"><?php _e( 'Start Bulk Copy', 'foogallery' ); ?></button>
                        <?php wp_nonce_field( 'foogallery_bulk_copy_start', 'foogallery_bulk_copy_start_nonce', false ); ?>
                    </div>
                    <span id="foogallery_bulk_copy_spinner" class="spinner"></span>
                </div>
            </p>
            <?php
        }
    }
}