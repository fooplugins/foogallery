# TEST-functions

## Overview
Unit test coverage for `includes/functions.php`, implemented in `tests/phpunit/FooGalleryFunctionsTest.php`.

## Test File
- `tests/phpunit/FooGalleryFunctionsTest.php`

## Test Cases

### `test_foogallery_plugin_name_is_filterable`
- **Purpose**: Ensure plugin name default and filter behavior.
- **Setup**: None.
- **Steps**:
  - Call `foogallery_plugin_name()` with no filters.
  - Add `foogallery_plugin_name` filter returning custom value.
- **Assertions**:
  - Default is `FooGallery`.
  - Filtered value returns custom string.

### `test_gallery_template_helpers_handle_missing_and_valid_templates`
- **Purpose**: Verify template retrieval behavior.
- **Setup**: Add a `foogallery_gallery_templates` filter with a single template.
- **Steps**:
  - Call `foogallery_get_gallery_template('missing')`.
  - Call `foogallery_gallery_templates()`.
  - Call `foogallery_get_gallery_template('simple-template')`.
- **Assertions**:
  - Missing slug returns `false`.
  - Templates list contains the injected template.
  - Template array includes the requested slug.

### `test_get_setting_returns_defaults_and_filters`
- **Purpose**: Ensure settings read defaults and are filterable.
- **Setup**: Add `foogallery_get_setting-gallery_template` filter.
- **Steps**:
  - Call `foogallery_get_setting('gallery_template')`.
  - Call `foogallery_get_setting('unknown_setting', 'fallback')`.
  - Apply filter and re-call.
- **Assertions**:
  - Default template is `default`.
  - Unknown setting returns fallback.
  - Filtered value returned.

### `test_default_options_have_expected_keys`
- **Purpose**: Verify default options contain required keys.
- **Setup**: None.
- **Steps**:
  - Call `foogallery_get_default_options()`.
- **Assertions**:
  - Keys `gallery_template`, `gallery_permalinks_enabled`, `lightbox` exist.
  - Default template is `default`.

### `test_default_options_are_filterable`
- **Purpose**: Ensure defaults can be overridden via filter.
- **Setup**: Add `foogallery_defaults` filter to change `gallery_template`.
- **Steps**:
  - Call `foogallery_get_default_options()` with filter applied.
- **Assertions**:
  - `gallery_template` equals filtered value.

### `test_gallery_template_setting_prefers_shortcode_args`
- **Purpose**: Ensure shortcode args override stored template settings.
- **Setup**:
  - Create a gallery.
  - Set globals `current_foogallery`, `current_foogallery_template`, `current_foogallery_arguments`.
- **Steps**:
  - Call `foogallery_gallery_template_setting('lightbox', 'fallback')`.
- **Assertions**:
  - Returned value equals `current_foogallery_arguments['lightbox']`.
- **Cleanup**:
  - Restore previous global values.

### `test_get_all_galleries_returns_gallery_objects`
- **Purpose**: Confirm gallery retrieval returns `FooGallery` objects for publish/draft.
- **Setup**:
  - Create a published and draft gallery.
  - Create a non-gallery post.
- **Steps**:
  - Call `foogallery_get_all_galleries()`.
- **Assertions**:
  - Result is non-empty.
  - Items are `FooGallery` objects.
  - IDs include both published and draft galleries.

### `test_extract_gallery_shortcodes_parses_ids`
- **Purpose**: Validate shortcode parsing into ID map.
- **Setup**: Provide content string with two shortcodes.
- **Steps**:
  - Call `foogallery_extract_gallery_shortcodes($content)`.
- **Assertions**:
  - Returns array keyed by gallery IDs with full shortcode text.

### `test_gallery_shortcode_regex_matches_self_closing`
- **Purpose**: Ensure shortcode regex matches self-closing usage.
- **Setup**: None.
- **Steps**:
  - Run regex from `foogallery_gallery_shortcode_regex()` against `[foogallery id="99" /]`.
- **Assertions**:
  - `preg_match` returns `1`.

### `test_gallery_shortcode_regex_matches_wrapped_content`
- **Purpose**: Ensure regex matches non-self-closing shortcode.
- **Setup**: None.
- **Steps**:
  - Run regex against `[foogallery id="77"]Content[/foogallery]`.
- **Assertions**:
  - `preg_match` returns `1`.

### `test_gallery_shortcode_tag_is_filterable`
- **Purpose**: Verify shortcode tag filter overrides tag.
- **Setup**: Add `foogallery_gallery_shortcode_tag` filter.
- **Steps**:
  - Call `foogallery_gallery_shortcode_tag()`.
- **Assertions**:
  - Returns filtered tag name.

### `test_build_class_attribute_includes_template_and_custom_classes`
- **Purpose**: Ensure class attribute includes template and extra classes.
- **Setup**:
  - Create gallery with template meta.
  - Set `current_foogallery_arguments` to include `classname` and `classes`.
- **Steps**:
  - Call `foogallery_build_class_attribute($gallery, 'added')`.
- **Assertions**:
  - Output contains `foogallery-default`, `custom-class`, `extra-class`, and `added`.
- **Cleanup**:
  - Restore previous `current_foogallery_arguments`.

### `test_build_class_attribute_safe_escapes_html`
- **Purpose**: Ensure HTML is escaped in class attribute.
- **Setup**: Create gallery.
- **Steps**:
  - Call `foogallery_build_class_attribute_safe($gallery, 'unsafe"class')`.
- **Assertions**:
  - Output contains `unsafe` and does not contain raw quotes.

### `test_build_container_attributes_safe_escapes_values`
- **Purpose**: Verify attribute rendering escapes values and includes container id.
- **Setup**: Create gallery.
- **Steps**:
  - Call `foogallery_build_container_attributes_safe()` with attributes containing quotes.
- **Assertions**:
  - Output contains `id` attribute from `container_id()`.
  - `class` value is escaped (`&quot;`).
  - `data` attribute is present.

### `test_sorting_options_map_to_expected_orderby`
- **Purpose**: Validate sorting option mappings.
- **Setup**: None.
- **Steps**:
  - Call `foogallery_sorting_get_posts_orderby_arg()` and `_order_arg()` with each option.
- **Assertions**:
  - Correct `orderby` and `order` values for each option.

### `test_thumbnail_jpeg_quality_clamps_invalid_values`
- **Purpose**: Ensure invalid quality values fallback.
- **Setup**: Update options with `thumb_jpeg_quality`.
- **Steps**:
  - Set `0` and call `foogallery_thumbnail_jpeg_quality()`.
  - Set `95` and call again.
- **Assertions**:
  - `0` returns `80` fallback.
  - Valid value returns as-is.

### `test_caption_helpers_resolve_sources`
- **Purpose**: Validate caption source selection.
- **Setup**:
  - Create attachment with title, caption, description, and alt meta.
  - Instantiate `FooGalleryAttachment`.
- **Steps**:
  - Call `foogallery_get_caption_title_for_attachment()` with each source.
  - Call `foogallery_get_caption_by_source()` with each source.
  - Call `foogallery_get_caption_desc_for_attachment()` with each source.
- **Assertions**:
  - Correct value returned for title/desc/alt/caption sources.

### `test_gallery_datasources_include_default`
- **Purpose**: Ensure default datasource is present.
- **Setup**: None.
- **Steps**:
  - Call `foogallery_gallery_datasources()`.
- **Assertions**:
  - `media_library` key exists with expected `id` and `label`.

### `test_image_placeholder_html_escapes_attributes`
- **Purpose**: Confirm placeholder HTML escapes attributes.
- **Setup**: None.
- **Steps**:
  - Call `foogallery_image_placeholder_html()` with unsafe width value.
- **Assertions**:
  - Width and height attributes are escaped.
  - No double-quote injection present.

### `test_featured_attachment_thumbnail_src_falls_back_to_placeholder`
- **Purpose**: Ensure placeholder used when no featured attachment.
- **Setup**: Create gallery with no featured attachment.
- **Steps**:
  - Call `foogallery_find_featured_attachment_thumbnail_src()`.
- **Assertions**:
  - Returns `foogallery_image_placeholder_src()`.

### `test_get_attachment_id_by_url_returns_match`
- **Purpose**: Validate attachment lookup by URL.
- **Setup**: Create attachment with known GUID.
- **Steps**:
  - Call `foogallery_get_attachment_id_by_url()` for existing and missing URL.
- **Assertions**:
  - Existing URL returns attachment ID.
  - Missing URL returns `null`.

### `test_create_gallery_sets_template_and_attachments`
- **Purpose**: Ensure gallery creation sets template, settings, and attachments.
- **Setup**: Create an attachment.
- **Steps**:
  - Call `foogallery_create_gallery('masonry', $attachment_id)`.
- **Assertions**:
  - Post type is `foogallery`.
  - Template and attachment meta are set.
  - Settings meta is not empty.
- **Cleanup**:
  - Delete gallery post.

## Test Groups
- Group: `functions`

## Run Commands
- `npm run test:php`