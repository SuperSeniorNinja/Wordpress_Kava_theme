<?php
/**
 * WooCommerce Account Settings
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (  class_exists( 'WC_Settings_Page' ) ) :

/**
 * WC_Settings_Accounts
 */
class WC_Settings_Simple_Auctions extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'simple_auctions';
		$this->label = esc_html__( 'Auctions', 'wc_simple_auctions' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		return apply_filters( 'woocommerce_' . $this->id . '_settings', array(

			array(	'title' => esc_html__( 'Simple auction options', 'wc_simple_auctions' ), 'type' => 'title','desc' => '', 'id' => 'simple_auction_options' ),
										array(
											'title'    => esc_html__( 'Default Auction Sorting', 'wc_simple_auctions' ),
											'desc'     => esc_html__( 'This controls the default sort order of the auctions.', 'wc_simple_auctions' ),
											'id'       => 'wsa_default_auction_orderby',
											'class'    => 'wc-enhanced-select',
											'css'      => 'min-width:300px;',
											'default'  => 'menu_order',
											'type'     => 'select',
											'options'  => apply_filters( 'wsa_default_auction_orderby_options', array(
												'menu_order' => esc_html__( 'Default sorting (custom ordering + name)', 'woocommerce' ),
												'date'       => esc_html__( 'Sort by most recent', 'woocommerce' ),
												'bid_asc' => esc_html__('Sort by current bid: Low to high', 'wc_simple_auctions' ),
												'bid_desc' => esc_html__('Sort by current bid: High to low', 'wc_simple_auctions' ),
												'auction_end' => esc_html__('Sort auction by ending soonest', 'wc_simple_auctions' ),
												'auction_started' => esc_html__('Sort auction by recently started', 'wc_simple_auctions' ),
												'auction_activity' => esc_html__('Sort auction by most active', 'wc_simple_auctions' ),

											) ),
											'desc_tip' =>  true,
										),
                                        array(
											'title' 			=> esc_html__( 'Past auctions', 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Show finished auctions.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_finished_enabled',
											'default' 			=> 'no'
										),
										array(
											'title' 			=> esc_html__( 'Future auctions', 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Show auctions that did not start yet.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_future_enabled',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> esc_html__( "Do not show auctions on shop page", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Do not mix auctions and regular products on shop page. Just show auctions on the auction page (auctions base page)', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_shop',
											'default' 			=> 'no'
										),
                                        array(
											'title' 			=> esc_html__( "Do not show auctions on product search page", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Do not mix auctions and regular products on product search page.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_search',
											'default' 			=> 'no'
										),
										array(
											'title' 			=> esc_html__( "Do not show auctions on product category page", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Do not mix auctions and regular products on product category page. Just show auctions on the auction page (auctions base page)', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_cat',
											'default' 			=> 'no'
										),
										array(
											'title' 			=> esc_html__( "Do not show auctions on product tag page", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Do not mix auctions and regular products on product tag page. Just show auctions on the auction page (auctions base page)', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_mix_tag',
											'default' 			=> 'no'
										),
										array(
											'title' 			=> esc_html__( "Do not show auctions in products shortcodes", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Do not mix auctions and regular products in product shortcodes', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_dont_shortcodes',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> esc_html__( "Countdown format", 'wc_simple_auctions' ),
											'desc'				=> esc_html__( "The format for the countdown display. Default is yowdHMS", 'wc_simple_auctions' ),
											'desc_tip' 			=> esc_html__( 'Use the following characters (in order) to indicate which periods you want to display: Y for years, O for months, W for weeks, D for days, H for hours, M for minutes, S for seconds.Use upper-case characters for mandatory periods, or the corresponding lower-case characters for optional periods, i.e. only display if non-zero. Once one optional period is shown, all the ones after that are also shown.',
												'wc_simple_auctions'
											),
											'type' 				=> 'text',
											'id'				=> 'simple_auctions_countdown_format',
											'default' 			=> 'yowdHMS'
										),
										array(
											'title' 			=> esc_html__( "Use compact countdown ", 'wc_simple_auctions' ),
											'desc' 			=> esc_html__( 'Indicate whether or not the countdown should be displayed in a compact format.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_compact_countdown',
											'default' 			=> 'no'
										),
										array(
											'title' => esc_html__( 'Auctions Base Page', 'wc_simple_auctions' ),
											'desc' 		=> esc_html__( 'Set the base page for your auctions - this is where your auction archive will be.', 'wc_simple_auctions' ),
											'id' 		=> 'woocommerce_auction_page_id',
											'type' 		=> 'single_select_page',
											'default'	=> '',
											'class'		=> 'chosen_select_nostd',
											'css' 		=> 'min-width:300px;',
											'desc_tip'	=>  true
											),
										array(
											'title' 		=> esc_html__( "Use ajax bid check", 'wc_simple_auctions' ),
											'desc' 			=> esc_html__( 'Enables / disables ajax current bid checker (refresher) for auction - updates current bid value without refreshing page (increases server load, disable for best performance)', 'wc_simple_auctions' ),
											'type' 			=> 'checkbox',
											'id'			=> 'simple_auctions_live_check',
											'default' 		=> 'yes'
										),
										array(
											'title' 		=> esc_html__( "Ajax bid check interval", 'wc_simple_auctions' ),
											'desc' 			=> esc_html__( 'Time between two ajax requests in seconds (bigger intervals means less load for server)', 'wc_simple_auctions' ),
											'type' 			=> 'text',
											'id'			=> 'simple_auctions_live_check_interval',
											'default' 		=> '1'
										),
										array(
											'title' 		=> esc_html__( "Ajax bid check only when in focus", 'wc_simple_auctions' ),
											'desc' 			=> esc_html__( 'Ajax bid check only when page / browser is in focus, if this is off it will trigger ajax request if page is open and user is not looking at it (increases server load).', 'wc_simple_auctions' ),
											'type' 			=> 'checkbox',
											'id'			=> 'simple_auctions_focus',
											'default' 		=> 'yes'
										),
										array(
											'title' 			=> esc_html__( "Allow highest bidder to outbid himself", 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_curent_bidder_can_bid',
											'default' 			=> 'no'
										),

										array(
											'title' 			=> esc_html__( "Allow on proxy auction change to smaller max bid value", 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_smaller_max_bid',
											'default' 			=> 'no'
										),

										array(
											'title' 			=> esc_html__( "Allow watchlists", 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_watchlists',
											'default' 			=> 'yes'
										),
										array(
											'title' 			=> esc_html__( "Max bid amount", 'wc_simple_auctions' ),
											'desc'				=> esc_html__( "Maximum value for single bid. Default value is ", 'wc_simple_auctions' ).wc_price('99999999999.99'),
											'type' 				=> 'number',
											'id'				=> 'simple_auctions_max_bid_amount',
											'default' 			=> ''
										),
										array(
											'title' 			=> esc_html__( "Allow Buy It Now after bidding has started", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'For auction listings with the Buy It Now option, you have the chance to purchase an item immediately, before bidding starts. After someone bids, the Buy It Now option disappears and bidding continues until the listing ends, with the item going to the highest bidder. If is not checked Buy It Now disappears when bid exceeds the Buy Now price for normal auction, or is lower than reverse auction.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_alow_buy_now',
											'default' 			=> 'yes'

										),
										array(
											'title' 			=> esc_html__( "Set proxy auctions on by default", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Check box for proxy auction is on by default. You have to uncheckit for normal auctions', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_proxy_auction_on',
											'default' 			=> 'no'

										),
										array(
											'title' 			=> esc_html__( "Enable sealed auctions", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Click here to enable sealed auctions.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_sealed_on',
											'default' 			=> 'no'
										),

										array(
											'title' 			=> esc_html__( "Remove pay button from reverse auctions.", 'wc_simple_auctions' ),
											'desc' 				=> esc_html__( 'Click here to enable removing pay functionality for reverse auctions.', 'wc_simple_auctions' ),
											'type' 				=> 'checkbox',
											'id'				=> 'simple_auctions_remove_pay_reverse',
											'default' 			=> 'no'
										),

										array( 'type' => 'sectionend', 'id' => 'simple_auction_options'),

		)); // End pages settings
	}
}
return new WC_Settings_Simple_Auctions();

endif;
