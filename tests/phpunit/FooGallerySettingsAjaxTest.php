<?php

class FooGallerySettingsAjaxTest extends WP_Ajax_UnitTestCase {
	private $admin_id;
	private $subscriber_id;
	private $gallery_id;

	public function setUp(): void {
		parent::setUp();

		require_once FOOGALLERY_PATH . 'includes/admin/class-settings.php';
		new FooGallery_Admin_Settings();

		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->gallery_id = self::factory()->post->create( array(
			'post_type'   => FOOGALLERY_CPT_GALLERY,
			'post_status' => 'publish',
		) );
	}

	public function tearDown(): void {
		$_POST = array();
		parent::tearDown();
	}

	/**
	 * Tests that subscribers cannot clear CSS optimizations via AJAX.
	 *
	 * Why: Clearing CSS caches affects global output and must be admin-only.
	 * Setup: Create a subscriber and send the request with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message.
	 *
	 * @group ajax
	 */
	public function test_clear_css_optimizations_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'foogallery_clear_css_optimizations' );

		try {
			$this->_handleAjax( 'foogallery_clear_css_optimizations' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests that admins can clear CSS optimizations via AJAX.
	 *
	 * Why: Administrators need to purge cached styles when troubleshooting.
	 * Setup: Create an admin and send the request with a valid nonce.
	 * Expectation: The response is JSON success with a confirmation message payload.
	 *
	 * @group ajax
	 */
	public function test_clear_css_optimizations_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'foogallery_clear_css_optimizations' );

		try {
			$this->_handleAjax( 'foogallery_clear_css_optimizations' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertArrayHasKey( 'html', $response['data'] );
			$this->assertNotEmpty( $response['data']['html'] );
		}
	}

	/**
	 * Tests that subscribers cannot run the thumbnail generation test via AJAX.
	 *
	 * Why: Running diagnostics can expose system behavior and should be admin-only.
	 * Setup: Create a subscriber and send the request with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message.
	 *
	 * @group ajax
	 */
	public function test_thumb_generation_test_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'foogallery_thumb_generation_test' );

		try {
			$this->_handleAjax( 'foogallery_thumb_generation_test' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests that admins can run the thumbnail generation test via AJAX.
	 *
	 * Why: Admins need to validate thumbnail generation for troubleshooting.
	 * Setup: Create an admin and send the request with a valid nonce.
	 * Expectation: The response is JSON success with an HTML payload.
	 *
	 * @group ajax
	 */
	public function test_thumb_generation_test_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'foogallery_thumb_generation_test' );

		try {
			$this->_handleAjax( 'foogallery_thumb_generation_test' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertArrayHasKey( 'html', $response['data'] );
			$this->assertNotEmpty( $response['data']['html'] );
		}
	}

	/**
	 * Tests that subscribers cannot apply retina defaults via AJAX.
	 *
	 * Why: Updating default retina settings writes post meta and should be admin-only.
	 * Setup: Create a subscriber and send the request with a valid nonce.
	 * Expectation: The response is JSON error with an insufficient permissions message.
	 *
	 * @group ajax
	 */
	public function test_apply_retina_defaults_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'foogallery_apply_retina_defaults' );

		try {
			$this->_handleAjax( 'foogallery_apply_retina_defaults' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests that admins can apply retina defaults via AJAX.
	 *
	 * Why: Admins should be able to update gallery defaults across the site.
	 * Setup: Create an admin and send defaults with a valid nonce.
	 * Expectation: The response is JSON success with a confirmation message.
	 *
	 * @group ajax
	 */
	public function test_apply_retina_defaults_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'foogallery_apply_retina_defaults' );
		$_POST['defaults'] = 'foogallery[default_retina_support|test]';

		try {
			$this->_handleAjax( 'foogallery_apply_retina_defaults' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( true, $response['success'] );
			$this->assertArrayHasKey( 'html', $response['data'] );
			$this->assertNotEmpty( $response['data']['html'] );
		}
	}
}
