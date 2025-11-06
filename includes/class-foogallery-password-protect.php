<?php
/**
 * Class used to handle password protected galleries.
 */
if ( ! class_exists( 'FooGallery_Password_Protect' ) ) {

	class FooGallery_Password_Protect {

		function __construct() {
            add_filter( 'the_password_form', array( $this, 'customize_password_form_for_galleries' ), 10, 3 );
		}

		/**
		 * Customize the password form when used for a gallery.
		 *
		 * @param string $output The default password form HTML
		 * @param WP_Post $post The post object
		 * @param string $invalid_password The invalid password message
		 * @return string Modified password form HTML
		 */
		public function customize_password_form_for_galleries( $output, $post, $invalid_password ) {
			global $current_foogallery;

			if ( empty( $current_foogallery ) ) {
				return $output;
			}

			// Check if we're in gallery context
			$gallery = foogallery::get( $post );
			
			if ( !empty( $gallery ) && $current_foogallery->ID === $gallery->ID ) {
				// Get current URL for form action
				$current_url = '';
				if ( isset( $_SERVER['REQUEST_URI'] ) ) {
					$current_url = esc_url_raw( $_SERVER['REQUEST_URI'] );
				}
				
				// Replace the redirect_to hidden input value with current URL
				$output = preg_replace(
					'/<input type="hidden" name="redirect_to" value="[^"]*" \/>/',
					'<input type="hidden" name="redirect_to" value="' . esc_attr( $current_url ) . '" />',
					$output
				);
                
                // Replace the default text with gallery-specific text
				$output = str_replace(
					__( 'This content is password protected. To view it please enter your password below:' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
					__( 'This gallery is password protected. To view it please enter your password below:', 'foogallery' ),
					$output
				);
			}
			
			return $output;
		}
    }
}