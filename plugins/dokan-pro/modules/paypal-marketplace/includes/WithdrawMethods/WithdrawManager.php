<?php
namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WithdrawMethods;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WithdrawManager
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\WithdrawMethods
 *
 * @since 3.3.0
 */
class WithdrawManager {
    /**
     * Handle paypal marketplace success response process
     *
     * @param int $user_id
     *
     * @since 3.3.0
     *
     * @return WP_Error|bool true on success
     */
    public static function handle_connect_success_response( $user_id ) {
        $paypal_settings = get_user_meta( $user_id, Helper::get_seller_marketplace_settings_key(), true );
        $processor       = Processor::init();
        $merchant_data   = $processor->get_merchant_id( $paypal_settings['tracking_id'] );

        if ( is_wp_error( $merchant_data ) ) {
            Helper::log_paypal_error( $user_id, $merchant_data, 'dpm_connect_success_response', 'user' );
            return $merchant_data;
        }

        //storing paypal merchant id
        $merchant_id = $merchant_data['merchant_id'];
        update_user_meta( $user_id, Helper::get_seller_merchant_id_key(), $merchant_id );

        $paypal_settings['connection_status'] = 'success';

        update_user_meta(
            $user_id,
            Helper::get_seller_marketplace_settings_key(),
            $paypal_settings
        );

        //update paypal email in dokan profile settings
        $dokan_settings = get_user_meta( $user_id, 'dokan_profile_settings', true );

        $dokan_settings['payment']['dokan_paypal_marketplace'] = [
            'email' => $paypal_settings['email'],
        ];
        update_user_meta( $user_id, 'dokan_profile_settings', $dokan_settings );

        //validate merchant status for payment receiving
        return static::update_merchant_status( $merchant_id, $user_id );
    }

    /**
     * Validate status of a merchant and store data
     *
     * @param string $merchant_id
     * @param int $user_id
     *
     * @since 3.3.0
     *
     * @return WP_Error|bool true on success
     */
    public static function update_merchant_status( $merchant_id, $user_id = null ) {
        $processor       = Processor::init();
        $merchant_status = $processor->get_merchant_status( $merchant_id );

        if ( empty( $user_id ) ) {
            $user_id = Helper::get_user_id_by_merchant_id( $merchant_id );

            if ( ! $user_id ) {
                return new WP_Error(
                    'invalid_user_id',
                    // translators: PayPal Merchant ID
                    sprintf( __( 'No User found with given Merchant ID: %1$s', 'dokan' ), $merchant_id )
                );
            }
        }

        if ( is_wp_error( $merchant_status ) ) {
            Helper::log_paypal_error( $user_id, $merchant_status, 'dpm_validate_merchant_status', 'user' );
            return $merchant_status;
        }

        update_user_meta( $user_id, Helper::get_seller_enabled_for_received_payment_key(), false );
        update_user_meta( $user_id, Helper::get_seller_payments_receivable_key(), $merchant_status['payments_receivable'] );
        update_user_meta( $user_id, Helper::get_seller_primary_email_confirmed_key(), $merchant_status['primary_email_confirmed'] );

        //check if the user is able to receive payment
        if ( $merchant_status['payments_receivable'] && $merchant_status['primary_email_confirmed'] ) {
            $oauth_integrations = $merchant_status['oauth_integrations'];

            array_map(
                function ( $integration ) use ( $user_id ) {
                    if ( 'OAUTH_THIRD_PARTY' === $integration['integration_type'] && count( $integration['oauth_third_party'] ) ) {
                        update_user_meta( $user_id, Helper::get_seller_enabled_for_received_payment_key(), true );
                    }
                }, $oauth_integrations
            );
        }

        //check if the user is able to use UCC mode
        $products = $merchant_status['products'];

        array_map(
            function ( $value ) use ( $user_id ) {
                if ( 'PPCP_CUSTOM' === $value['name'] && 'SUBSCRIBED' === $value['vetting_status'] ) {
                    update_user_meta( $user_id, Helper::get_seller_enable_for_ucc_key(), true );
                }
            }, $products
        );

        return true;
    }
}
