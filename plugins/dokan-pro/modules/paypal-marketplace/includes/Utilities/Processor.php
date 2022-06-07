<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Processor
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities
 *
 * @since 3.3.0
 */
class Processor {

    /**
     * Instance of self
     *
     * @var Processor
     */
    protected static $instance = null;

    /**
     * @var bool
     */
    protected $test_mode = false;

    /**
     * @var string
     */
    protected $api_base_url = 'https://api.paypal.com/';

    /**
     * @var array
     */
    protected $additional_request_header = [];

    /**
     * Processor constructor.
     *
     * @since 3.3.0
     */
    protected function __construct() {
        if ( Helper::is_test_mode() ) {
            $this->test_mode    = true;
            $this->api_base_url = 'https://api.sandbox.paypal.com/';
        }
    }

    /**
     * Initialize Processor() class
     *
     * @since 3.3.0
     *
     * @return Processor
     */
    public static function init() {
        if ( static::$instance === null ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Create partner referral
     *
     * @see https://developer.paypal.com/docs/api/partner-referrals/v2/#partner-referrals_create
     *
     * @param $vendor_email_address
     * @param $tracking_id
     * @param array $products
     *
     * @since 3.3.0
     *
     * @return string|WP_Error
     */
    public function create_partner_referral( $vendor_email_address, $tracking_id, $products = [ 'PPCP' ] ) {
        $partner_referral_data = [
            'email'                   => $vendor_email_address,
            'preferred_language_code' => 'en-US',
            'tracking_id'             => $tracking_id,
            'partner_config_override' => [
                'partner_logo_url'       => Helper::get_marketplace_logo(),
                'return_url'             => add_query_arg(
                    [
                        'action'   => 'dokan-paypal-marketplace-connect-success',
                        'status'   => 'success',
                        '_wpnonce' => wp_create_nonce( 'dokan-paypal-marketplace-connect-success' ),
                    ],
                    dokan_get_navigation_url( 'settings/payment' )
                ),
                'return_url_description' => 'the url to return the merchant after the paypal onboarding process.',
                'action_renewal_url'     => site_url(),
            ],
            'legal_consents'          => [
                [
                    'type'    => 'SHARE_DATA_CONSENT',
                    'granted' => true,
                ],
            ],
            'operations'              => [
                [
                    'operation'                  => 'API_INTEGRATION',
                    'api_integration_preference' => [
                        'rest_api_integration' => [
                            'integration_method'  => 'PAYPAL',
                            'integration_type'    => 'THIRD_PARTY',
                            'third_party_details' => [
                                'features' => [
                                    'PAYMENT',
                                    'REFUND',
                                    'DELAY_FUNDS_DISBURSEMENT',
                                    'PARTNER_FEE',
                                    'READ_SELLER_DISPUTE',
                                    'UPDATE_SELLER_DISPUTE',
                                    'ACCESS_MERCHANT_INFORMATION',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'products'                => $products,
        ];

        $url      = $this->make_paypal_url( 'v2/customer/partner-referrals/' );
        $response = $this->make_request(
            [
				'url' => $url,
				'data' => wp_json_encode( $partner_referral_data ),
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['links'][1] ) && 'action_url' === $response['links'][1]['rel'] ) {
            return $response;
        }

        return new WP_Error( 'dokan_paypal_create_partner_referral_error', $response );
    }

    /**
     * Get merchant ID from tracking id
     *
     * @param $tracking_id
     * @since 3.3.0
     * @return string|WP_Error
     */
    public function get_merchant_id( $tracking_id ) {
        $partner_id = Helper::get_partner_id();
        $url        = $this->make_paypal_url( "v1/customer/partners/{$partner_id}/merchant-integrations/?tracking_id={$tracking_id}" );

        $response = $this->make_request(
            [
				'url' => $url,
				'method' => 'get',
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['merchant_id'] ) ) {
            return $response;
        }

        return new WP_Error( 'dokan_paypal_get_merchant_id_error', $response );
    }

    /**
     * Get merchant status
     *
     * @param string $merchant_id
     * @since 3.3.0
     * @return array|WP_Error
     */
    public function get_merchant_status( $merchant_id ) {
        $partner_id = Helper::get_partner_id();
        $url        = $this->make_paypal_url( "v1/customer/partners/{$partner_id}/merchant-integrations/{$merchant_id}" );

        $response = $this->make_request(
            [
				'url' => $url,
				'method' => 'get',
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return $response;
    }

    /**
     * Get order details by order id
     *
     * @param $order_id
     * @since 3.3.0
     * @return array|WP_Error
     */
    public function get_order( $order_id ) {
        $url = $this->make_paypal_url( "v2/checkout/orders/{$order_id}" );

        $response = $this->make_request(
            [
                'url' => $url,
                'method' => 'get',
            ]
        );

        return $response;
    }

    /**
     * Create order with details in PayPal
     *
     * @param $order_data
     * @since 3.3.0
     * @return string|WP_Error
     */
    public function create_order( $order_data ) {
        $url                             = $this->make_paypal_url( 'v2/checkout/orders' );
        $this->additional_request_header = [
            'Prefer'                        => 'return=representation',
            'PayPal-Partner-Attribution-Id' => Helper::get_bn_code(),
        ];

        $response = $this->make_request(
            [
				'url' => $url,
				'data' => wp_json_encode( $order_data ),
			]
        );
        // we need to empty this, otherwise this will be used on subsequent requests
        $this->additional_request_header = [];

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if (
            isset( $response['status'] ) &&
            'CREATED' === $response['status'] &&
            isset( $response['links'][1] ) &&
            'approve' === $response['links'][1]['rel']
        ) {
            return $response;
        }

        return new WP_Error( 'dokan_paypal_create_order_error', $response );
    }

    /**
     * Capture payment
     *
     * @see https://developer.paypal.com/docs/api/payments/v2/#captures
     *
     * @param string $order_id PayPal Order ID
     *
     * @since 3.3.0
     *
     * @return array|bool|WP_Error
     */
    public function capture_payment( $order_id ) {
        $url                             = $this->make_paypal_url( "v2/checkout/orders/{$order_id}/capture" );
        $this->additional_request_header = [
            'Prefer'                        => 'return=representation',
            'PayPal-Partner-Attribution-Id' => Helper::get_bn_code(),
            /**
             * @see https://developer.paypal.com/docs/platforms/develop/idempotency/
             */
            'PayPal-Request-Id'             => $order_id,
        ];

        $response = $this->make_request( [ 'url' => $url ] );
        // we need to empty this, otherwise this will be used on subsequent requests
        $this->additional_request_header = [];

        // early return incase of error
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // check charged captured successfully
        if (
            isset( $response['intent'], $response['status'] ) &&
            'CAPTURE' === $response['intent'] &&
            'COMPLETED' === $response['status']
        ) {
            return $response;
        }

        // return error otherwise
        return new WP_Error(
            'dokan_paypal_capture_order_error',
            // translators: 1) Payment Gateway Title, 2) Capture Payment Status
            sprintf( __( '[%1$s] Capture Payment Failed. Capture Status: %2$s', 'dokan' ), Helper::get_gateway_title(), $response['status'] )
        );
    }

    /**
     * Disburse payment to a vendor
     *
     * @see https://developer.paypal.com/docs/api/referenced-payouts/v1/#referenced-payouts-items
     *
     * @param array $data
     *
     * @since 3.3.0
     *
     * @return array|WP_Error
     */
    public function create_referenced_payout( $data ) {
        $url = $this->make_paypal_url( 'v1/payments/referenced-payouts-items' );

        $this->additional_request_header = [
            'PayPal-Partner-Attribution-Id' => Helper::get_bn_code(),
            /**
             * @see https://developer.paypal.com/docs/platforms/develop/idempotency/
             */
            'PayPal-Request-Id'             => $data['reference_id'],
        ];

        $response = $this->make_request(
            [
                'url'    => $url,
                'data'   => wp_json_encode( $data ),
            ]
        );

        // we need to empty this, otherwise this will be used on subsequent requests
        $this->additional_request_header = [];

        return $response;
    }

    /**
     * Get all registered webhooks
     *
     * @see https://developer.paypal.com/docs/api/webhooks/v1/#webhooks_list
     *
     * @since 3.3.0
     *
     * @return array|WP_Error
     */
    public function get_webhooks() {
        $url      = $this->make_paypal_url( 'v1/notifications/webhooks' );
        $response = $this->make_request(
            [
				'url' => $url,
				'method' => 'get',
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['webhooks'] ) ) {
            return $response['webhooks'];
        }

        return new WP_Error( 'dokan_paypal_list_webhooks_error', $response );
    }

    /**
     * Create webhook on PayPal
     *
     * @param $webhook_url
     * @param $event_types
     *
     * @since 3.3.0
     * @return array|WP_Error
     */
    public function create_webhook( $webhook_url, $event_types ) {
        $url          = $this->make_paypal_url( 'v1/notifications/webhooks' );
        $webhook_data = [
            'url'         => $webhook_url,
            'event_types' => $event_types,
        ];

        $response = $this->make_request(
            [
				'url' => $url,
				'data' => wp_json_encode( $webhook_data ),
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['id'] ) ) {
            return $response;
        }

        return new WP_Error( 'dokan_paypal_create_webhook_error', $response );
    }

    /**
     * @return bool|WP_Error
     * @since 3.3.0
     */
    public function delete_webhook( $id ) {
        if ( empty( $id ) ) {
            return new WP_Error( 'dokan_paypal_invalid_webhook_id', __( 'Invalid webhook id provided, Please check your input.', 'dokan' ) );
        }
        $url      = $this->make_paypal_url( 'v1/notifications/webhooks/' . $id );
        $response = $this->make_request(
            [
				'url' => $url,
				'method' => 'delete',
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    public function verify_webhook_request( $data ) {
        $url      = $this->make_paypal_url( 'v1/notifications/verify-webhook-signature' );
        $response = $this->make_request(
            [
                'url' => $url,
                'data' => wp_json_encode( $data ),
            ]
        );

        if ( is_wp_error( $response ) ) {
            dokan_log( 'PayPal Marketplace Verify Webhook Error: ' . print_r( $response, true ) );
            return false;
        }

        if ( isset( $response['verification_status'] ) && $response['verification_status'] === 'SUCCESS' ) {
            return true;
        }

        return false;
    }

    /**
     * Get access token
     *
     * @return string|WP_Error
     * @since 3.3.0
     */
    public function get_access_token() {
        if ( get_transient( '_dokan_paypal_marketplace_access_token' ) ) {
            return get_transient( '_dokan_paypal_marketplace_access_token' );
        }

        $access_token = $this->create_access_token();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        return $access_token;
    }

    /**
     * Create access token
     *
     * @return string|WP_Error
     * @since 3.3.0
     */
    public function create_access_token() {
        $url      = $this->make_paypal_url( 'v1/oauth2/token/' );
        $response = $this->make_request(
            [
				'url' => $url,
				'data' => [ 'grant_type' => 'client_credentials' ],
				'method' => 'post',
				'header' => true,
				'content_type_json' => false,
				'request_with_token' => false,
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['access_token'] ) && isset( $response['expires_in'] ) ) {
            set_transient( '_dokan_paypal_marketplace_access_token', $response['access_token'], $response['expires_in'] );

            return $response['access_token'];
        }
    }

    /**
     * Make paypal full url
     *
     * @param $path
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function make_paypal_url( $path ) {
        return $this->api_base_url . $path;
    }

    /**
     * Send get request
     *
     * @param $url
     *
     * @return array|mixed|WP_Error
     */
    public function get_request( $url ) {
        $header = $this->get_header();

        if ( is_wp_error( $header ) ) {
            return $header;
        }

        $args = [
            'timeout'     => '30',
            'redirection' => '30',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $header,
            'cookies'     => [],
        ];

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );

        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return new WP_Error( 'dokan_paypal_request_error', $body );
        }

        return json_decode( $body, true );
    }

    /**
     * Make request
     *
     * @param array $data
     *
     * @since 3.3.0
     * @return array|WP_Error
     */
    //public function make_request( $url, $data = [], $method = 'post', $header = true, $content_type_json = true, $request_with_token = true ) {
    public function make_request( $data = [] ) {
        $defaults = [
            'url'                => '',
            'data'               => [],
            'method'             => 'post',
            'header'             => true,
            'content_type_json'  => true,
            'request_with_token' => true,
        ];

        $parsed_args = wp_parse_args( $data, $defaults );

        $header = $parsed_args['header'] === true ? $this->get_header( $parsed_args['content_type_json'], $parsed_args['request_with_token'] ) : [];

        if ( is_wp_error( $header ) ) {
            return $header;
        }

        $args = [
            'timeout'     => '120',
            'redirection' => '120',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $header,
            'cookies'     => [],
        ];

        if ( ! empty( $parsed_args['data'] ) ) {
            $args['body'] = $parsed_args['data'];
        }

        switch ( strtolower( $parsed_args['method'] ) ) {
            case 'get':
                $args['method'] = 'GET';
                break;
            case 'post':
                $args['method'] = 'POST';
                break;
            case 'delete':
                $args['method'] = 'DELETE';
                break;
            case 'patch':
                $args['method'] = 'PATCH';
                break;
            default:
                $args['method'] = 'POST';
        }

        $response = wp_remote_request( esc_url_raw( $parsed_args['url'] ), $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body            = json_decode( wp_remote_retrieve_body( $response ), true );
        $paypal_debug_id = wp_remote_retrieve_header( $response, 'paypal-debug-id' );

        if (
            200 !== wp_remote_retrieve_response_code( $response ) &&
            201 !== wp_remote_retrieve_response_code( $response ) &&
            202 !== wp_remote_retrieve_response_code( $response ) &&
            204 !== wp_remote_retrieve_response_code( $response )
        ) {
            return new WP_Error( 'dokan_paypal_request_error', $body, [ 'paypal_debug_id' => $paypal_debug_id ] );
        }

        if ( $paypal_debug_id ) {
            $body['paypal_debug_id'] = $paypal_debug_id;
        }

        return $body;
    }

    /**
     * Headers data for curl request
     *
     * @param bool $content_type_json
     * @param bool $request_with_token
     *
     * @since 3.3.0
     * @return array|WP_Error
     */
    public function get_header( $content_type_json = true, $request_with_token = true ) {
        $content_type = $content_type_json ? 'json' : 'x-www-form-urlencoded';

        $headers = [
            'Content-Type' => 'application/' . $content_type,
        ];

        if ( ! $request_with_token ) {
            $headers['Authorization'] = 'Basic ' . $this->get_authorization_data();
            $headers['Ignorecache']   = true;

            return $headers;
        }

        $access_token = $this->get_access_token();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $headers['Authorization'] = 'Bearer ' . $access_token;

        //merge array if there is any additional data
        $headers = array_merge( $headers, $this->additional_request_header );

        return $headers;
    }

    /**
     * Get base64 encoded authorization data
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_authorization_data() {
        $client_id     = Helper::get_client_id();
        $client_secret = Helper::get_client_secret();

        return base64_encode( $client_id . ':' . $client_secret ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    /**
     * Get generated client token
     *
     * @since 3.3.0
     * @return string|WP_Error
     */
    public function get_generated_client_token() {
        if ( get_transient( '_dokan_paypal_marketplace_client_token' ) ) {
            return get_transient( '_dokan_paypal_marketplace_client_token' );
        }

        return $this->generate_client_token();
    }

    /**
     * Generate a client token for your buyer
     *
     * @see https://developer.paypal.com/docs/business/checkout/advanced-card-payments/#step-2-generate-a-client-token-for-your-buyer
     *
     * @since 3.3.0
     *
     * @return string|WP_Error
     */
    public function generate_client_token() {
        $url = $this->make_paypal_url( 'v1/identity/generate-token' );

        $response = $this->make_request( [ 'url' => $url ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['client_token'] ) ) {
            set_transient( '_dokan_paypal_marketplace_client_token', $response['client_token'], $response['expires_in'] );

            return $response['client_token'];
        }

        return new WP_Error( 'dokan_paypal_generate_client_token_error', $response );
    }

    /**
     * Make decision based on some condition for continue transaction
     *
     * @see https://developer.paypal.com/docs/business/checkout/add-capabilities/3d-secure/#3d-secure-response-parameters
     *
     * @param $order_data
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function continue_transaction( $order_data ) {
        //if no source considered it as a paypal payment not using any card
        if ( empty( $order_data['payment_source']['card'] ) ) {
            return true;
        }
        $payment_source = $order_data['payment_source']['card'];

        $authentication_result = $payment_source['authentication_result'];
        $liability_shift       = isset( $authentication_result['liability_shift'] ) ? $authentication_result['liability_shift'] : 'unknown';

        $enrollment_status = isset( $authentication_result['three_d_secure']['enrollment_status'] ) ?
            $authentication_result['three_d_secure']['enrollment_status'] : 'unknown';

        $authentication_status = isset( $authentication_result['three_d_secure']['authentication_status'] ) ?
            $authentication_result['three_d_secure']['authentication_status'] : 'unknown';

        /**
         * EnrollmentStatus, AuthenticationStatus, LiabilityShift
         * where only two parameter, placed 'unknown' for that blank field
         */
        $allowed_transaction_conditions = [
            [ 'Y', 'Y', 'POSSIBLE' ],
            [ 'Y', 'A', 'POSSIBLE' ],
            [ 'N', 'unknown', 'NO' ],
            [ 'U', 'unknown', 'NO' ],
            [ 'B', 'unknown', 'NO' ],
        ];

        foreach ( $allowed_transaction_conditions as $condition ) {
            if (
                $enrollment_status === $condition[0] &&
                $authentication_status === $condition[1] &&
                $liability_shift === $condition[2]
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Refund a capture
     *
     * @see https://developer.paypal.com/docs/api/payments/v2/#captures_refund
     *
     * @param $capture_id
     * @param $merchant_id
     * @param array $data
     *
     * @since 3.3.0
     *
     * @return array|\WP_Error
     */
    public function refund( $capture_id, $merchant_id, $data = [] ) {
        $url                   = $this->make_paypal_url( "v2/payments/captures/{$capture_id}/refund" );
        $auth_assertion_header = $this->get_auth_assertion_header( $merchant_id );

        $this->additional_request_header = [
            'Prefer'                        => 'return=representation',
            'Paypal-Auth-Assertion'         => $auth_assertion_header,
            'PayPal-Partner-Attribution-Id' => Helper::get_bn_code(),
        ];

        $response = $this->make_request(
            [
				'url' => $url,
				'data' => wp_json_encode( $data ),
			]
        );

        // reset additional headers
        $this->additional_request_header = [];

        return $response;
    }

    /**
     * Get PayPal auth assertion header, this is needed for refund
     *
     * @see https://developer.paypal.com/docs/platforms/manage-risk/issue-refund/#1-generate-paypal-auth-assertion-header
     *
     * @param string $merchant_id
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_auth_assertion_header( $merchant_id ) {
        $client_id             = Helper::get_client_id();
        $auth_1                = base64_encode( wp_json_encode( [ 'alg' => 'none' ] ) ); //phpcs:ignore
        $auth_2                = base64_encode( //phpcs:ignore
            wp_json_encode(
                [
                    'payer_id' => $merchant_id,
                    'iss'      => $client_id,
                ]
            )
        );

        $auth_assertion_header = $auth_1 . '.' . $auth_2 . '.';

        return $auth_assertion_header;
    }

    /**
     * Format refund data
     *
     * @param float|string $amount
     * @param int|string $invoice_id
     * @param string $reason
     * @param string $currency
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function format_refund_data( $amount, $invoice_id, $currency, $reason ) {
        $refund_data = [
            'amount'        => [
                'value'         => wc_format_decimal( $amount, 2 ),
                'currency_code' => $currency,
            ],
            'invoice_id'    => $invoice_id,
            'note_to_payer' => '' !== $reason ? $reason : 'Refund',
        ];

        return $refund_data;
    }
}
