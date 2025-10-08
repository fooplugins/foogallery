<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Bricks custom element: FooGallery
 * Docs: Create your own elements (extends \Bricks\Element)
 */
class FooGallery_Bricks_Element extends \Bricks\Element {

	// Element props displayed in the Bricks panel.
	public $category     = 'fooplugins';           // We'll localize this label below
	public $name         = 'foogallery';           // Unique slug (no spaces)
	public $icon         = 'ti-layout-grid2';      // Any supported icon class
	public $css_selector = '.foogallery-bricks';   // Root selector for style controls

	public function get_label() {
		return esc_html__( 'FooGallery', 'foogallery' );
	}

	public function get_keywords() {
		return [ 'gallery', 'image', 'foogallery', 'lightbox' ];
	}

	/**
	 * Group controls into Content and Style tabs.
	 * Ref: set_control_groups & set_controls API.
	 */
	public function set_control_groups() {
		$this->control_groups['content'] = [
			'title' => esc_html__( 'Content', 'foogallery' ),
			'tab'   => 'content',
		];

		$this->control_groups['links'] = [
			'title' => esc_html__( 'Quick Actions', 'foogallery' ),
			'tab'   => 'content',
		];

		$this->control_groups['style'] = [
			'title' => esc_html__( 'Style', 'foogallery' ),
			'tab'   => 'style',
		];
	}

	public function set_controls() {

		// Build select options from existing FooGalleries.
		$gallery_options = [ '' => esc_html__( '— Select a gallery —', 'foogallery' ) ];
		if ( function_exists( 'foogallery_get_all_galleries' ) ) {
			$galleries = foogallery_get_all_galleries(); // your internal helper
			foreach ( (array) $galleries as $g ) {
				$label = $g->name ?: sprintf( __( 'Gallery #%d', 'foogallery' ), $g->ID );
				$gallery_options[ (string) $g->ID ] = $label;
			}
		}

		// 1) Choose gallery
		$this->controls['gallery_id'] = [
			'tab'    => 'content',
			'group'  => 'content',
			'label'  => esc_html__( 'Gallery', 'foogallery' ),
			'type'   => 'select',             // Bricks control types list.
			'options'=> $gallery_options,
		];

		$this->controls['gallery_edit'] = [
			'tab'      => 'content',
			'group'    => 'content',
			'type'     => 'button',
			'label'    => esc_html__( 'Edit Gallery', 'foogallery' ),
			'url'      => '#',
			'text'     => esc_html__( 'Edit Gallery', 'foogallery' ),
			'target'   => '_blank',
		];

		$admin_new = function_exists( 'foogallery_admin_add_gallery_url' ) ? foogallery_admin_add_gallery_url() : admin_url( 'post-new.php?post_type=foogallery' );
		$this->controls['gallery_add'] = [
			'tab'      => 'content',
			'group'    => 'content',
			'type'     => 'button',
			'label'    => esc_html__( 'Add New Gallery', 'foogallery' ),
			'url'      => esc_url( $admin_new ),
			'text'     => esc_html__( 'Add New Gallery', 'foogallery' ),
			'target'   => '_blank',
		];

	}

	/**
	 * Load any needed assets only when used.
	 * Ref: enqueue_scripts guidance.
	 */
	public function enqueue_scripts() {
		// Usually FooGallery handles its own enqueue;
		// keep empty unless you have integration-specific JS/CSS.
	}

	/**
	 * Render element output (both frontend & builder canvas).
	 * Ref: render(), set_attribute(), render_attributes().
	 */
	public function render() {
		// Always set a root wrapper so Bricks attaches IDs/classes properly.
		$this->set_attribute( '_root', 'class', [ 'foogallery-bricks' ] );

		$gallery_id = isset( $this->settings['gallery_id'] ) ? absint( $this->settings['gallery_id'] ) : 0;

		if ( $gallery_id > 0 && function_exists( 'foogallery_render_gallery' ) ) {
			// Simple approach like Elementor - just render the gallery
			// CSS will be automatically loaded by FooGallery's template system
			foogallery_render_gallery( $gallery_id );
		} else if ( is_admin() ) {
			// Helpful placeholder in builder
			echo '<p>' . esc_html__( 'Please select a gallery to display.', 'foogallery' ) . '</p>';
		}
	}

}
