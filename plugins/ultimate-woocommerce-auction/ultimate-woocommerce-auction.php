<?php

/*
 *  Plugin Name: Ultimate Auction For WooCommerce
 *  Plugin URI: http://auctionplugin.net
 *  Description: Awesome plugin to host auctions with WooCommerce on your wordpress site and sell anything you want.
 *  Author: Nitesh Singh
 *  Version: 2.2.4
 *  Text Domain: ultimate-woocommerce-auction
 *  Domain Path: languages
 *  License: GPLv2
 *  Copyright 2022 Nitesh Singh
 *  WC requires at least: 4.0.0
 *  WC tested up to: 6.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

require_once ABSPATH . 'wp-admin/includes/plugin.php';
global $wpdb;
$blog_plugins = get_option( 'active_plugins', array() );
$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__),'uwa_free_plugin_action_links' );
if( ! function_exists( 'uwa_free_plugin_action_links' ) ) {
	
	function uwa_free_plugin_action_links( $links ) {

        $links[] = '<a href="http://auctionplugin.net/" style="color: #389e38;font-weight: bold;" target="_blank">' . __( 'Get Pro', 'ultimate-woocommerce-auction' ) . '</a>';       
        $links[] = '<a href="' . admin_url( 'admin.php?page=uwa_general_setting' ) . '">' . __( 'Settings', 'ultimate-woocommerce-auction' ) . '</a>';
        $links[] = '<a href="https://docs.auctionplugin.net/" target="_blank">' . __( 'Documentation', 'ultimate-woocommerce-auction' ) . '</a>';

        return $links;
		}
	}

if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) { 

	$pro_plugin = 'ultimate-woocommerce-auction-pro/ultimate-woocommerce-auction-pro.php';
	$free1_plugin = 'ultimate-woocommerce-auction/ultimate-woocommerce-auction.php';
	if ( is_plugin_active( $pro_plugin ) ) {

		add_action( 'admin_notices', 'uwa_custom_message' );
		function uwa_custom_message(){
			?>
		    <div class="notice notice-error is-dismissible">
		        <p><?php _e( 'You are trying to activate Ultimate WooCommerce free version where as you already have its PRO version activated. Please deactivate Ultimate WooCommerce Pro version and then activate Free version.', 'ultimate-woocommerce-auction'); ?></p>
		    </div>

		   	<?php
		}
		deactivate_plugins( $free1_plugin );			
	}
    else {

	if ( ! class_exists( 'Ultimate_WooCommerce_Auction_Free' ) ) { 	 
		/* Required minimums and constants */
		if( !defined( 'WOO_UA_VERSION' ) ) {
			define( 'WOO_UA_VERSION', '2.2.4' ); // plugin version
		}
		if( !defined( 'WOO_UA_DIR' ) ) {
			define( 'WOO_UA_DIR', dirname( __FILE__ ) ); // plugin dir
		}
		if( !defined( 'WOO_UA_Main_File' ) ) {
			define( 'WOO_UA_Main_File',WOO_UA_DIR.'/ultimate-woocommerce-auction.php' ); // plugin dir
		}
		if( !defined( 'WOO_UA_URL' ) ) {
			define( 'WOO_UA_URL', plugin_dir_url( __FILE__ ) ); // plugin url
		}
		if( !defined( 'WOO_UA_ASSETS_URL' ) ) {
			define( 'WOO_UA_ASSETS_URL', WOO_UA_URL . 'assets/' ); // plugin url
		}
		if( !defined( 'WOO_UA_ADMIN' ) ) {
			define( 'WOO_UA_ADMIN', WOO_UA_DIR . '/includes/admin' ); // plugin admin dir
		}
		if( !defined( 'WOO_UA_PLUGIN_BASENAME' ) ) {
			define( 'WOO_UA_PLUGIN_BASENAME', basename( WOO_UA_DIR ) ); // plugin base name	
		}
		if( !defined( 'WOO_UA_TEMPLATE' ) ) {
			define( 'WOO_UA_TEMPLATE', WOO_UA_DIR . '/templates/' ); // plugin admin dir
		}
		if( !defined( 'WOO_UA_WC_TEMPLATE' ) ) {
			define( 'WOO_UA_WC_TEMPLATE', WOO_UA_DIR . '/templates/woocommerce/' ); // plugin admin dir
		}
		if( !defined( 'WOO_UA_POST_TYPE' ) ) {
			define( 'WOO_UA_POST_TYPE', 'product' ); // plugin base name
		}
		if( !defined( 'WOO_UA_PRODUCT_TYPE' ) ) {
			define( 'WOO_UA_PRODUCT_TYPE', 'auction' ); // plugin base name
		}
		
		class Ultimate_WooCommerce_Auction_Free { 
		  
			public function __construct() {
				  add_action( 'woocommerce_init', array( &$this, 'init' ) );
			} 
			/**
			* Init the plugin after plugins_loaded so environment variables are set.
			*/
			public function init() {
				global $woocommerce;
				global $sitepress;
		 
				add_action( 'init', array( $this, 'uwa_free_plugins_textdomain' ) );
				add_action( 'wpmu_new_blog', array( $this, 'uwa_free_plugin_new_blog' ), 10, 6 );				
				
				$pset = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';					
				if( $pset== "uwa_general_setting" || $pset== "uwa_manage_auctions" || $pset=="uwa_why_pro") {
					
				}
				else{
					add_action('admin_notices', array($this, 'uwa_pro_add_plugins_notice'));
				}				
				/* Create Auction Product Type */
				require_once ( WOO_UA_DIR . '/includes/class-uwa-product.php' );
				/* Scripts class to handle scripts functionality */
				require_once( WOO_UA_DIR . '/includes/class-uwa-scripts.php' );
				/* loads the Misc Functions file */
				require_once ( WOO_UA_DIR . '/includes/uwa-misc-functions.php' );
				require_once ( WOO_UA_DIR . '/includes/class-my-auction-setting.php' );
				require_once ( WOO_UA_DIR . '/includes/class-my-auction.php' );
				require_once ( WOO_UA_DIR . '/includes/class-my-auction-watchlist.php' );
				
				/***To override templates within a plugin, two filters are provided by WooCommerce, ' woocommerce_locate_template' and  	'wc_get_template_part'. 
				*Create a subfolder named 'woocommerce' inside the plugin folder, and place there custom templates. 
				*Templates will be loaded in the following hierarchy: 
				*plugin/template_path/template_name 
				*default/template_name				
				*/		
				add_filter('woocommerce_locate_template', array( $this,'uwa_free_woocommerce_locate_template'), 10, 3);
				/* Admin class to handle admin side functionality */
				require_once( WOO_UA_ADMIN . '/class-uwa-admin.php' );
				/* front side template */
				require_once( WOO_UA_DIR . '/includes/class-uwa-front.php' );
				/* Bidding Class File */
				require_once ( WOO_UA_DIR . '/includes/class-uwa-bid.php' );
				/* Ajax handle */
				require_once ( WOO_UA_DIR . '/includes/class-uwa-ajax.php' );
				/* Shortcode class for handels plugin shortcodes  */
				/* Shortcode class for handels plugin shortcodes  */
				require_once ( WOO_UA_DIR . '/includes/class-uwa-shortcodes.php' );
				add_action('init', array( $this,'ultimate_woocommerce_auction_place_bid'));
				//Include Auction Scheduler file for cron job
				require_once ( WOO_UA_DIR . '/includes/action-scheduler/action-scheduler.php' );


				/* For WPML Support - start */
				if ( function_exists( 'icl_object_id' ) && is_object($sitepress) && method_exists( $sitepress,'get_default_language' ) ) {

					add_action( 'ultimate_woocommerce_auction_place_bid', array( $this,'uwa_syncronise_metadata_wpml' ), 1 );
					add_action( 'ultimate_woocommerce_auction_delete_bid', array( $this,'uwa_syncronise_metadata_wpml' ), 1 );
					add_action( 'ultimate_woocommerce_auction_close', array( $this,'uwa_syncronise_metadata_wpml' ), 1 );
					add_action( 'ultimate_woocommerce_auction_started', array( $this,'uwa_syncronise_metadata_wpml' ), 1 );
					add_action( 'woocommerce_process_product_meta', array( $this,'uwa_syncronise_metadata_wpml' ), 85 );
					
				}
				/* For WPML Support - end */

			}				
			/**
			* Load Text Domain.
			*/
			public function uwa_free_plugins_textdomain() {
				/* Set filter for plugin's languages directory */
				$lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
				$lang_dir	= apply_filters( 'ultimate_woocommerce_auction_languages_directory', $lang_dir );
				
				/* Traditional WordPress plugin locale filter */
				$locale	= apply_filters( 'plugin_locale',  get_locale(), 'ultimate-woocommerce-auction' );
				$mofile	= sprintf( '%1$s-%2$s.mo', 'ultimate-woocommerce-auction', $locale );
				
				/* Setup paths to current locale file */
				$mofile_local	= $lang_dir . $mofile;
				$mofile_global	= WP_LANG_DIR . '/' . WOO_UA_PLUGIN_BASENAME . '/' . $mofile;
				
				if ( file_exists( $mofile_global ) ) { 
					/* Look in global /wp-content/languages/ultimate-woocommerce-auction folder */				
					load_textdomain( 'ultimate-woocommerce-auction', $mofile_global );
					
				} elseif ( file_exists( $mofile_local ) ) { 
					/* Look in local plugins/ultimate-woocommerce-auction/languages/ folder  */
					load_textdomain( 'ultimate-woocommerce-auction', $mofile_local );
					
				} else { 
					/* Load the default language files */	
					load_plugin_textdomain( 'ultimate-woocommerce-auction', false, $lang_dir );
				}
			}
			
			public function uwa_pro_add_plugins_notice() {
				
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
			}
			
			public function uwa_free_install($network_wide) {	
				global $wpdb;

				/* Check if the plugin is being network-activated or not. */
				if ( $network_wide ) {
					/* Retrieve all site IDs from this network.*/
					$site_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;" );					
					/* Install the plugin for all these sites. */
					foreach ( $site_ids as $site_id ) {
						switch_to_blog( $site_id );
						$this->uwa_free_create_tables();						
						restore_current_blog();
					}
				} else {
					$this->uwa_free_create_tables();								
				}				
			}
			
		public function uwa_free_plugin_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {

			$plugin_file      = basename( dirname( __FILE__ ) ) . '/ultimate-woocommerce-auction.php';
			if ( is_plugin_active_for_network(  $plugin_file ) ) {
					switch_to_blog($blog_id);
					$this->uwa_free_create_tables();
					restore_current_blog();
				} 

		}
		public static function uwa_free_deactivation() {
			
		}
		/**
		 * Create Database	
		 *	 
		 */ 	
		public function uwa_free_create_tables() {
				
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;
			
			$log_table = $wpdb->prefix . "woo_ua_auction_log";
			$sql = "CREATE TABLE IF NOT EXISTS $log_table (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `userid` bigint(20) unsigned NOT NULL,
			  `auction_id` bigint(20) unsigned DEFAULT NULL,
			  `bid` decimal(32,4) DEFAULT NULL,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `proxy` tinyint(1) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			);";
			
			dbDelta($sql);
			wp_insert_term('auction', 'product_type');
			   if (get_option('woo_ua_show_auction_pages_shop') == FALSE) {
					add_option('woo_ua_show_auction_pages_shop', 'yes');
				}
				if (get_option('woo_ua_show_auction_pages_search') == FALSE) {
					add_option('woo_ua_show_auction_pages_search', 'yes');
				}				
				if (get_option('woo_ua_show_auction_pages_cat') == FALSE) {
					add_option('woo_ua_show_auction_pages_cat', 'yes');
				}
				
				if (get_option('woo_ua_show_auction_pages_tag') == FALSE) {
					add_option('woo_ua_show_auction_pages_tag', 'yes');
				}				
				
				if (get_option('woo_ua_auctions_countdown_format') == FALSE) {
					add_option('woo_ua_auctions_countdown_format', 'yowdHMS');
				}
				if (get_option('woo_ua_auctions_bid_ajax_enable') == FALSE) {
					add_option('woo_ua_auctions_bid_ajax_enable', 'no');
				}
				if (get_option('woo_ua_auctions_bid_ajax_interval') == FALSE) {
					add_option('woo_ua_auctions_bid_ajax_interval', '1');
				}
				
				if (get_option('woo_ua_auctions_bids_reviews_tab') == FALSE) {
					add_option('woo_ua_auctions_bids_reviews_tab', 'yes');
				}
				if (get_option('woo_ua_auctions_private_message') == FALSE) {
					add_option('woo_ua_auctions_private_message', 'yes');
				}
				
				if (get_option('woo_ua_auctions_bids_section_tab') == FALSE) {
					add_option('woo_ua_auctions_bids_section_tab', 'yes');
				}
				
				if (get_option('woo_ua_auctions_watchlists') == FALSE) {
					add_option('woo_ua_auctions_watchlists', 'yes');
				}
				
				/* cron setting	*/			
				if (get_option('woo_ua_cron_auction_status') == FALSE) {
					add_option('woo_ua_cron_auction_status', '2');
				}
				if (get_option('woo_ua_cron_auction_status_number') == FALSE) {
					add_option('woo_ua_cron_auction_status_number', '25');
				}
								
				update_option('woo_ua_auction_db_ver', WOO_UA_VERSION);
				update_option('woo_ua_auction_ver', WOO_UA_VERSION);
				flush_rewrite_rules();					
		}
		/**
		* Templating with plugin folder
		* @param int $post_id the post (product) identifier
		* @param stdClass $post the post (product)	
		*/
		public function uwa_free_woocommerce_locate_template($template, $template_name, $template_path){
			global $woocommerce;
			if (!$template_path) {				
			  $template_path = $woocommerce->template_url;
			}
			$plugin_path = WOO_UA_TEMPLATE.'woocommerce/';
			$template_locate = locate_template( array( $template_path . $template_name, $template_name ) );
			/** Modification: Get the template from this plugin, if it exists */
			if (!$template_locate && file_exists($plugin_path . $template_name)) {
				return $plugin_path . $template_name;
			} else { 				
				return $template;
			}
		}			
		/**
		* Function For Place Bid Button Click.
		*
		* @package Ultimate WooCommerce Auction
		* @author Nitesh Singh 
		* @since 1.0	
		*/		
		public function ultimate_woocommerce_auction_place_bid( $url = false ) {

			if (empty($_REQUEST['uwa-place-bid']) || !is_numeric($_REQUEST['uwa-place-bid'])) {
				return;
			}
			global $woocommerce;
			$product_id = absint($_REQUEST['uwa-place-bid']);
			$bid = abs(round((int)str_replace(',', '.', $_REQUEST['uwa_bid_value']), wc_get_price_decimals()));				
			$was_place_bid = false;
			$placed_bid = array();
			$placing_bid = wc_get_product($product_id);
			$product_type = method_exists( $placing_bid, 'get_type') ? $placing_bid->get_type() : $placing_bid->product_type;		
			$quantity = 1;					
			if ('auction' === $product_type) {

				$product_data = wc_get_product($product_id);					
				$outbiddeduser = $placing_bid->get_woo_ua_auction_current_bider();
				$UWA_Bid = new UWA_Bid; 
				/* Placing Bid */
				if ($UWA_Bid->uwa_bidplace($product_id, $bid)) {
					uwa_bid_place_message($product_id);
					$was_place_bid = true;
					$placed_bid[] = $product_id;						
					$current_user = wp_get_current_user();	
					/**Send Notification to Bidder/Admin */
					if($was_place_bid){
					WC()->mailer();
					   /* bid placed notification to bidder */
						do_action('uwa_bid_place_email', $current_user->ID, $placing_bid);
						/* bid placed notification to admin */
						do_action('uwa_bid_place_email_admin', $placing_bid);
						if(!empty($outbiddeduser)){							
							/* send mail to outbiddeduser user */
							do_action( 'uwa_outbid_bid_email', $outbiddeduser,$placing_bid);							
							/* send mail to Admin */
							do_action( 'uwa_outbid_bid_email_admin',$placing_bid);	
						}						
					}
				}
				if (version_compare($woocommerce->version, '2.1', ">=")) {

					if (wc_notice_count('error') == 0) {
						wp_safe_redirect(esc_url(remove_query_arg(array('uwa-place-bid', 'quantity', 'product_id'), wp_get_referer())));
						exit;
					}
					return;
				} else {
					wp_safe_redirect(esc_url(remove_query_arg(array('uwa-place-bid', 'quantity', 'product_id'), wp_get_referer())));
					exit;
				}

			} else {
				wc_add_notice(__('This product is not Auction', 'ultimate-woocommerce-auction'), 'error');
				return;
			}

		}
		/**
		 * Syncronise auction meta data with WPML
		 *
		 * Sync meta via translated products			 
		 *
		 */
		public function uwa_syncronise_metadata_wpml( $data ){

			global $sitepress;
			$deflanguage = $sitepress->get_default_language();
			if ( is_array( $data ) ) {
				$product_id = $data['product_id'];
			} else {
				$product_id = $data;
			}

			$meta_values = get_post_meta( $product_id );
			$orginalid   = $sitepress->get_original_element_id( $product_id,'post_product' );
			$trid        = $sitepress->get_element_trid( $product_id, 'post_product' );
			$all_posts   = $sitepress->get_element_translations( $trid, 'post_product' );

			unset( $all_posts[ $deflanguage ] );

			if ( ! empty( $all_posts ) ) {
				foreach ( $all_posts as $key => $translatedpost ) {
					if ( isset( $meta_values['woo_ua_product_condition'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_product_condition', $meta_values['woo_ua_product_condition'][0] );
					}

					if ( isset( $meta_values['woo_ua_opening_price'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_opening_price', $meta_values['woo_ua_opening_price'][0] );
					}

					if ( isset( $meta_values['woo_ua_lowest_price'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_lowest_price', $meta_values['woo_ua_lowest_price'][0] );
					}

					if ( isset( $meta_values['woo_ua_bid_increment'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_bid_increment', $meta_values['woo_ua_bid_increment'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_type'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_type', $meta_values['woo_ua_auction_type'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_start_date'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_start_date', $meta_values['woo_ua_auction_start_date'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_end_date'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_end_date', $meta_values['woo_ua_auction_end_date'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_has_started'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_has_started', $meta_values['woo_ua_auction_has_started'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_closed'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_closed', $meta_values['woo_ua_auction_closed'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_fail_reason'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_fail_reason', $meta_values['woo_ua_auction_fail_reason'][0] );
					}

					if ( isset( $meta_values['woo_ua_order_id'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_order_id', $meta_values['woo_ua_order_id'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_payed'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_payed', $meta_values['woo_ua_auction_payed'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_max_bid'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_max_bid', $meta_values['woo_ua_auction_max_bid'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_max_current_bider'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_max_current_bider', $meta_values['woo_ua_auction_max_current_bider'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_current_bid'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_current_bid', $meta_values['woo_ua_auction_current_bid'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_current_bider'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_current_bider', $meta_values['woo_ua_auction_current_bider'][0] );
					}

					if ( isset( $meta_values['woo_ua_auction_bid_count'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_auction_bid_count', $meta_values['woo_ua_auction_bid_count'][0] );
					}
					
					if ( isset( $meta_values['woo_ua_buy_now'][0] ) ) {
						update_post_meta( $translatedpost->element_id, 'woo_ua_buy_now', $meta_values['woo_ua_buy_now'][0] );
					}						
					
					if ( isset( $meta_values['_regular_price'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_regular_price', $meta_values['_regular_price'][0] );
					}						
					
					if ( isset( $meta_values['_auction_wpml_language'][0] ) ) {
						update_post_meta( $translatedpost->element_id, '_lottery_wpml_language', $meta_values['_auction_wpml_language'][0] );
					}
				}
			}
			
		} /* end of function */			
			
		} /* end of class */
		
	} /* end of if - class*/
	
	$uwa_auctions = new Ultimate_WooCommerce_Auction_Free();
	register_activation_hook( __FILE__, array( $uwa_auctions, 'uwa_free_install' ) );
	register_deactivation_hook( __FILE__, array( $uwa_auctions, 'uwa_free_deactivation' ) );
	
	//Include Auction Scheduler file for cron job
	require_once ( WOO_UA_DIR . '/includes/action-scheduler/action-scheduler.php' );

	} /* end of else */
} else {

	add_action( 'admin_notices', 'uwa_install_woocommerce_admin_notice' );
	/**
	 * Print an admin notice if WooCommerce is deactivated
	 *	 
	 */	
	if( ! function_exists( 'uwa_install_woocommerce_admin_notice' ) ) {
		
		function uwa_install_woocommerce_admin_notice() { ?>			

			<div class="updated" id="uwa-free-installer-notice" style="padding: 1em; position: relative;">
            	<h2><?php _e( 'Your Ultimate WooCommerce Auction is almost ready!', 'ultimate-woocommerce-auction' ); ?></h2>

	            <?php
	            $plugin_file      = basename( dirname( __FILE__ ) ) . '/ultimate-woocommerce-auction.php';
	            $core_plugin_file = 'woocommerce/woocommerce.php';
	            ?>
	            <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php _e( 'Dismiss this notice', 'ultimate-woocommerce-auction' ); ?>"></a>

	            <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && 
	            	is_plugin_inactive('woocommerce' ) ): ?>
	                <p><?php echo sprintf( __( 'You just need to activate the <strong>%s</strong> to make it functional.', 'ultimate-woocommerce-auction' ), 'WooCommerce' ); ?></p>
	                <p>
	                    <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=214569a558', 'activate-plugin_' . $core_plugin_file ); ?>" 
						title="<?php _e( 'Activate this plugin', 'ultimate-woocommerce-auction' ); ?>">
						<?php _e( 'Activate', 'ultimate-woocommerce-auction' ); ?></a>
	                </p>
	            <?php else: ?>
				
	                <p><?php echo sprintf( __( "You just need to install the %sCore Plugin%s to make it functional.", "ultimate-woocommerce-auction" ), '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">', '</a>' ); ?></p>

	                <p>	                  
	                   <a class="install-now button" data-slug="woocommerce" href="<?php echo esc_url(admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce')) ;?>" aria-label="Install WooCommerce 4.0.0 now" data-name="WooCommerce 4.0.0">Install Now</a>
	                </p>
	            <?php endif ?>
	        </div>

			<?php			
		}
	}	
	$plugin = plugin_basename( __FILE__ );
	if ( is_plugin_active( $plugin ) ) {
		//deactivate_plugins( $plugin );
	}	
} 