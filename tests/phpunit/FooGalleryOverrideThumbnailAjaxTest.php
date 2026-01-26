<?php

class FooGalleryOverrideThumbnailAjaxTest extends WP_Ajax_UnitTestCase {
	private $editor_id;
	private $subscriber_id;
	private $attachment_id;
	private $last_status_code;

	public function setUp(): void {
		parent::setUp();

		$this->editor_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->attachment_id = self::factory()->attachment->create( array(
			'post_mime_type' => 'image/jpeg',
			'post_title' => 'Override Thumbnail Attachment',
			'guid' => 'https://example.org/override-thumbnail.jpg',
		) );

		update_post_meta( $this->attachment_id, '_foogallery_override_thumbnail', 123 );

		if ( ! has_action( 'wp_ajax_foogallery_remove_alternate_img' ) ) {
			if ( ! class_exists( 'FooGallery_Override_Thumbnail' ) ) {
				require_once FOOGALLERY_PATH . 'includes/class-override-thumbnail.php';
			}

			new FooGallery_Override_Thumbnail();
		}

		$this->last_status_code = null;
		add_filter( 'status_header', array( $this, 'capture_status_header' ), 10, 4 );
	}

	public function tearDown(): void {
		remove_filter( 'status_header', array( $this, 'capture_status_header' ), 10 );
		parent::tearDown();
	}

	public function capture_status_header( $status_header, $code, $description, $protocol ) {
		$this->last_status_code = (int) $code;

		return $status_header;
	}

	/**
	 * Tests that subscribers cannot remove override thumbnails.
	 *
	 * Why: Removing override thumbnails mutates attachment metadata and must be restricted.
	 * Setup: Create a subscriber and an attachment with an override thumbnail, then send AJAX with a valid nonce.
	 * Expectation: The response is JSON error with "Insufficient permissions.", status 403, and the meta remains.
	 *
	 * @group ajax
	 */
	public function test_remove_override_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );
		$this->last_status_code = null;

		$_POST = array(
			'nonce' => wp_create_nonce( 'foogallery-modal-nonce' ),
			'img_id' => $this->attachment_id,
		);

		try {
			$this->_handleAjax( 'foogallery_remove_alternate_img' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
			$this->assertSame( 403, $this->last_status_code );
		}

		$this->assertSame( '123', get_post_meta( $this->attachment_id, '_foogallery_override_thumbnail', true ) );
	}

	/**
	 * Tests that editors can remove override thumbnails.
	 *
	 * Why: Users with edit permissions should be able to clear override thumbnails.
	 * Setup: Create an editor and an attachment with an override thumbnail, then send AJAX with a valid nonce.
	 * Expectation: The response is JSON success and the override thumbnail meta is removed.
	 *
	 * @group ajax
	 */
	public function test_remove_override_allows_editor() {
		wp_set_current_user( $this->editor_id );
		$this->last_status_code = null;

		$_POST = array(
			'nonce' => wp_create_nonce( 'foogallery-modal-nonce' ),
			'img_id' => $this->attachment_id,
		);

		try {
			$this->_handleAjax( 'foogallery_remove_alternate_img' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
		}

		$this->assertSame( '', get_post_meta( $this->attachment_id, '_foogallery_override_thumbnail', true ) );
	}

	/**
	 * Tests that invalid attachment IDs return an error.
	 *
	 * Why: The endpoint should reject empty or invalid IDs to avoid mutating unrelated data.
	 * Setup: Use an editor with a valid nonce but send an invalid attachment ID.
	 * Expectation: The response is JSON error with "Invalid attachment data." and status 400.
	 *
	 * @group ajax
	 */
	public function test_remove_override_rejects_invalid_id() {
		wp_set_current_user( $this->editor_id );
		$this->last_status_code = null;

		$_POST = array(
			'nonce' => wp_create_nonce( 'foogallery-modal-nonce' ),
			'img_id' => 0,
		);

		try {
			$this->_handleAjax( 'foogallery_remove_alternate_img' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Invalid attachment data.', $response['data']['message'] );
			$this->assertSame( 400, $this->last_status_code );
		}
	}
}
