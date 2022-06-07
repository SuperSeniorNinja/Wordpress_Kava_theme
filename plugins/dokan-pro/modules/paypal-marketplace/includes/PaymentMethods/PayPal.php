<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\PaymentMethods;

use WC_Payment_Gateway;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Cart\CartManager;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Order\OrderManager;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;
use WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class PayPal
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\PaymentMethods
 *
 * @since 3.3.0
 */
class PayPal extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function __construct() {
        $this->supports = [
            'products',
            'refunds',
        ];

        $this->init_fields();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        $this->init_hooks();

        if ( ! $this->is_valid_for_use() ) {
            $this->enabled = 'no';
        }
    }

    /**
     * Init essential fields
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function init_fields() {
        $this->id                 = Helper::get_gateway_id();
        $this->icon               = false;
        $this->has_fields         = true;
        $this->method_title       = __( 'Dokan PayPal Marketplace', 'dokan' );
        $this->method_description = __( 'Pay Via PayPal Marketplace', 'dokan' );
        $this->icon               = apply_filters( 'woocommerce_paypal_icon', DOKAN_PAYPAL_MP_ASSETS . 'images/paypal-marketplace.svg' );

        $title                = $this->get_option( 'title' );
        $this->title          = empty( $title ) ? __( 'PayPal Marketplace', 'dokan' ) : $title;
        $this->test_mode      = $this->get_option( 'test_mode' );
        $this->app_user       = $this->get_option( 'app_user' );
        $this->app_pass       = $this->get_option( 'app_pass' );
        $this->app_id         = $this->get_option( 'app_id' );
        $this->test_app_user  = $this->get_option( 'test_app_user' );
        $this->test_app_pass  = $this->get_option( 'test_app_pass' );
        $this->debug          = $this->get_option( 'debug' );
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = require DOKAN_PAYPAL_MP_TEMPLATE_PATH . 'admin-gateway-settings.php';
    }

    /**
     * Initialize necessary actions
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ &$this, 'process_admin_options' ] );
        add_action( 'admin_footer', [ $this, 'admin_script' ] );
    }

    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function is_valid_for_use() {
        if ( ! in_array( get_woocommerce_currency(), array_keys( Helper::get_supported_currencies() ), true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Display information in frontend
     * after checkout process button
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function payment_fields() {
        echo $this->get_option( 'description' );

        $payment_fields = apply_filters( 'dokan_paypal_payment_fields', true );

        if ( $payment_fields && CartManager::is_ucc_enabled_for_all_seller_in_cart() ) {
            Helper::get_template( '3DS-payment-option' );
        }
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        // PayPal store orphaned order for 3 hours, so we don't need to check if multiple order created on paypal end.

        $process_payment = apply_filters( 'dokan_paypal_process_payment', [ 'order' => $order ] );

        if ( isset( $process_payment['product_type'] ) && 'product_pack' === $process_payment['product_type'] ) {
            return $process_payment['data'];
        }

        $sub_orders     = get_children(
            [
                'post_parent' => $order_id,
                'post_type'   => 'shop_order',
            ]
        );
        $purchase_units = [];

        if ( $order->get_meta( 'has_sub_order' ) ) {
            foreach ( $sub_orders as $item ) {
                $sub_order = wc_get_order( $item->ID );
                $sub_order->update_meta_data( '_dokan_paypal_payment_disbursement_mode', Helper::get_disbursement_mode() );
                $sub_order->save_meta_data();
                $purchase_units[] = OrderManager::make_purchase_unit_data( $sub_order );
            }
        } else {
            $order->update_meta_data( '_dokan_paypal_payment_disbursement_mode', Helper::get_disbursement_mode() );
            $purchase_units[] = OrderManager::make_purchase_unit_data( $order );
        }

        $create_order_data = [
            'intent'              => 'CAPTURE',
            'payer'               => OrderManager::get_shipping_address( $order, true ),
            'application_context' => [
                'return_url'          => $this->get_return_url( $order ),
                'cancel_url'          => $order->get_cancel_order_url_raw(),
                'brand_name'          => get_bloginfo( 'name' ),
                'user_action'         => 'PAY_NOW',
                'payment_method'      => [
                    'payer_selected'  => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            ],
            'purchase_units'      => $purchase_units,
        ];

        $processor = Processor::init();
        $create_order_url = $processor->create_order( $create_order_data );

        if ( is_wp_error( $create_order_url ) ) {
            $error_message = sprintf(
            // translators: 1) error message from payment gateway
                __( 'Error while creating PayPal order: %1$s', 'dokan' ), Helper::get_error_message( $create_order_url )
            );
            wc_add_notice( $error_message, 'error' );
            dokan_log( '[Dokan PayPal Marketplace] Create Order Data: ' . print_r( $create_order_data, true ) );
            Helper::log_paypal_error( $order->get_id(), $create_order_url, 'dpm_create_order' );

            return [
                'result'   => 'failure',
                'redirect' => false,
                'messages' => '<ul class="woocommerce-error" role="alert"><li>' . $error_message . '</li></ul>',
            ];
        }
        //store paypal debug id & create order id
        $order->update_meta_data( '_dokan_paypal_create_order_debug_id', $create_order_url['paypal_debug_id'] );
        $order->update_meta_data( '_dokan_paypal_order_id', $create_order_url['id'] );
        $order->update_meta_data( '_dokan_paypal_redirect_url', $create_order_url['links'][1]['href'] );
        $order->update_meta_data( 'shipping_fee_recipient', 'seller' );
        $order->update_meta_data( 'tax_fee_recipient', 'seller' );
        $order->save_meta_data();

        return [
            'result'              => 'success',
            'id'                  => $order_id,
            'paypal_redirect_url' => $create_order_url['links'][1]['href'],
            'paypal_order_id'     => $create_order_url['id'],
            'redirect'            => $create_order_url['links'][1]['href'],
            'success_redirect'    => $this->get_return_url( $order ),
            'cancel_redirect'     => $order->get_cancel_order_url_raw(),
        ];
    }

    /**
     * Get the state to send to paypal
     *
     * @param string $cc
     * @param string $state
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_paypal_state( $cc, $state ) {
        if ( 'US' === $cc ) {
            return $state;
        }

        $states = WC()->countries->get_states( $cc );

        if ( isset( $states[ $state ] ) ) {
            return $states[ $state ];
        }

        return $state;
    }

    /**
     * Admin options with extra information
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function admin_options() {
        if ( $this->is_valid_for_use() ) {
            parent::admin_options();
        } else {
            ?>
            <div class="inline error">
                <p>
                    <strong><?php esc_html_e( 'Gateway disabled', 'dokan' ); ?></strong>:
                    <?php
                    echo wp_kses(
                        sprintf(
                        // translators: 1) UCC supported country lists
                            __( 'Dokan PayPal Marketplace does not support your store currency. <strong>Supported Currencies are:</strong> %1$s', 'dokan' ),
                            implode( ', ', Helper::get_supported_currencies() )
                        ),
                        [
                            'strong' => [],
                        ]
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Admin script
     *
     * @since DOKAN_SINCE_LITE
     *
     * @return void
     */
    public function admin_script() {
        ?>
        <script type="text/javascript">
            ;(function ($, document) {
                const Dokan_PayPal_MarketPlace_Settings = {
                    payment_id_prefix: 'woocommerce_<?php echo Helper::get_gateway_id(); ?>_',

                    init: function() {
                        Dokan_PayPal_MarketPlace_Settings.inputToggle();
                        //toggle sandbox and live api credentials
                        $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}test_mode`).on('change', Dokan_PayPal_MarketPlace_Settings.inputToggle);

                        Dokan_PayPal_MarketPlace_Settings.disbursementPeriodToggle();
                        // toggle disbursement mode
                        $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}disbursement_mode`).on('change', Dokan_PayPal_MarketPlace_Settings.disbursementPeriodToggle);

                        // validate disbursement period validation
                        Dokan_PayPal_MarketPlace_Settings.disbursementPeriodValidation();
                        $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}disbursement_delay_period`).on('change', Dokan_PayPal_MarketPlace_Settings.disbursementPeriodValidation);

                        Dokan_PayPal_MarketPlace_Settings.noticeIntervalToggle();
                        //toggle notice interval fields
                        $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}display_notice_to_non_connected_sellers`).on('change', Dokan_PayPal_MarketPlace_Settings.noticeIntervalToggle);
                    },

                    inputToggle: function() {
                        let settings_input_ids = [
                            'app_user',
                            'app_pass'
                        ];

                        if ( $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}test_mode`).is(':checked') ) {
                            settings_input_ids.map(function (id) {
                                $('#' + Dokan_PayPal_MarketPlace_Settings.payment_id_prefix + 'test_' + id).closest('tr').show();
                                $('#' + Dokan_PayPal_MarketPlace_Settings.payment_id_prefix + id).closest('tr').hide();
                            });
                        } else {
                            settings_input_ids.map(function (id) {
                                $('#' + Dokan_PayPal_MarketPlace_Settings.payment_id_prefix + id).closest('tr').show();
                                $('#' + Dokan_PayPal_MarketPlace_Settings.payment_id_prefix + 'test_' + id).closest('tr').hide();
                            });
                        }
                    },

                    disbursementPeriodToggle: function() {
                        let val = $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}disbursement_mode`).val();
                        if ( val === 'DELAYED' ) {
                            $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}disbursement_delay_period`).closest('tr').show();
                        } else {
                            $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}disbursement_delay_period`).closest('tr').hide();
                        }
                    },

                    disbursementPeriodValidation: function() {
                        let disbursementPeriod = $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}disbursement_delay_period`);
                        if ( parseInt( disbursementPeriod.val() ) > 29 ) {
                            disbursementPeriod.val( 29 );
                        }
                    },

                    noticeIntervalToggle: function() {
                        let noticeCheckbox = $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}display_notice_to_non_connected_sellers`);
                        let noticeInterval = $(`#${Dokan_PayPal_MarketPlace_Settings.payment_id_prefix}display_notice_interval`);
                        if ( noticeCheckbox.prop('checked') ) {
                            noticeInterval.closest('tr').show();
                        } else {
                            noticeInterval.closest('tr').hide();
                        }
                    },
                };

                $( document ).ready( function ( $ ) {
                    Dokan_PayPal_MarketPlace_Settings.init();
                } );

            })(jQuery, document);
        </script>
        <?php
    }

    /**
     * Process admin options
     *
     * @since 3.3.0
     *
     * @return bool|void
     */
    public function process_admin_options() {
        parent::process_admin_options();

        // delete token transient after settings is being updated
        delete_transient( '_dokan_paypal_marketplace_access_token' );
        delete_transient( '_dokan_paypal_marketplace_client_token' );

        //create webhook automatically
        /**
         * @uses \WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookHandler $instance
         */
        $instance = dokan_pro()->module->paypal_marketplace->webhook;

        if ( Helper::is_enabled() ) {
            //if gateway is enabled, automatically create webhook for this site
            if ( $instance instanceof WebhookHandler ) {
                $instance->register_webhook();
            }
        } else {
            //if gateway is disabled, delete created webhook for this site
            if ( $instance instanceof WebhookHandler ) {
                $instance->deregister_webhook();
            }
        }

        /*
         * hack applied to process vendor subscription payment,
         * from kapil bhai: Amra method e seller er id and details dei. Tai admin er tao evabe rakha
         */
        //todo: remove below lines from here
        //update_user_meta( dokan_get_current_user_id(), '_dokan_paypal_merchant_id', $this->get_option( 'partner_id' ) );
        //update_user_meta( dokan_get_current_user_id(), '_dokan_paypal_enable_for_receive_payment', true );
    }

    /**
     * Check if this payment method is available with conditions
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function is_available() {
        $is_available = parent::is_available();

        if ( ! $is_available ) {
            return false;
        }

        //we are returning true for admin. otherwise admin will get error on the dashboard.
        if ( is_admin() ) {
            return true;
        }

        // check if admin provided all the api information right
        if ( ! Helper::is_ready() ) {
            return false;
        }

        if ( is_checkout_pay_page() ) {
            global $wp;

            //get order id if this is a order review page
            $order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : null;

            $order = wc_get_order( $order_id );

            //return if this is not an order object
            if ( ! is_object( $order ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     *
     * @since 3.3.0
     * @return bool
     */
    public function needs_setup() {
        if (
            empty( Helper::get_partner_id() ) ||
            empty( Helper::get_client_id() ) ||
            empty( Helper::get_client_secret() ) ) {
            return true;
        }

        return false;
    }
}

