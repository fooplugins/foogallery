<?php
/**
 * FooGallery FooGrid Pro Extension
 *
 * A gallery with inline preview based on the Google image search results.
 *
 * @package   FooGrid_Template_FooGallery_Extension
 * @author     FooPlugins
 * @license   GPL-2.0+
 * @link      https://fooplugins.com
 * @copyright 2014  FooPlugins
 *
 * @wordpress-plugin
 * Plugin Name: FooGallery - FooGrid
 * Description: A gallery with inline preview based on Google\'s image search results.
 * Version:     1.0.0
 * Author:       FooPlugins
 * Author URI:  https://fooplugins.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !class_exists( 'FooGallery_FooGrid_Gallery_Template' ) ) {

	define('FOOGALLERY_FOOGRID_GALLERY_TEMPLATE_URL', plugin_dir_url( __FILE__ ));
	define('FOOGALLERY_FOOGRID_GALLERY_TEMPLATE_PATH', plugin_dir_path( __FILE__ ));

	class FooGallery_FooGrid_Gallery_Template {
		/**
		 * Wire up everything we need to run the extension
		 */
		function __construct() {
			add_filter( 'foogallery_gallery_templates', array( $this, 'add_template' ), 100, 1 );
			add_filter( 'foogallery_gallery_templates_files', array( $this, 'register_myself' ) );
			add_filter( 'foogallery_located_template-foogridpro', array( $this, 'enqueue_dependencies' ) );

			//get thumbnail dimensions
			add_filter( 'foogallery_template_thumbnail_dimensions-foogridpro', array( $this, 'get_thumbnail_dimensions' ), 10, 2 );

			add_filter( 'foogallery_template_load_css-foogridpro', '__return_false' );
			add_filter( 'foogallery_template_load_js-foogridpro', '__return_false' );

			//add the data options needed for polaroid
			add_filter( 'foogallery_build_container_data_options-foogridpro', array( $this, 'add_data_options' ), 10, 3 );

			//override specific settings when saving the gallery
			add_filter( 'foogallery_save_gallery_settings-foogridpro', array( $this, 'override_settings'), 10, 3 );

			//build up any preview arguments
			add_filter( 'foogallery_preview_arguments-foogridpro', array( $this, 'preview_arguments' ), 10, 2 );

			//build up the thumb dimensions from some arguments
			add_filter( 'foogallery_calculate_thumbnail_dimensions-foogridpro', array( $this, 'build_thumbnail_dimensions_from_arguments' ), 10, 2 );

			//check if the old FooGrid is installed
			if ( is_admin() ) {
                add_action( 'admin_notices', array( $this, 'display_foogrid_notice') );
            }

            //build up the arguments needed for rendering this template
            add_filter( 'foogallery_gallery_template_arguments-foogridpro', array( $this, 'build_gallery_template_arguments' ) );
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
		 * Enqueue any script or stylesheet file dependencies that your gallery template relies on
		 *
		 * @param  $gallery
		 */
		function enqueue_dependencies( $gallery ) {
            foogallery_enqueue_core_gallery_template_style();
            foogallery_enqueue_core_gallery_template_script();
		}

		/**
		 * Add our gallery template to the list of templates available for every gallery
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_template( $gallery_templates ) {

			$gallery_templates[] = array(
				'slug'        => 'foogridpro',
				'name'        => __( 'Grid PRO', 'foogallery'),
                'preview_support' => true,
                'common_fields_support' => true,
                'lazyload_support' => true,
                'paging_support' => true,
                'thumbnail_dimensions' => true,
				'mandatory_classes' => 'foogrid',
				'filtering_support' => true,
				'embed_support' => true,
				'fields'	  => array(
					array(
						'id'      => 'thumbnail_size',
						'title'   => __('Thumbnail Size', 'foogallery'),
						'desc'    => __('Choose the size of your thumbs.', 'foogallery'),
						'type'    => 'thumb_size',
						'default' => array(
							'width' => 320,
							'height' => 180,
							'crop' => true
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'desc'	  => __('You can choose to either link each thumbnail to the full size image or to the image\'s attachment page.', 'foogallery')
					),
//					array(
//						'id'      => 'theme',
//						'section' => __( 'General', 'foogallery' ),
//						'title' => __('Theme', 'foogallery'),
//						'desc' => __('The theme for the content viewer.', 'foogallery'),
//						'default' => '',
//						'type'    => 'radio',
//						'spacer'  => '<span class="spacer"></span>',
//						'choices' => array(
//							'' => __( 'Dark (Default)', 'foogallery' ),
//							'foogrid-light' => __( 'Light', 'foogallery' )
//						)
//					),
					array(
						'id'      => 'transition',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Transition', 'foogallery'),
						'desc' => __('Transition type to use switching between items, or no transitions at all.', 'foogallery'),
						'default' => 'foogrid-transition-fade',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'foogrid-transition-fade' => __( 'Fade', 'foogallery' ),
							'foogrid-transition-horizontal' => __( 'Horizontal', 'foogallery' ),
							'foogrid-transition-vertical' => __( 'Vertical', 'foogallery' ),
							'' => __( 'None', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'class'
						)
					),
					array(
						'id' => 'loop',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Loop', 'foogallery'),
						'desc' => __('Whether the slider should loop (i.e. the first slide goes to the last, the last slide goes to the first).', 'foogallery'),
						'default' => 'yes',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'yes' => __( 'Yes', 'foogallery' ),
							'no' => __( 'No', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id'      => 'columns',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Max Columns', 'foogallery'),
						'desc' => __('The maximum number of thumbnail columns to display. * This amount is automatically reduced on small screen sizes.', 'foogallery'),
						'default' => 'foogrid-cols-4',
						'type'    => 'select',
						'choices' => array(
							'foogrid-cols-2' => __( '2 Columns', 'foogallery' ),
							'foogrid-cols-3' => __( '3 Columns', 'foogallery' ),
							'foogrid-cols-4' => __( '4 Columns', 'foogallery' ),
							'foogrid-cols-5' => __( '5 Columns', 'foogallery' ),
							'foogrid-cols-6' => __( '6 Columns', 'foogallery' ),
							'foogrid-cols-7' => __( '7 Columns', 'foogallery' ),
							'foogrid-cols-8' => __( '8 Columns', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'select',
							'data-foogallery-value-selector' => 'option:selected',
							'data-foogallery-preview' => 'class'
						)
					),
					array(
						'id'      => 'captions',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Stage Caption', 'foogallery'),
						'desc' => __('The position of caption in the stage or no captions at all. * The caption will automatically switch to below the item on small screen sizes.', 'foogallery'),
						'default' => 'foogrid-caption-below',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'foogrid-caption-below' => __( 'Below', 'foogallery' ),
							'foogrid-caption-right' => __( 'Right', 'foogallery' ),
							'' => __( 'None', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'class'
						)
					),
					array(
						'id' => 'scroll',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Scroll', 'foogallery'),
						'desc' => __('Whether the page is scrolled to the selected item.', 'foogallery'),
						'default' => 'yes',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'yes' => __( 'Yes', 'foogallery' ),
							'no' => __( 'No', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id' => 'scroll_smooth',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Smooth Scroll', 'foogallery'),
						'desc' => __('Whether or not to perform a smooth scrolling animation to the selected item.', 'foogallery'),
						'default' => 'yes',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'yes' => __( 'Yes', 'foogallery' ),
							'no' => __( 'No', 'foogallery' )
						),
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input:checked',
							'data-foogallery-preview' => 'shortcode'
						)
					),
					array(
						'id' => 'scroll_offset',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Scroll Offset', 'foogallery'),
						'desc' => __('The amount to offset scrolling by. * This can be used to counter fixed headers.', 'foogallery'),
						'class'   => 'small-text',
						'type' => 'number',
						'default' => 0,
						'row_data'=> array(
							'data-foogallery-change-selector' => 'input',
							'data-foogallery-value-selector' => 'input',
							'data-foogallery-preview' => 'shortcode'
						)
					),
				)
			);

			return $gallery_templates;
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
			$loop = foogallery_gallery_template_setting( 'loop', 'yes' ) === 'yes';
			$scroll = foogallery_gallery_template_setting( 'transition', 'yes' ) === 'yes';
			$scroll_smooth = foogallery_gallery_template_setting( 'transition', 'yes' ) === 'yes';
			$scroll_offset = foogallery_gallery_template_setting( 'scroll_offset', 0 );

			$options['template']['loop'] = $loop;
			$options['template']['scroll'] = $scroll;
			$options['template']['scroll_smooth'] = $scroll_smooth;
			$options['template']['scroll_offset'] = intval( $scroll_offset );

			return $options;
		}

		/**
		 * Override specific settings so that the gallery template will always work
		 *
		 * @param $settings
		 * @param $post_id
		 * @param $form_data
		 *
		 * @return mixed
		 */
		function override_settings($settings, $post_id, $form_data) {
			return $settings;
		}

		/**
		 * Build up a arguments used in the preview of the gallery
		 * @param $args
		 * @param $post_data
		 *
		 * @return mixed
		 */
		function preview_arguments( $args, $post_data ) {
			$args['thumbnail_width'] = $post_data[FOOGALLERY_META_SETTINGS]['foogridpro_thumbnail_size']['width'];
			$args['thumbnail_height'] = $post_data[FOOGALLERY_META_SETTINGS]['foogridpro_thumbnail_size']['height'];
			$args['thumbnail_crop'] = isset( $post_data[FOOGALLERY_META_SETTINGS]['foogridpro_thumbnail_size']['crop'] ) ? '1' : '0';
			return $args;
		}

		/**
		 * Builds thumb dimensions from arguments
		 *
		 * @param array $dimensions
		 * @param array $arguments
		 *
		 * @return mixed
		 */
		function build_thumbnail_dimensions_from_arguments( $dimensions, $arguments ) {
            if ( array_key_exists( 'thumbnail_height', $arguments) ) {
                return array(
                    'height' => intval($arguments['thumbnail_height']),
                    'width' => intval($arguments['thumbnail_width']),
                    'crop' => $arguments['thumbnail_crop'] === '1'
                );
            }
            return null;
		}

		/**
		 * Get the thumb dimensions arguments saved for the gallery for this gallery template
		 *
		 * @param array $dimensions
		 * @param FooGallery $foogallery
		 *
		 * @return mixed
		 */
		function get_thumbnail_dimensions( $dimensions, $foogallery ) {
			$dimensions = $foogallery->get_meta( 'foogridpro_thumbnail_size', false );
			return $dimensions;
		}

        /**
         * Display a message if the FooGrid extension is also installed
         */
		function display_foogrid_notice() {
		    if ( class_exists('FooGrid_Template_FooGallery_Extension') ) {
                ?>
                <div class="notice error">
                    <p>
                        <strong><?php _e('FooGrid Extension Redundant!', 'foogallery'); ?></strong><br/>
                        <?php _e('You have both FooGallery PRO and the FooGrid extension activated. FooGallery PRO includes the Grid PRO gallery template, which makes the FooGrid extension redundant.', 'foogallery'); ?>
                        <br/>
                        <?php _e('Please edit all galleries that use the FooGrid gallery template and change them to use the Grid PRO gallery template. Once this is done, you can delete the FooGrid extension.', 'foogallery'); ?>
                        <br/>
                    </p>
                </div>
                <?php
            }
        }

        /**
         * Build up the arguments needed for rendering this gallery template
         *
         * @param $args
         * @return array
         */
        function build_gallery_template_arguments( $args ) {
            $args = foogallery_gallery_template_setting( 'thumbnail_size', array() );
            $args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

            return $args;
        }
	}
}