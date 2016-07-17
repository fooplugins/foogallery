<?php
/*
 * FooGallery Admin Notices class
 */

if ( ! class_exists( 'FooGallery_Admin_Notices' ) ) {

    class FooGallery_Admin_Notices {

        public function __construct() {
            add_action( 'admin_notices', array( $this, 'display_thumb_test_notice') );
            add_action( 'foogallery_thumbnail_generation_test', array( $this, 'save_test_results') );
        }

        function should_run_tests() {
            $option = get_option( FOOGALLERY_OPTION_THUMB_TEST );
            $option_value = $this->generate_option_value();

            if ( !isset( $option ) ) {
                //we have never run tests before
                return true;
            } else {
                $option_key = $option['key'];
                if ( $option_value !== $option_key ) {
                    //either the PHP version or Host has changed. In either case, we should run tests again!
                    return true;
                }
            }

            return false;
        }

        function should_show_alert() {
            $option = get_option( FOOGALLERY_OPTION_THUMB_TEST );

            if ( isset( $option ) && array_key_exists( 'results', $option ) ) {
                $results = $option['results'];
                //should show the alert if the tests were not a success
                return !$results['success'];
            }

            return false;
        }

        function generate_option_value() {
            $php_version = phpversion();
            $host = home_url();
            return "php$($php_version}-{$host}";
        }

        function save_test_results($results) {
            update_option( FOOGALLERY_OPTION_THUMB_TEST, array (
                'key' => $this->generate_option_value(),
                'results' => $results
            ));
        }

        function display_thumb_test_notice() {
            //check if we are on specific admin pages
            if ( FOOGALLERY_CPT_GALLERY === foo_current_screen_post_type() ) {

                if ($this->should_run_tests()) {
                    $thumbs = new FooGallery_Thumbnails();
                    $thumbs->run_thumbnail_generation_tests();
                }

                if ($this->should_show_alert()) {
                    ?>
                    <div class="notice error">
                        <p>
                            <strong><?php _e('Thumbnail Generation Alert!', 'foogallery'); ?></strong><br/>
                            <?php _e('There is a problem generating thumbnails for your gallery. Please check that your hosting provider has the GD Image Library extension installed and enabled.' , 'foogallery'); ?><br />
                            <?php _e('If thumbnails cannot be generated, then full-sized, uncropped images will be used instead. This will result in slow page load times, and thumbnails that do not look correct.', 'foogallery'); ?>
                            <br/>
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }

}