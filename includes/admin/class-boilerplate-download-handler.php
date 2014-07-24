<?php

/**
 * Boilerplate Download Handler for FooGallery
 */
if ( ! class_exists( 'FooGallery_Boilerplate_Download_Handler' ) ) {

	class FooGallery_Boilerplate_Download_Handler {

		function __construct() {
			add_action( 'admin_init', array( $this, 'listen_for_boilerplate_download' ), 1 );
		}

		private $slug;

		function listen_for_boilerplate_download() {
			$nonce = safe_get_from_request( 'foogallery_boilerplate_nonce' );
			$action = safe_get_from_request( 'action' );

			if ( empty($nonce) || empty($action) ) {
				return;
			}

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'foogallery_boilerplate' ) ) {

				$boilerplate_type        = $_POST['boilerplate_type'];
				$boilerplate_name        = $_POST['boilerplate_name'];
				$boilerplate_desc        = $_POST['boilerplate_desc'];
				$boilerplate_author      = $_POST['boilerplate_author'];
				$boilerplate_author_link = $_POST['boilerplate_author_link'];

				if ( 'download' === $action ) {
					$this->run( $boilerplate_name, $boilerplate_type, $boilerplate_desc, $boilerplate_author, $boilerplate_author_link );
				}
			}
		}

		function run( $boilerplate_name, $boilerplate_type, $boilerplate_desc, $boilerplate_author, $boilerplate_author_link ) {
			$this->slug = str_replace( ' ', '-', strtolower( $boilerplate_name ) );
			$package = str_replace( ' ', '_', foo_title_case( $boilerplate_name . ' ' . $boilerplate_type ) ) . '_FooGallery_Extension';
			$constant = strtoupper( $package );

			//setup some variables for replacement
			$variables = array(
				'{name}'        => $boilerplate_name,
				'{slug}'		=> $this->slug,
				'{plugin_slug}' => "foogallery-{$this->slug}",
				'{package}'		=> $package,
				'{constant}'	=> $constant,
				'{type}'		=> $boilerplate_type,
				'{desc}'        => $boilerplate_desc,
				'{author}'      => $boilerplate_author,
				'{author_link}' => $boilerplate_author_link,
			);

			$upload_dir = wp_upload_dir();

			//create the generator
			$zip_generator = new FooGallery_Boilerplate_Zip_Generator( array(
				'name'                 => 'foogallery',
				'process_extensions'   => array( 'php', 'css', 'js', 'txt', ),
				'source_directory'     => FOOGALLERY_PATH . "/includes/admin/boilerplates/{$boilerplate_type}/",
				'zip_root_directory'   => "foogallery-{$this->slug}-{$boilerplate_type}",
				'download_filename'    => "foogallery-{$this->slug}-{$boilerplate_type}.zip",
				'filename_filter'      => array( $this, 'process_zip_filename' ),
				'variables'            => $variables,
				'zip_temp_directory'   => $upload_dir['path'],
			));

			//generate the zip file
			$zip_generator->generate();

			//download it to the client
			$zip_generator->send_download_headers();
			die();
		}

		function process_zip_filename( $filename ) {
			//replace slug
			$new_filename = str_replace( 'EXTSLUG', $this->slug, $filename );
			//rename to php
			$new_filename = str_replace( '.php.txt', '.php', $new_filename );

			return $new_filename;
		}
	}
}

