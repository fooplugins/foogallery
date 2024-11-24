<?php
/*
 * FooGallery Pro Feature Promotion class
 */

if ( ! class_exists( 'FooGallery_Pro_Promotion' ) ) {
	class FooGallery_Pro_Promotion {

		private $urls = array(
			'foogallery-pagination'        => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pagination/',
			'foogallery-filtering'         => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filtering/',
			'foogallery-hover-presets'     => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/hover-presets/',
			'foogallery-captions'          => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/custom-captions/',
			'foogallery-lightbox'          => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pro-lightbox/',
			'foogallery-thumbnail-filters' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filter-effects/',
			'foogallery-loaded-effects'    => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/animated-loaded-effects/',
			'foogallery-polaroid'          => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/polaroid-gallery/',
			'foogallery-grid'              => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/grid-gallery/',
			'foogallery-slider'            => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/slider-gallery/',
			'foogallery-videos'            => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/video-gallery/',
			'foogallery-exif'              => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/exif-data/',
			'foogallery-bulk-copy'         => 'https://fooplugins.com/bulk-copy-foogallery-pro/',
			'foogallery-trial'             => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/start-trial/',
			'foogallery-pricing'           => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pricing/',
			'foogallery-plans'             => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pricing/#plans',
			'foobox-pro'                   => 'https://fooplugins.com/foobox/',
			'foogallery-datasources'       => 'https://fooplugins.com/load-galleries-from-other-sources/',
			'foogallery-commerce'          => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/',
			'foogallery-product-gallery'   => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/#product-gallery',
			'foogallery-protection'        => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/photo-watermark/',
            'foogallery-master'            => 'https://fooplugins.com/documentation/foogallery/pro-commerce/use-master-gallery/',
            'foogallery-import-export'     => 'https://fooplugins.com/documentation/foogallery/getting-started-foogallery/import-export/',
            'foogallery_whitelabeling'     => 'https://fooplugins.com/documentation/foogallery/pro-commerce/white-labeling/'
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

				// Determine current plan, and show promotions based on the current plan.
				$fs_instance = foogallery_fs();
				$current_plan = $fs_instance->get_plan_name();
				$is_free = $fs_instance->is_free_plan();
				$is_trial = $fs_instance->is_trial();

				// If in trial mode, skip showing promotions
				if ( $is_trial ) {
					return;
				}

				$show_starter_promos = true;
				$show_expert_promos = true;
				$show_commerce_promos = true;

				if ( !$is_free ) {
					if ( FOOGALLERY_PRO_PLAN_STARTER === $current_plan ) {
						$show_starter_promos = false;
					} else if ( FOOGALLERY_PRO_PLAN_EXPERT == $current_plan ) {
						$show_starter_promos = $show_expert_promos = false;
					} else {
						// Do not show any promos!
						return;
					}
				}

				if ( $show_starter_promos ) {
					//PRO Starter Templates
					add_filter( 'foogallery_gallery_templates', array( $this, 'add_promo_templates' ), 99, 1 );
					add_filter( 'foogallery_override_gallery_template_fields-polaroid_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );
					add_filter( 'foogallery_override_gallery_template_fields-grid_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );
					add_filter( 'foogallery_override_gallery_template_fields-slider_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );

					//presets
					add_filter( 'foogallery_gallery_template_common_thumbnail_fields_hover_effect_type_choices', array( $this, 'add_preset_type' ) );
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_preset_promo_fields' ), 99, 2 );

					//Instagram filters
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_appearance_promo_fields' ), 20, 2 );
				}

				if ( $show_expert_promos ) {
					//Videos
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_video_promo_fields' ) );

					//filtering
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_filtering_promo_fields' ), 10, 2 );

					//paging
					add_filter( 'foogallery_gallery_template_paging_type_choices', array( $this, 'add_promo_paging_choices' ) );
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_paging_promo_fields' ), 10, 2 );

					//Datasource promos
					add_action( 'foogallery_gallery_datasources', array( $this, 'add_expert_promo_datasources' ), 99 );
					//add_action( 'foogallery_gallery_metabox_items_add', array( $this, 'add_datasources_css' ), 9 );
					add_action( 'foogallery_admin_datasource_modal_content', array( $this, 'render_datasource_modal_content_default' ) );
					add_action( 'foogallery-datasource-modal-content_folders_promo', array( $this, 'render_datasource_modal_content_folders_promo' ), 10, 3 );
					add_action( 'foogallery-datasource-modal-content_media_tags_promo', array( $this, 'render_datasource_modal_content_taxonomy_promo' ), 10, 3 );
					add_action( 'foogallery-datasource-modal-content_media_categories_promo', array( $this, 'render_datasource_modal_content_taxonomy_promo' ), 10, 3 );
					add_action( 'foogallery-datasource-modal-content_lightroom_promo', array( $this, 'render_datasource_modal_content_lightroom_promo' ), 10, 3 );
					add_action( 'foogallery-datasource-modal-content_rml_promo', array( $this, 'render_datasource_modal_content_rml_promo' ), 10, 3 );
					add_action( 'foogallery-datasource-modal-content_post_query_promo', array( $this, 'render_datasource_modal_content_post_query_promo' ), 10, 3 );
					remove_action( 'foogallery_admin_datasource_modal_content', array( $foogallery_admin_datasource_instance, 'render_datasource_modal_default_content' ) );

					//Custom Captions
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_advanced_caption_fields' ), 100, 2 );

					//EXIF
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_exif_promo_fields' ), 10, 2 );

					//Bulk Copy Settings
					add_action( 'add_meta_boxes_' . FOOGALLERY_CPT_GALLERY, array( $this, 'add_bulk_copy_meta_box_to_gallery' ) );

					add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_expert_section_icons' ) );

					//EXIF global settings (TODO)
				}

				if ( $show_commerce_promos ) {

					add_filter( 'foogallery_gallery_settings_metabox_section_icon', array( $this, 'add_commerce_section_icons' ) );

					//Ecommerce Settings
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_ecommerce_promo_fields' ), 90, 2 );

					//Product Datasource
					add_action( 'foogallery_gallery_datasources', array( $this, 'add_commerce_promo_datasources' ), 99 );
					add_action( 'foogallery-datasource-modal-content_products_promo', array( $this, 'render_datasource_modal_content_products_promo' ), 10, 3 );

					//Product Gallery Template
					add_filter( 'foogallery_gallery_templates', array( $this, 'add_commerce_promo_templates' ), 999, 1 );
					add_filter( 'foogallery_override_gallery_template_fields-product_promo', array( $this, 'remove_all_fields_from_promo_gallery_template' ), 999, 2 );

					//Watermarking & Protection Settings
					add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_protection_promo_fields' ), 80, 2 );

					//Watermarking & Protection global settings (TODO)

					//Ecommerce global settings (TODO)
				}
			}
		}

		/**
		 * Add a metabox to the gallery for bulk copying
		 * @param $post
		 */
		function add_bulk_copy_meta_box_to_gallery($post) {
			add_meta_box(
				'foogallery_bulk_copy',
				__( 'Bulk Copy', 'foogallery' ),
				array( $this, 'render_gallery_bulk_copy_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'low'
			);
		}

		/**
		 * Render the bulk copy metabox on the gallery edit page
		 * @param $post
		 */
		function render_gallery_bulk_copy_metabox( $post ) {
			$this->render_datasource_modal_content(
				__('Bulk Copy Settings', 'foogallery' ),
				__('Copy settings from one gallery to other galleries in bulk. You can choose which settings, and what galleries to copy them to.', 'foogallery' ),
				'foogallery-bulk-copy'
			);
		}

		/**
		 * Add caption fields to the gallery template
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_advanced_caption_fields( $fields, $template ) {

			//add caption type field before other caption fields
			$new_fields[] = array(
				'id'       => 'promo_captions_type',
				'title'    => __( 'Caption Type', 'foogallery' ),
				'desc'     => __( 'What type of captions do you want to display in the gallery. By default, captions will be built up from the image attributes for both the caption title and description.', 'foogallery' ),
				'section'  => __( 'Captions', 'foogallery' ),
				'type'     => 'radio',
				'default'  => '',
				'choices'  => array(
					''       => __( 'Default', 'foogallery' ),
					'custom' => array(
						'label'   => __( 'Custom',   'foogallery' ),
						'tooltip' => __('Captions can be built up using custom HTML and placeholders', 'foogallery'),
						'class'   => 'foogallery-promo',
						'icon'    => 'dashicons-star-filled'
					)
				),
				'row_data' => array(
					'data-foogallery-change-selector' => 'input:radio',
					'data-foogallery-value-selector'  => 'input:checked',
				)
			);

			$new_fields[] = array(
				'id'       => 'promo_custom_captions',
				'title'    => __( 'PRO Expert Feature : Advanced Custom Captions', 'foogallery' ),
				'desc'     => __( 'Take complete control over your image captions, and customize them by using HTML and pre-defined placeholders. Integrates with popular solutions like ACF and Pods for unlimited possibilities.', 'foogallery')
				                  . '<br /><br />' . $this->build_promo_trial_html( 'pagination', __( 'PRO Expert', 'foogallery' ) ) . '<br /><br />',
				'cta' => $this->build_cta_buttons( 'foogallery-captions' ),
				'section'  => __( 'Captions', 'foogallery' ),
				'type'     => 'promo',
				'row_data'=> array(
					'data-foogallery-change-selector'          => 'input',
					'data-foogallery-hidden'                   => true,
					'data-foogallery-show-when-field'          => 'promo_captions_type',
					'data-foogallery-show-when-field-value'    => 'custom',
				)
			);

			$field_index = foogallery_admin_fields_find_index_of_field( $fields, 'caption_title_source' );

			array_splice( $fields, $field_index, 0, $new_fields );

			return $fields;
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

		function render_datasource_modal_content( $datasouce_title, $datasource_desc,
			$datasource_url_name = 'foogallery-datasources', $plan = null, $class = '' ) {
			if ( !isset( $plan ) ) {
				$plan = __('PRO Expert', 'foogallery');
			}
?>
			<div class="foogallery_template_field_type-promo <?php echo $class; ?>">
				<div class="foogallery-promo">
					<strong><?php echo $plan; ?> <?php _e('Feature', 'foogallery' ); ?> : <?php echo $datasouce_title; ?></strong>
					<br><br>
					<?php echo $datasource_desc; ?>
					<br><br>
					<?php echo $this->build_promo_trial_html( 'datasources', $plan ); ?>
					<br><br>
					<a class="button-primary" href="<?php echo esc_url( $this->build_url( $datasource_url_name ) ); ?>" target="_blank"><?php echo __( 'Learn More', 'foogallery' ); ?></a>
					<a class="button-secondary" href="<?php echo esc_url( $this->build_url( 'foogallery-plans' ) ); ?>" target="_blank"><?php echo __( 'Compare Plans', 'foogallery' ); ?></a>
				</div>
			</div>
<?php
		}

		/**
		 * Add the expert promotion datasources
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		function add_expert_promo_datasources( $datasources ) {
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
		private function build_promo_trial_html( $promotion, $plan = null ) {
			if ( !isset( $plan ) ) {
				$plan = __( 'PRO', 'foogallery' );
			}
		    $trial_link = '<a href="' . $this->build_url('foogallery-trial', $promotion ) . '" target="_blank">' . __('start a 7 day free trial', 'foogallery') . '</a>';
		    $pro_link = '<a href="' . foogallery_admin_pricing_url() . '">' . __('Upgrade to', 'foogallery') . ' ' . $plan . '</a>';
			return sprintf( __('To try the %s features, %s (no credit card required). Or you can %s right now.', 'foogallery' ), $plan, $trial_link, $pro_link );
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
					'title'    => __( 'PRO Expert Feature : Advanced Pagination', 'foogallery' ),
					'desc'     => __( 'Besides the dot pagination, you can also add more advanced pagination to your galleries with a large number of images or videos:', 'foogallery' ) .
								'<ul class="ul-disc"><li><strong>' . __('Numbered' ,'foogallery') . '</strong> ' . __( 'adds a numbered pagination control to top or bottom of your gallery.', 'foogallery' ) .
								'</li><li><strong>' . __('Infinite Scroll' ,'foogallery') . '</strong> ' . __( 'adds the popular \'infinite scroll\' capability to your gallery, so as your visitors scroll, the gallery will load more items.', 'foogallery' ) .
								'</li><li><strong>' . __('Load More' ,'foogallery') . '</strong> ' . __( 'adds a \'Load More\' button to the end of your gallery. When visitors click the button, the next set of items will load in the gallery.', 'foogallery' ) .
					              '</li></ul>' . $this->build_promo_trial_html( 'pagination', __( 'PRO Expert', 'foogallery' ) ) . '<br /><br />',
					'cta' => $this->build_cta_buttons( 'foogallery-pagination' ),
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
                'tooltip' => __('Choose from 11 stylish hover effect presets in PRO Starter.', 'foogallery'),
                'class'   => 'foogallery-promo-prostarter',
				'icon'    => 'dashicons-star-filled'
            );

			return $choices;
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
			$index_of_hover_effect_preset_field = foogallery_admin_fields_find_index_of_field( $fields, 'hover_effect_preset' );

			$new_fields[] = array(
				'id'       => 'hover_effect_preset_promo_help',
				'title'    => __( 'PRO Starter Feature : Hover Effect Presets', 'foogallery' ),
				'desc'     => __( 'There are 11 stylish hover effect presets to choose from, which takes all the hard work out of making your galleries look professional and elegant.', 'foogallery' ) .
				              '<br />' . __( 'Some of the effects like "Sarah" add subtle colors on hover, while other effects like "Layla" and "Oscar" add different shapes to the thumbnail.', 'foogallery') .
				              '<br />' . __(' You really need to see all the different effects in action to appreciate them.', 'foogallery' ) . '<br /><br />' .
				              $this->build_promo_trial_html( 'hover-presets', __( 'PRO Starter', 'foogallery' )  ) . '<br /><br />',
				'class'   => 'foogallery_promo_prostarter',
				'type'     => 'promo',
				'cta' => $this->build_cta_buttons( 'foogallery-hover-presets' ),
				'section'  => __( 'Hover Effects', 'foogallery' ),
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
		 * Add EXIF fields to the gallery templates
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_exif_promo_fields( $fields, $template ) {
			$fields[] = array(
				'id'       => 'promo_exif',
				'title'    => __( 'PRO Expert Feature : EXIF Metadata', 'foogallery' ),
				'section'  => __( 'EXIF', 'foogallery' ),
				'desc'     => __( 'Show image metadata within your galleries. A must-have for professional photographers wanting to showcase specific metadata about each image.', 'foogallery' )
				              . '<br /><br />' . $this->build_promo_trial_html( 'filtering', __( 'PRO Expert', 'foogallery' ) ). '<br /><br />',
				'cta' => $this->build_cta_buttons( 'foogallery-exif' ),
				'type'     => 'promo',
				'row_data' => array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-value-selector'  => 'input:checked',
				)
			);

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
					'id'       => 'promo_filtering',
					'title'    => __( 'PRO Expert Feature : Filtering by Tags or Categories', 'foogallery' ),
					'section'  => __( 'Filtering', 'foogallery' ),
					'desc'     => __( 'Add frontend filtering to your gallery, simply by assigning media tags or media categories to your gallery attachments. Other filtering features include:', 'foogallery' ) .
					              '<ul class="ul-disc"><li><strong>' . __('Filter Source' ,'foogallery') . '</strong> - ' . __( 'choose to filter the gallery by tag or category, or any other attachment taxonomy.', 'foogallery' ) .
					              '</li><li><strong>' . __('Look &amp; Feel' ,'foogallery') . '</strong> - ' . __( 'display the filters above or below the gallery, and choose a color theme.', 'foogallery' ) .
					              '</li><li><strong>' . __('Selection Mode' ,'foogallery') . '</strong> - ' . __( 'allow your visitors to select a single or multiple filters. Multiple also supports union or intersection modes.', 'foogallery' ) .
					              '</li><li><strong>' . __('Show Counters' ,'foogallery') . '</strong> - ' . __( 'show the number of items in each tag filter.', 'foogallery' ) .
					              '</li><li><strong>' . __('Adjust Size & Opacity' ,'foogallery') . '</strong> - ' . __( 'adjust the size and opacity of each filter based on the number of items.', 'foogallery' ) .
					              '</li></ul>' . $this->build_promo_trial_html( 'filtering', __( 'PRO Expert', 'foogallery' ) ). '<br /><br />',
					'cta' => $this->build_cta_buttons( 'foogallery-filtering' ),
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
		function add_expert_section_icons( $section_slug ) {
			if ( 'filtering' === $section_slug ) {
				return 'dashicons-filter';
			}

			if ( 'lightbox' === $section_slug ) {
				return 'dashicons-grid-view';
			}

			if ( 'exif' === strtolower( $section_slug ) ) {
				return 'dashicons-info-outline';
			}

			return $section_slug;
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
				'title'    => __( 'PRO Starter Feature : Thumbnail Filters (Like Instagram!)', 'foogallery' ),
				'section'  => __( 'Appearance', 'foogallery' ),
				'desc'     => __( 'Apply a filter to your gallery thumbnails, just like you can in Instagram. Choose from 12 unique filters!', 'foogallery' )
	                . '<br /><br />' . $this->build_promo_trial_html( 'appearance', __( 'PRO Starter', 'foogallery' )  ) . '<br /><br />',
				'type'     => 'promo',
				'class'   => 'foogallery_promo_prostarter',
				'cta'      => $this->build_cta_buttons( 'foogallery-thumbnail-filters' )
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
				'title'    => __( 'PRO Expert Feature : Video Galleries', 'foogallery' ),
				'desc'     => __( 'Take your galleries to the next level with full video support:', 'foogallery' ) .
				              '<ul class="ul-disc"><li><strong>' . __( 'Video Galleries', 'foogallery' ) . '</strong> - ' . __( 'Easily import videos to create beautiful video galleries. Or mix images and videos if you like.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Youtube Video Search', 'foogallery' ) . '</strong> - ' . __( 'Search for Youtube videos, and then import them into your galleries in seconds.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Vimeo Search And Import', 'foogallery' ) . '</strong> - ' . __( 'Import albums, channels, users or individual videos from Vimeo.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Import From Other Sources', 'foogallery' ) . '</strong> - ' . __( 'Import from other popular video sources, including Facebook, Daily Motion, TED and others!', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Self-Hosted Videos', 'foogallery' ) . '</strong> - ' . __( 'Host your own videos? No problem! Upload them, select thumbnails and use in your gallery.', 'foogallery' ) .
				              '</li></ul>' . $this->build_promo_trial_html( 'videos', __( 'PRO Expert', 'foogallery' )  ) . '<br /><br />',
				'type'     => 'promo',
				'default'  => 'fg-video-default',
				'cta' => $this->build_cta_buttons( 'foogallery-videos' )
			);

			return $fields;
		}

		private function build_cta_buttons( $url_name ) {
			return array(
				array(
					'text' => __( 'View Demo', 'foogallery' ),
					'link' => $this->build_url( $url_name )
				),
				array(
					'text' => __( 'Compare PRO Plans', 'foogallery' ),
					'link' => $this->build_url( 'foogallery-plans' ),
					'class' => 'button-secondary'
				)
			);
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
						'desc'    => __( 'Available in all PRO plans, the Polaroid PRO gallery template is a fun take on the simple portfolio gallery. Image thumbnails are framed as Polaroid photos which are staggered on the page.', 'foogallery' ) . '<br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-polaroid-gallery.jpg" />' .
						             '<br /><br />' . $this->build_promo_trial_html( 'polaroid', __( 'PRO Starter', 'foogallery' )  ) . '<br /><br />',
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'class'   => 'foogallery_promo_prostarter',
						'keep_in_promo' => true,
						'cta' => $this->build_cta_buttons( 'foogallery-polaroid' )
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
						'desc'    => __( 'Available in all PRO plans, the Grid PRO gallery template creates a stylish grid gallery that allows you to "preview" each image, similar to how Google Image Search works.', 'foogallery' ) . '<br /><br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-grid-gallery.jpg" />' .
						             '<br /><br />' . $this->build_promo_trial_html( 'grid', __( 'PRO Starter', 'foogallery' )  ) . '<br /><br />',
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'class'   => 'foogallery_promo_prostarter',
						'keep_in_promo' => true,
						'cta' => $this->build_cta_buttons( 'foogallery-grid' )
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
						'desc'    => __( 'Available in all PRO plans, the Slider PRO gallery template creates an amazing slider gallery in either a horizontal or a vertical layout.', 'foogallery' ) . '<br /><br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-slider-gallery-vertical.jpg" /><br /><br />' .
						             '<img src="https://assets.fooplugins.com/foogallery/foogallery-slider-gallery-horizontal.jpg" /><br /><br />' .
						             $this->build_promo_trial_html( 'slider', __( 'PRO Starter', 'foogallery' ) ) . '<br /><br />',
						'section' => __( 'General', 'foogallery' ),
						'type'    => 'promo',
						'class'   => 'foogallery_promo_prostarter',
						'keep_in_promo' => true,
						'cta' => $this->build_cta_buttons( 'foogallery-slider' )
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

		/**
		 * Add Ecommerce fields to the gallery templates
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_ecommerce_promo_fields( $fields, $template ) {
			$new_fields[] = array(
				'id'       => 'promo_ecommerce',
				'title'    => __( 'PRO Commerce Feature : WooCommerce Integration', 'foogallery' ),
				'section'  => __( 'Ecommerce', 'foogallery' ),
				'desc'     => __( 'Start making money from selling your photographs, with our deep integration with WooCommerce:', 'foogallery' ) .
				              '<ul class="ul-disc"><li><strong>' . __( 'Product Datasource', 'foogallery' ) . '</strong> - ' . __( 'Create a dynamic product gallery that updates when you add or change products.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Filter By Product Categories', 'foogallery' ) . '</strong> - ' . __( 'Filter your gallery images by product category.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Sale Ribbons', 'foogallery' ) . '</strong> - ' . __( 'Draw attention to products on sale, and show a cool ribbon over your product.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Add To Cart Buttons', 'foogallery' ) . '</strong> - ' . __( 'Add buttons to your products to easily add to cart or view.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Custom Caption Support', 'foogallery' ) . '</strong> - ' . __( 'Already using our advanced custom captions? Now you can include any product info in your caption template.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Product Variation Support', 'foogallery' ) . '</strong> - ' . __( 'Using variable products? No problem! Show variations in the lightbox and add directly to cart from the lightbox.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Manually Link Products', 'foogallery' ) . '</strong> - ' . __( 'You can also manually link a product to each item in your gallery for complete control.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Master Product', 'foogallery' ) . '</strong> - ' . __( 'You can set a "Master Product" for all items in the gallery. Info from the attachment will be transferred to items in the cart and order.', 'foogallery' ) .
				              '</li></ul>' . $this->build_promo_trial_html( 'ecommerce', __( 'PRO Commerce', 'foogallery' )  ) . '<br /><br />',
				'cta' => $this->build_cta_buttons( 'foogallery-commerce' ),
				'class'   => 'foogallery_promo_commerce',
				'type'     => 'promo',
				'row_data' => array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-value-selector'  => 'input:checked',
				)
			);

			// find the index of the advanced section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}


		/**
		 * Add Protection fields to the gallery templates
		 *
		 * @uses "foogallery_override_gallery_template_fields"
		 *
		 * @param $fields
		 * @param $template
		 *
		 * @return array
		 */
		function add_protection_promo_fields( $fields, $template ) {
			$new_fields[] = array(
				'id'       => 'promo_protection',
				'title'    => __( 'PRO Commerce Feature : Image Protection', 'foogallery' ),
				'section'  => __( 'Protection', 'foogallery' ),
				'desc'     => __( 'Protect your images from theft:', 'foogallery' ) .
				              '<ul class="ul-disc"><li><strong>' . __( 'Disable Right Click', 'foogallery' ) . '</strong> - ' . __( 'Prevent your visitors from being able to right click on thumbnails and full size images in the lightbox.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Watermark Generation', 'foogallery' ) . '</strong> - ' . __( 'Generate advanced watermarks for all images in your gallery.', 'foogallery' ) .
				              '</li><li><strong>' . __( 'Built-in watermark designs', 'foogallery' ) . '</strong> - ' . __( 'You can choose one of our built-in repeating watermarks, or you can upload and use your own.', 'foogallery' ) .
				              '</li></ul>' . $this->build_promo_trial_html( 'protection', __( 'PRO Commerce', 'foogallery' )  ) . '<br /><br />',
				'cta' => $this->build_cta_buttons( 'foogallery-protection' ),
				'class'   => 'foogallery_promo_commerce',
				'type'     => 'promo',
				'row_data' => array(
					'data-foogallery-change-selector' => 'input',
					'data-foogallery-value-selector'  => 'input:checked',
				)
			);

			// find the index of the advanced section.
			$index = foogallery_admin_fields_find_index_of_section( $fields, __( 'Advanced', 'foogallery' ) );

			array_splice( $fields, $index, 0, $new_fields );

			return $fields;
		}

		/**
		 * Add the commerce promotion datasources
		 *
		 * @param $datasources
		 *
		 * @return mixed
		 */
		function add_commerce_promo_datasources( $datasources ) {
			$datasources['products_promo'] = array(
				'id'     => 'products_promo',
				'name'   => __( 'WooCommerce Products', 'foogallery' ),
				'menu'  => __( 'WooCommerce Products', 'foogallery' ),
				'public' => true,
			);

			return $datasources;
		}

		/**
		 * Output the server folders datasource modal content
		 *
		 * @param $foogallery_id
		 */
		function render_datasource_modal_content_products_promo( $foogallery_id, $datasource_value ) {
			$this->render_datasource_modal_content(
				__( 'WooCommerce Product Datasource', 'foogallery' ),
				__( 'Create a dynamic gallery from your WooCommerce products.<br>You can limit how many products, only show certain categories or exclude specific products from your product gallery. Your gallery will dynamically update when you add or change any of your products.', 'foogallery' ),
				'foogallery-product-gallery',
				__( 'PRO Commerce', 'foogallery' ),
				'foogallery_promo_commerce'
			);
		}

		/**
		 * Returns the Dashicon that can be used in the settings tabs
		 *
		 * @param $section_slug
		 *
		 * @return string
		 */
		function add_commerce_section_icons( $section_slug ) {
			if ( 'ecommerce' === strtolower( $section_slug ) ) {
				return 'dashicons-cart';
			}

			if ( 'protection' === strtolower( $section_slug ) ) {
				return 'dashicons-lock';
			}

			return $section_slug;
		}

		/**
		 * Add our commerce promo gallery templates
		 * @param $gallery_templates
		 *
		 * @return array
		 */
		function add_commerce_promo_templates( $gallery_templates ) {
			
			$gallery_templates[] = array(
				'slug'                  => 'product_promo',
				'name'                  => __( 'Product Gallery', 'foogallery' ),
				'preview_support'       => false,
				'common_fields_support' => false,
				'lazyload_support'      => false,
				'paging_support'        => false,
				'thumbnail_dimensions'  => false,
				'filtering_support'     => false,
				'fields'                => array(
					array(
						'id'            => 'product_promo',
						'title'         => __( 'Product Gallery Template', 'foogallery' ),
						'desc'          => __( 'Only available in the Commerce PRO plan, the Product Gallery template works out of the box with the WooCommerce Product Datasource, making it very easy for you to start selling your photographs online.', 'foogallery' ) .
						                   '<br />' . '<img src="https://assets.fooplugins.com/foogallery/foogallery-product-gallery.png" />' .
						                   '<br /><br />' . $this->build_promo_trial_html( 'product-gallery', __( 'PRO Commerce', 'foogallery' ) ) . '<br /><br />',
						'section'       => __( 'General', 'foogallery' ),
						'type'          => 'promo',
						'class'         => 'foogallery_promo_commerce',
						'keep_in_promo' => true,
						'cta'           => $this->build_cta_buttons( 'foogallery-product-gallery' )
					)
				)
			);

			return $gallery_templates;
		}
	}
}