<?php
/**
 * Class to help users who also use Autoptomize plugin
 * Date: 2017/11/12
 */

if ( !class_exists( 'FooGallery_Autoptimize_Compatibility' ) ) {

    class FooGallery_Autoptimize_Compatibility {

        const transient_key = 'foogallery_autoptimize_notice';

        function __construct() {
            if ( is_admin() ) {
                add_action( 'admin_notices', array( $this, 'admin_notice' ) );
                add_action( 'foogallery_admin_new_version_detected', array( $this, 'set_to_show_admin_notice' ) );

                add_action( 'wp_ajax_foogallery_autoptimize_dismiss', array( $this, 'admin_notice_dismiss' ) );
            }
        }

        /**
         * Set the transient for 3 days to display the message to flush the cache
         */
        function set_to_show_admin_notice() {
            if ( class_exists( 'autoptimizeCache' ) ) {
                set_transient(FooGallery_Autoptimize_Compatibility::transient_key, true, 3 * 24 * 60 * 60);
            }
        }

        /**
         * Display the admin notice
         */
        function admin_notice() {
            if ( !class_exists( 'autoptimizeCache' ) ) return;
            $show_notice = get_transient( FooGallery_Autoptimize_Compatibility::transient_key );
            if ( false === $show_notice ) return;
            ?>
            <script type="text/javascript">
                ( function ( $ ) {
                    $( document ).ready( function () {
                        $( '.foogallery-autoptimize-notice.is-dismissible' )
                            .on( 'click', '.notice-dismiss', function ( e ) {
                                e.preventDefault();
                                $.post( ajaxurl, {
                                    action: 'foogallery_autoptimize_dismiss',
                                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                                    _wpnonce: '<?php echo wp_create_nonce( 'foogallery_autoptimize_dismiss' ); ?>'
                                } );
                            } );
                    } );
                } )( jQuery );
            </script>
            <div class="foogallery-autoptimize-notice notice error is-dismissible">
                <p>
                    <strong><?php _e( 'FooGallery + Autoptimize : ', 'foobox-image-lightbox' ); ?></strong>
                    <?php _e( 'We noticed that you have the Autoptimize plugin installed. After updating FooGallery, please make sure you delete the Autoptimize cache from the admin bar above to make sure your galleries continue to display correctly.' ); ?>
                    <br />
                    <?php _e( 'If you continue to have issues using FooGallery and Autoptimize together, then please goto Autoptimize Plugin settings –> Java Script Options –> Exclude Scripts and add foogallery.min.js' ); ?>
                </p>
            </div>
            <?php
        }

        /**
         * Dismiss the admin notice
         */
        function admin_notice_dismiss() {
            if ( check_admin_referer( 'foogallery_autoptimize_dismiss' ) ) {
                delete_transient( FooGallery_Autoptimize_Compatibility::transient_key );
            }
        }
    }
}