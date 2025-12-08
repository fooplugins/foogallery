<?php
/**
 * Handles automatic Freemius license activation from a predefined constant.
 *
 * @package   FooGallery
 * @author    FooPlugins
 * @license   GPL-2.0+
 * @link      https://github.com/fooplugins/foogallery
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Automatically applies a Freemius license key defined via a constant.
 */
class FooGallery_License_Constant_Handler {

    const LICENSE_CONSTANT = 'FOOGALLERY_LICENSE_KEY';

    const OPTION_STATE = 'foogallery_constant_license_activation_state';

    /**
     * Mark that activation just ran so we can attempt license application on the next admin load.
     *
     * @return void
     */
    public static function flag_activation() {
        if ( ! defined( self::LICENSE_CONSTANT ) ) {
            return;
        }

        update_option( self::OPTION_STATE, 'pending' );
    }

    /**
     * Boot the handler hooks.
     *
     * @return void
     */
    public static function init() {
        if ( ! defined( self::LICENSE_CONSTANT ) ) {
            return;
        }

        $handler = new self();

        add_action( 'foogallery_fs_loaded', array( $handler, 'license_key_auto_activation' ) );
        add_action( 'admin_init', array( $handler, 'license_key_auto_activation' ) );
    }

    /**
     * Attempt to apply the configured license key.
     *
     * @return void
     */
    public function license_key_auto_activation() {
        $state = get_option( self::OPTION_STATE, '' );

        if ( 'pending' !== $state && 1 !== $state ) {
            return;
        }

        if ( is_network_admin() || ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        if ( ! defined( self::LICENSE_CONSTANT ) ) {
            return;
        }

        $license_key = $this->sanitize_license_constant( constant( self::LICENSE_CONSTANT ) );

        if ( '' === $license_key ) {
            $this->mark_state( 'invalid_constant' );

            return;
        }

        if ( ! function_exists( 'foogallery_fs' ) ) {
            return;
        }

        $fs = foogallery_fs();

        if ( ! $fs->has_api_connectivity() ) {
            return;
        }

        if ( $fs->has_active_valid_license() ) {
            $this->mark_state( 'done' );

            return;
        }

        try {
            $next_page = $fs->activate_migrated_license( $license_key, null, null, array(), get_current_blog_id() );
        } catch ( Exception $exception ) {
            $this->mark_state( 'unexpected_error' );

            return;
        }

        if ( $fs->can_use_premium_code() ) {
            $this->mark_state( 'done' );

            if ( is_string( $next_page ) ) {
                fs_redirect( $next_page );
            }
        } else {
            $this->mark_state( 'failed' );
        }
    }

    /**
     * Validate and normalize the license constant value.
     *
     * @param mixed $license_constant Raw constant value.
     *
     * @return string
     */
    private function sanitize_license_constant( $license_constant ) {
        if ( ! is_string( $license_constant ) ) {
            return '';
        }

        $license_key = trim( $license_constant );

        if ( '' === $license_key ) {
            return '';
        }

        if ( ! preg_match( '/^[A-Za-z0-9-]{10,}$/', $license_key ) ) {
            return '';
        }

        return $license_key;
    }

    /**
     * Update the activation attempt state.
     *
     * @param string $state New state value.
     *
     * @return void
     */
    private function mark_state( $state ) {
        update_option( self::OPTION_STATE, $state );
    }
}
