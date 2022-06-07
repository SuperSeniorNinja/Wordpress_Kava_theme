<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class BillingSubscriptionReActivated
 *
 * @package WeDevs\DokanPro\Payment\PayPal\WebhookEvents
 *
 * @since 3.3.7
 *
 * @author weDevs
 */
class BillingSubscriptionReActivated extends WebhookEventHandler {

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

        // get order id
        $order_id = get_user_meta( $vendor_id, 'product_order_id', true );

        // validate order
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook: BillingSubscriptionReActivated, Invalid Order id: ' . $order_id ); // maybe deleted order
            return;
        }

        // check if order is vendor subscription order
        if ( $order->get_meta( '_dokan_vendor_subscription_order' ) !== 'yes' ) {
            return;
        }

        // validate product
        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        if ( ! $product ) {
            return;
        }

        $subscription = new SubscriptionPack( $product->get_id(), $vendor_id );

        // check already activated subscription
        if ( ! $subscription->has_active_cancelled_subscrption() ) {
            return;
        }

        if ( $subscription->reactivate_subscription() ) {
            // update order status
            $order->add_order_note( __( 'Subscription Reactivated.', 'dokan' ) );
        }
    }
}
