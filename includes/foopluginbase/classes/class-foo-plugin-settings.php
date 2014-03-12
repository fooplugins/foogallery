<?php
/*
 * Foo Plugin Settings
 *
 * A helpful class to handle settings for a plugin
 *
 * Version: 2.0
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

if ( !class_exists( 'Foo_Plugin_Settings_v2_0' ) ) {
	class Foo_Plugin_Settings_v2_0 {

		protected $plugin_slug;

		protected $_settings = array(); //the plugin settings array
		protected $_settings_sections = array(); //the plugin sections array
		protected $_settings_tabs = array(); //the plugin tabs array
		protected $_admin_errors = false; //store of admin errors

		function __construct($plugin_slug) {
			$this->plugin_slug = $plugin_slug;
		}

		function get_tabs() {
			return $this->_settings_tabs;
		}

		//check if we have any setting of a certain type
		function has_setting_of_type($type) {
			foreach ( $this->_settings as $setting ) {
				if ( $setting['type'] == $type ) return true;
			}

			return false;
		}

		function has_tab($tab_id) {
			return array_key_exists( $tab_id, $this->_settings_tabs );
		}

		// add a setting tab
		function add_tab($tab_id, $title) {
			if ( !$this->has_tab( $tab_id ) ) {

				//pre action
				do_action( $this->plugin_slug . '-before_settings_tab', $tab_id, $title );

				$tab = array(
					'id'    => $tab_id,
					'title' => $title
				);

				$this->_settings_tabs[$tab_id] = $tab;

				//post action
				do_action( $this->plugin_slug . '-after_settings_tab', $tab_id, $title );
			}
		}

		function has_section($section_id) {
			return array_key_exists( $section_id, $this->_settings_sections );
		}

		// add a setting section
		function add_section($section_id, $title, $desc = '') {

			//check we have the section
			if ( !$this->has_section( $section_id ) ) {

				//pre action
				do_action( $this->plugin_slug . '-before_settings_section', $section_id, $title, $desc );

				$section = array(
					'id'    => $section_id,
					'title' => $title,
					'desc'  => $desc
				);

				$this->_settings_sections[$section_id] = $section;

				$section_callback = create_function( '',
					'echo "' . $desc . '";' );

				add_settings_section( $section_id, $title, $section_callback, $this->plugin_slug );

				//post action
				do_action( $this->plugin_slug . '-after_settings_section', $section_id, $title, $desc );
			}
		}

		function add_section_to_tab($tab_id, $section_id, $title, $desc = '') {
			if ( array_key_exists( $tab_id, $this->_settings_tabs ) ) {

				//get the correct section id for the tab
				$section_id = $tab_id . '-' . $section_id;

				//add the section to the tab
				if ( !array_key_exists( $section_id, $this->_settings_sections ) ) {
					$this->_settings_tabs[$tab_id]['sections'][$section_id] = $section_id;
				}

				//add the section
				$this->add_section( $section_id, $title, $desc );

			}

			return $section_id;
		}

		function add_settings($settings = false) {
			if ( !is_array( $settings ) || ( !array_key_exists( 'settings', $settings ) ) ) return;

			foreach($settings['settings'] as $setting) {
				//add a tab if needed
				$tab_id = foo_safe_get( $setting, 'tab', false );

				if ($tab_id !== false && !$this->has_tab( $tab_id ) && array_key_exists( 'tabs', $settings ) && array_key_exists( $tab_id, $settings['tabs'] ) ) {
					$tab = $settings['tabs'][$tab_id];
					$this->add_tab( $tab_id, $tab );
				}

				//add a section if needed
				$section_id = foo_safe_get( $setting, 'section', false );
				if ($section_id !== false && !$this->has_section( $section_id ) &&  array_key_exists( 'sections', $settings ) && array_key_exists( $section_id, $settings['sections'] ) ) {
					$section = $settings['sections'][$section_id];
					$this->add_section_to_tab( $tab_id, $section_id, $section['name'] );
				}

				$this->add_setting( $setting );
			}
		}

		// add a settings field
		function add_setting($args = array()) {

			$defaults = array(
				'id'          => 'default_field',
				'title'       => 'Default Field',
				'desc'        => '',
				'default'     => '',
				'placeholder' => '',
				'type'        => 'text',
				'section'     => '',
				'choices'     => array(),
				'class'       => '',
				'tab'         => ''
			);

			//only declare up front so no debug warnings are shown
			$title = $type = $id = $desc = $default = $placeholder = $choices = $class = $section = $tab = null;

			extract( wp_parse_args( $args, $defaults ) );

			$field_args = array(
				'type'        => $type,
				'id'          => $id,
				'desc'        => $desc,
				'default'     => $default,
				'placeholder' => $placeholder,
				'choices'     => $choices,
				'label_for'   => $id,
				'class'       => $class
			);

			if ( count( $this->_settings ) == 0 ) {
				//only do this once
				register_setting( $this->plugin_slug, $this->plugin_slug, array($this, 'validate') );
			}

			$this->_settings[] = $args;

			$section_id = foo_convert_to_key( $section );

			//check we have the tab
			if ( !empty($tab) ) {
				$tab_id = foo_convert_to_key( $tab );

				//add the tab
				$this->add_tab( $tab_id, foo_title_case( $tab ) );

				//add the section
				$section_id = $this->add_section_to_tab( $tab_id, $section_id, foo_title_case( $section ) );
			} else {
				//just add the section
				$this->add_section( $section_id, foo_title_case( $section ) );
			}

			do_action( $this->plugin_slug . '-before_setting', $args );

			//add the setting!
			add_settings_field( $id, $title, array($this, 'render'), $this->plugin_slug, $section_id, $field_args );

			do_action( $this->plugin_slug . '-after_setting', $args );
		}

		// render HTML for individual settings
		function render($args = array()) {

			//only declare up front so no debug warnings are shown
			$type = $id = $desc = $default = $placeholder = $choices = $class = $section = $tab = null;

			extract( $args );

			$options = get_option($this->plugin_slug);

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				//if we are in the network settings then use site options directly.
				if (is_network_admin()) {
					$options = get_site_option($this->plugin_slug);
				} else {
					$site_options = get_site_option($this->plugin_slug);
					$options = wp_parse_args($options, $site_options);
				}
			}

			$has_options = $options !== false;

			if ( !isset($options[$id]) && $type != 'checkbox' ) {
				$options[$id] = $default;
			}

			$field_class = '';
			if ( $class != '' ) {
				$field_class = ' class="' . $class . '"';
			}

			$errors = get_settings_errors( $id );

			do_action( $this->plugin_slug . '-before_settings_render', $args );

			switch ( $type ) {

				case 'heading':
					echo '</td></tr><tr valign="top"><td colspan="2">' . $desc;
					break;

				case 'html':
					echo $desc;
					break;

				case 'checkbox':
					$checked = '';
					if (isset($options[$id]) && $options[$id] == 'on') {
						$checked = ' checked="checked"';
					} else if ($options === false && $default == 'on') {
						$checked = ' checked="checked"';
					} else if ($has_options === false && $default == 'on') {
						$checked = ' checked="checked"';
					}

					//echo '<input type="hidden" name="'.$this->plugin_slug.'[' . $id . '_default]" value="' . $default . '" />';
					echo '<input' . $field_class . ' type="checkbox" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" value="on"' . $checked . ' /> <label for="' . $id . '"><small>' . $desc . '</small></label>';

					break;

				case 'select':
					echo '<select' . $field_class . ' name="' . $this->plugin_slug . '[' . $id . ']">';

					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $options[$id] == $value ) {
							$selected = ' selected="selected"';
						}
						echo '<option ' . $selected . ' value="' . $value . '">' . $label . '</option>';
					}

					echo '</select>';

					break;

				case 'radio':
					$i           = 0;
					$saved_value = $options[$id];
					if ( empty($saved_value) ) {
						$saved_value = $default;
					}
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $saved_value == $value ) {
							$selected = ' checked="checked"';
						}
						echo '<input' . $field_class . $selected . ' type="radio" name="' . $this->plugin_slug . '[' . $id . ']" id="' . $id . $i . '" value="' . $value . '"> <label for="' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;

				case 'textarea':
					echo '<textarea' . $field_class . ' id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" placeholder="' . $placeholder . '">' . esc_attr( $options[$id] ) . '</textarea>';

					break;

				case 'password':
					echo '<input' . $field_class . ' type="password" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" />';

					break;

				case 'text':
					echo '<input class="regular-text ' . $class . '" type="text" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" placeholder="' . $placeholder . '" value="' . esc_attr( $options[$id] ) . '" />';

					break;

				case 'checkboxlist':
					$i = 0;
					foreach ( $choices as $value => $label ) {

						$checked = '';
						if ( isset($options[$id][$value]) && $options[$id][$value] == 'true' ) {
							$checked = 'checked="checked"';
						}

						echo '<input' . $field_class . ' ' . $checked . ' type="checkbox" name="' . $this->plugin_slug . '[' . $id . '|' . $value . ']" id="' . $id . $i . '" value="on"> <label for="' . $id . $i . '">' . $label . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;
				case 'image':
					echo '<input class="regular-text image-upload-url" type="text" id="' . $id . '" name="' . $this->plugin_slug . '[' . $id . ']" placeholder="' . $placeholder . '" value="' . esc_attr( $options[$id] ) . '" />';
					echo '<input data-uploader-title="' . __('Select An Image', $this->plugin_slug) . '" data-link="' . $id . '" class="image-upload-button" type="button" name="upload_button" value="' . __( 'Select Image', $this->plugin_slug ) . '" />';
					break;

				default:
					do_action( $this->plugin_slug . '-settings_custom_type_render', $args );
					break;
			}

			do_action( $this->plugin_slug . '-after_settings_render', $args );

			if ( is_array( $errors ) ) {
				foreach ( $errors as $error ) {
					echo "<span class='error'>{$error['message']}</span>";
				}
			}

			if ( $type != 'checkbox' && $type != 'heading' && $type != 'html' && $desc != '' ) {
				echo '<br /><small>' . $desc . '</small>';
			}
		}

		// validate our settings
		function validate($input) {

			//check to see if the options were reset
			if ( isset ($input['reset-defaults']) ) {
				delete_option( $this->plugin_slug );
				delete_option( $this->plugin_slug . '_valid' );
				delete_option( $this->plugin_slug . '_valid_expires' );
				add_settings_error(
					'reset',
					'reset_error',
					__( 'Settings restored to default values', $this->plugin_slug ),
					'updated'
				);

				return false;
			}

//            if (empty($input['sample_text'])) {
//
//                add_settings_error(
//                    'sample_text',           // setting title
//                    'sample_text_error',            // error ID
//                    'Please enter some sample text',   // error message
//                    'error'                        // type of message
//                );
//
//            }

			foreach ( $this->_settings as $setting ) {
				$this->validate_setting( $setting, $input );
			}

			return $input;
		}

		function validate_setting($setting, &$input) {
			//validate a single setting

			if ( $setting['type'] == 'checkboxlist' ) {

				unset($checkboxarray);

				foreach ( $setting['choices'] as $value => $label ) {
					if ( !empty($input[$setting['id'] . '|' . $value]) ) {
						// If it's not null, make sure it's true, add it to an array
						$checkboxarray[$value] = 'true';
					} else {
						$checkboxarray[$value] = 'false';
					}
				}

				if ( !empty($checkboxarray) ) {
					$input[$setting['id']] = $checkboxarray;
				}

			}
		}
	}
}