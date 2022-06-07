<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * AJAX Event Handler.
 *
 * @class    WSA_AJAX
 * @category Class
 */
class WSA_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'wp_loaded', array( __CLASS__, 'do_wc_ajax' ), 10 );
		
	}
	/**
	 * Set WC AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['wsa-ajax'] ) ) {
			wc_maybe_define_constant( 'DOING_AJAX', true );
			wc_maybe_define_constant( 'WC_DOING_AJAX', true );
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}
	/**
	 * Send headers for WC Ajax Requests.
	 *
	 * @since 2.5.0
	 */
	private static function wc_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}
	/**
	 * Check for WC Ajax request and fire action.
	 */
	public static function do_wc_ajax() {
		global $wp_query;
		if ( ! empty( $_GET['wsa-ajax'] ) ) {
			self::wc_ajax_headers();
			do_action( 'wsa_ajax_' . sanitize_text_field( $_GET['wsa-ajax'] ) );
			wp_die();
		}
	}

}
WSA_AJAX::init();
