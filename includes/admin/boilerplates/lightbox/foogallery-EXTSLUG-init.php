<?php
//This init class is used to add the extension to the extensions list while you are developing them.
//When the extension is added to the supported list of extensions, this file is no longer needed.

if ( !class_exists( '{package}_Init' ) ) {
	class {package}_Init {

		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'add_to_extensions_list' ) );
		}

		function add_to_extensions_list( $extensions ) {
			$extensions[] = array(
				'slug'=> '{slug}',
				'class'=> '{package}',
				'title'=> '{name}',
				'description'=> '{desc}',
				'author'=> '{author}',
				'author_url'=> '{author_link}',
				'thumbnail'=> '/assets/extension_bg.png',
				'categories'=> array('Build Your Own'),
				'tags'=> array('{type}'),
				'source'=> 'generated'
			);

			return $extensions;
		}
	}

	new {package}_Init();
}