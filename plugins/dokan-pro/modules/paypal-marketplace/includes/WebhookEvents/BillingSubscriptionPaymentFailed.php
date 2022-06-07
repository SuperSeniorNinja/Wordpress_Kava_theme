<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class BillingSubscriptionPaymentFailed
 *
 * @package WeDevs\DokanPro\Payment\PayPal\WebhookEvents
 *
 * @since 3.3.7
 *
 * @author weDevs
 */
class BillingSubscriptionPaymentFailed extends WebhookEventHandler {

    /**
     * CheckoutOrderApproved constructor.
     *
     * @param $event
     *
     * @since 3.3.7
     */
    public function __construct( $event ) {
        $this->set_event( $event );
    }

    /**
     * Handle billing subscription failed
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function handle() {
        $event           = $this->get_event();
        $subscription_id = sanitize_text_field( $event->resource->id );

        // check if vendor subscription module is active
        if ( ! Helper::has_vendor_subscription_module() ) {
            return;
        }

        //get vendor id
        $vendor_id = Helper::get_vendor_id_by_subscription( $subscription_id );
        if ( ! $vendor_id ) {
            return;
        }

        $order_id = get_user_meta( $vendor_id, 'product_order_idproduct_order_id', true );

        // validate order
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook: BillingSubscriptionPaymentFailed, Invalid Order id: ' . $order_id ); // maybe deleted order
            return;
        }

        // check payment gateway used was dokan paypal marketplace
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        // check if order is vendor subscription order
        if ( $order->get_meta( '_dokan_vendor_subscription_order' ) !== 'yes' ) {
            return;
        }

        // make sure subscription id match with stored subscription id
        $order_subscription_id = $order->get_meta( '_dokan_paypal_marketplace_vendor_subscription_id' );
        if ( empty( $order_subscription_id ) || $order_subscription_id !== $subscription_id ) {
            return;
        }

        update_user_meta( $vendor_id, 'can_post_product', '0' );
        //update order status
        //Helper::update_order_status( $order, 'on-hold', __( 'Order status is now on-hold due to failed payment.', 'dokan' ) );
    }
}
