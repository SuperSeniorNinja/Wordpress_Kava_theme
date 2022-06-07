<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: WooCommerce CoinPayments.net Gateway
 * Plugin URI: https://www.coinpayments.net/
 * Description:  Provides a CoinPayments.net Payment Gateway.
 * Author: CoinPayments.net
 * Author URI: https://www.coinpayments.net/
 * Version: 1.0.14
 */

/**
 * CoinPayments.net Gateway
 * Based on the PayPal Standard Payment Gateway
 *
 * Provides a CoinPayments.net Payment Gateway.
 *
 * @class 		WC_Coinpayments
 * @extends		WC_Gateway_Coinpayments
 * @version		1.0.14
 * @package		WooCommerce/Classes/Payment
 * @author 		CoinPayments.net based on PayPal module by WooThemes
 */

add_action( 'plugins_loaded', 'coinpayments_gateway_load', 0 );
function coinpayments_gateway_load() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        // oops!
        return;
    }

    /**
     * Add the gateway to WooCommerce.
     */
    add_filter( 'woocommerce_payment_gateways', 'wccoinpayments_add_gateway' );

    function wccoinpayments_add_gateway( $methods ) {
    	if (!in_array('WC_Gateway_Coinpayments', $methods)) {
				$methods[] = 'WC_Gateway_Coinpayments';
			}
			return $methods;
    }


    class WC_Gateway_Coinpayments extends WC_Payment_Gateway {

	var $ipn_url;

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
	public function __construct() {
		global $woocommerce;

        $this->id           = 'coinpayments';
        $this->icon         = apply_filters( 'woocommerce_coinpayments_icon', plugins_url().'/coinpayments-payment-gateway-for-woocommerce/assets/images/icons/coinpayments.png' );
        $this->has_fields   = false;
        $this->method_title = __( 'CoinPayments.net', 'woocommerce' );
        $this->ipn_url   = add_query_arg( 'wc-api', 'WC_Gateway_Coinpayments', home_url( '/' ) );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title 			= $this->get_option( 'title' );
		$this->description 		= $this->get_option( 'description' );
		$this->merchant_id 			= $this->get_option( 'merchant_id' );
		$this->ipn_secret   = $this->get_option( 'ipn_secret' );
		$this->send_shipping	= $this->get_option( 'send_shipping' );
		$this->debug_email			= $this->get_option( 'debug_email' );
		$this->allow_zero_confirm = $this->get_option( 'allow_zero_confirm' ) == 'yes' ? true : false;
		$this->form_submission_method = $this->get_option( 'form_submission_method' ) == 'yes' ? true : false;
		$this->invoice_prefix	= $this->get_option( 'invoice_prefix', 'WC-' );
		$this->simple_total = $this->get_option( 'simple_total' ) == 'yes' ? true : false;

		// Logs
		$this->log = new WC_Logger();

		// Actions
		add_action( 'woocommerce_receipt_coinpayments', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook
		add_action( 'woocommerce_api_wc_gateway_coinpayments', array( $this, 'check_ipn_response' ) );

		if ( !$this->is_valid_for_use() ) $this->enabled = false;
    }


    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @access public
     * @return bool
     */
    function is_valid_for_use() {
        //if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_coinpayments_supported_currencies', array( 'AUD', 'CAD', 'USD', 'EUR', 'JPY', 'GBP', 'CZK', 'BTC', 'LTC' ) ) ) ) return false;
        // ^- instead of trying to maintain this list just let it always work
        return true;
    }

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {

		?>
		<h3><?php _e( 'CoinPayments.net', 'woocommerce' ); ?></h3>
		<p><?php _e( 'Completes checkout via CoinPayments.net', 'woocommerce' ); ?></p>

    	<?php if ( $this->is_valid_for_use() ) : ?>

			<table class="form-table">
			<?php
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
			?>
			</table><!--/.form-table-->

		<?php else : ?>
            <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'CoinPayments.net does not support your store currency.', 'woocommerce' ); ?></p></div>
		<?php
			endif;
	}


    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
							'title' => __( 'Enable/Disable', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Enable CoinPayments.net', 'woocommerce' ),
							'default' => 'yes'
						),
			'title' => array(
							'title' => __( 'Title', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
							'default' => __( 'CoinPayments.net', 'woocommerce' ),
							'desc_tip'      => true,
						),
			'description' => array(
							'title' => __( 'Description', 'woocommerce' ),
							'type' => 'textarea',
							'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
							'default' => __( 'Pay with Bitcoin, Litecoin, or other altcoins via CoinPayments.net', 'woocommerce' )
						),
			'merchant_id' => array(
							'title' => __( 'Merchant ID', 'woocommerce' ),
							'type' 			=> 'text',
							'description' => __( 'Please enter your CoinPayments.net Merchant ID.', 'woocommerce' ),
							'default' => '',
						),
			'ipn_secret' => array(
							'title' => __( 'IPN Secret', 'woocommerce' ),
							'type' 			=> 'text',
							'description' => __( 'Please enter your CoinPayments.net IPN Secret.', 'woocommerce' ),
							'default' => '',
						),
			'simple_total' => array(
							'title' => __( 'Compatibility Mode', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( "This may be needed for compatibility with certain addons if the order total isn't correct.", 'woocommerce' ),
							'default' => ''
						),
			'send_shipping' => array(
							'title' => __( 'Collect Shipping Info?', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Enable Shipping Information on Checkout page', 'woocommerce' ),
							'default' => 'yes'
						),
			'allow_zero_confirm' => array(
							'title' => __( 'Enable 1st-confirm payments?', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( '* WARNING * If this is selected orders will be marked as paid as soon as your buyer\'s payment is detected, but before it is fully confirmed. This can be dangerous if the payment never confirms and is only recommended for digital downloads.', 'woocommerce' ),
							'default' => ''
						),
			'invoice_prefix' => array(
							'title' => __( 'Invoice Prefix', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'Please enter a prefix for your invoice numbers. If you use your CoinPayments.net account for multiple stores ensure this prefix is unique.', 'woocommerce' ),
							'default' => 'WC-',
							'desc_tip'      => true,
						),
			'testing' => array(
							'title' => __( 'Gateway Testing', 'woocommerce' ),
							'type' => 'title',
							'description' => '',
						),
			'debug_email' => array(
							'title' => __( 'Debug Email', 'woocommerce' ),
							'type' => 'email',
							'default' => '',
							'description' => __( 'Send copies of invalid IPNs to this email address.', 'woocommerce' ),
						)
			);

    }


	/**
	 * Get CoinPayments.net Args
	 *
	 * @access public
	 * @param mixed $order
	 * @return array
	 */
	function get_coinpayments_args( $order ) {
		global $woocommerce;

		$order_id = $order->get_id();

		if ( in_array( $order->get_billing_country(), array( 'US','CA' ) ) ) {
			$order->set_billing_phone(str_replace( array( '( ', '-', ' ', ' )', '.' ), '', $order->get_billing_phone() ));
		}

		// CoinPayments.net Args
		$coinpayments_args = array(
				'cmd' 					=> '_pay_auto',
				'merchant' 				=> $this->merchant_id,
				'allow_extra' 				=> 0,
				// Get the currency from the order, not the active currency
				'currency' 		=> $order->get_currency(),
				'reset' 				=> 1,
				'success_url' 				=> $this->get_return_url( $order ),
				'cancel_url'			=> esc_url_raw($order->get_cancel_order_url_raw()),

				// Order key + ID
				'invoice'				=> $this->invoice_prefix . $order->get_order_number(),
				'custom' 				=> serialize( array( $order->get_id(), $order->get_order_key() ) ),

				// IPN
				'ipn_url'			=> $this->ipn_url,

				// Billing Address info
				'first_name'			=> $order->get_billing_first_name(),
				'last_name'				=> $order->get_billing_last_name(),
				'email'					=> $order->get_billing_email(),
		);

		if ($this->send_shipping == 'yes') {
			$coinpayments_args = array_merge($coinpayments_args, array(
				'want_shipping' => 1,
				'company'					=> $order->get_billing_company(),
				'address1'				=> $order->get_billing_address_1(),
				'address2'				=> $order->get_billing_address_2(),
				'city'					=> $order->get_billing_city(),
				'state'					=> $order->get_billing_state(),
				'zip'					=> $order->get_billing_postcode(),
				'country'				=> $order->get_billing_country(),
				'phone'					=> $order->get_billing_phone(),
			));
		} else {
			$coinpayments_args['want_shipping'] = 0;
		}

		if ($this->simple_total) {
			$coinpayments_args['item_name'] 	= sprintf( __( 'Order %s' , 'woocommerce'), $order->get_order_number() );
			$coinpayments_args['quantity'] 		= 1;
			$coinpayments_args['amountf'] 		= number_format( $order->get_total(), 8, '.', '' );
			$coinpayments_args['taxf'] 				= 0.00;
			$coinpayments_args['shippingf']		= 0.00;
		} else if ( wc_tax_enabled() && wc_prices_include_tax() ) {
			$coinpayments_args['item_name'] 	= sprintf( __( 'Order %s' , 'woocommerce'), $order->get_order_number() );
			$coinpayments_args['quantity'] 		= 1;
			$coinpayments_args['amountf'] 		= number_format( $order->get_total() - $order->get_total_shipping() - $order->get_shipping_tax(), 8, '.', '' );
			$coinpayments_args['shippingf']		= number_format( $order->get_total_shipping() + $order->get_shipping_tax() , 8, '.', '' );
			$coinpayments_args['taxf'] 				= 0.00;
		} else {
			$coinpayments_args['item_name'] 	= sprintf( __( 'Order %s' , 'woocommerce'), $order->get_order_number() );
			$coinpayments_args['quantity'] 		= 1;
			$coinpayments_args['amountf'] 		= number_format( $order->get_total() - $order->get_total_shipping() - $order->get_total_tax(), 8, '.', '' );
			$coinpayments_args['shippingf']		= number_format( $order->get_total_shipping(), 8, '.', '' );
			$coinpayments_args['taxf']				= number_format( $order->get_total_tax(), 8, '.', '' );
		}

		$coinpayments_args = apply_filters( 'woocommerce_coinpayments_args', $coinpayments_args );

		return $coinpayments_args;
	}


    /**
	 * Generate the coinpayments button link
     *
     * @access public
     * @param mixed $order_id
     * @return string
     */
    function generate_coinpayments_url($order) {
		global $woocommerce;

		if ( $order->get_status() != 'completed' && get_post_meta($order->get_id(), 'CoinPayments payment complete', true ) != 'Yes' ) {
			//$order->update_status('on-hold', 'Customer is being redirected to CoinPayments...');
			$order->update_status('pending', 'Customer is being redirected to CoinPayments...');
		}

		$coinpayments_adr = "https://www.coinpayments.net/index.php?";
		$coinpayments_args = $this->get_coinpayments_args( $order );
		$coinpayments_adr .= http_build_query( $coinpayments_args, '', '&' );
		return $coinpayments_adr;
	}


    /**
     * Process the payment and return the result
     *
     * @access public
     * @param int $order_id
     * @return array
     */
	function process_payment( $order_id ) {

		$order          = wc_get_order( $order_id );

		return array(
				'result' 	=> 'success',
				'redirect'	=> $this->generate_coinpayments_url($order),
		);

	}


    /**
     * Output for the order received page.
     *
     * @access public
     * @return void
     */
	function receipt_page( $order ) {
		echo '<p>'.__( 'Thank you for your order, please click the button below to pay with CoinPayments.net.', 'woocommerce' ).'</p>';

		echo $this->generate_coinpayments_form( $order );
	}

	/**
	 * Check CoinPayments.net IPN validity
	 **/
	function check_ipn_request_is_valid() {
		global $woocommerce;

		$order = false;
		$error_msg = "Unknown error";
		$auth_ok = false;

		if (isset($_POST['ipn_mode']) && $_POST['ipn_mode'] == 'hmac') {
			if (isset($_SERVER['HTTP_HMAC']) && !empty($_SERVER['HTTP_HMAC'])) {
				$request = file_get_contents('php://input');
				if ($request !== FALSE && !empty($request)) {
					if (isset($_POST['merchant']) && $_POST['merchant'] == trim($this->merchant_id)) {
						$hmac = hash_hmac("sha512", $request, trim($this->ipn_secret));
						if ($hmac == $_SERVER['HTTP_HMAC']) {
							$auth_ok = true;
						} else {
							$error_msg = 'HMAC signature does not match';
						}
					} else {
						$error_msg = 'No or incorrect Merchant ID passed';
					}
				} else {
					$error_msg = 'Error reading POST data';
				}
			} else {
				$error_msg = 'No HMAC signature sent.';
			}
		} else {
			$error_msg = "Unknown IPN verification method.";
		}

		if ($auth_ok) {
	    if (!empty($_POST['invoice']) && !empty($_POST['custom'])) {
	    	$order = $this->get_coinpayments_order( $_POST );
	    }

			if ($order !== FALSE) {
				if ($_POST['ipn_type'] == "button" || $_POST['ipn_type'] == "simple") {
					if ($_POST['merchant'] == $this->merchant_id) {
						if ($_POST['currency1'] == $order->get_currency()) {
							if ($_POST['amount1'] >= $order->get_total()) {
								print "IPN check OK\n";
								return true;
							} else {
								$error_msg = "Amount received is less than the total!";
							}
						} else {
							$error_msg = "Original currency doesn't match!";
						}
					} else {
						$error_msg = "Merchant ID doesn't match!";
					}
				} else {
					$error_msg = "ipn_type != button or simple";
				}
			} else {
				$error_msg = "Could not find order info for order: ".$_POST['invoice'];
			}
		}

		$report = "Error Message: ".$error_msg."\n\n";

		$report .= "POST Fields\n\n";
		foreach ($_POST as $key => $value) {
			$report .= $key.'='.$value."\n";
		}

		if ($order) {
			$order->update_status('on-hold', sprintf( __( 'CoinPayments.net IPN Error: %s', 'woocommerce' ), $error_msg ) );
		}
		if (!empty($this->debug_email)) { mail($this->debug_email, "CoinPayments.net Invalid IPN", $report); }
		mail(get_option( 'admin_email' ), sprintf( __( 'CoinPayments.net Invalid IPN', 'woocommerce' ), $error_msg ), $report );
		die('IPN Error: '.$error_msg);
		return false;
	}

	/**
	 * Successful Payment!
	 *
	 * @access public
	 * @param array $posted
	 * @return void
	 */
	function successful_request( $posted ) {
		global $woocommerce;

		$posted = stripslashes_deep( $posted );

		// Custom holds post ID
	    if (!empty($_POST['invoice']) && !empty($_POST['custom'])) {
			    $order = $this->get_coinpayments_order( $posted );
			    if ($order === FALSE) {
			    	die("IPN Error: Could not find order info for order: ".$_POST['invoice']);
			    }

        	$this->log->add( 'coinpayments', 'Order #'.$order->get_id().' payment status: ' . $posted['status_text'] );
         	$order->add_order_note('CoinPayments.net Payment Status: '.$posted['status_text']);

         	if ( $order->get_status() != 'completed' && get_post_meta( $order->get_id(), 'CoinPayments payment complete', true ) != 'Yes' ) {
         		// no need to update status if it's already done
            if ( ! empty( $posted['txn_id'] ) )
             	update_post_meta( $order->get_id(), 'Transaction ID', $posted['txn_id'] );
            if ( ! empty( $posted['first_name'] ) )
             	update_post_meta( $order->get_id(), 'Payer first name', $posted['first_name'] );
            if ( ! empty( $posted['last_name'] ) )
             	update_post_meta( $order->get_id(), 'Payer last name', $posted['last_name'] );
            if ( ! empty( $posted['email'] ) )
             	update_post_meta( $order->get_id(), 'Payer email', $posted['email'] );

						if ($posted['status'] >= 100 || $posted['status'] == 2 || ($this->allow_zero_confirm && $posted['status'] >= 0 && $posted['received_confirms'] > 0 && $posted['received_amount'] >= $posted['amount2'])) {
							print "Marking complete\n";
							update_post_meta( $order->get_id(), 'CoinPayments payment complete', 'Yes' );
             	$order->payment_complete();
						} else if ($posted['status'] < 0) {
							print "Marking cancelled\n";
              $order->update_status('cancelled', 'CoinPayments.net Payment cancelled/timed out: '.$posted['status_text']);
							mail( get_option( 'admin_email' ), sprintf( __( 'Payment for order %s cancelled/timed out', 'woocommerce' ), $order->get_order_number() ), $posted['status_text'] );
            } else {
							print "Marking pending\n";
							$order->update_status('pending', 'CoinPayments.net Payment pending: '.$posted['status_text']);
						}
	        }
	        die("IPN OK");
	    }
	}

	/**
	 * Check for CoinPayments IPN Response
	 *
	 * @access public
	 * @return void
	 */
	function check_ipn_response() {

		@ob_clean();

		if ( ! empty( $_POST ) && $this->check_ipn_request_is_valid() ) {
			$this->successful_request($_POST);
		} else {
			wp_die( "CoinPayments.net IPN Request Failure" );
 		}
	}

	/**
	 * get_coinpayments_order function.
	 *
	 * @access public
	 * @param mixed $posted
	 * @return void
	 */
	function get_coinpayments_order( $posted ) {
		$custom = maybe_unserialize( stripslashes_deep($posted['custom']) );

    	// Backwards comp for IPN requests
    	if ( is_numeric( $custom ) ) {
	    	$order_id = (int) $custom;
	    	$order_key = $posted['invoice'];
    	} elseif( is_string( $custom ) ) {
	    	$order_id = (int) str_replace( $this->invoice_prefix, '', $custom );
	    	$order_key = $custom;
    	} else {
    		list( $order_id, $order_key ) = $custom;
		}

		$order = wc_get_order( $order_id );

		if ($order === FALSE) {
			// We have an invalid $order_id, probably because invoice_prefix has changed
			$order_id 	= wc_get_order_id_by_order_key( $order_key );
			$order 		= wc_get_order( $order_id );
		}

		// Validate key
		if ($order === FALSE || $order->get_order_key() !== $order_key ) {
			return FALSE;
		}

		return $order;
	}

}

class WC_Coinpayments extends WC_Gateway_Coinpayments {
	public function __construct() {
		_deprecated_function( 'WC_Coinpayments', '1.4', 'WC_Gateway_Coinpayments' );
		parent::__construct();
	}
}
}
