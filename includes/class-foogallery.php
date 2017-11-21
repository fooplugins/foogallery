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
		$this->_post = null;
		$this->ID = 0;
		$this->attachment_ids = array();
		$this->_attachments = false;
		$this->datasource_name = foogallery_default_datasource();
		$this->_datasource = false;
		$this->settings = array();
	}

	/**
	 * private gallery load function
	 * @param $post
	 */
	private function load( $post ) {
		$this->_post = $post;
		$this->ID = $post->ID;
		$this->slug = $post->post_name;
		$this->name = $post->post_title;
		$this->author = $post->post_author;
		$this->post_status = $post->post_status;

		$attachment_meta = get_post_meta( $post->ID, FOOGALLERY_META_ATTACHMENTS, true );
		$this->attachment_ids = is_array( $attachment_meta ) ? array_filter( $attachment_meta ) : array();

		$this->load_meta( $post->ID );

		do_action( 'foogallery_instance_after_load', $this, $post );
	}

	/**
	 * private meta data load function
	 * @param $post_id int
	 */
	private function load_meta( $post_id ) {
		$this->gallery_template = get_post_meta( $post_id, FOOGALLERY_META_TEMPLATE, true );
		$this->settings = $this->load_settings( $post_id );
		$this->custom_css = get_post_meta( $post_id, FOOGALLERY_META_CUSTOM_CSS, true );
		$this->sorting = get_post_meta( $post_id, FOOGALLERY_META_SORT, true );
		$this->datasource_name = get_post_meta( $post_id, FOOGALLERY_META_DATASOURCE, true );
		if ( empty( $this->datasource_name ) ) {
			$this->datasource_name = foogallery_default_datasource();
		}
        $this->retina = get_post_meta( $post_id, FOOGALLERY_META_RETINA, true );
		$this->force_use_original_thumbs = 'true' === get_post_meta( $post_id, FOOGALLERY_META_FORCE_ORIGINAL_THUMBS, true );
	}

	private function load_settings( $post_id ) {
		$settings = get_post_meta( $post_id, FOOGALLERY_META_SETTINGS, true );

		//the gallery is considered new if the template has not been set
		$is_new = empty( $this->gallery_template );

		//if we have no settings, and the gallery is not new, then allow for an upgrade
		if ( empty( $settings ) && !$is_new ) {
			$settings = apply_filters( 'foogallery_settings_upgrade', $settings, $this );
		}

		//if we still have no settings, then get default settings for the gallery template
        if ( empty( $settings ) && !$is_new ) {
		    $settings = foogallery_build_default_settings_for_gallery_template( $this->gallery_template );

            $settings = apply_filters('foogallery_default_settings-' . $this->gallery_template, $settings, $this);
        }

		return $settings;
	}

	/**
	 * private function to load a gallery by an id
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
	 * @param $template
	 * @param $attachment_ids
	 *
	 * @return FooGallery
	 */
	public static function dynamic( $template, $attachment_ids ) {
		$gallery = new self( null );

		$gallery->gallery_template = $template;
		$gallery->attachment_ids = $attachment_ids;

		//loads all meta data from the default gallery
		$default_gallery_id = foogallery_get_setting( 'default_gallery_settings' );
		if ( $default_gallery_id > 0 ) {
			$gallery->load_meta( $default_gallery_id );
		}

		return $gallery;
	}

	/**
	 * Static function to load a Gallery instance by passing in a post object
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
	 * Returns the number of attachments in the current gallery
	 * @return int
	 */
	public function attachment_count() {
		return $this->datasource()->getCount();
	}

	/**
	 * Checks if the gallery has attachments
	 * @return bool
	 */
	public function has_attachments() {
		return $this->attachment_count() > 0;
	}

	/**
	 * Checks if the gallery exists
	 * @return bool
	 */
	public function does_exist() {
		return $this->ID > 0;
	}

	/**
	 * Returns true if the gallery is published
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
	 * @return string
	 */
	public function attachment_id_csv() {
		return $this->datasource()->getSerializedData();
	}

	/**
	 * Lazy load the attachments for the gallery
	 *
	 * @return array
	 */
	public function attachments() {
		//lazy load the attachments for performance
		if ( $this->_attachments === false ) {
			$this->_attachments = $this->datasource()->getAttachments();
		}

		return $this->_attachments;
	}

	/**
	 * @deprecated 1.3.0 This is now moved into the datasource implementation
	 *
	 * This forces the attachments to be fetched using the correct ordering.
	 * Some plugins / themes override this globally for some reason, so this is a preventative measure to ensure sorting is correct
	 * @param $query WP_Query
	 */
	public function force_gallery_ordering( $query ) {
		_deprecated_function( __FUNCTION__, '1.3.0' );
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
	 * @deprecated 1.3.0 This is now moved into the datasource implementation
	 *
	 * @return int|mixed|string
	 */
	public function find_featured_attachment_id() {
		_deprecated_function( __FUNCTION__, '1.3.0' );

		return 0;
	}

	/**
	 * Gets the featured image FooGalleryAttachment object. If no featured image is set, then get back the first image in the gallery
	 *
	 * @return bool|FooGalleryAttachment
	 */
	public function featured_attachment() {
		return $this->datasource()->getFeaturedAttachment();
	}

	/**
	 * @deprecated 1.3.0 This is now moved into the datasource implementation
	 *
	 * @param string $size
	 * @param bool   $icon
	 *
	 * @return bool
	 */
	public function featured_image_src( $size = 'thumbnail', $icon = false ) {
		_deprecated_function( __FUNCTION__, '1.3.0' );

		return false;
	}

	/**
	 * @deprecated 1.3.0 This is now moved into the datasource implementation
	 *
	 * Get an HTML img element representing the featured image for the gallery
	 *
	 * @param string $size Optional, default is 'thumbnail'.
	 * @param bool $icon Optional, default is false. Whether it is an icon.
	 *
	 * @return string HTML img element or empty string on failure.
	 */
	public function featured_image_html( $size = 'thumbnail', $icon = false ) {
		_deprecated_function( __FUNCTION__, '1.3.0' );

		return '';
	}

	public function image_count() {
		$no_images_text = foogallery_get_setting( 'language_images_count_none_text',   __( 'No images', 'foogallery' ) );
		$singular_text  = foogallery_get_setting( 'language_images_count_single_text', __( '1 image', 'foogallery' ) );
		$plural_text    = foogallery_get_setting( 'language_images_count_plural_text', __( '%s images', 'foogallery' ) );

		$count = $this->attachment_count();

		switch ( $count ) {
			case 0:
				$count_text = $no_images_text === false ? __( 'No images', 'foogallery' ) : $no_images_text;
				break;
			case 1:
				$count_text = $singular_text === false ? __( '1 image', 'foogallery' ) : $singular_text;
				break;
			default:
				$count_text = sprintf( $plural_text === false ?  __( '%s images', 'foogallery' ) : $plural_text, $count );
		}

		return apply_filters( 'foogallery_image_count', $count_text, $this );
	}

	/**
	 * Returns a safe name for the gallery, in case there has been no title set
	 *
	 * @return string
	 */
	public function safe_name() {
		return empty( $this->name ) ?
				sprintf( __( '%s #%s', 'foogallery' ), foogallery_plugin_name(), $this->ID ) :
				$this->name;
	}

	public function find_usages() {
		return get_posts( array(
			'post_type'      => array( 'post', 'page', ),
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
	 * @return string|void
	 */
	public function gallery_template_name() {
		$template = $this->gallery_template_details();
		if ( false !== $template ) {
			return $template['name'];
		}
		return __( 'Unknown', 'foogallery' );
	}

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
	 * Returns the current gallery datasource object
	 *
	 * @returns IFooGalleryDatasource
	 */
	public function datasource() {
		//lazy load the datasource only when needed
		if ( $this->_datasource === false ) {
			$this->_datasource = foogallery_instantiate_datasource( $this->datasource_name );
			$this->_datasource->setGallery( $this );
		}

		return $this->_datasource;
	}
}
