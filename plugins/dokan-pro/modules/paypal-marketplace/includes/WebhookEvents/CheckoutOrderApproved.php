<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CheckoutOrderApproved
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents
 *
 * @see @see https://developer.paypal.com/docs/api-basics/notifications/webhooks/event-names/#orders
 *
 * @since 3.3.0
 */
class CheckoutOrderApproved extends WebhookEventHandler {
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
     * Handle checkout approved order
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle() {
        $event           = $this->get_event();
        $paypal_order_id = sanitize_text_field( $event->resource->id );
        $order_id        = sanitize_text_field( $event->resource->purchase_units[0]->invoice_id );
        $order           = wc_get_order( $order_id );

        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook: CheckoutOrderApproved, Invalid Order id: ' . $order_id );
            return;
        }

        // check payment gateway used was dokan paypal marketplace
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        //allow if the order is pending
        if ( $order->has_status( [ 'processing', 'completed' ] ) ) {
            return;
        }

        $processor  = Processor::init();
        $response   = $processor->capture_payment( $paypal_order_id );

        if ( is_wp_error( $response ) ) {
            Helper::log_paypal_error( $order->get_id(), $response, 'capture_payment' );
            return;
        }

        //store paypal debug id
        $order->update_meta_data( '_dokan_paypal_capture_payment_debug_id', $response['paypal_debug_id'] );
        $order->save_meta_data();
    }
}
