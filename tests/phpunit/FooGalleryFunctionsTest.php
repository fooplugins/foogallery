<?php

class FooGalleryFunctionsTest extends WP_UnitTestCase {
	private function create_gallery_post( array $args = array(), array $meta = array() ) {
		$post_id = $this->factory->post->create( array_merge( array(
			'post_title'  => 'Test Gallery',
			'post_type'   => FOOGALLERY_CPT_GALLERY,
			'post_status' => 'publish',
		), $args ) );

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return $post_id;
	}

	private function create_attachment( array $args = array() ) {
		$defaults = array(
			'post_title'     => 'Test Image',
			'post_mime_type' => 'image/jpeg',
			'guid'           => 'https://example.org/test-image.jpg',
		);

		return $this->factory->attachment->create( array_merge( $defaults, $args ) );
	}

	public function test_foogallery_plugin_name_is_filterable() {
		$this->assertSame( 'FooGallery', foogallery_plugin_name() );

		$filter = function() {
			return 'FooGallery Pro';
		};

		add_filter( 'foogallery_plugin_name', $filter );
		$this->assertSame( 'FooGallery Pro', foogallery_plugin_name() );
		remove_filter( 'foogallery_plugin_name', $filter );
	}
}
