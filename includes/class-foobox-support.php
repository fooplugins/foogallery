<?php
/**
 * Adds in better support for FooBox Free and PRO
 */

if ( !class_exists( 'FooGallery_FooBox_Support' ) ) {

	class FooGallery_FooBox_Support {

		function __construct() {
			//we need to make sure outdated versions of FooBox never run in the future
			$this->ensure_outdated_foobox_extensions_never_run();

			//add the FooBox lightbox option no matter if using Free or Pro
			add_filter( 'foogallery_gallery_template_field_lightboxes', array($this, 'add_lightbox') );

			//alter the default lightbox to be foobox
			add_filter( 'foogallery_alter_gallery_template_field', array( $this, 'make_foobox_default_lightbox' ), 10, 2 );

            //allow changing of field values
            add_filter( 'foogallery_render_gallery_template_field_value', array( $this, 'check_lightbox_value' ), 10, 4 );

            if ( class_exists( 'fooboxV2' ) ) {
				//FooBox PRO specific functionality

				//only add FooBox PRO functionality after FooBox version 1.2.29
				if ( defined( FOOBOX_BASE_VERSION ) && version_compare( FOOBOX_BASE_VERSION, '1.2.29', '>' ) ) {
					add_filter( 'foogallery_attachment_custom_fields', array($this, 'add_panning_fields' ) );
					add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_panning_attributes' ), 10, 3 );
				}

			} else {
				//FooBox Free specific functionality
				add_filter( 'foogallery_album_stack_link_class_name', array($this, 'album_stack_link_class_name'));
			}
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
		    return class_exists( 'FooBox' ) || class_exists( 'fooboxV2' );
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