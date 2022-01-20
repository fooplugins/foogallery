<?php
/**
 * A base datasource class that can be extended by datasources to reduce required code.
 * The FooGallery attachment data will be cached for 24 hours once it is retrieved from the source.
 * The cache expiry can be overridden with a filter.
 *
 * @since 2.1.34
 */
if ( ! class_exists( 'FooGallery_Datasource_Base' ) ) {

	abstract class FooGallery_Datasource_Base {

		protected $datasource_key;
		protected $datasource_name;
		protected $admin_js;
		protected $admin_css;

		function __construct( $datasource_key, $datasource_name, $admin_js = null, $admin_css = null ) {
			$this->datasource_key = $datasource_key;
			$this->datasource_name = $datasource_name;
			$this->admin_js = $admin_js;
			$this->admin_css = $admin_css;

			add_filter( 'foogallery_gallery_datasources', array( $this, 'add_datasource' ) );

			add_action( "foogallery-datasource-modal-content_{$datasource_key}", array( $this, 'render_datasource_modal_content' ), 10, 2 );
			add_action( 'foogallery_gallery_metabox_items_list', array( $this, 'render_datasource_state' ), 10, 1 );

			// Filters required for the datasource
			add_filter( "foogallery_datasource_{$datasource_key}_item_count", array( $this, 'get_gallery_attachment_count'	), 10, 2 );
			add_filter( "foogallery_datasource_{$datasource_key}_attachment_ids", array( $this, 'get_gallery_attachment_ids' ), 10, 2 );
			add_filter( "foogallery_datasource_{$datasource_key}_attachments", array( $this, 'get_gallery_attachments'	), 10, 2 );
			add_filter( "foogallery_datasource_{$datasource_key}_featured_image", array( $this, 'get_gallery_featured_attachment' ), 10, 2 );

			// When a gallery is saved in the admin, clear the cached gallery data.
			add_action( 'foogallery_before_save_gallery_datasource', array( $this, 'clear_gallery_transient' ) );

			// Enqueue some assets for the datasource.
			add_action( 'foogallery_admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );
		}

		/**
		 * Returns the transient key used to cache the gallery data.
		 *
		 * @param $foogallery_id
		 *
		 * @return string
		 */
		protected function get_transient_key( $foogallery_id ) {
			return "_foogallery_datasource_{$this->datasource_key}_{$foogallery_id}";
		}

		/**
		 * Clears the cached gallery data.
		 *
		 * @param $foogallery_id
		 */
		public function clear_gallery_transient( $foogallery_id ) {
			delete_transient( $this->get_transient_key( $foogallery_id ) );
		}

		/**
		 * Returns the featured FooGalleryAttachment from the datasource, which will be the first item.
		 *
		 * @param FooGalleryAttachment $default
		 * @param FooGallery $foogallery
		 *
		 * @return bool|FooGalleryAttachment
		 */
		public function get_gallery_featured_attachment( $default, $foogallery ) {
			$attachments = $this->get_gallery_attachments_from_datasource( $foogallery );
			return reset( $attachments );
		}

		/**
		 * Returns the number of attachments used for the gallery
		 *
		 * @param int $count
		 * @param FooGallery $foogallery
		 *
		 * @return int
		 */
		public function get_gallery_attachment_count( $count, $foogallery ) {
			return count( $this->get_gallery_attachments_from_datasource( $foogallery ) );
		}

		/**
		 * Returns an array of the attachment ID's for the gallery
		 *
		 * @param $attachment_ids
		 * @param $foogallery
		 *
		 * @return array
		 */
		public function get_gallery_attachment_ids( $attachment_ids, $foogallery ) {
			return array_keys( $this->get_gallery_attachments_from_datasource( $foogallery ) );
		}

		/**
		 * Returns an array of FooGalleryAttachments from the datasource
		 *
		 * @param array $attachments
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		public function get_gallery_attachments( $attachments, $foogallery ) {
			return $this->get_gallery_attachments_from_datasource( $foogallery );
		}

		/**
		 * @var array
		 */
		private $_attachments;

		/**
		 * Returns a cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		protected function get_gallery_attachments_from_datasource( $foogallery ) {
			if ( isset( $this->_attachments ) ) {
				return $this->_attachments;
			}

			global $foogallery_gallery_preview;

			$this->_attachments = array();

			if ( ! empty( $foogallery->datasource_value ) ) {
				$transient_key = $this->get_transient_key( $foogallery->ID );

				//never get the cached results if we are doing a preview
				if ( isset( $foogallery_gallery_preview ) ) {
					$cached_attachments = false;
				} else {
					$cached_attachments = get_transient( $transient_key );
				}

				if ( false === $cached_attachments ) {
					// Get the expiry time in hours. Can be overridden with a filter for the specific datasource.
					$expiry_hours = apply_filters( "foogallery_datasource_{$this->datasource_key}_expiry", 24 );
					$expiry       = $expiry_hours * 60 * 60;

					//find all image files in the post_query
					$this->_attachments = $this->build_attachments_from_datasource( $foogallery );

					//save a cached list of attachments
					set_transient( $transient_key, $this->_attachments, $expiry );
				} else {
					$this->_attachments = $cached_attachments;
				}
			}

			return $this->_attachments;
		}

		/**
		 * Add the Datasource to the array of available datasources.
		 *
		 * @param array $datasources The array of datasources.
		 *
		 * @return mixed
		 */
		public function add_datasource( $datasources ) {

			$datasources[$this->datasource_key] = apply_filters( "foogallery_datasource_{$this->datasource_key}_object", array(
				'id'     => $this->datasource_key,
				'name'   => $this->datasource_name,
				'menu'   => $this->datasource_name,
				'public' => true
			) );

			return $datasources;
		}

		/**
		 * Enqueues assets if required
		 */
		public function enqueue_scripts_and_styles() {
			if ( !empty( $this->admin_js ) ) {
				wp_enqueue_script(
					"foogallery.admin.datasources.{$this->datasource_key}",
					$this->admin_js,
					array( 'jquery' ),
					FOOGALLERY_VERSION
				);
			}
			if ( !empty( $this->admin_css ) ) {
				wp_enqueue_style(
					"foogallery.admin.datasources.{$this->datasource_key}",
					$this->admin_css,
					array(),
					FOOGALLERY_VERSION
				);
			}
		}

		/**
		 * Safely get a value out of the datasource array.
		 *
		 * @param $gallery
		 * @param $key
		 * @param $default
		 *
		 * @return mixed|string
		 */
		protected function get_datasource_value( $gallery, $key, $default = '' ) {
			return isset( $gallery ) &&
			       isset( $gallery->datasource_value ) &&
			       is_array( $gallery->datasource_value ) &&
			       array_key_exists( $key, $gallery->datasource_value ) ? $gallery->datasource_value[$key] : $default;
		}


		/**
		 * Returns a non cached array of FooGalleryAttachments from the datasource
		 *
		 * @param FooGallery $foogallery
		 *
		 * @return array(FooGalleryAttachment)
		 */
		abstract function build_attachments_from_datasource( $foogallery );

		/**
		 * Render the modal content for the datasource.
		 *
		 * @param int $foogallery_id The ID of the current gallery.
		 * @param array $datasource_value The saved datasource value.
		 */
		abstract function render_datasource_modal_content( $foogallery_id, $datasource_value );

		/**
		 * Output the html to display the state of the datasource.
		 *
		 * @param FooGallery $gallery
		 */
		abstract function render_datasource_state( $gallery );
	}
}
