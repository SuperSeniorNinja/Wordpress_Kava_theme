<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer Outbid Email
 *
 * Customer note emails are sent when you add a note to an order.
 *
 * @class 		WC_Email_SA_Outbid
 * @extends 	WC_Email
 * 
 */
class WC_Email_SA_Customerbid_Note extends WC_Email {

	/** @var string */
	var $current_bid;

	/** @var string */
	var $title;

	/** @var string */
	var $auction_id;	

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		global $woocommerce_auctions;		

		$this->id 				= 'customer_bid_note';
		$this->title 			= __( 'Customer bid notification', 'wc_simple_auctions' );
		$this->description		= __( 'Customer bid emails are sent to customer when customer places bid (confirmation email)', 'wc_simple_auctions' );
		$this->customer_email = true;
		$this->template_html 	= 'emails/customerbid.php';
		$this->template_plain 	= 'emails/plain/customerbid.php';
		$this->template_base	= $woocommerce_auctions->plugin_path. 'templates/';

		$this->subject 			= __( 'You have placed bid on {site_title}', 'wc_simple_auctions');
		$this->heading      	= __( 'You have placed bid on {site_title}', 'wc_simple_auctions');

		$this->proxy    		= $this->get_option( 'proxy' );

		// Triggers		 
		add_action( 'woocommerce_simple_auctions_place_bid_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();		
		
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $args ) {

		if ( ! $this->is_enabled() ) return;	

		if ( $args ) {
			
			$args = wp_parse_args( $args);			

			$customer_user = absint( get_post_meta( $args['product_id'], '_auction_current_bider', true ) );

			$autobid = get_post_meta($args['product_id'], '_auction_current_bid_proxy', true);			

			if ($autobid == 'yes' &&  $this->proxy == 'no' ){
				return;
			}
			
			extract( $args );
			if ( $customer_user ) {
					$this->object 		= new WP_User( $customer_user );
					$this->recipient	= $this->object->user_email;
			}
			if ( $args['product_id'] ) {
				$product_data = wc_get_product(  $args['product_id'] );
				$this->auction_id = $args['product_id'];
				$this->current_bid = $product_data->get_curent_bid();
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
				'email_heading' 		=> $this->get_heading(),
				'blogname'				=> $this->get_blogname(),
				'current_bid' 			=> $this->current_bid,
				'product_id'			=> $this->auction_id,
				'email'			=> $this,
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
				'email_heading' 		=> $this->get_heading(),
				'blogname'				=> $this->get_blogname(),
				'current_bid' 			=> $this->current_bid,
				'product_id'			=> $this->auction_id,
				'email'			=> $this,
			) );
		return ob_get_clean();
	}
	/**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable this email notification', 'woocommerce' ),
				'default' 		=> 'yes'
			),
			'proxy' => array(
				'title' 		=> __( 'Enable/Disable  sending "bid notification" email for proxy (auto) bidding', 'wc_simple_auctions' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable this email notification', 'woocommerce' ),
				'default' 		=> 'yes'
			),
		
			'subject' => array(
				'title' 		=> __( 'Subject', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', 'woocommerce' ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose which format of email to send.', 'woocommerce' ),
				'default' 		=> 'html',
				'class'			=> 'email_type',
				'options'		=> array(
					'plain'		 	=> __( 'Plain text', 'woocommerce' ),
					'html' 			=> __( 'HTML', 'woocommerce' ),
					'multipart' 	=> __( 'Multipart', 'woocommerce' ),
				)
			)
		);		
    }
}