<?php
/*
 * FooGallery Pro Feature Promotion class
 */

if ( ! class_exists( 'FooGallery_Pro_Promotion' ) ) {
	class FooGallery_Pro_Promotion {

		private $urls = array(
			'foogallery-pagination'        => 'https://fooplugins.com/foogallery/gallery-pagination/',
			'foogallery-filtering'         => 'https://fooplugins.com/foogallery/wordpress-filtered-gallery/',
			'foogallery-hover-presets'     => 'https://fooplugins.com/foogallery/hover-presets/',
			'foogallery-lightbox'          => 'https://fooplugins.com/foogallery/foogallery-pro-lightbox/',
			'foogallery-thumbnail-filters' => 'https://fooplugins.com/foogallery/thumbnail-filters/',
			'foogallery-loaded-effects'    => 'https://fooplugins.com/foogallery/animated-loaded-effects/',
			'foogallery-polaroid'          => 'https://fooplugins.com/foogallery/wordpress-polaroid-gallery/',
			'foogallery-grid'              => 'https://fooplugins.com/foogallery/wordpress-grid-gallery/',
			'foogallery-slider'            => 'https://fooplugins.com/foogallery/wordpress-slider-gallery/',
			'foogallery-videos'            => 'https://fooplugins.com/foogallery/wordpress-video-gallery/',
			'foogallery-trial'             => 'https://fooplugins.com/foogallery/start-trial/',
			'foogallery-pricing'           => 'https://fooplugins.com/foogallery/#pricing',
			'foobox-pro'                   => 'https://fooplugins.com/foobox/',
		);

		function __construct() {
			add_action( 'admin_init', array( $this, 'include_promos' ) );
		}

		/**
		 * conditionally include promos
		 */
		function include_promos() {
			global $foogallery_admin_datasource_instance;

			add_filter( 'foogallery_admin_settings_override', array( $this, 'include_promo_settings' ) );

			if ( $this->can_show_promo() ) {
				//paging
				add_filter( 'foogallery_gallery_template_paging_type_choices', array( $this, 'add_promo_paging_choices' ) );
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_paging_promo_fields' ), 10, 2 );

				//presets
				add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_type_choices', array( $this, 'add_preset_type' ) );
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_preset_promo_fields' ), 99, 2 );

				//filtering
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_filtering_promo_fields' ), 10, 2 );
				add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_section_icons' ) );

				//lightbox
				add_filter( 'foogallery_gallery_template_field_lightboxes', array($this, 'add_lightbox'), 10, 2 );
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_lightbox_promo_fields' ), 10, 2 );

				//Instagram + Loaded Effects
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_appearance_promo_fields' ), 20, 2 );

				//Videos
				add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_video_promo_fields' ) );

				//PRO Templates
				add_filter( 'foogallery_gallery_templates', array( $this, 'add_promo_templates' ), 99, 1 );
				add_filter( 'foogallery_override_gallery_template_fields-polaroid_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );
				add_filter( 'foogallery_override_gallery_template_fields-grid_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );
				add_filter( 'foogallery_override_gallery_template_fields-slider_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );

				//Datasource promos
				add_action( 'foogallery_gallery_datasources', array( $this, 'add_promo_datasources' ), 99 );
				add_action( 'foogallery_gallery_metabox_items_add', array( $this, 'add_datasources_css' ), 9 );
				add_action( 'foogallery_admin_datasource_modal_content', array( $this, 'render_datasource_modal_content_default' ) );

				add_action( 'foogallery-datasource-modal-content_folders_promo', array( $this, 'render_datasource_modal_content_folders_promo' ), 10, 3 );
				add_action( 'foogallery-datasource-modal-content_media_tags_promo', array( $this, 'render_datasource_modal_content_taxonomy_promo' ), 10, 3 );
				add_action( 'foogallery-datasource-modal-content_media_categories_promo', array( $this, 'render_datasource_modal_content_taxonomy_promo' ), 10, 3 );
				add_action( 'foogallery-datasource-modal-content_lightroom_promo', array( $this, 'render_datasource_modal_content_lightroom_promo' ), 10, 3 );
				add_action( 'foogallery-datasource-modal-content_rml_promo', array( $this, 'render_datasource_modal_content_rml_promo' ), 10, 3 );
				add_action( 'foogallery-datasource-modal-content_post_query_promo', array( $this, 'render_datasource_modal_content_post_query_promo' ), 10, 3 );

				remove_action( 'foogallery_admin_datasource_modal_content', array( $foogallery_admin_datasource_instance, 'render_datasource_modal_default_content' ) );
			}
		}

		function add_datasources_css() {
			?>
			<style>
                .gallery_datasources_button {
                    color: #1d7b30 !important;
                    border-color: #1d7b30 !important;
	                background-color: #f7fff6 !important;
				}
			</style>
			<?php
		}

		/**
		 * Output the default datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_default() {
			$this->render_datasource_modal_content(
				__('Dynamic Galleries From Other Sources', 'foogallery' ),
				__('Create dynamic galleries by using other sources for your images. Load images from a directory on your server, or load all images for a specific tag, or even sync your Adobe Lightroom images and show them in your gallery.', 'foogallery' )
			);
		}

		/**
		 * Output the server folders datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_folders_promo( $foogallery_id, $datasource_value ) {
			$this->render_datasource_modal_content(
				__('Server Folder Datasource', 'foogallery' ),
				__('Create a dynamic gallery by loadings images directly from a folder/directory on your server.<br>This allows you to FTP or upload images directly to your server, and then your gallery will dynamically change to include all newly added/updated images.', 'foogallery' )
			);
		}

		/**
		 * Output the tags datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_taxonomy_promo( $foogallery_id, $datasource_value ) {
			$this->render_datasource_modal_content(
				__('Media Tags/Categories Datasource', 'foogallery' ),
				__('Create a dynamic gallery by loadings images for specific tags or categories. This means you only need to upload a new image and add the correct tag, for it to show in the gallery. You can specify different tags or categories for each image or video in your media library.', 'foogallery' )
			);
		}


		/**
		 * Output the lightroom datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_lightroom_promo( $foogallery_id, $datasource_value ) {
			$this->render_datasource_modal_content(
				__('Adobe Lightroom Datasource', 'foogallery' ),
				__('We have integrated with the WP/LR Sync plugin to enable you to create galleries from a collection within Adobe Lightroom.', 'foogallery' )
			);
		}

		/**
		 * Output the RML datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_rml_promo( $foogallery_id, $datasource_value ) {
			$this->render_datasource_modal_content(
				__('Real Media Library Datasource', 'foogallery' ),
				__('Real Media Library is a media library organization plugin. It allows you to sort your library into folders and subfolders. You will need the plugin, available on Code Canyon, in order to create galleries from these folders in your media library.', 'foogallery' )
			);
		}

		/**
		 * Output the Post Query datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_post_query_promo( $foogallery_id, $datasource_value ) {
			$this->render_datasource_modal_content(
				__('Post Query Datasource', 'foogallery' ),
				__('You can also pull a gallery from post types on your site. This includes items like your blog posts, pages or articles. Choose a post type, the number of items you want to show in your gallery, posts that you want to exclude, and whether you want the gallery to link to the featured image or the post itself.', 'foogallery' )
			);
		}

		function render_datasource_modal_content( $datasouce_title, $datasource_desc, $datasource_url = 'https://fooplugins.com/load-galleries-from-other-sources/' ) {
?>
			<div class="foogallery_template_field_type-promo">
				<div class="foogallery-promo">
					<strong><?php _e('FooGallery PRO Feature', 'foogallery'); ?> : <?php echo $datasouce_title; ?></strong>
					<br><br>
					<?php echo $datasource_desc; ?>
					<br><br>
					<?php echo $this->build_promo_trial_html( 'datasources' ); ?>
					<br><br>
					<a class="button-primary" href="<?php echo esc_url( $datasource_url ); ?>" target="_blank"><?php echo __( 'Learn More About Using Other Sources', 'foogallery' ); ?></a>
				</div>
			</div>
<?php
		}

		/**
		 * Add the promotion datasources
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		function add_promo_datasources( $datasources ) {
			$datasources['media_tags_promo'] = array(
				'id'     => 'media_tags_promo',
				'name'   => __( 'Media Tags', 'foogallery' ),
				'menu'  => __( 'Media Tags', 'foogallery' ),
				'public' => true,
			);

			$datasources['media_categories_promo'] = array(
				'id'     => 'media_categories_promo',
				'name'   => __( 'Media Categories', 'foogallery' ),
				'menu'  => __( 'Media Categories', 'foogallery' ),
				'public' => true,
			);

			$datasources['folders_promo'] = array(
				'id'     => 'folders_promo',
				'name'   => __( 'Server Folder', 'foogallery' ),
				'menu'   => __( 'Server Folder', 'foogallery' ),
				'public' => true
			);

			$datasources['lightroom_promo'] = array(
				'id'     => 'lightroom_promo',
				'name'   => __( 'Adobe Lightroom', 'foogallery' ),
				'menu'   => __( 'Adobe Lightroom', 'foogallery' ),
				'public' => true
			);

			$datasources['rml_promo'] = array(
				'id'     => 'rml_promo',
				'name'   => __( 'Real Media Library', 'foogallery' ),
				'menu'   => __( 'Real Media Library', 'foogallery' ),
				'public' => true
			);

			$datasources['post_query_promo'] = array(
				'id'     => 'post_query_promo',
				'name'   => __( 'Post Query', 'foogallery' ),
				'menu'   => __( 'Post Query', 'foogallery' ),
				'public' => true
			);

			return $datasources;
		}

		/**
		 * Builds up a URL that can be used for tracking
		 *
		 * @param $url_name
		 * @param $promotion
		 *
		 * @return string
		 */
		private function build_url( $url_name, $promotion = '') {
			if ( empty( $promotion ) ) {
				$promotion = $url_name;
			}

			$promotion = str_replace( 'foogallery-', '', $promotion );

			return foogallery_admin_url( $this->urls[ $url_name ], 'promos', $promotion );
		}

		/**
		 * Returns true if the promo areas can be shown
		 * @return bool
		 */
		private function can_show_promo() {
			return foogallery_get_setting( 'pro_promo_disabled' ) !== 'on';
		}

		/*
		 * Include promo settings
		 */
		public function include_promo_settings( $settings ) {
			$settings['settings'][] = array(
				'id'      => 'pro_promo_disabled',
				'title'   => __( 'Disable PRO Promotions', 'foogallery' ),
				'desc'    => __( 'Disable all premium upsell promotions throughout the WordPress admin.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'advanced'
			);

			return $settings;
		}

		/**
		 * Build up some trial and buy links shared for all promos
		 *
		 * @param $promotion
		 *
		 * @return string
		 */
		private function build_promo_trial_html( $promotion ) {
		    $trial_link = '<a href="' . $this->build_url('foogallery-trial', $promotion ) . '" target="_blank">' . __('start a 7 day free trial', 'foogallery') . '</a>';
		    $pro_link = '<a href="' . $this->build_url( 'foogallery-pricing', $promotion ) . '" target="_blank">' . __('Upgrade to PRO', 'foogallery') . '</a>';
			return sprintf( __('To try the PRO features, %s (no credit card required). Or you can %s right now.', 'foogallery' ), $trial_link, $pro_link );
		}

		/**
		 * Adds promo choices for paging
		 * @param $choices
		 *
		 * @return mixed
		 */
		public function add_promo_paging_choices( $choices ) {
			$choices['promo-page'] = array(
				'label'   => __( 'Numbered',   'foogallery' ),
				'tooltip' => __('Add numbered pagination controls to your larger galleries.', 'foogallery'),
				'class'   => 'foogallery-promo',
				'icon'    => 'dashicons-star-filled'
			);
			$choices['promo-infin'] = array(
				'label'   => __( 'Infinite Scroll',   'foogallery' ),
				'tooltip' => __('Add the popular infinite scroll ability to your larger galleries.', 'foogallery'),
				'class'   => 'foogallery-promo',
				'icon'    => 'dashicons-star-filled'
			);
			$choices['promo-load'] = array(
				'label'   => __( 'Load More',   'foogallery' ),
				'tooltip' => __('Add a Load More button to the end of your larger galleries.', 'foogallery'),
				'class'   => 'foogallery-promo',
				'icon'    => 'dashicons-star-filled'
			);
			return $choices;
		}

		/**
		 * Add promo paging fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_paging_promo_fields( $fields, $template ) {
			if ( $template && array_key_exists( 'paging_support', $template ) && true === $template['paging_support'] ) {
				$fields[] = array(
					'id'       => 'promo_paging',
					'title'    => __( 'FooGallery PRO Feature : Advanced Pagination', 'foogallery' ),
					'desc'     => __( 'Besides the dot pagination, you can also add more advanced pagination to your galleries with a large number of images or videos:', 'foogallery' ) .
								'<ul class="ul-disc"><li><strong>' . __('Numbered' ,'foogallery') . '</strong> ' . __( 'adds a numbered pagination control to top or bottom of your gallery.', 'foogallery' ) .
								'</li><li><strong>' . __('Infinite Scroll' ,'foogallery') . '</strong> ' . __( 'adds the popular \'infinite scroll\' capability to your gallery, so as your visitors scroll, the gallery will load more items.', 'foogallery' ) .
								'</li><li><strong>' . __('Load More' ,'foogallery') . '</strong> ' . __( 'adds a \'Load More\' button to the end of your gallery. When visitors click the button, the next set of items will load in the gallery.', 'foogallery' ) .
					              '</li></ul>' . $this->build_promo_trial_html( 'pagination' ) . '<br /><br />',
					'cta_text' => __( 'View Demos', 'foogallery' ),
					'cta_link' => $this->build_url( 'foogallery-pagination' ),
					'section'  => __( 'Paging', 'foogallery' ),
					'type'     => 'promo',
					'row_data'=> array(
						'data-foogallery-change-selector'          => 'input',
						'data-foogallery-preview'                  => 'shortcode',
						'data-foogallery-hidden'                   => true,
						'data-foogallery-show-when-field'          => 'paging_type',
						'data-foogallery-show-when-field-operator' => 'indexOf',
						'data-foogallery-show-when-field-value'    => 'promo',
					)
				);
			}

			return $fields;
		}

		/**
		 * Adds the promo preset type for hover effect type
		 *
		 * @param $choices
		 *
		 * @return mixed
		 */
		function add_preset_type( $choices ) {
			$choices['promo-presets'] = array(
				'label'   => __( 'Preset',   'foogallery' ),
                'tooltip' => __('Choose from 11 stylish hover effect presets in FooGallery PRO.', 'foogallery'),
                'class'   => 'foogallery-promo',
				'icon'    => 'dashicons-star-filled'
            );

			return $choices;
		}

		/**
		 * Return the index of the requested section
		 *
		 * @param $fields
		 * @param $section
		 *
		 * @return int
		 */
		private function find_index_of_section( $fields, $section ) {
			$index = 0;
			foreach ( $fields as $field ) {
				if ( isset( $field['section'] ) && $section === $field['section'] ) {
					return $index;
				}
				$index++;
			}
			return $index;
		}

		/**
		 * Return the index of the requested field
		 *
		 * @param $fields
		 * @param $field_id
		 *
		 * @return int
		 */
		private function find_index_of_field( $fields, $field_id ) {
			$index = 0;
			foreach ( $fields as $field ) {
				if ( isset( $field['id'] ) && $field_id === $field['id'] ) {
					return $index;
				}
				$index++;
			}
			return $index;
		}

		/**
		 * Add the fields for presets promos
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_preset_promo_fields( $fields, $template ) {
			$index_of_hover_effect_preset_field = $this->find_index_of_field( $fields, 'hover_effect_preset' );

			$new_fields[] = array(
				'id'       => 'hover_effect_preset_promo_help',
				'title'    => __( 'FooGallery PRO Feature : Hover Effect Presets', 'foogallery' ),
				'desc'     => __( 'There are 11 stylish hover effect presets to choose from, which takes all the hard work out of making your galleries look professional and elegant.', 'foogallery' ) .
				              '<br />' . __( 'Some of the effects like "Sarah" add subtle colors on hover, while other effects like "Layla" and "Oscar" add different shapes to the thumbnail.', 'foogallery') .
				              '<br />' . __(' You really need to see all the different effects in action to appreciate them.', 'foogallery' ) . '<br /><br />' . $this->build_promo_trial_html( 'hover-presets' ) . '<br /><br />',
				'cta_text' => __( 'View Demos', 'foogallery' ),
				'cta_link' => $this->build_url( 'foogallery-hover-presets' ),
				'section'  => __( 'Hover Effects', 'foogallery' ),
				'type'     => 'promo',
				'row_data' => array(
					'data-foogallery-hidden'                => true,
					'data-foogallery-show-when-field'       => 'hover_effect_type',
					'data-foogallery-show-when-field-value' => 'promo-presets',
				)
			);

			array_splice( $fields, $index_of_hover_effect_preset_field, 0, $new_fields );

			return $fields;
		}

		/**
		 * Add filtering fields to the gallery templates
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_filtering_promo_fields( $fields, $template ) {
			if ( $template && array_key_exists( 'filtering_support', $template ) && true === $template['filtering_support'] ) {
				$fields[] = array(
					'id'       => 'filtering_type',
					'title'    => __( 'FooGallery PRO Feature : Filtering by Tags or Categories', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'desc'     => __( 'Add frontend filtering to your gallery, simply by assigning media tags or media categories to your gallery attachments. Other filtering features include:', 'foogallery' ) .
					              '<ul class="ul-disc"><li><strong>' . __('Filter Source' ,'foogallery') . '</strong> - ' . __( 'choose to filter the gallery by tag or category, or any other attachment taxonomy.', 'foogallery' ) .
					              '</li><li><strong>' . __('Look &amp; Feel' ,'foogallery') . '</strong> - ' . __( 'display the filters above or below the gallery, and choose a color theme.', 'foogallery' ) .
					              '</li><li><strong>' . __('Selection Mode' ,'foogallery') . '</strong> - ' . __( 'allow your visitors to select a single or multiple filters. Multiple also supports union or intersection modes.', 'foogallery' ) .
					              '</li><li><strong>' . __('Show Counters' ,'foogallery') . '</strong> - ' . __( 'show the number of items in each tag filter.', 'foogallery' ) .
					              '</li><li><strong>' . __('Adjust Size & Opacity' ,'foogallery') . '</strong> - ' . __( 'adjust the size and opacity of each filter based on the number of items.', 'foogallery' ) .
					              '</li></ul>' . $this->build_promo_trial_html( 'filtering' ). '<br /><br />',
					'cta_text' => __( 'View Demos', 'foogallery' ),
					'cta_link' => $this->build_url( 'foogallery-filtering' ),
					'type'     => 'promo',
					'row_data' => array(
						'data-foogallery-change-selector' => 'input',
						'data-foogallery-preview'         => 'shortcode',
						'data-foogallery-value-selector'  => 'input:checked',
					)
				);
			}

			return $fields;
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_section_icons( $section_slug ) {
			if ( 'filtering' === $section_slug ) {
				return 'dashicons-filter';
			}

			if ( 'lightbox' === $section_slug ) {
				return 'dashicons-grid-view';
			}

			return $section_slug;
		}

		/**
		 * Add the FooGallery PRO lightbox
		 * @param $lightboxes
		 *
		 * @return mixed
		 */
		function add_lightbox($lightboxes) {
			$lightboxes['foogallery'] = __( 'FooGallery PRO Lightbox (Not installed!)', 'foogallery' );
			return $lightboxes;
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
		public function add_lightbox_promo_fields( $fields, $template ) {
			//see if the template has a lightbox field
			$found_lightbox = false;
			$position = 0;
			foreach ($fields as $field) {
				if ( 'lightbox' === $field['id'] ) {
					$found_lightbox = true;
					break;
				}
				$position++;
			}

			if ( $found_lightbox ) {

				$field_promo[] = array(
					'id'       => 'lightbox_promo',
					'title'    => __( 'FooGallery PRO Feature : PRO Lightbox', 'foogallery' ),
					'desc'     => __( 'We built a brand new lightbox from the ground up, that works perfectly with your galleries. With 20+ settings to customize the lightbox, including:', 'foogallery' ) .
					              '<ul class="ul-disc"><li><strong>' . __( 'Custom Colors', 'foogallery' ) . '</strong> - ' . __( 'Set your theme and choose your colors! Customize the lightbox colors for each gallery.', 'foogallery' ) .
					              '</li><li><strong>' . __( 'Thumbnail Strip', 'foogallery' ) . '</strong> - ' . __( 'Easily view and navigate through all the images in the gallery without closing the lightbox. Plus, you can also show captions for the thumbs!', 'foogallery' ) .
					              '</li><li><strong>' . __( 'Auto Progress', 'foogallery' ) . '</strong> - ' . __( 'Set the lightbox to auto-progress to the next available image in the gallery after a certain time delay.', 'foogallery' ) .
					              '</li><li><strong>' . __( 'Unique Lightbox Per Gallery', 'foogallery' ) . '</strong> - ' . __( 'Make each of your galleries unique by customizing the lightbox per gallery.', 'foogallery' ) .
					              '</li></ul>' . $this->build_promo_trial_html( 'lightbox' ) . '<br /><br />',
					'section'  => __( 'Lightbox', 'foogallery' ),
					'type'     => 'promo',
					'cta_text' => __( 'View Demos', 'foogallery' ),
					'cta_link' => $this->build_url( 'foogallery-lightbox' )
				);

				//find the index of the first Hover Effect field
				$index = $this->find_index_of_section( $fields, __( 'Hover Effects', 'foogallery' ) );

				array_splice( $fields, $index, 0, $field_promo );

				$lightbox_desc = __( 'Website visitors prefer a gallery with a lightbox. A lightbox allows you to showcase your images, as well as improve navigation between images in your gallery. We have a few Lightbox options that you will love:', 'foogallery' ) .
				                 '<ul class="ul-disc">';

				$foobox_pro_installed = class_exists( 'fooboxV2' );
				$foobox_free_installed = class_exists( 'FooBox' ) && !$foobox_pro_installed;

				if ( !$foobox_free_installed && !$foobox_pro_installed ) {
					$foobox_free_install_url = wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'install-plugin',
								'plugin' => 'foobox-image-lightbox'
							),
							admin_url( 'update.php' )
						),
						'install-plugin_foobox-image-lightbox'
					);
					$foobox_free_html = ' <a href="' . $foobox_free_install_url . '" target="_blank">' . __( 'Install it now!', 'foogallery' ) . '</a>';

					$foobox_free_info = __( 'Our free responsive lightbox that just works.', 'foogallery' ) . $foobox_free_html;

					$lightbox_desc .= '<li><strong>' . __( 'FooBox Free', 'foogallery' ) . '</strong> - ' . $foobox_free_info . '</li>';
				}

				if ( !$foobox_pro_installed ) {
					$foobox_pro_html = ' <a href="' . $this->build_url('foobox-pro') . '" target="_blank">' . __( 'View more details', 'foogallery' ) . '</a>';

					$lightbox_desc .= '<li><strong>' . __( 'FooBox PRO', 'foogallery' ) . '</strong> - ' . __( 'The stand-alone PRO version of our Lightbox plugin with tons of extra features including social sharing.', 'foogallery' ) . $foobox_pro_html . '</li>';
				}

				$foogallery_pro_lightbox_info = __( 'Our built-in Lightbox plugin that comes with FooGallery PRO. Check out the Lightbox tab to see more details.', 'foogallery' );
				$lightbox_desc .= '<li><strong>' . __( 'FooGallery PRO Lightbox', 'foogallery' ) . '</strong> - ' . $foogallery_pro_lightbox_info . '</li>';

				$lightbox_help_field = array(
					array(
						'id'      => 'lightbox_promo_2',
						'title'   => __( 'Your Gallery Needs A Lightbox!', 'foogallery' ),
						'desc'    => $lightbox_desc,
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'row_data' => array(
							'data-foogallery-hidden' 				   => true,
							'data-foogallery-show-when-field'          => 'lightbox',
							'data-foogallery-show-when-field-value'    => 'none',
						)
					)
				);

				array_splice( $fields, $position + 1, 0, $lightbox_help_field );

				$lightbox_foogallery_help_field = array(
					array(
						'id'      => 'lightbox_promo_3',
						'title'   => __( 'FooGallery PRO Lightbox', 'foogallery' ),
						'desc'    => $foogallery_pro_lightbox_info,
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'row_data' => array(
							'data-foogallery-hidden' 				   => true,
							'data-foogallery-show-when-field'          => 'lightbox',
							'data-foogallery-show-when-field-value'    => 'foogallery',
						)
					)
				);

				array_splice( $fields, $position + 2, 0, $lightbox_foogallery_help_field );

				if ( !$foobox_free_installed && !$foobox_pro_installed) {
					$foobox_free_help_field = array(
						array(
							'id'       => 'lightbox_promo_4',
							'desc'     => '<strong>' . __( 'Try FooBox FREE', 'foogallery' ) . '</strong> - ' . $foobox_free_info,
							'section'  => __( 'General', 'foogallery' ),
							'type'     => 'help',
							'row_data' => array(
								'data-foogallery-hidden'                => true,
								'data-foogallery-show-when-field'       => 'lightbox',
								'data-foogallery-show-when-field-value' => 'foobox',
							)
						)
					);

					array_splice( $fields, $position + 3, 0, $foobox_free_help_field );
				} else if ( !$foobox_pro_installed ) {
					$foobox_pro_help_field = array(
						array(
							'id'       => 'lightbox_promo_5',
							'desc'     => '<strong>' . __( 'Try FooBox PRO!', 'foogallery' ) . '</strong> - ' . __( 'The stand-alone PRO version of our Lightbox plugin with tons of extra features including social sharing.', 'foogallery' ) . $foobox_pro_html,
							'section'  => __( 'General', 'foogallery' ),
							'type'     => 'help',
							'row_data' => array(
								'data-foogallery-hidden'                => true,
								'data-foogallery-show-when-field'       => 'lightbox',
								'data-foogallery-show-when-field-value' => 'foobox',
							)
						)
					);

					array_splice( $fields, $position + 3, 0, $foobox_pro_help_field );
				}
			}

			return $fields;
		}

		/**
		 * Add fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_appearance_promo_fields( $fields, $template ) {

			$fields[] = array(
				'id'       => 'filter_promo',
				'title'    => __( 'FooGallery PRO Feature : Thumbnail Filters (Like Instagram!)', 'foogallery' ),
				'section'  => __( 'Appearance', 'foogallery' ),
				'desc'     => __( 'Apply a filter to your gallery thumbnails, just like you can in Instagram. Choose from 12 unique filters!', 'foogallery' )
	                . '<br /><br />' . $this->build_promo_trial_html( 'appearance' ) . '<br /><br />',
				'type'     => 'promo',
				'cta_text' => __( 'View Demos', 'foogallery' ),
				'cta_link' => $this->build_url( 'foogallery-thumbnail-filters' ),
			);

			return $fields;
		}

		/**
		 * Add fields to the gallery template
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_video_promo_fields( $fields ) {

			$fields[] = array(
				'id'       => 'video_promo',
				'section'  => __( 'Video', 'foogallery' ),
				'title'    => __( 'FooGallery PRO Feature : Video Galleries', 'foogallery' ),
				'desc'     => __( 'Take your galleries to the next level with full video support:', 'foogallery' ) .
				              '<ul class="ul-disc"><li><strong>' . __( 'Video Galleries', 'foogallery' ) . '</strong> - ' . __( 'Easily import videos to create beautiful video galleries. Or mix images and videos if you like.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Youtube Video Search', 'foogallery' ) . '</strong> - ' . __( 'Search for Youtube videos, and then import them into your galleries in seconds.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Vimeo Search And Import', 'foogallery' ) . '</strong> - ' . __( 'Import albums, channels, users or individual videos from Vimeo.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Import From Other Sources', 'foogallery' ) . '</strong> - ' . __( 'Import from other popular video sources, including Facebook, Daily Motion, TED and others!', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Self-Hosted Videos', 'foogallery' ) . '</strong> - ' . __( 'Host your own videos? No problem! Upload them, select thumbnails and use in your gallery.', 'foogallery' ) .
				              '</li></ul>' . $this->build_promo_trial_html( 'videos' ) . '<br /><br />',
				'type'     => 'promo',
				'default'  => 'fg-video-default',
				'cta_text' => __( 'View Demos', 'foogallery' ),
				'cta_link' => $this->build_url( 'foogallery-videos' )
			);

			return $fields;
		}

		/**
		 * Add our promo gallery templates
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_promo_templates( $gallery_templates ) {
			$gallery_templates[] = array(
				'slug'                  => 'polaroid_promo',
				'name'                  => __( 'Polaroid PRO', 'foogallery' ),
				'preview_support'       => false,
				'common_fields_support' => false,
				'lazyload_support'      => false,
				'paging_support'        => false,
				'thumbnail_dimensions'  => false,
				'filtering_support'     => false,
				'fields'	  => array(
					array(
						'id'      => 'polaroid_promo',
						'title'   => __( 'Polaroid PRO Gallery Template', 'foogallery' ),
						'desc'    => __( 'Only available in FooGallery PRO, the Polaroid PRO gallery template is a fun take on the simple portfolio gallery. Image thumbnails are framed as Polaroid photos which are staggered on the page.', 'foogallery' ) . '<br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-polaroid-gallery.jpg" />' .
						             '<br /><br />' . $this->build_promo_trial_html( 'polaroid' ) . '<br /><br />',
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'keep_in_promo' => false,
						'cta_text'=> __('View Demo', 'foogallery'),
						'cta_link'=> $this->build_url( 'foogallery-polaroid' )
					)
				)
			);

			$gallery_templates[] = array(
				'slug'                  => 'grid_promo',
				'name'                  => __( 'Grid PRO', 'foogallery'),
				'preview_support'       => false,
				'common_fields_support' => false,
				'lazyload_support'      => false,
				'paging_support'        => false,
				'thumbnail_dimensions'  => false,
				'filtering_support'     => false,
				'fields'	  => array(
					array(
						'id'      => 'grid_promo',
						'title'   => __( 'Grid PRO Gallery Template', 'foogallery' ),
						'desc'    => __( 'Only available in FooGallery PRO, the Grid PRO gallery template creates a stylish grid gallery that allows you to "preview" each image, similar to how Google Image Search works.', 'foogallery' ) . '<br /><br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-grid-gallery.jpg" />' .
						             '<br /><br />' . $this->build_promo_trial_html( 'grid' ) . '<br /><br />',
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'keep_in_promo' => false,
						'cta_text'=> __('View Demo', 'foogallery'),
						'cta_link'=> $this->build_url( 'foogallery-grid' )
					)
				)
			);

			$gallery_templates[] = array(
				'slug'                  => 'slider_promo',
				'name'                  => __( 'Slider PRO', 'foogallery'),
				'preview_support'       => false,
				'common_fields_support' => false,
				'lazyload_support'      => false,
				'paging_support'        => false,
				'thumbnail_dimensions'  => false,
				'filtering_support'     => false,
				'fields'	  => array(
					array(
						'id'      => 'slider_promo',
						'title'   => __( 'Slider PRO Gallery Template', 'foogallery' ),
						'desc'    => __( 'Only available in FooGallery PRO, the Slider PRO gallery template creates an amazing slider gallery in either a horizontal or a vertical layout.', 'foogallery' ) . '<br /><br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-slider-gallery-vertical.jpg" /><br /><br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-slider-gallery-horizontal.jpg" /><br /><br />' .
						             $this->build_promo_trial_html( 'slider' ) . '<br /><br />',
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'keep_in_promo' => false,
						'cta_text'=> __('View Demo', 'foogallery'),
						'cta_link'=> $this->build_url( 'foogallery-slider' )
					)
				)
			);

			return $gallery_templates;
		}

		/**
		 * Remove fields from the promo template
		 *
		 * @uses "foogallery_override_gallery_template_fields-template"
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function remove_all_fields_from_promo_gallery_template( $fields, $template ) {
			$remaining_fields = array();

			//remove all fields that are not of type promo
			foreach ($fields as $field) {
				if ( array_key_exists( 'keep_in_promo', $field ) ) {
					$remaining_fields[] = $field;
				}
			}

			return $remaining_fields;
		}
	}
}