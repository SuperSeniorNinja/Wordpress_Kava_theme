<?php

// Exit if accessed directly

if ( !defined( 'ABSPATH' ) ) exit;

/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin, 
 * rename it, and work inside the copy. If you modify this plugin directly and 
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */

$auction_type  = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
class Woo_Ua_Logs_List_Table extends WP_List_Table {
	
	public $allData;
    public $auction_type;

	
	public function uwa_auction_get_data($per_page, $page_number){
		global $sitepress;	

		$pagination = ((int)$page_number - 1) * (int)$per_page;
		$search = (isset($_POST['s'])) ? sanitize_key($_POST['s']) : '';
		$auction_type  = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';	
		$meta_query = array(
						'relation' => 'AND',
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							),							
						);
		
		if ($auction_type == 'expired') {						
			$meta_query= array(
						'relation' => 'AND',
							array(			     
								'key' => 'woo_ua_auction_closed',
								'value' => array('1','2','3','4'),
								'compare' => 'IN',
							),							
						);
		}
		
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => $per_page,
			'offset' => $pagination,	
			's'=> $search,
			'meta_key' => 'woo_ua_auction_last_activity',
			'orderby' => 'meta_value_num',
			'order'  => 'DESC',
			'meta_query' => array($meta_query),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')),
			'auction_arhive' => TRUE
		);
		
		/* For WPML Support - start */		
		$filter_id = (isset($_REQUEST['uwa_auction_id'])) ? absint($_REQUEST['uwa_auction_id']) : '';
		if($filter_id!=""){
			$args['p']=$filter_id;			
		}	 
		
		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 
			'get_current_language')) {
		   
			$args['suppress_filters']=0;	
		}
		/* For WPML Support - end */
		
		$auction_item_array = get_posts($args);
		$data_array = array();
		foreach ($auction_item_array as $single_auction) {

			global $wpdb; 
			$datetimeformat = get_option('date_format').' '.get_option('time_format');	
			$row = array();
			$auction_ID = $single_auction->ID;
			$auction_title = $single_auction->post_title;
	        $row['title'] = '<a href="'.get_permalink( $auction_ID ).'">'.get_the_title(  $auction_ID ).'</a>';
						
			
			$create_date = get_post_meta($auction_ID, 'woo_ua_auction_start_date', true);
			$row['create_date'] = mysql2date($datetimeformat,$create_date);
			
			$ending_date = get_post_meta($auction_ID, 'woo_ua_auction_end_date', true);
			$row['ending_date'] = mysql2date($datetimeformat,$ending_date);
			
			$opening_price = get_post_meta($auction_ID, 'woo_ua_opening_price', true);
			$current_bid_price = get_post_meta($auction_ID, 'woo_ua_auction_current_bid', true);
			$row['opening_price'] = wc_price($opening_price);
			if(!empty($current_bid_price)){
				$row['opening_price'] = wc_price($opening_price).' / '.wc_price($current_bid_price);
			}
						
			$row['bidders'] = ''; 
			$results = array();
			$row_bidders = '';
						
			/*$query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$single_auction->ID.' ORDER BY id DESC LIMIT 2';*/

			$tbl_log = $wpdb->prefix.'woo_ua_auction_log';
			$query_bidders = $wpdb->prepare("SELECT * FROM $tbl_log WHERE auction_id = %d ORDER BY id DESC LIMIT 2", 
				$single_auction->ID);

            $results = $wpdb->get_results($query_bidders);			
			if (!empty($results)) {
               				
                foreach ($results as $result) {
						
				$userid	= $result->userid;
				$userdata = get_userdata( $userid );
				$bidder_name = $userdata->user_nicename;
                if ($userdata){					
					
					$bidder_name = "<a href='".get_edit_user_link( $userid )."' target='_blank'>".$bidder_name.'</a>';
					
				} else {
					
				 	$bidder_name = 'User id:'.$userid;
                } 
				$bid_amt = wc_price($result->bid);
				$bid_time = mysql2date($datetimeformat,$result->date);
				$row_bidders .= "<tr>";					
				$row_bidders .= "<td>".$bidder_name." </td>";					
				$row_bidders .= "<td>".$bid_amt."</td>";					
				$row_bidders .= "<td>".$bid_time."</td>";					
				$row_bidders .= "</tr>";					
								
				
                }
				//$row['bidders'] = "<div class='uwa-bidder-list-".$single_auction->ID.">";
				$row['bidders'] = "<table class='uwa-bidslist uwa-bidder-list-".$auction_ID."'>";
				$row['bidders'] .= $row_bidders;				
				$row['bidders'] .= "</table>";			
				

				/*$query_bidders_count = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$single_auction->ID.' ORDER BY id DESC';*/
								
				$tbl_log = $wpdb->prefix.'woo_ua_auction_log';
				$query_bidders_count = $wpdb->prepare("SELECT * FROM $tbl_log WHERE auction_id = %d ORDER BY id DESC", 
					$single_auction->ID);
				
                $results_count = $wpdb->get_results($query_bidders_count);	
				if (count($results_count) > 2) {
                        $row['bidders'] .= "
                            <a href='#' class='uwa-see-more show-all'  rel='".$auction_ID."' >".__('See more', 'ultimate-woocommerce-auction').'</a>';
                }
				
			} else {
				
				$row['bidders'] = __('No bids placed', 'ultimate-woocommerce-auction');
			}
			
			
			$data_array[] = $row;
		} /* end of foreach */
	   
	   
		$this->allData = $data_array;
		return $data_array;
	}
	
	/**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns(){
		$auction_type  = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
        $columns = array(           
            'title' => __('Auction Title', 'ultimate-woocommerce-auction'),
            'create_date' => __('Creation Date', 'ultimate-woocommerce-auction'),
            'ending_date' => __('Ending Date', 'ultimate-woocommerce-auction'),
            'opening_price' => __('Starting / Current Price', 'ultimate-woocommerce-auction'),
            'bidders' => __('Bidders Name / Bid / Time', 'ultimate-woocommerce-auction'),                    
           
        );
		
		if ($auction_type == 'expired') {
			 $columns = array(           
				'title' => __('Auction Title', 'ultimate-woocommerce-auction'),
				'create_date' => __('Creation Date', 'ultimate-woocommerce-auction'),
				'ending_date' => __('End Date', 'ultimate-woocommerce-auction'),
				'opening_price' => __('Starting / Final Price', 'ultimate-woocommerce-auction'),
				 'bidders' => __('Bidders Name / Bid / Time', 'ultimate-woocommerce-auction'),                    
			   
			);
		}
		
        return $columns;
    }

	 /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns(){
        $sortable_columns = array(
            'title' => array('title', true),
            'create_date' => array('create_date', true),
            'ending_date' => array('ending_date', true),
            'opening_price' => array('opening_price', true),
            'bidders' => array('bidders', true),           
          
        );
        return $sortable_columns;
    }

	/**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items(){

    	global $sitepress;
		
		$search = (isset($_POST['s'])) ? sanitize_key($_POST['s']) : '';
		$this->auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
		$columns = $this->get_columns();
		$hidden = array();
		$per_page = '';
		$current_page = '';
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$orderby =  isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'title';		
		if ($orderby === 'title') {
			$this->items = $this->uwa_auction_sort_array($this->uwa_auction_get_data($per_page, $current_page));
		} else {
			$this->items = $this->uwa_auction_get_data($per_page, $current_page);
		}
		$per_page = 20;
		$current_page = $this->get_pagenum();
		$auction_type  = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
		$meta_query = array(
						'relation' => 'AND',
							array(			     
								'key'  => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							),							
						);
		
		if ($auction_type == 'expired') {						
			$meta_query= array(
						'relation' => 'AND',
							array(			     
								'key' => 'woo_ua_auction_closed',
								'value' => array('1','2','3','4'),
								'compare' => 'IN',
							),							
						);
		}
		
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,	
			's'=> $search,
			'meta_key' => 'woo_ua_auction_last_activity',
			'orderby' => 'meta_value_num',
			'order'  => 'DESC',
			'meta_query' => array($meta_query),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 
				'terms' => 'auction')),
			'auction_arhive' => TRUE
		);

		/* For WPML Support - start */
		$filter_id = (isset($_REQUEST['uwa_auction_id'])) ? absint($_REQUEST['uwa_auction_id']) : '';	
		if($filter_id!=""){
			$args['p']=$filter_id;			
		}
		if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {
		     $args['suppress_filters']=0;	
		}		
		/* For WPML Support - end */

		$auctions = get_posts($args);
	    $total_items = count($auctions);
	    //$this->found_data = array_slice($this->allData, (($current_page - 1) * $per_page), $per_page);

	    $this->set_pagination_args(array(
	        'total_items' => $total_items,
	        'per_page' => $per_page,
	    ));

	    $this->items = $this->uwa_auction_sort_array($this->uwa_auction_get_data($per_page, 
	    	$current_page));
	}


	public function get_result_e(){
    	return $this->allData;
	}

	public function uwa_auction_sort_array($args){

	    if (!empty($args)) {
			
	        $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title';

			if($orderby === 'create_date') {
				
	            $order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';	            
	        }
			else if($orderby === 'ending_date') {
				
	            $order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';
	        }
			else {
	            $order = 'desc';
	        }	
	       			
	        foreach ($args as $array) {
	            $sort_key[] = $array[$orderby];
	        }
	        if ($order == 'asc') {
	            array_multisort($sort_key, SORT_ASC, $args);
	        } else {
	            array_multisort($sort_key, SORT_DESC, $args);
	        }
	    }

	    return $args;
	}

	public function column_default($item, $column_name){
	    switch ($column_name) {
	        case 'title':
	        case 'create_date':
	        case 'ending_date':
	        case 'opening_price':
	        case 'bidders':	  
	        return $item[ $column_name ];
	        default:
	            return print_r($item, true); //Show the whole array for troubleshooting purposes
	        }
    }
	
	
} /* end of class */


	/**
	 * Auctions table 
	 *
	 * @since 1.0.0 
	 */
	function woo_ua_list_page_handler_display() {
		 	// menu list 
			
			global $wpdb;
			$table = new Woo_Ua_Logs_List_Table();
			$search_s = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
			
			if( $search_s ){
            	$table->prepare_items($search_s);
			} 
			else {
				$table->prepare_items();
			}
			
		?>
				
		<div class="wrap" id="uwa_auction_setID">
			<div id='icon-tools' class='icon32'></br></div>
			
			<h2 class="uwa_main_h2"><?php _e( 'Ultimate Auction for WooCommerce', 'ultimate-woocommerce-auction' ); ?>
				<span class="uwa_version_text"><?php _e( 'Version :', 'ultimate-woocommerce-auction' ); ?> <?php echo esc_attr(WOO_UA_VERSION); ?></span></h2>	 
			
			<div class="get_uwa_pro">

	         	<!-- <a href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=horizontal banner&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url(WOO_UA_ASSETS_URL);?>/images/UWCA_row.jpg" alt="" /> </a>
		                
		    	<div class="clear"></div> -->
		    	<?php
		    	global $current_user;
				$user_id = $current_user->ID;
				/* If user clicks to ignore the notice, add that to their user meta */
				if (isset($_GET['uwa_pro_add_plugin_notice_ignore']) && '0' == absint($_GET['uwa_pro_add_plugin_notice_ignore'])) {
					update_user_meta($user_id, 'uwa_pro_add_plugin_notice_disable', 'true', true);
				}
				if (current_user_can('manage_options')) {
					$user_id = $current_user->ID;
					$user_hide_notice = get_user_meta( $user_id, 'uwa_pro_add_plugin_notice_disable', true );				
					if ($user_hide_notice != "true") {
													?>
					<div class="notice notice-info">
						<div class="get_uwa_pro" style="display:flex;justify-content: space-evenly;">
							<a href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=admin notice&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url(WOO_UA_ASSETS_URL);?>/images/UWCA_row.jpg" alt="" /> </a>
							<p class="uwa_hide_free">
							<?php
							//printf(__('<a href="%s">Hide Notice</a>', 'ultimate-woocommerce-auction'),esc_attr(add_query_arg('uwa_pro_add_plugin_notice_ignore', '0')));?>
							</p>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'uwa_pro_add_plugin_notice_ignore', '0' ), 'ultimate-woocommerce-auction', '_ultimate-woocommerce-auction_nonce' ) ); ?>" class="woocommerce-message-close notice-dismiss" style="position:relative;float:right;padding:9px 0px 9px 9px;text-decoration:none;"></a>									
							<div class="clear"></div>
						</div>
					</div>
						<?php
					}
				}	
				?>	
		    </div>
			
			<div id="uwa-auction-banner-text">	
				<?php _e('If you like <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank"> our plugin working </a> with WooCommerce, please leave us a <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank">★★★★★</a> rating. A huge thanks in advance!', 'ultimate-woocommerce-auction' ); ?>	 
		    </div>

			<?php 
			/* $manage_setting_tab  = isset( $_REQUEST['auction_type'] ) ? esc_attr( $_REQUEST['auction_type'] ) : 'live'; */
			$manage_setting_tab  = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live'; 
	       
			?>							
			
			<div class="uwa-action-container" style="float:right;margin-right: 10px;">
					<form action="" method="POST">					
						<input type="text" name="s" value="<?php echo esc_attr( $search_s ); ?>" />
						<input type="submit" class="button-secondary" 
							name="wdm_auction_search_submit" 							
							value="<?php esc_html_e('Search', 'ultimate-woocommerce-auction'); ?>" />
					</form>
	        </div>

		    <ul class="subsubsub">
				<li><a href="?page=uwa_manage_auctions&auction_type=live" class="<?php echo esc_attr($manage_setting_tab) == 'live' ? 'current' : ''; ?>"><?php _e('Live Auctions', 'ultimate-woocommerce-auction');?></a>|</li>
				<li><a href="?page=uwa_manage_auctions&auction_type=expired" class="<?php echo esc_attr($manage_setting_tab) == 'expired' ? 'current' : ''; ?>"><?php _e('Expired Auctions', 'ultimate-woocommerce-auction');?></a></li>
				
		    </ul><br class="clear">
			<form id="persons-table" method="GET">
			<?php $page_s =  isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';?>				
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_s ); ?>"/>	<?php $table->display();?>					
			</form>
		</div>			
			
		<?php	
	} 