<?php

class FooGalleryGalleryMetaboxesAjaxTest extends WP_Ajax_UnitTestCase {
	private $admin_id;
	private $subscriber_id;
	private $gallery_id;
	private $attachment_id;

	public function setUp(): void {
		parent::setUp();

		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->gallery_id = self::factory()->post->create( array(
			'post_type' => FOOGALLERY_CPT_GALLERY,
			'post_status' => 'publish',
		) );

		$this->attachment_id = self::factory()->attachment->create( array(
			'post_mime_type' => 'image/jpeg',
			'post_title' => 'Cache Attachment',
			'guid' => 'https://example.org/cache-attachment.jpg',
		) );

		update_post_meta( $this->gallery_id, FOOGALLERY_META_ATTACHMENTS, array( $this->attachment_id ) );

		require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php';

		global $foogallery_admin_metaboxes_instantiated;
		$foogallery_admin_metaboxes_instantiated = false;

		new FooGallery_Admin_Gallery_MetaBoxes();
	}

	/**
	 * Tests subscribers cannot create gallery pages.
	 *
	 * Why: Page creation should only be allowed for users who can edit the gallery.
	 * Setup: Create a subscriber and a gallery, then request page creation with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message and a 403 code.
	 *
	 * @group ajax
	 */
	public function test_create_gallery_page_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['foogallery_create_gallery_page_nonce'] = wp_create_nonce( 'foogallery_create_gallery_page' );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_create_gallery_page' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
			$this->assertSame( 403, $this->_last_response_code );
		}
	}

	/**
	 * Tests admins can create gallery pages.
	 *
	 * Why: Authorized users need to generate draft pages for gallery usage.
	 * Setup: Create an admin and a gallery, then request page creation with a valid nonce.
	 * Expectation: The response is JSON success with a draft page ID.
	 *
	 * @group ajax
	 */
	public function test_create_gallery_page_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['foogallery_create_gallery_page_nonce'] = wp_create_nonce( 'foogallery_create_gallery_page' );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_create_gallery_page' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertNotEmpty( $response['data']['page_id'] );

			$page = get_post( $response['data']['page_id'] );
			$this->assertSame( 'page', $page->post_type );
			$this->assertSame( 'draft', $page->post_status );
		}
	}

	/**
	 * Tests subscribers cannot clear gallery thumbnail cache.
	 *
	 * Why: Clearing cache should be restricted to users with edit access on the gallery.
	 * Setup: Create a subscriber and a gallery, then request cache clearing with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message and a 403 code.
	 *
	 * @group ajax
	 */
	public function test_clear_gallery_thumb_cache_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['foogallery_clear_gallery_thumb_cache_nonce'] = wp_create_nonce( 'foogallery_clear_gallery_thumb_cache' );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_clear_gallery_thumb_cache' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
			$this->assertSame( 403, $this->_last_response_code );
		}
	}

	/**
	 * Tests admins can clear gallery thumbnail cache.
	 *
	 * Why: Editors and admins need to invalidate cached thumbnails for galleries.
	 * Setup: Create an admin and a gallery with attachments, then request cache clearing with a valid nonce.
	 * Expectation: The response is JSON success with a cache cleared message.
	 *
	 * @group ajax
	 */
	public function test_clear_gallery_thumb_cache_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['foogallery_clear_gallery_thumb_cache_nonce'] = wp_create_nonce( 'foogallery_clear_gallery_thumb_cache' );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_clear_gallery_thumb_cache' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertSame( 'The thumbnail cache has been cleared!', $response['data']['message'] );
		}
	}

	/**
	 * Tests subscribers cannot clear attachment thumbnail cache.
	 *
	 * Why: Clearing an attachment cache should only be allowed for users with edit access.
	 * Setup: Create a subscriber and an attachment, then request cache clearing with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message and a 403 code.
	 *
	 * @group ajax
	 */
	public function test_clear_attachment_thumb_cache_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['foogallery_clear_attachment_thumb_cache_nonce'] = wp_create_nonce( 'foogallery_clear_attachment_thumb_cache' );
		$_POST['attachment_id'] = $this->attachment_id;

		try {
			$this->_handleAjax( 'foogallery_clear_attachment_thumb_cache' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
			$this->assertSame( 403, $this->_last_response_code );
		}
	}

	/**
	 * Tests admins can clear attachment thumbnail cache.
	 *
	 * Why: Authorized users need to invalidate cached thumbnails for attachments.
	 * Setup: Create an admin and an attachment, then request cache clearing with a valid nonce.
	 * Expectation: The response is JSON success with a cache cleared message.
	 *
	 * @group ajax
	 */
	public function test_clear_attachment_thumb_cache_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['foogallery_clear_attachment_thumb_cache_nonce'] = wp_create_nonce( 'foogallery_clear_attachment_thumb_cache' );
		$_POST['attachment_id'] = $this->attachment_id;

		try {
			$this->_handleAjax( 'foogallery_clear_attachment_thumb_cache' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertSame( 'The thumbnail cache has been cleared!', $response['data']['message'] );
		}
	}
}
