<?php
namespace FooPlugins\FooGallery\Admin;
use WP_Upgrader_Skin;

class FooGallery_Silent_Installer_Skin extends WP_Upgrader_Skin {
	public $feedback = false;

	public function header() { }
	public function footer() { }
	public function before() { }
	public function after() { }
	public function feedback( $feedback, ...$args  ) {
		$this->feedback = $feedback;
	}
}
