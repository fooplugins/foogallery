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
		$this->assertSame( 'simple-template', $templates[0]['slug'] );

		$template = foogallery_get_gallery_template( 'simple-template' );
		$this->assertIsArray( $template );
		$this->assertSame( 'simple-template', $template['slug'] );
		remove_filter( 'foogallery_gallery_templates', $template_filter );
	}

	public function test_get_setting_returns_defaults_and_filters() {
		$this->assertSame( 'default', foogallery_default_gallery_template() );
		$this->assertSame( 'default', foogallery_get_setting( 'gallery_template' ) );
		$this->assertSame( 'fallback', foogallery_get_setting( 'unknown_setting', 'fallback' ) );

		$filter = function( $value ) {
			return 'filtered';
		};

		add_filter( 'foogallery_get_setting-gallery_template', $filter );
		$this->assertSame( 'filtered', foogallery_get_setting( 'gallery_template' ) );
		remove_filter( 'foogallery_get_setting-gallery_template', $filter );
	}

	public function test_default_options_have_expected_keys() {
		$defaults = foogallery_get_default_options();
		$this->assertArrayHasKey( 'gallery_template', $defaults );
		$this->assertArrayHasKey( 'gallery_permalinks_enabled', $defaults );
		$this->assertArrayHasKey( 'lightbox', $defaults );
		$this->assertSame( 'default', $defaults['gallery_template'] );
	}

	public function test_gallery_template_setting_prefers_shortcode_args() {
		$gallery_id = $this->create_gallery_post();
		$gallery = FooGallery::get_by_id( $gallery_id );

		global $current_foogallery;
		global $current_foogallery_arguments;
		global $current_foogallery_template;

		$current_foogallery = $gallery;
		$current_foogallery_template = 'default';
		$current_foogallery_arguments = array( 'lightbox' => 'custom' );

		$this->assertSame( 'custom', foogallery_gallery_template_setting( 'lightbox', 'fallback' ) );
	}

	public function test_get_all_galleries_returns_gallery_objects() {
		$gallery_id = $this->create_gallery_post();
		$this->create_gallery_post( array( 'post_status' => 'draft' ) );
		$this->factory->post->create( array( 'post_type' => 'post' ) );

		$galleries = foogallery_get_all_galleries();
		$this->assertNotEmpty( $galleries );
		$this->assertInstanceOf( FooGallery::class, $galleries[0] );
		$this->assertSame( $gallery_id, $galleries[0]->ID );
	}
}
