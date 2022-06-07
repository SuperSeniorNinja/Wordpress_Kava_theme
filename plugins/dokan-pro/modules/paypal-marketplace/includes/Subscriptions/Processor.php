<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Subscriptions;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor as PayPalProcessor;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

/**
 * Class SubscriptionProcessor
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\VendorSubscriptions
 *
 * @see https://developer.paypal.com/docs/subscriptions/
 *
 * @since 3.3.7
 */
class Processor extends PayPalProcessor {

    /**
     * Instance of self
     *
     * @var static
     */
    protected static $subscription_instance = null;

    /**
     * Initialize Processor() class
     *
     * @since 3.3.7
     *
     * @return Processor
     */
    public static function init() {
        if ( static::$subscription_instance === null ) {
            static::$subscription_instance = new static();
        }

        return static::$subscription_instance;
    }

    /**
     * Create product for subscription
     *
     * @see  https://developer.paypal.com/docs/api/catalog-products/v1/#products_create
     *
     * @param array $product_data Product data is contains necessary information about the subscription product
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function create_product( $product_data ) {
        $url = $this->make_paypal_url( 'v1/catalogs/products' );

        $response = $this->make_request(
            [
				'url'  => $url,
				'data' => wp_json_encode( $product_data ),
			]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( ! empty( $response['id'] ) ) {
            return $response;
        }

        return new \WP_Error( 'dokan_paypal_create_product_error', $response );
    }

    /**
     * Get product list
     *
     * @since 3.3.7
     *
     * @return array|mixed|\WP_Error
     */
    public function get_products_list() {
        $url      = $this->make_paypal_url( 'v1/catalogs/products' );
        $response = $this->make_request(
            [
                'url'    => $url,
                'method' => 'get',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['products'] ) ) {
            return $response;
        }

        return new \WP_Error( 'dokan_paypal_get_product_list_error', $response );
    }

    /**
     * Get single product details
     *
     * @param $paypal_product_id
     *
     * @since 3.3.7
     *
     * @return array|mixed|\WP_Error
     */
    public function get_product( $paypal_product_id ) {
        $url = $this->make_paypal_url( "v1/catalogs/products/{$paypal_product_id}" );

        $response = $this->make_request(
            [
                'url'    => $url,
                'method' => 'get',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['id'] ) ) {
            return $response;
        }

        return new \WP_Error( 'dokan_paypal_get_product_details_error', $response );
    }

    /**
     * Create Plan against a product for subscription
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#plans_create
     *
     * @param array $plan_data Plan data is contains necessary information about the subscription product
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function create_plan( $plan_data ) {
        $url = $this->make_paypal_url( 'v1/billing/plans' );

        $response = $this->make_request(
            [
                'url'  => $url,
                'data' => wp_json_encode( $plan_data ),
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( ! empty( $response['id'] ) ) {
            return $response;
        }

        return new \WP_Error( 'dokan_paypal_create_plan_error', $response );
    }

    /**
     * Deactivates a plan, by ID.
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#plans_deactivate
     *
     * @param string $plan_id The ID of the plan.
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error A successful request returns the HTTP 204 No Content status code with no JSON response body.
     */
    public function deactivate_plan( $plan_id ) {
        $url = $this->make_paypal_url( "v1/billing/plans/{$plan_id}/deactivate" );

        $response = $this->make_request(
            [
                'url'  => $url,
            ]
        );

        return $response;
    }

    /**
     * Create subscription on paypal
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_create
     *
     * @param array $subscription_data Subscription data is contains necessary parameter about the subscription
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function create_subscription( $subscription_data ) {
        $url = $this->make_paypal_url( 'v1/billing/subscriptions' );

        $this->additional_request_header = [
            'Prefer'                        => 'return=representation',
            'PayPal-Partner-Attribution-Id' => Helper::get_bn_code(),
        ];

        $response = $this->make_request(
            [
                'url'  => $url,
                'data' => wp_json_encode( $subscription_data ),
            ]
        );

        $this->additional_request_header = [];

        if ( is_wp_error( $response ) ) {
            dokan_log( 'create_subscription: ' . print_r( $response, true ) );
            return $response;
        }

        if ( ! empty( $response['id'] ) ) {
            return $response;
        }

        return new \WP_Error( 'dokan_paypal_create_subscription_error', $response );
    }

    /**
     * Get single subscription details
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
     *
     * @param $subscription_id
     *
     * @since 3.3.7
     *
     * @return array|mixed|\WP_Error
     */
    public function get_subscription( $subscription_id ) {
        $url = $this->make_paypal_url( "v1/billing/subscriptions/{$subscription_id}" );

        $response = $this->make_request(
            [
                'url'    => $url,
                'method' => 'get',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( isset( $response['id'] ) ) {
            return $response;
        }

        return new \WP_Error( 'dokan_paypal_get_subscription_error', $response );
    }

    /**
     * Activate a subscription
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_activate
     *
     * @param string $subscription_id
     * @param string $reason
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function activate_subscription( $subscription_id, $reason ) {
        return $this->subscription_action( $subscription_id, $reason, 'activate' );
    }

    /**
     * Cancel a subscription
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_cancel
     *
     * @param string $subscription_id
     * @param string $reason
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function cancel_subscription( $subscription_id, $reason ) {
        return $this->subscription_action( $subscription_id, $reason, 'cancel' );
    }

    /**
     * Suspend a subscription
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_suspend
     *
     * @param string $subscription_id
     * @param string $reason
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function suspend_subscription( $subscription_id, $reason ) {
        return $this->subscription_action( $subscription_id, $reason, 'suspend' );
    }

    /**
     * Subscription actions based on action type
     * Types are: activate, cancel, suspend
     *
     * @param string $subscription_id
     * @param string $reason
     * @param string $action_type
     *
     * @since 3.3.7
     *
     * @return array|\WP_Error
     */
    public function subscription_action( $subscription_id, $reason, $action_type ) {
        $url = $this->make_paypal_url( "v1/billing/subscriptions/{$subscription_id}/{$action_type}" );

        $response = $this->make_request(
            [
                'url'  => $url,
                'data' => wp_json_encode( [ 'reason' => $reason ] ),
            ]
        );

        return $response;
    }
}
