<?php
/**
 * WooCommerce bid class
 *
 * The WooCommerce bid class stores bid data and handles bidding process. *
 *
 * @class 		WC_Bid
 * @version		1.0.0
 * @category	Class
 * 
 */
class WC_Bid {
	public $bid;

	/**
	 * Constructor for the bid class. Loads options and hooks in the init method.
	 *
	 * @access public
	 * @return void
     * 
	 */
	public function __construct() {
		add_action('init', array($this, 'init'), 5);
	}

	/**
	 * Loads the bid data from the PHP session during WordPress init and hooks in other methods.
	 *
	 * @access public
	 * @return void
     * 
	 */
	public function init() {

	}

	/**
	 * Place bid
	 *
	 * @param string $product_id contains the id of the product to add to the cart
	 * @return bool
     * 
	 */
	public function placebid ( $product_id, $bid ) {
		global $product_data;
		global $sitepress;

		$is_proxy_bid = false;
		$log_id = false;


		if (function_exists('icl_object_id') && method_exists($sitepress, 'get_default_language')) {
				
		    $product_id = icl_object_id($product_id	,'product',false, $sitepress->get_default_language());
		}
		
		$this->bid = apply_filters( 'woocommerce_simple_auctions_place_bid_value', (float)wc_format_decimal($bid) , $product_id) ;
		
		$product_data = wc_get_product($product_id);

		$maximum_bid_amount = get_option( 'simple_auctions_max_bid_amount', '999999999999.99');
	
		$maximum_bid_amount = $maximum_bid_amount > 0 ? $maximum_bid_amount : '999999999999.99' ;
				
		do_action('woocommerce_simple_auctions_before_place_bid', $product_id, $bid,$product_data);
		
			
		if ((apply_filters( 'woocommerce_simple_auctions_before_place_bid_filter',$product_data, $bid) == false) OR !is_object($product_data) ){
			return false;
		}

		if (!is_user_logged_in()) {
			wc_add_notice( sprintf( wp_kses_post( __('Sorry, you must be logged in to place a bid. <a href="%s" class="button">Login &rarr;</a>', 'wc_simple_auctions') ), get_permalink(wc_get_page_id('myaccount'))), 'error');
			return false;
		}

		if ($this->bid <= 0) {
			wc_add_notice( sprintf( esc_html__('Bid must be greater than 0!', 'wc_simple_auctions'), get_permalink(wc_get_page_id('myaccount'))), 'error');
			return false;
		}
		
		if ($this->bid >= $maximum_bid_amount) {
			wc_add_notice( sprintf( esc_html__('Bid must be lower than %s !', 'wc_simple_auctions'), wc_price($maximum_bid_amount)), 'error');
			return false;
		}

		// Check if auction is_finished
		if ($product_data -> is_closed()) {
			wc_add_notice( sprintf( esc_html__( 'Sorry, auction for &quot;%s&quot; is finished', 'wc_simple_auctions' ), $product_data -> get_title()), 'error' );
			return false;
		}

		// Check if auction is_started
		if (!$product_data -> is_started()) {
			wc_add_notice( sprintf( esc_html__('Sorry, the auction for &quot;%s&quot; has not started yet', 'wc_simple_auctions'), $product_data -> get_title()),'error');
			return false;
		}

		// Stock check - only check if we're managing stock and backorders are not allowed
		if (!$product_data -> is_in_stock()) {
			wc_add_notice( sprintf( esc_html__('You cannot place a bid for &quot;%s&quot; because the product is out of stock.', 'wc_simple_auctions'), $product_data -> get_title()),'error');
			return false;
		}

		if ($product_data ->get_auction_sealed() == 'yes') { 
			return $this->auction_sealed_placebid($product_data, $bid);
		}

		$current_user = wp_get_current_user();
		$auction_type = $product_data->get_auction_type();
		

		// Check if bid is needed or no need to bid (allready highest bidder?)
		if (! ($product_data->get_auction_reserved_price() && $product_data->is_reserve_met() === FALSE) && $current_user->ID == $product_data->get_auction_current_bider() && get_option( 'simple_auctions_curent_bidder_can_bid') !== 'yes') {
			wc_add_notice( sprintf( esc_html__('No need to bid. Your bid is winning! ', 'wc_simple_auctions'), $product_data->get_title()));
			return false;
			
		}

        if ( ( ! $product_data->get_auction_reserved_price() ||  ( $product_data->get_auction_reserved_price() && $product_data->is_reserve_met() !== FALSE ) ) && $current_user->ID == $product_data->get_auction_current_bider() && get_option( 'simple_auctions_curent_bidder_can_bid') === 'yes') {

        	 if ($product_data->get_auction_proxy() && $product_data->is_reserve_met() === TRUE ) {

        	 	if ($auction_type == 'normal') {

	        	 	if (  $this->bid <= (float)$product_data->get_auction_max_bid()  && get_option( 'simple_auctions_smaller_max_bid', 'no' ) === 'no') {
						wc_add_notice( sprintf( esc_html__('New max bid cannot be smaller than old max bid!', 'wc_simple_auctions'), $product_data -> get_title()));

						return false;
	        	 	}
	        	 	if( $this->bid <= $product_data->bid_value()){
	        	 		wc_add_notice( sprintf( esc_html__('New max bid cannot be smaller than current bid!', 'wc_simple_auctions'), $product_data -> get_title()));

	        	 		return false;

	        	 	}
		            update_post_meta($product_id, '_auction_max_bid', $this->bid );
		            do_action( 'woocommerce_simple_auctions_changed_max_bid',  array( 'product_id' => $product_id,  'auction_max_bid' => $this->bid, 'auction_max_current_bider' => $current_user->ID) );
		            wc_add_notice( esc_html__('You have changed your maximum bid successfully', 'wc_simple_auctions'));

		            return TRUE;

	            } elseif ($auction_type == 'reverse') {
	            	if (  $this->bid >= (float)$product_data->get_auction_max_bid()) {
						wc_add_notice( sprintf( esc_html__('New min bid cannot be bigger than old min bid!', 'wc_simple_auctions'), $product_data -> get_title()));

						return false;
	        	 	}
		            update_post_meta($product_id, '_auction_max_bid', $this->bid );
		            wc_add_notice( sprintf( esc_html__('You have changed your minimum bid successfully', 'wc_simple_auctions') ) );
		            do_action( 'woocommerce_simple_auctions_changed_min_bid',  array( 'product_id' => $product_id, 'auction_max_bid' => $this->bid, 'auction_max_current_bider' => $current_user->ID ) );

		            return TRUE;
        		}
        	} 	        
        }        
        
		if ($auction_type == 'normal') {				
			
			if ( apply_filters( 'woocommerce_simple_auctions_minimal_bid_value', $product_data->bid_value(), $product_data, $this->bid )<= ($this->bid )) {

				// Check for proxy bidding
				if ($product_data->get_auction_proxy()) {
					if ($this->bid > (float)$product_data->get_auction_max_bid() OR  !$product_data->get_auction_max_bid()) {

						if ($product_data->get_auction_reserved_price() && $product_data->is_reserve_met() === FALSE && apply_filters( 'woocommerce_simple_auctions_proxy_bid_to_reserve', true, $product_data ,$this->bid ) === true ) {
							if ($this->bid > (float)$product_data->get_auction_reserved_price()) {

								$curent_bid = (float)$product_data->get_auction_reserved_price();

    						} else {
    								$curent_bid = $this->bid;
    						}

						} else {

							if ($product_data->get_auction_max_bid()){
                                $temp_bid = (float) $product_data->get_auction_max_bid() + (float)$product_data->get_auction_bid_increment();
								$curent_bid = ($this->bid < $temp_bid) ? $this->bid : $temp_bid ;
							} else {
								$curent_bid = ($this->bid < $product_data->bid_value()) ? $this->bid : $product_data->bid_value();		
							}
							
						}

						if($product_data->get_auction_max_bid() > $product_data->get_auction_current_bid()){
							$this->log_bid($product_id, $product_data->get_auction_max_bid(), get_userdata($product_data->get_auction_max_current_bider()), 1);
						}

						$curent_bid =  apply_filters( 'woocommerce_simple_auctions_proxy_curent_bid_value' , $curent_bid ,$product_data,$this->bid );
						$outbiddeduser = $product_data->get_auction_current_bider();

						update_post_meta($product_id, '_auction_max_bid', $this->bid);
						update_post_meta($product_id, '_auction_max_current_bider', $current_user->ID);
						update_post_meta($product_id, '_auction_current_bid', $curent_bid);
						update_post_meta($product_id, '_auction_current_bider', $current_user->ID);
						update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));
						delete_post_meta($product_id, '_auction_current_bid_proxy');
						$log_id = $this->log_bid($product_id, $curent_bid, $current_user, 0);
						do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser, 'log_id' => $log_id, 'auction_max_bid' => $this->bid, 'auction_max_current_bider' => $current_user->ID ) );
                        
					} else {
						
						$is_proxy_bid = true; 
						$this->log_bid($product_id, $this->bid, $current_user, 0);
						if($this->bid == (float)$product_data ->get_auction_max_bid()) {
							$proxy_bid = $product_data -> get_auction_max_bid();	
						} else {
							$proxy_bid =  apply_filters( 'woocommerce_simple_auctions_proxy_bid_value' , $this->bid + $product_data->get_auction_bid_increment() ,$product_data ,$this->bid);
							
							if ($proxy_bid > (float)$product_data ->get_auction_max_bid()) 
								$proxy_bid = (float)$product_data ->get_auction_max_bid();
						}
						
						update_post_meta($product_id, '_auction_current_bid', $proxy_bid);
						update_post_meta($product_id, '_auction_current_bid_proxy', 'yes');
						update_post_meta($product_id, '_auction_current_bider', $product_data->get_auction_max_current_bider());
						update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 2));
						$log_id = $this -> log_bid($product_id, $proxy_bid, get_userdata($product_data->get_auction_max_current_bider()), 1);
						do_action( 'woocommerce_simple_auctions_proxy_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $product_data->get_auction_max_current_bider(),  'log_id' => $log_id ) );
						wc_add_notice(sprintf( esc_html__('You were outbid. Try again!', 'wc_simple_auctions'), $product_data -> get_title()),'error');
						
					}

				} else {

				    $outbiddeduser = $product_data->get_auction_current_bider();
					$curent_bid = $product_data -> get_curent_bid();
					update_post_meta($product_id, '_auction_current_bid', $this->bid);
					update_post_meta($product_id, '_auction_current_bider', $current_user->ID);
					update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));
					delete_post_meta($product_id, '_auction_current_bid_proxy');
					$log_id = $this -> log_bid($product_id, $this->bid, $current_user);
					do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser, 'log_id' => $log_id ) );
					
				}

			} else {
		    	wc_add_notice(sprintf( wp_kses_post( __('Your bid for &quot;%s&quot; is smaller than the current bid. Your bid must be at least %s ', 'wc_simple_auctions') ), $product_data -> get_title(),wc_price($product_data -> bid_value())),'error');

				return false;
			}

		} elseif ($auction_type == 'reverse') {
			
			if (apply_filters( 'woocommerce_simple_auctions_minimal_bid_value', $product_data->bid_value(), $product_data, $this->bid ) >= $bid) {

				// Check for proxy bidding
				if ($product_data->get_auction_proxy() ) {					
				    
					if (  $this->bid < (float)$product_data->get_auction_max_bid() OR  !$product_data->get_auction_max_bid()) {

						if ($product_data->get_auction_reserved_price() && $product_data -> is_reserve_met() === FALSE  && apply_filters( 'woocommerce_simple_auctions_proxy_bid_to_reserve', true, $product_data ,$this->bid ) === true ) {

							 if ($this->bid < (float)$product_data->get_auction_reserved_price()) {

								$curent_bid = (float)$product_data->get_auction_reserved_price();

						      } else {
								$curent_bid = $this->bid;
							 }

						} else {
							
							if ($product_data->get_auction_max_bid()){
                                $temp_bid = (float) $product_data->get_auction_max_bid() - (float) $product_data->get_auction_bid_increment();
								$curent_bid = $this->bid > $temp_bid ? $this->bid : $temp_bid ;
								
							} else {
								$curent_bid = $this->bid > $product_data->bid_value() ? $this->bid : $product_data->bid_value();			
							}
						}

						if($product_data->get_auction_max_bid() <  $product_data->get_auction_current_bid() ){

							$this -> log_bid( $product_id, $product_data->get_auction_max_bid(), get_userdata( $product_data->get_auction_max_current_bider() ), 1 );
						}	

						$outbiddeduser = $product_data->get_auction_current_bider();
						update_post_meta($product_id, '_auction_max_bid', $this->bid);
						update_post_meta($product_id, '_auction_max_current_bider', $current_user->ID);
						update_post_meta($product_id, '_auction_current_bid', $curent_bid);
						update_post_meta($product_id, '_auction_current_bider', $current_user->ID);
						update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));
						delete_post_meta($product_id, '_auction_current_bid_proxy');
						$log_id = $this -> log_bid($product_id, $curent_bid, $current_user, 0);
						do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser, 'log_id' => $log_id, 'auction_max_bid' => $this->bid, 'auction_max_current_bider' => $current_user->ID ) );
                        
					} else {
					    $is_proxy_bid = true; 
						
						$this -> log_bid($product_id, $this->bid, $current_user, 0);
						if($this->bid == (float)$product_data->get_auction_max_bid()) {
							$proxy_bid = $product_data->get_auction_max_bid();	
						} else {
							$proxy_bid =  apply_filters( 'woocommerce_simple_auctions_proxy_bid_value' , $this->bid - $product_data->get_auction_bid_increment() ,$product_data ,$this->bid);
							if ($proxy_bid < (float)$product_data->get_auction_max_bid()) 
								$proxy_bid = (float)$product_data->get_auction_max_bid();
						}
						update_post_meta($product_id, '_auction_current_bid', $proxy_bid);
						update_post_meta($product_id, '_auction_current_bid_proxy', 'yes');
						update_post_meta($product_id, '_auction_current_bider', $product_data->get_auction_max_current_bider());
						update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 2));
						$log_id = $this -> log_bid($product_id, $proxy_bid, get_userdata($product_data->get_auction_max_current_bider()), 1);
						
						wc_add_notice(sprintf( esc_html__('You were outbid. Try again!', 'wc_simple_auctions'), $product_data -> get_title()),'error');
						
					}

				} else {

				    $outbiddeduser = $product_data->get_auction_current_bider();
					$curent_bid = $product_data->get_curent_bid();
					update_post_meta($product_id, '_auction_current_bid', $this->bid);
					update_post_meta($product_id, '_auction_current_bider', $current_user->ID);
					update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));
					delete_post_meta($product_id, '_auction_current_bid_proxy');
					$log_id = $this -> log_bid($product_id, $this->bid, $current_user);
					do_action( 'woocommerce_simple_auctions_outbid',  array( 'product_id' => $product_id ,  'outbiddeduser_id' => $outbiddeduser, 'log_id' => $log_id ) );
				}

			} else {
				wc_add_notice(sprintf( esc_html__('Your bid for &quot;%s&quot; is larger than the current bid', 'wc_simple_auctions'), $product_data -> get_title()),'error');

				return false;
			}

		} else {

			wc_add_notice(sprintf( esc_html__('There was no bid', 'wc_simple_auctions'), $product_data -> get_title()),'error');

			return false;
		}

		do_action('woocommerce_simple_auctions_place_bid', array( 'product_id' => $product_id, 'is_proxy_bid' => $is_proxy_bid , 'log_id' => $log_id ));

		return true;
	}

    /**
     * Log bid
     *
     * @param int, int, int, int
     * @return int
     * 
     */
	public function log_bid ( $product_id, $bid, $current_user, $proxy = 0 ) {
		
		global $wpdb;
		$log_bid_id = false;

		$log_bid = $wpdb -> insert($wpdb -> prefix . 'simple_auction_log', array('userid' => $current_user->ID, 'auction_id' => $product_id, 'bid' => $bid, 'proxy' => $proxy , 'date' => current_time('mysql')), array('%d', '%d', '%f', '%d' , '%s'));

		if( $log_bid ){
			$log_bid_id = $wpdb->insert_id;
		}
		do_action( 'woocommerce_simple_auctions_log_bid', $log_bid_id, $product_id, $bid, $current_user );		

		return $log_bid_id ;
	}

	/**
     * Process auction with sealed bid
     *
     * @param object, float 
     * @return boolean
     * 
     */
	function auction_sealed_placebid ( $product_data, $bid ){
		$outbiddeduser_id = false;
		$current_user = wp_get_current_user();
		$product_id = $product_data->get_id();
		$auction_type = $product_data->get_auction_type();
		
		// Check if bid is needed
		if ( ( $product_data -> is_user_biding($product_id, $current_user->ID) > 0 ) && get_option( 'simple_auctions_curent_bidder_can_bid') !== 'yes') {
	
			wc_add_notice(sprintf( esc_html__('You already placed bid for this auction! ', 'wc_simple_auctions'), $product_data -> get_title()));
			
			return false;				
		}		
		
		if ($auction_type == 'normal') {

			if ( !empty($product_data->get_auction_start_price()) ) {

				if ( $product_data->get_auction_start_price() > $bid )  {

			    	wc_add_notice(sprintf( wp_kses_post( __('Your bid for &quot;%s&quot; is smaller than the minimum bid. Your bid must be at least %s ', 'wc_simple_auctions') ), $product_data -> get_title(),wc_price($product_data->get_auction_start_price() )),'error');
					
					return false;
				} 
			}

			if (  $this->bid > (float)$product_data -> get_curent_bid()) {
				$outbiddeduser_id = $product_data->get_auction_current_bider();
				update_post_meta($product_id, '_auction_current_bid', $this->bid);
				update_post_meta($product_id, '_auction_current_bider', $current_user->ID);
			}			
					
			update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));
					
			$log_id = $this->log_bid($product_id, $bid, $current_user);

			do_action( 'woocommerce_simple_auctions_place_sealedbid', array( 'product_id' => $product_id, 'bid' => $bid, 'current_user' => $current_user, 'log_id' => $log_id, 'outbiddeduser_id' => $outbiddeduser_id) );


		} elseif ($auction_type == 'reverse'){

			if ( !empty($product_data->get_auction_start_price())) {

				if ( $product_data->get_auction_start_price() < $bid )  {

			    	wc_add_notice( sprintf( wp_kses_post( __('Your bid for &quot;%s&quot; is bigger than the maximum bid. Your bid must be at least %s ', 'wc_simple_auctions') ), $product_data -> get_title(),wc_price($product_data->get_auction_start_price() ) ), 'error' );

					return false;
				} 
			}	

			if ( $this->bid < (float)$product_data -> get_curent_bid() ) {
				$outbiddeduser_id = $product_data->get_auction_current_bider();
				update_post_meta($product_id, '_auction_current_bid', $this->bid);
				update_post_meta($product_id, '_auction_current_bider', $current_user->ID);
			}
					
			update_post_meta($product_id, '_auction_bid_count', absint($product_data->get_auction_bid_count() + 1));
					
			$log_id = $this -> log_bid($product_id, $bid, $current_user);

			do_action( 'woocommerce_simple_auctions_place_sealedbid',  array( 'product_id' => $product_id, 'bid' => $bid,  'current_user' => $current_user, 'log_id' => $log_id, 'outbiddeduser_id' => $outbiddeduser_id ));


		} else{
			wc_add_notice( sprintf( esc_html__('There was no bid', 'wc_simple_auctions'), $product_data -> get_title() ), 'error' );

			return false;
		}

		do_action('woocommerce_simple_auctions_place_bid', array( 'product_id' => $product_id ));

		return true;
	}

}