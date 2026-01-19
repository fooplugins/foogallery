<?php

class FooGalleryAttachmentModalAjaxTest extends WP_Ajax_UnitTestCase {
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
			'post_title' => 'Modal Attachment',
			'guid' => 'https://example.org/modal-attachment.jpg',
		) );

		register_taxonomy( 'foogallery_test_tax', 'attachment' );

		update_post_meta( $this->gallery_id, FOOGALLERY_META_ATTACHMENTS, array( $this->attachment_id ) );

		require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-attachment-modal.php';
		new FooGallery_Admin_Gallery_Attachment_Modal();
	}

	/**
	 * Tests subscriber cannot open attachment modal.
	 *
	 * @group ajax
	 */
	public function test_open_modal_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery_attachment_modal_open' );
		$_POST['img_id'] = $this->attachment_id;
		$_POST['gallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_open' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests admin can open attachment modal.
	 *
	 * @group ajax
	 */
	public function test_open_modal_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery_attachment_modal_open' );
		$_POST['img_id'] = $this->attachment_id;
		$_POST['gallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_open' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertArrayHasKey( 'html', $response );
		}
	}

	/**
	 * Tests missing attachment ID returns error.
	 *
	 * @group ajax
	 */
	public function test_open_modal_invalid_img_id_returns_error() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery_attachment_modal_open' );
		$_POST['img_id'] = 0;
		$_POST['gallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_open' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Invalid attachment data.', $response['data']['message'] );
		}
	}

	/**
	 * Tests subscriber cannot save attachment modal data.
	 *
	 * @group ajax
	 */
	public function test_save_modal_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-modal-nonce' );
		$_POST['img_id'] = $this->attachment_id;
		$_POST['foogallery'] = array( 'title' => 'New Title' );

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_save' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests admin can save attachment modal data.
	 *
	 * @group ajax
	 */
	public function test_save_modal_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-modal-nonce' );
		$_POST['img_id'] = $this->attachment_id;
		$_POST['foogallery'] = array( 'title' => 'Updated Title' );

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_save' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
		}
	}

	/**
	 * Tests invalid attachment ID returns error on save.
	 *
	 * @group ajax
	 */
	public function test_save_modal_invalid_img_id_returns_error() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-modal-nonce' );
		$_POST['img_id'] = 0;
		$_POST['foogallery'] = array( 'title' => 'Updated Title' );

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_save' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Invalid attachment data.', $response['data']['message'] );
		}
	}

	/**
	 * Tests subscriber cannot add taxonomy term.
	 *
	 * @group ajax
	 */
	public function test_add_taxonomy_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery_attachment_modal_taxonomies' );
		$_POST['img_id'] = $this->attachment_id;
		$_POST['taxonomy'] = 'foogallery_test_tax';
		$_POST['term'] = 'New Term';

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_taxonomy_add' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests admin can add taxonomy term.
	 *
	 * @group ajax
	 */
	public function test_add_taxonomy_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery_attachment_modal_taxonomies' );
		$_POST['img_id'] = $this->attachment_id;
		$_POST['taxonomy'] = 'foogallery_test_tax';
		$_POST['term'] = 'New Term';

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_taxonomy_add' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertNotEmpty( $response['data']['id'] );
			$this->assertSame( 'New Term', $response['data']['name'] );
		}
	}

	/**
	 * Tests invalid attachment ID returns error on taxonomy add.
	 *
	 * @group ajax
	 */
	public function test_add_taxonomy_invalid_img_id_returns_error() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery_attachment_modal_taxonomies' );
		$_POST['img_id'] = 0;
		$_POST['taxonomy'] = 'foogallery_test_tax';
		$_POST['term'] = 'New Term';

		try {
			$this->_handleAjax( 'foogallery_attachment_modal_taxonomy_add' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Invalid data.', $response['data']['message'] );
		}
	}
}
