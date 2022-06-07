<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Order\OrderManager;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Refund
 * @package WeDevs\DokanPro\Payment\Paypal
 *
 * @see https://developer.paypal.com/docs/platforms/manage-risk/issue-refund/
 *
 * @since 3.3.0
 *
 * @author weDevs
 */
class Refund {

    /**
     * Refund constructor.
     *
     * @since 3.3.0
     *
     * @return void
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
    private function hooks() {
        add_action( 'dokan_refund_request_created', [ $this, 'process_refund' ] );
        add_filter( 'dokan_refund_approve_vendor_refund_amount', [ $this, 'vendor_refund_amount' ], 10, 3 );
        add_action( 'dokan_refund_approve_before_insert', [ $this, 'add_vendor_withdraw_entry' ], 10, 3 );
        add_action( 'dokan_refund_approve_before_insert', [ $this, 'update_gateway_fee' ], 10, 3 );
        add_filter( 'dokan_excluded_gateways_from_auto_process_api_refund', [ $this, 'exclude_from_auto_process_api_refund' ] );
    }

    /**
     * Process refund request
     *
     * @param $refund
     *
     * @return void
     * @throws \Exception
     * @since 3.3.0
     */
    public function process_refund( $refund ) {
        // get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        // check if gateway is ready
        if ( ! Helper::is_ready() ) {
            return;
        }

        // check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            dokan_log( sprintf( '%1$s: This refund is not allowed to approve, Refund ID: %2$s, Order ID: %3$s', Helper::get_gateway_title(), $refund->get_id(), $refund->get_order_id() ) );
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        // check if capture id exists
        $capture_id = $order->get_meta( '_dokan_paypal_payment_capture_id' );
        if ( ! $capture_id ) {
            $order->add_order_note(
                sprintf(
                /* translators: 1) Gateway Title 2) Refund ID 3) Order ID */
                    __( '%1$s Error: Automatic refund is not possible for this order. Reason: No PayPal capture id is found. Refund id: %2$s, Order ID: %3$s', 'dokan' ),
                    Helper::get_gateway_title(), $refund->get_id(), $refund->get_order_id()
                )
            );
            return;
        }

        // check for merchant id
        $seller_id   = $refund->get_seller_id();
        $merchant_id = Helper::get_seller_merchant_id( $seller_id );

        if ( ! $merchant_id ) {
            $order->add_order_note(
                sprintf(
                /* translators: 1) Gateway Title 2) Refund ID 3) Order ID */
                    __( '%1$s Error: Automatic refund is not possible for this order. Reason: No PayPal capture id is found. Refund id: %2$s, Order ID: %3$s', 'dokan' ),
                    Helper::get_gateway_title(), $refund->get_id(), $refund->get_order_id()
                )
            );
            return;
        }

        /*
         * Handle manual refund.
         * Here, if method returns `string true`, that means this refund is for API Refund.
         * Otherwise handle manual refund.
         *
         * Here, we are just approving if it is Manual refund.
         */
        if ( $refund->is_manual() ) {
            $refund = $refund->approve();

            if ( is_wp_error( $refund ) ) {
                dokan_log( $refund->get_error_message(), 'error' );
            }
            return;
        }

        // finally process refund
        $processor   = Processor::init();
        $refund_data = $processor->format_refund_data(
            $refund->get_refund_amount(),
            $order->get_id(),
            $order->get_currency(),
            $refund->get_refund_reason()
        );

        /**
         * @see https://developer.paypal.com/docs/api/payments/v2/#captures_refund
         */
        $paypal_refund = $processor->refund( $capture_id, $merchant_id, $refund_data );

        if ( is_wp_error( $paypal_refund ) ) {
            $order->add_order_note(
                sprintf(
                    // translators: 1) Payment Gateway id, 2) API error message
                    __( '%1$s: API Refund Error: %2$s', 'dokan' ), Helper::get_gateway_title(),
                    Helper::get_error_message( $paypal_refund )
                )
            );
            Helper::log_paypal_error( $order->get_id(), $paypal_refund, 'refund_payment' );

            // cancel refund request
            $refund->cancel();
            return;
        }

        // check data is valid
        if ( ! empty( $paypal_refund['id'] ) && 'COMPLETED' !== $paypal_refund['status'] ) {
            // find out what happened for this request
            // todo: log error type
            $refund_status_details = isset( $paypal_refund['status_details']['reason']['ECHECK'] ) ? __( 'The customer\'s account is funded through an eCheck, which has not yet cleared.', 'dokan' ) : '';
            $order->add_order_note(
                // translators: 1) gateway title 2) failed return status, 3) refund message if any
                sprintf( __( '%1$s: Automatic refund failed. Status: %2$s.%3$s', 'dokan' ), Helper::get_gateway_title(), $paypal_refund['status'], $refund_status_details )
            );
            return;
        }

        dokan_log( "[Dokan PayPal Marketplace] Refund Payment for order {$order->get_id()} :\n" . print_r( $paypal_refund, true ) );

        $order->add_order_note(
            /* translators: %s: paypal refund id */
            sprintf( __( 'Refund Processed Via %1$s ( Refund ID: %2$s )', 'dokan' ), Helper::get_gateway_title(), $paypal_refund['id'] )
        );

        //store refund id as array, this will help track all partial refunds
        $refund_ids = OrderManager::get_refund_ids_by_order( $order );

        if ( ! in_array( $paypal_refund['id'], $refund_ids, true ) ) {
            $refund_ids[] = $paypal_refund['id'];
        }
        update_post_meta( $order->get_id(), '_dokan_paypal_refund_id', $refund_ids );

        // store last refund debug id
        update_post_meta( $order->get_id(), '_dokan_paypal_refund_debug_id', $paypal_refund['paypal_debug_id'] );

        // prepare data for further process this request
        $args = [
            'paypal_marketplace'    => true,
            'net_refund_amount'     => (float) $paypal_refund['seller_payable_breakdown']['net_amount']['value'],
            'reversed_admin_fee'    => (float) $paypal_refund['seller_payable_breakdown']['platform_fees'][0]['amount']['value'],
            'reversed_gateway_fee'  => (float) $paypal_refund['seller_payable_breakdown']['paypal_fee']['value'],
            'total_refunded_amount' => (float) $paypal_refund['seller_payable_breakdown']['total_refunded_amount']['value'],
            'paypal_refund_id'      => $paypal_refund['id'],
        ];

        // Try to approve the refund.
        $refund = $refund->approve( $args );

        if ( is_wp_error( $refund ) ) {
            dokan_log( $refund->get_error_message(), 'error' );
        }
    }

    /**
     * Recalculate gateway fee after a refund.
     *
     * @param $refund \WeDevs\DokanPro\Refund\Refund
     * @param $args array
     * @param $vendor_refund float
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function update_gateway_fee( $refund, $args, $vendor_refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );
        $dokan_gateway_fee = (float) get_post_meta( $order->get_id(), '_dokan_paypal_payment_processing_fee', true );

        if ( $args['reversed_gateway_fee'] > 0 ) {
            $dokan_gateway_fee -= $args['reversed_gateway_fee'];
        }

        /*
         * If there is no remaining amount then its full refund and we are updating the processing fee to 0.
         * because seller is already paid the processing fee from his account. if we keep this then it will deducted twice.
         */
        if ( $order->get_remaining_refund_amount() <= 0 ) {
            $dokan_gateway_fee = 0;
        }

        $order->update_meta_data( 'dokan_gateway_fee', $dokan_gateway_fee );
        $order->save_meta_data();
    }

    /**
     * Withdraw entry for automatic refund as debit
     *
     * @param $refund \WeDevs\DokanPro\Refund\Refund
     * @param $args array
     * @param $vendor_refund float
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function add_vendor_withdraw_entry( $refund, $args, $vendor_refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        global $wpdb;

        $wpdb->insert(
            $wpdb->dokan_vendor_balance,
            [
                'vendor_id'    => $refund->get_seller_id(),
                'trn_id'       => $refund->get_order_id(),
                'trn_type'     => 'dokan_refund',
                'perticulars'  => maybe_serialize( $args ),
                'debit'        => $vendor_refund,
                'credit'       => 0,
                'status'       => 'wc-completed', // see: Dokan_Vendor->get_balance() method
                'trn_date'     => current_time( 'mysql' ),
                'balance_date' => current_time( 'mysql' ),
            ],
            [
                '%d',
                '%d',
                '%s',
                '%s',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
            ]
        );
    }

    /**
     * Set vendor refund amount as paypal refund amount
     *
     * @param $vendor_refund float
     * @param $args array
     * @param $refund \WeDevs\DokanPro\Refund\Refund
     *
     * @since 3.3.0
     *
     * @return float
     */
    public function vendor_refund_amount( $vendor_refund, $args, $refund ) {
        if ( isset( $args['paypal_marketplace'], $args['net_refund_amount'] ) && $args['paypal_marketplace'] ) {
            return wc_format_decimal( $args['net_refund_amount'] );
        }

        return $vendor_refund;
    }

    /**
     * Excludes Paypal marketplace from auto process API refund.
     *
     * @since 3.5.0
     *
     * @param array $gateways
     *
     * @return array
     */
    public function exclude_from_auto_process_api_refund( $gateways ) {
        $gateways[ Helper::get_gateway_id() ] = Helper::get_gateway_title();
        return $gateways;
    }
}
