<?php

if ( !class_exists( 'FooGallery_Videoslider_Gallery_Template' ) ) {

	class FooGallery_Videoslider_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 99 );

			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );

			//change the version of the included assets to be foovideo version, not foogallery
			add_filter( 'foogallery_template_js_ver-videoslider', array( $this, 'change_version' ), 10, 2 );
			add_filter( 'foogallery_template_css_ver-videoslider', array( $this, 'change_version' ), 10, 2 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-videoslider', array( $this, 'preview_arguments' ), 10, 2 );
		}

		/**
		 * Add the video gallery template to the list of templates available
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {

			$gallery_templates[] = array(
				'slug'        => 'videoslider',
				'name'        => __( 'Video Slider', 'foo-video'),
				'admin_js'	  => plugin_dir_url( __FILE__ ) . 'js/admin-gallery-videoslider.js',
				'preview_support' => true,
				'mandatory_classes' => 'rvs-container rvs-hide-credits',
				'fields'	  => array(
					array(
						'id'      => 'layout',
						'title'   => __('Layout', 'foo-video'),
						'desc'    => __( 'You can choose either a horizontal or vertical layout for your responsive video gallery.', 'foo-video' ),
						'type'    => 'icon',
						'default' => 'rvs-vertical',
						'choices' => array(
							'rvs-vertical' => array( 'label' => __( 'Vertical' , 'foo-video' ), 'img' => plugin_dir_url( __FILE__ ) . 'assets/video-layout-vertical.png' ),
							'rvs-horizontal' => array( 'label' => __( 'Horizontal' , 'foo-video' ), 'img' => plugin_dir_url( __FILE__ ) . 'assets/video-layout-horizontal.png' )
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview' => 'shortcode',
						)
					),
					array(
						'id'      => 'lightbox',
						'title'   => __( 'Lightbox', 'foogallery' ),
						'desc'    => __( 'Choose which lightbox you want to use. The lightbox will only work if you set the thumbnail link to "Full Size Image".', 'foogallery' ),
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'lightbox',
					),
					array(
						'id'      => 'viewport',
						'title'   => __('Use Viewport Width', 'foo-video'),
						'desc'    => __('Use the viewport width instead of the parent element width.', 'foo-video'),
						'default' => '',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'No', 'foo-video' ),
							'rvs-use-viewport' => __( 'Yes', 'foo-video' )
						)
					),
					array(
						'id'      => 'theme',
						'title'   => __('Theme', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'default' => '',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'Dark', 'foo-video' ),
							'rvs-light' => __( 'Light', 'foo-video' ),
							'rvs-custom' => __( 'Custom', 'foo-video' )
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview' => 'class',
						)
					),
					array(
						'id'      => 'theme_custom_bgcolor',
						'title'   => __('Background Color', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#000000',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-hidden'          		   => true,
							'data-foogallery-show-when-field'          => 'theme',
							'data-foogallery-show-when-field-value'    => 'rvs-custom',
						)
					),
					array(
						'id'      => 'theme_custom_textcolor',
						'title'   => __('Text Color', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#ffffff',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-hidden'          		   => true,
							'data-foogallery-show-when-field'          => 'theme',
							'data-foogallery-show-when-field-value'    => 'rvs-custom',
						)
					),
					array(
						'id'      => 'theme_custom_hovercolor',
						'title'   => __('Hover BG Color', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#222222',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-hidden'          		   => true,
							'data-foogallery-show-when-field'          => 'theme',
							'data-foogallery-show-when-field-value'    => 'rvs-custom',
						)
					),
					array(
						'id'      => 'theme_custom_dividercolor',
						'title'   => __('Divider Color', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#2e2e2e',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-hidden'          		   => true,
							'data-foogallery-show-when-field'          => 'theme',
							'data-foogallery-show-when-field-value'    => 'rvs-custom',
						)
					),
					array(
						'id'      => 'highlight',
						'title'   => __('Highlight', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'desc'    => __('The color that is used to highlight the selected video.', 'foo-video'),
						'default' => '',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'Purple', 'foo-video' ),
							'rvs-blue-highlight' => __( 'Blue', 'foo-video' ),
							'rvs-green-highlight' => __( 'Green', 'foo-video' ),
							'rvs-orange-highlight' => __( 'Orange', 'foo-video' ),
							'rvs-red-highlight' => __( 'Red', 'foo-video' ),
							'rvs-custom-highlight' => __( 'Custom', 'foo-video' )
						),
						'row_data' => array(
							'data-foogallery-change-selector' => 'input:radio',
							'data-foogallery-value-selector'  => 'input:checked',
							'data-foogallery-preview' => 'class',
						)
					),
					array(
						'id'      => 'highlight_custom_bgcolor',
						'title'   => __('Highlight BG Color', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => '#7816d6',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-hidden'          		   => true,
							'data-foogallery-show-when-field'          => 'highlight',
							'data-foogallery-show-when-field-value'    => 'rvs-custom-highlight',
						)
					),
					array(
						'id'      => 'highlight_custom_textcolor',
						'title'   => __('Highlight Text Color', 'foo-video'),
						'section' => __( 'Appearance', 'foogallery' ),
						'type'    => 'colorpicker',
						'default' => 'rgba(255, 255, 255, 1)',
						'opacity' => true,
						'row_data' => array(
							'data-foogallery-hidden'          		   => true,
							'data-foogallery-show-when-field'          => 'highlight',
							'data-foogallery-show-when-field-value'    => 'rvs-custom-highlight',
						)
					)
				)
			);

			return $gallery_templates;
		}

		/**
		 * Register myself so that all associated JS and CSS files can be found and automatically included
		 * @param $extensions
		 *
		 * @return array
		 */
		function register_myself( $extensions ) {
			$extensions[] = __FILE__;
			return $extensions;
		}

		/**
		 * Change the asset enqueue version from FooGallery version to FooVideo version
		 *
		 * @param $version string
		 * @param $current_foogallery FooGallery
		 * @return string
		 */
		public function change_version( $version, $current_foogallery ) {
			return FOOVIDEO_VERSION;
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['layout'] = $post_data[FOOGALLERY_META_SETTINGS]['justified_layout'];
			return $args;
		}
	}
}