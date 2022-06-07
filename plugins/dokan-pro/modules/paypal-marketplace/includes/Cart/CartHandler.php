<?php
namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Cart;

use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Utilities\Processor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CartHandler
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\Cart
 *
 * @since 3.3.0
 */
class CartHandler {
    /**
     * CartHandler constructor.
     *
     * @since DOKAN_LITE_SINCE
     */
    public function __construct() {
        //show paypal smart payment buttons
        add_action( 'woocommerce_review_order_after_submit', [ $this, 'display_paypal_button' ] );
        add_action( 'woocommerce_pay_order_after_submit', [ $this, 'display_paypal_button' ], 20 );
        add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'after_checkout_validation' ], 15, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_vendor_is_connected' ], 10, 2 );
    }

    /**
     * Checkout page script added
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function payment_scripts() {
        if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        // if our payment gateway is disabled
        if ( ! Helper::is_enabled() ) {
            return;
        }

        if ( 'smart' !== Helper::get_button_type() ) {
            return;
        }

        if ( ! apply_filters( 'dokan_paypal_load_payment_scripts', true ) ) {
            return;
        }

        //loading this scripts only in checkout page
        if ( ! is_order_received_page() && is_checkout() || is_checkout_pay_page() ) {
            global $wp;

            //get order id if this is a order review page
            $order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : null;
            // Use minified libraries if SCRIPT_DEBUG is turned off
            $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
            $version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;

            $paypal_js_sdk_url = CartManager::get_paypal_sdk_url();

            //paypal sdk enqueue
            wp_enqueue_script( 'dokan_paypal_sdk', $paypal_js_sdk_url, [], null, false ); //phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion

            wp_enqueue_script( 'dokan_paypal_checkout', DOKAN_PAYPAL_MP_ASSETS . 'js/paypal-checkout' . $suffix . '.js', [ 'dokan_paypal_sdk' ], time(), true ); // don't cache this script
            wp_enqueue_style( 'dokan_paypal_payment_method', DOKAN_PAYPAL_MP_ASSETS . 'css/paypal-payment-method' . $suffix . '.css', [], $version );

            //localize data
            $data = [
                'payment_button_type'     => Helper::get_button_type(),
                'is_checkout_page'        => is_checkout(),
                'is_ucc_enabled'          => CartManager::is_ucc_enabled_for_all_seller_in_cart(),
                'nonce'                   => wp_create_nonce( 'dokan_paypal_checkout_nonce' ),
                'is_checkout_pay_page'    => is_checkout_pay_page(),
                'order_id'                => $order_id,
                'card_info_error_message' => __( 'Please fill up the card info!', 'dokan' ),
                'ucc_fields_placeholder'  => [
                    'card_number' => __( 'Card Number', 'dokan' ),
                    'cvv_number'  => __( 'Card Security Number', 'dokan' ),
                    'expiry_date' => __( 'mm/yy', 'dokan' ),
                ],
                'ajaxurl'         => admin_url( 'admin-ajax.php' ),
            ];

            if ( is_checkout_pay_page() ) {
                // get order info
                $order = wc_get_order( $order_id );
                if ( $order instanceof \WC_Order ) {
                    $data['billing_address'] = [
                        'streetAddress'     => $order->get_billing_address_1(),
                        'extendedAddress'   => $order->get_billing_address_2(),
                        'region'            => $order->get_billing_state(),
                        'locality'          => $order->get_billing_city(),
                        'postalCode'        => $order->get_billing_postcode(),
                        'countryCodeAlpha2' => $order->get_billing_country(),
                    ];
                }
            }

            wp_localize_script( 'dokan_paypal_sdk', 'dokan_paypal', $data );

            //add BN code to script
            add_filter( 'script_loader_tag', [ $this, 'add_bn_code_to_script' ], 10, 3 );
        }
    }

    /**
     * Add bn code and merchant ids to paypal script
     *
     * @param $tag
     * @param $handle
     * @param $source
     *
     * @return string
     */
    public function add_bn_code_to_script( $tag, $handle, $source ) {
        if ( 'dokan_paypal_sdk' === $handle ) {
            $paypal_merchant_ids = [];

            //if this is a order review page
            if ( is_checkout_pay_page() ) {
                global $wp;

                //get order id if this is a order review page
                $order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : null;

                $order = wc_get_order( $order_id );

                foreach ( $order->get_items( 'line_item' ) as $key => $line_item ) {
                    $product_id = $line_item->get_product_id();
                    $seller_id  = get_post_field( 'post_author', $product_id );

                    $merchant_id = Helper::get_seller_merchant_id( $seller_id );
                    $merchant_id = apply_filters( 'dokan_paypal_marketplace_merchant_id', $merchant_id, $product_id );

                    if ( ! empty( $merchant_id ) && ! in_array( $merchant_id, $paypal_merchant_ids, true ) ) {
                        $paypal_merchant_ids[ 'seller_' . $seller_id ] = $merchant_id;
                    }
                }
            } elseif ( is_checkout() ) {
                foreach ( WC()->cart->get_cart() as $item ) {
                    $product_id = $item['data']->get_id();
                    $seller_id  = get_post_field( 'post_author', $product_id );

                    $merchant_id = Helper::get_seller_merchant_id( $seller_id );
                    $merchant_id = apply_filters( 'dokan_paypal_marketplace_merchant_id', $merchant_id, $product_id );

                    if ( ! empty( $merchant_id ) && ! in_array( $merchant_id, $paypal_merchant_ids, true ) ) {
                        $paypal_merchant_ids[ 'seller_' . $seller_id ] = $merchant_id;
                    }
                }
            }

            if ( count( $paypal_merchant_ids ) > 1 ) {
                $source .= '&merchant-id=*';
            } elseif ( 1 === count( $paypal_merchant_ids ) ) {
                //get the first item of associative array
                $paypal_merchant_id = reset( $paypal_merchant_ids );
                $source .= '&merchant-id=' . esc_attr( $paypal_merchant_id );
            }

            //get paypal merchant ids
            $data_merchant_id = '';
            if ( ! empty( $paypal_merchant_ids ) ) {
                $data_merchant_id = ' data-merchant-id="' . esc_attr( implode( ',', $paypal_merchant_ids ) ) . '"';
            }

            //get token if ucc mode enabled
            $data_client_token = '';
            if ( CartManager::is_ucc_enabled_for_all_seller_in_cart() ) {
                $processor    = Processor::init();
                $client_token = $processor->get_generated_client_token();

                if ( is_wp_error( $client_token ) ) {
                    dokan_log( 'dokan paypal marketplace generated access token error: ' . $client_token->get_error_message() );
                } else {
                    $data_client_token = ' data-client-token="' . esc_attr( $client_token ) . '"';
                }
            }

            //@codingStandardsIgnoreStart
            $tag = '<script async src="'
                   . esc_url_raw( $source ) . '" id="' . esc_attr( $handle ) . '-js"'
                   . $data_client_token
                   . $data_merchant_id
                   . ' data-partner-attribution-id="' . esc_attr( Helper::get_bn_code() ) . '"></script>';
            //@codingStandardsIgnoreEnd
        }

        return $tag;
    }

    /**
     * Display PayPal button on the checkout page order review.
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function display_paypal_button() {
        if ( ! apply_filters( 'dokan_paypal_display_paypal_button', true ) ) {
            return;
        }
        // do not load if button type is not smart
        if ( Helper::get_button_type() !== 'smart' ) {
            return;
        }
        ?>
        <img src="<?php echo DOKAN_PAYPAL_MP_ASSETS . 'images/spinner-2x.gif'; ?>" class="paypal-loader" style="margin: 0 auto;" alt="<?php esc_html_e( 'PayPal is loading...', 'dokan' ); ?>">
        <div id="paypal-button-container" style="display:none;">
            <?php if ( CartManager::is_ucc_enabled_for_all_seller_in_cart() ) : ?>
                <div class="unbranded_checkout">
                    <button id="pay_unbranded_order" href="#" class="button alt" value="<?php esc_attr_e( 'Place order', 'dokan' ); ?>"><?php esc_html_e( 'Pay', 'dokan' ); ?></button>
                    <p class="text-center"><?php esc_html_e( 'OR', 'dokan' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Validation after checkout
     *
     * @param $data
     * @param $errors
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function after_checkout_validation( $data, $errors ) {
        if ( Helper::get_gateway_id() !== $data['payment_method'] ) {
            return;
        }

        if ( ! is_object( WC()->cart ) ) {
            return;
        }

        $available_vendors = [];
        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id = $item['data']->get_id();
            // check if this is a vendor subscription product
            if ( apply_filters( 'dokan_paypal_marketplace_escape_after_checkout_validation', Helper::is_vendor_subscription_product( $product_id ), $item ) ) {
                continue;
            }

            $available_vendors[ get_post_field( 'post_author', $product_id ) ][] = $item['data'];
        }

        // PayPal does not allow if there are more than 10 products in the cart
        if ( count( $available_vendors ) > 10 ) {
            $errors->add(
                'paypal-not-configured',
                wp_kses(
                    sprintf(
                        // translators: 1) and 2) Payment Gateway Title
                        __( '<strong>Error!</strong> %1$s Does not support more than 10 vendor products in the cart. Please remove some vendor products to continue purchasing with %1$s', 'dokan' ),
                        Helper::get_gateway_title()
                    ),
                    [
                        'strong' => [],
                    ]
                )
            );
        }

        foreach ( array_keys( $available_vendors ) as $vendor_id ) {
            if ( ! Helper::is_seller_enable_for_receive_payment( $vendor_id ) ) {
                //$vendor      = dokan()->vendor->get( $vendor_id );
                //$vendor_name = sprintf( '<a href="%s">%s</a>', esc_url( $vendor->get_shop_url() ), $vendor->get_shop_name() );

                $vendor_products = [];
                foreach ( $available_vendors[ $vendor_id ] as $product ) {
                    $vendor_products[] = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_name() );
                }

                $errors->add(
                    'paypal-not-configured',
                    wp_kses(
                        sprintf(
                            /* translators: %s: vendor products */
                            __( '<strong>Error!</strong> Remove product %s and continue checkout, this product/vendor is not eligible to be paid with PayPal', 'dokan' ),
                            implode( ', ', $vendor_products )
                        ),
                        [
                            'strong' => [],
                        ]
                    )
                );
            }
        }
    }

    /**
     * If PayPal Marketplace is only payment gateway available and vendor is not connected with PayPal, product can not be added to cart for that vendor
     *
     * @param bool $passed
     * @param int $product_id
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function validate_vendor_is_connected( $passed, $product_id ) {
        // check if this is a vendor subscription product
        if ( Helper::is_vendor_subscription_product( $product_id ) ) {
            return $passed;
        }

        // check if dokan paypal is only payment gateway available
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return $passed;
        }

        // check if paypal is ready
        if ( ! Helper::is_ready() ) {
            return $passed;
        }

        if ( count( $available_gateways ) > 1 ) {
            return $passed;
        }

        // get post author
        $seller_id = get_post_field( 'post_author', $product_id );

        // check if vendor is already connected with PayPal
        if ( ! Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            $message = wp_kses(
                sprintf(
                    // translators: 1) Product title
                    __( '<strong>Error!</strong> Could not add product %1$s to cart, this product/vendor is not eligible to be paid with %2$s', 'dokan' ),
                    get_the_title( $product_id ), Helper::get_gateway_title()
                ),
                [
                    'strong' => [],
                ]
            );
            wc_add_notice( $message, 'error' );
            return false;
        }

        return $passed;
    }
}
