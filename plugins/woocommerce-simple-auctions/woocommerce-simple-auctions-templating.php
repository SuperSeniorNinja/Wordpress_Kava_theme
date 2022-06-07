<?php
/**
 * WooCommerce Template Functions
 *
 * Functions used in the template files to output content - in most cases hooked in via the template actions. All functions are pluggable.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! function_exists( 'woocommerce_auction_add_to_cart' ) ) {

	/**
	 * Output the auction product add to cart area.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *         
	 */
	function woocommerce_auction_add_to_cart() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'single-product/add-to-cart/auction.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_bid' ) ) {

	/**
	 * Output the bid in auction template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_bid() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'single-product/bid.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_pay' ) ) {

	/**
	 * Output the pay for auction template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_pay() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'single-product/pay.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_bid_form' ) ) {

	/**
	 * Output the bid form template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_bid_form() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'single-product/auction-bid-form.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_condition' ) ) {

	/**
	 * Output the condition template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_condition() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-condition.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_countdown' ) ) {

	/**
	 * Output the countdown template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_countdown() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-countdown.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_dates' ) ) {

	/**
	 * Output the dates template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_dates() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-dates.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_max_bid' ) ) {

	/**
	 * Output the max_bid template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_max_bid() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-max-bid.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_reserve' ) ) {

	/**
	 * Output the reserve template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_reserve() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-reserve.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_sealed' ) ) {

	/**
	 * Output the sealed template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_sealed() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-sealed.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_ajax_conteiner_start' ) ) {

	/**
	 * Output the ajax-conteiner-start template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_ajax_conteiner_start() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-ajax-conteiner-start.php' );
	}
}
if ( ! function_exists( 'woocommerce_auction_ajax_conteiner_end' ) ) {

	/**
	 * Output the ajax-conteiner-end template.
	 *
	 * @access public
	 * @subpackage	Product
	 * @return void
         *  
	 */
	function woocommerce_auction_ajax_conteiner_end() {
		global $product;
		
		if(method_exists( $product, 'get_type') && $product->get_type() == 'auction')
			wc_get_template( 'global/auction-ajax-conteiner-end.php' );
	}
}
