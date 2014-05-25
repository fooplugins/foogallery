<?php
/**
 * Foo Metabox Sanity allows plugin authors to whitelist metaboxes for your custom post types
 * Original code and idea by Thomas Griffin - https://github.com/thomasgriffin/metabox-sanity
 *
 * @package   Foo_Plugin_Base
 * @version   1.0.0
 * @author    Brad Vincent
 * @copyright Copyright (c) 2014, Brad Vincent
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      https://github.com/fooplugins/Foo_Plugin_Base
 */

/*
    Copyright 2014 Brad Vincent (fooplugins.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'Foo_Plugin_Metabox_Sanity_v1' ) ) {
	/**
	 * Provides an intuitive way for plugins to whitelist
	 * metaboxes on their custom post type interfaces.
	 *
	 * @since   2.1.0
	 *
	 * @package Foo_Plugin_Base
	 * @author  Brad Vincent
	 */
	class Foo_Plugin_Metabox_Sanity_v1 {

		protected $plugin_slug;

		public function __construct($plugin_slug) {
			$this->plugin_slug = $plugin_slug;

			add_action( 'add_meta_boxes', array($this, 'maintain_sanity'), 999 );
		}

		/**
		 * TODO
		 */
		function maintain_sanity() {

			$metabox_sanity_config = apply_filters( $this->plugin_slug . '_metabox_sanity', false );

			// If there is no data, return.
			if ( empty($metabox_sanity_config) ) {
				return;
			}

			// Bring the global metaboxes array into scope.
			global $wp_meta_boxes;

			// Loop through each post type and start whitelisting metaboxes!
			foreach ( $metabox_sanity_config as $post_type => $data ) {
				// If no contexts or priorities have been specified, do nothing.
				if ( empty($data['contexts']) || empty($data['priorities']) ) {
					continue;
				}

				// Now loop through each context that has been assigned to the post type.
				foreach ( (array) $data['contexts'] as $context ) {
					// Now loop through each priority within the context.
					foreach ( (array) $data['priorities'] as $priority ) {
						if ( isset($wp_meta_boxes[$post_type][$context][$priority]) ) {
							// Loop through each priority and check the data to determine whether the metabox will stay or go.
							foreach ( (array) $wp_meta_boxes[$post_type][$context][$priority] as $id => $metabox_data ) {
								// If the metabox ID matches one to pass over, whitelist it and allow it to be registered.
								if ( in_array( $id, (array) $data['whitelist'] ) ) {
									unset($data['whitelist'][$id]);
									continue;
								}

								// Otherwise, loop over the IDs to skip and see if there is a relevant match to whitelist.
								foreach ( (array) $data['whitelist'] as $skip ) {
									if ( preg_match( '#^' . $id . '#i', $skip ) ) {
										continue;
									}
								}

								// The metabox is not allowed on this screen. Prevent it from being registered.
								unset($wp_meta_boxes[$post_type][$context][$priority][$id]);
							}
						}
					}
				}
			}
		}
	}
}