<?php
/**
 * Compare Integration Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Compare_Integration' ) ) {

	/**
	 * Define Jet_CW_Compare_Integration class
	 */
	class Jet_CW_Compare_Integration {

		/**
		 * Initialize integration hooks
		 *
		 * @return void
		 */
		public function __construct() {

			// Add compare buttons html to Products Grid widget from JetWooBuilder
			add_action( 'jet-woo-builder/templates/jet-woo-products/compare-button', array( $this, 'add_compare_button' ), 10, 1 );

			// Add compare buttons html to Products List widget from JetWooBuilder
			add_action( 'jet-woo-builder/templates/jet-woo-products-list/compare-button', array( $this, 'add_compare_button' ), 10, 1 );

			// Add compare buttons controls to Products Grid widget from JetWooBuilder
			add_action( 'elementor/element/jet-woo-products/section_dots_style/after_section_end', array( $this, 'register_compare_button_content_controls' ), 10, 2 );
			add_action( 'elementor/element/jet-woo-products/section_dots_style/after_section_end', array( $this, 'register_compare_button_style_controls' ), 10, 2 );

			add_action( 'elementor/element/jet-woo-products/section_general/before_section_end', array( $this, 'register_compare_button_show_control' ), 10, 2 );
			add_action( 'elementor/element/jet-woo-products-list/section_general/before_section_end', array( $this, 'register_compare_button_show_control' ), 10, 2 );

			// Add compare buttons controls to Products List widget from JetWooBuilder
			add_action( 'elementor/element/jet-woo-products-list/section_button_style/after_section_end', array( $this, 'register_compare_button_content_controls' ), 10, 2 );
			add_action( 'elementor/element/jet-woo-products-list/section_button_style/after_section_end', array( $this, 'register_compare_button_style_controls' ), 10, 2 );

			if ( filter_var( jet_cw()->settings->get( 'add_default_compare_button' ), FILTER_VALIDATE_BOOLEAN ) ) {
				// Add compare buttons style controls to Archive Products widget from ElementorPro
				add_action( 'elementor/element/woocommerce-archive-products/section_design_box/after_section_end', array( $this, 'register_compare_button_style_controls' ), 10, 2 );

				// Add compare button html to default WooCommerce content product template
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_compare_button_default' ), 11 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'add_compare_button_default' ), 31 );
			}

			// Processing compare button icons
			add_filter( 'jet-woo-builder/jet-woo-products-grid/settings', array( $this, 'compare_button_icon' ), 10, 2 );
			add_filter( 'jet-woo-builder/jet-woo-products-list/settings', array( $this, 'compare_button_icon' ), 10, 2 );

		}

		/**
		 * Add widgets compare button
		 *
		 * @param array $settings
		 */
		public function add_compare_button( $settings = array() ) {

			$widget_settings = array(
				'button_icon_position' => $settings['compare_button_icon_position'],
				'use_button_icon'      => $settings['compare_use_button_icon'],
				'button_icon_normal'   => $settings['selected_compare_button_icon_normal'],
				'button_label_normal'  => $settings['compare_button_label_normal'],
				'use_as_remove_button' => $settings['compare_use_as_remove_button'],
				'button_icon_added'    => $settings['selected_compare_button_icon_added'],
				'button_label_added'   => $settings['compare_button_label_added'],
				'_widget_id'           => $settings['_widget_id'],
			);

			jet_cw()->compare_render->render_compare_button( $widget_settings );

		}

		/**
		 * Returns wishlist button icon settings
		 *
		 * @param $settings
		 * @param $widget
		 *
		 * @return mixed
		 */
		public function compare_button_icon( $settings, $widget ) {

			if ( isset( $settings['selected_compare_button_icon_normal'] ) || isset( $settings['compare_button_icon_normal'] ) ) {
				$settings['selected_compare_button_icon_normal'] = htmlspecialchars( $widget->__render_icon( $settings, 'compare_button_icon_normal', '%s', '', false ) );
			}

			if ( isset( $settings['selected_compare_button_icon_added'] ) || isset( $settings['compare_button_icon_added'] ) ) {
				$settings['selected_compare_button_icon_added'] = htmlspecialchars( $widget->__render_icon( $settings, 'compare_button_icon_added', '%s', '', false ) );
			}

			return $settings;
		}

		/**
		 * Add default compare button
		 */
		public function add_compare_button_default() {

			$widget_settings = array(
				'button_icon_position' => 'left',
				'use_button_icon'      => false,
				'button_icon_normal'   => '',
				'button_label_normal'  => __( 'Add To Compare', 'jet-cw' ),
				'use_as_remove_button' => false,
				'button_icon_added'    => '',
				'button_label_added'   => __( 'View Compare', 'jet-cw' ),
				'_widget_id'           => 'default',
			);

			jet_cw()->compare_render->render_compare_button( $widget_settings );

		}

		/**
		 * Register wishlist button controls in Elementor editor
		 *
		 * @param       $obj
		 * @param array $args
		 */
		public function register_compare_button_content_controls( $obj = null, $args = array() ) {

			$obj->start_controls_section(
				'section_compare_content',
				array(
					'label' => esc_html__( 'Compare', 'jet-cw' ),
				)
			);

			$obj->start_controls_tabs( 'tabs_compare_button_content' );

			$obj->start_controls_tab(
				'tab_compare_button_content_normal',
				array(
					'label' => esc_html__( 'Normal', 'jet-cw' ),
				)
			);

			$obj->__add_advanced_icon_control(
				'compare_button_icon_normal',
				array(
					'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
					'type'        => Elementor\Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-compress',
					'fa5_default' => array(
						'value'   => 'fas fa-compress',
						'library' => 'fa-solid',
					),
				)
			);

			$obj->add_control(
				'compare_button_label_normal',
				array(
					'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
					'type'    => Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Add To Compare', 'jet-cw' ),
				)
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'tab_compare_button_content_added',
				array(
					'label' => esc_html__( 'Added', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'compare_use_as_remove_button',
				array(
					'label'        => esc_html__( 'Use as remove button', 'jet-cw' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
					'label_off'    => esc_html__( 'No', 'jet-cw' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$obj->__add_advanced_icon_control(
				'compare_button_icon_added',
				array(
					'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
					'type'        => Elementor\Controls_Manager::ICON,
					'label_block' => true,
					'file'        => '',
					'default'     => 'fa fa-check',
					'fa5_default' => array(
						'value'   => 'fas fa-check',
						'library' => 'fa-solid',
					),
				)
			);

			$obj->add_control(
				'compare_button_label_added',
				array(
					'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
					'type'    => Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'View Compare', 'jet-cw' ),
				)
			);

			$obj->end_controls_tab();

			$obj->end_controls_tabs();

			$obj->add_control(
				'compare_button_icon_settings_heading',
				array(
					'label'     => esc_html__( 'Icon', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$obj->add_control(
				'compare_use_button_icon',
				array(
					'label'        => esc_html__( 'Use Icon?', 'jet-cw' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
					'label_off'    => esc_html__( 'No', 'jet-cw' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$obj->add_control(
				'compare_button_icon_position',
				array(
					'label'       => esc_html__( 'Icon Position', 'jet-cw' ),
					'type'        => Elementor\Controls_Manager::SELECT,
					'options'     => array(
						'left'   => esc_html__( 'Left', 'jet-cw' ),
						'top'    => esc_html__( 'Top', 'jet-cw' ),
						'right'  => esc_html__( 'Right', 'jet-cw' ),
						'bottom' => esc_html__( 'Bottom', 'jet-cw' ),
					),
					'default'     => 'left',
					'render_type' => 'template',
					'condition'   => array(
						'compare_use_button_icon' => 'yes',
					),
				)
			);

			$obj->end_controls_section();

		}

		/**
		 * Register compare button styles controls in Elementor editor
		 *
		 * @param       $obj
		 * @param array $args
		 */
		public function register_compare_button_style_controls( $obj = null, $args = array() ) {

			$css_scheme = apply_filters(
				'jet-compare-button/compare-button/css-scheme',
				array(
					'added'        => '.added-to-compare',
					'container'    => '.jet-compare-button__container',
					'button'       => '.jet-compare-button__link',
					'plane_normal' => '.jet-compare-button__plane-normal',
					'plane_added'  => '.jet-compare-button__plane-added',
					'state_normal' => '.jet-compare-button__state-normal',
					'state_added'  => '.jet-compare-button__state-added',
					'icon_normal'  => '.jet-compare-button__state-normal .jet-compare-button__icon',
					'label_normal' => '.jet-compare-button__state-normal .jet-compare-button__label',
					'icon_added'   => '.jet-compare-button__state-added .jet-compare-button__icon',
					'label_added'  => '.jet-compare-button__state-added .jet-compare-button__label',
				)
			);

			$obj->start_controls_section(
				'section_button_compare_general_style',
				array(
					'label'      => esc_html__( 'Compare', 'jet-cw' ),
					'tab'        => Elementor\Controls_Manager::TAB_STYLE,
					'show_label' => false,
				)
			);

			$obj->add_group_control(
				Elementor\Group_Control_Typography::get_type(),
				array(
					'name'     => 'compare_button_typography',
					'scheme'   => Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} ' . $css_scheme['button'] . ',{{WRAPPER}} ' . $css_scheme['label_normal'] . ',{{WRAPPER}} ' . $css_scheme['label_added'],
				)
			);

			$obj->add_control(
				'compare_custom_size',
				array(
					'label'        => esc_html__( 'Custom Size', 'jet-cw' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
					'label_off'    => esc_html__( 'No', 'jet-cw' ),
					'return_value' => 'yes',
					'default'      => 'false',
				)
			);

			$obj->add_responsive_control(
				'compare_button_custom_width',
				array(
					'label'      => esc_html__( 'Custom Width', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::SLIDER,
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
						'compare_custom_size' => 'yes',
					),
				)
			);

			$obj->add_responsive_control(
				'compare_button_custom_height',
				array(
					'label'      => esc_html__( 'Custom Height', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::SLIDER,
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
						'compare_custom_size' => 'yes',
					),
				)
			);

			$obj->start_controls_tabs( 'compare_button_style_tabs' );

			$obj->start_controls_tab(
				'compare_button_normal_styles',
				array(
					'label' => esc_html__( 'Normal', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'compare_button_normal_color',
				array(
					'label'     => esc_html__( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['label_normal'] => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['icon_normal']  => 'color: {{VALUE}}',
					),
				)
			);

			$obj->add_control(
				'compare_button_normal_background',
				array(
					'label'     => esc_html__( 'Background Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Elementor\Core\Schemes\Color::get_type(),
						'value' => Elementor\Core\Schemes\Color::COLOR_1,
					),
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['button'] . ' ' . $css_scheme['plane_normal'] => 'background-color: {{VALUE}}',
					),
				)
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'compare_button_hover_styles',
				array(
					'label' => esc_html__( 'Hover', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'compare_button_hover_color',
				array(
					'label'     => esc_html__( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['label_normal'] => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['icon_normal']  => 'color: {{VALUE}}',
					),
				)
			);

			$obj->add_control(
				'compare_button_hover_background',
				array(
					'label'     => esc_html__( 'Background Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Elementor\Core\Schemes\Color::get_type(),
						'value' => Elementor\Core\Schemes\Color::COLOR_4,
					),
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['plane_normal'] => 'background-color: {{VALUE}}',
					),
				)
			);

			$obj->add_control(
				'compare_button_border_hover_color',
				array(
					'label'     => esc_html__( 'Border Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['plane_normal'] => 'border-color: {{VALUE}}',
					),
					'condition' => array(
						'compare_button_border_border!' => '',
					),
				)
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'compare_button_added_styles',
				array(
					'label' => esc_html__( 'Added', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'compare_button_added_color',
				array(
					'label'     => esc_html__( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['added'] . $css_scheme['button']                                    => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['added'] . $css_scheme['button'] . ' ' . $css_scheme['label_added'] => 'color: {{VALUE}}',
						'{{WRAPPER}} ' . $css_scheme['added'] . ' ' . $css_scheme['icon_added']                          => 'color: {{VALUE}}',
					),
				)
			);

			$obj->add_control(
				'compare_button_added_background',
				array(
					'label'     => esc_html__( 'Background Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Elementor\Core\Schemes\Color::get_type(),
						'value' => Elementor\Core\Schemes\Color::COLOR_4,
					),
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['added'] . ' ' . $css_scheme['plane_added'] => 'background-color: {{VALUE}}',
					),
				)
			);

			$obj->add_control(
				'compare_button_added_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['added'] . ' ' . $css_scheme['plane_added'] => 'border-color: {{VALUE}}',
					),
					'condition' => array(
						'compare_button_border_border!' => '',
					),
				)
			);

			$obj->end_controls_tab();

			$obj->end_controls_tabs();

			$obj->add_group_control(
				Elementor\Group_Control_Border::get_type(),
				array(
					'name'        => 'compare_button_border',
					'label'       => esc_html__( 'Border', 'jet-cw' ),
					'placeholder' => '1px',
					'default'     => '1px',
					'selector'    => '{{WRAPPER}} ' . $css_scheme['plane_normal'] . ', ' . '{{WRAPPER}} ' . $css_scheme['plane_added'],
				)
			);

			$obj->add_control(
				'compare_button_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} ' . $css_scheme['button']       => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} ' . $css_scheme['plane_normal'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}} ' . $css_scheme['plane_added']  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$obj->add_responsive_control(
				'compare_button_alignment',
				array(
					'label'     => esc_html__( 'Alignment', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::CHOOSE,
					'default'   => 'center',
					'options'   => jet_cw_tools()->get_available_flex_horizontal_alignment(),
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['container'] => 'justify-content: {{VALUE}};',
					),
					'separator' => 'before',
				)
			);

			$obj->add_responsive_control(
				'compare_button_padding',
				array(
					'label'      => __( 'Padding', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} ' . $css_scheme['button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$obj->add_responsive_control(
				'compare_button_margin',
				array(
					'label'      => __( 'Margin', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} ' . $css_scheme['button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$obj->add_control(
				'compare_button_icon_heading',
				array(
					'label'     => esc_html__( 'Icon', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$obj->start_controls_tabs( 'tabs_compare_icon_styles' );

			$obj->start_controls_tab(
				'tab_compare_icon_normal',
				array(
					'label' => esc_html__( 'Normal', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'normal_compare_icon_color',
				array(
					'label'     => esc_html__( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['icon_normal'] => 'color: {{VALUE}}',
					),
				)
			);

			$obj->add_responsive_control(
				'normal_compare_icon_font_size',
				array(
					'label'      => esc_html__( 'Font Size', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::SLIDER,
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
						'{{WRAPPER}} ' . $css_scheme['icon_normal'] => 'font-size: {{SIZE}}{{UNIT}}',
					),
				)
			);

			$obj->add_responsive_control(
				'normal_compare_icon_margin',
				array(
					'label'      => __( 'Margin', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} ' . $css_scheme['icon_normal'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'tab_compare_icon_hover',
				array(
					'label' => esc_html__( 'Hover', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'compare_icon_color_hover',
				array(
					'label'     => esc_html__( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['button'] . ':hover ' . $css_scheme['icon_normal'] => 'color: {{VALUE}}',
					),
				)
			);

			$obj->end_controls_tab();

			$obj->start_controls_tab(
				'tab_compare_icon_added',
				array(
					'label' => esc_html__( 'Added', 'jet-cw' ),
				)
			);

			$obj->add_control(
				'compare_icon_color_added',
				array(
					'label'     => esc_html__( 'Color', 'jet-cw' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $css_scheme['added'] . $css_scheme['button'] . ' ' . $css_scheme['icon_added'] => 'color: {{VALUE}}',
					),
				)
			);

			$obj->add_responsive_control(
				'compare_icon_font_size_added',
				array(
					'label'      => esc_html__( 'Font Size', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::SLIDER,
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
						'{{WRAPPER}} ' . $css_scheme['icon_added'] => 'font-size: {{SIZE}}{{UNIT}}',
					),
				)
			);

			$obj->add_responsive_control(
				'compare_icon_margin_added',
				array(
					'label'      => __( 'Margin', 'jet-cw' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array(
						'{{WRAPPER}} ' . $css_scheme['icon_added'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$obj->end_controls_tab();

			$obj->end_controls_tabs();

			$obj->end_controls_section();

		}

		/**
		 * Register compare button display controls
		 *
		 * @param       $obj
		 * @param array $args
		 */
		public function register_compare_button_show_control( $obj = null, $args = array() ) {

			$obj->add_control(
				'show_compare',
				array(
					'label'        => esc_html__( 'Show Compare', 'jet-cw' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
					'label_off'    => esc_html__( 'No', 'jet-cw' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$obj->add_responsive_control(
				'compare_button_order',
				array(
					'type'      => Elementor\Controls_Manager::NUMBER,
					'label'     => esc_html__( 'Compare Button Order', 'jet-cw' ),
					'default'   => 1,
					'min'       => 1,
					'max'       => 10,
					'step'      => 1,
					'selectors' => array(
						'{{WRAPPER}} ' . '.jet-compare-button__container' => 'order: {{VALUE}}',
					),
				)
			);

		}

	}

}