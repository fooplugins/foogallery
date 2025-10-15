<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_FooGallery_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'foogallery';
    }

    /**
     * Get widget title.
     *
     * Retrieve widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'FooGallery', 'plugin-name' );
    }

    /**
     * Get widget icon.
     *
     * Retrieve oEmbed widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-gallery-justified';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the oEmbed widget belongs to.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'general' ];
    }

    /**
     * Build gallery select control options.
     *
     * Provides a shared list of galleries for both the control definition and
     * any runtime refresh requests coming from the editor UI.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array
     */
    public static function get_gallery_options() {
        $options = [
            '' => '',
        ];

        $galleries = foogallery_get_all_galleries();

        if ( empty( $galleries ) ) {
            return $options;
        }

        foreach ( $galleries as $gallery ) {
            $name = $gallery->name;
            if ( empty( $name ) ) {
                $name = 'Gallery #' . $gallery->ID;
            }

            $options[ $gallery->ID ] = $name;
        }

        return $options;
    }

    /**
     * Register oEmbed widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function _register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'foogallery' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $options = self::get_gallery_options();

        $this->add_control(
            'gallery_id',
            [
                'label' => __( 'Choose the gallery', 'foogallery' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $options
            ]
        );

        $this->add_control(
            'gallery_refresh',
            [
                'text' => esc_html__( 'Refresh Galleries', 'foogallery' ),
                'type' => \Elementor\Controls_Manager::BUTTON,
                'show_label' => false,
                'event' => 'foogallery:refresh',
            ]
        );

        $this->add_control(
            'gallery_edit',
            [
                'text' => esc_html__( 'Edit Gallery', 'foogallery' ),
                'type' => \Elementor\Controls_Manager::BUTTON,
                'show_label' => false,
                'event' => 'foogallery:edit',
                'condition' => [
                    'gallery_id!' => '',
                ],
            ]
        );

        $this->add_control(
            'gallery_add',
            [
                'text' => esc_html__( 'Add New Gallery', 'foogallery' ),
                'type' => \Elementor\Controls_Manager::BUTTON,
                'show_label' => false,
                'event' => 'foogallery:add',
                'condition' => [
                    'gallery_id!' => '',
                ],
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Render oEmbed widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $foogallery_id = intval( $settings['gallery_id'] );
        if ( $foogallery_id  > 0 ) {
            foogallery_render_gallery( $foogallery_id );
        } else if ( is_admin() ) {
            echo '<p>' . __( 'Please select a gallery to display.', 'foogallery' ) . '</p>';
        }
    }
}
