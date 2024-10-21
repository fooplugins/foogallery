<?php

// Common includes.
require_once FOOGALLERY_PATH . 'includes/render-functions.php';
require_once FOOGALLERY_PATH . 'includes/class-foogallery.php';

// Include bundled extensions.
new FooPlugins\FooGallery\Extensions\Album\FooGallery_Albums_Extension();
new FooPlugins\FooGallery\Extensions\DefaultTemplates\FooGallery_Default_Templates_Extension(); // Legacy!
new FooPlugins\FooGallery\Extensions\DemoContentGenerator\FooGallery_Demo_Content_Generator();
