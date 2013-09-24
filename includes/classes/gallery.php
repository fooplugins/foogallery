<?php

/**
 * Class FooGallery_Gallery
 *
 * An easy to use wrapper class for a gallery post
 */
class FooGallery_Gallery extends stdClass {

	/**
	 * private constructor
	 *
	 * @param null $post
	 */
	private function __construct($post = NULL) {
		$this->set_defaults();

		if ($post !== NULL) {
			$this->load($post);
		}
    }

	/**
	 *  Sets the default when a new gallery is instantiated
	 */
	private function set_defaults() {
		$this->_post = NULL;
		$this->ID = 0;
		$this->_attachments = false;
	}

	/**
	 * private gallery load function
	 * @param $post
	 */
	private function load($post) {

		$this->_post = $post;
		$this->ID = $post->ID;
		$this->slug = $post->post_name;
		$this->name = $post->post_title;
		$this->author = $post->post_author;
        $this->post_status = $post->post_status;
		$this->attachments_meta = get_post_meta($post->ID, FOOGALLERY_META_ATTACHMENTS, true);
        $settings = get_post_meta($post->ID, FOOGALLERY_META_SETTINGS, true);
        $this->link_to_image = $this->is_checked( $settings, 'link_to_image', true );
        $this->default_template = $this->get_meta( $settings, 'default_template', 'default' );

		do_action('foogallery_gallery_after_load', $this, $post);
	}

	/**
	 * private function to load a gallery by an id
	 * @param $post_id
	 */
	private function load_by_id($post_id) {
		$post = get_post($post_id);
		if ($post) {
			$this->load($post);
		}
	}

	/**
	 * private function to load a gallery by the slug.
	 * Will be used when loading gallery shortcodes
	 * @param $slug
	 */
	private function load_by_slug($slug) {
		if (!empty($slug)) {
			$args = array(
				'name' => $slug,
				'numberposts' => 1,
				'post_type' => FOOGALLERY_CPT_GALLERY
			);

			$galleries = get_posts($args);

			if ($galleries) {
				$this->load($galleries[0]);
			}
		}
	}

	/**
	 * Static function to load a Gallery instance by passing in a post object
	 * @static
	 *
	 * @param $post
	 *
	 * @return FooGallery_Gallery
	 */
	public static function get($post) {
		return new self($post);
	}

	/**
	 * Static function to load a Gallery instance by post id
	 *
	 * @param $post_id
	 *
	 * @return FooGallery_Gallery
	 */
	public static function get_by_id($post_id) {
		$gallery = new self();
		$gallery->load_by_id($post_id);
		return $gallery;
	}

	/**
	 * Static function to load a gallery instance by passing in a gallery slug
	 *
	 * @param $slug
	 *
	 * @return FooGallery_Gallery
	 */
	public static function get_by_slug($slug) {
		$gallery = new self();
		$gallery->load_by_slug($slug);
		return $gallery;
	}

    function get_meta($data, $key, $default) {
        if (!is_array($data)) return $default;

        $value = array_key_exists($key, $data) ? $data[$key] : NULL;

        if ($value === NULL)
            return $default;

        return $value;
    }

    function is_checked($data, $key, $default = false) {
        if (!is_array($data)) return $default;

        return array_key_exists($key, $data);

        return $default;
    }

	/**
	 * Checks if the gallery has attachments
	 * @return bool
	 */
	public function has_attachments() {
		if ( $this->_attachments !== false ) {
			return sizeof($this->_attachments) > 0;
		} else if (!empty($this->attachments_meta)) {
			return sizeof( explode( ',', $this->attachments_meta ) ) > 0;
		}

		return false;
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
	 * Lazy load the attachments for the gallery
	 *
	 * @param bool $fetch if true will fetch the attachment info
	 *
	 * @return array
	 */
	public function attachments() {
		//lazy load the attachments for performance
		if ( $this->_attachments === false ) {
			$this->_attachments = array();

			foreach ( explode(',', $this->attachments_meta) as $att_id) {
				$this->_attachments[] = $att_id;
			}
		}

		return $this->_attachments;
    }

    public function shortcode() {
        return '[' . FOOGALLERY_CPT_GALLERY . ' id="' . $this->ID . '"]';
    }

}