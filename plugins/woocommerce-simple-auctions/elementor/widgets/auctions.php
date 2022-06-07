<?php
namespace ElementorPro\Modules\Woocommerce\Widgets;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use ElementorPro\Modules\QueryControl\Controls\Group_Control_Query;
use ElementorPro\Modules\Woocommerce\Classes\Auctions_Renderer;
use ElementorPro\Modules\Woocommerce\Classes\Current_Query_Auctions_Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Auctions extends Products_Base {

	public function get_name() {
		return 'woocommerce-auctions';
	}

	public function get_title() {
		return esc_html__( 'Auctions', 'elementor-pro' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'product', 'archive' ];
	}

	public function get_categories() {
		return [
			'woocommerce-elements',
		];
	}

	protected function register_query_controls() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_group_control(
			Group_Control_Query::get_type(),
			[
				'name' => Auctions_Renderer::QUERY_CONTROL_NAME,
				'post_type' => 'product',
				'presets' => [ 'include', 'exclude', 'order' ],
				'fields_options' => [
					'post_type' => [
						'default' => 'product',
						'options' => [
							'current_query' => esc_html__( 'Current Query', 'elementor-pro' ),
							'product' => esc_html__( 'Latest Auctions', 'wc_simple_auctions' ),
							'featured' => esc_html__( 'Featured', 'wc_simple_auctions' ),
							'finished' => esc_html__( 'Finished', 'wc_simple_auctions' ),
							'future' => esc_html__( 'Future', 'wc_simple_auctions' ),
							'by_id' => esc_html_x( 'Manual Selection', 'Posts Query Control', 'elementor-pro' ),
						],
					],
					'orderby' => [
						'default' => 'date',
						'options' => [
							'date' => esc_html__( 'Date', 'elementor-pro' ),
							'title' => esc_html__( 'Title', 'elementor-pro' ),
							'bid' => esc_html__( 'Bid', 'wc_simple_auctions' ),
							'auction_started' => esc_html__( 'Auction Started', 'wc_simple_auctions' ),
							'auction_end' => esc_html__( 'Ending soonest', 'wc_simple_auctions' ),
							'auction_activity' => esc_html__( 'Most active', 'wc_simple_auctions' ),
							'rand' => esc_html__( 'Random', 'elementor-pro' ),
							'menu_order' => esc_html__( 'Menu Order', 'elementor-pro' ),
						],
					],
					'order' => [
						'condition' => [
							'orderby' => [ 'date', 'title', 'bid','menu_order' ],
						],
					],
					'exclude' => [
						'options' => [
							'current_post' => esc_html__( 'Current Post', 'elementor-pro' ),
							'manual_selection' => esc_html__( 'Manual Selection', 'elementor-pro' ),
							'terms' => esc_html__( 'Term', 'elementor-pro' ),
						],
					],
					'include' => [
						'options' => [
							'terms' => esc_html__( 'Term', 'elementor-pro' ),
						],
					],
				],
				'exclude' => [
					'posts_per_page',
					'exclude_authors',
					'authors',
					'offset',
					'related_fallback',
					'related_ids',
					'query_id',
					'avoid_duplicates',
					'ignore_sticky_posts',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'elementor-pro' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Columns', 'elementor-pro' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'elementor-products-columns%s-',
				'min' => 1,
				'max' => 12,
				'default' => Auctions_Renderer::DEFAULT_COLUMNS_AND_ROWS,
				'required' => true,
				'render_type' => 'template',
				'device_args' => [
					Controls_Stack::RESPONSIVE_TABLET => [
						'required' => false,
					],
					Controls_Stack::RESPONSIVE_MOBILE => [
						'required' => false,
					],
				],
				'min_affected_device' => [
					Controls_Stack::RESPONSIVE_DESKTOP => Controls_Stack::RESPONSIVE_TABLET,
					Controls_Stack::RESPONSIVE_TABLET => Controls_Stack::RESPONSIVE_TABLET,
				],
			]
		);

		$this->add_control(
			'rows',
			[
				'label' => esc_html__( 'Rows', 'elementor-pro' ),
				'type' => Controls_Manager::NUMBER,
				'default' => Auctions_Renderer::DEFAULT_COLUMNS_AND_ROWS,
				'render_type' => 'template',
				'range' => [
					'px' => [
						'max' => 20,
					],
				],
			]
		);

		$this->add_control(
			'paginate',
			[
				'label' => esc_html__( 'Pagination', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'allow_order',
			[
				'label' => esc_html__( 'Allow Order', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'paginate' => 'yes',
				],
			]
		);

		$this->add_control(
			'wc_notice_frontpage',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'Ordering is not available if this widget is placed in your front page. Visible on frontend only.', 'elementor-pro' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition' => [
					'paginate' => 'yes',
					'allow_order' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_result_count',
			[
				'label' => esc_html__( 'Show Result Count', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'paginate' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_finished',
			[
				'label' => esc_html__( 'Show finished auctions', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => get_option( 'simple_auctions_finished_enabled' ),

			]
		);
		$this->add_control(
			'show_future',
			[
				'label' => esc_html__( 'Show auctions that did not start yet', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => get_option( 'simple_auctions_future_enabled' ),
			]
		);

		$this->end_controls_section();

		$this->register_query_controls();

		parent::_register_controls();
	}

	protected function get_shortcode_object( $settings ) {

		if ( 'current_query' === $settings[ Auctions_Renderer::QUERY_CONTROL_NAME . '_post_type' ] ) {
			$type = 'current_query';
			return new Current_Query_Auctions_Renderer( $settings, $type );
		}
		$type = 'products';

		return new Auctions_Renderer( $settings, $type );
	}

	protected function render() {

		if ( WC()->session ) {
			wc_print_notices();
		}


		// For Auctions_Renderer.
		if ( ! isset( $GLOBALS['post'] ) ) {
			$GLOBALS['post'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		$settings = $this->get_settings();

		$shortcode = $this->get_shortcode_object( $settings );

		$content = $shortcode->get_content();

		if ( $content ) {
			echo $content;
		} elseif ( $this->get_settings( 'nothing_found_message' ) ) {
			echo '<div class="elementor-nothing-found elementor-products-nothing-found">' . esc_html( $this->get_settings( 'nothing_found_message' ) ) . '</div>';
		}
	}

	public function render_plain_content() {}


}
