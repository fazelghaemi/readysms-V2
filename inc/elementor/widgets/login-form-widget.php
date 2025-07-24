<?php
defined( 'ABSPATH' ) || exit;

class Elementor_ReadySMS_Login_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'readysms_login_form';
    }

    public function get_title() {
        return __('ReadySMS Login Form', 'readysms');
    }

    public function get_icon() {
        return 'eicon-accordion readysms-el-icon';
    }

    public function get_categories() {
        return ['readysms_category'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'readysms' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'width-avatar',
            [
                'label' => esc_html__( 'Width', 'readysms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 96,
                ],
            ]
        );

        $this->add_control(
            'border-radius',
            [
                'label' => esc_html__( 'Margin', 'readysms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'default' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .readysms-profile-general img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $size = !empty($settings['width-avatar']['size']) ? $settings['width-avatar']['size'] : 96;
        if(empty($user_id)) {
            global $current_user;
            $user_id = $current_user->ID;
        }
        ?>
        <?php woocommerce_login_form([]); ?>
        <?php
        if (is_admin()) {
            echo '<style>.elementor-widget-readysms_login_form .elementor-widget-container{min-height: 1px;}
            </style>';
        }
    }

    protected function content_template() {}

}