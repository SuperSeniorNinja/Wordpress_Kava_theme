<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Subscriptions;

use DokanPro\Modules\Subscription\SubscriptionPack;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Order\OrderManager;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Subscriptions\Processor as SubscriptionProcessor;

/**
 * Class VendorSubscription
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\VendorSubscriptions
 *
 * @see https://developer.paypal.com/docs/subscriptions/
 *
 * @since 3.3.7
 */
class VendorSubscription {

    /**
     * SubscriptionController constructor.
     *
     * @since 3.3.7
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init_hooks' ] );
    }

    /**
     * Init Vendor Subscription Related Hooks
     *
     * @since 3.3.7
     */
    public function init_hooks() {
        if ( Helper::has_vendor_subscription_module() ) {
            add_action( 'woocommerce_process_product_meta_product_pack', [ $this, 'handle_subscription_after_save' ], 99, 1 );

            //cart handler
            add_filter( 'dokan_paypal_marketplace_merchant_id', [ $this, 'get_merchant_id_for_subscription_product' ], 10, 2 );
            add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'maybe_empty_cart' ], 100, 3 );

            //order handler
            // this hook will handle creating a vendor subscription (recurring and non-recurring) after successful payment
            add_action( 'dokan_paypal_capture_payment_completed', [ $this, 'handle_subscription_after_payment' ], 10, 2 );

            // this hook will handle vendor subscription product purchase
            add_filter( 'dokan_paypal_process_payment', [ $this, 'handle_subscription_product' ], 10, 1 );

            // below hooks will decide if smart payment button will load or not
            add_filter( 'dokan_paypal_load_payment_scripts', [ $this, 'load_payment_elements' ], 10, 1 );
            add_filter( 'dokan_paypal_display_paypal_button', [ $this, 'load_payment_elements' ], 10, 1 );
            add_filter( 'dokan_paypal_payment_fields', [ $this, 'load_payment_elements' ], 10, 1 );

            // below hooks will be used to cancel subscription via user actions
            // recurring subscription hooks
            add_action( 'dps_cancel_recurring_subscription', [ $this, 'suspend_recurring_subscription' ], 10, 3 );
            add_action( 'dps_activate_recurring_subscription', [ $this, 'reactivate_recurring_subscription' ], 10, 2 );

            // non recurring subscription hooks
            add_filter( 'dps_cancel_non_recurring_subscription_immediately', [ $this, 'cancel_non_recurring_subscription_immediately' ], 10, 2 );
            add_action( 'dokan_subscription_cancelled', [ $this, 'before_cancelling_subscriptions' ], 10, 3 );
            // ignore vendor subscription unpaid orders
            add_filter( 'woocommerce_cancel_unpaid_order', [ $this, 'cancel_unpaid_order' ], 10, 2 );
            // change suborder notice for vendors
            add_filter( 'dokan_suborder_notice_to_customer', [ $this, 'suborder_notice_to_vendor' ], 10, 2 );
        }
    }

    /**
     * Handle subscription product after save
     *
     * @param $product_id
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function handle_subscription_after_save( $product_id ) {
        if ( ! Helper::is_enabled() ) {
            return;
        }

        global $pagenow;
        $screen = get_current_screen();

        if ( ! current_user_can( 'manage_woocommerce' ) && ( 'edit' !== $screen->parent_base || 'post.php' !== $pagenow ) ) {
            return;
        }

        if ( ! SubscriptionHelper::is_recurring_subscription_product( $product_id ) ) {
            return;
        }

        if ( ! get_post_meta( $product_id, '_dokan_paypal_marketplace_subscription_product_id', true ) ) {
            $created_product = Helper::create_product_in_paypal( $product_id );

            if ( is_wp_error( $created_product ) ) {
                dokan_log( 'Create product in paypal error: ' . Helper::get_error_message( $created_product ) );
                return;
            }
        }
    }

    /**
     * Get admin partner id for subscription product
     *
     * @param $merchant_id
     * @param $product_id
     *
     * @since 3.3.7
     *
     * @return string
     */
    public function get_merchant_id_for_subscription_product( $merchant_id, $product_id ) {
        // check if this is a recurring subscription product
        if ( SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return Helper::get_partner_id();
        }

        return $merchant_id;
    }

    /**
     * Empty cart if the vendor is already in a subscription
     *
     * @param $valid
     * @param $product_id
     * @param $quantity
     *
     * @since 3.3.7
     *
     * @return mixed
     */
    public function maybe_empty_cart( $valid, $product_id, $quantity ) {
        if ( ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return $valid;
        }

        // check if user has active subscription pack
        $vendor_id           = dokan_get_current_user_id();
        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $vendor_subscription instanceof SubscriptionPack || ! $vendor_subscription->has_subscription() ) {
            return $valid;
        }

        // get current users subscription order
        $subscription_order = SubscriptionHelper::get_subscription_order( $vendor_id );
        if ( ! $subscription_order || $subscription_order->get_payment_method() !== Helper::get_gateway_id() ) {
            return $valid;
        }

        WC()->cart->empty_cart();

        wc_add_notice( __( 'You are already under a subscription plan. You need to cancel it first.', 'dokan' ) );

        $page_url = dokan_get_navigation_url( 'subscription' );
        wp_safe_redirect( add_query_arg( [ 'already-has-subscription' => 'true' ], $page_url ) );
        exit;
    }

    /**
     * Make decision for loading payment scripts/html
     * if cart contains subscription product then do not need to load payment scripts
     *
     * @param bool $ret
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function load_payment_elements( $ret ) {
        if ( SubscriptionHelper::cart_contains_subscription() &&
            SubscriptionHelper::cart_contains_recurring_subscription_product()
        ) {
            return false;
        }

        return $ret;
    }

    /**
     * Handle subscription product
     *
     * @param $data
     *
     * @since 3.3.7
     *
     * @return array
     */
    public function handle_subscription_product( $data ) {
        $order = $data['order'];
        if ( ! is_object( $order ) ) {
            $order = wc_get_order( absint( $order ) );
        }

        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        // check if subscription module is active
        if ( ! $product ) {
            return [];
        }

        // create recurring subscription
        if ( SubscriptionHelper::is_recurring_pack( $product->get_id() ) ) {
            return $this->handle_recurring_subscription_purchase( $order, $product );
        }

        // create nonrecurring subscription
        return $this->handle_non_recurring_subscription_purchase( $order );
    }

    /**
     * Handle non recurring vendor subscription
     *
     * @param \WC_Order $order
     *
     * @since 3.3.7
     *
     * @return array
     */
    private function handle_non_recurring_subscription_purchase( \WC_Order $order ) {
        $create_order_data = [
            'intent'              => 'CAPTURE',
            'payer'               => OrderManager::get_shipping_address( $order, true ),
            'purchase_units'      => [ OrderManager::make_subscription_purchase_unit_data( $order ) ],
            'application_context' => [
                'return_url'          => $order->get_checkout_order_received_url(),
                'cancel_url'          => $order->get_cancel_order_url_raw(),
                'brand_name'          => get_bloginfo( 'name' ),
                'user_action'         => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'payment_method'      => [
                    'payer_selected'  => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
            ],
        ];

        $processor = SubscriptionProcessor::init();
        $create_order_url = $processor->create_order( $create_order_data );

        if ( is_wp_error( $create_order_url ) ) {
            $error_message = sprintf(
            // translators: 1) error message from payment gateway
                __( 'Error while creating PayPal order: %1$s', 'dokan' ), Helper::get_error_message( $create_order_url )
            );
            wc_add_notice( $error_message, 'error' );
            Helper::log_paypal_error( $order->get_id(), $create_order_url, 'dpm_create_order' );

            return [
                'product_type' => 'product_pack',
                'data'         => [
                    'result'   => 'failure',
                    'redirect' => false,
                    'messages' => '<ul class="woocommerce-error" role="alert"><li>' . $error_message . '</li></ul>',
                ],
            ];
        }
        //store paypal debug id & create order id
        $order->update_meta_data( '_dokan_paypal_create_order_debug_id', $create_order_url['paypal_debug_id'] );
        $order->update_meta_data( '_dokan_paypal_order_id', $create_order_url['id'] );
        $order->update_meta_data( '_dokan_paypal_redirect_url', $create_order_url['links'][1]['href'] );
        $order->update_meta_data( '_dokan_vendor_subscription_order', 'yes' );
        $order->update_meta_data( 'dokan_gateway_fee_paid_by', 'admin' );
        $order->update_meta_data( 'shipping_fee_recipient', 'admin' );
        $order->update_meta_data( 'tax_fee_recipient', 'admin' );
        $order->save_meta_data();

        return [
            'product_type' => 'product_pack',
            'data'         => [
                'result'              => 'success',
                'id'                  => $order->get_id(),
                'paypal_redirect_url' => $create_order_url['links'][1]['href'],
                'paypal_order_id'     => $create_order_url['id'],
                'redirect'            => $create_order_url['links'][1]['href'],
                'success_redirect'    => $order->get_checkout_order_received_url(),
                'cancel_redirect'     => $order->get_cancel_order_url_raw(),
            ],
        ];
    }
    /**
     * Create Subscription on paypal
     *
     * @param \WC_Order $order
     * @param \WC_Product $product
     *
     * @since 3.3.7
     *
     * @return array
     */
    private function handle_recurring_subscription_purchase( $order, $product ) {
        $plan_id    = null;
        $product_id = null;
        $processor  = SubscriptionProcessor::init();

        // check if order has subscription plan
        $plan_id = $order->get_meta( '_dokan_paypal_marketplace_subscription_plan_id' );

        if ( ! empty( $plan_id ) ) {
            // disable old current plan
            $processor->deactivate_plan( $plan_id );
        }

        // create new subscription plan
        $plan_id = Helper::create_plan_in_paypal( $product, $order );

        if ( is_wp_error( $plan_id ) ) {
            wc_add_notice( Helper::get_error_message( $plan_id ), 'error' );

            return [
                'product_type' => 'product_pack',
                'data'         => [
                    'result'   => 'error',
                    'redirect' => $order->get_cancel_order_url(),
                ],
            ];
        }

        $subscription_data = [
            'plan_id'             => $plan_id,
            'quantity'            => '1',
            'application_context' => [
                'brand_name'          => get_bloginfo( 'name' ),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'SUBSCRIBE_NOW',
                'payment_method'      => [
                    'payer_selected'  => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url'          => $order->get_checkout_order_received_url(),
                'cancel_url'          => $order->get_cancel_order_url(),
            ],
            'custom_id'           => $order->get_id(),
            'subscriber'          => [
                'name' => [
                    'given_name' => $order->get_billing_first_name(),
                    'surname'    => $order->get_billing_last_name(),
                ],
                'email_address' => $order->get_billing_email(),
            ],
        ];

        $created_subscription = $processor->create_subscription( $subscription_data );

        if ( is_wp_error( $created_subscription ) ) {
            $error_message = sprintf(
            // translators: 1) error message from payment gateway
                __( 'Error while creating PayPal Subscription order: %1$s', 'dokan' ), Helper::get_error_message( $created_subscription )
            );
            wc_add_notice( $error_message, 'error' );
            Helper::log_paypal_error( $order->get_id(), $created_subscription, 'create_subscription' );

            return [
                'product_type' => 'product_pack',
                'data'         => [
                    'result'   => 'failure',
                    'redirect' => false,
                    'messages' => '<ul class="woocommerce-error" role="alert"><li>' . $error_message . '</li></ul>',
                ],
            ];
        }

        //store paypal debug id & create order id
        $order->update_meta_data( '_dokan_paypal_create_order_debug_id', $created_subscription['paypal_debug_id'] );
        $order->update_meta_data( '_dokan_paypal_order_id', $created_subscription['id'] );
        $order->update_meta_data( '_dokan_paypal_redirect_url', $created_subscription['links'][0]['href'] );
        $order->update_meta_data( '_dokan_vendor_subscription_order', 'yes' );
        $order->update_meta_data( '_dokan_paypal_marketplace_vendor_subscription_id', $created_subscription['id'] );
        $order->update_meta_data( 'dokan_gateway_fee_paid_by', 'admin' );
        $order->update_meta_data( 'shipping_fee_recipient', 'admin' );
        $order->update_meta_data( 'tax_fee_recipient', 'admin' );
        $order->save_meta_data();

        return [
            'product_type' => 'product_pack',
            'data'         => [
                'result'              => 'success',
                'id'                  => $order->get_id(),
                'paypal_redirect_url' => $created_subscription['links'][0]['href'],
                'paypal_order_id'     => $created_subscription['id'],
                'redirect'            => $created_subscription['links'][0]['href'],
                'success_redirect'    => $order->get_checkout_order_received_url(),
                'cancel_redirect'     => $order->get_cancel_order_url_raw(),
            ],
        ];
    }

    /**
     * Store subscription data after capture payment in PayPal
     *
     * @param \WC_Order $order
     * @param $capture_payment
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function handle_subscription_after_payment( \WC_Order $order, $capture_payment ) {
        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );

        if ( empty( $product ) ) {
            return;
        }

        $dokan_subscription = new SubscriptionPack( $product->get_id(), $order->get_customer_id() );

        $dokan_subscription->activate_subscription( $order );

        if ( ! $dokan_subscription->is_recurring() ) {
            // add order notes
            $order->add_order_note( __( 'Subscription activated.', 'dokan' ) );
        }
    }

    /**
     * Reactivate recurring subscription
     *
     * @param int $order_id
     * @param int $vendor_id
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function reactivate_recurring_subscription( $order_id, $vendor_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        // validate product
        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        if ( ! $product ) {
            return;
        }

        // get subscription object
        $vendor_subscription = new SubscriptionPack( $product->get_id(), $vendor_id );

        // get paypal subscription id
        $subscription_id = get_user_meta( $vendor_id, '_dokan_paypal_marketplace_vendor_subscription_id', true );

        $processor_obj = SubscriptionProcessor::init();
        // get main subscription info
        $subscription_details = $processor_obj->get_subscription( $subscription_id );

        if ( is_wp_error( $subscription_details ) ) {
            dokan_log( '[PayPal Marketplace] Invalid Subscription Details: ' . print_r( $subscription_details, true ) );
            return;
        }

        // check if already suspended
        if ( 'ACTIVE' === strtoupper( $subscription_details['status'] ) ) {
            $vendor_subscription->reset_active_cancelled_subscription();
            return;
        }

        $activate_subscription = $processor_obj->activate_subscription( $subscription_id, 'reactivating subscription' );

        if ( is_wp_error( $activate_subscription ) ) {
            dokan_log( '[PayPal Marketplace] Error while activating subscription: ' . print_r( $activate_subscription, true ) );
            return;
        }

        if ( $vendor_subscription->reactivate_subscription() ) {
            $order->add_order_note( __( 'Subscription reactivated.', 'dokan' ) );
        }
    }

    /**
     * Cancel/Suspend subscription from PayPal end
     *
     * @param int $order_id
     * @param int $vendor_id
     * @param bool $cancel_immediately
     *
     * @throws \Exception
     *
     * @return void
     */
    public function suspend_recurring_subscription( $order_id, $vendor_id, $cancel_immediately ) {
        $order         = wc_get_order( $order_id );
        $cancel_reason = __( 'Subscription Suspended By Admin.', 'dokan' );

        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        // validate product
        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        if ( ! $product ) {
            return;
        }

        // get subscription object
        $vendor_subscription = new SubscriptionPack( $product->get_id(), $vendor_id );

        // get paypal subscription id
        $subscription_id = get_user_meta( $vendor_id, '_dokan_paypal_marketplace_vendor_subscription_id', true );

        if ( empty( $subscription_id ) ) {
            return;
        }

        // if $cancel_immediately is false, check if this hook was called from frontend, if so cancel subscription immediately
        if ( ! $cancel_immediately && ( isset( $_POST['dps_cancel_subscription'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dps-sub-cancel' ) ) ) {
            $cancel_immediately = true;
            $cancel_reason = __( 'Subscription Suspended By Vendor', 'dokan' );
        }

        $processor_obj = SubscriptionProcessor::init();
        // get main subscription info
        $subscription_details = $processor_obj->get_subscription( $subscription_id );

        if ( is_wp_error( $subscription_details ) ) {
            dokan_log( '[PayPal Marketplace] Invalid Subscription Details: ' . print_r( $subscription_details, true ) );
            return;
        }

        // check subscription status
        if ( ! in_array( strtoupper( $subscription_details['status'] ), [ 'SUSPENDED', 'ACTIVE' ], true ) ) {
            // delete subscription information from our end
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
            // add order note
            $order->add_order_note( __( 'Subscription Cancelled.', 'dokan' ) );
            return;
        }

        // permanently cancel subscription
        if ( $cancel_immediately ) {
            // suspend subscription
            $response = $processor_obj->cancel_subscription( $subscription_id, 'subscription suspended' );
            if ( is_wp_error( $response ) ) {
                dokan_log( '[PayPal Marketplace] Subscription Cancellation Failed: ' . print_r( $response, true ) );
                return;
            }

            // delete subsciption
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
            // add order note
            $order->add_order_note( __( 'Subscription Cancelled.', 'dokan' ) );
            return;
        }

        // check if already suspended
        if ( 'SUSPENDED' === strtoupper( $subscription_details['status'] ) ) {
            $vendor_subscription->set_active_cancelled_subscription();
            return;
        }

        // suspend subscription
        $response = $processor_obj->suspend_subscription( $subscription_id, 'subscription suspended' );
        if ( is_wp_error( $response ) ) {
            dokan_log( '[PayPal Marketplace] Subscription Suspend Failed: ' . print_r( $response, true ) );
            return;
        }

        $now = dokan_current_datetime();
        // check if next billing time exists
        if ( ! empty( $subscription_details['billing_info']['next_billing_time'] ) ) {
            $next_billing_timestamp = strtotime( $subscription_details['billing_info']['next_billing_time'], time() );
            if ( $now->getTimestamp() < $next_billing_timestamp ) {
                $now = $now->setTimestamp( $next_billing_timestamp );
            }
        }

        if ( $vendor_subscription->suspend_subscription( $now->format( 'Y-m-d H:i:s' ) ) ) {
            // add order note
            $order->add_order_note( $cancel_reason );
        }
    }

    /**
     * Cancel Non Recurring Subscription Immediately
     *
     * @param bool $cancel_immediately
     * @param int $order_id
     * @param int $vendor_id
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function cancel_non_recurring_subscription_immediately( $cancel_immediately, $order_id ) {
        if ( $cancel_immediately === true ) {
            return $cancel_immediately;
        }
        $order = wc_get_order( $order_id );

        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $cancel_immediately;
        }

        // if $cancel_immediately is false, check if this hook was called from frontend, if so cancel subscription immediately
        if ( isset( $_POST['dps_cancel_subscription'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dps-sub-cancel' ) ) {
            $cancel_immediately = true;
        }

        return $cancel_immediately;
    }

    /**
     * Cancel main order before cancelling non-recurring subscription
     *
     * @param $vendor_id
     * @param $product_id
     * @param $order_id
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function before_cancelling_subscriptions( $vendor_id, $product_id, $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        $subscription = new SubscriptionPack( $product_id, $vendor_id );
        // if subscription is recurring, return
        if ( $subscription->is_recurring() ) {
            return;
        }

        // update subscription order note as cancelled
        $order->add_order_note( __( 'Subscription Cancelled.', 'dokan' ) );
    }

    /**
     * Do not cancel unpaid orders if order is vendor subscription order
     *
     * @param bool $cancel
     * @param \WC_Order $order
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function cancel_unpaid_order( $cancel, $order ) {
        // check if order is vendor subscription order
        if ( $order->get_meta( '_dokan_vendor_subscription_order' ) === 'yes' ) {
            return false;
        }

        return $cancel;
    }

    /**
     * Change notice to vendors for recurring payments
     *
     * @param string $message
     * @param \WC_Order $order
     *
     * @since 3.3.7
     *
     * @return string
     */
    public function suborder_notice_to_vendor( $message, $order ) {
        // check if order is vendor subscription order
        if ( $order->get_meta( '_dokan_vendor_subscription_order' ) === 'yes' ) {
            $message = esc_html__( 'Vendor Subscriptions Related Orders.', 'dokan' );
        }

        return $message;
    }
}
