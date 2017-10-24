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
						)
					),
					array(
						'id'      => 'thumbnail_link',
						'title'   => __('Thumbnail Link', 'foogallery'),
						'default' => 'image' ,
						'type'    => 'thumb_link',
						'desc'	  => __('You can choose to either link each thumbnail to the full size image or to the image\'s attachment page.', 'foogallery')
					),
					array(
						'id'      => 'theme',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Theme', 'foogallery'),
						'desc' => __('The theme for the content viewer.', 'foogallery'),
						'default' => '',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'' => __( 'Dark (Default)', 'foogallery' ),
							'foogrid-light' => __( 'Light', 'foogallery' )
						)
					),
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
						)
					),
					array(
						'id'      => 'captions',
						'section' => __( 'General', 'foogallery' ),
						'title'   => __('Captions', 'foogallery'),
						'desc' => __('The position of captions or no captions at all. * The caption will automatically switch to below the item on small screen sizes.', 'foogallery'),
						'default' => 'foogrid-caption-below',
						'type'    => 'radio',
						'spacer'  => '<span class="spacer"></span>',
						'choices' => array(
							'foogrid-caption-below' => __( 'Below', 'foogallery' ),
							//'foogrid-caption-right' => __( 'Right', 'foogallery' ),
							'' => __( 'None', 'foogallery' )
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
						)
					),
					array(
						'id' => 'scroll_offset',
						'section' => __( 'General', 'foogallery' ),
						'title' => __('Scroll Offset', 'foogallery'),
						'desc' => __('The amount to offset scrolling by. * This can be used to counter fixed headers.', 'foogallery'),
						'type' => 'number',
						'default' => 0
					)
				)
			);

			return $gallery_templates;
		}
	}
}