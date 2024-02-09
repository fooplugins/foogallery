<?php

/**
 * Class FooGallery
 *
 * An easy to use wrapper class for a FooGallery gallery post
 */
class FooGallery extends stdClass {

	/**
	 * private constructor
	 *
	 * @param null $post
	 */
	private function __construct( $post = null ) {
		$this->set_defaults();

		if ( $post !== null ) {
			$this->load( $post );
		}
	}

	/**
	 *  Sets the default when a new gallery is instantiated
	 */
	private function set_defaults() {
		$this->_post                     = null;
		$this->ID                        = 0;
		$this->attachment_ids            = array();
		$this->_attachments              = false;
		$this->datasource_name           = foogallery_default_datasource();
		$this->settings                  = array();
		$this->sorting                   = '';
		$this->force_use_original_thumbs = false;
		$this->retina                    = array();
	}

	/**
	 * private gallery load function
	 *
	 * @param $post
	 */
	private function load( $post ) {
		if ( $post->post_type !== FOOGALLERY_CPT_GALLERY ) {
			return;
		}

		$this->_post       = $post;
		$this->ID          = $post->ID;
		$this->slug        = $post->post_name;
		$this->name        = $post->post_title;
		$this->author      = $post->post_author;
		$this->post_status = $post->post_status;

		// Load attachment metadata.
		$attachment_meta      = get_post_meta( $post->ID, FOOGALLERY_META_ATTACHMENTS, true );
		$this->attachment_ids = is_array( $attachment_meta ) ? array_filter( $attachment_meta ) : array();

		// Load datasource metadata.
		$this->datasource_name  = get_post_meta( $post->ID, FOOGALLERY_META_DATASOURCE, true );
		if ( empty( $this->datasource_name ) ) {
			$this->datasource_name = foogallery_default_datasource();
		} else {
			$this->datasource_value = get_post_meta( $post->ID, FOOGALLERY_META_DATASOURCE_VALUE, true );
		}

		$gallery_id = apply_filters( 'foogallery_load_gallery_settings_id', $post->ID, $post );

		$this->load_meta( $gallery_id );

		do_action( 'foogallery_instance_after_load', $this, $post );
	}

	/**
	 * private meta data load function
	 *
	 * @param $post_id int
	 */
	private function load_meta( $post_id ) {
		$this->gallery_template          = get_post_meta( $post_id, FOOGALLERY_META_TEMPLATE, true );
		$this->settings                  = $this->load_settings( $post_id );
		$this->custom_css                = get_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS, true );
		$this->sorting                   = get_post_meta( $post_id, FOOGALLERY_META_SORT, true );
		$this->retina                    = get_post_meta( $post_id, FOOGALLERY_META_RETINA, true );
		$this->force_use_original_thumbs = 'true' === get_post_meta( $post_id, FOOGALLERY_META_FORCE_ORIGINAL_THUMBS, true );
	}

	private function load_settings( $post_id ) {
		$settings = get_post_meta( $post_id, FOOGALLERY_META_SETTINGS, true );

		//the gallery is considered new if the template has not been set
		$is_new = empty( $this->gallery_template );

		//if we have no settings, and the gallery is not new, then allow for an upgrade
		if ( empty( $settings ) && ! $is_new ) {
			$settings = apply_filters( 'foogallery_settings_upgrade', $settings, $this );
		}

		//if we still have no settings, then get default settings for the gallery template
		if ( empty( $settings ) && ! $is_new ) {
			$settings = foogallery_build_default_settings_for_gallery_template( $this->gallery_template );

			$settings = apply_filters( 'foogallery_default_settings-' . $this->gallery_template, $settings, $this );
		}

		//allow the settings to be overridden
		return apply_filters( 'foogallery_settings_override', $settings, $this->gallery_template, $this );
	}

	/**
	 * private function to load a gallery by an id
	 *
	 * @param $post_id
	 */
	private function load_by_id( $post_id ) {
		$post = get_post( $post_id );
		if ( $post ) {
			$this->load( $post );
		}
	}

	/**
	 * private function to load a gallery by the slug.
	 * Will be used when loading gallery shortcodes
	 *
	 * @param $slug
	 */
	private function load_by_slug( $slug ) {
		if ( ! empty( $slug ) ) {
			$args = array(
				'name'        => $slug,
				'numberposts' => 1,
				'post_type'   => FOOGALLERY_CPT_GALLERY,
			);

			$galleries = get_posts( $args );

			if ( $galleries ) {
				$this->load( $galleries[0] );
			}
		}
	}

	/**
	 * Static function to build a dynamic gallery that does not exist in the database
	 *
	 * @param $template
	 * @param $attachment_ids
	 *
	 * @return FooGallery
	 */
	public static function dynamic( $template, $attachment_ids ) {
		$gallery = new self( null );

		$gallery->gallery_template = $template;
		$gallery->attachment_ids   = $attachment_ids;

		//loads all meta data from the default gallery
		$default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
		if ( $default_gallery_id > 0 ) {
			$gallery->load_meta( $default_gallery_id );
		}

		return $gallery;
	}

	/**
	 * Static function to build a dynamic gallery that does not exist in the database for a specific datasource
	 *
	 * @return FooGallery
	 */
	public static function dynamic_for_datasource( $datasource ) {
		$gallery = new self( null );

		//loads all meta data from the default gallery
		$default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
		if ( $default_gallery_id > 0 ) {
			$gallery->load_meta( $default_gallery_id );
		}

		$gallery->datasource_name = $datasource;

		//set the datasource_value from a filter

		return $gallery;
	}

	/**
	 * Static function to load a Gallery instance by passing in a post object
	 *
	 * @static
	 *
	 * @param $post
	 *
	 * @return FooGallery
	 */
	public static function get( $post ) {
		return new self( $post );
	}

	/**
	 * Static function to load a Gallery instance by post id
	 *
	 * @param $post_id
	 *
	 * @return FooGallery | boolean
	 */
	public static function get_by_id( $post_id ) {
		$gallery = new self();
		$gallery->load_by_id( $post_id );
		if ( ! $gallery->does_exist() ) {
			return false;
		}

		return $gallery;
	}

	/**
	 * Static function to load a gallery instance by passing in a gallery slug
	 *
	 * @param string $slug
	 *
	 * @return FooGallery | boolean
	 */
	public static function get_by_slug( $slug ) {
		$gallery = new self();
		$gallery->load_by_slug( $slug );
		if ( ! $gallery->does_exist() ) {
			return false;
		}

		return $gallery;
	}

	/**
	 * Get a setting using the current template and meta key
	 *
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	function get_setting( $key, $default ) {
		return $this->get_meta( "{$this->gallery_template}_$key", $default );
	}

	/**
	 * Get a meta value using a full key
	 *
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	function get_meta( $key, $default ) {
		if ( ! is_array( $this->settings ) ) {
			return $default;
		}

		$value = array_key_exists( $key, $this->settings ) ? $this->settings[ $key ] : null;

		if ( $value === null ) {
			return $default;
		}

		return $value;
	}

	function is_checked( $key, $default = false ) {
		if ( ! is_array( $this->settings ) ) {
			return $default;
		}

		return array_key_exists( $key, $this->settings );
	}

	/**
	 * Returns the number of attachments in the current gallery, that were added from the media library
	 *
	 * @return int
	 */
	public function attachment_count() {
		if ( is_array( $this->attachment_ids ) ) {
			return count( $this->attachment_ids );
		}

		return 0;
	}

	/**
	 * Checks if the gallery has attachments. There will only be attachments if they were added from the media library.
	 *
	 * @return bool
	 */
	public function has_attachments() {
		return $this->attachment_count() > 0;
	}

	/**
	 * Checks if the gallery exists
	 *
	 * @return bool
	 */
	public function does_exist() {
		return $this->ID > 0;
	}

	/**
	 * Returns true if the gallery is published
	 *
	 * @return bool
	 */
	public function is_published() {
		return $this->post_status === 'publish';
	}

	/**
	 * Returns true if the gallery is newly created and not yet saved
	 */
	public function is_new() {
		$template = get_post_meta( $this->ID, FOOGALLERY_META_TEMPLATE, true );

		return empty( $template );
	}

	/**
	 * Get a comma separated list of attachment ids
	 *
	 * @return string
	 */
	public function attachment_id_csv() {
		if ( is_array( $this->attachment_ids ) ) {
			// Filter the array to ensure all values are strings or numbers.
			$filtered_ids = array_filter( $this->attachment_ids, function( $id ) {
				return is_string( $id ) || is_numeric( $id );
			});

			return implode( ',', $filtered_ids );
		}

		return '';
	}

	/**
	 * Lazy load the attachments for the gallery
	 *
	 * @return array
	 */
	public function attachments() {
		//lazy load the attachments for performance
		if ( $this->_attachments === false ) {
			$this->_attachments = $this->apply_datasource_filter( 'attachments', array() );
		}

		return $this->_attachments;
	}

	/**
	 * Output the shortcode for the gallery
	 *
	 * @return string
	 */
	public function shortcode() {
		return foogallery_build_gallery_shortcode( $this->ID );
	}

	/**
	 * Gets the featured image FooGalleryAttachment object. If no featured image is set, then get back the first image
	 * in the gallery
	 *
	 * @return bool|FooGalleryAttachment
	 */
	public function featured_attachment() {
		//first try get back the featured image for the gallery
		$attachment_id = get_post_thumbnail_id( $this->ID );
		if ( $attachment_id ) {
			return FooGalleryAttachment::get_by_id( $attachment_id );
		}

		//then get the featured image from the datasource
		$default_placeholder_attachment      = new FooGalleryAttachment();
		$default_placeholder_attachment->url = foogallery_image_placeholder_src();

		return $this->apply_datasource_filter( 'featured_image', $default_placeholder_attachment );
	}

	/**
	 * Returns the string representation of the number of items in the gallery
	 *
	 * @return string
	 */
	public function image_count() {
		$no_images_text = esc_html( foogallery_get_setting( 'language_images_count_none_text', __( 'No images', 'foogallery' ) ) );
		$singular_text  = esc_html( foogallery_get_setting( 'language_images_count_single_text', __( '1 image', 'foogallery' ) ) );
		$plural_text    = esc_html( foogallery_get_setting( 'language_images_count_plural_text', __( '%s images', 'foogallery' ) ) );

		$count = $this->item_count();

		switch ( $count ) {
			case 0:
				$count_text = $no_images_text === false ? __( 'No images', 'foogallery' ) : $no_images_text;
				break;
			case 1:
				$count_text = $singular_text === false ? __( '1 image', 'foogallery' ) : $singular_text;
				break;
			default:
				$count_text = sprintf( $plural_text === false ? __( '%s images', 'foogallery' ) : $plural_text, $count );
		}

		return esc_html( apply_filters( 'foogallery_image_count', $count_text, $this, $count ) );
	}

	/**
	 * Returns a safe name for the gallery, in case there has been no title set
	 *
	 * @return string
	 */
	public function safe_name() {
		return empty( $this->name ) ? sprintf( __( '%s #%s', 'foogallery' ), foogallery_plugin_name(), $this->ID ) : $this->name;
	}

	/**
	 * Finds usages of the FooGallery
	 *
	 * @return WP_Post[]
	 */
	public function find_usages() {
		return get_posts( array(
			'post_type'      => foogallery_allowed_post_types_for_usage(),
			'post_status'    => array( 'draft', 'publish', ),
			'posts_per_page' => -1,
			'orderby'        => 'post_type',
			'meta_query'     => array(
				array(
					'key'     => FOOGALLERY_META_POST_USAGE,
					'value'   => $this->ID,
					'compare' => 'IN',
				),
			),
		) );
	}

	/**
	 * Returns the current gallery template details
	 *
	 * @return array|bool
	 */
	public function gallery_template_details() {
		if ( ! empty( $this->gallery_template ) ) {

			foreach ( foogallery_gallery_templates() as $template ) {
				if ( $this->gallery_template == $template['slug'] ) {
					return $template;
				}
			}
		}

		return false;
	}

	/**
	 * Returns the name of the gallery template
	 *
	 * @return string
	 */
	public function gallery_template_name() {
		$template = $this->gallery_template_details();
		if ( false !== $template ) {
			return $template['name'];
		}

		return __( 'Unknown', 'foogallery' );
	}

	/**
	 * Returns true if the gallery template has a field of a certain type
	 *
	 * @param $field_type
	 *
	 * @return bool
	 */
	public function gallery_template_has_field_of_type( $field_type ) {
		$gallery_template_details = $this->gallery_template_details();

		if ( false != $gallery_template_details ) {
			if ( array_key_exists( 'fields', $gallery_template_details ) ) {
				foreach ( $gallery_template_details['fields'] as $field ) {
					if ( $field_type == $field['type'] ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Loads default settings from another gallery if it is set on the settings page
	 */
	public function load_default_settings_if_new() {
		if ( $this->is_new() ) {
			$default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
			//loads all meta data from the default gallery
			$this->load_meta( $default_gallery_id );
		}
	}


	/**
	 * Small helper function to apply the datasource-specific filters in a consistent way
	 *
	 * @param $filter_name
	 * @param $filter_default_value
	 *
	 * @return mixed
	 * @since 1.8.0
	 */
	private function apply_datasource_filter( $filter_name, $filter_default_value ) {
		return apply_filters( "foogallery_datasource_{$this->datasource_name}_{$filter_name}", $filter_default_value, $this );
	}

	/**
	 * Does the current gallery contain any items
	 *
	 * @return bool
	 * @since 1.8.0
	 */
	public function has_items() {
		return $this->item_count() > 0;
	}

	/**
	 * Return the number of items in the gallery. This is determined by querying the gallery datasource
	 *
	 * @return bool
	 * @since 1.8.0
	 */
	public function item_count() {
		return $this->apply_datasource_filter( 'item_count', 0 );
	}

	/**
	 * Returns an array of the attachment ID's within the gallery
	 *
	 * @return array
	 */
	public function item_attachment_ids() {
		return $this->apply_datasource_filter( 'attachment_ids', $this->attachment_ids );
	}

	public function is_empty() {
		if ( foogallery_default_datasource() === $this->datasource_name ) {
			return $this->attachment_count() === 0;
		}

		return empty( $this->datasource_value );
	}

	/**
	 * Returns true if the datasource is not media_library
	 */
	public function is_dynamic() {
		return $this->datasource_name !== foogallery_default_datasource();
	}

	private $container_id;

	/**
	 * Returns the ID that is rendered on the container div of the gallery
	 *
	 * @return string
	 */
	public function container_id() {
		if ( isset( $this->container_id ) ) {
			return $this->container_id;
		}

		global $foogallery_container_ids;

		if ( !isset( $foogallery_container_ids ) ) {
			$foogallery_container_ids = array();
		}
		$foogallery_container_id = 'foogallery-gallery-' . $this->ID;

		if ( array_key_exists( $this->ID, $foogallery_container_ids ) ) {
			//The FooGallery has already been added to the page, so we need to generate a new container_id

			$count = count( $foogallery_container_ids[$this->ID] );
			$foogallery_container_id .= '_' . $count;
		}

		$foogallery_container_ids[$this->ID][] = $foogallery_container_id;

		$this->container_id = $foogallery_container_id;

		return $foogallery_container_id;
	}
}
