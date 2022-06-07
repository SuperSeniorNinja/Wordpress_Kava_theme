<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Factories\EventFactory;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WebhookHandler
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace
 *
 * @see https://developer.paypal.com/docs/api-basics/notifications/webhooks/
 *
 * @since 3.3.0
 */
class WebhookHandler {

    /**
     * WebhookHandler constructor.
     *
     * @since 3.3.0
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'woocommerce_api_dokan-paypal', [ $this, 'handle_events' ] );
    }

    /**
     * Handle events which are coming from PayPal
     *
     * @since 3.3.0
     *
     * @return void
     * @throws \WeDevs\Dokan\Exceptions\DokanException
     */
    public function handle_events() {
        //if the gateway is disabled then we are not processing further execution
        if ( ! Helper::is_ready() ) {
            status_header( 200 );
            exit();
        }

        $request_body = file_get_contents( 'php://input' );
        $event        = json_decode( $request_body );

        if ( ! $event ) {
            status_header( 400 );
            exit();
        }

        // get request header
        $request_headers = array_change_key_case( $this->get_request_headers(), CASE_UPPER );

        $validate_args = [
            'auth_algo'         => isset( $request_headers['PAYPAL-AUTH-ALGO'] ) ? $request_headers['PAYPAL-AUTH-ALGO'] : '',
            'cert_url'          => isset( $request_headers['PAYPAL-CERT-URL'] ) ? $request_headers['PAYPAL-CERT-URL'] : '',
            'transmission_id'   => isset( $request_headers['PAYPAL-TRANSMISSION-ID'] ) ? $request_headers['PAYPAL-TRANSMISSION-ID'] : '',
            'transmission_sig'  => isset( $request_headers['PAYPAL-TRANSMISSION-SIG'] ) ? $request_headers['PAYPAL-TRANSMISSION-SIG'] : '',
            'transmission_time' => isset( $request_headers['PAYPAL-TRANSMISSION-TIME'] ) ? $request_headers['PAYPAL-TRANSMISSION-TIME'] : '',
            'webhook_id'        => get_option( Helper::get_webhook_key() ),
            'webhook_event'     => $event,
        ];

        // verify webhook event
        $processor = Processor::init();
        if ( ! $processor->verify_webhook_request( $validate_args ) ) {
            dokan_log( 'PayPal Marketplace: Incoming webhook failed validation: ' . print_r( $event, true ) );
            //webhook verification failed
            status_header( 400 );
            exit();
        }

        dokan_log( "[Dokan PayPal Marketplace] Webhook request body:\n" . print_r( $event, true ) );

        EventFactory::handle( $event );

        status_header( 200 );
        exit();
    }

    /**
     * Gets the incoming request headers. Some servers are not using
     * Apache and "getallheaders()" will not work so we may need to
     * build our own headers.
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_request_headers() {
        if ( function_exists( 'getallheaders' ) ) {
            return getallheaders();
        }

        $headers = array();

        foreach ( $_SERVER as $name => $value ) {
            if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
                $headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
            }
        }

        return $headers;
    }

    /**
     * Check if we are using required webhooks
     *
     * @param array $response
     * @param string $webhook_id
     *
     * @since 3.3.7
     *
     * @return bool
     */
    protected function get_webhook_events_from_response( $response, $webhook_id ) {
        if ( ! is_array( $response ) ) {
            return false;
        }

        $event_data = null;
        foreach ( $response as $event ) {
            if ( $event['id'] === $webhook_id ) {
                $event_data = $event;
                break;
            }
        }

        if ( null === $event_data || empty( $event_data['event_types'] ) ) {
            return false;
        }

        $events_available = true;
        $webhook_events  = wp_list_pluck( $event_data['event_types'], 'name' );
        $required_events = array_keys( Helper::get_supported_webhook_events() );

        foreach ( $required_events as $event ) {
            if ( ! in_array( $event, $webhook_events, true ) ) {
                $events_available = false;
                break;
            }
        }


        return $events_available;
    }

    /**
     * Register webhook and remove old webhook endpoints from PayPal
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function register_webhook() {
        if ( ! Helper::is_api_ready() ) {
            return false;
        }

        $processor = Processor::init();
        $response  = $processor->get_webhooks();
        $site_url  = str_replace( [ 'http://', 'https://' ], '', home_url( '/' ) );

        if ( is_wp_error( $response ) ) {
            dokan_log( 'Dokan PayPal Marketplace listing webhook error: ' . Helper::get_error_message( $response ) );
            return false;
        }

        $hooks = wp_list_pluck( $response, 'url', 'id' );

        // check webhook already exists and webhook url and site url are same
        $existing_web_hook_id = get_option( Helper::get_webhook_key(), '' );
        if ( ! empty( $existing_web_hook_id ) &&
            array_key_exists( $existing_web_hook_id, $hooks ) &&
            false !== strpos( $hooks[ $existing_web_hook_id ], $site_url . 'wc-api/dokan-paypal' )
        ) {
            // check we've all the required webhook events
            if ( $this->get_webhook_events_from_response( $response, $existing_web_hook_id ) ) {
                return true;
            }
        }

        foreach ( $hooks as $hook_id => $hook_url ) {
            // remove all dokan webhooks for current site
            if ( false !== strpos( $hook_url, $site_url . 'wc-api/dokan-paypal' ) ) {
                $processor->delete_webhook( $hook_id );
            }
        }

        // create required webhook
        $events     = Helper::get_webhook_events_for_notification();
        $response   = $processor->create_webhook( home_url( 'wc-api/dokan-paypal', 'https' ), $events );
        if ( is_wp_error( $response ) ) {
            delete_option( Helper::get_webhook_key() );
            dokan_log( 'Could not create webhook automatically: ' . print_r( $response, true ) );
            return false;
        }

        //store this webhook to database
        update_option( Helper::get_webhook_key(), $response['id'] );

        return true;
    }

    /**
     * Delete webhook on PayPal end
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function deregister_webhook() {
        if ( ! Helper::is_api_ready() ) {
            return false;
        }

        $processor = Processor::init();
        $response  = $processor->get_webhooks();
        if ( is_wp_error( $response ) ) {
            dokan_log( 'Dokan PayPal Marketplace listing webhook error: ' . print_r( $response, true ) );
            return false;
        }

        $hooks      = wp_list_pluck( $response, 'url', 'id' );
        $site_url   = str_replace( [ 'http://', 'https://' ], '', home_url( '/' ) );
        foreach ( $hooks as $hook_id => $hook_url ) {
            // remove all dokan webhooks for current site
            if ( false !== strpos( $hook_url, $site_url . 'wc-api/dokan-paypal' ) ) {
                $processor->delete_webhook( $hook_id );
            }
        }

        // delete database reference
        delete_option( Helper::get_webhook_key() );

        return true;
    }
}
