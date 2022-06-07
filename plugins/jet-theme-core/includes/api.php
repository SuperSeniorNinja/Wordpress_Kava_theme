<?php
namespace Jet_Theme_Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Api {

	private $config         = array();
	private $enabled       = null;
	private $error_message = '';

	/**
	 * Constructor for the class
	 */
	function __construct() {
		$this->config  = jet_theme_core()->config->get( 'api' );
	}

	/**
	 * Check if remote API is enabled
	 *
	 * @return boolean [description]
	 */
	public function is_enabled() {

		if ( null !== $this->enabled ) {
			return $this->enabled;
		}

		if ( empty( $this->config['enabled'] ) || true !== $this->config['enabled'] ) {
			$this->enabled = false;
			return $this->enabled;
		}

		if ( empty( $this->config['base'] ) || empty( $this->config['path'] ) || empty( $this->config['endpoints'] ) ) {
			$this->enabled = false;
			return $this->enabled;
		}

		$this->enabled = true;

		return $this->enabled;
	}

	/**
	 * Retrieve URL to spescific endpoint.
	 *
	 * @param  [type] $for [description]
	 * @return [type]      [description]
	 */
	public function api_url( $for ) {

		if ( ! $this->is_enabled() ) {
			return false;
		}

		if ( empty( $this->config['endpoints'][ $for ] ) ) {
			return false;
		}

		return $this->config['base'] . $this->config['path'] . $this->config['endpoints'][ $for ];
	}

	/**
	 * Returns API base URL
	 *
	 * @return string
	 */
	public function api_base() {

		if ( ! $this->is_enabled() ) {
			return apply_filters( 'jet-theme-core/api/base-url', false );
		}

		$base_url = $this->config['base'];

		return apply_filters( 'jet-theme-core/api/base-url', $base_url );
	}

	/**
	 * Retrieve request arguments for API request
	 *
	 * @return array
	 */
	public function request_args() {
		return array(
			'timeout'   => 60,
			'sslverify' => false
		);
	}

	/**
	 * Get remote system info by key.
	 *
	 * @param  string|array $key [description]
	 * @return [type]      [description]
	 */
	public function get_info( $key = '' ) {

		$api_url = $this->api_url( 'info' );

		if ( ! $api_url ) {
			return false;
		}

		$response = wp_remote_get( $api_url, $this->request_args() );
		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body, true );

		if ( ! $body || ! isset( $body['success'] ) || true !== $body['success'] ) {
			return false;
		}

		if ( ! $key ) {
			unset( $body['success'] );
			return $body;
		}

		if ( is_string( $key ) ) {
			return isset( $body[ $key ] ) ? $body[ $key ] : false;
		}

		if ( is_array( $key ) ) {

			$result = array();

			foreach ( $key as $_key ) {
				$result[ $_key ] = isset( $body[ $_key ] ) ? $body[ $_key ] : false;
			}

			return $result;

		}

	}

	/**
	 * Retrirve error message by error code
	 *
	 * @return string
	 */
	public function get_error_by_code( $code ) {

		$messages = array(
			'missing' => __( 'Your license is missing. Please check your key again.', 'jet-theme-core' ),
			'no_activations_left' => sprintf( __( '<strong>You have no more activations left.</strong> <a href="%s" target="_blank">Please upgrade to a more advanced license</a> (you\'ll only need to cover the difference).', 'jet-theme-core' ), $this->upgrade_url() ),
			'expired' => sprintf( __( '<strong>Your License Has Expired.</strong> <a href="%s" target="_blank">Renew your license today</a> to keep getting feature updates, premium support and unlimited access to the template library.', 'jet-theme-core' ), $this->renew_url() ),
			'revoked' => __( '<strong>Your license key has been cancelled</strong> (most likely due to a refund request). Please consider acquiring a new license.', 'jet-theme-core' ),
			'disabled' => __( '<strong>Your license key has been cancelled</strong> (most likely due to a refund request). Please consider acquiring a new license.', 'jet-theme-core' ),
			'invalid' => __( '<strong>Your license key doesn\'t match your current domain</strong>. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'jet-theme-core' ),
			'site_inactive' => __( '<strong>Your license key doesn\'t match your current domain</strong>. This is most likely due to a change in the domain URL. Please deactivate the license and then reactivate it again.', 'jet-theme-core' ),
			'inactive' => __( '<strong>Your license key doesn\'t match your current domain</strong>. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'jet-theme-core' ),
		);

		$default = __( 'An error occurred. Please check your internet connection and try again. If the problem persists, contact our support.', 'jet-theme-core' );

		return isset( $messages[ $code ] ) ? $messages[ $code ] : $default;

	}

	/**
	 * Returns upgrade URL
	 *
	 * @return string
	 */
	public function upgrade_url() {
		return $this->config['base'];
	}

	/**
	 * Returns upgrade URL
	 *
	 * @return string
	 */
	public function renew_url() {
		return $this->config['base'];
	}

	/**
	 * Set error message and return false.
	 *
	 * @param [type] $message [description]
	 */
	public function set_error( $message, $code = null ) {

		if ( ! $code ) {
			$this->error_message = $message;
		} else {
			$this->error_message = $this->get_error_by_code( $code );
		}

		return false;
	}

	/**
	 * Returns error message
	 *
	 * @return [type] [description]
	 */
	public function get_activation_error() {
		return $this->error_message;
	}

}
