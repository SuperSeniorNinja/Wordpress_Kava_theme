<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Subscriptions\Processor as SubscriptionProcessor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class BillingSubscriptionSuspended
 *
 * @package WeDevs\DokanPro\Payment\PayPal\WebhookEvents
 *
 * @since 3.3.7
 *
 * @author weDevs
 */
class BillingSubscriptionSuspended extends WebhookEventHandler {

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
        $order_id        = isset( $event->resource->custom_id ) ? absint( $event->resource->custom_id ) : 0;
        $vendor_id       = 0;

        // check if vendor subscription module is active
        if ( ! Helper::has_vendor_subscription_module() ) {
            return;
        }

        if ( ! $order_id ) {
            //get vendor id
            $vendor_id = Helper::get_vendor_id_by_subscription( $subscription_id );
            if ( ! $vendor_id ) {
                return;
            }

            // get order id
            $order_id = get_user_meta( $vendor_id, 'product_order_id', true );
        }

        // validate order
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook: BillingSubscriptionSuspended, Invalid Order id: ' . $order_id ); // maybe deleted order
            return;
        }

        // get vendor id
        $vendor_id = ! empty( $vendor_id ) ? $vendor_id : $order->get_customer_id();

        // check if order is vendor subscription order
        if ( $order->get_meta( '_dokan_vendor_subscription_order' ) !== 'yes' ) {
            return;
        }

        // get product id
        $product_id   = get_user_meta( $order->get_customer_id(), 'product_package_id', true );
        $subscription = new SubscriptionPack( $product_id, $vendor_id );

        if ( $subscription->has_active_cancelled_subscrption() ) {
            return;
        }

        // get subscription information
        $processor = SubscriptionProcessor::init();

        $paypal_subscription = $processor->get_subscription( $subscription_id );
        if ( is_wp_error( $paypal_subscription ) ) {
            return;
        }

        $now = dokan_current_datetime();
        // check if next billing time exists
        if ( ! empty( $paypal_subscription['billing_info']['next_billing_time'] ) ) {
            $next_billing_timestamp = strtotime( $paypal_subscription['billing_info']['next_billing_time'], time() );
            if ( $now->getTimestamp() < $next_billing_timestamp ) {
                $now = $now->setTimestamp( $next_billing_timestamp );
            }
        }

        // store old enddate into another meta
        if ( $subscription->suspend_subscription( $now->format( 'Y-m-d H:i:s' ) ) ) {
            //update order note
            $order->add_order_note( __( 'Subscription Suspended From PayPal Dashboard.', 'dokan' ) );
        }

        //todo: capture outstanding payment @see https://developer.paypal.com/docs/platforms/subscriptions/add-capabilities/payment-failure-retry/
    }
}
