<?php
/**
 * Class: Jet_Wishlist_Widget
 * Name: Wishlist
 * Slug: jet-wishlist
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Wishlist_Widget extends Jet_CW_Base {

	public function get_name() {
		return 'jet-wishlist';
	}

	public function get_title() {
		return esc_html__( 'Wishlist', 'jet-cw' );
	}

	public function get_icon() {
		return 'jet-cw-icon-wishlist';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-adjust-the-wishlist-settings-for-woocommerce-shop-using-jetcomparewishlist/';
	}

	public function get_categories() {
		return array( 'jet-cw' );
	}

	protected function register_controls() {

		$css_scheme = apply_filters(
			'jet-compare-wishlist/jet-wishlist/css-scheme',
			array(
				'row'                    => '.cw-col-row',
				'cols'                   => '.cw-col-row > div',
				'item'                   => '.jet-wishlist .jet-wishlist-item',
				'item-content'           => '.jet-wishlist-item__content',
				'item-thumbnail'         => '.jet-cw-thumbnail',
				'item-thumbnail-wrapper' => '.jet-wishlist-item__thumbnail',
				'item-categories'        => '.jet-wishlist .jet-cw-categories',
				'item-sku'               => '.jet-wishlist .jet-cw-sku',
				'item-stock'             => '.jet-wishlist .jet-cw-stock-status',
				'item-in-stock'          => '.jet-wishlist .jet-cw-stock-status .available-on-backorder',
				'item-out-of-stock'      => '.jet-wishlist .jet-cw-stock-status .out-of-stock',
				'item-title'             => '.jet-wishlist .jet-cw-product-title',
				'item-price'             => '.jet-wishlist .jet-cw-price',
				'item-excerpt'           => '.jet-wishlist .jet-cw-excerpt',
				'item-currency'          => '.jet-wishlist .jet-cw-price .woocommerce-Price-currencySymbol',
				'item-rating'            => '.jet-wishlist .jet-cw-rating-stars',
				'item-button-wrapper'    => '.jet-cw-add-to-cart',
				'item-button'            => '.jet-cw-add-to-cart .button',
				'item-remove-button'     => '.jet-cw-remove-button.jet-wishlist-item-remove-button',
				'item-tags'              => '.jet-wishlist .jet-cw-tags',
				'overlay'                => '.jet-wishlist .jet-wishlist-product-img-overlay',
				'empty-text'             => '.jet-wishlist-empty',
			)
		);

		$columns = jet_cw_tools()->get_select_range( 6 );

		$this->start_controls_section(
			'section_general_style',
			array(
				'label'      => esc_html__( 'General', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			)
		);

		$this->add_control(
			'presets',
			[
				'type'    => 'select',
				'label'   => esc_html__( 'Product Presets', 'jet-cw' ),
				'default' => 'preset-1',
				'options' => [
					'preset-1'  => esc_html__( 'Preset 1', 'jet-cw' ),
					'preset-2'  => esc_html__( 'Preset 2', 'jet-cw' ),
					'preset-3'  => esc_html__( 'Preset 3', 'jet-cw' ),
					'preset-4'  => esc_html__( 'Preset 4', 'jet-cw' ),
					'preset-5'  => esc_html__( 'Preset 5', 'jet-cw' ),
					'preset-6'  => esc_html__( 'Preset 6', 'jet-cw' ),
					'preset-7'  => esc_html__( 'Preset 7 ', 'jet-cw' ),
					'preset-8'  => esc_html__( 'Preset 8 ', 'jet-cw' ),
					'preset-9'  => esc_html__( 'Preset 9 ', 'jet-cw' ),
					'preset-10' => esc_html__( 'Preset 10', 'jet-cw' ),
				],
			]
		);

		$this->add_responsive_control(
			'wishlist_columns',
			array(
				'label'   => esc_html__( 'Columns', 'jet-cw' ),
				'type'    => Controls_Manager::SELECT,
				'desktop_default'    => 3,
				'tablet_default'     => 2,
				'mobile_default'     => 1,
				'options' => $columns,
				'selectors'          => [
					'{{WRAPPER}} .jet-wishlist .jet-woo-products__item' => '--columns: {{VALUE}}',
				],
			)
		);

		$this->add_control(
			'equal_height_cols',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Equal Columns Height', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'empty_wishlist_text',
			array(
				'label'       => esc_html__( 'Empty Wishlist Text', 'jet-cw' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'No products were added to the wishlist.', 'jet-cw' ),
			)
		);

		$this->add_control(
			'item_content_heading',
			array(
				'label'     => esc_html__( 'Content Options', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'show_item_title',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Title', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'title_html_tag',
			[
				'label'     => esc_html__( 'Title HTML Tag', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h5',
				'options'   => jet_cw_tools()->get_available_title_html_tags(),
				'condition' => [
					'show_item_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_trim_type',
			[
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Title Trim Type', 'jet-cw' ),
				'default'   => 'word',
				'options'   => [
					'word'    => esc_html__( 'Words', 'jet-woo-builder' ),
					'letters' => esc_html__( 'Letters', 'jet-woo-builder' ),
				],
				'condition' => [
					'show_item_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_length',
			[
				'type'        => Controls_Manager::NUMBER,
				'label'       => esc_html__( 'Title Words/Letters Count', 'jet-cw' ),
				'description' => esc_html__( 'Set -1 to show full title and 0 to hide it.', 'jet-cw' ),
				'min'         => -1,
				'default'     => -1,
				'condition'   => [
					'show_item_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_tooltip',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Enable Title Tooltip', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
				'conditions'   => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'title_length',
							'operator' => '>',
							'value'    => 0,
						],
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'cw_thumbnail_size',
				'default' => 'thumbnail',
			)
		);

		$this->add_control(
			'enable_image_overlay',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Enable Image Overlay', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'thumbnail_position',
			array(
				'label'     => esc_html__( 'Position', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => esc_html__( 'Default', 'jet-cw' ),
					'left'    => esc_html__( 'Left', 'jet-cw' ),
					'right'   => esc_html__( 'Right', 'jet-cw' ),
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_control(
			'show_item_rating',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Rating', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'cw_rating_icon',
			array(
				'label'     => esc_html__( 'Rating Icon', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'jetcomparewishlist-icon-rating-1',
				'options'   => jet_cw_tools()->get_available_rating_icons_list(),
				'condition' => [
					'show_item_rating' => 'yes',
				],
			)
		);

		$this->add_control(
			'show_item_excerpt',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Excerpt', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_item_categories',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Categories', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_item_tags',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Tags', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_item_price',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Price', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_item_stock_status',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Stock Status', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_item_sku',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show SKU', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'show_item_button',
			[
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Add to Cart Button', 'jet-cw' ),
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		$this->wishlist_columns_styles( $css_scheme );

		$this->wishlist_item_styles( $css_scheme );

		$this->wishlist_thumbnail_styles( $css_scheme );

		$this->wishlist_categories_styles( $css_scheme );

		$this->wishlist_sku_styles( $css_scheme );

		$this->wishlist_stock_status_styles( $css_scheme );

		$this->wishlist_title_styles( $css_scheme );

		$this->wishlist_price_styles( $css_scheme );

		$this->wishlist_excerpt_styles( $css_scheme );

		$this->wishlist_rating_styles( $css_scheme );

		$this->wishlist_add_to_cart_styles( $css_scheme );

		$this->wishlist_remove_button_styles( $css_scheme );

		$this->wishlist_tags_styles( $css_scheme );

		$this->wishlist_overlay_styles( $css_scheme );

		$this->wishlist_empty_text_styles( $css_scheme );
	}

	protected function render() {

		$settings = $this->get_settings();

		$widget_settings = array(
			'presets'                 => $settings['presets'],
			'empty_wishlist_text'     => esc_html__( $settings['empty_wishlist_text'], 'jet-cw' ),
			'remove_button_text'      => $settings['remove_button_text'],
			'wishlist_remove_icon'    => htmlspecialchars( $this->__render_icon( 'remove_button_icon', '%s', '', false ) ),
			'equal_height_cols'       => $settings['equal_height_cols'],
			'show_item_title'         => $settings['show_item_title'],
			'title_html_tag'          => jet_cw_tools()->sanitize_html_tag( $settings['title_html_tag'] ),
			'enable_image_overlay'    => $settings['enable_image_overlay'],
			'title_trim_type'         => $settings['title_trim_type'],
			'title_length'            => $settings['title_length'],
			'title_tooltip'           => $settings['title_tooltip'],
			'thumbnail_position'      => $settings['thumbnail_position'],
			'cw_thumbnail_size_size'  => $settings['cw_thumbnail_size_size'],
			'show_item_rating'        => $settings['show_item_rating'],
			'cw_rating_icon'          => $settings['cw_rating_icon'],
			'wishlist_columns'        => $settings['wishlist_columns'],
			'show_item_excerpt'       => $settings['show_item_excerpt'],
			'show_item_categories'    => $settings['show_item_categories'],
			'show_item_tags'          => $settings['show_item_tags'],
			'show_item_price'         => $settings['show_item_price'],
			'show_item_stock_status'  => $settings['show_item_stock_status'],
			'show_item_sku'           => $settings['show_item_sku'],
			'show_item_button'        => $settings['show_item_button'],
			'_widget_id'              => $this->get_id(),
		);

		$selector = 'div.jet-wishlist__content[data-widget-id="' . $widget_settings['_widget_id'] . '"]';

		jet_cw()->widgets_store->store_widgets_types( 'jet-wishlist', $selector, $widget_settings, 'wishlist' );

		$this->__context = 'render';

		$this->__open_wrap();

		jet_cw_widgets_functions()->get_widget_wishlist( $widget_settings );

		$this->__close_wrap();

	}

	public function wishlist_columns_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_columns_style',
			array(
				'label'      => esc_html__( 'Columns', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'columns_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['cols'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['row']  => 'margin-left: -{{LEFT}}{{UNIT}}; margin-right:-{{RIGHT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_item_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_item_style',
			array(
				'label'      => esc_html__( 'Item', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'item_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'item_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['item'],
			)
		);

		$this->add_control(
			'item_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'item_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item'],
			)
		);

		$this->add_responsive_control(
			'item_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_control(
			'content_heading',
			array(
				'label'     => esc_html__( 'Content', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'item_content_vert_align',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Top', 'jet-cw' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => esc_html__( 'Middle', 'jet-cw' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Bottom', 'jet-cw' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-content'] => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_thumbnail_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_thumbnail_style',
			array(
				'label'      => esc_html__( 'Thumbnail', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'thumbnail_width',
			array(
				'label'      => esc_html__( 'Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
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
				'default'    => array(
					'size' => 150,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . '.jet-wishlist-thumbnail-left ' . $css_scheme['item-thumbnail-wrapper']  => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . '.jet-wishlist-thumbnail-right ' . $css_scheme['item-thumbnail-wrapper'] => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'thumbnail_position!' => 'default',
				),
			)
		);

		$this->add_control(
			'thumbnail_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-thumbnail'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'thumbnail_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['item-thumbnail'],
			)
		);

		$this->add_control(
			'thumbnail_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-thumbnail'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'thumbnail_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-thumbnail'],
			)
		);

		$this->add_responsive_control(
			'thumbnail_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-thumbnail'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'thumbnail_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-thumbnail'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnail_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-thumbnail'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_responsive_control(
			'thumbnail_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-thumbnail'] => 'order: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_categories_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_categories_style',
			array(
				'label'      => esc_html__( 'Categories', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'categories_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-categories'] . ', {{WRAPPER}} ' . $css_scheme['item-categories'] . ' a',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'categories_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-categories'] . ', {{WRAPPER}} ' . $css_scheme['item-categories'] . ' a',
			)
		);

		$this->start_controls_tabs( 'categories_style_tabs' );

		$this->start_controls_tab(
			'categories_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'categories_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-categories'] . ' a' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['item-categories']        => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'categories_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'categories_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-categories'] . ' a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'categories_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-categories'] => 'order: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'categories_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-categories'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_sku_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_sku_style',
			array(
				'label'      => esc_html__( 'SKU', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sku_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-sku'],
			)
		);

		$this->add_control(
			'sku_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-sku'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'sku_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-sku'],
			)
		);

		$this->add_responsive_control(
			'sku_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-sku'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'sku_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-sku'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_stock_status_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_stock_style',
			array(
				'label'      => esc_html__( 'Stock Status', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'stock_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-stock'] . ' .stock',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'stock_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-stock'],
			)
		);

		$this->start_controls_tabs( 'stock_style_tabs' );

		$this->start_controls_tab(
			'on_backorder_styles',
			array(
				'label' => esc_html__( 'On Backorder', 'jet-cw' ),
			)
		);

		$this->add_control(
			'on_backorder_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-in-stock'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'out_of_stock_styles',
			array(
				'label' => esc_html__( 'Out Of Stock', 'jet-cw' ),
			)
		);

		$this->add_control(
			'out_of_stock_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-out-of-stock'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'stock_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-stock'] => 'order: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'stock_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-stock'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_title_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_title_style',
			array(
				'label'      => esc_html__( 'Title', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'show_item_title' => 'yes',
				],
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-title'],
			)
		);

		$this->start_controls_tabs( 'title_style_tabs' );

		$this->start_controls_tab(
			'title_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'title_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-title'] . ' a' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'title_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-title'] . ' a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-title'],
			)
		);

		$this->add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-title'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'title_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-title'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_responsive_control(
			'title_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-title'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_price_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_price_style',
			array(
				'label'      => esc_html__( 'Price', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'show_item_price' => 'yes',
				],
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-price'],
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'price_space_between',
			array(
				'label'     => esc_html__( 'Space Between Prices', 'jet-cw' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del+ins' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_price_style' );

		$this->start_controls_tab(
			'tab_price_regular',
			array(
				'label' => __( 'Regular', 'jet-cw' ),
			)
		);

		$this->add_control(
			'price_regular_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'price_regular_decoration',
			array(
				'label'     => esc_html__( 'Text Decoration', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'line-through',
				'options'   => jet_cw_tools()->get_available_text_decoration_styles(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del' => 'text-decoration: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'price_regular_size',
			array(
				'label'     => esc_html__( 'Size', 'jet-cw' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 90,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'price_regular_weight',
			array(
				'label'     => esc_html__( 'Font Weight', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '400',
				'options'   => jet_cw_tools()->get_available_font_weight_styles(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del' => 'font-weight: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_price_sale',
			array(
				'label' => __( 'Sale', 'jet-cw' ),
			)
		);

		$this->add_control(
			'price_sale_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' ins' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'price_sale_decoration',
			array(
				'label'     => esc_html__( 'Text Decoration', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => jet_cw_tools()->get_available_text_decoration_styles(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' ins' => 'text-decoration: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'price_sale_size',
			array(
				'label'     => esc_html__( 'Size', 'jet-cw' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 90,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' ins' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'price_sale_weight',
			array(
				'label'     => esc_html__( 'Font Weight', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '400',
				'options'   => jet_cw_tools()->get_available_font_weight_styles(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' ins' => 'font-weight: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'price_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'price_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_responsive_control(
			'price_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_control(
			'currency_sign_heading',
			array(
				'label'     => esc_html__( 'Currency Sign', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'currency_sign_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-currency'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'currency_sign_size',
			array(
				'label'     => esc_html__( 'Size', 'jet-cw' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 90,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-currency'] => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'currency_sign_vertical_align',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => jet_cw_tools()->verrtical_align_attr(),
				'default'   => 'baseline',
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-currency'] => 'vertical-align: {{VALUE}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_currency_sign_style' );

		$this->start_controls_tab(
			'tab_currency_sign_regular',
			array(
				'label' => __( 'Regular', 'jet-cw' ),
			)
		);

		$this->add_control(
			'currency_sign_color_regular',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del .woocommerce-Price-currencySymbol' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'currency_sign_size_regular',
			array(
				'label'     => esc_html__( 'Size', 'jet-cw' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 90,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del .woocommerce-Price-currencySymbol' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_currency_sign_sale',
			array(
				'label' => esc_html__( 'Sale', 'jet-cw' ),
			)
		);

		$this->add_control(
			'currency_sign_color_sale',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' ins .woocommerce-Price-currencySymbol' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'currency_sign_size_sale',
			array(
				'label'     => esc_html__( 'Size', 'jet-cw' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 6,
						'max' => 90,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' ins .woocommerce-Price-currencySymbol' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function wishlist_excerpt_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_excerpt_style',
			array(
				'label'      => esc_html__( 'Excerpt', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-excerpt'],
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-excerpt'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'excerpt_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-excerpt'],
			)
		);

		$this->add_responsive_control(
			'excerpt_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-excerpt'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'excerpt_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-excerpt'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_rating_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_rating_styles',
			array(
				'label'      => esc_html__( 'Rating', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'show_item_rating' => 'yes',
				],
			)
		);

		$this->add_responsive_control(
			'rating_font_size',
			array(
				'label'      => esc_html__( 'Font Size (px)', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 16,
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] . ' .product-rating__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_rating_styles' );

		$this->start_controls_tab(
			'tab_rating_all',
			array(
				'label' => esc_html__( 'All', 'jet-cw' ),
			)
		);

		$this->add_control(
			'rating_color_all',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a1a2a4',
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] . ' .product-rating__icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_rating_rated',
			array(
				'label' => esc_html__( 'Rated', 'jet-cw' ),
			)
		);

		$this->add_control(
			'rating_color_rated',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fdbc32',
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] . ' > .product-rating__icon.active' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'rating_space_between',
			array(
				'label'      => esc_html__( 'Space Between Stars (px)', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 2,
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] . ' .product-rating__icon + .product-rating__icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'rating_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'rating_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'rating_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-rating'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_add_to_cart_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_add_to_cart_style',
			array(
				'label'      => esc_html__( 'Add To Cart', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'show_item_button' => 'yes',
				],
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'        => 'add_to_cart_typography',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['item-button'],
				'placeholder' => '1px',
			)
		);

		$this->add_responsive_control(
			'add_to_cart_width',
			array(
				'label'      => esc_html__( 'Button Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'%',
					'px',
				),
				'range'      => array(
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
					'px' => array(
						'min' => 50,
						'max' => 1000,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => '',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_add_to_cart_style' );

		$this->start_controls_tab(
			'tab_add_to_cart_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'add_to_cart_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'add_to_cart_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-button'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_add_to_cart_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'add_to_cart_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . ':hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_background_hover_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . ':hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . ':hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'add_to_cart_hover_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-button'] . ':hover',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_add_to_cart_added',
			array(
				'label' => esc_html__( 'Added', 'jet-cw' ),
			)
		);

		$this->add_control(
			'add_to_cart_disabled_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.added' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_background_disabled_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.added' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_added_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.added' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.added' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'add_to_cart_added_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-button'] . '.added',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_add_to_cart_loading',
			array(
				'label' => esc_html__( 'Loading', 'jet-cw' ),
			)
		);

		$this->add_control(
			'add_to_cart_loading_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.loading' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_background_loading_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.loading' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_loading_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.loading' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'add_to_cart_loading_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-button'] . '.loading',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'add_to_cart_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['item-button'],
				'separator'   => 'before',

			)
		);

		$this->add_control(
			'add_to_cart_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'add_to_cart_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'add_to_cart_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'add_to_cart_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button-wrapper'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
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
					'{{WRAPPER}} ' . $css_scheme['item-button-wrapper'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	public function wishlist_remove_button_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_remove_button_style',
			array(
				'label'      => esc_html__( 'Remove Button', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'remove_button_text',
			array(
				'label' => esc_html__( 'Button Text', 'jet-cw' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$this->__add_advanced_icon_control(
			'remove_button_icon',
			array(
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-trash-o',
				'fa5_default' => array(
					'value'   => 'fas fa-trash-alt',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'remove_button_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-remove-button'],
			)
		);

		$this->start_controls_tabs( 'tabs_remove_button_style' );

		$this->start_controls_tab(
			'tab_remove_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'remove_button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'remove_button_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_remove_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'remove_button_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ':hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'remove_button_background_hover_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ':hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'remove_button_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'remove_button_border_border!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ':hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'remove_button_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['item-remove-button'],
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'remove_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'remove_button_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-remove-button'],
			)
		);

		$this->add_responsive_control(
			'remove_button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'remove_button_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'remove_button_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'order: {{VALUE}}',
				),
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'remove_button_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_control(
			'remove_button_icon_heading',
			array(
				'label'     => esc_html__( 'Icon', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'remove_button_icon_size',
			array(
				'label'      => esc_html__( 'Size', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 12,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ' .icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'remove_button_icon_offset',
			array(
				'label'      => esc_html__( 'Offset', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'    => array(
					'size' => 12,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ' .icon'      => 'margin-right: {{SIZE}}{{UNIT}};',
					'.rtl {{WRAPPER}} ' . $css_scheme['item-remove-button'] . ' .icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'remove_button_icon_style_tabs' );

		$this->start_controls_tab(
			'remove_button_icon_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'remove_button_icon_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ' .icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'remove_button_icon_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'remove_button_icon_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-remove-button'] . ':hover .icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function wishlist_tags_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_tags_style',
			array(
				'label'      => esc_html__( 'Tags', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tags_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-tags'] . ', {{WRAPPER}} ' . $css_scheme['item-tags'] . ' a',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'tags_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-tags'] . ', {{WRAPPER}} ' . $css_scheme['item-tags'] . ' a',
			)
		);

		$this->start_controls_tabs( 'tags_style_tabs' );

		$this->start_controls_tab(
			'tags_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'tags_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-tags'] . ' a' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['item-tags']        => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tags_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'tags_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-tags'] . ' a:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'tags_order',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Order', 'jet-cw' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 10,
				'step'      => 1,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-tags'] => 'order: {{VALUE}}',
				),
				'separator' => 'before',
				'condition' => array(
					'presets' => array( 'preset-1' ),
				),
			)
		);

		$this->add_responsive_control(
			'tags_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-tags'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

	protected function wishlist_overlay_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_overlay_style',
			array(
				'label'      => esc_html__( 'Overlay', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->start_controls_tabs( 'tabs_overlay_style' );

		$this->start_controls_tab(
			'tab_overlay_normal',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'overlay_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['overlay'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_overlay_hover',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'overlay_bg_hover',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item'] . ':hover .jet-wishlist-product-img-overlay' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function wishlist_empty_text_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_empty_text_style',
			array(
				'label'      => esc_html__( 'Empty Text', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'empty_text_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['empty-text'],
			)
		);

		$this->add_control(
			'empty_text_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['empty-text'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'empty_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['empty-text'],
			)
		);

		$this->add_control(
			'empty_text_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['empty-text'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'empty_text_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['empty-text'],
			)
		);

		$this->add_control(
			'empty_text_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['empty-text'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->add_responsive_control(
			'empty_text_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['empty-text'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'empty_text_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['empty-text'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'empty_text_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['empty-text'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();

	}

}