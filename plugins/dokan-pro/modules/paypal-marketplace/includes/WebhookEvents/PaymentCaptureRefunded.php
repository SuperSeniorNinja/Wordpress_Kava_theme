<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Order\OrderManager;
use WeDevs\DokanPro\Refund\Refund;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class PaymentCaptureRefunded
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents
 *
 * @since 3.3.0
 *
 * @see https://developer.paypal.com/docs/api/payments/v2/#refunds
 *
 * @author weDevs
 */
class PaymentCaptureRefunded extends WebhookEventHandler {

    /**
     * CheckoutOrderApproved constructor.
     *
     * @param $event
     *
     * @since 3.3.0
     */
    public function __construct( $event ) {
        $this->set_event( $event );
    }

    /**
     * Handle payment sale
     *
     * @since 3.3.0
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle() {
        $event          = $this->get_event();
        $order_id       = sanitize_text_field( $event->resource->custom_id );
        $refund_id      = sanitize_text_field( $event->resource->id );
        $refund_status  = sanitize_text_field( $event->resource->status );
        $order          = wc_get_order( $order_id );

        // validate refund status
        if ( 'COMPLETED' !== $refund_status ) {
            return;
        }

        // get order object
        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Invalid Order id: ' . $order_id );
            status_header( 400 );
            exit();
        }

        // checked order happened with this
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        // check refund already processed
        $refund_ids = OrderManager::get_refund_ids_by_order( $order );

        if ( in_array( $refund_id, $refund_ids, true ) ) {
            return; // refund already process, so we are skipping from here
        }

        //store refund id as array, this will help track all partial refunds
        $refund_ids[] = $refund_id;
        update_post_meta( $order->get_id(), '_dokan_paypal_refund_id', $refund_ids );

        // get seller payable data
        $seller_payable_breakdown = $event->resource->seller_payable_breakdown;

        // insert new refund request
        $args = [
            'order_id'        => $order->get_id(),
            'seller_id'       => dokan_get_seller_id_by_order( $order->get_id() ),
            'refund_amount'   => (float) $seller_payable_breakdown->gross_amount->value,
            'refund_reason'   => ! empty( $event->resource->note_to_payer ) ? sanitize_text_field( $event->resource->note_to_payer ) : __( 'Refunded Via PayPal', 'dokan' ),
        ];
        $refund = $this->create_refund( $args );

        if ( is_wp_error( $refund ) ) {
            dokan_log( 'Dokan PayPal Marketplace Refund: ' . $refund->get_error_message() );
            return;
        }

        $order->add_order_note(
        /* translators: %s: paypal refund id */
            sprintf( __( 'Refund Processed Via PayPal Dashboard ( Refund ID: %1$s )', 'dokan' ), $refund_id )
        );

        // Try to approve the refund.
        $args = [
            'paypal_marketplace'    => true,
            'refund_amount'         => (float) $seller_payable_breakdown->gross_amount->value,
            'net_refund_amount'     => (float) $seller_payable_breakdown->net_amount->value,
            'reversed_admin_fee'    => (float) $seller_payable_breakdown->platform_fees[0]->amount->value,
            'reversed_gateway_fee'  => (float) $seller_payable_breakdown->paypal_fee->value,
            'total_refunded_amount' => (float) $seller_payable_breakdown->total_refunded_amount->value,
            'paypal_refund_id'      => $refund_id,
        ];
        try {
            $refund = $refund->approve( $args );
            if ( is_wp_error( $refund ) ) {
                dokan_log( 'Dokan PayPal Marketplace Refund: ' . $refund->get_error_message(), 'error' );
                return;
            }
        } catch ( \Exception $e ) {
            dokan_log( 'Dokan PayPal Marketplace Refund: ' . $e->getMessage(), 'error' );
            return;
        }
    }

    /**
     * @param array $args
     *
     * @since 3.3.0
     *
     * @return WP_Error|Refund
     */
    private function create_refund( $args = [] ) {
        global $wpdb;

        $default_args = [
            'order_id'        => 0,
            'seller_id'       => 0,
            'refund_amount'   => 0,
            'refund_reason'   => '',
            'item_qtys'       => [],
            'item_totals'     => [],
            'item_tax_totals' => [],
            'restock_items'   => null,
            'date'            => current_time( 'mysql' ),
            'status'          => 0,
            'method'          => 'false',
        ];

        $args = wp_parse_args( $args, $default_args );

        $inserted = $wpdb->insert(
            $wpdb->dokan_refund,
            $args,
            [ '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
        );

        if ( $inserted !== 1 ) {
            return new WP_Error( 'dokan_refund_create_error', __( 'Could not create new refund', 'dokan' ) );
        }

        $refund = dokan_pro()->refund->get( $wpdb->insert_id );

        return $refund;
    }
}
