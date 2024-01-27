<?php

// Common includes.
require_once FOOGALLERY_PATH . 'includes/render-functions.php';
require_once FOOGALLERY_PATH . 'includes/class-posttypes.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-attachment.php';
require_once FOOGALLERY_PATH . 'includes/class-thumbnails.php';
require_once FOOGALLERY_PATH . 'includes/extensions/class-extension.php';
require_once FOOGALLERY_PATH . 'includes/extensions/class-extensions-api.php';
require_once FOOGALLERY_PATH . 'includes/extensions/class-extensions-loader.php';
require_once FOOGALLERY_PATH . 'includes/class-attachment-filters.php';
require_once FOOGALLERY_PATH . 'includes/class-retina.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-widget.php';
require_once FOOGALLERY_PATH . 'gutenberg/class-foogallery-gutenberg.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-debug.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-attachment-type.php';

// Include built-in thumbnail generation files.
require_once FOOGALLERY_PATH . 'includes/thumbs/includes.php';

// Include bundled extensions.
require_once FOOGALLERY_PATH . 'extensions/albums/class-albums-extension.php';
require_once FOOGALLERY_PATH . 'extensions/default-templates/class-default-templates-extension.php'; // Legacy!
require_once FOOGALLERY_PATH . 'extensions/default-templates/class-default-templates.php';
require_once FOOGALLERY_PATH . 'extensions/demo-content-generator/class-demo-content-generator.php';
require_once FOOGALLERY_PATH . 'extensions/import-export/class-foogallery-import-export-extension.php';

// load Template Loader files.
require_once FOOGALLERY_PATH . 'includes/public/class-foogallery-template-loader.php';

// Load all Compatibility files.
require_once FOOGALLERY_PATH . 'includes/compatibility/class-foogallery-compatibility.php';

require_once FOOGALLERY_PATH . 'includes/class-version-check.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-animated-gif-support.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-cache.php';
require_once FOOGALLERY_PATH . 'includes/class-thumbnail-dimensions.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-common-fields.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-lazyload.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-paging.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-attachment-custom-class.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-extensions-compatibility.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-crop-position.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-force-https.php';

// Datasource includes.
require_once FOOGALLERY_PATH . 'includes/class-foogallery-datasource-media_library.php';

if ( is_admin() ) {

	// Only admin includes.
	require_once FOOGALLERY_PATH . 'includes/admin/class-admin.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-extensions.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-settings.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-editor.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-items.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-fields.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-settings.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metabox-settings-helper.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-menu.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-columns.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-attachment-fields.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-admin-notices.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-datasources.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-pro-promotion.php';
	require_once FOOGALLERY_PATH . 'includes/admin/class-demo-content.php';
	
	// Admin gallery modal new
	require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-attachment-modal.php';

} else {
	// Only front-end includes.
	require_once FOOGALLERY_PATH . 'includes/public/class-public.php';
	require_once FOOGALLERY_PATH . 'includes/public/class-css-load-optimizer.php';
	require_once FOOGALLERY_PATH . 'includes/public/class-admin-bar.php';
	require_once FOOGALLERY_PATH . 'includes/public/class-yoast-seo-sitemaps.php';
	require_once FOOGALLERY_PATH . 'includes/public/class-rank-math-seo-sitemaps.php';
	require_once FOOGALLERY_PATH . 'includes/public/class-aioseo-sitemaps.php';
}

require_once FOOGALLERY_PATH . 'includes/public/class-shortcodes.php';
require_once FOOGALLERY_PATH . 'includes/class-gallery-advanced-settings.php';
require_once FOOGALLERY_PATH . 'includes/class-il8n.php';

require_once FOOGALLERY_PATH . 'includes/class-foogallery-lightbox.php';
