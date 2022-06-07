<?php
/**
 * Class: Jet_Wishlist_Count_Button
 * Name: Wishlist Count Button
 * Slug: jet-wishlist-count-button
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Wishlist_Count_Button extends Jet_CW_Base {

	public function get_name() {
		return 'jet-wishlist-count-button';
	}

	public function get_title() {
		return esc_html__( 'Wishlist Count Button', 'jet-cw' );
	}

	public function get_icon() {
		return 'jet-cw-icon-wishlist-count';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-adjust-the-wishlist-settings-for-woocommerce-shop-using-jetcomparewishlist/';
	}

	public function get_categories() {
		return array( 'jet-cw' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_button_content',
			array(
				'label' => esc_html__( 'Content', 'jet-cw' ),
			)
		);

		$this->__add_advanced_icon_control(
			'button_icon',
			array(
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-heart-o',
				'fa5_default' => array(
					'value'   => 'far fa-heart',
					'library' => 'fa-regular',
				),
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Wishlist', 'jet-cw' ),
			)
		);

		$this->add_control(
			'button_icon_settings_heading',
			array(
				'label'     => esc_html__( 'Icon', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'use_button_icon',
			array(
				'label'        => esc_html__( 'Use Icon?', 'jet-cw' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'button_icon_position',
			array(
				'label'       => esc_html__( 'Icon Position', 'jet-cw' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'left'   => esc_html__( 'Left', 'jet-cw' ),
					'top'    => esc_html__( 'Top', 'jet-cw' ),
					'right'  => esc_html__( 'Right', 'jet-cw' ),
					'bottom' => esc_html__( 'Bottom', 'jet-cw' ),
				),
				'default'     => 'left',
				'render_type' => 'template',
				'condition'   => array(
					'use_button_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'count_heading',
			[
				'label'     => __( 'Count', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_count',
			[
				'label'   => __( 'Show Count', 'jet-cw' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'hide_empty_count',
			[
				'label'     => __( 'Hide Empty', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'show_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'count_format',
			[
				'label'       => __( 'Format', 'jet-cw' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '%s',
				'description' => __( 'Display format for count items that added to wishlist', 'jet-cw' ),
				'condition'   => [
					'show_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'count_position',
			[
				'label'     => __( 'Position', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top-right',
				'options'   => [
					'top-right'     => __( 'Top Right', 'jet-cw' ),
					'center-right'  => __( 'Center Right', 'jet-cw' ),
					'bottom-right'  => __( 'Bottom Right', 'jet-cw' ),
					'bottom-center' => __( 'Bottom Center', 'jet-cw' ),
					'bottom-left'   => __( 'Bottom Left', 'jet-cw' ),
					'center-left'   => __( 'Center Left', 'jet-cw' ),
					'top-left'      => __( 'Top Left', 'jet-cw' ),
					'top-center'    => __( 'Top Center', 'jet-cw' ),
					'center'        => __( 'Center', 'jet-cw' ),
				],
				'condition' => [
					'show_count' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-wishlist-button/wishlist-count-button/css-scheme',
			array(
				'button'    => '.jet-wishlist-count-button__link',
				'container' => '.jet-wishlist-count-button__wrapper',
				'icon'      => '.jet-wishlist-count-button__icon',
				'count'     => '.jet-wishlist-count-button__count',
			)
		);

		$this->start_controls_section(
			'section_button_style',
			array(
				'label'      => esc_html__( 'General', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}}  ' . $css_scheme['button'],
			)
		);

		$this->add_control(
			'custom_size',
			array(
				'label'        => esc_html__( 'Custom Size', 'jet-cw' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'false',
			)
		);

		$this->add_responsive_control(
			'button_custom_width',
			array(
				'label'      => esc_html__( 'Custom Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'range'      => array(
					'px' => array(
						'min' => 40,
						'max' => 1000,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'custom_size' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'button_custom_height',
			array(
				'label'      => esc_html__( 'Custom Height', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 1000,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'custom_size' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab(
			'button_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'button_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'wishlist_button_normal_background',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'wishlist_button_hover_background',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_border_hover_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'button_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'button_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['button'],
			)
		);

		$this->add_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['container'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => __( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin',
			array(
				'label'      => __( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'button_icon_heading',
			array(
				'label'     => esc_html__( 'Icon', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'icon_font_size',
			array(
				'label'      => esc_html__( 'Size', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
					'em',
					'rem',
				),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'font-size: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_icon_styles' );

		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'normal_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'hover_icon_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['icon'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'icon_margin',
			array(
				'label'      => __( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'count_style_heading',
			array(
				'label'     => esc_html__( 'Count', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'count_size',
			array(
				'label'      => esc_html__( 'Size', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'count_width',
			array(
				'label'      => esc_html__( 'Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'count_height',
			array(
				'label'      => esc_html__( 'Height', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
					'em',
					'%',
				),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 200,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_count_styles' );

		$this->start_controls_tab(
			'tab_count_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'normal_count_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'normal_count_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_count_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'hover_count_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['count'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'hover_count_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['count'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'hover_count_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['count'] => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'count_border_border!' => '',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'count_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['count'],
			)
		);

		$this->add_control(
			'count_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'count_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['count'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	public static function render_callback( $settings = array() ) {

		$selector = 'a.jet-wishlist-count-button__link[data-widget-id="' . $settings['_widget_id'] . '"]';

		jet_cw()->widgets_store->store_widgets_types( 'jet-wishlist-count-button', $selector, $settings, 'wishlist' );

		echo '<div class="jet-wishlist-count-button__wrapper">';
		jet_cw_widgets_functions()->get_wishlist_count_button( $settings );
		echo '</div>';

	}

	protected function render() {

		$widget_id = $this->get_id();
		$settings  = $this->get_settings();

		$this->__context = 'render';

		$this->__open_wrap();

		$widget_settings = array(
			'button_icon_position' => $settings['button_icon_position'],
			'use_button_icon'      => $settings['use_button_icon'],
			'button_icon'          => htmlspecialchars( $this->__render_icon( 'button_icon', '%s', '', false ) ),
			'button_label'         => esc_html__( $settings['button_label'], 'jet-cw' ),
			'show_count'           => $settings['show_count'],
			'hide_empty_count'     => $settings['hide_empty_count'],
			'count_format'         => wp_kses_post( $settings['count_format'] ),
			'count_position'       => $settings['count_position'],
			'_widget_id'           => $widget_id,
		);

		echo self::render_callback( $widget_settings );

		$this->__close_wrap();

	}

}
