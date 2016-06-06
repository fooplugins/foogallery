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
		$attachment_meta = get_post_meta( $this->ID, FOOGALLERY_META_ATTACHMENTS, true );
		$this->attachment_ids = is_array( $attachment_meta ) ? array_filter( $attachment_meta ) : array();
		$this->gallery_template = get_post_meta( $post->ID, FOOGALLERY_META_TEMPLATE, true );
		$this->settings = get_post_meta( $post->ID, FOOGALLERY_META_SETTINGS, true );
		$this->custom_css = get_post_meta( $post->ID, FOOGALLERY_META_CUSTOM_CSS, true );
		$this->sorting = get_post_meta( $post->ID, FOOGALLERY_META_SORT, true );
		do_action( 'foogallery_foogallery_instance_after_load', $this, $post );
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
	 * @return FooGallery
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
	 * @return FooGallery
	 */
	public static function get_by_slug( $slug ) {
		$gallery = new self();
		$gallery->load_by_slug( $slug );
		if ( ! $gallery->does_exist() ) {
			return false;
		}
		return $gallery;
	}

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
	 * Checks if the gallery has attachments
	 * @return bool
	 */
	public function has_attachments() {
		return sizeof( $this->attachment_ids ) > 0;
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
		$settings = get_post_meta( $this->ID, FOOGALLERY_META_SETTINGS, true );
		return empty( $settings );
	}

	/**
	 * Get a comma separated list of attachment ids
	 * @return string
	 */
	public function attachment_id_csv() {
		if ( is_array( $this->attachment_ids ) ) {
			return implode( ',', $this->attachment_ids );
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
			$this->_attachments = array();

			if ( ! empty( $this->attachment_ids ) ) {

				add_action( 'pre_get_posts', array( $this, 'force_gallery_ordering' ), 99 );

				$attachment_query_args = apply_filters( 'foogallery_attachment_get_posts_args', array(
					'post_type'      => 'attachment',
					'posts_per_page' => -1,
					'post__in'       => $this->attachment_ids,
					'orderby'        => foogallery_sorting_get_posts_orderby_arg( $this->sorting ),
					'order'          => foogallery_sorting_get_posts_order_arg( $this->sorting )
				) );

				$attachments = get_posts( $attachment_query_args );

				remove_action( 'pre_get_posts', array( $this, 'force_gallery_ordering' ), 99 );

				$this->_attachments = array_map( array( 'FooGalleryAttachment', 'get' ), $attachments );
			}
		}

		return $this->_attachments;
	}

	/**
	 * This forces the attachments to be fetched using the correct ordering.
	 * Some plugins / themes override this globally for some reason, so this is a preventative measure to ensure sorting is correct
	 * @param $query WP_Query
	 */
	public function force_gallery_ordering( $query ) {
		//only care about attachments
		if ( array_key_exists( 'post_type', $query->query ) &&
		     'attachment' === $query->query['post_type'] ) {
			$query->set( 'orderby', foogallery_sorting_get_posts_orderby_arg( $this->sorting ) );
			$query->set( 'order', foogallery_sorting_get_posts_order_arg( $this->sorting ) );
		}
	}

	/**
	 * Output the shortcode for the gallery
	 *
	 * @return string
	 */
	public function shortcode() {
		return foogallery_build_gallery_shortcode( $this->ID );
	}

	public function find_featured_attachment_id() {
		$attachment_id = get_post_thumbnail_id( $this->ID );

		//if no featured image could be found then get the first image
		if ( ! $attachment_id && $this->attachment_ids ) {
			$attachment_id_values = array_values( $this->attachment_ids );
			$attachment_id = array_shift( $attachment_id_values );
		}
		return $attachment_id;
	}

	/**
	 * Gets the featured image FooGalleryAttachment object. If no featured image is set, then get back the first image in the gallery
	 *
	 * @return bool|FooGalleryAttachment
	 */
	public function featured_attachment() {
		$attachment_id = $this->find_featured_attachment_id();

		if ( $attachment_id ) {
			return FooGalleryAttachment::get_by_id( $attachment_id );
		}

		return false;
	}

	public function featured_image_src( $size = 'thumbnail', $icon = false ) {
		$attachment_id = $this->find_featured_attachment_id();
		if ( $attachment_id && $image_details = wp_get_attachment_image_src( $attachment_id, $size, $icon ) ) {
			return reset( $image_details );
		}
		return false;
	}

	/**
	 * Get an HTML img element representing the featured image for the gallery
	 *
	 * @param string $size Optional, default is 'thumbnail'.
	 * @param bool $icon Optional, default is false. Whether it is an icon.
	 *
	 * @return string HTML img element or empty string on failure.
	 */
	public function featured_image_html( $size = 'thumbnail', $icon = false ) {
		$attachment_id = $this->find_featured_attachment_id();
		if ( $attachment_id && $thumb = @wp_get_attachment_image( $attachment_id, $size, $icon ) ) {
			return $thumb;
		}
		return '';
	}

	public function image_count() {
		$no_images_text = foogallery_get_setting( 'language_images_count_none_text',   __( 'No images', 'foogallery' ) );
		$singular_text  = foogallery_get_setting( 'language_images_count_single_text', __( '1 image', 'foogallery' ) );
		$plural_text    = foogallery_get_setting( 'language_images_count_plural_text', __( '%s images', 'foogallery' ) );

		$count = sizeof( $this->attachment_ids );

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
			$this->gallery_template = get_post_meta( $default_gallery_id, FOOGALLERY_META_TEMPLATE, true );
			$this->settings = get_post_meta( $default_gallery_id, FOOGALLERY_META_SETTINGS, true );
			$this->sorting = foogallery_get_setting( 'gallery_sorting' );
		}
	}
}
