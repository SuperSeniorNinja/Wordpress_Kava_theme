<?php

namespace WeDevs\DokanPro\Modules\Elementor\Abstracts;

use WeDevs\DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Widget_Button;

abstract class DokanButton extends Widget_Button {

    use PositionControls;

    /**
     * Widget categories
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        parent::register_controls();

        $this->update_control(
            'icon_align',
            [
                'default' => 'right',
            ]
        );

        $this->update_control(
            'button_text_color',
            [
                'default' => dokan_get_option( 'btn_text', 'dokan_colors', '#ffffff' ),
            ]
        );

        $this->update_control(
            'background_color',
            [
                'default' => dokan_get_option( 'btn_primary', 'dokan_colors', '#f05025' ),
            ]
        );

        $this->update_control(
            'border_color',
            [
                'default' => dokan_get_option( 'btn_primary_border', 'dokan_colors', '#f05025' ),
            ]
        );

        $this->add_position_controls();
    }

    /**
     * Button wrapper class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_wrapper_class() {
        return 'dokan-btn-wrap';
    }

    /**
     * Set wrapper classes
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' ' . $this->get_button_wrapper_class() . ' elementor-widget-' . parent::get_name();
    }

    /**
     * Button class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_class() {
        return 'dokan-btn';
    }

    /**
     * Frontend render method
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        $this->add_render_attribute(
            'button',
            'class',
            [ $this->get_button_class() ]
        );

        parent::render();
    }
}
