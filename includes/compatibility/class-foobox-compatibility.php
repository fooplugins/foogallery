<?php
/**
 * Adds in better support for FooBox Free and PRO
 */

if ( !class_exists( 'FooGallery_FooBox_Compatibility' ) ) {

	class FooGallery_FooBox_Compatibility {

		function __construct() {
			//we need to make sure outdated versions of FooBox never run in the future
			$this->ensure_outdated_foobox_extensions_never_run();

			//add the FooBox lightbox option no matter if using Free or Pro
			add_filter( 'foogallery_gallery_template_field_lightboxes', array($this, 'add_lightbox'), 11, 2 );

			//alter the default lightbox to be foobox
			add_filter( 'foogallery_alter_gallery_template_field', array( $this, 'make_foobox_default_lightbox' ), 10, 2 );

            //allow changing of field values
            add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'check_lightbox_value' ), 10, 4 );

            if ( class_exists( 'fooboxV2' ) ) {
				//FooBox PRO specific functionality

				//only add FooBox PRO functionality after FooBox version 1.2.29
				if ( defined( 'FOOBOX_BASE_VERSION' ) && version_compare( FOOBOX_BASE_VERSION, '1.2.29', '>' ) ) {
					add_filter( 'foogallery_attachment_custom_fields', array($this, 'add_panning_fields' ) );
					add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_panning_attributes' ), 10, 3 );
				}

			} else {
				//FooBox Free specific functionality
				add_filter( 'foogallery_album_stack_link_class_name', array($this, 'album_stack_link_class_name'));
			}

			//cater for different captions sources
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_caption_attributes' ), 20, 3 );

			//add custom captions
			add_filter( 'foogallery_build_attachment_html_caption_custom', array( &$this, 'customize_captions' ), 90, 3 );

			//add fields for FooBox free captions
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_caption_fields' ), 20, 2 );
		}

		/**
		 * Customize the captions if needed
		 *
		 * @param $captions
		 * @param $foogallery_attachment    FooGalleryAttachment
		 * @param $args array
		 *
		 * @return array
		 */
		function customize_captions( $captions, $foogallery_attachment, $args) {

			if ( isset( $foogallery_attachment->custom_captions ) && $foogallery_attachment->custom_captions ) {
				//specifically for foobox, make sure the custom captions are set
				$foogallery_attachment->caption_title = ' ';
				$foogallery_attachment->caption_desc  = $captions['desc'];
			}

			return $captions;
		}

		/**
		 * Handle custom captions for the lightbox
		 * @param $attr
		 * @param $args
		 * @param $foogallery_attachment
		 *
		 * @return mixed
		 */
		function add_caption_attributes( $attr, $args, $foogallery_attachment ) {
			global $current_foogallery;

			$force_same = false;

			//check if lightbox set to foobox
			//Note that the $current_foogallery->lightbox property is only set if FooGallery PRO is running
			if ( isset( $current_foogallery->lightbox ) && 'foobox' === $current_foogallery->lightbox ) {

				//check lightbox caption source field that is added in FooGallery PRO
				$lightbox_caption_source = foogallery_gallery_template_setting( 'lightbox_caption_override', false );

				if ( 'override' === $lightbox_caption_source ) {
					$caption_title_source = foogallery_gallery_template_setting( 'lightbox_caption_override_title', '' );
					if ( 'none' === $caption_title_source ) {
						$attr['data-caption-title'] = ' ';
					} else if ( '' !== $caption_title_source ) {
						$attr['data-caption-title'] = foogallery_sanitize_full( foogallery_get_caption_by_source( $foogallery_attachment, $caption_title_source, 'title' ) );
					}

					$caption_desc_source = foogallery_gallery_template_setting( 'lightbox_caption_override_desc', '' );
					if ( 'none' === $caption_desc_source ) {
						$attr['data-caption-desc'] = ' ';
					} else if ( '' !== $caption_desc_source ) {
						$attr['data-caption-desc'] = foogallery_sanitize_full( foogallery_get_caption_by_source( $foogallery_attachment, $caption_desc_source, 'description' ) );
					}
				} else if ( 'custom' === $lightbox_caption_source ) {

					$template = foogallery_gallery_template_setting( 'lightbox_caption_custom_template', '' );
					if ( ! empty( $template ) ) {
						$attr['data-caption-title'] = ' ';
						$attr['data-caption-desc']  = foogallery_sanitize_full( FooGallery_Pro_Advanced_Captions::build_custom_caption( $template, $foogallery_attachment ) );
					}
				} else if ( '' === $lightbox_caption_source ) {
					//same as thumbnail
					//either way, we need to force the lightbox captions to match the thumb captions
					$force_same = true;
				}

			} else {
				//we will get here if FooGallery FREE is running
				$lightbox = foogallery_gallery_template_setting_lightbox();

				//we only want to make changes if the lightbox is set to foobox
				if ( 'foobox' === $lightbox ) {
					//check foobox caption source field that is only added if FooBox free is installed
					$foobox_caption_source = foogallery_gallery_template_setting( 'foobox_caption_source', false );

					if ( 'override' === $foobox_caption_source ) {
						$caption_title_source = foogallery_gallery_template_setting( 'foobox_caption_override_title', '' );
						if ( 'none' === $caption_title_source ) {
							$attr['data-caption-title'] = ' ';
						} else if ( '' !== $caption_title_source ) {
							$attr['data-caption-title'] = foogallery_sanitize_full( foogallery_get_caption_by_source( $foogallery_attachment, $caption_title_source, 'title' ) );
						}

						$caption_desc_source = foogallery_gallery_template_setting( 'foobox_caption_override_desc', '' );
						if ( 'none' === $caption_desc_source ) {
							$attr['data-caption-desc'] = ' ';
						} else if ( '' !== $caption_desc_source ) {
							$attr['data-caption-desc'] = foogallery_sanitize_full( foogallery_get_caption_by_source( $foogallery_attachment, $caption_desc_source, 'description' ) );
						}
					} else if ( 'same' === $foobox_caption_source ) {
						//same as thumbnail, or FooGallery FREE
						$force_same = true;
					}
				}
			}

			//force the same captions as the thumbnail
			if ( $force_same ) {
				if ( isset( $foogallery_attachment->caption_title ) ) {
					$attr['data-caption-title'] = foogallery_sanitize_full( $foogallery_attachment->caption_title );
				} else {
					$attr['data-caption-title'] = ' ';
				}

				if ( isset( $foogallery_attachment->caption_desc ) ) {
					$attr['data-caption-desc'] = foogallery_sanitize_full( $foogallery_attachment->caption_desc );
				} else {
					$attr['data-caption-desc'] = ' ';
				}
			}

			return $attr;
		}

		/**
		 * Add caption fields for FooBox FREE
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return mixed
		 */
		function add_caption_fields( $fields, $template ) {
			//see if the template has a lightbox field
			$found_lightbox = false;
			foreach ( $fields as $key => &$field ) {
				if ( 'lightbox' === $field['id'] ) {
					$found_lightbox = true;
					break;
				}
			}

			if ( $found_lightbox && $this->is_foobox_installed() && !foogallery_is_pro() ) {

				$new_fields[] = array(
					'id'      => 'foobox_caption_source',
					'title'   => __( 'Lightbox Caption Source', 'foogallery' ),
					'desc'    => __( 'The lightbox captions can be different to the thumbnail captions.', 'foogallery' ),
					'section' => __( 'Lightbox', 'foogallery' ),
					'type'    => 'radio',
					'default' => '',
					'choices' => array(
						'' => __('Smart (try to show both caption titles and descriptions if available)', 'foogallery' ),
						'same' => __( 'Same As Thumbnail', 'foogallery' ),
						'override'  => __( 'Override', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden' => true,
						'data-foogallery-show-when-field' => 'lightbox',
						'data-foogallery-show-when-field-value' => 'foobox',
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-value-selector'  => 'input:checked',
					)
				);

				$new_fields[] = array(
					'id'      => 'foobox_caption_override_title',
					'title'   => __( 'Override Caption Title', 'foogallery' ),
					'desc'    => __( 'You can override the caption title to be different from the thumbnail caption title.', 'foogallery' ),
					'section' => __( 'Lightbox', 'foogallery' ),
					'type'    => 'radio',
					'default' => '',
					'choices' => array(
						'' => __( 'Same As Thumbnail', 'foogallery' ),
						'title'  => __( 'Attachment Title', 'foogallery' ),
						'caption'  => __( 'Attachment Caption', 'foogallery' ),
						'alt'  => __( 'Attachment Alt', 'foogallery' ),
						'desc'  => __( 'Attachment Description', 'foogallery' ),
						'none'  => __( 'None', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'foobox_caption_source',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'override',
						'data-foogallery-change-selector'          => 'input:radio',
						'data-foogallery-value-selector'           => 'input:checked',
					)
				);

				$new_fields[] = array(
					'id'      => 'foobox_caption_override_desc',
					'title'   => __( 'Override Caption Desc.', 'foogallery' ),
					'desc'    => __( 'You can override the caption description to be different from the thumbnail caption description.', 'foogallery' ),
					'section' => __( 'Lightbox', 'foogallery' ),
					'type'    => 'radio',
					'default' => '',
					'choices' => array(
						'' => __( 'Same As Thumbnail', 'foogallery' ),
						'title'  => __( 'Attachment Title', 'foogallery' ),
						'caption'  => __( 'Attachment Caption', 'foogallery' ),
						'alt'  => __( 'Attachment Alt', 'foogallery' ),
						'desc'  => __( 'Attachment Description', 'foogallery' ),
						'none'  => __( 'None', 'foogallery' ),
					),
					'row_data'=> array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'foobox_caption_source',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'override',
						'data-foogallery-change-selector'          => 'input:radio',
						'data-foogallery-value-selector'           => 'input:checked',
					)
				);

				//find the index of the first Hover Effect field
				$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Hover Effects', 'foogallery' ) );

				array_splice( $fields, $index, 0, $new_fields );
			}

			return $fields;
		}

        /***
         * Check if we have a lightbox value from FooBox free and change it if foobox free is no longer active
         * @param $value
         * @param $field
         * @param $gallery
         * @param $template
         *
         * @return string
         */
        function check_lightbox_value($value, $field, $gallery, $template) {

            if ( isset( $field['lightbox'] ) ) {
                if ( 'foobox-free' === $value ) {
                    if ( !class_exists( 'Foobox_Free' ) ) {
                        return 'foobox';
                    }
                }
            }

            return $value;
        }

        /**
         * Change the default for lightbox if foobox is activated
         *
         * @param $field
         * @param $gallery_template
         * @return mixed
         */
		function make_foobox_default_lightbox( $field, $gallery_template ) {
		    if ( $this->is_foobox_installed() ) {
                if (isset($field['lightbox']) && true === $field['lightbox']) {
                    $field['default'] = 'foobox';
                }
            }

		    return $field;
        }

		function is_foobox_installed() {
		    return $this->is_foobox_free_installed() || $this->is_foobox_pro_installed();
        }

		function is_foobox_free_installed() {
			return class_exists( 'FooBox' );
		}

		function is_foobox_pro_installed() {
			return class_exists( 'fooboxV2' );
		}

		function ensure_outdated_foobox_extensions_never_run() {
			global $foogallery_extensions;

			//backwards compatibility for older versions of the FooBox Free extension class
			if ( class_exists( 'FooGallery_FooBox_Free_Extension' ) ) {
				$foogallery_extensions['foobox-image-lightbox'] = $this;
			}

			//backwards compatibility for older versions of the FooBox PRO extension class
			if ( class_exists( 'FooGallery_FooBox_Extension' ) ) {
				$foogallery_extensions['foobox'] = $this;
			}
		}

		function add_lightbox($lightboxes) {
			$option_text = __( 'FooBox', 'foogallery' );
			if ( !$this->is_foobox_installed() ) {
				$option_text .= __( ' (Not installed!)', 'foogallery' );
			}

			$lightboxes['foobox'] = $option_text;
			return $lightboxes;
		}

		function album_stack_link_class_name( $class_name ) {
			return str_replace( 'foobox-free', 'foobox', $class_name );
		}

		function add_panning_fields( $fields ) {
			$fields['foobox_panning'] = array(
				'label'       =>  __( 'Panning', 'foogallery' ),
				'input'       => 'radio',
				'helps'       => __( 'Enable mouse panning for this image in the lightbox.', 'foogallery' ),
				'exclusions'  => array( 'audio', 'video' ),
				'options'     => array(
					'' => __( 'Disabled', 'foogallery' ),
					'enabled' => __( 'Enabled', 'foogallery' )
				)
			);

			return $fields;
		}

		function add_panning_attributes( $attr, $args, $foogallery_attachment ) {

			$foobox_panning = get_post_meta( $foogallery_attachment->ID, '_foobox_panning', true );

			if ( !empty( $foobox_panning ) ) {
				//add data-overflow="true" + data-proportion="false" attributes to the anchor link
				$attr['data-overflow'] = 'true';
				$attr['data-proportion'] = 'false';
			}

			return $attr;
		}
	}
}