<?php
//This init class is used to add the extension to the extensions list while you are developing them.
//When the extension is added to the supported list of extensions, this file is no longer needed.

if ( !class_exists( 'Custom_Branding_FooGallery_Extension_Init' ) ) {
	class Custom_Branding_FooGallery_Extension_Init {

		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'add_to_extensions_list' ) );
		}

		function add_to_extensions_list( $extensions ) {
			$extensions[] = array(
				'slug'=> 'custom_branding',
				'class'=> 'Custom_Branding_FooGallery_Extension',
				'title'=> __('Custom Branding', 'foogallery-custom_branding'),
				'file' => 'foogallery-custom-branding-extension.php',
				'description'=> __('Brand FooGallery to whatever you like. Ideal for freelancers and agencies', 'foogallery-custom_branding'),
				'author'=> 'Brad Vincent',
				'author_url'=> 'http://fooplugins.com',
				'thumbnail'=> CUSTOM_BRANDING_FOOGALLERY_EXTENSION_URL . '/assets/extension_bg.png',
				'tags'=> array( __('functionality', 'foogallery'), __('premium', 'foogallery') ),	//use foogallery translations
				'categories'=> array( __('Premium', 'foogallery') ), //use foogallery translations
				'source' => 'fooplugins',
				'download_button' => array(
					'text' => 'Buy - $27',
					'target' => '_blank',
					'href' => 'http://fooplugins.com/plugins/foogallery-custom-branding',
					'confirm' => false
				)
			);

			return $extensions;
		}
	}

	new Custom_Branding_FooGallery_Extension_Init();
}