<?php
/**
 * Class for adding advanced caption settings to all gallery templates
 * Date: 10/02/2020
 */
if ( ! class_exists( 'FooGallery_Pro_Advanced_Captions' ) ) {

    class FooGallery_Pro_Advanced_Captions {

        function __construct() {
            //add fields to all templates
            add_filter( 'foogallery_override_gallery_template_fields', array( $this, 'add_advanced_caption_fields' ), 100, 2 );

            //add custom captions
            add_filter( 'foogallery_build_attachment_html_caption_custom', array( $this, 'customize_captions' ), 30, 3 );
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
                'id'       => 'captions_type',
                'title'    => __( 'Caption Type', 'foogallery' ),
                'desc'     => __( 'What type of captions do you want to display in the gallery. By default, captions will be built up from the image attributes for both the caption title and description.', 'foogallery' ),
                'section'  => __( 'Captions', 'foogallery' ),
                'type'     => 'radio',
                'default'  => '',
                'choices'  => array(
                    ''  => __( 'Default (Captions will be built up from a title and description)', 'foogallery' ),
                    'custom'   => __( 'Custom (Captions will be built up using a custom caption template)', 'foogallery' ),
                ),
                'row_data'=> array(
                    'data-foogallery-change-selector' => 'input:radio',
                    'data-foogallery-preview' => 'shortcode',
                    'data-foogallery-value-selector'  => 'input:checked',
                )
            );

            $field_index = $this->find_index_of_field( $fields, 'caption_title_source' );

            array_splice( $fields, $field_index, 0, $new_fields );

            //change the existing captions fields to only show if the default caption type is chosen
            $caption_title_source_field = &$this->find_field( $fields, 'caption_title_source' );
            $caption_title_source_field['row_data'] = array(
                'data-foogallery-change-selector'       => 'input:radio',
                'data-foogallery-hidden'                => true,
                'data-foogallery-show-when-field'       => 'captions_type',
                'data-foogallery-show-when-field-value' => '',
                'data-foogallery-preview'               => 'shortcode'
            );

            $caption_desc_source_field = &$this->find_field( $fields, 'caption_desc_source' );
            $caption_desc_source_field['row_data'] = array(
                'data-foogallery-change-selector'       => 'input:radio',
                'data-foogallery-hidden'                => true,
                'data-foogallery-show-when-field'       => 'captions_type',
                'data-foogallery-show-when-field-value' => '',
                'data-foogallery-preview'               => 'shortcode'
            );

            $captions_limit_length_field = &$this->find_field( $fields, 'captions_limit_length' );
            $captions_limit_length_field['row_data'] = array(
                'data-foogallery-change-selector'       => 'input:radio',
                'data-foogallery-hidden'                => true,
                'data-foogallery-show-when-field'       => 'captions_type',
                'data-foogallery-show-when-field-value' => '',
                'data-foogallery-preview'               => 'shortcode'
            );

            //then add some more caption fields
            $fields[] = array(
                'id'      => 'caption_custom_template',
                'title'   => __( 'Custom Caption Template', 'foogallery' ),
                'desc'	  => __( 'The template used to build up the custom template.', 'foogallery '),
                'section' => __( 'Captions', 'foogallery' ),
                'type'    => 'textarea',
                'default' => '',
                'row_data' => array(
                    'data-foogallery-hidden'                => true,
                    'data-foogallery-show-when-field'       => 'captions_type',
                    'data-foogallery-show-when-field-value' => 'custom',
                    'data-foogallery-preview'               => 'shortcode'
                )
            );

            $fields[] = array(
                'id'      => 'caption_custom_help',
                'title'   => __( 'Custom Caption Help', 'foogallery' ),
                'desc'	  => __( '<strong>Custom Caption Help</strong><br />The custom caption template can use any HTML together with the following dynamic placeholders:<br /><br />' .
                    '<code>{{ID}}</code> - attachment ID<br />' .
                    '<code>{{title}}</code> - attachment title<br />' .
                    '<code>{{caption}}</code> - attachment caption<br />' .
                    '<code>{{description}}</code> - attachment description<br />' .
                    '<code>{{alt}}</code> - attachment ALT text<br />' .
                    '<code>{{custom_url}}</code> - custom URL<br />' .
                    '<code>{{custom_target}}</code> - custom target<br />' .
                    '<code>{{url}}</code> - full-size image URL<br />' .
                    '<code>{{width}}</code> - full-size image width<br />' .
                    '<code>{{height}}</code> - full-size image height<br /><br />' .
                    'You can also include custom attachment metadata by using <code>{{postmeta.metakey}}</code> where "metakey" is the key/slug/name of the metadata.', 'foogallery '),
                'section' => __( 'Captions', 'foogallery' ),
                'type'    => 'help',
                'row_data' => array(
                    'data-foogallery-hidden'                => true,
                    'data-foogallery-show-when-field'       => 'captions_type',
                    'data-foogallery-show-when-field-value' => 'custom',
                    'data-foogallery-preview'               => 'shortcode'
                )
            );

            return $fields;
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
         * Return the requested field
         *
         * @param $fields
         * @param $field_id
         *
         * @return array|bool
         */
        private function &find_field( &$fields, $field_id ) {
            foreach ( $fields as &$field ) {
                if ( isset( $field['id'] ) && $field_id === $field['id'] ) {
                    return $field;
                }
            }
            return false;
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
            $caption_type = foogallery_gallery_template_setting( 'captions_type', '' );

            if ( 'custom' === $caption_type ) {
                $captions = array();
                $template = foogallery_gallery_template_setting( 'caption_custom_template', '' );
                $captions['desc'] = $this->build_custom_caption( $template, $foogallery_attachment );
            }

            return $captions;
        }

        /**
         * Build up the custom caption based on the template
         *
         * @param $template
         * @param $foogallery_attachment FooGalleryAttachment
         * @return string
         */
        function build_custom_caption( $template, $foogallery_attachment ) {
            $html = $template;

            $html = preg_replace_callback( '/\{\{(.+?)\}\}/',
                function ($matches) use ($foogallery_attachment) {
                    $property = $matches[1];
                    if ( property_exists( $foogallery_attachment, $property ) ) {
                        return $foogallery_attachment->$property;
                    } else if ( strpos( $property, 'postmeta.' ) === 0 ) {
                        $post_meta_key = str_replace( 'postmeta.', '', $property );
                        $post_meta_value = get_post_meta( $foogallery_attachment->ID, $post_meta_key, true );

                        return $post_meta_value;
                    }

                    return '';
                },
                $html );

            return apply_filters( 'foogallery_build_custom_caption', $html, $template, $foogallery_attachment );
        }
    }
}