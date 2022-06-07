<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class RegisterGateways
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\Gateways
 *
 * @since 3.3.0
 */
class RegisterGateways {

    /**
     * RegisterGateways constructor.
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.3.0
     *
     * @return void
     */
    private function hooks() {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
    }

    /**
     * Register payment gateway
     *
     * @since 3.3.0
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways[] = '\WeDevs\DokanPro\Modules\PayPalMarketplace\PaymentMethods\PayPal';

        return $gateways;
    }
}
