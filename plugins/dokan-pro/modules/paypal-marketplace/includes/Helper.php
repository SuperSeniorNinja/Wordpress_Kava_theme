<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace;

use DokanPro\Modules\Subscription\SubscriptionPack;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Subscriptions\Processor as SubscriptionProcessor;
use WC_Abstract_Order;
use WC_Product;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Helper
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace
 *
 * @since 3.3.0
 */
class Helper {

    /**
     * Get PayPal gateway id
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_gateway_id() {
        // do not change this value ever, otherwise this will cause inconsistancy while retrieving data
        return 'dokan_paypal_marketplace';
    }

    /**
     * Get settings of the gateway
     *
     * @param null $key
     *
     * @since 3.3.0
     *
     * @return mixed|void
     */
    public static function get_settings( $key = null ) {
        $settings = get_option( 'woocommerce_' . static::get_gateway_id() . '_settings', [] );

        if ( $key && isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }

        return $settings;
    }

    /**
     * Check whether it's enabled or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function get_gateway_title() {
        $settings = static::get_settings();

        return ! empty( $settings['title'] ) ? $settings['title'] : __( 'PayPal Marketplace', 'dokan' );
    }

    /**
     * Get BN Code
     *
     * @since 3.3.3
     *
     * @return bool
     */
    public static function get_bn_code() {
        $settings = static::get_settings();

        return ! empty( $settings['bn_code'] ) ? sanitize_text_field( $settings['bn_code'] ) : 'weDevs_SP_Dokan';
    }

    /**
     * Check whether it's enabled or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = static::get_settings();

        return ! empty( $settings['enabled'] ) && 'yes' === $settings['enabled'];
    }

    /**
     * Check if this gateway is enabled and ready to use
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_ready() {
        if ( ! static::is_enabled() ||
            empty( static::get_partner_id() ) ||
            empty( static::get_client_id() ) ||
            empty( static::get_client_secret() ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check if this gateway is enabled and ready to use
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_api_ready() {
        if (
            empty( static::get_client_id() ) ||
            empty( static::get_client_secret() ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the seller is enabled for receive paypal payment
     *
     * @param $seller_id
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_seller_enable_for_receive_payment( $seller_id ) {
        return static::get_seller_merchant_id( $seller_id ) && static::get_seller_enabled_for_received_payment( $seller_id );
    }

    /**
     * Check whether the gateway in test mode or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_test_mode() {
        $settings = static::get_settings();

        return ! empty( $settings['test_mode'] ) && 'yes' === $settings['test_mode'];
    }

    /**
     * Check whether the test mode is enabled or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_debug_log_enabled() {
        $settings = static::get_settings();

        return ! empty( $settings['debug'] ) && 'yes' === $settings['debug'];
    }

    /**
     * Check whether Unbranded Credit Card mode is enabled or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_ucc_mode_allowed() {
        $settings = static::get_settings();

        return ! empty( $settings['ucc_mode'] ) && 'yes' === $settings['ucc_mode'];
    }

    /**
     * Unbranded credit card mode is allowed or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function is_ucc_enabled() {
        $wc_base_country = WC()->countries->get_base_country();

        if (
            'smart' === static::get_button_type() &&
            static::is_ucc_mode_allowed() &&
            array_key_exists( $wc_base_country, static::get_advanced_credit_card_debit_card_supported_countries() ) &&
            in_array( get_woocommerce_currency(), static::get_advanced_credit_card_debit_card_supported_currencies( $wc_base_country ), true )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get advanced credit card debit card supported countries (UCC/Unbranded payments)
     *
     * @see https://developer.paypal.com/docs/business/checkout/reference/currency-availability-advanced-cards/
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_advanced_credit_card_debit_card_supported_countries() {
        $supported_countries = [
            'AU' => 'Australia',
            'CA' => 'Canada',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'US' => 'United States',
            'GB' => 'United Kingdom',
        ];

        return apply_filters( 'dokan_paypal_advanced_credit_card_debit_card_supported_countries', $supported_countries );
    }

    /**
     * Get branded payment supported countries
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_branded_payment_supported_countries() {
        $supported_countries = [
            'AD' => 'Andorra',
            'AR' => 'Argentina',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BM' => 'Bermuda',
            'BW' => 'Botswana',
            'BR' => 'Brazil',
            'KY' => 'Cayman Islands',
            'CL' => 'Chile',
            'C2' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'SV' => 'El Salvador',
            'FO' => 'Faroe Islands',
            'GF' => 'French Guiana',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GP' => 'Guadeloupe',
            'GT' => 'Guatemala',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR China',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KW' => 'Kuwait',
            'LS' => 'Lesotho',
            'MY' => 'Malaysia',
            'MQ' => 'Martinique',
            'MU' => 'Mauritius',
            'MX' => 'Mexico',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RU' => 'Russia',
            'SM' => 'San Marino',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SG' => 'Singapore',
            'ZA' => 'South Africa',
            'KR' => 'South Korea',
            'CH' => 'Switzerland',
            'TW' => 'Taiwan',
            'AE' => 'United Arab Emirates',
            'UY' => 'Uruguay',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'BG' => 'Bulgaria',
            'CA' => 'Canada',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'EE' => 'Estonia',
            'FI' => 'Finland',
            'FR' => 'France',
            'GR' => 'Greece',
            'HU' => 'Hungary',
            'IT' => 'Italy',
            'LV' => 'Latvia',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MT' => 'Malta',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'ES' => 'Spain',
            'SE' => 'Sweden',
            'GB' => 'United Kingdom',
            'US' => 'United States',
        ];

        return apply_filters( 'dokan_paypal_branded_payment_supported_countries', $supported_countries );
    }

    /**
     * Get Paypal supported currencies except US
     * for advanced credit card debit card
     *
     * @see https://developer.paypal.com/docs/business/checkout/reference/currency-availability-advanced-cards/
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_advanced_credit_card_debit_card_non_us_supported_currencies() {
        return apply_filters(
            'dokan_paypal_supported_currencies', [
                'AUD',
                'CAD',
                'CHF',
                'CZK',
                'DKK',
                'EUR',
                'GBP',
                'HKD',
                'HUF',
                'JPY',
                'NOK',
                'NZD',
                'PLN',
                'SEK',
                'SGD',
                'USD',
            ]
        );
    }

    /**
     * Get US supported currencies for advanced credit card debit card
     *
     * @see https://developer.paypal.com/docs/business/checkout/reference/currency-availability-advanced-cards/
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_advanced_credit_card_debit_card_us_supported_currencies() {
        return apply_filters(
            'dokan_paypal_us_supported_currencies', [
                'AUD',
                'CAD',
                'EUR',
                'GBP',
                'JPY',
                'USD',
            ]
        );
    }

    /**
     *
     * @see https://developer.paypal.com/docs/platforms/develop/currency-codes/
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_supported_currencies() {
        $supported_currencies = [
            'AUD' => __( 'Australian dollar', 'dokan' ),
            'BRL' => __( 'Brazilian real', 'dokan' ),
            'CAD' => __( 'Canadian dollar', 'dokan' ),
            'CZK' => __( 'Czech koruna', 'dokan' ),
            'DKK' => __( 'Danish krone', 'dokan' ),
            'EUR' => __( 'Euro', 'dokan' ),
            'HKD' => __( 'Hong Kong dollar', 'dokan' ),
            'HUF' => __( 'Hungarian forint', 'dokan' ),
            'ILS' => __( 'Israeli new shekel', 'dokan' ),
            'JPY' => __( 'Japanese yen', 'dokan' ),
            'MYR' => __( 'Malaysian ringgit', 'dokan' ),
            'MXN' => __( 'Mexican peso', 'dokan' ),
            'TWD' => __( 'New Taiwan dollar', 'dokan' ),
            'NZD' => __( 'New Zealand dollar', 'dokan' ),
            'NOK' => __( 'Norwegian krone', 'dokan' ),
            'PHP' => __( 'Philippine peso', 'dokan' ),
            'PLN' => __( 'Polish złoty', 'dokan' ),
            'GBP' => __( 'Pound sterling', 'dokan' ),
            'RUB' => __( 'Russian ruble', 'dokan' ),
            'SGD' => __( 'Singapore dollar', 'dokan' ),
            'SEK' => __( 'Swedish krona', 'dokan' ),
            'CHF' => __( 'Swiss franc', 'dokan' ),
            'THB' => __( 'Thai baht', 'dokan' ),
            'USD' => __( 'United States dollar', 'dokan' ),
        ];

        return apply_filters( 'dokan_paypal_supported_currencies', $supported_currencies );
    }

    /**
     * Get advanced credit card debit card supported currencies
     *
     * @see https://developer.paypal.com/docs/business/checkout/reference/currency-availability-advanced-cards/
     *
     * @param $country_code
     *
     * @since 3.3.0
     *
     * @return array|bool
     */
    public static function get_advanced_credit_card_debit_card_supported_currencies( $country_code ) {
        $supported_countries = static::get_advanced_credit_card_debit_card_supported_countries();

        if ( ! array_key_exists( $country_code, $supported_countries ) ) {
            return false;
        }

        if ( 'US' === $country_code ) {
            return static::get_advanced_credit_card_debit_card_us_supported_currencies();
        }

        return static::get_advanced_credit_card_debit_card_non_us_supported_currencies();
    }

    /**
     * Get PayPal product type based on country
     *
     * @param $country_code
     *
     * @since 3.3.0
     *
     * @return bool|string
     */
    public static function get_product_type( $country_code ) {
        $ucc_supported_countries        = static::get_advanced_credit_card_debit_card_supported_countries();
        $branded_supported_countries    = static::get_branded_payment_supported_countries();

        if ( ! array_key_exists( $country_code, array_merge( $ucc_supported_countries, $branded_supported_countries ) ) ) {
            return false;
        }

        if ( array_key_exists( $country_code, $ucc_supported_countries ) ) {
            return 'PPCP';
        }

        if ( array_key_exists( $country_code, $branded_supported_countries ) ) {
            return 'EXPRESS_CHECKOUT';
        }
    }

    /**
     * @since 3.3.0
     * @param bool|null $test_mode
     * @return string
     */
    public static function get_seller_merchant_id_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_paypal_test_merchant_id' : '_dokan_paypal_merchant_id';
    }

    /**
     * @since 3.3.0
     * @param bool|null $test_mode
     * @return string
     */
    public static function get_seller_enabled_for_received_payment_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_paypal_test_enable_for_receive_payment' : '_dokan_paypal_enable_for_receive_payment';
    }

    /**
     * @since 3.3.0
     * @param bool|null $test_mode
     * @return string
     */
    public static function get_seller_marketplace_settings_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_paypal_test_marketplace_settings' : '_dokan_paypal_marketplace_settings';
    }

    /**
     * @since 3.3.0
     * @param bool|null $test_mode
     * @return string
     */
    public static function get_seller_payments_receivable_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_paypal_test_payments_receivable' : '_dokan_paypal_payments_receivable';
    }

    /**
     * @since 3.3.0
     * @param bool|null $test_mode
     * @return string
     */
    public static function get_seller_primary_email_confirmed_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_paypal_test_primary_email_confirmed' : '_dokan_paypal_primary_email_confirmed';
    }

    /**
     * @since 3.3.0
     * @param bool|null $test_mode
     * @return string
     */
    public static function get_seller_enable_for_ucc_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_paypal_test_enable_for_ucc' : '_dokan_paypal_enable_for_ucc';
    }

    /**
     *
     * @since 3.3.0
     * @param int $seller_id
     * @return string
     */
    public static function get_seller_merchant_id( $seller_id ) {
        return get_user_meta( $seller_id, static::get_seller_merchant_id_key(), true );
    }

    /**
     *
     * @since 3.3.0
     * @param int $seller_id
     * @return string
     */
    public static function get_seller_enabled_for_received_payment( $seller_id ) {
        return get_user_meta( $seller_id, static::get_seller_enabled_for_received_payment_key(), true );
    }

    /**
     * Log PayPal error data with debug id
     *
     * @param int $id
     * @param WP_Error $error
     * @param string $meta_key
     *
     * @param string $context
     *
     * @since 3.3.0
     *
     * @return void
     */
    public static function log_paypal_error( $id, $error, $meta_key, $context = 'post' ) {
        $error_data = $error->get_error_data();

        //store paypal debug id
        if ( isset( $error_data['paypal_debug_id'] ) ) {
            switch ( $context ) {
                case 'post':
                    update_post_meta( $id, "_dokan_paypal_{$meta_key}_debug_id", $error_data['paypal_debug_id'] );
                    break;

                case 'user':
                    update_user_meta( $id, "_dokan_paypal_{$meta_key}_debug_id", $error_data['paypal_debug_id'] );
                    break;
            }
        }

        dokan_log( "[Dokan PayPal Marketplace] $meta_key Error:\n" . print_r( $error, true ), 'error' );
    }

    /**
     * Get user id by merchant id
     *
     * @param $merchant_id
     *
     * @since 3.3.0
     *
     * @return int
     */
    public static function get_user_id_by_merchant_id( $merchant_id ) {
        global $wpdb;

        $user_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = %s AND `meta_value`= %s",
                static::get_seller_merchant_id_key(),
                $merchant_id
            )
        );

        return absint( $user_id );
    }

    /**
     * Get Percentage of from a price
     *
     * @param $price
     * @param $extra_amount
     *
     * @since 3.3.0
     *
     * @return float|int
     */
    public static function get_percentage( $price, $extra_amount ) {
        $percentage = ( $extra_amount * 100 ) / $price;

        return $percentage;
    }

    /**
     * Get list of supported webhook events
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_supported_webhook_events() {
        return apply_filters(
            'dokan_paypal_supported_webhook_events', [
                'MERCHANT.ONBOARDING.COMPLETED'       => 'MerchantOnboardingCompleted',
                'CUSTOMER.MERCHANT-INTEGRATION.CAPABILITY-UPDATED' => 'CustomerMerchantIntegrationCapabilityUpdated',
                'CUSTOMER.MERCHANT-INTEGRATION.SELLER-EMAIL-CONFIRMED' => 'CustomerMerchantIntegrationSellerEmailConfirmed',
                'MERCHANT.PARTNER-CONSENT.REVOKED'    => 'MerchantPartnerConsentRevoked',
                'CHECKOUT.ORDER.APPROVED'             => 'CheckoutOrderApproved',
                'CHECKOUT.ORDER.COMPLETED'            => 'CheckoutOrderCompleted',
                'PAYMENT.REFERENCED-PAYOUT-ITEM.COMPLETED' => 'PaymentReferencedPayoutItemCompleted',
                'PAYMENT.CAPTURE.REFUNDED'            => 'PaymentCaptureRefunded',
                'PAYMENT.CAPTURE.REVERSED'            => 'PaymentCaptureRefunded',
                'BILLING.SUBSCRIPTION.ACTIVATED'      => 'BillingSubscriptionActivated',
                'BILLING.SUBSCRIPTION.RE-ACTIVATED'   => 'BillingSubscriptionReActivated',
                'BILLING.SUBSCRIPTION.SUSPENDED'      => 'BillingSubscriptionSuspended',
                'BILLING.SUBSCRIPTION.CANCELLED'      => 'BillingSubscriptionCancelled',
                'BILLING.SUBSCRIPTION.EXPIRED'        => 'BillingSubscriptionCancelled',
                'BILLING.SUBSCRIPTION.PAYMENT.FAILED' => 'BillingSubscriptionPaymentFailed',
                'PAYMENT.SALE.COMPLETED'              => 'PaymentSaleCompleted',
            ]
        );
    }

    /**
     * Get webhook events for notification
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_webhook_events_for_notification() {
        $events = array_keys( static::get_supported_webhook_events() );

        return array_map(
            function ( $event ) {
                return [ 'name' => $event ];
            }, $events
        );
    }

    /**
     * Get PayPal client id
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_client_id() {
        $key      = static::is_test_mode() ? 'test_app_user' : 'app_user';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get PayPal client secret key
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_client_secret() {
        $key      = static::is_test_mode() ? 'test_app_pass' : 'app_pass';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get Paypal partner id
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_partner_id() {
        $key      = 'partner_id';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get client id
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_button_type() {
        $key      = 'button_type';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get Cart item quantity exceeded error message
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_max_quantity_error_message() {
        $key      = 'max_error';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get Payment Action (capture or authorize)
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_disbursement_mode() {
        $key      = 'disbursement_mode';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : 'INSTANT';
    }

    /**
     * Get disbersement delay period
     *
     * @since 3.3.0
     *
     * @return int
     */
    public static function get_disbursement_delay_period() {
        $key      = 'disbursement_delay_period';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? (int) $settings[ $key ] : 0;
    }

    /**
     * Get marketplace logo url
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_marketplace_logo() {
        $key      = 'marketplace_logo';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? esc_url_raw( $settings[ $key ] ) : esc_url_raw( esc_url_raw( DOKAN_PLUGIN_ASSEST . '/images/dokan-logo.png' ) );
    }

    /**
     * Check if non-connected sellers sees notice on their dashboard to connect their PayPal account
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function display_notice_on_vendor_dashboard() {
        $key      = 'display_notice_on_vendor_dashboard';
        $settings = self::get_settings();

        return ! empty( $settings[ $key ] ) && 'yes' === $settings[ $key ];
    }

    /**
     * Check if non-connected sellers gets announcement to connect their PayPal account
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function display_announcement_to_non_connected_sellers() {
        $key      = 'display_notice_to_non_connected_sellers';
        $settings = self::get_settings();

        return ! empty( $settings[ $key ] ) && 'yes' === $settings[ $key ];
    }

    /**
     * Get Connect announcement interval
     *
     * @since DOKAN_PRO_SiNCE
     *
     * @return int
     */
    public static function non_connected_sellers_display_notice_intervals() {
        $key      = 'display_notice_interval';
        $settings = self::get_settings();

        return ! empty( $settings[ $key ] ) ? absint( $settings[ $key ] ) : 7;
    }

    /**
     * Get webhook key
     *
     * @since 3.3.0
     *
     * @return string
     */
    public static function get_webhook_key() {
        return static::is_test_mode() ? 'dokan_paypal_marketplace_test_webhook' : 'dokan_paypal_marketplace_webhook';
    }

    /**
     * Get human readable error message
     *
     * @since 3.3.0
     * @param WP_Error $error
     * @return mixed|string
     */
    public static function get_error_message( WP_Error $error ) {
        $error_message = $error->get_error_message();
        if ( is_array( $error_message ) && isset( $error_message['details'][0]['description'] ) ) {
            $messages = '';
            foreach ( $error_message['details'] as $detail ) {
                if ( isset( $detail['field'] ) && isset( $detail['issue'] ) && isset( $detail['description'] ) ) {
                    $messages .= sprintf( '<p><strong>%s:</strong> <em>%s</em>, <strong>%s</strong></p>', $detail['issue'], $detail['field'], $detail['description'] );
                }
            }
            $error_message = $messages;
        } elseif ( is_array( $error_message ) && isset( $error_message['details']['description'] ) ) {
            $error_message = $error_message['details']['description'];
        } elseif ( is_array( $error_message ) && isset( $error_message['message'] ) ) {
            $error_message = $error_message['message'];
        }
        return $error_message;
    }

    /**
     * Include module template
     *
     * @since 3.3.0
     *
     * @param string $name template file name
     * @param array  $args
     *
     * @return void
     */
    public static function get_template( $name, $args = [] ) {
        //todo: sanitize file name
        dokan_get_template( "$name.php", $args, '', trailingslashit( DOKAN_PAYPAL_MP_TEMPLATE_PATH ) );
    }

    /**
     * Check whether subscription module is enabled or not
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public static function has_vendor_subscription_module() {
        // don't confused with product_subscription, id for vendor subscription module is product_subscription
        return function_exists( 'dokan_pro' ) && dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Check if the order is a subscription order
     *
     * @param WC_Product|int $product
     *
     * @since 3.3.0
     *
     * @return bool
     **/
    public static function is_vendor_subscription_product( $product ) {
        if ( is_int( $product ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof WC_Product ) {
            return false;
        }

        if ( ! self::has_vendor_subscription_module() ) {
            return false;
        }

        if ( 'product_pack' === $product->get_type() ) {
            return true;
        }

        return false;
    }

    /**
     * Get vendor id by subscriptoin id
     *
     * @since 3.3.0
     *
     * @param string $subscription_id
     *
     * @return int
     */
    public static function get_vendor_id_by_subscription( $subscription_id ) {
        global $wpdb;

        $vendor_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = %s AND `meta_value`= %s LIMIT 1",
                '_dokan_paypal_marketplace_vendor_subscription_id',
                $subscription_id
            )
        );

        return absint( $vendor_id );
    }

    /**
     * Create product request for Paypal
     *
     * @param int|WC_Product $product_pack
     *
     * @since 3.3.7
     *
     * @return string|\WP_Error
     */
    public static function create_product_in_paypal( $product_pack ) {
        if ( ! is_a( $product_pack, 'WC_Product' ) ) {
            $product_pack = wc_get_product( $product_pack );
        }

        if ( ! $product_pack ) {
            return new WP_Error( 'invalid_product', __( 'No valid subscription product found to process.', 'dokan' ) );
        }

        $subscription_description = 'Dokan Vendor Subscription Product: ' . $product_pack->get_title() . ' #' . $product_pack->get_id();
        // product params
        $product_data = [
            'name'        => substr( trim( $product_pack->get_title() ), 0, 127 ), // max length is 127 characters
            'description' => substr( trim( $subscription_description ), 0, 256 ), // max length is 256 characters
            'type'        => 'SERVICE',
            'category'    => 'SERVICES',
        ];

        $paypal_processor = SubscriptionProcessor::init();
        $paypal_product   = $paypal_processor->create_product( $product_data );

        if ( is_wp_error( $paypal_product ) ) {
            static::log_paypal_error( $product_pack->get_id(), $paypal_product, 'subscription_product_create' );
            return $paypal_product;
        }

        //store product id for later use
        update_post_meta( $product_pack->get_id(), '_dokan_paypal_marketplace_subscription_product_id', $paypal_product['id'] );

        return $paypal_product['id'];
    }

    /**
     * Create product plan request for Paypal
     *
     * @param int|WC_Product $product
     * @param int|\WC_Order|null $order
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#plans_create
     *
     * @since 3.3.7
     *
     * @return void|\WP_Error
     */
    public static function create_plan_in_paypal( $product, $order = null ) {
        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product ) {
            return new WP_Error( 'invalid_product', __( 'No valid subscription product found to process.', 'dokan' ) );
        }

        // get paypal subscription product id
        $paypal_product_id = get_post_meta( $product->get_id(), '_dokan_paypal_marketplace_subscription_product_id', true );

        // check if we've got a valid product id
        if ( empty( $paypal_product_id ) ) {
            $paypal_product_id = static::create_product_in_paypal( $product );
            if ( is_wp_error( $paypal_product_id ) ) {
                return $paypal_product_id;
            }
        }

        // create plan data
        $plan_description   = 'Dokan Vendor Subscription: ' . $product->get_title() . ' #' . $product->get_id() . ' plan';
        $billing_cycles     = static::get_billing_cycles( $product->get_id(), $order );
        if ( is_wp_error( $billing_cycles ) ) {
            return $billing_cycles;
        }

        $plan_data = [
            'product_id'          => $paypal_product_id,
            'name'                => substr( trim( $product->get_title() ), 0, 127 ), // max length is 127 characters,
            'description'         => substr( trim( $plan_description ), 0, 127 ), // max length is 127 characters,,
            'status'              => 'ACTIVE',
            'billing_cycles'      => $billing_cycles,
            'payment_preferences' => [
                'auto_bill_outstanding'     => true,
                'setup_fee'                 => [
                    'value'         => '0',
                    'currency_code' => get_woocommerce_currency(),
                ],
                'setup_fee_failure_action'  => 'CANCEL',
                'payment_failure_threshold' => 1,
            ],
            'taxes'               => [
                'percentage' => '0',
                'inclusive'  => wc_prices_include_tax(),
            ],
        ];

        if ( $order && $order->get_total_tax() ) {
            $percentage = static::get_percentage( $order->get_subtotal(), $order->get_total_tax() );

            $plan_data['taxes'] = [
                'percentage' => $percentage, //paypal only support percentage
                'inclusive'  => true,
            ];
        }

        // finally create subscription plan
        $processor    = SubscriptionProcessor::init();
        $created_plan = $processor->create_plan( $plan_data );

        if ( is_wp_error( $created_plan ) ) {
            static::log_paypal_error( $product->get_id(), $created_plan, 'subscription_plan_create' );
            return $created_plan;
        }

        //store plan id for later use
        if ( $order ) {
            $order->update_meta_data( '_dokan_paypal_marketplace_subscription_plan_id', $created_plan['id'] );
            $order->save_meta_data();
        }

        return $created_plan['id'];
    }

    /**
     * Get paypal formatted billing cycles from product id
     *
     * @param int $product_id
     * @param \WC_Order|int $order
     * @param int|null $vendor_id
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#definition-billing_cycle
     *
     * @since 3.3.7
     *
     * @return WP_Error|array
     */
    protected static function get_billing_cycles( $product_id, $order, $vendor_id = null ) {
        // try getting order object
        if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return new WP_Error( 'invalid-order', __( 'Can not create subscription plan under PayPal due to invalid order.', 'dokan' ) );
        }

        // try getting vendor id
        if ( null === $vendor_id && $order ) {
            $vendor_id = $order->get_customer_id();
        }

        // get SubscriptionPack object
        $subscription = new SubscriptionPack( $product_id, $vendor_id );

        $vendor_used_trial = false;
        // if vendor already has used a trial pack, create a new plan without trial period
        if ( ! empty( $vendor_id ) && SubscriptionHelper::has_used_trial_pack( $vendor_id ) ) {
            $vendor_used_trial = true;
        }

        if ( ! $subscription->is_recurring() ) {
            return [];
        }

        $billing_cycles   = [];
        $billing_sequence = 1;

        //maybe add trial billing details
        if ( $subscription->is_trial() && ! $vendor_used_trial ) {
            $trial_interval_unit  = $subscription->get_trial_period_types(); //day, week, month, year
            $trial_interval_count = absint( $subscription->get_trial_range() ); //int
            $trial_interval_count = static::validate_subscription_frequency( $trial_interval_unit, $trial_interval_count );

            $billing_cycles[] = static::format_subscription_billing_cycle_data(
                [
                    'interval_unit'  => $trial_interval_unit,
                    'interval_count' => $trial_interval_count,
                    'sequence'       => $billing_sequence++,
                    'tenure_type'    => 'TRIAL',
                    'price'          => 0,
                    'total_cycles'   => 1,
                ]
            );
        }

        $product_pack                = $subscription->get_product();
        $subscription_total_cycles   = $subscription->get_period_length(); // Billing cycle stop, 0 (zero) for never stops
        $subscription_interval_unit  = $subscription->get_period_type(); // interval_unit: day, week, month, year
        $subscription_interval_count = $subscription->get_recurring_interval(); // interval_count: int
        $subscription_interval_count = static::validate_subscription_frequency( $subscription_interval_unit, $subscription_interval_count );

        //discount added?
        if ( $order && $order->get_discount_total() ) {
            // seller used discount, discounted amount will apply as trial into trial
            $billing_cycles[] = static::format_subscription_billing_cycle_data(
                [
                    'interval_unit'  => $subscription_interval_unit,
                    'interval_count' => $subscription_interval_count,
                    'sequence'       => $billing_sequence++,
                    'tenure_type'    => 'TRIAL',
                    'price'          => $order->get_total(),
                    'total_cycles'   => 1,
                ]
            );
        }

        // for regular billing cycle
        $order_items = $order->get_items( 'line_item' );
        /**
         * @var \WC_Order_Item_Product $order_item
         */
        $order_item = current( $order_items );

        if ( empty( $order_item ) ) {
            return new WP_Error( 'invalid-order-iten', __( 'Invalid Order Item Found.', 'dokan' ) );
        }

        // calculate price from order item, this is because discount will not apply on recurring payment
        $price = $order_item->get_subtotal( 'edit' ) + $order_item->get_subtotal_tax( 'edit' );
        $billing_cycles[] = static::format_subscription_billing_cycle_data(
            [
                'interval_unit'  => $subscription_interval_unit,
                'interval_count' => $subscription_interval_count,
                'sequence'       => $billing_sequence++,
                'tenure_type'    => 'REGULAR',
                'price'          => $price,
                'total_cycles'   => $subscription_total_cycles,
            ]
        );

        return $billing_cycles;
    }

    /**
     * Format and make a complete billing cycle data
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#definition-billing_cycle
     *
     * @param array $data holds all the necessary param to make the billing cycle data
     *
     * @return array
     */
    public static function format_subscription_billing_cycle_data( $data ) {
        $billing_cycle_data = [
            'frequency'    => [
                'interval_unit'  => isset( $data['interval_unit'] ) ? strtoupper( $data['interval_unit'] ) : 'WEEK',
                'interval_count' => isset( $data['interval_count'] ) ? $data['interval_count'] : 1,
            ],
            'tenure_type'  => isset( $data['tenure_type'] ) ? strtoupper( $data['tenure_type'] ) : 'REGULAR',
            'sequence'     => isset( $data['sequence'] ) ? $data['sequence'] : 1,
            'total_cycles' => isset( $data['total_cycles'] ) ? $data['total_cycles'] : 0,
        ];

        if ( isset( $data['price'] ) ) {
            $billing_cycle_data['pricing_scheme'] = [
                'fixed_price' => [
                    'value'         => wc_format_decimal( $data['price'], 2 ),
                    'currency_code' => isset( $data['currency'] ) ? $data['currency'] : get_woocommerce_currency(),
                ],
            ];
        }

        return $billing_cycle_data;
    }

    /**
     * Frequency validation
     *
     * @see https://developer.paypal.com/docs/api/subscriptions/v1/#definition-frequency
     *
     * @param string $trial_interval_unit
     * @param int $trial_interval_count
     *
     * @since 3.3.7
     *
     * @return int
     */
    public static function validate_subscription_frequency( $trial_interval_unit, $trial_interval_count ) {
        switch ( strtoupper( $trial_interval_unit ) ) {
            case 'DAY':
                if ( $trial_interval_count > 365 ) {
                    $trial_interval_count = 365;
                }
                break;

            case 'WEEK':
                if ( $trial_interval_count > 52 ) {
                    $trial_interval_count = 52;
                }
                break;

            case 'MONTH':
                if ( $trial_interval_count > 12 ) {
                    $trial_interval_count = 12;
                }
                break;

            case 'YEAR':
                if ( $trial_interval_count > 1 ) {
                    $trial_interval_count = 1;
                }
                break;
        }

        return $trial_interval_count;
    }

    /**
     * Set Order status to Cancelled with an order note
     *
     * @param \WC_Order $order
     * @param string $status
     * @param string|null $vendor_id
     *
     * @since 3.3.7
     *
     * @return void
     */
    public static function update_order_status( $order, $status = 'cancelled', $note = null ) {
        if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
            return;
        }

        $note = ! empty( $note ) ? $note : __( 'Subscription Cancelled.', 'dokan' );
        $order->add_order_note( $note );
        $order->set_status( $status );
        $order->save();
    }
}
