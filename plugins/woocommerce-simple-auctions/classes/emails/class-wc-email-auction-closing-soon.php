<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer Outbid Email
 *
 * Customer note emails are sent for remind to pay.
 *
 * @class 		WC_Email_SA_Auction_Reminde_to_pay
 * @extends 	WC_Email
 * 
 */
class WC_Email_SA_Auction_Closing_soon extends WC_Email {

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

		$this->id 				= 'auction_closing_soon';
		$this->title 			= __( 'Auction closing soon email reminder', 'wc_simple_auctions' );
		$this->description		= __( 'Reminder for the customer that auction is closing soon.', 'wc_simple_auctions' );
		$this->customer_email 	= true;
		$this->template_html 	= 'emails/auction_closing_soon.php';
		$this->template_plain 	= 'emails/plain/auction_closing_soon.php';
		$this->template_base	= $woocommerce_auctions->plugin_path.  'templates/';
		$this->subject 			= __( 'Auction closing on {site_title}', 'wc_simple_auctions');
		$this->heading      	= __( 'Reminder that auction is closing.', 'wc_simple_auctions');

		$this->interval 		=  $this->get_option( 'interval','1');
		$this->interval2		=  $this->get_option( 'interval2','n/a');

		$this->bidders_enabled = $this->get_option( 'bidders_enabled','yes');
		$this->watchlist_enabled = $this->get_option( 'watchlist_enabled','no');

		// Triggers
		add_action( 'woocommerce_simple_auction_closing_soon_notification', array( $this, 'trigger' ) );

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

		global $wpdb;

		if ( !$this->is_enabled() ) return;
			
		$args = array( 'meta_query' => array( 'users' =>  array( 'relation' => 'OR') ) );

		if ( $product_id ) {

			$product_data = wc_get_product(  $product_id );

			if ( 'yes' === $this->bidders_enabled ) {

				$args['meta_query']['users'][]= array( 'key' => 'wsa_my_auctions', 'value' => $product_id ) ;
			}

			if ( 'yes' === $this->watchlist_enabled ) {
				$args['meta_query']['users'][]= array( 'key' => '_auction_watch', 'value' => $product_id ) ;
			}

			$args['meta_query'][]= array(
				'relation' => 'OR',
				array( 'key' => 'auctions_closing_soon_emails', 'value' => '1' , 'compare' => '=' ),
				array( 'key' => 'auctions_closing_soon_emails', 'compare' => 'NOT EXISTS' )
				) ;

			$user_query = new WP_User_Query( $args );

			// User Loop
			if ( ! empty( $user_query->results ) ) {
				foreach ( $user_query->results as $user ) {
					$this->recipient   = $user->user_email;
					$this->auction_id  = $product_id;
					$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				}
			}
		}
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
		global $woocommerce;
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
	  /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
    	$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable this notification', 'woocommerce' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable closing soon email notification', 'woocommerce' ),
				'default' 		=> 'no'
			),
			'bidders_enabled' => array(
				'title' 		=> __( 'Enabled for all participants', 'woocommerce' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Send email to all participating bidders', 'woocommerce' ),
				'default' 		=> 'yes'
			),
			'watchlist_enabled' => array(
				'title' 		=> __( 'Enabled for users who have auction in their whishlist', 'woocommerce' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Send email to users who has auction in their wishlist. If "Enabled for all participants" is disabled, this can be used as opt in so only users who have auction in whishlist will get this notification.', 'woocommerce' ),
				'default' 		=> 'no'
			),
			'interval' => array(
				'title' 		=> __( 'Timer - send email n hours prior auction end time', 'woocommerce' ),
				'type' 			=> 'number',

				'custom_attributes' => array('step'	=> '0.5', 'min'	=> '0.5'),
				'description' 	=> sprintf( __( 'Send reminder mail intervals in hour default is <code>%s</code>. This means email notifications will be sent <code>%s</code> hour(s) prior auction end time.', 'woocommerce' ), '1', $this->interval ),

				'placeholder' 	=> '',
				'default' 		=> '1'
			),
			'interval2' => array(
				'title' 		=> __( 'Second Timer - send email n hours prior auction end time', 'woocommerce' ),
				'type' 			=> 'number',

				'custom_attributes' => array('step'	=> '0.5', 'min'	=> '0.5'),
				'description' 	=> sprintf( __( 'Send reminder mail intervals in hour default is <code>%s</code>. This means email notifications will be sent <code>%s</code> hour(s) prior auction end time. Leave empty to disable', 'woocommerce' ), 'disbled', $this->interval2 ),

				'placeholder' 	=> '',
				'default' 		=> ''
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
				'description' 	=> __( 'Choose which format of email message.', 'woocommerce' ),
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
