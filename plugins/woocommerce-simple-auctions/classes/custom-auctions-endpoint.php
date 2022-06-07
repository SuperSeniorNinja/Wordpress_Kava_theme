<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Auctions_My_Account_Endpoint {

	/**
	 * Custom WooCommerce my account endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint = 'auctions-endpoint';

	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Change the My Accout page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );

		// Insering your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
		add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );

		add_action( 'template_redirect', array( $this, 'save_account_details' ) );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {

		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );

	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = esc_html__( 'Auctions settings', 'wc_simple_auctions' );

			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	public function new_menu_items( $items ) {
		// Remove the logout menu item.
		if( isset( $items['customer-logout'] ) ) {
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
		}
		
		// Insert your custom endpoint.
		$items[ self::$endpoint ] = esc_html__( 'Auctions settings', 'wc_simple_auctions' );

		if( isset( $logout ) ){
			// Insert back the logout item.
			$items['customer-logout'] = $logout;	
		}		

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
		wc_get_template('myaccount/auctions-settings.php');
	}

	/**
	 * Save account details.
	 */
	public function save_account_details() {

		if ( empty( $_POST['action'] ) || 'save_auctions_settings' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_auctions_settings' ) ) {
			return;
		}

		nocache_headers();

		$user_id = (int) get_current_user_id();
		if( isset( $_POST['auctions_closing_soon_emails'] ) ){

			update_user_meta($user_id, 'auctions_closing_soon_emails', wc_clean( $_POST['auctions_closing_soon_emails'] ));
		} else {

			update_user_meta($user_id, 'auctions_closing_soon_emails',  '0' );
		}
	}

}

new Auctions_My_Account_Endpoint();
