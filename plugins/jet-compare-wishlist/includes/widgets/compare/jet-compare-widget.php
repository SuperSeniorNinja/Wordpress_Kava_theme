<?php
/**
 * Class: Jet_Compare_Widget
 * Name: Compare
 * Slug: jet-compare
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Core\Schemes\Typography as Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Compare_Widget extends Jet_CW_Base {

	public function get_name() {
		return 'jet-compare';
	}

	public function get_title() {
		return esc_html__( 'Compare', 'jet-cw' );
	}

	public function get_icon() {
		return 'jet-cw-icon-compare';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-adjust-the-comparison-settings-for-woocommerce-shop-using-jetcomparewishlist/';
	}

	public function get_categories() {
		return array( 'jet-cw' );
	}

	protected function register_controls() {

		$css_scheme = apply_filters(
			'jet-compare-wishlist/jet-compare/css-scheme',
			array(
				'compare-table-wrapper'          => '.jet-compare-table__wrapper',
				'compare-table'                  => '.jet-compare-table',
				'compare-table-row'              => '.jet-compare-table-row',
				'compare-table-cell'             => '.jet-compare-table-cell',
				'compare-table-controls-wrapper' => '.jet-compare-table__wrapper .jet-compare-table-difference-controls-wrapper',
				'compare-table-controls'         => '.jet-compare-table__wrapper .jet-compare-difference-control',
				'compare-table-controls-label'   => '.jet-compare-table__wrapper .jet-compare-difference-control .jet-compare-difference-control__label',
				'compare-table-controls-icon'    => '.jet-compare-table__wrapper .jet-compare-difference-control .jet-cw-icon',
				'compare-highlight-cell'         => '.jet-compare-table-highlight .highlighted .jet-compare-table-cell',
				'compare-table-heading'          => '.jet-compare-table-heading',
				'compare-remove-button'          => '.jet-cw-remove-button.jet-compare-item-remove-button',
				'item-thumbnail'                 => '.jet-cw-thumbnail',
				'item-title'                     => '.jet-compare-table .jet-cw-product-title',
				'item-price'                     => '.jet-compare-table .jet-cw-price',
				'item-currency'                  => '.jet-compare-table .jet-cw-price .woocommerce-Price-currencySymbol',
				'item-rating'                    => '.jet-compare-table .jet-cw-rating-stars',
				'item-button'                    => '.jet-cw-add-to-cart .button',
				'item-description'               => '.jet-cw-description',
				'item-short-description'         => '.jet-cw-excerpt',
				'item-sku'                       => '.jet-cw-sku',
				'item-stock'                     => '.jet-cw-stock-status',
				'item-in-stock'                  => '.jet-cw-stock-status .available-on-backorder',
				'item-out-of-stock'              => '.jet-cw-stock-status .out-of-stock',
				'item-weight'                    => '.jet-cw-weight',
				'item-dimensions'                => '.jet-cw-dimensions',
				'item-attributes'                => '.jet-cw-attributes',
				'item-categories'                => '.jet-compare-table .jet-cw-categories',
				'item-tags'                      => '.jet-compare-table .jet-cw-tags',
				'empty-text'                     => '.jet-compare-table-empty',
			)
		);

		$this->start_controls_section(
			'section_content_style',
			array(
				'label'      => esc_html__( 'Content', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			)
		);

		$table_data_list = new Repeater();

		$table_data_list->add_control(
			'compare_table_data_type',
			array(
				'label'   => esc_html__( 'Data Type', 'jet-cw' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'title',
				'options' => jet_cw_tools()->compare_table_data_list(),
			)
		);

		$table_data_list->add_control(
			'compare_table_data_title',
			array(
				'label'     => esc_html__( 'Title', 'jet-cw' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array(
					'compare_table_data_type!' => 'attributes',
				),
			)
		);

		$table_data_list->add_control(
			'compare_table_data_title_html_tag',
			array(
				'label'     => esc_html__( 'Title HTML Tag', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h5',
				'options'   => jet_cw_tools()->get_available_title_html_tags(),
				'condition' => array(
					'compare_table_data_type' => 'title',
				),
			)
		);

		$table_data_list->add_control(
			'compare_table_data_remove_text',
			array(
				'label'     => esc_html__( 'Button Text', 'jet-cw' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array(
					'compare_table_data_type' => 'compare_remove_button',
				),
			)
		);

		$this->__add_advanced_icon_control(
			'compare_table_data_remove_icon',
			array(
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-remove',
				'fa5_default' => array(
					'value'   => 'fas fa-remove',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'compare_table_data_type' => 'compare_remove_button',
				),
			),
			$table_data_list
		);

		$table_data_list->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'cw_thumbnail_size',
				'default'   => 'thumbnail',
				'condition' => array(
					'compare_table_data_type' => 'thumbnail',
				),
			)
		);

		$table_data_list->add_control(
			'cw_rating_icon',
			array(
				'label'     => esc_html__( 'Rating Icon', 'jet-cw' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'jetcomparewishlist-icon-rating-1',
				'options'   => jet_cw_tools()->get_available_rating_icons_list(),
				'condition' => array(
					'compare_table_data_type' => 'rating',
				),
			)
		);

		$table_data_list->add_control(
			'compare_table_custom_field',
			array(
				'label'     => esc_html__( 'Meta Field Key', 'jet-cw' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array(
					'compare_table_data_type' => 'custom_field',
				),
			)
		);

		$table_data_list->add_control(
			'compare_table_custom_field_fallback',
			array(
				'label'       => esc_html__( 'Fallback', 'jet-cw' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Show this if field value is empty', 'jet-cw' ),
				'condition'   => array(
					'compare_table_data_type' => 'custom_field',
				),
			)
		);

		$this->add_control(
			'compare_table_data',
			array(
				'type'        => Controls_Manager::REPEATER,
				'label'       => esc_html__( 'Compare Table', 'jet-cw' ),
				'fields'      => $table_data_list->get_controls(),
				'default'     => array(
					array(
						'compare_table_data_title' => esc_html__( 'Remove', 'jet-cw' ),
						'compare_table_data_type'  => 'compare_remove_button',
					),
					array(
						'compare_table_data_title' => esc_html__( 'Thumbnail', 'jet-cw' ),
						'compare_table_data_type'  => 'thumbnail',
					),
					array(
						'compare_table_data_title' => esc_html__( 'Title', 'jet-cw' ),
						'compare_table_data_type'  => 'title',
					),
				),
				'title_field' => '{{{ compare_table_data_title }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings_style',
			array(
				'label'      => esc_html__( 'Settings', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			)
		);

		$this->add_control(
			'empty_compare_text',
			array(
				'label'   => esc_html__( 'Empty Compare Text', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'You have no comparison lists. Add products to the comparison.', 'jet-cw' ),
			)
		);

		$this->add_control(
			'scrolled_table',
			array(
				'label'   => esc_html__( 'Scrolled Table', 'jet-cw' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'scrolled_table_desc',
			array(
				'raw'             => esc_html__( 'Scrolled table allow table to be scrolled horizontally.', 'jet-cw' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition'       => array(
					'scrolled_table' => 'yes',
				),
			)
		);

		$this->add_control(
			'scrolled_table_on',
			array(
				'label'       => esc_html__( 'Scrolled On', 'jet-cw' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => array( 'mobile' ),
				'options'     => array(
					'mobile'  => esc_html__( 'Mobile', 'jet-cw' ),
					'tablet'  => esc_html__( 'Tablet', 'jet-cw' ),
					'desktop' => esc_html__( 'Desktop', 'jet-cw' ),
				),
				'condition'   => array(
					'scrolled_table' => 'yes',
				),
			)
		);

		$this->add_control(
			'compare_table_differences',
			[
				'label'     => esc_html__( 'Enable Differences Controls', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'compare_table_highlight_differences',
			[
				'label'     => esc_html__( 'Highlight Differences', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => [
					'compare_table_differences' => 'yes',
				],
			]
		);

		$this->start_controls_tabs(
			'compare_table_highlight_button_content',
			[
				'condition' => [
					'compare_table_highlight_differences' => 'yes',
					'compare_table_differences'           => 'yes',
				],
			]
		);

		$this->start_controls_tab(
			'compare_table_highlight_button_content_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			]
		);

		$this->__add_advanced_icon_control(
			'compare_table_highlight_button_icon_normal',
			[
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
			]
		);

		$this->add_control(
			'compare_table_highlight_button_label_normal',
			[
				'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Highlight', 'jet-cw' ),
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'compare_table_highlight_button_content_active_tab',
			[
				'label' => esc_html__( 'Active', 'jet-cw' ),
			]
		);

		$this->__add_advanced_icon_control(
			'compare_table_highlight_button_icon_active',
			[
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
			]
		);

		$this->add_control(
			'compare_table_highlight_button_label_active',
			[
				'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Clear', 'jet-cw' ),
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'compare_table_only_differences',
			[
				'label'     => esc_html__( 'Differences Visibility', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => [
					'compare_table_differences' => 'yes',
				],
			]
		);

		$this->start_controls_tabs(
			'compare_table_only_differences_button_content',
			[
				'condition' => [
					'compare_table_only_differences' => 'yes',
					'compare_table_differences'      => 'yes',
				],
			]
		);

		$this->start_controls_tab(
			'compare_table_only_differences_button_content_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			]
		);

		$this->__add_advanced_icon_control(
			'compare_table_only_differences_button_icon_normal',
			[
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
			]
		);

		$this->add_control(
			'compare_table_only_differences_button_label_normal',
			[
				'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Show Differences', 'jet-cw' ),
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'compare_table_only_differences_button_content_active_tab',
			[
				'label' => esc_html__( 'Active', 'jet-cw' ),
			]
		);

		$this->__add_advanced_icon_control(
			'compare_table_only_differences_button_icon_active',
			[
				'label'       => esc_html__( 'Button Icon', 'jet-cw' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
			]
		);

		$this->add_control(
			'compare_table_only_differences_button_label_active',
			[
				'label'   => esc_html__( 'Button Label Text', 'jet-cw' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Show All', 'jet-cw' ),
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->compare_table_styles( $css_scheme );

		$this->compare_table_headings_styles( $css_scheme );

		$this->compare_table_cells_styles( $css_scheme );

		$this->compare_remove_button_styles( $css_scheme );

		$this->compare_thumbnail_styles( $css_scheme );

		$this->compare_title_styles( $css_scheme );

		$this->compare_price_styles( $css_scheme );

		$this->compare_rating_styles( $css_scheme );

		$this->compare_add_to_cart_styles( $css_scheme );

		$this->compare_description_styles( $css_scheme );

		$this->compare_short_description_styles( $css_scheme );

		$this->compare_sku_styles( $css_scheme );

		$this->compare_stock_status_styles( $css_scheme );

		$this->compare_weight_styles( $css_scheme );

		$this->compare_dimensions_styles( $css_scheme );

		$this->compare_attributes_styles( $css_scheme );

		$this->compare_categories_styles( $css_scheme );

		$this->compare_tags_styles( $css_scheme );

		$this->compare_difference_controls_styles( $css_scheme );

		$this->compare_highlighted_cell_styles( $css_scheme );

		$this->compare_empty_text_styles( $css_scheme );

	}

	protected function render() {

		$settings = $this->get_settings();

		$widget_settings = array(
			'empty_compare_text'        => esc_html__( $settings['empty_compare_text'], 'jet_cw' ),
			'compare_table_data'        => $this->new_icon_render( $settings['compare_table_data'] ),
			'scrolled_table'            => $settings['scrolled_table'],
			'scrolled_table_on'         => $settings['scrolled_table_on'],
			'compare_table_differences' => $settings['compare_table_differences'],
			'highlight_differences'     => $settings['compare_table_highlight_differences'],
			'only_differences'          => $settings['compare_table_only_differences'],
			'_widget_id'                => $this->get_id(),
		);

		$highlight_differences = filter_var( $widget_settings['highlight_differences'], FILTER_VALIDATE_BOOLEAN );
		$only_differences      = filter_var( $widget_settings['only_differences'], FILTER_VALIDATE_BOOLEAN );

		if ( $highlight_differences ) {
			$widget_settings['highlight_button_icon_normal']  = htmlspecialchars( $this->__render_icon( 'compare_table_highlight_button_icon_normal', '%s', '', false ) );
			$widget_settings['highlight_button_label_normal'] = $settings['compare_table_highlight_button_label_normal'];
			$widget_settings['highlight_button_icon_active']  = htmlspecialchars( $this->__render_icon( 'compare_table_highlight_button_icon_active', '%s', '', false ) );
			$widget_settings['highlight_button_label_active'] = $settings['compare_table_highlight_button_label_active'];
		}

		if ( $only_differences ) {
			$widget_settings['only_differences_button_icon_normal']  = htmlspecialchars( $this->__render_icon( 'compare_table_only_differences_button_icon_normal', '%s', '', false ) );
			$widget_settings['only_differences_button_label_normal'] = $settings['compare_table_only_differences_button_label_normal'];
			$widget_settings['only_differences_button_icon_active']  = htmlspecialchars( $this->__render_icon( 'compare_table_only_differences_button_icon_active', '%s', '', false ) );
			$widget_settings['only_differences_button_label_active'] = $settings['compare_table_only_differences_button_label_active'];
		}

		$selector = 'div.jet-compare-table__wrapper[data-widget-id="' . $widget_settings['_widget_id'] . '"]';

		jet_cw()->widgets_store->store_widgets_types( 'jet-compare', $selector, $widget_settings, 'compare' );

		$this->__context = 'render';

		$this->__open_wrap();

		jet_cw_widgets_functions()->get_widget_compare_table( $widget_settings );

		$this->__close_wrap();

	}

	/**
	 * Return settings with new icon controller.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function new_icon_render( $settings ) {

		$new_icon_settings = array();

		foreach ( $settings as $item ) {
			$this->__processed_item = $item;

			if ( isset( $item['selected_compare_table_data_remove_icon'] ) || isset( $item['compare_table_data_remove_icon'] ) ) {
				$item['selected_compare_table_data_remove_icon'] = htmlspecialchars( $this->__render_icon( 'compare_table_data_remove_icon', '%s', ' ', false ) );
				$new_icon_settings[]                             = $item;
			}
		}
		$this->__processed_item = false;

		return $new_icon_settings;

	}

	/**
	 * Compare table elements styles
	 **/
	public function compare_table_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_table_style',
			array(
				'label'      => esc_html__( 'Table', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'table_width',
			array(
				'label'      => esc_html__( 'Table Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1200,
					),
				),
				'default'    => array(
					'unit' => '%',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-wrapper'] => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'table_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-table-wrapper'],
			)
		);

		$this->add_control(
			'table_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-wrapper'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'table_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-wrapper'],
			)
		);

		$this->add_responsive_control(
			'table_align',
			array(
				'label'                => esc_html__( 'Table Alignment', 'jet-cw' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'   => array(
						'title' => esc_html__( 'Start', 'jet-cw' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-cw' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'End', 'jet-cw' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					),
				),
				'selectors_dictionary' => array(
					'left'   => ! is_rtl() ? 'margin-left: 0; margin-right: auto;' : 'margin-left: auto; margin-right: 0;',
					'center' => 'margin-left: auto; margin-right: auto;',
					'right'  => ! is_rtl() ? 'margin-left: auto; margin-right: 0;' : 'margin-left: 0; margin-right: auto;',
				),
				'selectors'            => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-wrapper'] => '{{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

	}

	public function compare_table_headings_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_table_headings_style',
			array(
				'label' => esc_html__( 'Table Headings', 'jet-cw' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'table_headings_width',
			array(
				'label'      => esc_html__( 'Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px', '%',
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
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading'] => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'table_headings_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-heading'],
			)
		);

		$this->start_controls_tabs( 'table_heading_tabs' );

		$this->start_controls_tab(
			'table_headings_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'table_heading_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading'] => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'table_heading_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'table_headings_striped_row',
			array(
				'label' => esc_html__( 'Striped rows', 'jet-cw' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'table_headings_even_row_bg_color',
			array(
				'label'     => esc_html__( 'Even Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} tr:nth-child(even) ' . $css_scheme['compare-table-heading'] => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'table_headings_striped_row' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'table_headings_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'table_headings_row_bg_color_hover',
			array(
				'label'     => esc_html__( 'Row Hover Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':hover ' . $css_scheme['compare-table-heading'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'table_headings_even_row_bg_color_hover',
			array(
				'label'     => esc_html__( 'Even Row Hover Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':nth-child(even):hover ' . $css_scheme['compare-table-heading'] => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'table_headings_striped_row' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'table_heading_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'table_heading_border',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-heading'],
			)
		);

		$this->add_control(
			'table_heading_hidden_border',
			array(
				'label'     => esc_html__( 'Hidden border for header container', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading']                                                      => ! is_rtl() ? 'border-left-style: hidden;' : 'border-right-style: hidden;',
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':first-child ' . $css_scheme['compare-table-heading'] => 'border-top-style: hidden;',
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':last-child ' . $css_scheme['compare-table-heading']  => 'border-bottom-style: hidden;',
				),
				'condition' => array(
					'table_heading_border_border!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'table_heading_horizontal_alignment',
			array(
				'label'     => esc_html__( 'Alignment Horizontal', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_responsive_control(
			'table_heading_vertical_alignment',
			array(
				'label'     => esc_html__( 'Alignment Vertical', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'top'    => array(
						'title' => esc_html__( 'Top', 'jet-cw' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => esc_html__( 'Middle', 'jet-cw' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => esc_html__( 'Bottom', 'jet-cw' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-heading'] => 'vertical-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

	}

	public function compare_table_cells_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_table_body_style',
			array(
				'label' => esc_html__( 'Table Content', 'jet-cw' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'table_cell_width',
			array(
				'label'      => esc_html__( 'Cell Min Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px', '%',
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
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] => 'min-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'table_cell_tabs' );

		$this->start_controls_tab(
			'table_cell_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'table_cell_bg_color',
			array(
				'label'     => esc_html__( 'Row Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'table_body_striped_row',
			array(
				'label' => esc_html__( 'Striped rows', 'jet-cw' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'table_body_even_row_bg_color',
			array(
				'label'     => esc_html__( 'Even Row Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} tr:nth-child(even) ' . $css_scheme['compare-table-cell'] => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'table_body_striped_row' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'table_body_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			)
		);


		$this->add_control(
			'table_row_bg_color_hover',
			array(
				'label'     => esc_html__( 'Row Hover Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':hover ' . $css_scheme['compare-table-cell'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'table_body_even_row_bg_color_hover',
			array(
				'label'     => esc_html__( 'Even Row Hover Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':nth-child(even):hover ' . $css_scheme['compare-table-cell'] => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'table_body_striped_row' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'table_cell_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'table_cell_border',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-cell'],
			)
		);

		$this->add_control(
			'table_cell_hidden_border',
			array(
				'label'     => esc_html__( 'Hidden border for body container', 'jet-cw' ),
				'type'      => Controls_Manager::SWITCHER,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':first-child ' . $css_scheme['compare-table-cell'] => 'border-top-style: hidden;',
					'{{WRAPPER}} ' . $css_scheme['compare-table-row'] . ':last-child ' . $css_scheme['compare-table-cell']  => 'border-bottom-style: hidden;',
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] . ':first-child'                                     => ! is_rtl() ? 'border-left-style: hidden;' : 'border-right-style: hidden;',
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] . ':last-child'                                      => ! is_rtl() ? 'border-right-style: hidden;' : 'border-left-style: hidden;',
				),
				'condition' => array(
					'table_cell_border_border!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'table_cell_horizontal_alignment',
			array(
				'label'     => esc_html__( 'Alignment Horizontal', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => jet_cw_tools()->get_available_horizontal_alignment(),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
				'classes'   => 'elementor-control-align',
			)
		);

		$this->add_responsive_control(
			'table_cell_vertical_alignment',
			array(
				'label'     => esc_html__( 'Alignment Vertical', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'top'    => array(
						'title' => esc_html__( 'Top', 'jet-cw' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => esc_html__( 'Middle', 'jet-cw' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => esc_html__( 'Bottom', 'jet-cw' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-table-cell'] => 'vertical-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

	}

	public function compare_remove_button_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_remove_button_style',
			array(
				'label'      => esc_html__( 'Remove Button', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'remove_button_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-remove-button'],
			)
		);

		$this->start_controls_tabs( 'tabs_remove_button_style' );

		$this->start_controls_tab(
			'tab_remove_button_normal',
			array(
				'label' => __( 'Normal', 'jet-cw' ),
			)
		);

		$this->add_control(
			'remove_button_text_color',
			array(
				'label'     => __( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'remove_button_background_color',
			array(
				'label'     => __( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_remove_button_hover',
			array(
				'label' => __( 'Hover', 'jet-cw' ),
			)
		);

		$this->add_control(
			'remove_button_hover_color',
			array(
				'label'     => __( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ':hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'remove_button_background_hover_color',
			array(
				'label'     => __( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ':hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'remove_button_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'remove_button_border_border!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ':hover' => 'border-color: {{VALUE}};',
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
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-remove-button'],
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
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'remove_button_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-remove-button'],
			)
		);

		$this->add_responsive_control(
			'remove_button_padding',
			array(
				'label'      => __( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
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
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ' .icon' => 'font-size: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ' .icon' => ! is_rtl() ? 'margin-right: {{SIZE}}{{UNIT}};' : 'margin-left: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ' .icon' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} ' . $css_scheme['compare-remove-button'] . ':hover .icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function compare_thumbnail_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_thumbnail_style',
			array(
				'label'      => esc_html__( 'Thumbnail', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
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

		$this->end_controls_section();

	}

	public function compare_title_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_title_style',
			array(
				'label'      => esc_html__( 'Title', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
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

		$this->end_controls_section();

	}

	public function compare_price_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_price_style',
			array(
				'label'      => esc_html__( 'Price', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
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
					'{{WRAPPER}} ' . $css_scheme['item-price'] . ' del+ins' => ! is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};',
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
			'archive_price_sale_color',
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

	public function compare_rating_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_rating_styles',
			array(
				'label'      => esc_html__( 'Rating', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
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

		$this->end_controls_section();

	}

	public function compare_add_to_cart_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_add_to_cart_style',
			array(
				'label'      => esc_html__( 'Add To Cart', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
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
				'condition' => array(
					'add_to_cart_border_border!' => '',
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
			'add_to_cart_added_color',
			array(
				'label'     => esc_html__( 'Text Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-button'] . '.added' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'add_to_cart_background_added_color',
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
				'condition' => array(
					'add_to_cart_border_border!' => '',
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

		$this->end_controls_section();

	}

	public function compare_description_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_description_style',
			array(
				'label'      => esc_html__( 'Description', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-description'],
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-description'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'description_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-description'],
			)
		);

		$this->end_controls_section();

	}

	public function compare_short_description_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_short_description_style',
			array(
				'label'      => esc_html__( 'Short Description', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'short_description_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-short-description'],
			)
		);

		$this->add_control(
			'short_description_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-short-description'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'short_description_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-short-description'],
			)
		);

		$this->end_controls_section();

	}

	public function compare_sku_styles( $css_scheme ) {

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

		$this->end_controls_section();

	}

	public function compare_stock_status_styles( $css_scheme ) {

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

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'stock_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-stock'],
			)
		);

		$this->end_controls_section();

	}

	public function compare_weight_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_weight_style',
			array(
				'label'      => esc_html__( 'Weight', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'weight_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-weight'],
			)
		);

		$this->add_control(
			'weight_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-weight'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'weight_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-weight'],
			)
		);

		$this->end_controls_section();

	}

	public function compare_dimensions_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_dimensions_style',
			array(
				'label'      => esc_html__( 'Dimensions', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'dimensions_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-dimensions'],
			)
		);

		$this->add_control(
			'dimensions_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-dimensions'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'dimensions_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-dimensions'],
			)
		);

		$this->end_controls_section();

	}

	public function compare_attributes_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_attributes_style',
			array(
				'label'      => esc_html__( 'Attributes', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'attributes_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-attributes'],
			)
		);

		$this->add_control(
			'attributes_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['item-attributes'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'attributes_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-attributes'],
			)
		);

		$this->end_controls_section();

	}

	public function compare_categories_styles( $css_scheme ) {

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

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'categories_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-categories'] . ', {{WRAPPER}} ' . $css_scheme['item-categories'] . ' a',
			)
		);

		$this->end_controls_section();

	}

	public function compare_tags_styles( $css_scheme ) {

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

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'tags_text_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['item-tags'] . ', {{WRAPPER}} ' . $css_scheme['item-tags'] . ' a',
			)
		);

		$this->end_controls_section();

	}

	public function compare_difference_controls_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_difference_controls_style',
			[
				'label'      => esc_html__( 'Difference Controls', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'compare_table_differences' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'controls_buttons_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'jet-cw' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'flex-end',
				'options'   => jet_cw_tools()->get_available_flex_horizontal_alignment(),
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-wrapper'] => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'controls_wrapper_margin',
			[
				'label'      => esc_html__( 'Margin', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-wrapper'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'controls_wrapper_padding',
			[
				'label'      => esc_html__( 'Padding', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-wrapper'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'controls_custom_size',
			[
				'label'        => esc_html__( 'Custom Size', 'jet-cw' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-cw' ),
				'label_off'    => esc_html__( 'No', 'jet-cw' ),
				'return_value' => 'yes',
				'default'      => 'false',
				'separator'    => 'before',
			]
		);

		$this->add_responsive_control(
			'controls_custom_width',
			[
				'label'      => esc_html__( 'Custom Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em', '%',
				],
				'range'      => [
					'px' => [
						'min' => 40,
						'max' => 1000,
					],
					'em' => [
						'min' => 1,
						'max' => 20,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'controls_custom_size' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'controls_custom_height',
			[
				'label'      => esc_html__( 'Custom Height', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em', '%',
				],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 1000,
					],
					'em' => [
						'min' => 1,
						'max' => 20,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'controls_custom_size' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_distance',
			[
				'label'      => esc_html__( 'Distance Between Controls', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em', '%',
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-wrapper'] => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_icon_heading',
			[
				'label'     => esc_html__( 'Icon', 'jet-cw' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_icon_font_size',
			[
				'label'      => esc_html__( 'Font Size', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em', 'rem',
				],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-icon'] => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_icon_box_width',
			[
				'label'      => esc_html__( 'Icon Box Width', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em', '%',
				],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-icon'] => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_icon_box_height',
			[
				'label'      => esc_html__( 'Icon Box Height', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em', '%',
				],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-icon'] => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_icon_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'jet-cw' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [
					'px', 'em',
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ' .jet-compare-difference-control__state-normal' => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ' .jet-compare-difference-control__state-hover'  => 'gap: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'after',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'switcher_buttons_label_typography',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls-label'],
			]
		);

		$this->start_controls_tabs( 'compare_table_controls_styles' );

		$this->start_controls_tab(
			'compare_table_controls_normal',
			[
				'label' => esc_html__( 'Normal', 'jet-cw' ),
			]
		);

		$this->add_control(
			'compare_table_controls_normal_label_color',
			[
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-label'] => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_normal_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-icon'] => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_normal_icon_bg_color',
			[
				'label'     => esc_html__( 'Icon Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls-icon'] => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'compare_table_controls_normal_background',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'compare_table_controls_normal_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'],
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_normal_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'compare_table_controls_normal_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'compare_table_controls_hover',
			[
				'label' => esc_html__( 'Hover', 'jet-cw' ),
			]
		);

		$this->add_control(
			'compare_table_controls_hover_label_color',
			[
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover .jet-compare-difference-control__label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_hover_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover .jet-cw-icon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_hover_icon_bg_color',
			[
				'label'     => esc_html__( 'Icon Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover .jet-cw-icon' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'compare_table_controls_hover_background',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'compare_table_controls_hover_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover',
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_hover_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'compare_table_controls_hover_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . ':hover',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'compare_table_controls_active',
			[
				'label' => esc_html__( 'Active', 'jet-cw' ),
			]
		);

		$this->add_control(
			'compare_table_controls_active_label_color',
			[
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active .jet-compare-difference-control__label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_active_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active .jet-cw-icon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_active_icon_bg_color',
			[
				'label'     => esc_html__( 'Icon Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active .jet-cw-icon' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'compare_table_controls_active_background',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'compare_table_controls_active_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active',
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_active_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'compare_table_controls_active_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.active',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'compare_table_controls_disable',
			[
				'label' => esc_html__( 'Disable', 'jet-cw' ),
			]
		);

		$this->add_control(
			'compare_table_controls_disable_label_color',
			[
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable .jet-compare-difference-control__label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_disable_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable .jet-cw-icon' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'compare_table_controls_disable_icon_bg_color',
			[
				'label'     => esc_html__( 'Icon Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable .jet-cw-icon' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'compare_table_controls_disable_background',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'compare_table_controls_disable_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable',
			]
		);

		$this->add_responsive_control(
			'compare_table_controls_disable_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'jet-cw' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'compare_table_controls_disable_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-table-controls'] . '.disable',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function compare_highlighted_cell_styles( $css_scheme ) {

		$this->start_controls_section(
			'section_highlight_style',
			[
				'label'      => esc_html__( 'Highlight', 'jet-cw' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'compare_table_highlight_differences' => 'yes',
				],
			]
		);

		$this->add_control(
			'cell_highlight_color',
			[
				'label'     => esc_html__( 'Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-highlight-cell'] . ' > *' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'cell_highlight_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'jet-cw' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['compare-highlight-cell'] => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'cell_highlight_border',
				'label'       => esc_html__( 'Border', 'jet-cw' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['compare-highlight-cell'],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'cell_highlight_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['compare-highlight-cell'],
			]
		);

		$this->end_controls_section();

	}

	public function compare_empty_text_styles( $css_scheme ) {

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