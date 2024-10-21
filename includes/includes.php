<?php

// Common includes.
require_once FOOGALLERY_PATH . 'includes/render-functions.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery.php';
require_once FOOGALLERY_PATH . 'includes/extensions/class-extension.php';
require_once FOOGALLERY_PATH . 'includes/extensions/class-extensions-api.php';
require_once FOOGALLERY_PATH . 'includes/extensions/class-extensions-loader.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery-widget.php';

// Include built-in thumbnail generation files.
require_once FOOGALLERY_PATH . 'includes/thumbs/includes.php';

// Include bundled extensions.
new FooPlugins\FooGallery\Extensions\Album\FooGallery_Albums_Extension();
new FooPlugins\FooGallery\Extensions\DefaultTemplates\FooGallery_Default_Templates_Extension; // Legacy!
new FooPlugins\FooGallery\Extensions\DemoContentGenerator\FooGallery_Demo_Content_Generator();

// load Template Loader files.
require_once FOOGALLERY_PATH . 'includes/public/class-foogallery-template-loader.php';
