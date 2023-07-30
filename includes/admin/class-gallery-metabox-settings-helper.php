<?php
/**
 * Created by bradvin
 * Date: 28/04/2017
 *
 */
if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Settings_Helper' ) ) {

	class FooGallery_Admin_Gallery_MetaBox_Settings_Helper {

		/**
		 * @var FooGallery
		 */
		private $gallery;

		/**
		 * @var bool
		 */
		private $hide_help;

		/**
		 * @var array
		 */
		public $gallery_templates;

		/**
		 * FooGallery_Admin_Gallery_MetaBox_Settings_Helper constructor.
		 * @param $gallery FooGallery
		 */
		function __construct($gallery) {
			$this->gallery = $gallery;
			$this->hide_help = 'on' == foogallery_get_setting( 'hide_gallery_template_help' );
			$this->hide_promo = 'on' == foogallery_get_setting( 'pro_promo_disabled' );

			$this->gallery_templates = foogallery_gallery_templates();

			$this->current_gallery_template = foogallery_default_gallery_template();
			if ( ! empty($this->gallery->gallery_template) ) {
				$this->current_gallery_template = $this->gallery->gallery_template;
			}
		}

		private function render_gallery_template_settings_tabs( $template, $sections ) {
			$tab_active = 'foogallery-tab-active';
			foreach ( $sections as $section_slug => $section ) {
				$subsection_active = '';
			    //if there are no fields then set the slug to the first subsection
				if ( isset( $section['subsections'] ) && count( $section['fields'] ) === 0 ) {
				    foreach ( $section['subsections'] as $subsection_slug => $subsection ) {
					    $section_slug = $subsection_slug;
					    $subsection_active = 'foogallery-tab-active';
					    break;
                    }
				}
			    ?>
				<div class="foogallery-vertical-tab <?php echo $tab_active; ?>"
					 data-name="<?php echo $template['slug']; ?>-<?php echo $section_slug; ?>">
					<span class="dashicons <?php echo $section['icon_class']; ?>"></span>
					<span class="foogallery-tab-text"><?php echo $section['name']; ?></span>
                    <?php
                    if ( isset( $section['subsections'] ) ) { ?>
                        <div class="foogallery-vertical-child-tabs">
                        <?php foreach ( $section['subsections'] as $subsection_slug => $subsection ) { ?>
                            <div class="foogallery-vertical-child-tab <?php echo $subsection_active; ?>" data-name="<?php echo esc_attr( $template['slug'] . '-' . $subsection_slug ); ?>">
                                <span class="foogallery-tab-text"><?php echo esc_html( $subsection['name'] ); ?></span>
                            </div>
                        <?php
	                        $subsection_active = '';
                            } ?>
                        </div>
                    <?php } ?>
				</div>
				<?php
				$tab_active = '';
			}
		}

		private function render_gallery_template_settings_tab_contents( $template, $sections, $tab_active = 'foogallery-tab-active' ) {
			foreach ( $sections as $section_slug => $section ) {
				$subsection_active = '';

				//if we are on an active section, then do the check for subsections
				if ( $tab_active === 'foogallery-tab-active' ) {
					//if there are no fields then set the slug to the first subsection
					if ( isset( $section['subsections'] ) && count( $section['fields'] ) === 0 ) {
						foreach ( $section['subsections'] as $subsection_slug => $subsection ) {
							$subsection_active = 'foogallery-tab-active';
							break;
						}
					}
				}

				?>
				<div class="foogallery-tab-content <?php echo $tab_active; ?>"
					 data-name="<?php echo $template['slug']; ?>-<?php echo $section_slug; ?>">
					<?php $this->render_gallery_template_settings_tab_contents_fields( $template, $section ); ?>
				</div>
				<?php
                if ( isset( $section['subsections'] ) ) {
                    $this->render_gallery_template_settings_tab_contents( $template, $section['subsections'], $subsection_active );
	                $subsection_active = '';
                }
				$tab_active = '';
			}
		}

		private function render_gallery_template_settings_tab_contents_fields( $template, $section ) {
			?>
			<table class="foogallery-metabox-settings">
				<tbody>
				<?php
				foreach ( $section['fields'] as $field ) {
					$field_type = isset( $field['type'] ) ? $field['type'] : 'unknown';
					$field_class ="foogallery_template_field foogallery_template_field_type-{$field_type} foogallery_template_field_id-{$field['id']} foogallery_template_field_template-{$template['slug']} foogallery_template_field_template_id-{$template['slug']}-{$field['id']}";
					$is_promo = array_key_exists( 'promo', $field );
					if ( $is_promo ) {
						$field_class .= ' foogallery_template_field_promo';
                    }
					if ( array_key_exists( 'class', $field ) ) {
						$field_class .= ' ' . $field['class'];
					}
					$field_row_data_html = '';
					if ( isset( $field['row_data'] ) ) {
						$field_row_data = array_map( 'esc_attr', $field['row_data'] );
						foreach ( $field_row_data as $field_row_data_name => $field_row_data_value ) {
							$field_row_data_html .= " $field_row_data_name=" . '"' . $field_row_data_value . '"';
						}
					}
					?>
					<tr class="<?php echo $field_class; ?>"<?php echo $field_row_data_html; ?>>
						<?php if ( 'help' === $field_type ) { ?>
							<td colspan="2">
								<div class="foogallery-help">
									<?php if ( array_key_exists( 'title', $field ) ) { ?>
									<?php echo '<strong>' . $field['title'] . '</strong><br /><br />'; ?>
									<?php } ?>
									<?php echo $field['desc']; ?>
								</div>
							</td>
						<?php } else if ( 'promo' === $field_type ) { ?>
                            <td colspan="2">
                                <div class="foogallery-promo">
	                                <button class="foogallery-admin-promo-dismiss notice-dismiss"></button>
	                                <?php echo '<strong>' . $field['title'] . '</strong><br /><br />'; ?>
									<?php
                                    echo $field['desc'];
									if ( array_key_exists( 'cta_text', $field ) ) {
									    echo '<a class="button-primary" href="' . $field['cta_link'] . '" target="_blank">' . $field['cta_text'] . '</a>';
                                    }
									if ( array_key_exists( 'cta', $field ) ) {
										foreach ( $field['cta'] as $cta ) {
											$button_class = isset( $cta['class'] ) ? $cta['class'] : 'button-primary';
											echo '<a class="' . $button_class . '" href="' . $cta['link'] . '" target="_blank">' . $cta['text'] . '</a>';
										}
									}
									?>
                                </div>
                            </td>
						<?php } else { ?>
							<th>
								<label for="FooGallerySettings_<?php echo $template['slug'] . '_' . $field['id']; ?>"><?php echo $field['title']; ?></label>
								<?php if ( $is_promo ) { ?>
                                    <span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php echo esc_attr($field['promo']); ?>"><i class="dashicons dashicons-star-filled"></i></span>
								<?php } ?>
                                <?php if ( !empty( $field['desc'] ) ) { ?>
									<span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php echo esc_attr($field['desc']); ?>"><i class="dashicons dashicons-editor-help"></i></span>
								<?php } ?>
							</th>
							<td>
								<?php do_action( 'foogallery_render_gallery_template_field', $field, $this->gallery, $template ); ?>
							</td>
						<?php } ?>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php
		}

		private function render_gallery_template_settings( $template ) {
			$sections = $this->build_model_for_template( $template );
			?>
			<div class="foogallery-settings">
				<div class="foogallery-vertical-tabs">
					<?php $this->render_gallery_template_settings_tabs( $template, $sections ); ?>
				</div>
				<div class="foogallery-tab-contents">
					<?php $this->render_gallery_template_settings_tab_contents( $template, $sections ); ?>
				</div>
			</div>
			<?php
		}

		public function render_gallery_settings() {
			foreach ( $this->gallery_templates as $template ) {
				$field_visibility = ($this->current_gallery_template !== $template['slug']) ? 'style="display:none"' : '';
				?><div
				class="foogallery-settings-container foogallery-settings-container-<?php echo $template['slug']; ?>"
				<?php echo $field_visibility; ?>>
				<?php $this->render_gallery_template_settings( $template ); ?>
				</div><?php
			}
		}

		/**
		 * build up and return a model that we can use to render the gallery settings
		 */
		private function build_model_for_template($template) {

		    $fields = foogallery_get_fields_for_template( $template );

			//create a sections array and fill it with fields
			$sections = array();
			foreach ( $fields as $field ) {

				if (isset($field['type']) && 'help' == $field['type'] && $this->hide_help) {
					continue; //skip help if the 'hide help' setting is turned on
				}

				if (isset($field['type']) && 'promo' == $field['type'] && $this->hide_promo) {
					continue; //skip promo if the 'hide promos' setting is turned on
				}

				$section_name = isset($field['section']) ? $field['section'] : __( 'General', 'foogallery' );

				$section_slug = apply_filters( 'foogallery_gallery_settings_metabox_section_slug', $section_name );

				if ( !isset( $sections[ $section_slug ] ) ) {
					$sections[ $section_slug ] = array (
						'name' => $section_name,
						'icon_class' => apply_filters( 'foogallery_gallery_settings_metabox_section_icon', $section_slug ),
						'fields' => array()
					);
				}

				if ( isset( $field['subsection'] ) ) {
				    $subsection = key( $field['subsection'] );
				    if ( !isset( $sections[ $section_slug ]['subsections'] ) ) {
					    $sections[ $section_slug ]['subsections'] = array();
				    }
				    if ( !array_key_exists( $subsection, $sections[ $section_slug ]['subsections'] ) ) {
					    $sections[ $section_slug ]['subsections'][$subsection] = array(
                            'name' => $field['subsection'][$subsection],
					        'fields' => array()
                        );
				    }
					$sections[ $section_slug ]['subsections'][$subsection]['fields'][] = $field;
				} else {
					$sections[ $section_slug ]['fields'][] = $field;
				}
			}

			return $sections;
		}

		public function render_hidden_gallery_template_selector() {
			?>
			<span class="hidden foogallery-template-selector"> &mdash;
				<select id="FooGallerySettings_GalleryTemplate" name="<?php echo FOOGALLERY_META_TEMPLATE; ?>">
                    <?php
					foreach ( $this->gallery_templates as $template ) {
						$selected = ($this->current_gallery_template === $template['slug']) ? 'selected' : '';

						$preview_css = '';
						if ( isset( $template['preview_css'] ) ) {
							if ( is_array( $template['preview_css'] ) ) {
								//dealing with an array of css files to include
								$preview_css = implode( ',', $template['preview_css'] );
							} else {
								$preview_css = $template['preview_css'];
							}
						}
						$preview_css = empty( $preview_css ) ? '' : ' data-preview-css="' . $preview_css . '" ';

						$mandatory_classes = '';
						if ( isset( $template['mandatory_classes'] ) ) {
							$mandatory_classes = ' data-mandatory-classes="' . $template['mandatory_classes'] . '" ';
						}

						echo "<option {$selected}{$preview_css}{$mandatory_classes} value=\"{$template['slug']}\">{$template['name']}</option>";
					}
					?>
                </select>
            </span>
			<?php
		}
	}
}