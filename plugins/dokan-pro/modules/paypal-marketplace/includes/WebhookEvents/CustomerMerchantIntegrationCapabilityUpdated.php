<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookEvents;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\WithdrawMethods\WithdrawManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class PaymentSaleCompleted
 * @package WeDevs\DokanPro\Payment\PayPal\WebhookEvents
 *
 * @since 3.3.0
 *
 * @author weDevs
 */
class CustomerMerchantIntegrationCapabilityUpdated extends WebhookEventHandler {

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
     * Handle payment sale
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle() {
        $event       = $this->get_event();
        $merchant_id = sanitize_text_field( $event->resource->merchant_id );
        $user_id     = Helper::get_user_id_by_merchant_id( $merchant_id );

        if ( ! $user_id || ! $merchant_id ) {
            // translators: 1) Gateway id, 2) User ID, 3) PayPal Merchant ID
            dokan_log( sprintf( '[%1$s] Webhook Error: Invalid User (%2$s) or Merchant ID (%3$s )', Helper::get_gateway_title(), $user_id, $merchant_id ) );
            return;
        }

        // update merchant status
        WithdrawManager::update_merchant_status( $merchant_id, $user_id );
    }
}
