<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer Buy Now
 *
 * Customer note emails are sent to winning bidder when someone buy item for buy now price 
 *
 * @class 		WC_Email_SA_Auction_Buy_Now
 * @extends 	WC_Email
 * 
 */
class WC_Email_SA_Auction_Buy_Now extends WC_Email {
	
	/** @var string */
	var $winning_bid;

	/** @var string */
	var $title;

	/** @var string */
	var $auction_id;
	
	/** @var string */
	var $checkout_url;	

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		global $woocommerce_auctions;

		$this->id 				= 'auction_buy_now';
		$this->title 			= __( 'Auction Buy Now', 'wc_simple_auctions' );
		$this->description		= __( 'Auction buy now emails are sent to winning bidder when someone buys the item for the buy now price.', 'wc_simple_auctions' );
		$this->customer_email 	= true;
		$this->template_html 	= 'emails/auction_buy_now.php';
		$this->template_plain 	= 'emails/plain/auction_buy_now.php';
		$this->template_base	= $woocommerce_auctions->plugin_path. 'templates/';

		$this->subject 			= __( 'Item sold on {site_title}', 'wc_simple_auctions');
		$this->heading      	= __( 'Item sold for buy now price', 'wc_simple_auctions');
		
		$this->checkout_url 	= simple_auction_get_checkout_url();

		// Triggers
		add_action( 'woocommerce_simple_auction_close_buynow_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $product_id ) {

		if ( ! $this->is_enabled() ) return;		
		
		if ( $product_id ) {

			$product_data = wc_get_product( $product_id );	
			$customer_user = absint( get_post_meta( $product_id, '_auction_current_bider', true ) );
		
			if ( $product_data ) {

				if ( $customer_user ) {
				
					$this->object 		= new WP_User( $customer_user );
					$this->recipient	= $this->object->user_email;
				}
					
				$this->auction_id = $product_id;
				$this->winning_bid = $product_data->get_curent_bid();		
			}
		}		
		
		if ( ! $this->get_recipient() ) return;
		
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {

		ob_start();
		wc_get_template( 	$this->template_html, array(
				'email_heading' => $this->get_heading(),
				'blogname'      => $this->get_blogname(),
				'current_bid'   => $this->winning_bid,
				'product_id'    => $this->auction_id,
				'checkout_url'  => $this->checkout_url,
				'email'         => $this,
			) );
		
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {

		ob_start();
		wc_get_template( $this->template_plain, array(
				'email_heading' => $this->get_heading(),
				'blogname'      => $this->get_blogname(),
				'current_bid'   => $this->winning_bid,
				'product_id'    => $this->auction_id,
				'checkout_url'  => $this->checkout_url,
				'email'         => $this,
				
			) );
		return ob_get_clean();
	}
}