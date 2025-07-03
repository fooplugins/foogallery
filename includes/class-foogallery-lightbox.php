<?php
	/**
	 * FooGallery Lightbox class
	 *
	 * @package FooGallery
	 */

if ( ! class_exists( 'FooGallery_Lightbox' ) ) {

	/**
	 * FooGallery Lightbox class
	 */
	class FooGallery_Lightbox {
		/**
		 * Constructor method.
		 * Initializes the FooGallery Lightbox class and adds necessary filters.
		 */
		public function __construct() {
			// add lightbox custom fields.
			add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'lightbox_custom_fields' ), 10, 2 );

			// add the data options needed for lightbox.
			add_filter( 'foogallery_build_container_data_options', array( $this, 'add_data_options' ), 10, 3 );

			// set the settings icon for lightbox.
			add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

			// add the FooGallery lightbox option.
			add_filter( 'foogallery_gallery_template_field_lightboxes', array( $this, 'add_lightbox' ) );

			// alter the default lightbox to be FooGallery Lightbox.
			add_filter( 'foogallery_alter_gallery_template_field', array( $this, 'make_foogallery_default_lightbox' ), 99, 2 );

			// add specific lightbox data attribute to the container div.
			add_filter( 'foogallery_build_container_attributes', array( $this, 'add_lightbox_data_attributes' ), 10, 2 );

			// remove PRO lightbox option from albums.
			add_filter( 'foogallery_alter_gallery_template_field', array( $this, 'alter_gallery_template_field' ), 999, 2 );

			// cater for different captions sources.
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_caption_attributes' ), 10, 3 );
		}

		/**
		 * Handle custom captions for the lightbox
		 *
		 * @param array  $attr               The HTML attributes for the attachment link.
		 * @param array  $args               An array of arguments.
		 * @param object $foogallery_attachment The FooGallery attachment object.
		 *
		 * @return array                    The modified HTML attributes.
		 */
		public function add_caption_attributes( $attr, $args, $foogallery_attachment ) {
			global $current_foogallery;

			if ( ! property_exists( $current_foogallery, 'lightbox' ) ) {
				// TODO : rather use foogallery_current_gallery_check_template_has_supported_feature.
				$template = foogallery_get_gallery_template( $current_foogallery->gallery_template );
				$lightbox = foogallery_gallery_template_setting_lightbox();
				if ( $template && isset( $template['panel_support'] ) && $template['panel_support'] ) {
					$lightbox = 'foogallery';
				}
				$current_foogallery->lightbox = $lightbox;
			}

			// check if lightbox set to foogallery.
			if ( 'foogallery' === $current_foogallery->lightbox ) {

				// check lightbox caption source.
				$source = foogallery_gallery_template_setting( 'lightbox_caption_override', '' );

				if ( 'override' === $source ) {
					$caption_title_source = foogallery_gallery_template_setting( 'lightbox_caption_override_title', '' );
					if ( '' === $caption_title_source ) {
						if ( array_key_exists( 'data-caption-title', $attr ) ) {
							$attr['data-lightbox-title'] = $attr['data-caption-title'];
						}
					} else if ( 'none' === $caption_title_source ) {
						$attr['data-lightbox-title'] = '';
					} else {
						$attr['data-lightbox-title'] = foogallery_get_caption_by_source( $foogallery_attachment, $caption_title_source, 'title' );
					}

					$caption_desc_source = foogallery_gallery_template_setting( 'lightbox_caption_override_desc', '' );
					if ( '' === $caption_desc_source ) {
						if ( array_key_exists( 'data-caption-desc', $attr ) ) {
							$attr['data-lightbox-description'] = $attr['data-caption-desc'];
						}
					} else if ( 'none' === $caption_desc_source ) {
						$attr['data-lightbox-description'] = '';
					} else {
						$attr['data-lightbox-description'] = foogallery_get_caption_by_source( $foogallery_attachment, $caption_desc_source, 'description' );
					}
				} else if ( 'custom' === $source ) {

					$template = foogallery_gallery_template_setting( 'lightbox_caption_custom_template', '' );
					if ( ! empty( $template ) ) {
						$attr['data-lightbox-description'] = FooGallery_Pro_Advanced_Captions::build_custom_caption( $template, $foogallery_attachment );
					}
				} else if ( '' === $source ) {
					// if same as thumbnails, then check if custom captions was set.
					if ( isset( $foogallery_attachment->custom_captions ) && $foogallery_attachment->custom_captions ) {
						$attr['data-lightbox-title'] = '';
						$attr['data-lightbox-description'] = $foogallery_attachment->caption_desc;
					}
				}
			}

			// Make sure the captions are sanitized!!
			if ( isset( $attr['data-lightbox-title'] ) ) {
				$attr['data-lightbox-title'] = foogallery_sanitize_full( $attr['data-lightbox-title'] );
			}

			if ( isset( $attr['data-lightbox-description'] ) ) {
				$attr['data-lightbox-description'] = foogallery_sanitize_full( $attr['data-lightbox-description'] );
			}

			return $attr;
		}

		/**
		 * Add fields to all galleries.
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return mixed
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 */
		public function lightbox_custom_fields( $fields, $template ) {
			$use_lightbox = true;
			if ( $template && array_key_exists( 'panel_support', $template ) && true === $template['panel_support'] ) {
				$use_lightbox = false;
			}

			$section = $use_lightbox ? __( 'Lightbox', 'foogallery' ) : __( 'Panel', 'foogallery' );

            if ( $use_lightbox ) {
                if ( foogallery_admin_fields_has_field( $fields, 'thumbnail_link' ) &&
                    foogallery_admin_fields_has_field( $fields, 'lightbox' ) &&
                    !foogallery_admin_fields_has_field( $fields, 'lightbox_warning' ) ) {

                    $warning_field = array(
                        array(
                            'id' => 'lightbox_warning',
                            'title' => __('Your Lightbox Will Not Work!', 'foogallery'),
                            'desc' => __('No lightbox will be shown, because under the General tab, you have set the Thumbnail Link to "Not linked".', 'foogallery'),
                            'section' => __( 'Lightbox', 'foogallery' ),
                            'subsection' => array('lightbox-general' => __('General', 'foogallery')),
                            'type' => 'help',
                            'row_data' => array(
                                'data-foogallery-hidden' => true,
                                'data-foogallery-show-when-field' => 'thumbnail_link',
                                'data-foogallery-show-when-field-operator' => '===',
                                'data-foogallery-show-when-field-value' => 'none',
                            ),
                        )
                    );

                    $index = foogallery_admin_fields_find_index_of_field( $fields, 'lightbox' );

                    array_splice( $fields, $index, 0, $warning_field );
                }

                $field[] = array(
                    'id' => 'lightbox_promo',
                    'title' => __('Your Gallery Needs A Lightbox!', 'foogallery'),
                    'desc' => __('Website visitors prefer a gallery with a lightbox. A lightbox allows you to showcase your images, as well as improve navigation between images in your gallery.', 'foogallery'),
                    'section' => $section,
                    'subsection' => array('lightbox-general' => __('General', 'foogallery')),
                    'type' => 'help',
                    'row_data' => array(
                        'data-foogallery-hidden' => true,
                        'data-foogallery-show-when-field' => 'lightbox',
                        'data-foogallery-show-when-field-operator' => '===',
                        'data-foogallery-show-when-field-value' => 'none',
                    ),
                );
            }

			$field[] = array(
				'id'         => 'lightbox_theme',
				'title'      => __( 'Theme', 'foogallery' ),
				'desc'       => __( 'The overall appearance including background and button color. By default it will inherit from Appearance -> Theme', 'foogallery' ),
				'section'    => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'spacer'     => '<span class="spacer"></span>',
				'type'       => 'radio',
				'default'    => '',
				'choices'    => apply_filters(
					'foogallery_gallery_template_lightbox_theme_choices',
					array(
						''          => __( 'Inherit', 'foogallery' ),
						'fg-light'  => __( 'Light', 'foogallery' ),
						'fg-dark'   => __( 'Dark', 'foogallery' ),
						'fg-custom' => __( 'Custom', 'foogallery' ),
					)
				),
				'row_data'   => array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field' => 'lightbox',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value' => 'foogallery',
				)
			);

			if ( $use_lightbox ) {
				$field[] = array(
					'id'      => 'lightbox_help_controls',
                    'title'      => __( 'Lightbox Control Settings', 'foogallery' ),
					'desc'    => __( 'The Lightbox Controls are the action buttons that are shown within the lightbox, e.g. the Close button or the Navigation buttons', 'foogallery' ),
					'section' => $section,
					'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
					'type'    => 'help',
					'row_data'   => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'lightbox',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'foogallery',
					),
				);

                $field[] = array(
                    'id'      => 'lightbox_help_controls_2',
                    'desc'    => __( 'The Lightbox Controls settings are only available when your lightbox is set to "FooGallery Lightbox"', 'foogallery' ),
                    'section' => $section,
                    'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
                    'type'    => 'help',
                    'row_data'   => array(
                        'data-foogallery-hidden'                   => true,
                        'data-foogallery-show-when-field'          => 'lightbox',
                        'data-foogallery-show-when-field-operator' => '!==',
                        'data-foogallery-show-when-field-value'    => 'foogallery',
                    ),
                );
			}

			$field[] = array(
				'id'      => 'lightbox_button_theme',
				'title'   => __( 'Control Color', 'foogallery' ),
				'desc'    => __( 'You can override the button controls color. By default it will inherit from the theme.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_button_theme_choices', array(
					''                    => __( 'Same As Theme', 'foogallery' ),
					'custom' => __( 'Custom Color',   'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
					'data-foogallery-hidden' => true,
					'data-foogallery-show-when-field'          => 'lightbox',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				),
			);

			$field[] = array(
				'id'      => 'lightbox_custom_button_theme',
				'title'   => __( 'Custom Control Color', 'foogallery' ),
				'desc'    => __( 'You can override the button controls color by selecting a color.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'htmlicon',
				'default' => 'fg-button-light',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_custom_button_theme_choices', array(
					'fg-button-light'   => array( 'label' => __( 'Light',   'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-light"></div>' ),
					'fg-button-dark'    => array( 'label' => __( 'Dark',    'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-dark"></div>' ),
					'fg-button-blue'    => array( 'label' => __( 'Blue',    'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-blue"></div>' ),
					'fg-button-purple'  => array( 'label' => __( 'Purple',  'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-purple"></div>' ),
					'fg-button-green'   => array( 'label' => __( 'Green',   'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-green"></div>' ),
					'fg-button-red'     => array( 'label' => __( 'Red',     'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-red"></div>' ),
					'fg-button-orange'  => array( 'label' => __( 'Orange',  'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-orange"></div>' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_button_theme',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'custom',
				),
			);

			$field[] = array(
				'id'      => 'lightbox_button_highlight',
				'title'   => __( 'Control Hover Color', 'foogallery' ),
				'desc'    => __( 'You can override the button controls hover color. By default it will inherit from the theme.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_button_highlight_choices', array(
                    ''       => __( 'Same As Theme', 'foogallery' ),
                    'custom' => __( 'Custom Color', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
                    'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				),
			);

			$field[] = array(
				'id'      => 'lightbox_custom_button_highlight',
				'title'   => __( 'Custom Control Hover Color', 'foogallery' ),
				'desc'    => __( 'You can override the button controls hover color by selecting a color.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'htmlicon',
				'default' => 'fg-highlight-light',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_custom_button_highlight_choices', array(
					'fg-highlight-light'   => array( 'label' => __( 'Light',   'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-light"></div>' ),
					'fg-highlight-dark'    => array( 'label' => __( 'Dark',    'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-dark"></div>' ),
					'fg-highlight-blue'    => array( 'label' => __( 'Blue',    'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-blue"></div>' ),
					'fg-highlight-purple'  => array( 'label' => __( 'Purple',  'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-purple"></div>' ),
					'fg-highlight-green'   => array( 'label' => __( 'Green',   'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-green"></div>' ),
					'fg-highlight-red'     => array( 'label' => __( 'Red',     'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-red"></div>' ),
					'fg-highlight-orange'  => array( 'label' => __( 'Orange',  'foogallery' ), 'html' => '<div class="foogallery-setting-panel_theme fg-orange"></div>' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_button_highlight',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'custom',
				)
			);

            $hide_thumbs_by_default = false;
            //only hide the thumbs by default if we are using the Grid PRO template
            if ( $template && array_key_exists('slug', $template) && $template['slug'] === 'foogridpro' ) {
                $hide_thumbs_by_default = true;
            }

			if ( $use_lightbox ) {
				$field[] = array(
					'id'      => 'lightbox_help_thumbnails',
					'title'   => __( 'Thumbnail Strip Settings', 'foogallery' ),
					'desc'    => __( 'The below settings will control the thumbnail strip that is shown within the lightbox.', 'foogallery' ),
					'section' => $section,
					'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
					'type'    => 'help',
					'row_data' => array(
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'lightbox',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'foogallery',
					),
				);

                $field[] = array(
                    'id'      => 'lightbox_help_thumbnails_2',
                    'desc'    => __( 'The Lightbox Thumbnails settings are only available when your lightbox is set to "FooGallery Lightbox"', 'foogallery' ),
                    'section' => $section,
                    'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
                    'type'    => 'help',
                    'row_data'   => array(
                        'data-foogallery-hidden'                   => true,
                        'data-foogallery-show-when-field'          => 'lightbox',
                        'data-foogallery-show-when-field-operator' => '!==',
                        'data-foogallery-show-when-field-value'    => 'foogallery',
                    ),
                );
			}

			$field[] = array(
				'id'       => 'lightbox_thumbs',
				'title'    => __( 'Thumbnail Strip', 'foogallery' ),
				'desc'     => __( 'You can change the position of the thumbnails, or hide them completely.', 'foogallery' ),
				'section'  => $section,
				'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'default'  => $hide_thumbs_by_default ? 'none' : 'bottom',
				'choices'  => apply_filters( 'foogallery_gallery_template_lightbox_thumbs_choices', array(
					'bottom' => __( 'Bottom', 'foogallery' ),
					'top'    => __( 'Top', 'foogallery' ),
					'left'   => __( 'Left', 'foogallery' ),
					'right'  => __( 'Right', 'foogallery' ),
					'none'  => __( 'Hidden', 'foogallery' ),
				) ),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_thumbs_captions',
				'title'   => __( 'Thumbnail Strip Captions', 'foogallery' ),
				'desc'    => __( 'Whether or not the thumbnail strip should contain captions.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'no',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_thumbs_captions_choices', array(
					'yes' => __( 'Show Captions', 'foogallery' ),
					'no'  => __( 'No Captions', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_thumbs',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'none',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'       => 'lightbox_thumbs_captions_alignment',
				'title'    => __( 'Thumbnail Caption Alignment', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
				'type'     => 'radio',
				'spacer'   => '<span class="spacer"></span>',
				'default'  => 'default',
				'choices'  => array(
					'default' => __( 'Default', 'foogallery' ),
					'left'    => __( 'Left', 'foogallery' ),
					'center'  => __( 'Center', 'foogallery' ),
					'right'   => __( 'Right', 'foogallery' ),
					'justify' => __( 'Justify', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_thumbs',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'none',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_thumbs_bestfit',
				'title'   => __( 'Thumbnails Best Fit', 'foogallery' ),
				'desc'    => __( 'Adjust the size of the displayed thumbnails so that they fill the entire space within the strip.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_thumbs_bestfit_choices', array(
					''  => __( 'Default', 'foogallery' ),
					'yes' => __( 'Best Fit', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_thumbs',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'none',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_thumbs_size',
				'title'   => __( 'Thumbnail Size', 'foogallery' ),
				'desc'    => __( 'Adjust the size of the thumbnail image to display as either small (square) or large (landscape).', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-thumbnails' => __( 'Thumbnails', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_thumbs_size_choices', array(
					''  => __( 'Normal', 'foogallery' ),
					'small' => __( 'Small (square)', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_thumbs',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'none',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_transition',
				'title'   => __( 'Transition', 'foogallery' ),
				'desc'    => __( 'The transition to apply to the main content area when switching between items.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'fade',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_button_highlight_choices', array(
					'fade'       => __( 'Fade', 'foogallery' ),
					'horizontal' => __( 'Horizontal', 'foogallery' ),
					'vertical'   => __( 'Vertical', 'foogallery' ),
					'none'       => __( 'None', 'foogallery' )
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-preview' => 'shortcode',
					'data-foogallery-value-selector' => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_info_enabled',
				'title'   => __( 'Captions Enabled', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_info_enabled_choices', array(
					'' => __( 'Enabled', 'foogallery' ),
					'hidden'    => __( 'Enabled (but hidden initially)', 'foogallery' ),
					'disabled'   => __( 'Disabled', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_info_position',
				'title'   => __( 'Caption Position', 'foogallery' ),
				'desc'    => __( 'The position of the captions within the lightbox.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'bottom',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_info_position_choices', array(
					'bottom' => __( 'Bottom', 'foogallery' ),
					'top'    => __( 'Top', 'foogallery' ),
					'left'   => __( 'Left', 'foogallery' ),
					'right'  => __( 'Right', 'foogallery' ),
					//'none'  => __( 'Hidden', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_info_enabled',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'disabled',
				)
			);

			$field[] = array(
				'id'       => 'lightbox_info_alignment',
				'title'    => __( 'Caption Text Alignment', 'foogallery' ),
				'desc'     => __( 'Change the horizontal text alignment of the captions', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'type'     => 'radio',
				'spacer'   => '<span class="spacer"></span>',
				'default'  => 'default',
				'choices'  => array(
					'default' => __( 'Default', 'foogallery' ),
					'left'    => __( 'Left', 'foogallery' ),
					'center'  => __( 'Center', 'foogallery' ),
					'right'   => __( 'Right', 'foogallery' ),
					'justify' => __( 'Justify', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_info_enabled',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'disabled',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_info_overlay',
				'title'   => __( 'Caption Display', 'foogallery' ),
				'desc'    => __( 'Whether or not the caption is overlaid on top of the content, or is inline (outside of the content).', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'yes',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_info_choices', array(
					'yes' => __( 'Overlaid', 'foogallery' ),
					'no'  => __( 'Inline', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_info_enabled',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'disabled',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_caption_override',
				'title'   => __( 'Caption Source', 'foogallery' ),
				'desc'    => __( 'The captions can be different to the thumbnail captions.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_caption_override_choices', array(
					'' => __( 'Same As Thumbnail', 'foogallery' ),
					'override'  => __( 'Override', 'foogallery' ),
					'custom'  => __( 'Custom', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_info_enabled',
					'data-foogallery-show-when-field-operator' => '!==',
					'data-foogallery-show-when-field-value'    => 'disabled',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_caption_override_title',
				'title'   => __( 'Override Caption Title', 'foogallery' ),
				'desc'    => __( 'You can override the caption title to be different from the thumbnail caption title.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_caption_title_choices', array(
					'' => __( 'Same As Thumbnail', 'foogallery' ),
					'title'  => __( 'Attachment Title', 'foogallery' ),
					'caption'  => __( 'Attachment Caption', 'foogallery' ),
					'alt'  => __( 'Attachment Alt', 'foogallery' ),
					'desc'  => __( 'Attachment Description', 'foogallery' ),
					'none'  => __( 'None', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_caption_override',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'override',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_caption_override_desc',
				'title'   => __( 'Override Caption Desc.', 'foogallery' ),
				'desc'    => __( 'You can override the caption description to be different from the thumbnail caption description.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-captions' => __( 'Captions', 'foogallery' ) ),
				'type'    => 'radio',
				'default' => '',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_caption_title_choices', array(
					'' => __( 'Same As Thumbnail', 'foogallery' ),
					'title'  => __( 'Attachment Title', 'foogallery' ),
					'caption'  => __( 'Attachment Caption', 'foogallery' ),
					'alt'  => __( 'Attachment Alt', 'foogallery' ),
					'desc'  => __( 'Attachment Description', 'foogallery' ),
					'none'  => __( 'None', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_caption_override',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'override',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_auto_progress',
				'title'   => __( 'Auto Progress', 'foogallery' ),
				'desc'    => __( 'Auto progress to the next item after a specified time.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'no',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_auto_progress_choices', array(
					'yes' => __( 'Yes', 'foogallery' ),
					'no'  => __( 'No', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_auto_progress_seconds',
				'title'   => __( 'Auto Progress Seconds', 'foogallery' ),
				'desc'    => __( 'The time in seconds to display content before auto progressing to the next item.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'type'    => 'number',
				'default' => '10',
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_auto_progress',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'yes',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_auto_progress_start',
				'title'   => __( 'Auto Progress Start', 'foogallery' ),
				'desc'    => __( 'If the auto-progress will automatically start or not.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'type'    => 'radio',
				'spacer'  => '<span class="spacer"></span>',
				'choices' => array(
					'yes' => __( 'Yes', 'foogallery' ),
					'no'  => __( 'No', 'foogallery' ),
				),
				'default' => 'yes',
				'row_data'=> array(
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'lightbox_auto_progress',
					'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'yes',
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_fit_media',
				'title'   => __( 'Fit Media', 'foogallery' ),
				'desc'    => __( 'Whether or not to force images to fill the content area. Aspect ratios are maintained, the image is simply scaled so it covers the entire available area.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'no',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_fit_media_choices', array(
					'yes' => __( 'Yes', 'foogallery' ),
					'no'  => __( 'No', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_no_scrollbars',
				'title'   => __( 'Scroll Bars', 'foogallery' ),
				'desc'    => __( 'Whether or not to hide the page scrollbars when maximizing.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'no',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_no_scrollbars_choices', array(
					'yes' => __( 'Default', 'foogallery' ),
					'no'  => __( 'Hidden', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_mobile_layout',
				'title'   => __( 'Mobile Layout', 'foogallery' ),
				'desc'    => __( 'Which layout to use for the lightbox when on mobile.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-general' => __( 'General', 'foogallery' ) ),
				'type'    => 'radio',
				'default' => '',
				'choices' => array(
					'' => __( 'Mobile Optimized Layout', 'foogallery' ),
					'no'  => __( 'Same As Desktop', 'foogallery' ),
				),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_buttons_display',
				'title'   => __( 'Controls Display', 'foogallery' ),
				'desc'    => __( 'Whether or not the control buttons are overlaid on top of the content, or are inline (outside of the content).', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'no',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_buttons_display', array(
					'yes' => __( 'Overlaid', 'foogallery' ),
					'no'  => __( 'Inline', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'      => 'lightbox_hover_buttons',
				'title'   => __( 'Show Controls On Hover', 'foogallery' ),
				'desc'    => __( 'Only show the control buttons when you hover the mouse over.', 'foogallery' ),
				'section' => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'  => '<span class="spacer"></span>',
				'type'    => 'radio',
				'default' => 'no',
				'choices' => apply_filters( 'foogallery_gallery_template_lightbox_hover_buttons_choices', array(
					'yes' => __( 'Yes', 'foogallery' ),
					'no'  => __( 'No', 'foogallery' ),
				) ),
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input:radio',
					'data-foogallery-preview'                  => 'shortcode',
					'data-foogallery-value-selector'           => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			//Only show this setting for gallery templates that use the lightbox
			$field[] = array(
				'id'       => 'lightbox_show_fullscreen_button',
				'title'    => __( 'Fullscreen Button', 'foogallery' ),
				'desc'     => __( 'Whether of not to show the Fullscreen button', 'foogallery' ),
				'section'  => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'default'  => $use_lightbox ? 'yes' : 'no',
				'choices'  => array(
					'yes' => __( 'Shown', 'foogallery' ),
					'no'  => __( 'Hidden', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			//add this setting for gallery templates that use the panel, not lightbox
			if ( !$use_lightbox ) {
				$field[] = array(
					'id'       => 'lightbox_show_maximize_button',
					'title'    => __( 'Maximise Button', 'foogallery' ),
					'desc'     => __( 'Whether of not to show the Maximise button', 'foogallery' ),
					'section'  => $section,
					'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
					'spacer'   => '<span class="spacer"></span>',
					'type'     => 'radio',
					'default'  => 'yes',
					'choices'  => array(
						'yes' => __( 'Shown', 'foogallery' ),
						'no'  => __( 'Hidden', 'foogallery' ),
					),
					'row_data' => array(
						'data-foogallery-change-selector' => 'input:radio',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
						'data-foogallery-hidden' 				   => true,
						'data-foogallery-show-when-field'          => 'lightbox',
						'data-foogallery-show-when-field-operator' => '===',
						'data-foogallery-show-when-field-value'    => 'foogallery',
					)
				);
			}

			$field[] = array(
				'id'       => 'lightbox_show_caption_button',
				'title'    => __( 'Caption Button', 'foogallery' ),
				'desc'     => __( 'Whether of not to show the Caption button', 'foogallery' ),
				'section'  => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'default'  => 'yes',
				'choices'  => array(
					'yes' => __( 'Shown', 'foogallery' ),
					'no'  => __( 'Hidden', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'       => 'lightbox_show_thumbstrip_button',
				'title'    => __( 'Thumbnail Strip Button', 'foogallery' ),
				'desc'     => __( 'Whether of not to show the thumbnail strip control button', 'foogallery' ),
				'section'  => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'default'  => 'no',
				'choices'  => array(
					'yes' => __( 'Shown', 'foogallery' ),
					'no'  => __( 'Hidden', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			$field[] = array(
				'id'       => 'lightbox_show_nav_buttons',
				'title'    => __( 'Prev/Next Buttons', 'foogallery' ),
				'desc'     => __( 'Whether of not to show the navigation (prev/next) buttons', 'foogallery' ),
				'section'  => $section,
				'subsection' => array( 'lightbox-controls' => __( 'Controls', 'foogallery' ) ),
				'spacer'   => '<span class="spacer"></span>',
				'type'     => 'radio',
				'default'  => 'yes',
				'choices'  => array(
					'yes' => __( 'Shown', 'foogallery' ),
					'no'  => __( 'Hidden', 'foogallery' ),
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-preview'         => 'shortcode',
					'data-foogallery-value-selector'  => 'input:checked',
					'data-foogallery-hidden' 				   => true,
					'data-foogallery-show-when-field'          => 'lightbox',
                    'data-foogallery-show-when-field-operator' => '===',
					'data-foogallery-show-when-field-value'    => 'foogallery',
				)
			);

			//find the index of the first Hover Effect field
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Hover Effects', 'foogallery' ) );

			array_splice( $fields, $index, 0, $field );

			return $fields;
		}

		/**
		 * Add the required data options if needed
		 *
		 * @param $options
		 * @param $gallery    FooGallery
		 *
		 * @param $attributes array
		 *
		 * @return array
		 */
		function add_data_options($options, $gallery, $attributes) {
			$template = foogallery_get_gallery_template( $gallery->gallery_template );
			if ( $template && array_key_exists( 'panel_support', $template ) && true === $template['panel_support'] ) {

				$options['template'] = $this->get_options_from_settings();

			}

			return $options;
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_section_icons( $section_slug ) {
			if ( 'lightbox' === $section_slug || 'panel' === $section_slug ) {
				return 'dashicons-grid-view';
			}

			return $section_slug;
		}

		/**
		 * Add the FooGallery Lightbox
		 * @param $lightboxes
		 *
		 * @return mixed
		 */
		function add_lightbox($lightboxes) {
			$lightboxes['foogallery'] = __( 'FooGallery Lightbox', 'foogallery' );
			return $lightboxes;
		}

		/**
		 * Change the default for lightbox
		 *
		 * @param $field
		 * @param $gallery_template
		 * @return mixed
		 */
		function make_foogallery_default_lightbox( $field, $gallery_template ) {
			if (isset($field['lightbox']) && true === $field['lightbox']) {
				$field['default'] = 'foogallery';
			}

			return $field;
		}

		/**
		 * Append the needed data attributes to the container div for the lightbox settings
		 *
		 * @param $attributes
		 * @param $gallery
		 *
		 * @return array
		 */
		function add_lightbox_data_attributes( $attributes, $gallery ) {
			$template = foogallery_get_gallery_template( $gallery->gallery_template );

			//only add the lightbox data attribute for the templates where a panel is used and not a lightbox
			if ( $template && !array_key_exists( 'panel_support', $template ) ) {

				//check if lightbox set to foogallery
				if ( 'foogallery' === foogallery_gallery_template_setting( 'lightbox', '' ) ) {

					$encoded_options = foogallery_json_encode( $this->get_options_from_settings() );

                    $attributes['data-foogallery-lightbox'] = $encoded_options;
                }
			}

			return $attributes;
		}

		/**
		 * return the options for the lightbox/panel
		 *
		 * @return array
		 */
		private function get_options_from_settings() {
            global $current_foogallery_template;
			$options = array();

            $hide_thumbs_by_default = false;
            //only hide the thumbs by default if we are using the Grid PRO template
            if ( $current_foogallery_template && 'foogridpro' === $current_foogallery_template ) {
                $hide_thumbs_by_default = true;
            }

			$theme = foogallery_gallery_template_setting( 'lightbox_theme', '' );
			if ( !empty( $theme ) ) {
				$options['theme'] = $theme;
			}

			$button_theme = foogallery_gallery_template_setting( 'lightbox_button_theme', '' );
			if ( !empty( $button_theme ) && 'custom' === $button_theme ) {
				$button_theme = foogallery_gallery_template_setting( 'lightbox_custom_button_theme', 'fg-button-light' );
				$options['button'] = $button_theme;
			}

			$button_highlight = foogallery_gallery_template_setting( 'lightbox_button_highlight', '' );
			if ( !empty( $button_highlight ) && 'custom' === $button_highlight ) {
				$button_highlight = foogallery_gallery_template_setting( 'lightbox_custom_button_highlight', 'fg-highlight-light' );
				$options['highlight'] = $button_highlight;
			}

			$thumbs = foogallery_gallery_template_setting( 'lightbox_thumbs', $hide_thumbs_by_default ? 'none' : 'bottom' );
			$options['thumbs'] = $thumbs;
			if ( 'none' !== $thumbs ) {
				$options['thumbsCaptions'] = foogallery_gallery_template_setting( 'lightbox_thumbs_captions', 'no' ) === 'yes';
				$options['thumbsBestFit'] = foogallery_gallery_template_setting( 'lightbox_thumbs_bestfit', '' ) === 'yes';
				$options['thumbsSmall'] = foogallery_gallery_template_setting( 'lightbox_thumbs_size', '' ) === 'small';
				$options['thumbsCaptionsAlign'] = foogallery_gallery_template_setting( 'lightbox_thumbs_captions_alignment', 'default' );
			}

			$info_enabled = foogallery_gallery_template_setting( 'lightbox_info_enabled', '' );
			$info_position = foogallery_gallery_template_setting( 'lightbox_info_position', 'bottom' );

			//check for legacy lightbox_info_position of 'none' or new lightbox_info_enabled setting
			if ( 'none' === $info_position || 'disabled' === $info_enabled ) {
				$options['info'] = false;
			} else {
				$options['info'] = $info_position;
				$options['infoVisible'] = 'hidden' !== $info_enabled;
				$options['infoOverlay'] = foogallery_gallery_template_setting( 'lightbox_info_overlay', 'yes' ) === 'yes';
			}

			$options['infoAlign'] = foogallery_gallery_template_setting( 'lightbox_info_alignment', 'default' );
			$options['transition'] = foogallery_gallery_template_setting( 'lightbox_transition', 'fade' );

			$auto_progress = foogallery_gallery_template_setting( 'lightbox_auto_progress', 'no' ) === 'yes';
			if ( $auto_progress ) {
				$options['autoProgress'] = intval( foogallery_gallery_template_setting( 'lightbox_auto_progress_seconds', '10' ) );
				$options['autoProgressStart'] = foogallery_gallery_template_setting( 'lightbox_auto_progress_start', 'yes' ) === 'yes';
			}

			$options['hoverButtons'] = foogallery_gallery_template_setting( 'lightbox_hover_buttons', 'no' ) === 'yes';
			$options['fitMedia'] = foogallery_gallery_template_setting( 'lightbox_fit_media', 'no' ) === 'yes';
			$options['noScrollbars'] = foogallery_gallery_template_setting( 'lightbox_no_scrollbars', 'no' ) !== 'yes';
			$options['preserveButtonSpace'] = foogallery_gallery_template_setting( 'lightbox_buttons_display', 'no' ) === 'no';

			$no_mobile = foogallery_gallery_template_setting( 'lightbox_mobile_layout', '' );
			if ( $no_mobile !== '' ) {
				$options['noMobile'] = true;
			}

			$show_fullscreen_button = foogallery_gallery_template_setting( 'lightbox_show_fullscreen_button', false );
			if ( $show_fullscreen_button !== false ) {
				$options['buttons']['fullscreen'] = ($show_fullscreen_button === 'yes');
			}

			$show_maximise_button = foogallery_gallery_template_setting( 'lightbox_show_maximize_button', false );
			if ( $show_maximise_button !== false ) {
				$options['buttons']['maximize'] = ($show_maximise_button === 'yes');
			}

			$show_caption_button = foogallery_gallery_template_setting( 'lightbox_show_caption_button', false );
			if ( $show_caption_button !== false ) {
				$options['buttons']['info'] = ($show_caption_button === 'yes');
			}

			$show_thumbstrip_button = foogallery_gallery_template_setting( 'lightbox_show_thumbstrip_button', false );
			if ( $show_thumbstrip_button !== false ) {
				$options['buttons']['thumbs'] = ($show_thumbstrip_button === 'yes');
			}

			$show_nav_buttons = foogallery_gallery_template_setting( 'lightbox_show_nav_buttons', 'yes' );
			if ( $show_nav_buttons !== 'yes' ) {
				$options['buttons']['prev'] = $options['buttons']['next'] = false;
			}

			$autoplay = foogallery_gallery_template_setting( 'video_autoplay', 'yes' );
			if ( 'yes' === $autoplay ) {
				$options['video']['autoPlay'] = true;
			}

			return apply_filters( 'foogallery_lightbox_data_attributes', $options );
		}

		/**
		 * Override the lightbox field for albums only
		 *
		 * @param $field
		 * @param $object
		 */
		function alter_gallery_template_field( $field, $object ) {
			if ( is_a( $object, 'FooGalleryAlbum' ) ) {
				if ( array_key_exists( 'lightbox', $field ) ) {
					unset( $field['choices']['foogallery'] );
				}
			}

			return $field;
		}
	}
}