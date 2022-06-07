<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	/**
	* WC_Product_Auction Product Class
	*
	* @class WC_Product_Auction
	*
	* @package Ultimate Auction For WooCommerce
	* @author Nitesh Singh 
	* @since 1.7
	*/
if ( ! class_exists( 'WC_Product_Auction' ) && class_exists( 'WC_Product' ) ) {		
	
class WC_Product_Auction extends WC_Product {
		
	/**
	 * Product Type and Post Type     
	 */
	public $post_type = 'product';
	public $product_type = 'auction';

	/**
	 * Stores product data.     
	 * Single product
	 */
	protected $results_data = array();

	/**
	* Constructor gets the post object and sets the ID for the loaded product.
	*
	* @access public
	* @param $product
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0	
	*
	*/
	public function __construct( $product ) {

		global $sitepress;
		
		if(is_array($this->data))
			$this->data = array_merge( $this->data, $this->results_data );
	
		
		$this->woo_ua_auction_item_condition_array = apply_filters( 'ultimate_woocommerce_auction_product_condition',array( 'new' => __('New', 'ultimate-woocommerce-auction'), 'used'=> __('Used', 'ultimate-woocommerce-auction') ));
		
		parent::__construct( $product );
		$this->is_woo_ua_closed();
		$this->is_woo_ua_started();
	
	}

	/**
	* Returns the Single product or unique ID for this object.	
	*/
	public function get_id() {		
		return $this->id; 
	}

	/**
	 * Get Product Type.
	 *		
	 */
	public function get_type() {
		return 'auction';
	}

	/**
	 * Checks if a product is auction
	 *	
	 */
	function is_auction() {

		return $this->get_type() == 'auction' ? true : false;
	}

	/**
	* Get Product Auction Main Product Id
	*	
	*/
	function get_woo_ua_product_id(){
		
        global $sitepress;
		
		/* For WPML Support - start */
        if (function_exists('icl_object_id') && function_exists('pll_default_language')) { 
            $id = icl_object_id($this->id,'product', false, pll_default_language());
        }
        elseif (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 
        	'get_default_language')) { 
            $id = icl_object_id($this->id,'product', false, 
            	$sitepress->get_default_language());
        }
        else {
            $id = $this->id;
        }
		/* For WPML Support - end */

        return $id;
	}	

	/**
	 * Get Product Auction condition
	 *	
	 */
	function get_woo_ua_condition() {		
		
			if ($this->get_woo_ua_auction_item_condition()){
				return  $this->woo_ua_auction_item_condition_array[$this->get_woo_ua_auction_item_condition()];
			} else {
				return FALSE;
			}
		
	}

	/**
	 * Get Auction Product Condition
	 *	
	 */
	public function get_woo_ua_auction_item_condition( $context = 'view' ) {
		
	   return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_product_condition', true );
	}

	/**
	* Get Auction Product Type
	*	
	*/
	public function get_woo_ua_auction_type( $context = 'view' ) {
		 
		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_type', true );
		
	}

	/**
	* Check Auction Product Reserve Met/Not met
	*	
	*/
	function is_woo_ua_reserve_met() {
		$reserved_price = $this->get_woo_ua_auction_reserved_price();

		if (!empty($reserved_price)){
			if($this->get_woo_ua_auction_type() == 'normal' ){
				return ( (float)$this->get_woo_ua_auction_reserved_price() <= (float)$this->get_woo_ua_auction_current_bid());
			} 
		}
		return TRUE;
	}

	/**
	* Check Auction Product Has Reserve Price
	*	
	*/
	function is_woo_ua_reserved() {

		if ($this->get_woo_ua_auction_reserved_price()){
				return TRUE;
			} else {
				return FALSE;
		}
	}

	/**
	* Get Auction Product Reserve Price
	*	
	*/
	public function get_woo_ua_auction_reserved_price( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_lowest_price', true );

	}

	/**
	* Get Auction Product Opening Price
	*	
	*/
	public function get_woo_ua_auction_start_price( $context = 'view' ) {

	return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_opening_price', true );

	}

	/**
	* Get Auction Product Bid Value
	*	
	*/
	public function woo_ua_bid_value() {
		$auction_bid_increment = ($this->get_woo_ua_increase_bid_value()) ? $this->get_woo_ua_increase_bid_value() : 1;

		if ( ! $this->get_woo_ua_auction_current_bid() ) { 		
		  return $this->get_woo_ua_current_bid();		  
		} else  {
			
			if($this->get_woo_ua_auction_type() == 'normal' ){
				$bid_value = round( wc_format_decimal($this->get_woo_ua_current_bid()) + wc_format_decimal($auction_bid_increment),wc_get_price_decimals());
			    return $bid_value;
			}
		}

		return FALSE;
	}

	/**
	* Get Auction Product Bid Increment Value
	*	
	*/
	function get_woo_ua_increase_bid_value() {

		if ($this->get_woo_ua_auction_bid_increment()){
			return $this->get_woo_ua_auction_bid_increment();
		} else {
			return FALSE;
		}

	}

	/**
	* Get Auction Product Bid Increment Value
	*	
	*/
	public function get_woo_ua_auction_bid_increment( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_bid_increment', true );

	}

	/**
	* Get Auction Product Current bid
	*	
	*/
	function get_woo_ua_current_bid() {

		if ($this->get_woo_ua_auction_current_bid()){
			$current_bid = ((float)$this->get_woo_ua_auction_current_bid());
			return $current_bid;
		}
		    $current_bid = ((float)$this->get_woo_ua_auction_start_price());
			return $current_bid;

	}

	/**
	* Get Auction Product Bid Count
	*	
	*/
	public function get_woo_ua_auction_bid_count( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_bid_count', true );

	}

	/**
	* Get Auction Product Maximum Current Bidder
	*	
	*/
	public function get_woo_ua_auction_max_current_bider( $context = 'view' ) {

	return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_max_current_bider', true );

	}

	/**
	* Get Auction Product Maximum Current Bid	
	*/
	public function get_woo_ua_auction_max_bid( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_max_bid', true );
	}

	/**
	* Get Auction Product End Time
	*	
	*/
	function get_woo_ua_auctions_end_time() {

		if ($this->get_woo_ua_auction_end_dates()){
			return $this->get_woo_ua_auction_end_dates();
		} else {
			return FALSE;
		}

	}

	/**
	* Get Auction Product End Date
	*	
	*/
	public function get_woo_ua_auction_end_dates( $context = 'view' ) {

	  return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_end_date', true );

	}	
	/**
	* Get Auction Product Start Time
	*	
	*/
	function get_woo_ua_auction_start_time() {

		if ($this->get_woo_ua_auction_dates_from()){
			return $this->get_woo_ua_auction_dates_from();
		} else {
			return FALSE;
		}

	}

	/**
	* Get Auction Product Start Date
	*	
	*/
	public function get_woo_ua_auction_dates_from( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_start_date', true );

	}

	/**
	* Get Auction Product Remaining Second Count
	*  
	*/
	function get_woo_ua_remaining_seconds() {

		if ($this->get_woo_ua_auction_end_dates()){
				$second_count = strtotime($this->get_woo_ua_auction_end_dates())  -  (get_option( 'gmt_offset' )*3600);
			return $second_count;

		} else {
			return FALSE;
		}

	}

	/**
	 * Is Auction Product closed
	 *	 
	 */
	function is_woo_ua_closed() {
		
		$id = $this->get_woo_ua_product_id();
		$closed_auction = $this->get_woo_ua_auction_closed();
		if (!empty($closed_auction)){

					return TRUE;

			}else {

			if ($this->is_woo_ua_finished() && $this->is_woo_ua_started() ){

				if ( !$this->get_woo_ua_auction_current_bider() && !$this->get_woo_ua_auction_current_bid()){
					update_post_meta( $id, 'woo_ua_auction_closed', '1');
					update_post_meta( $id, 'woo_ua_auction_fail_reason', '1');	
					do_action('ultimate_woocommerce_auction_close',  $id);	
					$order_id = FALSE;					
					return FALSE;
				}
				if ( $this->is_woo_ua_reserve_met() == FALSE){
					update_post_meta( $id, 'woo_ua_auction_closed', '1');
					update_post_meta( $id, 'woo_ua_auction_fail_reason', '2');
					do_action('ultimate_woocommerce_auction_close',  $id);
					$order_id = FALSE;									
					return FALSE;
				}
				update_post_meta( $id, 'woo_ua_auction_closed', '2');
				add_user_meta( $this->get_woo_ua_auction_current_bider(), 'woo_ua_auction_win', $id);
				do_action('ultimate_woocommerce_auction_close',  $id);
				
				$winneruser = $this->get_woo_ua_auction_current_bider();
				if($winneruser){
					 WC()->mailer();
					 do_action('uwa_won_email_bidder', $id ,$winneruser);
					 do_action('uwa_won_email_admin', $id , $winneruser );
					 //update winner mail sent meta data 
					 update_post_meta( $id, 'woo_ua_winner_mail_sent', '1');
				  }				
				
				return TRUE;

			} else {

				return FALSE;

			}
		}
			
	}

	/**
	* Get Auction Product Closed
	*
	*/
	public function get_woo_ua_auction_closed( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_closed', true );
	}

	/**
	* Is Auction Product Started
	*	 
	*/	
	function is_woo_ua_started() {
		
		$id = $this->get_woo_ua_product_id();
		
		$started_auction = $this->get_woo_ua_auction_has_started();
	
		if($started_auction === '1' ){
			
			return TRUE;
		}

		if ($this->get_woo_ua_auction_dates_from() != false ){
			
			$date1 = new DateTime($this->get_woo_ua_auction_dates_from());
			$date2 = new DateTime(current_time('mysql'));
			if ($date1 < $date2){
				update_post_meta( $id, 'woo_ua_auction_has_started', '1');
				delete_post_meta( $id, 'woo_ua_auction_started');	
				do_action('ultimate_woocommerce_auction_started',$id);	
			} else{
				update_post_meta( $id, 'woo_ua_auction_started', '0');
			}

			return ($date1 < $date2) ;
		} else {
			update_post_meta( $id, 'woo_ua_auction_started', '0');
			return FALSE;
		}
	}

	/**
	* Get Auction Product Has Started
	*	
	*/
	public function get_woo_ua_auction_has_started( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_has_started', true );

	}

	/**
	* Is Auction Product Has Finished
	*	
	*/
	function is_woo_ua_finished() {
		
		$end_dates = $this->get_woo_ua_auction_end_dates();
		
		if (!empty($end_dates) ){
			
			$date1 = new DateTime($this->get_woo_ua_auction_end_dates());
			$date2 = new DateTime(current_time('mysql'));

			if( $date1 < $date2){
				
		 	    return TRUE;
			 
			} else{
				
			   return FALSE;
			}
			
		} else {
			return FALSE;
		}
	}

	/**
	* Check if Auction Product is on user watchlist
	*	
	*/
	public function is_woo_ua_user_watching( $user_ID = false){

		$post_id = $this->get_woo_ua_product_id();

		if(!$user_ID){
			$user_ID = get_current_user_id();
		}

		$users_watching_auction = get_post_meta( $post_id, 'woo_ua_auction_watch', FALSE );

		if(is_array($users_watching_auction) && in_array($user_ID, $users_watching_auction)){
			
		  $return =  true;
		  
		} else{
			
		 $return =  false;
		}

		return $return;
	}

	/**
	* Get Auction Product Payed
	*	
	*/
	public function get_woo_ua_auction_payed( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_payed', true );
	}

	/**
	* Get Auction Product Current Bid
	*	
	*/
	public function get_woo_ua_auction_current_bid( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_current_bid', true );

	}

	/**
	* Get Auction Product Current Bidder
	*	
	*/
	public function get_woo_ua_auction_current_bider( $context = 'view' ) {
		 
		 return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_current_bider', true );
		
	}

	/**
	* Get Auction Product Bid History
	*	
	*/
	function woo_ua_auction_history($datefrom = FALSE, $user_id = FALSE) {
		global $wpdb;		
		$wheredatefrom ='';

		$id = $this->get_woo_ua_product_id();

		if($datefrom){
			$wheredatefrom =" AND CAST(date AS DATETIME) > '$datefrom' ";
		}
		if($user_id){
			$wheredatefrom =" AND userid = $user_id";
		}
		$tablename ='woo_ua_auction_log';
		
		
		$history = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."woo_ua_auction_log WHERE `auction_id` =  %d ".$wheredatefrom." ORDER BY  `date` desc , `bid`  desc ,`id`  desc  ",array($id) );
		 
		$history = $wpdb->get_results( $history);
		return $history;
	}
	
	/**
     * Get Auction Product last Bid History 
     *
     * @access public
     * @return object
     *
     */
	function woo_ua_auction_history_last($id) {
		global $wpdb;
		$datetimeformat = get_option('date_format').' '.get_option('time_format');	
		$log_data = '';
		
		$row_qiery = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."woo_ua_auction_log WHERE `auction_id` =  %d ORDER BY `date` desc ",array($id) );
		
		
		$log_value = $wpdb->get_row($row_qiery);
		if($log_value){
			$log_data = "<tr>";
				$log_data .= "<td class='bid_username'>".uwa_user_display_name($log_value->userid)."</td>";
	            $log_data .= "<td class='bid_date'>".mysql2date($datetimeformat ,$log_value->date)."</td>";
	            $log_data .= "<td class='bid_price'>".wc_price($log_value->bid)."</td>"; 
	         $log_data .= "</tr>";
	    }     
		return $log_data;
	}
	
	/**
	* Over write Woocommerce get_price_html for Auction Product
	*	
	*/
	public function get_price_html( $price = '' ) {
		
		$id = $this->get_woo_ua_product_id();

		if ($this->is_woo_ua_closed() && $this->is_woo_ua_started() ){
			
			if ($this->get_woo_ua_auction_closed() == '3'){
				
				$price = __('<span class="woo-ua-sold-for sold_for">Sold for</span>: ','ultimate-woocommerce-auction').wc_price($this->get_price());
			}
			else{
				
				if ($this->get_woo_ua_auction_current_bid()){
					if ( $this->is_woo_ua_reserve_met() == FALSE){
						
						$price = __('<span class="woo-ua-winned-for reserve_not_met">Reserve price Not met!</span> ','ultimate-woocommerce-auction');
						
					} else{
						$price = __('<span class="woo-ua-winned-for winning_bid">Winning Bid</span>: ','ultimate-woocommerce-auction').wc_price($this->get_woo_ua_auction_current_bid());
					}
				}
				else{
					$price = __('<span class="woo-ua-winned-for expired">Auction Expired</span> ','ultimate-woocommerce-auction');
				}


			}

		} elseif(!$this->is_woo_ua_started()){
			
			$price = '<span class="woo-ua-auction-price starting-bid" data-auction-id="'.$id.'" data-bid="'.$this->get_woo_ua_auction_current_bid().'" data-status="">'.__('<span class="uwa-starting auction">Starting bid</span>: ','ultimate-woocommerce-auction').wc_price($this->get_woo_ua_current_bid()).'</span>';
			
		} else {
			
				if (!$this->get_woo_ua_auction_current_bid()){
					$price = '<span class="woo-ua-auction-price starting-bid" data-auction-id="'.$id.'" data-bid="'.$this->get_woo_ua_auction_current_bid().'" data-status="running">'.__('<span class="woo-ua-current auction">Starting bid</span>: ','ultimate-woocommerce-auction').wc_price($this->get_woo_ua_current_bid()).'</span>';
				} else {
					$price = '<span class="woo-ua-auction-price current-bid" data-auction-id="'.$id.'" data-bid="'.$this->get_woo_ua_auction_current_bid().'" data-status="running">'.__('<span class="woo-ua-current auction">Current bid</span>: ','ultimate-woocommerce-auction').wc_price($this->get_woo_ua_current_bid()).'</span>';
				}
			

		}
		return apply_filters( 'woocommerce_get_price_html', $price, $this );
	}
	
	/**
	*Get the Product's Price.
	*	
	*/
	function get_price($context = 'view') {
		
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {

			if ($this->is_woo_ua_closed()){

				if ($this->get_woo_ua_auction_closed() == '3'){
					
					return apply_filters( 'woocommerce_get_price', $this->regular_price, $this );
				}
				if ($this->is_woo_ua_reserved()) {

					return apply_filters( 'woocommerce_get_price', $this->woo_ua_auction_current_bid, $this );
				}
			}
			return apply_filters( 'woocommerce_get_price', $this->price, $this );
		} else {
			if ($this->is_woo_ua_closed()){
				
				$empty_price = $this->get_prop( 'price', $context );

				if(empty($empty_price) OR $this->get_woo_ua_auction_closed() == '2') {
					
					$price = null;					
					
					$price= get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_current_bid', true );

					$this->set_price($price);
				}
				

				return $this->get_prop( 'price', $context );
			}

			return apply_filters( 'woocommerce_product_get_price',get_post_meta( $this->get_woo_ua_product_id(), '_price', true ),$this);

		}

		
	}	

	/**
	*Get the Product's regular price.
	*	
	*/
	public function get_regular_price( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), '_regular_price', true );
	}

	/**
	* Get the Add to url used mainly in loops.
	*
	*/
	public function add_to_cart_url() {
		
		$id = $this->get_woo_ua_product_id();
		
		return apply_filters( 'woocommerce_product_add_to_cart_url', get_permalink( $id ), $this );
	}

	/**
	* Wrapper for get_permalink
	*/
	public function get_permalink() {
		
		$id = $this->get_woo_ua_product_id();
		
		return get_permalink( $id );
	}

	/**
	* Get Auction Product add to cart button text
	*	
	*/
	public function add_to_cart_text() {
		if (!$this->is_woo_ua_finished() && $this->is_woo_ua_started() ){
			$text = __( 'Bid now', 'ultimate-woocommerce-auction' ) ;
		} elseif($this->is_woo_ua_finished()  ){
			$text = __( 'Expired', 'ultimate-woocommerce-auction' ) ;
		} elseif(!$this->is_woo_ua_finished() && !$this->is_woo_ua_started()  ){
			$text =  __( 'Pending', 'ultimate-woocommerce-auction' ) ;
		}

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	* Get Auction Product Fail Reason
	*	
	*/
	public function get_woo_ua_auction_fail_reason( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_auction_fail_reason', true );
	}	

	/**
	* Get Auction Product Order Id
	*	
	*/
	public function get_woo_ua_order_id( $context = 'view' ) {

		return get_post_meta( $this->get_woo_ua_product_id(), 'woo_ua_order_id', true );
		
	}
	
	/**
	* Get Auction Product User Max Bid
	*	 
	*/
	public function get_woo_ua_user_max_bid( $auction_id , $user_ID = false){

		global $wpdb;

		$wheredatefrom ='';
		$datefrom = false;

		$id = $this->get_woo_ua_product_id();

		if($datefrom){
			$wheredatefrom =" AND CAST(date AS DATETIME) > '$datefrom' ";
		}

		if(!$user_ID){
			$user_ID = get_current_user_id();
		}
		
		$maxbid = $wpdb->get_var( $wpdb->prepare( "SELECT bid FROM ".$wpdb->prefix."woo_ua_auction_log  WHERE auction_id = %d and userid = %d ".$wheredatefrom."  ORDER BY  `bid` desc", array($auction_id,$user_ID,) ) );

		return $maxbid;

	}
	
}
}