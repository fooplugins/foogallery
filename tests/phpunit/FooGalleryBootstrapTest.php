<?php

class FooGalleryBootstrapTest extends WP_UnitTestCase {
	public function test_plugin_loaded() {
		$this->assertTrue( class_exists( 'FooGallery_Plugin' ) );
	}
}
