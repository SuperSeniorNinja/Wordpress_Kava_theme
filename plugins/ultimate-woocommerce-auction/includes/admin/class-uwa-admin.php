<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh 
 * @since 1.0
 */
class UWA_Admin {
	
	
	private static $instance;	
	public $woo_ua_auction_item_condition;
	/**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	public function __construct() {
		
		//Admin Menu Page init
		add_action('admin_menu', array($this, 'uwa_admin_main_menu'));
				
		$current_user = wp_get_current_user();
		
		if ( in_array( 'administrator', (array) $current_user->roles )  ||
			in_array( 'shop_manager', (array) $current_user->roles )) {
			
			//Create new Product Type - Auction
			add_filter( 'product_type_selector', array( $this, 'uwa_add_auction_product' ) );
		}
		
		//Create Auction Product Tab
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'uwa_custom_product_tabs' ) );
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'uwa_hide_attributes_data_panel' ));
		add_action( 'woocommerce_product_data_panels', array( $this, 'uwa_options_product_tab_content' ));
		
		//Save Auction Product Data
		add_action( 'woocommerce_process_product_meta_auction', array( $this, 
			'uwa_save_auction_option_field' ));	

		add_action( 'woocommerce_process_product_meta', array( $this, 
			'uwa_process_product_option_field' ));
		
		//Auction Product Metabox - Bid History
		add_action( 'add_meta_boxes_product', array( $this, 'uwa_add_auction_metabox') );	
		
		//Auction Product Condition
		$this->woo_ua_auction_item_condition =  array('new' => __('New', 'ultimate-woocommerce-auction'), 'used' => __('Used', 'ultimate-woocommerce-auction'));		
		
		//Emails For Admin 
     	 add_filter('woocommerce_email_classes', array($this, 'uwa_register_email_classes'));
		 
		//Emails for html/plain
        add_filter( 'woocommerce_locate_core_template', array( $this, 'uwa_locate_core_template' ), 10, 3 );	
		
		//Filter On Admin product List page For Auction Product Type
		add_action('restrict_manage_posts', array($this, 'admin_uwa_filter_restrict_manage_posts'));		
		add_filter('parse_query', array($this, 'admin_uwa_filter'));
		
		//processing auction  product  item with woocoomrce order.
		add_action('woocommerce_order_status_processing', array($this, 'uwa_auction_payed'), 10, 1);
		//add_action('woocommerce_order_status_completed', array($this, 'uwa_auction_payed'), 10, 1);
		add_action('woocommerce_order_status_completed', array($this, 'uwa_auction_completed'), 10, 1);
		add_action('woocommerce_order_status_cancelled', array($this, 'uwa_auction_order_canceled'), 10, 1);
		add_action('woocommerce_order_status_refunded', array($this, 'uwa_auction_order_canceled'), 10, 1);
		add_action('woocommerce_checkout_update_order_meta', array($this, 'uwa_auction_order'), 10, 2);
		
		//Bid Cancel By Admin
		add_action("wp_ajax_admin_cancel_bid", array($this, "wp_ajax_admin_cancel_bid"));
		
		// Delete Auction product Meta While duplicating Products
		add_action("woocommerce_duplicate_product", array($this, "uwa_woocommerce_duplicate_product"));
		
		//custom js
		add_action( 'admin_footer', array( $this, 'uwa_auction_custom_js' ) );
		
		//new auction status in admin side in product list page		
		add_filter( 'manage_edit-product_columns',array( $this, 'uwa_auctions_status_columns'), 20 );
		
		add_action( 'manage_product_posts_custom_column', array( $this, 'uwa_auctions_status_columns_status' ),10, 2  );

	    add_action( 'admin_notices', array( $this,'uwa_manage_auction_page_admin_notice') );
	}
	
	
	public function uwa_manage_auction_page_admin_notice() { 
	
	if( isset($_GET[ 'page' ])  AND  sanitize_key($_GET[ 'page' ]) == "uwa_manage_auctions") {
	
	$products_page_url = admin_url('edit.php?post_type=product'); ?>	
    
	 <div class="notice notice-warning is-dismissible">
      <p> <?php printf(__('You can Manage All Auctions via Products List <a href="%s" target="blank">  Click Here. </a>', 'ultimate-woocommerce-auction'), esc_url($products_page_url)); ?> </p>  
	  </div>
    <?php  	}	
	
   }


	/**
	* Add Page In Admin Menu.
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
	public function uwa_admin_main_menu(  ){

		global $wp_version;
		if($wp_version >= '3.8')
			$menu_icon = WOO_UA_ASSETS_URL.'images/uwa_admin_menu_icon.png';
		else
			$menu_icon = WOO_UA_ASSETS_URL.'images/uwa_admin_menu_icon_black.png';	
				
		add_menu_page(__('Auctions', 'ultimate-woocommerce-auction'), __('Auctions', 'ultimate-woocommerce-auction'), 'manage_options', 'uwa_general_setting',  array($this, 'uwa_auction_setting_page_handler'),$menu_icon, 57.77);			
				
		add_submenu_page('uwa_general_setting', __('Settings', 'ultimate-woocommerce-auction'), __('Settings', 'ultimate-woocommerce-auction'), 'manage_options', 'uwa_general_setting', array($this, 'uwa_auction_setting_page_handler'));		
				
		add_submenu_page('uwa_general_setting', __('Auctions', 'ultimate-woocommerce-auction'), __('Auctions', 'ultimate-woocommerce-auction'), 'manage_options', 'uwa_manage_auctions', array($this, 'uwa_manage_auction_logs_page_handler'));
	
		add_submenu_page('uwa_general_setting', __('PRO Features', 'ultimate-woocommerce-auction'), __('PRO Features', 'ultimate-woocommerce-auction'), 'manage_options', 'uwa_why_pro', array($this, 'uwa_uwa_why_pro_page_handler'));

	}
	public function uwa_auction_setting_page_handler() {
		
		include_once( WOO_UA_ADMIN . '/uwa-admin-setting.php');				
	}
		
	public function uwa_manage_auction_logs_page_handler() {
		
		include_once( WOO_UA_ADMIN . '/uwa_manage_auctions.php');
		woo_ua_list_page_handler_display();
				
	}
	public function uwa_uwa_why_pro_page_handler() {		
		include_once( WOO_UA_ADMIN . '/uwa-why-pro.php');				
	}
	/**
	* Add to product type drop down.
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
	public function uwa_add_auction_product( $types ){
		// Key should be exactly the same as in the class
		$types[ 'auction' ] = __( 'Auction Product', 'ultimate-woocommerce-auction' );
		return $types;
	}
	/**
	* Add a custom product tab.
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
	public  function uwa_custom_product_tabs( $product_data_tabs) {	  
	    $auction_tab = array(
						'auction_tab' => array(
									'label'  => __('Auction', 'ultimate-woocommerce-auction'),
									'target' => 'auction_options',
									'class'  => array('show_if_auction' , 'hide_if_grouped', 'hide_if_external','hide_if_variable','hide_if_simple' ),
								),
					);

				return $auction_tab + $product_data_tabs;
    }
	/**
	* Hide Attributes data panel.
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
	public  function uwa_hide_attributes_data_panel( $tabs) {
        
        return $tabs;
    }
	/**
	* Contents of the Auction  Product options product tab.
	* 
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
     public function uwa_options_product_tab_content() {
        global $post;
			$product = wc_get_product($post->ID);

			$woo_ua_form_type = "add_product";
			if(isset($_GET['action']) && $_GET['action'] == "edit"){
	        	$woo_ua_form_type = "edit_product";
	        }

	        /* when auction is expired */
	        $readonly = "";
	        $disabled = "";
	        $is_auction_expired = "no";
			if ((method_exists( $product, 'get_type') && $product->get_type() == 'auction') && 
				$product->get_woo_ua_auction_closed() ) {
				
				$readonly = array('readonly' => 'readonly');
				$disabled = array('disabled' => 'disabled');
				$is_auction_expired = "yes";
			}

			?>

				<div id='auction_options' class='panel woocommerce_options_panel'>
					<div class='options_group'>
						<?php

						/* product is added or updated */
						woocommerce_wp_hidden_input( array(
							'id'			=> 'woo_ua_auction_form_type',
							'value'         => $woo_ua_form_type,						
							//'data_type' 	=> 'text',
						));

						/* product is expired or not */
						woocommerce_wp_hidden_input( array(
							'id'			=> 'woo_ua_auction_expired',
							'value'         => $is_auction_expired
						));

						if(isset($_GET['action']) && sanitize_text_field($_GET['action'] == "edit")){

							/* add fields during edit product */
							woocommerce_wp_hidden_input( array(
								'id'			=> 'woo_ua_product_type',
								'value'         => $product->get_type()
							));
							woocommerce_wp_hidden_input( array(
								'id'			=> 'woo_ua_product_id',
								'value'         => absint($_GET['post'])
							));
	        			}

						woocommerce_wp_select( array(
							'id' => 'woo_ua_product_condition', 
							'label' => __('Product Condition', 'ultimate-woocommerce-auction'),
							'options' => apply_filters('ultimate_woocommerce_auction_product_condition' ,$this->woo_ua_auction_item_condition))
						);

						woocommerce_wp_text_input( array(
							'id'			=> 'woo_ua_opening_price',
							'label'			=> __( 'Opening Price', 'ultimate-woocommerce-auction' ). ' (' . get_woocommerce_currency_symbol() . ')',
							'desc_tip'		=> 'true',
							'description'	=> __( 'Set the price where the price of the product will start from.', 'ultimate-woocommerce-auction' ),
							'data_type' 			=> 'price',
							'custom_attributes' => array(
									'step' => 'any',
									'min' => '0',
								)
						));		
						  
						woocommerce_wp_text_input( array(
							'id'			=> 'woo_ua_lowest_price',            
							'label'			=>  __('Lowest Price to Accept', 'ultimate-woocommerce-auction') . ' (' . get_woocommerce_currency_symbol() . ')',
							'desc_tip'		=> 'true',							
							'description'	=> __( 'Set Reserve price for your auction.', 'ultimate-woocommerce-auction' ),
							'data_type' => 'price',
							'custom_attributes' => array(
									'step' => 'any',
									'min' => '0',
								)
						) );
						
						$default_bid_inc = 1;
						$get_inc_val = get_post_meta($post->ID, 'woo_ua_bid_increment', 
							true);
						if($get_inc_val >= 1){
							$bid_inc_val = $get_inc_val;
						}
						else{
							$bid_inc_val = $default_bid_inc;	
						}

						woocommerce_wp_text_input( array(
							'id'			=> 'woo_ua_bid_increment',
							'label'			=> __( 'Bid Increment', 'ultimate-woocommerce-auction' ) . ' (' . get_woocommerce_currency_symbol() . ')',
							'desc_tip'		=> 'true',							
							'description'	=> __( 'Set an amount from which next bid should start.', 'ultimate-woocommerce-auction' ),
							'data_type' => 'price',
							'value' => $bid_inc_val,
							'custom_attributes' => array(
									'step' => 'any',
									'min' => '1',									
								)
						) );
						 
						woocommerce_wp_text_input( array(
							'id'			=> '_regular_price',
							'label'			=> __( 'Buy now price', 'ultimate-woocommerce-auction' ). ' (' . get_woocommerce_currency_symbol() . ')',
							'desc_tip'		=> 'true',
							'data_type' => 'price',
							'description'	=> __( 'Visitors can buy your auction by making payments via Available payment method.', 'ultimate-woocommerce-auction' ),								
						) );
						 
						 
						$nowdate =  wp_date('Y-m-d H:i:s',strtotime('+1 day', time()),get_uwa_wp_timezone());
						$end_date = get_post_meta($post->ID, 'woo_ua_auction_end_date', true) ?  : $nowdate;	

						$readonly ="";
						if ((method_exists( $product, 'get_type') && $product->get_type() == 'auction') && $product->get_woo_ua_auction_closed() ) {
							$readonly = array('readonly' => 'readonly');
						}
						woocommerce_wp_text_input( array(
							'id'			=> 'woo_ua_auction_end_date',
							'label'			=> __( 'Ending Date', 'ultimate-woocommerce-auction' ),
							'desc_tip'		=> 'true',
							'description'	=> __( 'Set the end date of Auction Product.', 'ultimate-woocommerce-auction' ),
							'type' 			=> 'text',			
							'class'         => 'datetimepicker',
							'value'         => $end_date,
							'custom_attributes' => $readonly,
						) );
						?>
							<div class="uwa_admin_current_time">
								<?php	                               						
								printf(__('Current Blog Time is %s', 'ultimate-woocommerce-auction'), '<strong>'.get_uwa_now_date().'</strong> ');
								printf(__('Timezone: %s', 'ultimate-woocommerce-auction'), '<strong>'.wp_timezone_string().'</strong> ');	
								echo sprintf( __( "%sChange.%s", "ultimate-woocommerce-auction" ), '<a href="'.esc_url(admin_url('options-general.php?#timezone_string')).'" target="_blank">', '</a>' ); ?>									
						</div>						
					</div>
				</div>
	<?php	 
    }

    /**
	 * Save Auction Product Data.
	 * 
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh 
	 * @since 1.0
	 */
    function uwa_save_auction_option_field( $post_id ) {
		global $wpdb, $woocommerce, $woocommerce_errors;
		$product_type = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );		
		if ( $product_type == 'auction' ) {
			
			update_post_meta($post_id, '_manage_stock', 'yes');
			update_post_meta($post_id, '_stock', '1');
			update_post_meta($post_id, '_backorders', 'no');
			update_post_meta($post_id, '_sold_individually', 'yes');
			
			
			if ( isset($_POST['_regular_price']) ) {
				update_post_meta( $post_id, '_regular_price', wc_format_decimal(wc_clean($_POST['_regular_price'] )));
				update_post_meta( $post_id, '_price', wc_format_decimal(wc_clean($_POST['_regular_price'] )));
			}
			
			
			if( isset($_POST['woo_ua_product_condition']) ) {
				update_post_meta( $post_id, 'woo_ua_product_condition', sanitize_text_field($_POST['woo_ua_product_condition'] ));
			}
			
			if ( isset($_POST['woo_ua_opening_price']) ) {
				update_post_meta( $post_id, 'woo_ua_opening_price', wc_format_decimal(wc_clean($_POST['woo_ua_opening_price'] )));
			}
			
			if( isset($_POST['woo_ua_lowest_price']) ) {				
				update_post_meta( $post_id, 'woo_ua_lowest_price', wc_format_decimal(wc_clean($_POST['woo_ua_lowest_price'] )));
			}
			
			if( isset($_POST['woo_ua_bid_increment']) ) {
				update_post_meta( $post_id, 'woo_ua_bid_increment', wc_format_decimal(wc_clean($_POST['woo_ua_bid_increment'] )));
			}

			$start_date = get_uwa_now_date();
			$end_date  = isset( $_POST['woo_ua_auction_end_date'] ) ? sanitize_text_field( $_POST['woo_ua_auction_end_date'] ) : '';
			if( $end_date ) {
				update_post_meta( $post_id, 'woo_ua_auction_end_date',$end_date);
			}	
			$auction_type = "normal";
			update_post_meta( $post_id, 'woo_ua_auction_type',sanitize_text_field( $auction_type ));


			/* Save starttime only when new auction is added not at edit time */
			if( isset($_POST['woo_ua_auction_form_type']) ) {
			 	if( $_POST['woo_ua_auction_form_type'] == "add_product" ) {
					update_post_meta( $post_id, 'woo_ua_auction_start_date', sanitize_text_field( $start_date ));
				}
			}

			/* converting simple product (or any) to auction product */
			if(isset($_POST['woo_ua_product_type'])) {
				if($_POST['woo_ua_product_type'] != "auction"){

					update_post_meta( $post_id, 'woo_ua_auction_start_date',sanitize_text_field( $start_date ));

					delete_post_meta( $post_id, "_sale_price");					
				}
			}	

			/* for duplicate products, add missing auction meta data */
			if(isset($_POST['woo_ua_product_type']) && 
				isset($_POST['woo_ua_product_id'])) {

				if($_POST['woo_ua_product_type'] == "auction" &&
					$_POST['woo_ua_product_id'] > 0){

					$object_id = absint($_POST['woo_ua_product_id']); 
					$meta_key = "woo_ua_auction_start_date";

					$is_exists = metadata_exists('post', $object_id, $meta_key);
					if($is_exists == false){						
						update_post_meta( $object_id, 'woo_ua_auction_start_date', sanitize_text_field( $start_date ));
					}

				} /* end of if */
			}


			/* when product type is auction, remove other data if added */
			delete_post_meta( $post_id, "_sale_price");
			

		} /* end of if - producttype */	
    }


    /**
	 * Process/save other Product Data.
	 * 
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 *
	 */
	public function uwa_process_product_option_field( $post_id ){
		global $wpdb, $woocommerce, $woocommerce_errors;
		
 		/* converting auction product to simple product (or any) */ 		
		if(isset($_POST['woo_ua_product_type']) && isset($_POST['product-type'])) {
			if(sanitize_text_field($_POST['woo_ua_product_type']) == "auction" && sanitize_text_field($_POST['product-type']) != "auction" ){

				/* when product type is not auction, remove auction related data */		
				delete_post_meta( $post_id, "woo_ua_auction_type");
				delete_post_meta( $post_id, "woo_ua_product_condition");
				delete_post_meta( $post_id, "woo_ua_opening_price");
				delete_post_meta( $post_id, "woo_ua_lowest_price");
				delete_post_meta( $post_id, "woo_ua_bid_increment");			
				delete_post_meta( $post_id, "woo_ua_auction_start_date");
				delete_post_meta( $post_id, "woo_ua_auction_end_date");			

				delete_post_meta( $post_id, "woo_ua_auction_current_bid");
				delete_post_meta( $post_id, "woo_ua_auction_current_bider");
				delete_post_meta( $post_id, "woo_ua_auction_max_bid");
				delete_post_meta( $post_id, "woo_ua_auction_max_current_bider");
				delete_post_meta( $post_id, "woo_ua_auction_bid_count");			
				
				delete_post_meta( $post_id, "woo_ua_auction_started");			
				delete_post_meta( $post_id, "woo_ua_auction_has_started");
				delete_post_meta( $post_id, "woo_ua_auction_closed");
				delete_post_meta( $post_id, "woo_ua_auction_fail_reason");

				delete_post_meta( $post_id, "woo_ua_auction_payed");
				delete_post_meta( $post_id, "woo_ua_order_id");
				delete_post_meta( $post_id, "woo_ua_buy_now");
				delete_post_meta( $post_id, "woo_ua_winner_mail_sent");
				delete_post_meta( $post_id, "woo_ua_auction_watch");
				delete_post_meta( $post_id, "woo_ua_auction_last_activity");
				
				/* delete_user_meta($user_ID, 'woo_ua_auction_watch', $post_id);
				add_user_meta($current_bider, 'woo_ua_auction_win', $product_id); */

				$table_usermeta = $wpdb->prefix."usermeta";
				$metakey = "woo_ua_auction_watch";
				$rows_affected = $wpdb->query($wpdb->prepare("DELETE FROM {$table_usermeta} 
					WHERE meta_key = %s AND meta_value = %d", $metakey, $post_id));
				
				$metakey = "woo_ua_auction_win";
				$rows_affected = $wpdb->query($wpdb->prepare("DELETE FROM {$table_usermeta} 
					WHERE meta_key = %s AND meta_value = %d", $metakey, $post_id));
			}

		} /* end of if - isset */
	}

	/**
	* Add Metabox for Auction Log/History Section
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
	public function uwa_add_auction_metabox( $product ) {

		$woo_pf = new WC_Product_Factory();
		$woo_prd = $woo_pf->get_product($product->ID);
		if( $woo_prd->get_type() !== 'auction' ) return;

		add_meta_box('uwa-auction-log',
					__( 'Bids History', 'ultimate-woocommerce-auction' ),
					 array( $this, 'uwa_render_auction_log' ),
					'product',
					'normal',
					'default'
		);
	}	
	/**
	 *  Callback for adding a meta box to the product editing screen used in uwa_render_auction_log
	 *
	 * @access public
	 *
	 */
	function uwa_render_auction_log() {
		global $woocommerce, $post;
			$product_data = wc_get_product($post->ID); ?>
			
		<?php if (($product_data->is_woo_ua_closed() === TRUE) and ($product_data->is_woo_ua_started() === TRUE)): ?>
				
				<p><?php _e('Auction has expired', 'ultimate-woocommerce-auction')?></p>
				
				<?php if ($product_data->get_woo_ua_auction_fail_reason() == '1') { ?>
				
							<p><?php _e('Auction Expired without any bids.', 'ultimate-woocommerce-auction')?></p>
					
				<?php } elseif ($product_data->get_woo_ua_auction_fail_reason() == '2') { ?>
				
							<p><?php _e('Auction Expired without reserve price met', 'ultimate-woocommerce-auction')?></p>
							
				<?php }
				
				if ($product_data->get_woo_ua_auction_closed() == '3') {?>
				
					<p><?php _e('This Auction Product has been sold for buy now price', 'ultimate-woocommerce-auction')?>: <span>
					<?php echo wp_kses_post(wc_price($product_data->get_regular_price())); ?></span></p>
				
				<?php } elseif ($product_data->get_woo_ua_auction_current_bider()) {?>

					<p><?php _e('Highest bidder was', 'ultimate-woocommerce-auction')?>: <span class="maxbider">
					<a href='<?php echo esc_url(get_edit_user_link($product_data->get_woo_ua_auction_current_bider()));?>'>
					<?php echo esc_attr(get_userdata($product_data->get_woo_ua_auction_current_bider())->display_name); ?></a>
					</span>
					</p>
					
					<p><?php _e('Highest bid was', 'ultimate-woocommerce-auction')?>: <span class="maxbid" >
					<?php echo wp_kses_post(wc_price($product_data->get_woo_ua_current_bid())); ?></span></p>

				<?php if ($product_data->get_woo_ua_auction_payed()) {?>
				
					<p><?php _e('Order has been paid, order ID is', 'ultimate-woocommerce-auction')?>: <span>
					<a href='post.php?&action=edit&post=<?php echo esc_attr($product_data->get_woo_ua_order_id()); ?>'>
					<?php echo esc_attr($product_data->get_woo_ua_order_id()); ?></a></span></p>
					
				<?php } elseif ($product_data->get_woo_ua_order_id()) {
					
					$order = wc_get_order( $product_data->get_woo_ua_order_id() );
					if ( $order ){
						$order_status = $order->get_status() ? $order->get_status() : __('unknown', 'ultimate-woocommerce-auction');?>
						<p><?php _e('Order has been made, order status is', 'ultimate-woocommerce-auction')?>: 
						<a href='post.php?&action=edit&post=<?php echo esc_attr($product_data->get_woo_ua_order_id()); ?>'>
						<?php echo esc_attr($order_status); ?></a><span>
					<?php }
				}?>
				
				<?php }?>


		<?php endif;?>
		
		<?php if (($product_data->is_woo_ua_closed() === FALSE) and ($product_data->is_woo_ua_started() === TRUE)): ?>
		
		<?php endif;?>
		<?php 		
		$heading = apply_filters('ultimate_woocommerce_auction_total_bids_heading', __( 'Total Bids Placed:', 'ultimate-woocommerce-auction' ) );
		?>
		<h2><?php echo esc_attr($heading); ?></h2>

			<div class="woo_ua" id="woo_ua_auction_history" v-cloak>
				<div class="uwa-table-responsive">
						<table class="uwa-admin-table uwa-admin-table-bordered">
						<?php
						$datetimeformat = get_option('date_format').' '.get_option('time_format');
						$woo_ua_auction_history = $product_data->woo_ua_auction_history();

						if ( !empty($woo_ua_auction_history)  ): ?>
						
							<tr>
								<th><?php _e('Bidder Name', 'ultimate-woocommerce-auction')?></th>
								<th><?php _e('Bidding Time', 'ultimate-woocommerce-auction')?></th>
								<th><?php _e('Bid', 'ultimate-woocommerce-auction')?></th>								
								<th class="actions"><?php _e('Actions', 'ultimate-woocommerce-auction')?></th>
							</tr>
							<?php foreach ($woo_ua_auction_history as $history_value) { ?>

							<tr>
								<td class="bid_username"><a href="<?php echo esc_url(get_edit_user_link($history_value->userid));?>">
								<?php echo esc_attr(uwa_user_display_name($history_value->userid));?></a></td>
								<td class="bid_date"><?php echo esc_attr(mysql2date($datetimeformat ,$history_value->date))?></td>
								<td class="bid_price"><?php echo wp_kses_post(wc_price($history_value->bid));?></td>
								<td class="bid_action">
								<a href='#' data-id=<?php echo esc_attr($history_value->id);?> 
								data-postid=<?php echo esc_attr($post->ID);?>  ><?php _e('Delete', 'ultimate-woocommerce-auction')?></a>
								</td>
							</tr>
							<?php } ?>	
						<?php endif;?>
							<tr class="start">
									<?php 
									$start_date = $product_data->get_woo_ua_auction_start_time(); ?>
									<?php if ($product_data->is_woo_ua_started() === TRUE) { ?>
								<td class="started"><?php _e('Auction started', 'ultimate-woocommerce-auction');?>
									<?php }   else { ?>									
								<td  class="started"><?php _e('Auction starting', 'ultimate-woocommerce-auction');?>		
									<?php } ?></td>	
								<td colspan="3"  class="bid_date"><?php echo esc_attr(mysql2date($datetimeformat,$start_date))?></td>
							</tr>
						</table>
				</div>
			</div>		
	<?php }	
	
	/**
	* Add New  Email Setting On WooCommerce Email Setting page
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/
	public function uwa_register_email_classes( $email_classes ) {
           
		   //User Emails
            $email_classes['UWA_Email_Place_Bid'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-auction-place-bid.php');	
			$email_classes['UWA_Email_Auction_Bid_Overbid'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-auction-bid-overbid.php');
			
			$email_classes['UWA_Email_Auction_Winner'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-auction-winner.php');
			
		   //Admin	
			$email_classes['UWA_Email_Auction_Bid_Admin'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-auction-bid-admin.php');
			$email_classes['UWA_Email_Auction_Bid_Overbid_Admin'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-auction-bid-overbid-admin.php');
			
			//Admin	Private Message
			$email_classes['UWA_Email_Private_Msg'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-private-msg.php');
						
			$email_classes['UWA_Email_Auction_Winner_Admin'] = include(WOO_UA_ADMIN . '/email/class-uwa-email-auction-winner-admin.php');
			
             return $email_classes;
    }
	/**
	* Create local Email Template for email setting
	* @package Ultimate WooCommerce Auction
	* @author Nitesh Singh 
	* @since 1.0
	*/		
	 public function uwa_locate_core_template( $core_file, $template, $template_base ) {
            $custom_template = array(
                
				//HTML Email  Bidder(User)
                'emails/placed-bid.php',               
                'emails/bid-outbided.php', 
				'emails/auction-winner.php',
				
				//HTML Email  Admin(Administrator)
				'emails/placed-bid-admin.php',
				'emails/bid-outbided-admin.php',
				'emails/auction-winner-admin.php',
				'emails/auction-private-msg.php',
				
				
                // Plain Email Bidder(User)
                'emails/plain/placed-bid.php',
                'emails/plain/bid-outbided.php',
                'emails/plain/auction-winner.php',
				
				// Plain Email Admin(Administrator)
				'emails/plain/placed-bid-admin.php',             
                'emails/plain/bid-outbided-admin.php',
                'emails/plain/auction-winner-admin.php',
                

            );

            if ( in_array( $template, $custom_template ) ) {
                $core_file = WOO_UA_WC_TEMPLATE . $template;
            }

            return $core_file;
    }	
	/**
	* Auction Filter On Product list Page
	*	 
	*/
	function admin_uwa_filter_restrict_manage_posts() {
				// Drop down list for auction 
		$post_type  = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';		
				if ($post_type == 'product') {
					$filter_values = array(
						'Live Auction' => 'live',
						'Expired Auction' => 'expired',
						'Fail Auction' => 'fail',
						'Sold Auction' => 'sold',
						'Paid Auction' => 'payed',
					);
					?>
			        <select name="uwa_filter">
			        <option value=""><?php _e('Auction filter By ', 'ultimate-woocommerce-auction');?></option>
			        <?php
                        $current_filter = isset( $_GET['uwa_filter'] ) ? sanitize_text_field( $_GET['uwa_filter'] ) : '';	
                        foreach ($filter_values as $label => $value) {
                            printf ( '<option value="%s"%s>%s</option>',$value, $value == $current_filter ? ' selected="selected"' : '', $label );
                        }
                    ?>
			        </select>
			        <?php
                }
	}			
	/**
	* If submitted filter by post meta
	*
	* make sure to change META_KEY to the actual meta key
	* and POST_TYPE to the name of your custom post type
	*
	* @access public
	* @param  (wp_query object) $query
	* @return void
	*/
	function admin_uwa_filter($query) {				
		global $pagenow;	
		$post_type  = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';	
		$uwa_filter  = isset( $_GET['uwa_filter'] ) ? sanitize_text_field( $_GET['uwa_filter'] ) : '';	
		if ($post_type == 'product' && is_admin() && $pagenow == 'edit.php' &&  $uwa_filter != '') {

			$taxquery = $query->get('tax_query');
			if (!is_array($taxquery)) {
				$taxquery = array();
			}

			$taxquery[] =
			array(
				'taxonomy' => 'product_type',
				'field' => 'slug',
				'terms' => 'auction',

			);

			$query->set('tax_query', $taxquery);

			
			switch ($uwa_filter) {
			case 'live':
				$query->query_vars['meta_query'] = array(

					array(
							'key'     => 'woo_ua_auction_closed',
							'compare' => 'NOT EXISTS',
					)

				);

				break;
			case 'expired':
				$query->query_vars['meta_query'] = array(
					array(
						'key' => 'woo_ua_auction_closed',
						'value' => array('1','2','3','4'),
						'compare' => 'IN',
					),
				);

				break;
			case 'fail':
				$query->query_vars['meta_key'] = 'woo_ua_auction_closed';
				$query->query_vars['meta_value'] = '1';

				break;
			case 'sold':
				$query->query_vars['meta_query'] = array(
					array(
						'key' => 'woo_ua_auction_closed',
						'value' => '3',
					),

					array(
						'key'     => 'woo_ua_auction_payed',
						'compare' => 'NOT EXISTS',
					)

				);

				break;
			case 'payed':
				$query->query_vars['meta_key'] = 'woo_ua_auction_payed';
				$query->query_vars['meta_value'] = '1';
				break;
			}
			
			
			
		}
	}
	/**
	* Auction Product  paid for
	*			 
	*
	*/
	function uwa_auction_payed($order_id) {

			$order = wc_get_order($order_id);

			if ($order) {
				$order_items = $order->get_items();

				if ($order_items) {
					foreach ($order_items as $item_id => $item) {

						$item_product_id = wc_get_order_item_meta( $item_id , '_product_id');

						$product_data = wc_get_product($item_product_id);
						if (method_exists( $product_data, 'get_type') && $product_data->get_type() == 'auction') {
								update_post_meta($item_product_id, 'woo_ua_auction_payed', 1, true);
								update_post_meta($item_product_id, 'woo_ua_order_id', $order_id, true);                                       
						}
					}
				}
			}

	}

	function uwa_auction_completed($order_id) {

			$order = wc_get_order($order_id);

			if ($order) {
				$order_items = $order->get_items();

				if ($order_items) {
					foreach ($order_items as $item_id => $item) {

						$item_product_id = wc_get_order_item_meta( $item_id , '_product_id');

						$product_data = wc_get_product($item_product_id);
						if (method_exists( $product_data, 'get_type') && $product_data->get_type() == 'auction') {
								update_post_meta($item_product_id, 'woo_ua_auction_payed', 1, true);
								update_post_meta($item_product_id, 'woo_ua_order_id', $order_id, true);

								if (!$product_data->is_woo_ua_finished()) {
								update_post_meta($item_product_id, 'woo_ua_auction_closed', '3');
								update_post_meta($item_product_id, 'woo_ua_buy_now', '1');
								update_post_meta($item_product_id, 'woo_ua_auction_end_date', get_uwa_now_date());
								}

								wc_update_product_stock($product_data, 0 , 'set' );
						}
					}
				}
			}

	}
	/**
	* Function When Order Cancel by user
	*		 
	*/
	function uwa_auction_order_canceled($order_id) {
			$order = wc_get_order($order_id);

			if ($order) {
				$order_items = $order->get_items();

				if ($order_items) {

					foreach ($order_items as $item_id => $item) {

						$item_product_id = wc_get_order_item_meta( $item_id , '_product_id');

						$product_data = wc_get_product($item_product_id);
						if (method_exists( $product_data, 'get_type') && $product_data->get_type() == 'auction') {
								delete_post_meta($item_product_id, 'woo_ua_auction_payed');
																																					}
					}
				}
			} 

	}

	/**
	 * Auction Product Order
	 *			 
	 */
	function uwa_auction_order($order_id, $posteddata) {

		$order = wc_get_order($order_id);

		if ($order) {

			$order_items = $order->get_items();

			if ($order_items) {

				foreach ($order_items as $item_id => $item) {

					$item_product_id = wc_get_order_item_meta( $item_id , '_product_id');
					
					$product_data = wc_get_product($item_product_id);
					if (method_exists( $product_data, 'get_type') && $product_data->get_type() == 'auction') {
						update_post_meta($order_id, '_auction', '1');
						update_post_meta($item_product_id, '_order_id', $order_id, true);


							/* for buynow product increase stock quantity */
						if($product_data->is_woo_ua_finished()){
					
						}else{

							update_post_meta($item_product_id, 'woo_ua_auction_closed', '3');
							update_post_meta($item_product_id, 'woo_ua_buy_now', '1');
							update_post_meta($item_product_id, 'woo_ua_auction_end_date', get_uwa_now_date());

							/*wc_update_product_stock($product_data, 1, 'increase' );*/
						}
						
					}
				}
			}
		}
	}
	/**
	 * Ajax delete bid
	 *
	 * Function for deleting bid in wp admin
	 *
	 * @access public
	 * @param  array
	 * @return string
	 *
	 */
	function wp_ajax_admin_cancel_bid() {
				global $wpdb;
				$postid  = isset( $_POST['postid'] ) ? absint( $_POST['postid'] ) : '';
				if (!current_user_can('edit_product', $postid)) {
						die();
				}
				if ($postid) {
					    $postid_id = absint($_POST["postid"]);
					    $log_id = absint($_POST["logid"]); 
						$product_data = wc_get_product($postid_id);						
						$log = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE id=%d", $log_id));
				
				if (!is_null($log)) {
					if ($product_data->get_woo_ua_auction_type() == 'normal') {
						
					if (($log->bid == $product_data->get_woo_ua_auction_current_bid()) && ($log->userid == $product_data->get_woo_ua_auction_current_bider())) {

						$newbid = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE auction_id =%d ". $time." ORDER BY  `date` desc , `bid`  desc LIMIT 1, 1 ", $postid_id));
						if (!is_null($newbid)) {
								update_post_meta($postid_id, 'woo_ua_auction_current_bid', $newbid->bid);
								update_post_meta($postid_id, 'woo_ua_auction_current_bider', $newbid->userid);
								delete_post_meta($postid_id, 'woo_ua_auction_max_bid');
								delete_post_meta($postid_id, 'woo_ua_auction_max_current_bider');
								$new_max_bider_id =  $newbid->userid;
						} else {
								delete_post_meta($postid_id, 'woo_ua_auction_current_bid');
								delete_post_meta($postid_id, 'woo_ua_auction_current_bider');
								delete_post_meta($postid_id, 'woo_ua_auction_max_bid');
								delete_post_meta($postid_id, 'woo_ua_auction_max_current_bider');
								$new_max_bider_id = false;
						}
						$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE id= %d", absint($_POST["logid"])));
						update_post_meta($postid_id, 'woo_ua_auction_bid_count', intval($product_data->get_woo_ua_auction_bid_count() - 1));
						do_action('ultimate_woocommerce_auction_delete_bid', array('product_id' => $postid_id, 'delete_user_id' => $log->userid, 'new_max_bider_id ' => $new_max_bider_id ) );
						$response['status'] = 1;
						$response['success_message'] = __('Bid Deleted Successfully','ultimate-woocommerce-auction');
						

					} else {
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE id= %d", absint($_POST["logid"])));
					update_post_meta($postid_id, 'woo_ua_auction_bid_count', intval($product_data->get_woo_ua_auction_bid_count() - 1));
					$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE id= %d", absint($_POST["logid"])));
					$response['status'] = 1;
					$response['success_message'] = __('Bid Deleted Successfully','ultimate-woocommerce-auction');

							}
						
					}
					
					}

				} else {
					
					$response['status'] = 0;
					$response['error_message'] = __('Bid Not Deleted','ultimate-woocommerce-auction');	
				}
				
				echo json_encode( $response );
	        exit;
				
    }		
	
	/**
	* Duplicate post
	*
	* Clear metadata when copy auction
	*
	* @access public
	* @param  array
	* @return string
	*
	*/
	function uwa_woocommerce_duplicate_product($postid) {

		$product = wc_get_product($postid);

			if (!$product) {
				return FALSE;
			}

			if (!(method_exists( $product, 'get_type') && $product->get_type() == 'auction') ) {
				return FALSE;
			}
			delete_post_meta($postid, 'woo_ua_auction_end_date');
			delete_post_meta($postid, 'woo_ua_auction_start_date');
			delete_post_meta($postid, 'woo_ua_auction_current_bid');
			delete_post_meta($postid, 'woo_ua_auction_current_bider');
			delete_post_meta($postid, 'woo_ua_auction_bid_count');			
			delete_post_meta($postid, 'woo_ua_winner_mail_sent');
			delete_post_meta($postid, 'woo_ua_auction_has_started');
			delete_post_meta($postid, 'woo_ua_auction_closed');
			delete_post_meta($postid, 'woo_ua_auction_started');			
			delete_post_meta($postid, 'woo_ua_auction_max_bid');			
			delete_post_meta($postid, 'woo_ua_auction_max_current_bider');
			delete_post_meta($postid, 'woo_ua_auction_fail_reason');
			delete_post_meta($postid, 'woo_ua_auction_payed');
			delete_post_meta($postid, 'woo_ua_order_id');	
			delete_post_meta($postid, '_stock_status');
			update_post_meta($postid, '_stock_status', 'instock');
			update_post_meta($postid, '_stock', '1');

			return TRUE;
	}	
	
	
		
		function uwa_auctions_status_columns( $columns_array ) {
		 
			// I want to display Brand column just after the product name column
			$auction_status_columns = __('Auction Status','ultimate-woocommerce-auction');
			return array_slice( $columns_array, 0, 5, true )
			+ array( 'admin_auction_status' => $auction_status_columns )
			+ array_slice( $columns_array, 5, NULL, true );
		 
		 
		}
 
	
	function uwa_auctions_status_columns_status( $column, $postid ) {
			global $woocommerce, $post;
			if( $column  == 'admin_auction_status' ) {				
				$product_data = wc_get_product($postid);				
				if( $product_data->get_type() == 'auction' ) {
				$closed = $product_data->is_woo_ua_closed();
				$failed = $product_data->get_woo_ua_auction_fail_reason();
				if($closed === TRUE){ ?>				
					<span style="color:red;font-size:20px"><?php _e('Expired', 'ultimate-woocommerce-auction')?></span>
				<?php } else { ?>				
					<span style="color:green;font-size:20px"><?php _e('Live', 'ultimate-woocommerce-auction')?></span>
				<?php }
				
				}
			}
	}
			
			
	/**
	* Show pricing fields for Action product.
	*/
	function uwa_auction_custom_js() {

		if ( 'product' != get_post_type() ) :
			return;
		endif;

		?><script type='text/javascript'>
			jQuery( document ).ready( function() {
				jQuery( '.inventory_tab' ).addClass( 'show_if_auction' ).show();
				
			});

		</script><?php

	}

}
UWA_Admin::get_instance();