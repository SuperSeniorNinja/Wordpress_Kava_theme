<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Frontend;

/**
 * Class for handling frontend assets
 *
 * @since 3.5.0
 */
class Assets {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
    }

    /**
     * Registers necessary scripts
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function register_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_register_script(
            'dokan-mangopay-kit',
            DOKAN_MANGOPAY_ASSETS . "vendor/mangopay-kit{$suffix}.js",
            array( 'jquery' ),
            DOKAN_PRO_PLUGIN_VERSION,
            true
        );

        wp_register_script(
            'dokan-mangopay-checkout',
            DOKAN_MANGOPAY_ASSETS . "js/checkout{$suffix}.js",
            array( 'dokan-mangopay-kit' ),
            DOKAN_PRO_PLUGIN_VERSION,
            true
        );

        wp_register_style(
            'dokan-mangopay-checkout',
            DOKAN_MANGOPAY_ASSETS . "css/checkout{$suffix}.css",
            array(),
            DOKAN_PRO_PLUGIN_VERSION
        );

        wp_register_script(
            'dokan-mangopay-vendor',
            DOKAN_MANGOPAY_ASSETS . "js/vendor{$suffix}.js",
            array( 'jquery' ),
            DOKAN_PRO_PLUGIN_VERSION,
            true
        );

        wp_register_style(
            'dokan-mangopay-vendor',
            DOKAN_MANGOPAY_ASSETS . "css/vendor{$suffix}.css",
            array(),
            DOKAN_PRO_PLUGIN_VERSION
        );

        wp_localize_script( 'dokan-mangopay-checkout', 'dokanMangopay', array(
            'regErrors'   => $this->card_registration_errors(),
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'emptyFields' => __( 'Please fill all the fields' ),
            'nonce'       => wp_create_nonce( 'dokan_mangopay_checkout_nonce' ),
        ) );

        wp_localize_script( 'dokan-mangopay-vendor', 'dokanMangopay', array(
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'processing'  => __( 'Processing', 'dokan' ),
            'makeActive'  => __( 'Make Active', 'dokan' ),
        ) );
    }

    /**
     * Translate error messages for localization.
     *
     * @since 3.5.0
     *
     * @return array
     */
    private function card_registration_errors() {
        return apply_filters(
            'dokan_mangopay_card_registration_errors',
            array(
                'base_message' => __( 'Card registration error: ', 'dokan' ),
                '009999'       => __( 'Browser does not support making cross-origin Ajax calls', 'dokan' ),
                '001596'       => __( 'An HTTP request was blocked by the User\'s computer (probably due to an antivirus)', 'dokan' ),
                '001597'       => __( 'An HTTP request failed', 'dokan' ),
                '001599'       => __( 'Token processing error', 'dokan' ),
                '101699'       => __( 'Invalid response', 'dokan' ),
                '105204'       => __( 'The CVV is missing or not in the required length/format', 'dokan' ),
                '105203'       => __( 'The expiry date is not valid', 'dokan' ),
                '105202'       => __( 'The card number is not in a valid format', 'dokan' ),
            )
        );
    }
}
