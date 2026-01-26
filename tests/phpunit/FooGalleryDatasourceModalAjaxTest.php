<?php

class FooGalleryDatasourceModalAjaxTest extends WP_Ajax_UnitTestCase {
	private $admin_id;
	private $subscriber_id;
	private $gallery_id;

	public function setUp(): void {
		parent::setUp();

		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->gallery_id = self::factory()->post->create( array(
			'post_type' => FOOGALLERY_CPT_GALLERY,
			'post_status' => 'publish',
		) );

		add_filter( 'foogallery_gallery_datasources', array( $this, 'register_test_datasource' ) );
		add_action( 'foogallery-datasource-modal-content_test_source', array( $this, 'render_test_datasource_content' ), 10, 2 );

		require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-datasources.php';
		new FooGallery_Admin_Gallery_Datasources();
	}

	public function tearDown(): void {
		remove_filter( 'foogallery_gallery_datasources', array( $this, 'register_test_datasource' ) );
		remove_action( 'foogallery-datasource-modal-content_test_source', array( $this, 'render_test_datasource_content' ), 10 );

		parent::tearDown();
	}

	public function register_test_datasource( $datasources ) {
		$datasources['test_source'] = array(
			'id'     => 'test_source',
			'name'   => __( 'Test Source', 'foogallery' ),
			'menu'   => __( 'Test Source', 'foogallery' ),
			'public' => true,
		);

		return $datasources;
	}

	public function render_test_datasource_content( $foogallery_id, $datasource_value ) {
		echo '<div class="foogallery-test-datasource">Test datasource content</div>';
	}

	/**
	 * Tests that subscribers cannot load datasource modal content.
	 *
	 * Why: Datasource modal content can expose configuration data and must be protected.
	 * Setup: Use a subscriber with a valid nonce, datasource key, and gallery ID.
	 * Expectation: The response is a JSON error with an insufficient permissions message.
	 *
	 * @group ajax
	 */
	public function test_datasource_modal_denies_subscriber() {
		wp_set_current_user( $this->subscriber_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-datasource-content' );
		$_POST['datasource'] = 'test_source';
		$_POST['datasource_value'] = wp_json_encode( array( 'foo' => 'bar' ) );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_load_datasource_content' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Insufficient permissions.', $response['data']['message'] );
		}
	}

	/**
	 * Tests that admins can load datasource modal content.
	 *
	 * Why: Authorized users should still receive the datasource HTML after validation.
	 * Setup: Use an admin with a valid nonce, datasource key, and gallery ID.
	 * Expectation: The response contains the rendered datasource HTML fragment.
	 *
	 * @group ajax
	 */
	public function test_datasource_modal_allows_admin() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-datasource-content' );
		$_POST['datasource'] = 'test_source';
		$_POST['datasource_value'] = wp_json_encode( array( 'foo' => 'bar' ) );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_load_datasource_content' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$this->assertStringContainsString( 'foogallery-test-datasource', $this->_last_response );
		}
	}

	/**
	 * Tests that unknown datasource keys are rejected.
	 *
	 * Why: Only registered datasources should be allowed to render modal content.
	 * Setup: Use an admin with a valid nonce but an invalid datasource key.
	 * Expectation: The response is a JSON error stating the datasource is invalid.
	 *
	 * @group ajax
	 */
	public function test_datasource_modal_invalid_datasource_returns_error() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-datasource-content' );
		$_POST['datasource'] = 'invalid_source';
		$_POST['datasource_value'] = wp_json_encode( array( 'foo' => 'bar' ) );
		$_POST['foogallery_id'] = $this->gallery_id;

		try {
			$this->_handleAjax( 'foogallery_load_datasource_content' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Invalid datasource.', $response['data']['message'] );
		}
	}

	/**
	 * Tests that requests for missing galleries return not found.
	 *
	 * Why: The endpoint must not expose modal content for non-existent galleries.
	 * Setup: Use an admin with a valid nonce and datasource key but an invalid gallery ID.
	 * Expectation: The response is a JSON error stating the gallery was not found.
	 *
	 * @group ajax
	 */
	public function test_datasource_modal_missing_gallery_returns_error() {
		wp_set_current_user( $this->admin_id );

		$_POST['nonce'] = wp_create_nonce( 'foogallery-datasource-content' );
		$_POST['datasource'] = 'test_source';
		$_POST['datasource_value'] = wp_json_encode( array( 'foo' => 'bar' ) );
		$_POST['foogallery_id'] = 999999;

		try {
			$this->_handleAjax( 'foogallery_load_datasource_content' );
			$this->fail( 'Expected ajax die.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertSame( false, $response['success'] );
			$this->assertSame( 'Gallery not found.', $response['data']['message'] );
		}
	}
}
