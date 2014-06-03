<?php
if (!class_exists('FooGallery_Extension_Template_Base')) {

	abstract class FooGallery_Extension_Template_Base {

		function __construct() {
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_extension_file' ) );
		}

		function register_extension_file( $extensions ) {
			$reflector = new ReflectionClass( get_class( $this ) );
			$extensions[] = $reflector->getFileName();
			return $extensions;
		}

		protected $slug = false;

		protected function is_active() {
			$api = new FooGallery_Extensions_API( false );
			return $api->is_active( $this->slug );
		}
	}
}