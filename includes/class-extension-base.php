<?php
if (!class_exists('FooGallery_Extension_Base')) {

    class FooGallery_Extension_Base {

		protected $slug = false;

		function __construct() {
			add_action( 'init', array( $this, 'try_run' ) );
		}

		protected function run() {
			throw new Exception( sprintf( __('Failed to run FooGallery extension running from %s', 'foogallery' ), __FILE__) );
		}

		public function try_run() {
			if ( $this->is_active() ) {
				$this->run();
			}
		}

		protected function is_active() {
			$api = new FooGallery_Extensions_API( false );
			return $api->is_active( $this->slug );
		}
	}
}