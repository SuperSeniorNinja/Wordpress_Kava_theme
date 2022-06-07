<?php
namespace ElementorPro\Modules\Woocommerce\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Current_Query_Auctions_Renderer extends Base_Auctions_Renderer {

	private $settings = [];

	public function __construct( $settings = [], $type = 'products' ) {
		$this->settings = $settings;
		$this->type = $type;
		$this->attributes = $this->parse_attributes( [
			'paginate' => $settings['paginate'],
			'cache' => false,
		] );
		$this->query_args = $this->parse_query_args();
	}

	/**
	 * Override the original `get_query_results`
	 * with modifications that:
	 * 1. Remove `pre_get_posts` action if `is_added_product_filter`.
	 *
	 * @return bool|mixed|object
	 */
	protected function get_query_results() {
		$results = parent::get_query_results();
		return $results;
	}

	protected function parse_query_args() {
		$settings = &$this->settings;


		if ( ! is_page( wc_get_page_id( 'auction' ) ) ) {
			$query_args = $GLOBALS['wp_query']->query_vars;
		}

		add_action( "woocommerce_shortcode_before_{$this->type}_loop", function () {
			wc_set_loop_prop( 'is_shortcode', false );
		} );

		if ( 'yes' === $settings['paginate'] ) {
			$page = get_query_var( 'paged', 1 );

			if ( 1 < $page ) {
				$query_args['paged'] = $page;
			}

			if ( 'yes' !== $settings['allow_order'] ) {
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			}

			if ( 'yes' !== $settings['show_result_count'] ) {
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			}
		}
		$query_args['show_past_auctions'] = $settings['show_finished'] === 'yes' ? 'true' : false ;
		$query_args['show_future_auctions'] = $settings['show_future'] === 'yes' ? 'true' : false ;
		$query_args['auction_arhive'] = true;
		// Always query only IDs.
		$query_args['fields'] = 'ids';

		return $query_args;
	}

	/* Set auctions query args.
	 *
	 * @since 3.2.0
	 * @param array $query_args Query args.
	 */
	protected function set_auctions_status_query_args( &$query_args ) {
			$settings = &$this->settings;

			if( $settings['show_finished'] == 'yes' ){
				$query_args['show_past_auctions'] = true;
			} else{
				$query_args['show_past_auctions'] = false;
			}
			if( $settings['show_future'] == 'yes' ){
				$query_args['show_future_auctions'] = true;
			} else{
				$query_args['show_future_auctions'] = false;
			}
			

	}

}
