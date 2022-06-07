<?php
namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Cart;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CartManager
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\Cart
 *
 * @since 3.3.0
 */
class CartManager {

    /**
     * Make paypal sdk url based on settings
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_paypal_sdk_url() {
        $client_id         = Helper::get_client_id();
        $paypal_js_sdk_url = 'https://www.paypal.com/sdk/js?';

        //add hosted fields component if ucc mode is enabled
        if ( static::is_ucc_enabled_for_all_seller_in_cart() ) {
            $paypal_js_sdk_url .= 'components=hosted-fields,buttons&';
        }

        $currency = get_woocommerce_currency();
        $paypal_js_sdk_url .= "client-id={$client_id}&currency={$currency}&intent=capture";

        return esc_url_raw( $paypal_js_sdk_url );
    }

    /**
     * Check if ucc mode is enabled for all seller in the cart
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_ucc_enabled_for_all_seller_in_cart() {
        $ucc_enabled = Helper::is_ucc_enabled();

        if ( ! $ucc_enabled || ! is_object( WC()->cart ) ) {
            return false;
        }

        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id = $item['data']->get_id();

            $seller_id = get_post_field( 'post_author', $product_id );

            if ( ! get_user_meta( $seller_id, Helper::get_seller_enable_for_ucc_key(), true ) ) {
                return false;
            }
        }

        return true;
    }
}
