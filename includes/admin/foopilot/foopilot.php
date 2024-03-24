<?php
require_once FOOGALLERY_PATH . 'includes/admin/foopilot/class-foopilot.php';
require_once FOOGALLERY_PATH . 'includes/admin/foopilot/class-foopilot-ajax-handler.php';
require_once FOOGALLERY_PATH . 'includes/admin/foopilot/class-foopilot-points-manager.php';
require_once FOOGALLERY_PATH . 'includes/admin/foopilot/modals/class-foopilot-modal.php';

/**
 * Generate a nonce.
 *
 * @return string The generated nonce.
 */
function foopilot_generate_nonce() {
	return wp_create_nonce( 'foopilot_nonce' );
}

/**
 * Verify the nonce.
 *
 * @param string $nonce The nonce to verify.
 * @return bool Whether the nonce is valid.
 */
function foopilot_verify_nonce( $nonce ) {
	return wp_verify_nonce( $nonce, 'foopilot_nonce' );
}
