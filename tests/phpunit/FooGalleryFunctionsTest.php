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

	public function test_gallery_template_helpers_handle_missing_and_valid_templates() {
		$this->assertFalse( foogallery_get_gallery_template( 'missing' ) );

		$template_filter = function( $templates ) {
			$templates[] = array(
				'slug'   => 'simple-template',
				'title'  => 'Simple Template',
				'fields' => array(),
			);
			return $templates;
		};

		add_filter( 'foogallery_gallery_templates', $template_filter );
		$templates = foogallery_gallery_templates();
		$this->assertCount( 1, $templates );
		$this->assertSame( 'simple-template', foogallery_get_gallery_template( 'simple-template' )['slug'] );
		remove_filter( 'foogallery_gallery_templates', $template_filter );
	}
}
