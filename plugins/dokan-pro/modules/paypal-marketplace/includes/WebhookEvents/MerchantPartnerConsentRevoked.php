<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\Dokan\Gateways\PayPal\Abstracts\WebhookEventHandler;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class MerchantPartnerConsentRevoked
 * @package WeDevs\Dokan\Gateways\PayPal\WebhookEvents
 *
 * @since 3.3.0
 *
 * @author weDevs
 */
class MerchantPartnerConsentRevoked extends WebhookEventHandler {

    /**
     * MerchantPartnerConsentRevoked constructor.
     *
     * @param $event
     *
     * @since 3.3.0
     */
    public function __construct( $event ) {
        $this->set_event( $event );
    }

    /**
     * Handle MerchantPartnerConsentRevoked
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle() {
        $event       = $this->get_event();
        $merchant_id = sanitize_text_field( $event->resource->merchant_id );

        $user_id = Helper::get_user_id_by_merchant_id( $merchant_id );

        if ( ! $user_id ) {
            return;
        }

        // delete user metas
        $delete_metas = [
            Helper::get_seller_merchant_id_key(),
            Helper::get_seller_enabled_for_received_payment_key(),
            Helper::get_seller_payments_receivable_key(),
            Helper::get_seller_primary_email_confirmed_key(),
            Helper::get_seller_enable_for_ucc_key(),
            Helper::get_seller_marketplace_settings_key(),
        ];

        foreach ( $delete_metas as $meta_key ) {
            delete_user_meta( $user_id, $meta_key );
        }
    }
}
