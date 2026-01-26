<?php

class FooGalleryRestGalleriesTest extends WP_UnitTestCase {
	private function create_gallery_post( array $args = array() ) {
		return self::factory()->post->create( array_merge( array(
			'post_title'  => 'Test Gallery',
			'post_type'   => FOOGALLERY_CPT_GALLERY,
			'post_status' => 'publish',
		), $args ) );
	}

	private function add_gallery_caps_to_user( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( $user ) {
			$user->add_cap( 'edit_foogallery' );
			$user->add_cap( 'edit_foogalleries' );
		}
	}

	/**
	 * Tests that the galleries REST route blocks users without edit_posts.
	 *
	 * Why: The endpoint exposes gallery metadata and should be restricted to trusted editors.
	 * Setup: Create a subscriber account and issue a GET request while authenticated as that user.
	 * Expectation: The response is a 403 authorization error and does not return gallery data.
	 */
	public function test_galleries_route_requires_edit_posts() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		wp_set_current_user( $subscriber_id );

		$request = new WP_REST_Request( 'GET', '/foogallery/v1/galleries' );
		$response = rest_do_request( $request );

		$this->assertSame( 403, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( 'foogallery_galleries_cannot_read', $data['code'] );
	}

	/**
	 * Tests that the galleries REST route only returns galleries the user can edit.
	 *
	 * Why: Authors should not see galleries they do not have permission to edit.
	 * Setup: Create two authors, grant edit_foogallery, and create one gallery for each author.
	 * Expectation: The response includes only the current user's gallery and excludes the other.
	 */
	public function test_galleries_route_filters_uneditable_galleries() {
		$author_id = self::factory()->user->create( array( 'role' => 'author' ) );
		$other_author_id = self::factory()->user->create( array( 'role' => 'author' ) );

		$this->add_gallery_caps_to_user( $author_id );
		$this->add_gallery_caps_to_user( $other_author_id );

		$gallery_id = $this->create_gallery_post( array( 'post_author' => $author_id ) );
		$other_gallery_id = $this->create_gallery_post( array( 'post_author' => $other_author_id ) );

		wp_set_current_user( $author_id );

		$request = new WP_REST_Request( 'GET', '/foogallery/v1/galleries' );
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$ids = array_map( 'absint', wp_list_pluck( $data, 'id' ) );

		$this->assertContains( $gallery_id, $ids );
		$this->assertNotContains( $other_gallery_id, $ids );
	}

	/**
	 * Tests that the galleries REST route stays scoped to the current site on multisite.
	 *
	 * Why: Multisite installations must not leak galleries between sites.
	 * Setup: Create a second site, add a gallery on each site, and request data from the primary site.
	 * Expectation: The response contains the primary site gallery and omits the second site gallery.
	 */
	public function test_galleries_route_limits_to_current_site_in_multisite() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'This test requires multisite.' );
		}

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		grant_super_admin( $admin_id );

		wp_set_current_user( $admin_id );

		$primary_gallery_id = $this->create_gallery_post( array( 'post_author' => $admin_id ) );

		$site_id = self::factory()->blog->create();
		switch_to_blog( $site_id );

		$secondary_gallery_id = $this->create_gallery_post( array( 'post_author' => $admin_id ) );

		restore_current_blog();

		$request = new WP_REST_Request( 'GET', '/foogallery/v1/galleries' );
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$ids = array_map( 'absint', wp_list_pluck( $data, 'id' ) );

		$this->assertContains( $primary_gallery_id, $ids );
		$this->assertNotContains( $secondary_gallery_id, $ids );
	}
}
