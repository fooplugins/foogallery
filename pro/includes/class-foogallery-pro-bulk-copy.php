<?php
/**
 * FooGallery Pro Bulk Copy Class
 */
if ( ! class_exists( 'FooGallery_Pro_Bulk_Copy' ) ) {

    class FooGallery_Pro_Bulk_Copy {

        function __construct() {
            //add the bulk copy metabox
            add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_bulk_copy_meta_box_to_gallery' ) );

            // Ajax call for starting the bulk copy
            add_action( 'wp_ajax_foogallery_bulk_copy_start', array( $this, 'ajax_bulk_copy_start' ) );

            // Ajax call for running the bulk copy
            add_action( 'wp_ajax_foogallery_bulk_copy_run', array( $this, 'ajax_bulk_copy_run' ) );
        }

        public function ajax_bulk_copy_run() {
            if (check_admin_referer('foogallery_bulk_copy_run', 'foogallery_bulk_copy_run_nonce')) {
                $data = $_POST['foogallery_bulk_copy'];
                $foogallery_id = $data['foogallery'];
                $errors = $settings = $galleries = array();
                $destination = false;
                if ( !array_key_exists( 'settings', $data ) ) {
                    $errors['settings'] = __('You need to decide which settings you want to copy across.', 'foogallery');
                } else {
                    $settings = $data['settings'];
                }

                if ( !isset( $data['destination'] ) ) {
                    $errors['destination'] = __('You need to choose the destination you want to copy to.', 'foogallery');
                } else {
                    $destination = $data['destination'];
                }

                if ( 'custom' === $destination ) {
                    if (!isset($data['gallery'])) {
                        $errors['gallery'] = __('You need to select which galleries you want to copy to.', 'foogallery');
                    } else {
                        $galleries = $data['gallery'];
                    }
                }

                if ( count($errors) ) {
                    $this->output_bulk_copy_form( $foogallery_id, $settings, $destination, $galleries, $errors );
                } else {
                    //if we get here, then we can perform a bulk copy!
                    $result = $this->run_bulk_copy( $foogallery_id, $settings, $destination, $galleries );
                    ?>
                    <div class="foogallery_bulk_copy_result"><?php echo $result; ?></div>
                    <button class="button button-primary button-large" id="foogallery_bulk_copy_start"><?php _e( 'Start Another Bulk Copy', 'foogallery' ); ?></button>
                    <?php wp_nonce_field( 'foogallery_bulk_copy_start', 'foogallery_bulk_copy_start_nonce', false ); ?>
                    <span class="foogallery_bulk_copy_spinner spinner"></span><?php
                }
            }

            die();
        }

        private function run_bulk_copy($foogallery_id, $settings, $destination = false, $galleries = array()) {

            if ( false === $settings ) {
                return __('No settings were selected to be copied!', 'foogallery');
            }

            $args = array(
                'post_type'     => FOOGALLERY_CPT_GALLERY,
                'post_status'	=> array( 'publish', 'draft' ),
                'cache_results' => false,
                'nopaging'      => true,
            );

            if ( 'all' === $destination ) {
                $exclude[] = intval($foogallery_id);
                $args['post__not_in'] = $exclude;
            } else {
                $args['post__in'] = $galleries;
            }

            $gallery_posts = get_posts( $args );

            $source_gallery_template = get_post_meta( $foogallery_id, FOOGALLERY_META_TEMPLATE, true );
            $source_settings = get_post_meta( $foogallery_id, FOOGALLERY_META_SETTINGS, true );
            $source_sorting = get_post_meta( $foogallery_id, FOOGALLERY_META_SORT, true );
            $source_retina = get_post_meta( $foogallery_id, FOOGALLERY_META_RETINA, true );
            $source_custom_css = get_post_meta( $foogallery_id, FOOGALLERY_META_CUSTOM_CSS, true );

            $count = 0;
            foreach ( $gallery_posts as $post ) {

                //override the post meta for the gallery
                if ( array_key_exists(FOOGALLERY_META_SETTINGS, $settings) ) {
                    update_post_meta( $post->ID, FOOGALLERY_META_TEMPLATE, $source_gallery_template );
                    update_post_meta( $post->ID, FOOGALLERY_META_SETTINGS, $source_settings );
                }

                if ( array_key_exists(FOOGALLERY_META_SORT, $settings) ) {
                    update_post_meta( $post->ID, FOOGALLERY_META_SORT, $source_sorting );
                }

                if ( array_key_exists(FOOGALLERY_META_RETINA, $settings) ) {
                    update_post_meta( $post->ID, FOOGALLERY_META_RETINA, $source_retina );
                }

                if ( array_key_exists(FOOGALLERY_META_CUSTOM_CSS, $settings) ) {
                    update_post_meta( $post->ID, FOOGALLERY_META_CUSTOM_CSS, $source_custom_css );
                }

                $count++;
            }

            return sprintf( __( 'Successfully copied settings to %d galleries.', 'foogallery' ), $count );
        }

        public function output_bulk_copy_form($foogallery_id, $settings = false, $destination = false, $galleries = array(), $errors = array()) {
            //check if we need to set some defaults
            if ( false === $settings ) {
                $settings[FOOGALLERY_META_SETTINGS] = FOOGALLERY_META_SETTINGS;
                $settings[FOOGALLERY_META_RETINA] = FOOGALLERY_META_RETINA;
                $settings[FOOGALLERY_META_SORT] = FOOGALLERY_META_SORT;
                $settings[FOOGALLERY_META_CUSTOM_CSS] = FOOGALLERY_META_CUSTOM_CSS;
            }
            $exclude[] = intval($foogallery_id);
            ?>
            <div class="foogallery-bulk-copy-settings">
                <table class="foogallery-metabox-settings">
                    <tr class="foogallery_template_field">
                        <th>
                            <label><?php _e('Which Settings?', 'foogallery'); ?></label>
                            <span data-balloon-length="large" data-balloon-pos="right" data-balloon="Choose which settings you wish to bulk copy to other galleries."><i class="dashicons dashicons-editor-help"></i></span>
                        </th>
                        <td>
                            <?php if ( array_key_exists( 'settings', $errors ) ) {?>
                                <div class="foogallery_bulk_copy_error"><?php echo $errors['settings']; ?></div>
                            <?php } ?>
                            <div class="foogallery_metabox_field-radio">
                                <input <?php echo (array_key_exists(FOOGALLERY_META_SETTINGS, $settings) ? 'checked="checked"' : ''); ?> type="checkbox" name="foogallery_bulk_copy[settings][<?php echo FOOGALLERY_META_SETTINGS; ?>]" id="FooGalleryBulkCopy_Settings_Template" value="<?php echo FOOGALLERY_META_SETTINGS; ?>">
                                <label for="FooGalleryBulkCopy_Settings_Template"><?php _e('Gallery Template & Settings', 'foogallery'); ?></label><br>

                                <input <?php echo (array_key_exists(FOOGALLERY_META_RETINA, $settings) ? 'checked="checked"' : ''); ?> type="checkbox" name="foogallery_bulk_copy[settings][<?php echo FOOGALLERY_META_RETINA; ?>]" id="FooGalleryBulkCopy_Settings_Retina" value="<?php echo FOOGALLERY_META_RETINA; ?>">
                                <label for="FooGalleryBulkCopy_Settings_Retina"><?php _e('Retina Settings', 'foogallery'); ?></label><br>

                                <input <?php echo (array_key_exists(FOOGALLERY_META_SORT, $settings) ? 'checked="checked"' : ''); ?> type="checkbox" name="foogallery_bulk_copy[settings][<?php echo FOOGALLERY_META_SORT; ?>]" id="FooGalleryBulkCopy_Settings_Sorting" value="<?php echo FOOGALLERY_META_SORT; ?>">
                                <label for="FooGalleryBulkCopy_Settings_Sorting"><?php _e('Sorting Settings', 'foogallery'); ?></label><br>

                                <input <?php echo (array_key_exists(FOOGALLERY_META_CUSTOM_CSS, $settings) ? 'checked="checked"' : ''); ?> type="checkbox" name="foogallery_bulk_copy[settings][<?php echo FOOGALLERY_META_CUSTOM_CSS; ?>]" id="FooGalleryBulkCopy_Settings_CustomCSS" value="<?php echo FOOGALLERY_META_CUSTOM_CSS; ?>">
                                <label for="FooGalleryBulkCopy_Settings_CustomCSS"><?php _e('Custom CSS', 'foogallery'); ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="foogallery_template_field">
                        <th>
                            <label for="FooGallerySettings_default_lightbox"><?php _e('Destination', 'foogallery'); ?></label>
                            <span data-balloon-length="large" data-balloon-pos="right" data-balloon="Choose which galleries you want to copy the settings to."><i class="dashicons dashicons-editor-help"></i></span>
                        </th>
                        <td>
                            <?php if ( array_key_exists( 'destination', $errors ) ) {?>
                                <div class="foogallery_bulk_copy_error"><?php echo $errors['destination']; ?></div>
                            <?php } ?>
                            <div class="foogallery_metabox_field-radio">
                                <input <?php echo ('all' === $destination ? 'checked="checked"' : ''); ?> type="radio" name="foogallery_bulk_copy[destination]" id="FooGalleryBulkCopy_Destination_All" value="all">
                                <label for="FooGalleryBulkCopy_Destination_All"><?php _e('All Galleries', 'foogallery'); ?></label><br>

                                <input <?php echo ('custom' === $destination ? 'checked="checked"' : ''); ?> type="radio" name="foogallery_bulk_copy[destination]" id="FooGalleryBulkCopy_Destination_Custom" value="custom">
                                <label for="FooGalleryBulkCopy_Destination_Custom"><?php _e('Custom Selection', 'foogallery'); ?></label><br>
                            </div>
                        </td>
                    </tr>
                    <tr class="foogallery_template_field">
                        <th>
                            <label for="FooGallerySettings_default_lightbox"><?php _e('Select the galleries', 'foogallery'); ?></label>
                        </th>
                        <td>
                            <?php if ( array_key_exists( 'gallery', $errors ) ) {?><div class="foogallery_bulk_copy_error"><?php echo $errors['gallery']; ?></div><?php } ?>
                            <div class="foogallery_metabox_field-radio bulk_copy_custom">
                                <?php foreach ( foogallery_get_all_galleries($exclude) as $foogallery ) {
                                    $checked = array_key_exists( $foogallery->ID, $galleries ) ? 'checked="checked"' : '';
                                    ?><div class="foogallery_bulk_copy_custom_selection">
                                    <input <?php echo $checked; ?> type="checkbox" name="foogallery_bulk_copy[gallery][<?php echo $foogallery->ID; ?>]" id="FooGalleryBulkCopy_Custom_<?php echo $foogallery->ID; ?>" value="<?php echo $foogallery->ID; ?>">
                                    <label for="FooGalleryBulkCopy_Custom_<?php echo $foogallery->ID; ?>"><?php echo $foogallery->name . ' [' . $foogallery->ID . ']'; ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <p>
                    <input type="hidden" name="foogallery_bulk_copy[foogallery]" value="<?php echo $foogallery_id; ?>" />
                    <button class="button button-primary button-large" id="foogallery_bulk_copy_run"><?php _e( 'Run Bulk Copy', 'foogallery' ); ?></button>
                    <span class="foogallery_bulk_copy_spinner spinner"></span>
                    <?php if ( count ($errors) > 0 ) { ?>
                        <br />
                        <div class="foogallery_bulk_copy_error"><?php _e('The bulk copy could not be run, due to errors. Please see above and correct the errors before continuing.', 'foogallery'); ?></div>
                    <?php } ?>
                    <?php wp_nonce_field( 'foogallery_bulk_copy_run', 'foogallery_bulk_copy_run_nonce', false ); ?>
                </p>
            </div>
            <?php
        }

        public function ajax_bulk_copy_start()
        {
            if (check_admin_referer('foogallery_bulk_copy_start', 'foogallery_bulk_copy_start_nonce')) {
                $foogallery_id = $_POST['foogallery'];
                $this->output_bulk_copy_form( $foogallery_id );
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
            <style type="text/css">
                .foogallery_bulk_copy_custom_selection {
                    display: inline-block;
                    width: 40%;
                    margin-bottom:5px;
                }

                .foogallery_bulk_copy_custom_selection label {
                    vertical-align: top;
                }

                .foogallery_bulk_copy_error {
                    position: relative;
                    line-height: 16px;
                    padding: 6px 5px;
                    font-size: 14px;
                    text-align: left;
                    margin-bottom: 5px;
                    background-color: #FFe4e4;
                    border-left: 4px solid #be392f;
                    -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                }

                .foogallery_bulk_copy_spinner {
                    float: none !important;
                }

                .foogallery_bulk_copy_result {
                    position: relative;
                    line-height: 16px;
                    padding: 6px 5px;
                    font-size: 14px;
                    text-align: left;
                    margin-top: 5px;
                    margin-bottom: 5px;
                    background-color: #e4ffe4;
                    border-left: 4px solid #1bbe33;
                    -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                }
            </style>
            <script type="text/javascript">
                jQuery(function ($) {
                    $('#foogallery_bulk_copy_container').on('click', '#foogallery_bulk_copy_start', function(e) {
                        e.preventDefault();

                        $('.foogallery_bulk_copy_spinner').addClass('is-active');
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
                                $('.foogallery_bulk_copy_spinner').removeClass('is-active');
                            }
                        });
                    });

                    $('#foogallery_bulk_copy_container').on('click', '#foogallery_bulk_copy_run', function(e) {
                        e.preventDefault();

                        $('.foogallery_bulk_copy_spinner').addClass('is-active');
                        var data = $('.foogallery-bulk-copy-settings').find('select, textarea, input').serialize() +
                            '&action=foogallery_bulk_copy_run' +
                            '&foogallery_bulk_copy_run_nonce=' + $('#foogallery_bulk_copy_run_nonce').val() +
                            '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function(data) {
                                $('#foogallery_bulk_copy_container').html(data);
                                $('.foogallery_bulk_copy_spinner').removeClass('is-active');
                            }
                        });
                    });
                });
            </script>
            <div>
                <p class="foogallery-help"><?php _e('You can bulk copy the settings from this gallery to other galleries in a few easy steps. To get started, click the button below. Please be sure to save your gallery before you start the copy, as only the saved settings stored in the database will be copied across.', 'foogallery'); ?></p>
            </div>
            <br/>
            <div id="foogallery_bulk_copy_container">
                <button class="button button-primary button-large" id="foogallery_bulk_copy_start"><?php _e( 'Start Bulk Copy', 'foogallery' ); ?></button>
                <?php wp_nonce_field( 'foogallery_bulk_copy_start', 'foogallery_bulk_copy_start_nonce', false ); ?>
                <span class="foogallery_bulk_copy_spinner spinner"></span>
            </div>
            <?php
        }
    }
}