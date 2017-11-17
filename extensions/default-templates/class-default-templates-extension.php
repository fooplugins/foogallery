<?php
/**
 * Legacy Default Extensions Class for previous versions where the default templates were an extension.
 * The class is now empty and has no logic. It is purely here so that existing installs do not break
 */

if ( ! class_exists( 'FooGallery_Default_Templates_Extension' ) ) {

	class FooGallery_Default_Templates_Extension {
		function __construct() {
		}
	}
}
