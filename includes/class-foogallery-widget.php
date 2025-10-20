<?php
/**
 * Widget to insert a FooGallery
 */

if ( ! class_exists( 'FooGallery_Widget_Init' ) ) {
    class FooGallery_Widget_Init
    {
        public function __construct()
        {
            add_action('widgets_init', array($this, 'register_widget'));
        }

        public function register_widget()
        {
            register_widget('FooGallery_Widget');
        }
    }
}

if ( ! class_exists( 'FooGallery_Widget' ) ) {
    class FooGallery_Widget extends WP_Widget
    {

        /**
         * Sets up the widgets name etc
         */
        public function __construct()
        {
            $widget_ops = array(
                'classname' => 'foogallery_widget',
                'description' => __('Insert a FooGallery', 'foogallery'),
            );

            parent::__construct('foogallery_widget', __('FooGallery Widget', 'foogallery'), $widget_ops);
        }


        /**
         * Outputs the content of the widget
         *
         * @param array $args
         * @param array $instance
         */
        public function widget($args, $instance)
        {
            // outputs the content of the widget
            echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            if (!empty($instance['title'])) {
                echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            //output the gallery here
            $foogallery_id = isset( $instance['foogallery_id'] ) ? intval( $instance['foogallery_id'] ) : 0;
            if ( $foogallery_id > 0 ) {
                foogallery_render_gallery( $foogallery_id );
            }

            echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }


        /**
         * Outputs the options form on admin
         *
         * @param array $instance The widget options
         * @return string|void
         */
        public function form($instance)
        {
            // outputs the options form on admin
            $title = !empty($instance['title']) ? $instance['title'] : __('New title', 'foogallery');
            $foogallery_id = !empty($instance['foogallery_id'])  ? intval($instance['foogallery_id']) : 0;
            $galleries = foogallery_get_all_galleries();
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'foogallery'); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text"
                       value="<?php echo esc_attr($title); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('foogallery_id') ); ?>"><?php esc_html_e('Select Gallery:', 'foogallery'); ?></label>
                <select class="widefat" id="<?php echo esc_attr( $this->get_field_id('foogallery_id') ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name('foogallery_id') ); ?>"
                       value="<?php echo esc_attr($title); ?>">
                    <?php foreach ( $galleries as $gallery ) {?>
                        <option <?php echo $gallery->ID == $foogallery_id ? 'selected="selected"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value="<?php echo esc_attr( $gallery->ID ); ?>"><?php echo esc_html( $gallery->name . ' [' . $gallery->ID . ']' ); ?></option>
                    <?php } ?>
                </select>
            </p>
            <?php
        }


        /**
         * Processing widget options on save
         *
         * @param array $new_instance The new options
         * @param array $old_instance The previous options
         * @return array|mixed
         */
        public function update($new_instance, $old_instance)
        {
            // processes widget options to be saved
            foreach ($new_instance as $key => $value) {
                $updated_instance[$key] = sanitize_text_field($value);
            }

            return $updated_instance;
        }
    }
}