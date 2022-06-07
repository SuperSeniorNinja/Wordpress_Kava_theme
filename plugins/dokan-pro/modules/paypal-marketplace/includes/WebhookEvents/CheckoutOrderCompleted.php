<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Order\OrderManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CheckoutOrderCompleted
 * @package WeDevs\Dokan\Gateways\PayPal\WebhookEvents
 *
 * @since 3.3.0
 *
 * @author weDevs
 */
class CheckoutOrderCompleted extends WebhookEventHandler {

    /**
     * CheckoutOrderCompleted constructor.
     *
     * @param $event
     *
     * @since 3.3.0
     */
    public function __construct( $event ) {
        $this->set_event( $event );
    }

    /**
     * Handle checkout completed order
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle() {
        $event           = $this->get_event();
        $paypal_order_id = sanitize_text_field( $event->resource->id );
        $order_id        = $event->resource->purchase_units[0]->invoice_id;
        $order           = wc_get_order( $order_id );

        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook: CheckoutOrderCompleted, Invalid Order id: ' . $order_id );
            return;
        }

        // check payment gateway used was dokan paypal marketplace
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        // check if order is already processed
        if ( OrderManager::is_charge_captured( $order ) ) {
            return;
        }

        //add capture id to meta data (converting it to array because store_capture_payment_data allows array data of purchase units)
        $purchase_units = json_decode( wp_json_encode( $event->resource->purchase_units ), true );

        //process order data
        OrderManager::handle_order_complete_status( $purchase_units, $paypal_order_id );

        // validate order status as completed, (COMPLETED = The payment was authorized or the authorized payment was captured for the order. )
        if (
            isset( $event->resource->intent, $event->resource->status ) &&
            'CAPTURE' === $event->resource->intent &&
            'COMPLETED' === $event->resource->status
        ) {
            // make paymet completed
            $order->payment_complete();

            /**
             * @args WC_Order $order Main Order ID
             * @args array $purchase_units
             * $args string $paypal_order_id
             */
            do_action( 'dokan_paypal_capture_payment_completed', $order, $purchase_units, $paypal_order_id );
        }
    }
}
