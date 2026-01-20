<?php

class FooGalleryPreviewAjaxTest extends WP_Ajax_UnitTestCase {
	private $admin_id;
	private $subscriber_id;
	private $gallery_id;

	public function setUp(): void {
		parent::setUp();

		require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-items.php';
		new FooGallery_Admin_Gallery_MetaBox_Items();

		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->gallery_id = self::factory()->post->create( array(
			'post_type' => FOOGALLERY_CPT_GALLERY,
			'post_status' => 'publish',
		) );

		add_filter( 'foogallery_preview_template', array( $this, 'force_preview_template' ), 10, 2 );
		add_filter( 'foogallery_gallery_templates', array( $this, 'register_preview_template' ) );
	}

	public function tearDown(): void {
		remove_filter( 'foogallery_preview_template', array( $this, 'force_preview_template' ), 10 );
		remove_filter( 'foogallery_gallery_templates', array( $this, 'register_preview_template' ) );
		parent::tearDown();
	}

	public function force_preview_template( $template ) {
		return $template;
	}

	public function register_preview_template( $templates ) {
		$templates[] = array(
			'slug' => 'default',
			'preview_support' => true,
		);

		return $templates;
	}

	/**
	 * Tests that subscribers cannot access gallery previews.
	 *
	 * Why: Preview responses include gallery output and should require edit permissions.
	 * Setup: Create a subscriber and a gallery, then request a preview with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message.
	 *
	 * @group ajax
	 */
	public function test_preview_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['foogallery_preview_nonce'] = wp_create_nonce( 'foogallery_preview' );
		$_POST['foogallery_id'] = $this->gallery_id;
		$_POST['foogallery_template'] = foogallery_default_gallery_template();
		$_POST['foogallery_attachments'] = array();

		try {
			$this->_handleAjax( 'foogallery_preview' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests that admins can access gallery previews.
	 *
	 * Why: Editors and admins should be able to render previews in the editor.
	 * Setup: Create an admin and a gallery, then request a preview with a valid nonce.
	 * Expectation: The response contains the preview markup and no JSON error payload.
	 *
	 * @group ajax
	 */
	public function test_preview_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['foogallery_preview_nonce'] = wp_create_nonce( 'foogallery_preview' );
		$_POST['foogallery_id'] = $this->gallery_id;
		$_POST['foogallery_template'] = foogallery_default_gallery_template();
		$_POST['foogallery_attachments'] = array();

		try {
			$this->_handleAjax( 'foogallery_preview' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$this->assertStringContainsString( 'foogallery', $this->_last_response );
		}
	}

	/**
	 * Tests invalid gallery IDs return an error.
	 *
	 * Why: The endpoint must reject invalid IDs instead of rendering arbitrary content.
	 * Setup: Request a preview with an invalid gallery ID and a valid nonce.
	 * Expectation: The response is JSON error with an invalid gallery ID message.
	 *
	 * @group ajax
	 */
	public function test_preview_invalid_gallery_id_returns_error() {
		wp_set_current_user( $this->admin_id );

		$_POST['foogallery_preview_nonce'] = wp_create_nonce( 'foogallery_preview' );
		$_POST['foogallery_id'] = 0;
		$_POST['foogallery_template'] = foogallery_default_gallery_template();
		$_POST['foogallery_attachments'] = array();

		try {
			$this->_handleAjax( 'foogallery_preview' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Invalid gallery ID.', $response['data']['message'] );
		}
	}
}
