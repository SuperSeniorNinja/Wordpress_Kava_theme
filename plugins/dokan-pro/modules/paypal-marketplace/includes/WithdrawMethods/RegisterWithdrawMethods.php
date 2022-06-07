<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\WithdrawMethods;

use WeDevs\DokanPro\Admin\Announcement;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class RegisterWithdrawMethods
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace
 *
 * @since 3.3.0
 */
class RegisterWithdrawMethods {

    /**
     * RegisterWithdrawMethod constructor.
     *
     * @since 3.3.0
     */
    public function __construct() {
        add_filter( 'dokan_withdraw_methods', [ $this, 'register_methods' ] );
        add_action( 'dokan_payment_settings_before_form', [ $this, 'handle_vendor_message' ], 10, 2 );
        add_action( 'template_redirect', [ $this, 'authorize_paypal_marketplace' ], 10 );
        add_action( 'template_redirect', [ $this, 'deauthorize_vendor' ] );
        add_action( 'wp_ajax_dokan_paypal_marketplace_connect', [ $this, 'handle_paypal_marketplace_connect' ] );
        // send announcement
        add_action( 'dokan_dashboard_before_widgets', [ $this, 'send_announcement_to_non_connected_vendor' ], 10 );
        // display notice
        add_action( 'dokan_dashboard_content_inside_before', [ $this, 'display_notice_on_vendor_dashboard' ] );

        add_filter( 'dokan_withdraw_method_settings_title', [ $this, 'get_heading' ], 10, 2 );
        add_filter( 'dokan_withdraw_method_icon', [ $this, 'get_icon' ], 10, 2 );
        add_filter( 'dokan_payment_method_storage_key', [ $this, 'get_storage_key' ] );
    }

    /**
     * Register methods
     *
     * @param array $methods
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function register_methods( $methods ) {
        // check if admin provided all the required api keys
        if ( ! Helper::is_ready() ) {
            return $methods;
        }

        $methods['dokan-paypal-marketplace'] = [
            'title'    => __( 'Dokan PayPal Marketplace', 'dokan' ),
            'callback' => [ $this, 'paypal_connect_button' ],
        ];

        return $methods;
    }

    /**
     * This enables dokan vendors to connect their PayPal account to the site PayPal gateway account
     *
     * @param $store_settings
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function paypal_connect_button( $store_settings ) {
        global $current_user;

        $email = isset( $store_settings['payment']['dokan_paypal_marketplace']['email'] ) ? esc_attr( $store_settings['payment']['dokan_paypal_marketplace']['email'] ) : $current_user->user_email;

        $is_seller_enabled = Helper::is_seller_enable_for_receive_payment( get_current_user_id() );

        $merchant_id           = Helper::get_seller_merchant_id( get_current_user_id() );
        $primary_email         = get_user_meta( get_current_user_id(), Helper::get_seller_primary_email_confirmed_key(), true );
        $nonce                 = wp_create_nonce( 'dokan-paypal-marketplace-connect' );
        $disconnect_paypal_url = wp_nonce_url(
            add_query_arg(
                [ 'action' => 'dokan-paypal-marketplace-disconnect' ],
                dokan_get_navigation_url( 'settings/payment' )
            ),
            'dokan-paypal-marketplace-disconnect'
        );

        // update merchant status if already not updated
        if ( ! $is_seller_enabled && ! empty( $merchant_id ) ) {
            if ( WithdrawManager::update_merchant_status( $merchant_id, get_current_user_id() ) ) {
                $is_seller_enabled = Helper::is_seller_enable_for_receive_payment( get_current_user_id() );
                $primary_email     = get_user_meta( get_current_user_id(), Helper::get_seller_primary_email_confirmed_key(), true );
            }
        }

        Helper::get_template(
            'vendor-settings-payment',
            [
                'email'             => $email,
                'is_seller_enabled' => $is_seller_enabled,
                'nonce'             => $nonce,
                'merchant_id'       => $merchant_id,
                'primary_email'     => $primary_email,
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'disconnect_url'    => $disconnect_paypal_url,
                'load_connect_js'   => ! $is_seller_enabled && empty( $merchant_id ),
            ]
        );
    }

    /**
     * Handle paypal marketplace connect process
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle_paypal_marketplace_connect() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'dokan-paypal-marketplace-connect' ) ) {
            wp_send_json_error(
                [
                    'type'    => 'error',
                    'message' => __( 'Are you cheating?', 'dokan' ),
                    'reload'  => true,
                    'url'     => add_query_arg(
                        [
                            'status'  => 'error',
                            'message' => rawurlencode( __( 'Nonce Verification Failed.', 'dokan' ) ),
                        ],
                        dokan_get_navigation_url( 'settings/payment' )
                    ),
                ]
            );
        }

        $user_id       = dokan_get_current_user_id();
        $email_address = isset( $_POST['vendor_paypal_email_address'] ) ? sanitize_email( wp_unslash( $_POST['vendor_paypal_email_address'] ) ) : '';

        if ( ! $email_address ) {
            wp_send_json_error(
                [
                    'type'    => 'error',
                    'message' => __( 'Email address field is required.', 'dokan' ),
                    'reload'  => true,
                    'url'     => add_query_arg(
                        [
                            'status'  => 'error',
                            'message' => rawurlencode( __( 'Email address field is required.', 'dokan' ) ),
                        ],
                        dokan_get_navigation_url( 'settings/payment' )
                    ),
                ]
            );
        }

        $tracking_id    = '_dokan_paypal_' . dokan_get_random_string() . '_' . $user_id;
        $dokan_settings = get_user_meta( $user_id, 'dokan_profile_settings', true );

        //get paypal product type based on seller country
        $product_type = Helper::get_product_type( $dokan_settings['address']['country'] );

        if ( ! $product_type ) {
            wp_send_json_error(
                [
                    'type'    => 'error',
                    'message' => __( 'Vendor\'s country is not supported by PayPal.', 'dokan' ),
                    'reload'  => true,
                    'url'     => add_query_arg(
                        [
                            'status'  => 'error',
                            'message' => rawurlencode( __( 'Selected country is not supported by PayPal. Please change your Country from Vendor Dashboard --> Settings --> Country', 'dokan' ) ),
                        ],
                        dokan_get_navigation_url( 'settings/payment' )
                    ),
                ]
            );
        }

        $processor  = Processor::init();
        $paypal_url = $processor->create_partner_referral( $email_address, $tracking_id, [ $product_type ] );

        if ( is_wp_error( $paypal_url ) ) {
            // log error message to user meta
            Helper::log_paypal_error( $user_id, $paypal_url, 'create_partner_referral', 'user' );
            $error_message = Helper::get_error_message( $paypal_url );
            wp_send_json_error(
                [
                    'type'    => 'error',
                    'reload'  => true,
                    'message' => __( 'Connect to PayPal error', 'dokan' ),
                    'url'     => add_query_arg(
                        [
                            'status'  => 'error',
                            'message' => rawurlencode( $error_message ),
                        ],
                        dokan_get_navigation_url( 'settings/payment' )
                    ),
                ]
            );
        }

        if ( isset( $paypal_url['links'][1] ) && 'action_url' === $paypal_url['links'][1]['rel'] ) {
            $paypal_action_url = $paypal_url['links'][1]['href'] . '&displayMode=minibrowser';
        }

        //keeping email and partner id for later use
        update_user_meta(
            $user_id,
            Helper::get_seller_marketplace_settings_key(),
            [
                'connect_process_started' => true,
                'connection_status'       => 'pending',
                'email'                   => $email_address,
                'tracking_id'             => $tracking_id,
            ]
        );

        wp_send_json_success(
            [
                'type'   => 'success',
                'url'    => $paypal_action_url,
            ]
        );
    }

    /**
     * Handle paypal marketplace
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function authorize_paypal_marketplace() {
        $user_id = dokan_get_current_user_id();
        if ( empty( $user_id ) || ! dokan_is_user_seller( $user_id ) ) {
            return;
        }

        if (
            ! isset( $_GET['action'] ) || (
                'dokan-paypal-marketplace-connect' !== $_GET['action'] &&
                'dokan-paypal-marketplace-connect-success' !== $_GET['action'] &&
                'dokan-paypal-merchant-status-update' !== $_GET['action'] )
        ) {
            return;
        }

        $get_data = wp_unslash( $_GET );

        if ( isset( $get_data['_wpnonce'] ) && 'dokan-paypal-marketplace-connect' === $get_data['action'] && ! wp_verify_nonce( $get_data['_wpnonce'], 'dokan-paypal-marketplace-connect' ) ) {
            wp_safe_redirect( add_query_arg( [ 'message' => 'error' ], dokan_get_navigation_url( 'settings/payment' ) ) );
            exit();
        }

        if ( isset( $get_data['_wpnonce'] ) && $_GET['action'] === 'dokan-paypal-marketplace-connect-success' && ! wp_verify_nonce( $get_data['_wpnonce'], 'dokan-paypal-marketplace-connect-success' ) ) {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'status'  => 'error',
                        'message' => 'paypal_connect_error',
                    ],
                    dokan_get_navigation_url( 'settings/payment' )
                )
            );
            exit();
        }

        if ( isset( $get_data['_wpnonce'] ) && $_GET['action'] === 'dokan-paypal-merchant-status-update' && ! wp_verify_nonce( $get_data['_wpnonce'], 'dokan-paypal-merchant-status-update' ) ) {
            wp_safe_redirect( add_query_arg( [ 'message' => 'error' ], dokan_get_navigation_url( 'settings/payment' ) ) );
            exit();
        }

        if ( isset( $get_data['action'] ) && 'dokan-paypal-marketplace-connect-success' === $get_data['action'] ) {
            $response = WithdrawManager::handle_connect_success_response( $user_id );
            if ( is_wp_error( $response ) ) {
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'status'  => 'error',
                            'message' => Helper::get_error_message( $response ),
                        ],
                        dokan_get_navigation_url( 'settings/payment' )
                    )
                );
                exit();
            }
        }

        if ( isset( $get_data['action'] ) && 'dokan-paypal-merchant-status-update' === $get_data['action'] ) {
            $merchant_id = Helper::get_seller_merchant_id( dokan_get_current_user_id() );
            $response = WithdrawManager::update_merchant_status( $merchant_id );
            if ( is_wp_error( $response ) ) {
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'status'  => 'error',
                            'message' => Helper::get_error_message( $response ),
                        ],
                        dokan_get_navigation_url( 'settings/payment' )
                    )
                );
                exit();
            }
        }
    }

    /**
     * Deauthorize vendor
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function deauthorize_vendor() {
        if ( ! isset( $_GET['action'] ) || 'dokan-paypal-marketplace-disconnect' !== $_GET['action'] ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'dokan-paypal-marketplace-disconnect' ) ) {
            return;
        }

        $vendor_id = get_current_user_id();

        if ( ! $vendor_id || ! dokan_is_user_seller( $vendor_id ) ) {
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
            delete_user_meta( $vendor_id, $meta_key );
        }

        $url = add_query_arg(
            [
                'status'  => 'success',
                'message' => __( 'PayPal account disconnected successfully.', 'dokan' ),
            ],
            dokan_get_navigation_url( 'settings/payment' )
        );

        wp_safe_redirect( $url );
        exit;
    }

    /**
     * Handle PayPal error message for payment settings
     *
     * @param $current_user
     * @param $profile_info
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function handle_vendor_message( $current_user, $profile_info ) {
        $_get_data = wp_unslash( $_GET );//phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $status     = isset( $_get_data['status'] ) ? sanitize_text_field( rawurldecode( $_get_data['status'] ) ) : '';
        $message    = isset( $_get_data['message'] ) ? wp_kses_post( rawurldecode( $_get_data['message'] ) ) : '';
        $class      = $status === 'error' ? 'dokan-error' : 'dokan-message';

        if ( ! empty( $status ) && ! empty( $message ) ) {
            echo "<div class='{$class}'>{$message}</div>";
        }
    }

    /**
     * Send announcement to vendors if their account is not connected with PayPal
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function send_announcement_to_non_connected_vendor() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! Helper::display_announcement_to_non_connected_sellers() ) {
            return;
        }

        // check stripe payment gateway is enabled
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return;
        }

        // check if stripe is ready
        if ( ! Helper::is_ready() ) {
            return;
        }

        // get current user id
        $seller_id = dokan_get_current_user_id();

        // check if current user is vendor
        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        // check if vendor is already connected with PayPal
        if ( Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            return;
        }

        if ( false === get_transient( "dokan_paypal_mp_notice_intervals_$seller_id" ) ) {
            /**
             * @var $announcement Announcement
             */
            $announcement = dokan_pro()->announcement;
            // sent announcement message
            $args = [
                'title'         => $this->connect_messsage(),
                'sender_type'   => 'selected_seller',
                'sender_ids'    => [ $seller_id ],
                'status'        => 'publish',
            ];
            $notice = $announcement->create_announcement( $args );

            if ( is_wp_error( $notice ) ) {
                dokan_log( sprintf( 'Error Creating PayPal Connect Announcement For Seller %1$s, Error Message: %2$s', $seller_id, $notice->get_error_message() ) );
                return;
            }

            // notice is sent, now store transient
            set_transient( "dokan_paypal_mp_notice_intervals_$seller_id", 'sent', DAY_IN_SECONDS * Helper::non_connected_sellers_display_notice_intervals() );
        }
    }

    /**
     * Display notice to vendors if their account is not connected with PayPal
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function display_notice_on_vendor_dashboard() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        // get current user id
        $seller_id = dokan_get_current_user_id();

        // check if current user is vendor
        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        if ( ! Helper::display_notice_on_vendor_dashboard() ) {
            return;
        }

        // check stripe payment gateway is enabled
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return;
        }

        // check if stripe is ready
        if ( ! Helper::is_ready() ) {
            return;
        }

        // check if vendor is already connected with PayPal
        if ( Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            return;
        }

        echo '<div class="dokan-alert dokan-alert-danger dokan-panel-alert">' . $this->connect_messsage() . '</div>';
    }

    /**
     * @return string
     */
    private function connect_messsage() {
        return wp_kses(
            sprintf(
            // Translators: %1$s is the link to the settings page, %2$s is anchor end tag.
                __( 'Your account is not connected with PayPal Marketplace. Connect your %1$s PayPal%2$s account to receive automatic payouts.', 'dokan' ),
                sprintf( '<a href="%1$s">', dokan_get_navigation_url( 'settings/payment' ) ),
                '</a>'
            ),
            [
                'a' => [
                    'href'   => true,
                    'target' => true,
                ],
            ]
        );
    }

    /**
     * Get the Withdrawal method icon
     *
     * @since 3.5.6
     *
     * @param string $method_icon
     * @param string $method_key
     *
     * @return string
     */
    public function get_icon( $method_icon, $method_key ) {
        if ( 'dokan-paypal-marketplace' === $method_key ) {
            $method_icon = DOKAN_PAYPAL_MP_ASSETS . 'images/paypal-withdraw-method.svg';
        }

        return $method_icon;
    }

    /**
     * Get the heading for this payment's settings page
     *
     * @since 3.5.6
     *
     * @param string $heading
     * @param string $slug
     *
     * @return string
     */
    public function get_heading( $heading, $slug ) {
        if ( false !== strpos( $slug, 'dokan-paypal-marketplace' ) ) {
            $heading = __( 'Dokan Paypal Marketplace Settings', 'dokan' );
        }

        return $heading;
    }

    /**
     * Get the storage key in payment settings for this method
     *
     * @since 3.5.6
     *
     * @param array $old_key
     *
     * @return array
     */
    public function get_storage_key( $old_key ) {
        $old_key['dokan-paypal-marketplace'] = 'dokan_paypal_marketplace';

        return $old_key;
    }
}
