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

		$previous_gallery = $current_foogallery;
		$previous_arguments = $current_foogallery_arguments;
		$previous_template = $current_foogallery_template;

		$current_foogallery = $gallery;
		$current_foogallery_template = 'default';
		$current_foogallery_arguments = array( 'lightbox' => 'custom' );

		$this->assertSame( 'custom', foogallery_gallery_template_setting( 'lightbox', 'fallback' ) );

		$current_foogallery = $previous_gallery;
		$current_foogallery_arguments = $previous_arguments;
		$current_foogallery_template = $previous_template;
	}

	public function test_get_all_galleries_returns_gallery_objects() {
		$gallery_id = $this->create_gallery_post();
		$draft_id = $this->create_gallery_post( array( 'post_status' => 'draft' ) );
		$this->factory->post->create( array( 'post_type' => 'post' ) );

		$galleries = foogallery_get_all_galleries();
		$this->assertNotEmpty( $galleries );
		$this->assertInstanceOf( FooGallery::class, $galleries[0] );

		$ids = array_map( function( $gallery ) {
			return $gallery->ID;
		}, $galleries );

		$this->assertContains( $gallery_id, $ids );
		$this->assertContains( $draft_id, $ids );
	}

	public function test_extract_gallery_shortcodes_parses_ids() {
		$content = 'Before [foogallery id="12"] middle [foogallery id="34" /] after';
		$ids = foogallery_extract_gallery_shortcodes( $content );
		$this->assertSame( array( 12 => '[foogallery id="12"]', 34 => '[foogallery id="34" /]' ), $ids );
	}

	public function test_gallery_shortcode_regex_matches_self_closing() {
		$regex = '/' . foogallery_gallery_shortcode_regex() . '/s';
		$content = '[foogallery id="99" /]';
		$this->assertSame( 1, preg_match( $regex, $content ) );
	}

	public function test_build_class_attribute_includes_template_and_custom_classes() {
		$gallery_id = $this->create_gallery_post();
		update_post_meta( $gallery_id, FOOGALLERY_META_TEMPLATE, 'default' );
		$gallery = FooGallery::get_by_id( $gallery_id );

		global $current_foogallery_arguments;
		$current_foogallery_arguments = array(
			'classname' => 'custom-class',
			'classes'   => 'extra-class',
		);

		$classes = foogallery_build_class_attribute( $gallery, 'added' );
		$this->assertStringContainsString( 'foogallery-default', $classes );
		$this->assertStringContainsString( 'custom-class', $classes );
		$this->assertStringContainsString( 'extra-class', $classes );
		$this->assertStringContainsString( 'added', $classes );
	}

	public function test_build_class_attribute_safe_escapes_html() {
		$gallery_id = $this->create_gallery_post();
		$gallery = FooGallery::get_by_id( $gallery_id );

		$classes = foogallery_build_class_attribute_safe( $gallery, 'unsafe"class' );
		$this->assertStringNotContainsString( '"', $classes );
		$this->assertStringContainsString( 'unsafe', $classes );
	}

	public function test_build_container_attributes_safe_escapes_values() {
		$gallery_id = $this->create_gallery_post();
		$gallery = FooGallery::get_by_id( $gallery_id );

		$attributes = array(
			'class' => 'test"class',
			'data'  => 'value',
		);

		$html = foogallery_build_container_attributes_safe( $gallery, $attributes );
		$this->assertStringContainsString( 'id="' . $gallery->container_id() . '"', $html );
		$this->assertStringContainsString( 'class="testclass"', $html );
		$this->assertStringContainsString( 'data="value"', $html );
	}

	public function test_sorting_options_map_to_expected_orderby() {
		$this->assertSame( 'date', foogallery_sorting_get_posts_orderby_arg( 'date_desc' ) );
		$this->assertSame( 'modified', foogallery_sorting_get_posts_orderby_arg( 'modified_asc' ) );
		$this->assertSame( 'title', foogallery_sorting_get_posts_orderby_arg( 'title_desc' ) );
		$this->assertSame( 'rand', foogallery_sorting_get_posts_orderby_arg( 'rand' ) );
		$this->assertSame( 'post__in', foogallery_sorting_get_posts_orderby_arg( 'unknown' ) );
		$this->assertSame( 'ASC', foogallery_sorting_get_posts_order_arg( 'title_asc' ) );
		$this->assertSame( 'DESC', foogallery_sorting_get_posts_order_arg( 'date_desc' ) );
	}

	public function test_thumbnail_jpeg_quality_clamps_invalid_values() {
		update_option( 'foogallery', array( 'thumb_jpeg_quality' => 0 ) );
		$this->assertSame( 80, foogallery_thumbnail_jpeg_quality() );

		update_option( 'foogallery', array( 'thumb_jpeg_quality' => 95 ) );
		$this->assertSame( 95, foogallery_thumbnail_jpeg_quality() );
	}

	public function test_caption_helpers_resolve_sources() {
		$attachment_id = $this->create_attachment( array(
			'post_title'   => 'Title',
			'post_excerpt' => 'Caption',
			'post_content' => 'Description',
		) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Alt Text' );
		$attachment = get_post( $attachment_id );

		$this->assertSame( 'Title', foogallery_get_caption_title_for_attachment( $attachment, 'title' ) );
		$this->assertSame( 'Description', foogallery_get_caption_title_for_attachment( $attachment, 'desc' ) );
		$this->assertSame( 'Alt Text', foogallery_get_caption_title_for_attachment( $attachment, 'alt' ) );
		$this->assertSame( 'Caption', foogallery_get_caption_title_for_attachment( $attachment, 'caption' ) );

		$foogallery_attachment = new FooGalleryAttachment( $attachment );
		$this->assertSame( 'Title', foogallery_get_caption_by_source( $foogallery_attachment, 'title', 'title' ) );
		$this->assertSame( 'Description', foogallery_get_caption_by_source( $foogallery_attachment, 'desc', 'title' ) );
		$this->assertSame( 'Alt Text', foogallery_get_caption_by_source( $foogallery_attachment, 'alt', 'title' ) );
		$this->assertSame( 'Caption', foogallery_get_caption_by_source( $foogallery_attachment, 'caption', 'title' ) );

		$this->assertSame( 'Title', foogallery_get_caption_desc_for_attachment( $attachment, 'title' ) );
		$this->assertSame( 'Caption', foogallery_get_caption_desc_for_attachment( $attachment, 'caption' ) );
		$this->assertSame( 'Alt Text', foogallery_get_caption_desc_for_attachment( $attachment, 'alt' ) );
		$this->assertSame( 'Description', foogallery_get_caption_desc_for_attachment( $attachment, 'desc' ) );
	}

	public function test_gallery_datasources_include_default() {
		$datasources = foogallery_gallery_datasources();
		$this->assertArrayHasKey( 'media_library', $datasources );
		$this->assertSame( 'media_library', $datasources['media_library']['id'] );
		$this->assertArrayHasKey( 'label', $datasources['media_library'] );
	}

	public function test_image_placeholder_html_escapes_attributes() {
		$html = foogallery_image_placeholder_html( array(
			'width'  => '150"',
			'height' => '150',
			'alt'    => 'Sample',
		) );

		$this->assertStringContainsString( 'width="150"', $html );
		$this->assertStringContainsString( 'height="150"', $html );
		$this->assertStringContainsString( 'alt="Sample"', $html );
		$this->assertStringNotContainsString( '150""', $html );
	}

	public function test_featured_attachment_thumbnail_src_falls_back_to_placeholder() {
		$gallery_id = $this->create_gallery_post();
		$gallery = FooGallery::get_by_id( $gallery_id );

		$src = foogallery_find_featured_attachment_thumbnail_src( $gallery, array(
			'width'  => 120,
			'height' => 120,
		) );

		$this->assertSame( foogallery_image_placeholder_src(), $src );
	}

	public function test_get_attachment_id_by_url_returns_match() {
		$attachment_id = $this->create_attachment( array(
			'guid' => 'https://example.org/uploads/attachment.jpg',
		) );

		$this->assertSame( $attachment_id, foogallery_get_attachment_id_by_url( 'https://example.org/uploads/attachment.jpg' ) );
		$this->assertNull( foogallery_get_attachment_id_by_url( 'https://example.org/uploads/missing.jpg' ) );
	}

	public function test_create_gallery_sets_template_and_attachments() {
		$attachment_id = $this->create_attachment();
		$gallery_id = foogallery_create_gallery( 'masonry', (string) $attachment_id );

		$this->assertSame( FOOGALLERY_CPT_GALLERY, get_post_type( $gallery_id ) );
		$this->assertSame( 'masonry', get_post_meta( $gallery_id, FOOGALLERY_META_TEMPLATE, true ) );
		$this->assertSame( array( $attachment_id ), get_post_meta( $gallery_id, FOOGALLERY_META_ATTACHMENTS, true ) );
		$this->assertNotEmpty( get_post_meta( $gallery_id, FOOGALLERY_META_SETTINGS, true ) );
	}
}
