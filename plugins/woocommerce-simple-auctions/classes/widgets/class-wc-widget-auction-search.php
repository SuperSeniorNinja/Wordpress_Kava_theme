<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auction Search Widget
 *
 * @extends  WC_Widget
 */
class WC_Widget_Auction_Search extends WC_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_auction_search';
		$this->widget_description = esc_html__( 'A Search box for auctions only.', 'wc_simple_auctions' );
		$this->widget_id          = 'woocommerce_auction_search';
		$this->widget_name        = esc_html__( 'WooCommerce Auction Search', 'wc_simple_auctions' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => '',
				'label' => esc_html__( 'Title', 'wc_simple_auctions' )
			)
		);

		parent::__construct();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return void
	 */
	function widget( $args, $instance ) {
		$this->widget_start( $args, $instance );

		ob_start();

		do_action( 'pre_get_auction_search_form'  );

		wc_get_template( 'auction-searchform.php' );

		$form = apply_filters( 'get_auction_search_form', ob_get_clean() );

		
		echo $form;
		

		$this->widget_end( $args );
	}
}