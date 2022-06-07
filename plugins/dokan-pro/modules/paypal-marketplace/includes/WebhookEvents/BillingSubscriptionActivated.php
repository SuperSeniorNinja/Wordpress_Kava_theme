<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use DokanPro\Modules\Subscription\SubscriptionPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class BillingSubscriptionActivated
 *
 * @package WeDevs\DokanPro\Payment\PayPal\WebhookEvents
 *
 * @since 3.3.7
 *
 * @author weDevs
 */
class BillingSubscriptionActivated extends WebhookEventHandler {

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
     * @param object $plan_data
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function check_trial_exists( $plan_data ) {
        foreach ( $plan_data as $item ) {
            if ( strtoupper( $item->tenure_type ) === 'TRIAL' ) {
                return true;
            }
        }
        // return false otherwise
        return false;
    }

    /**
     * Handle billing subscription activated
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function handle() {
        $event           = $this->get_event();
        $subscription_id = sanitize_text_field( $event->resource->id );
        $order_id        = sanitize_text_field( $event->resource->custom_id );
        $status          = sanitize_text_field( $event->resource->status );

        // validate subscription status
        if ( 'ACTIVE' !== $status ) {
            return;
        }

        // check if vendor subscription module is active
        if ( ! Helper::has_vendor_subscription_module() ) {
            return;
        }

        // validate order
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook: BillingSubscriptionActivated, Invalid Order id: ' . $order_id ); // maybe deleted order
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

        // validate product
        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        if ( ! $product ) {
            return;
        }

        // make sure subscription id match with stored subscription id
        $order_subscription_id = $order->get_meta( '_dokan_paypal_marketplace_vendor_subscription_id' );
        if ( empty( $order_subscription_id ) || $order_subscription_id !== $subscription_id ) {
            return;
        }

        //get vendor id
        $vendor_id = $order->get_customer_id();

        // get subscription object
        $subscription = new SubscriptionPack( $product->get_id(), $vendor_id );

        // check if we have active cancel subscription
        if ( $subscription->has_active_cancelled_subscrption() && $subscription->reactivate_subscription() ) {
            // update order status
            $order->add_order_note( __( 'Subscription Reactivated.', 'dokan' ) );
            return;
        }

        // get saved subscription id
        $saved_subscription_id = get_user_meta( $vendor_id, '_dokan_paypal_marketplace_vendor_subscription_id', true );

        // check already enabled subscription
        if ( ! empty( $order->get_meta( '_dokan_paypal_payment_capture_id' ) && ! empty( $saved_subscription_id ) && $saved_subscription_id === $subscription_id ) ) {
            return;
        }

        // store subscription id
        update_user_meta( $vendor_id, '_dokan_paypal_marketplace_vendor_subscription_id', $subscription_id );

        // finally activate subscription
        $subscription->activate_subscription( $order );

        // check subscription has trial,
        if ( $subscription->is_trial() && isset( $event->resource->billing_info->cycle_executions ) && $this->check_trial_exists( $event->resource->billing_info->cycle_executions ) ) {
            // translators: 1) PayPal Subscription ID
            $order->add_order_note( sprintf( __( 'Subscription Trial activated. Subscription ID: %s', 'dokan' ), $subscription_id ) );
            // store trial information as user meta
            update_user_meta( $vendor_id, '_dokan_subscription_is_on_trial', 'yes' );

            // store trial period also
            $trial_interval_unit  = $subscription->get_trial_period_types(); //day, week, month, year
            $trial_interval_count = absint( $subscription->get_trial_range() ); //int

            $time = dokan_current_datetime()->modify( "$trial_interval_count $trial_interval_unit" );
            if ( $time ) {
                update_user_meta( $vendor_id, '_dokan_subscription_trial_until', $time->format( 'Y-m-d H:i:s' ) );
            }
        } else {
            // translators: 1) PayPal Subscription ID
            $order->add_order_note( sprintf( __( 'Subscription activated.  Subscription ID: %s', 'dokan' ), $subscription_id ) );
        }
    }
}
