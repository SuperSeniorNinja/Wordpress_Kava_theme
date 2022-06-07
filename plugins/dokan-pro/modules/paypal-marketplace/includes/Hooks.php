<?php
namespace WeDevs\DokanPro\Modules\PayPalMarketplace;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * @since 3.5.4
 */
class Hooks {
    /**
     * Class constructor
     */
    public function __construct() {
        add_filter( 'dokan_tax_fee_recipient', [ $this, 'tax_fee_recipient' ], 10, 2 );
        add_filter( 'dokan_shipping_fee_recipient', [ $this, 'shipping_fee_recipient' ], 10, 2 );
    }

    /**
     * For PayPal Marketplace, tax and shipping fee recipient is seller
     *
     * @since 3.5.4
     *
     * @param string $recipient
     * @param int $order_id
     *
     * @return mixed|string
     */
    public function tax_fee_recipient( $recipient, $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return $recipient;
        }

        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return $recipient;
        }

        // check if order is vendor subscription order
        if ( 'yes' === $order->get_meta( '_dokan_vendor_subscription_order' ) ) {
            return 'admin';
        }

        return 'seller';
    }

    /**
     * For PayPal Marketplace, tax and shipping fee recipient is seller
     *
     * @since 3.5.4
     *
     * @param string $recipient
     * @param int $order_id
     *
     * @return mixed|string
     */
    public function shipping_fee_recipient( $recipient, $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return $recipient;
        }

        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return $recipient;
        }

        // check if order is vendor subscription order
        if ( 'yes' === $order->get_meta( '_dokan_vendor_subscription_order' ) ) {
            return 'admin';
        }

        return 'seller';
    }
}
