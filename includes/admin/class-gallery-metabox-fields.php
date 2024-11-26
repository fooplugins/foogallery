<?php

if ( ! class_exists( 'FooGallery_Admin_Gallery_MetaBox_Fields' ) ) {

	class FooGallery_Admin_Gallery_MetaBox_Fields {

		function __construct() {
			//render the different types of fields for our gallery settings
			add_action( 'foogallery_render_gallery_template_field', array( $this, 'render_gallery_template_field' ), 10, 3 );
		}

		/**
		 * Renders a gallery template field into the gallery settings metabox for a FooGallery
		 *
		 * @param array $field
		 * @param       $gallery FooGallery
		 * @param       $template
		 */
		function render_gallery_template_field( $field, $gallery, $template ) {
			$template_slug = $template['slug'];

			//only declare up front so no debug warnings are shown
			$type = $id = $desc = $default = $placeholder = $choices = $class = $spacer = $opactiy = null;

			extract( $field );

			$id = $template_slug . '_' . $id;

			$field['value'] = apply_filters( 'foogallery_render_gallery_template_field_value', $gallery->get_meta( $id, $default ), $field, $gallery, $template );

			$field_class = empty($class) ? '' : ' class="' . $class . '"';

			$field['choices'] = apply_filters( 'foogallery_render_gallery_template_field_choices', $choices, $field, $gallery );

			//allow for UI customization
			do_action( 'foogallery_render_gallery_template_field_before', $field, $gallery );

			echo '<div class="foogallery_metabox_field-' . $type . '">';

			switch ( $type ) {

				case 'html':
					echo wp_kses_post( $desc );
					$desc = '';
					break;

				case 'checkbox':
					if ( isset($gallery->settings[$id]) && $gallery->settings[$id] == 'on' ) {
						$field['value'] = 'on';
					} else if ( ! isset($gallery->settings) && $default == 'on' ) {
						$field['value'] = 'on';
					} else {
						$field['value'] = '';
					}

					$checked = 'on' === $field['value'] ? ' checked="checked"' : '';
					echo '<input' . esc_attr( $field_class ) . ' type="checkbox" id="FooGallerySettings_' . esc_attr( $id ) . '" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']" value="on"' . esc_attr( $checked ) . ' />';
					break;

				case 'select':
					echo '<select' . esc_attr( $field_class ) . ' id="FooGallerySettings_' . esc_attr( $id ) . '" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']">';
					foreach ( $choices as $value => $label ) {
						$selected = '';
						if ( $field['value'] == $value ) {
							$selected = ' selected="selected"';
						}
						echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_attr( $label ) . '</option>';
					}

					echo '</select>';
					break;

					case 'radio':
						$i = 0;
						$spacer = isset( $spacer ) ? $spacer : '<br />';
						foreach ( $choices as $value => $label ) {
							$selected = checked( $field['value'], $value, false );
							echo '<input' . esc_attr( $field_class ) . ' type="radio" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']" id="FooGallerySettings_' . esc_attr( $id . $i ) . '" value="' . esc_attr( $value ) . '"' . $selected . '>';
							echo '&nbsp;';
					
							$label_class = '';
							$label_icon = '';
							$label_tooltip = '';
							$label_tooltip_end = '';
					
							if ( is_array( $label ) ) {
								if ( isset( $label['class'] ) ) {
									$label_class = ' class="' . esc_attr( $label['class'] ) . '"';
								}
								if ( isset( $label['icon'] ) ) {
									$label_icon = '<i class="dashicons ' . esc_attr( $label['icon'] ) . '"></i>';
								}
								if ( isset( $label['tooltip'] ) ) {
									$label_tooltip = '<span data-balloon-length="large" data-balloon-pos="right" data-balloon="' . esc_attr( $label['tooltip'] ) . '">';
									$label_tooltip_end = '</span>';
								}
								$label = $label['label'];
							}
					
							echo '<label' . $label_class . ' for="FooGallerySettings_' . esc_attr( $id . $i ) . '">';
							echo wp_kses_post( $label_tooltip ) . esc_html( $label ) . wp_kses_post( $label_icon ) . wp_kses_post( $label_tooltip_end );
							echo '</label>';
					
							if ( $i < count( $choices ) - 1 ) {
								echo wp_kses( $spacer, array( 'br' => array(), 'span' => array( 'class' => array() ) ) );
							}
							$i++;
						}
						break;
					

				case 'textarea':
					echo '<textarea' . esc_attr( $field_class ) . ' id="FooGallerySettings_' . esc_attr( $id ) . '" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']" placeholder="' . $placeholder . '">' . esc_attr( $field['value'] ) . '</textarea>';

					break;

				case 'text':
					echo '<input' . esc_attr( $field_class ) . ' type="text" id="FooGallerySettings_' . esc_attr( $id ) . '" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']" value="' . esc_attr( $field['value'] ) . '" />';

					break;

				case 'colorpicker':

					$opacity_attribute = empty($opacity) ? '' : ' data-show-alpha="true"';

					echo '<input ' . esc_attr( $opacity_attribute ) . ' class="colorpicker" type="text" id="FooGallerySettings_' . esc_attr( $id ) . '" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']" value="' . esc_attr( $field['value'] ) . '" />';

					break;

				case 'number':
					$min = isset($min) ? $min : 0;
					$step = isset($step) ? $step : 1;
					echo '<input class="small-text ' . esc_attr( $class ) . '" type="number" step="' . esc_attr( $step ) . '" min="' . esc_attr( $min ) .'" id="FooGallerySettings_' . esc_attr( $id ) . '" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . ']" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( $field['value'] ) . '" />';

					break;

				case 'checkboxlist':
					$i = 0;
					foreach ( $choices as $value => $label ) {

						$checked = '';
						if ( isset($field['value'][$value]) && $field['value'][$value] == $value ) {
							$checked = 'checked="checked"';
						}

						echo '<input' . esc_attr( $field_class ) . ' ' . esc_attr( $checked ) . ' type="checkbox" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][' . esc_attr( $value ) . ']" id="FooGallerySettings_' . esc_attr( $id ) . esc_attr( $i ) . '" value="' . esc_attr( $value ) . '" data-value="' . esc_attr( $value ) . '"> <label for="FooGallerySettings_' . esc_attr( $id ) . esc_attr( $i ) . '">' . esc_attr( $label ) . '</label>';
						if ( $i < count( $choices ) - 1 ) {
							echo '<br />';
						}
						$i++;
					}

					break;
				case 'icon':
					$i = 0;
					$input_name = FOOGALLERY_META_SETTINGS . '[' . esc_attr( $id ) . ']';
					$icon_html = '';
					foreach ( $choices as $value => $icon ) {
						$selected = ( $field['value'] == $value ) ? ' checked="checked"' : '';
						$icon_html .= '<input style="display:none" name="' . esc_attr( $input_name ) . '" id="FooGallerySettings_' . esc_attr( $id ) . esc_attr( $i ) . '" ' . esc_attr( $selected ) . ' type="radio" value="' . esc_attr( $value ) . '" tabindex="' . esc_attr( $i ) . '"/>';
						$title = $icon['label'];
						$img = $icon['img'];
						$icon_html .= '<label for="FooGallerySettings_' . esc_attr( $id ) . esc_attr( $i ) . '" data-balloon-length="small" data-balloon-pos="down" data-balloon="' . esc_html( $title ) . '"><img src="' . esc_url( $img ). '" /></label>';
						$i++;
					}
					echo $icon_html;
					break;

				case 'htmlicon':
					$i = 0;
					$input_name = FOOGALLERY_META_SETTINGS . '[' . esc_attr( $id ) . ']';
					$icon_html = '';
					foreach ( $choices as $value => $icon ) {
						$selected = ( $field['value'] == $value ) ? ' checked="checked"' : '';
						$icon_html .= '<input style="display:none" name="' . $input_name. '" id="FooGallerySettings_' . esc_attr( $id ) . esc_attr( $i ) . '" ' . esc_attr( $selected ) . ' type="radio" value="' . esc_attr( $value ) . '" tabindex="' . esc_attr( $i ) . '"/>';
						$title = $icon['label'];
						$html = $icon['html'];
						$icon_html .= '<label for="FooGallerySettings_' . esc_attr( $id ) . esc_attr( $i ) . '" data-balloon-length="small" data-balloon-pos="down" data-balloon="' . $title . '">' . $html . '</label>';
						$i++;
					}
					echo $icon_html;
					break;

				case 'thumb_size':
					$width = is_array( $field['value'] ) ? $field['value']['width'] : 150;
					$height = is_array( $field['value'] ) ? $field['value']['height'] : 150;
					$crop = is_array( $field['value'] ) && array_key_exists( 'crop', $field['value'] ) ? $field['value']['crop'] : 0;
					$crop_checked = ( $crop == 1 ) ? ' checked="checked"' : '';
					echo '<label for="FooGallerySettings_' . esc_attr( $id ) . '_width">' . __( 'Width', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . esc_attr( $id ) . '_width" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][width]" value="' . esc_attr( $width ) . '" />';
					echo '<label for="FooGallerySettings_' . esc_attr( $id ) . '_width">' . __( 'Height', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . esc_attr( $id ) . '_height" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][height]" value="' . esc_attr( $height ) . '" />';
					echo '<div class="foogallery-thumbsize-crop"><input name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][crop]" type="hidden" id="FooGallerySettings_' . esc_attr( $id ) . '_nocrop" value="0" />';
					echo '<input name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][crop]" type="checkbox" id="FooGallerySettings_' . esc_attr( $id ) . '_crop" value="1"' . $crop_checked . '>';
					echo '<label for="FooGallerySettings_' . esc_attr( $id ) . '_crop">' . __( 'Crop thumbnail to exact dimensions', 'foogallery' ) . '</label></div>';
					break;

				case 'thumb_size_no_crop':
					$width = is_array( $field['value'] ) ? $field['value']['width'] : 150;
					$height = is_array( $field['value'] ) ? $field['value']['height'] : 150;
					echo '<label for="FooGallerySettings_' . esc_attr( $id ) . '_width">' . __( 'Width', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . esc_attr( $id ) . '_width" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][width]" value="' . esc_attr( $width ) . '" />';
					echo '<label for="FooGallerySettings_' . esc_attr( $id ) . '_width">' . __( 'Height', 'foogallery' ) . '</label>';
					echo '<input class="small-text" type="number" step="1" min="0" id="FooGallerySettings_' . esc_attr( $id ) . '_height" name="' . esc_attr( FOOGALLERY_META_SETTINGS ) . '[' . esc_attr( $id ) . '][height]" value="' . esc_attr( $height ) . '" />';
					break;

				default:
					do_action( 'foogallery_render_gallery_template_field_custom', $field, $gallery, $template );
					break;
			}

			if (!empty($suffix)) {
				echo $suffix;
			}

			echo '</div>';

			//allow for more customization
			do_action( 'foogallery_render_gallery_template_field_after', $field, $gallery );
		}
	}
}
