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
class PaymentReferencedPayoutItemCompleted extends WebhookEventHandler {

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
        $event          = $this->get_event();
        $order_id       = sanitize_text_field( $event->resource->invoice_id );
        $order          = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        // check payment gateway used was dokan paypal marketplace
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        // check that payment disbursement method wasn't direct
        if ( 'INSTANT' === $order->get_meta( '_dokan_paypal_payment_disbursement_mode' ) ) {
            return;
        }

        // check already disbursed or not
        if ( 'yes' === $order->get_meta( '_dokan_paypal_payment_withdraw_balance_added' ) ) {
            return;
        }

        $withdraw_data = $order->get_meta( '_dokan_paypal_payment_withdraw_data' );
        $response      = OrderManager::insert_vendor_withdraw_balance( $withdraw_data, true );

        if ( is_wp_error( $response ) ) {
            $order->add_order_note(
            // translators: 1) Payment Gateway Title, 2) Error message from gateway
                sprintf( __( '[%1$s] Inserting into vendor balance failed. Error Message: %2$s', 'dokan' ), Helper::get_gateway_title(), Helper::get_error_message( $response ) )
            );
        } else {
            $order->add_order_note(
            // translators: 1) Payment Gateway Title, 2) Error message from gateway
                sprintf( __( '[%1$s] Successfully disbursed fund to the vendor.', 'dokan' ), Helper::get_gateway_title() )
            );
        }
    }
}
