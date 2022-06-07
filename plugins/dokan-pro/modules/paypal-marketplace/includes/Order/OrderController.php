<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Order;

use WC_Order_Query;
use WeDevs\DokanPro\Modules\PayPalMarketplace\BackgroundProcess\DelayDisburseFund;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ajax
 * @package WeDevs\Dokan\Gateways\PayPal
 *
 * @since 3.3.0
 *
 * @author weDevs
 */
class OrderController {

    /**
     * Ajax constructor.
     *
     * @since 3.3.0
     */
    public function __construct() {
        add_action( 'wp_ajax_dokan_paypal_create_order', [ $this, 'create_order' ] );
        add_action( 'wp_ajax_dokan_paypal_capture_payment', [ $this, 'capture_payment' ] );
        add_action( 'wp_ajax_nopriv_dokan_paypal_capture_payment', [ $this, 'capture_payment' ] );
        add_action( 'woocommerce_order_status_changed', [ $this, 'order_status_changed' ], 10, 3 );
        add_action( 'dokan_paypal_mp_daily_schedule', [ $this, 'disburse_delayed_payment' ] );
    }

    /**
     * Create order on paypal
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function create_order() {
        $order_id = $this->do_validation();

        /**
         * @var $dokan_paypal \WeDevs\DokanPro\Modules\PayPalMarketplace\PaymentMethods\PayPal
         */
        $dokan_paypal    = dokan_pro()->module->paypal_marketplace->gateway_paypal;
        $process_payment = $dokan_paypal->process_payment( $order_id );

        if ( isset( $process_payment['result'] ) && $process_payment['result'] === 'failure' ) {
            WC()->session->set( 'wc_notices', [] );
            wp_send_json_error(
                [
                    'data'    => $process_payment,
                ]
            );
        }

        wp_send_json_success(
            [
                'data'    => $process_payment,
            ]
        );
    }

    /**
     * Capture Payment
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function capture_payment() {
        try {
            $order_id = $this->do_validation();

            $paypal_order_id = get_post_meta( $order_id, '_dokan_paypal_order_id', true );

            if ( ! $paypal_order_id ) {
                wp_send_json_error(
                    [
                        'type'    => 'no_order_id',
                        'message' => __( 'No PayPal order id found.', 'dokan' ),
                    ]
                );
            }

            $this->handle_capture_payment_validation( $order_id, $paypal_order_id );
        } catch ( \Exception $e ) {
            wp_send_json_error(
                [
                    'type'    => 'paypal_capture_payment',
                    'message' => __( 'Error in capturing payment.', 'dokan' ),
                ]
            );
        }
    }

    /**
     * Handle capture payment/store data
     *
     * @param $order_id
     * @param $paypal_order_id
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle_capture_payment_validation( $order_id, $paypal_order_id ) {
        $order     = wc_get_order( $order_id );
        $processor = Processor::init();

        //first fetch the order details
        $paypal_order = $processor->get_order( $paypal_order_id );

        if ( is_wp_error( $paypal_order ) ) {
            wp_send_json_error(
                [
                    'type'    => 'paypal_capture_payment',
                    'message' => __( 'Error in getting paypal order.', 'dokan' ),
                ]
            );
        }

        if ( ! $processor->continue_transaction( $paypal_order ) ) {
            wp_send_json_error(
                [
                    'type'    => 'paypal_capture_payment',
                    'message' => __( 'Authorization not supported.', 'dokan' ),
                ]
            );
        }

        $capture_payment = $processor->capture_payment( $paypal_order_id );

        if ( is_wp_error( $capture_payment ) ) {
            Helper::log_paypal_error( $order->get_id(), $capture_payment, 'dpm_capture_payment' );

            wp_send_json_error(
                [
                    'type'    => 'paypal_capture_payment',
                    'message' => $capture_payment->get_error_message(),
                ]
            );
        }

        //dokan_log( "[Dokan PayPal Marketplace] Capture Payment:\n" . print_r( $capture_payment, true ) );

        //store paypal debug id
        $order->update_meta_data( '_dokan_paypal_capture_payment_debug_id', $capture_payment['paypal_debug_id'] );
        $order->save_meta_data();

        //process order data
        OrderManager::handle_order_complete_status( $capture_payment['purchase_units'], $paypal_order_id );

        // check charged captured successfully
        if (
            isset( $capture_payment['intent'], $capture_payment['status'] ) &&
            'CAPTURE' === $capture_payment['intent'] &&
            'COMPLETED' === $capture_payment['status']
        ) {
            // make paymet completed
            $order->payment_complete();

            /**
             * @args WC_Order $order Main Order ID
             * @args array $purchase_units
             * $args string $paypal_order_id
             */
            do_action( 'dokan_paypal_capture_payment_completed', $order, $capture_payment['purchase_units'], $paypal_order_id );
        }

        wp_send_json_success(
            [
                'type'    => 'paypal_capture_payment',
                'message' => __( 'Successfully captured payment!', 'dokan' ),
                'data'    => $capture_payment,
            ]
        );
    }

    /**
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     */
    public function order_status_changed( $order_id, $old_status, $new_status ) {
        // check if order status is completedd
        if ( 'completed' !== $new_status ) {
            return;
        }

        // get order
        $order = wc_get_order( $order_id );

        // check if order is a valid WC_Order instance
        if ( ! $order ) {
            return;
        }

        // check payment gateway used was dokan paypal marketplace
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        // check that payment disbursement method wasn't direct
        if ( 'ON_ORDER_COMPLETE' !== $order->get_meta( '_dokan_paypal_payment_disbursement_mode' ) ) {
            return;
        }

        // check already disbursed or not
        if ( 'yes' === $order->get_meta( '_dokan_paypal_payment_withdraw_balance_added' ) ) {
            return;
        }

        // finally call api to disburse fund to vendor
        OrderManager::_disburse_payment( $order );
    }

    /**
     * This method will add queue for to be disburse payments
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function disburse_delayed_payment() {
        $time_now       = dokan_current_datetime();
        $time_now       = $time_now->setTime( 23, 59, 59 );
        $interval_days  = Helper::get_disbursement_delay_period();

        if ( $interval_days > 0 ) {
            $interval_days  = $interval_days > 29 ? 29 : $interval_days;
            $interval       = new \DateInterval( "P{$interval_days}D" );
            $time_now       = $time_now->sub( $interval );
        }

        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10, 2 );
        $query = new WC_Order_Query(
            [
                'dokan_paypal_delayed_disbursement' => true,
                'date_created'  => '<=' . $time_now->getTimestamp(),
                'status'        => [ 'wc-processing', 'wc-completed' ],
                'type'          => 'shop_order',
                'limit'         => -1,
                'return'        => 'ids',
            ]
        );
        $orders = $query->get_orders();
        remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10 );

        $bg_class = dokan_pro()->module->paypal_marketplace->delay_disburse_bg;
        if ( ! $bg_class instanceof DelayDisburseFund ) {
            return;
        }

        foreach ( $orders as $order_id ) {
            $bg_class->push_to_queue( [ 'order_id' => $order_id ] );
        }

        $bg_class->save()->dispatch();
    }

    /**
     * This method will add metadata param
     *
     * @param $query
     * @param $query_vars
     *
     * @since 3.3.0
     *
     * @return mixed
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['dokan_paypal_delayed_disbursement'] ) ) {
            $query['meta_query'][] = [
                'key'       => '_dokan_paypal_payment_disbursement_mode',
                'value'     => 'DELAYED',
                'compare'   => '=',
            ];
            $query['meta_query'][] = [
                'key'       => '_dokan_paypal_payment_withdraw_balance_added',
                'value'     => 'yes',
                'compare'   => '!=',
            ];
        }

        return $query;
    }

    /**
     * Do the necessary validation
     *
     * @param $post_data
     *
     * @since 3.3.0
     *
     * @return int|string
     */
    public function do_validation() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'dokan_paypal_checkout_nonce' ) ) {
            dokan_log( 'Nonce Validation Failed. Posted Data: ' . print_r( $_POST, true ) );
            wp_send_json_error(
                [
                    'type'    => 'nonce',
                    'message' => __( 'Nonce validation failed.', 'dokan' ),
                ]
            );
        }

        $order_id = ( isset( $_POST['order_id'] ) ) ? sanitize_key( wp_unslash( $_POST['order_id'] ) ) : 0;

        if ( ! $order_id ) {
            wp_send_json_error(
                [
                    'type'    => 'no_order_id',
                    'message' => __( 'No Order ID provided.', 'dokan' ),
                ]
            );
        }

        return $order_id;
    }
}
