<?php

use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

return apply_filters(
    'dokan_paypal_marketplace_admin_gateway_settings', [
        'shipping_tax_fee_recipient_notice' => [
            'title'       => __( 'Note', 'dokan' ),
            'type'        => 'title',
            /* translators: %s: URL */
            'description' => wp_kses(
                __( 'For this payment gateway, <strong>Shipping Fee Recipient</strong> and <strong>Tax Fee Recipient</strong> will be set to <strong>Seller</strong>. Otherwise, in case of partial refund, you will not be able to refund shipping or tax fee from admin commission.', 'dokan' ),
                [
                    'strong' => [],
                ]
            ),
        ],
        'enabled'        => [
            'title'   => __( 'Enable/Disable', 'dokan' ),
            'type'    => 'checkbox',
            'label'   => __( 'Enable Dokan PayPal Marketplace', 'dokan' ),
            'default' => 'no',
        ],
        'title'          => [
            'title'       => __( 'Title', 'dokan' ),
            'type'        => 'text',
            'class'       => 'input-text regular-input ',
            'description' => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
            'default'     => __( 'PayPal Marketplace', 'dokan' ),
            'desc_tip'    => true,
        ],
        'description'    => [
            'title'       => __( 'Description', 'dokan' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'dokan' ),
            'default'     => __( 'Pay via PayPal Marketplace; you can pay with your credit card if you don\'t have a PayPal account', 'dokan' ),
        ],
        'partner_id'     => [
            'title'       => __( 'PayPal Merchant ID/Partner ID', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => __( 'To get Merchant ID goto Paypal Dashboard --> Account Settings --> Business Information section.', 'dokan' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => 'PayPal Merchant ID/Partner ID',
        ],
        'api_details'    => array(
            'title'       => __( 'API credentials', 'dokan' ),
            'type'        => 'title',
            /* translators: %s: URL */
            'description' => wp_kses(
                sprintf(
                // translators: 1) help documentation link
                    __( 'Your API credentials are a client ID and secret, which authenticate API requests from your account. You get these credentials from a REST API app in the Developer Dashboard. Visit <a href="%1$s">this link</a> for more information about getting your api details.', 'dokan' ),
                    'https://developer.paypal.com/docs/platforms/get-started/'
                ),
                [
                    'a'         => [
                        'href'   => true,
                        'target' => true,
                    ],
                ]
            ),
        ),
        'test_mode'      => [
            'title'       => __( 'PayPal sandbox', 'dokan' ),
            'type'        => 'checkbox',
            'label'       => __( 'Enable PayPal sandbox', 'dokan' ),
            'default'     => 'no',
            /* translators: %s: paypal developer url */
            'description' => sprintf( __( 'PayPal sandbox can be used to test payments. Sign up for a developer account <a href="%s">here</a>.', 'dokan' ), 'https://developer.paypal.com/' ),
        ],
        'app_user'       => [
            'title'       => __( 'Client ID', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => __( 'For this payment method your need an application credential', 'dokan' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'Client ID', 'dokan' ),
        ],
        'app_pass'       => [
            'title'       => __( 'Client Secret', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => __( 'For this payment method your need an application credential', 'dokan' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'Client Secret', 'dokan' ),
        ],
        'test_app_user'  => [
            'title'       => __( 'Sandbox Client ID', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => __( 'For this system please sign up in developer account and get your  application credential', 'dokan' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'Sandbox Client ID', 'dokan' ),
        ],
        'test_app_pass'  => [
            'title'       => __( 'Sandbox Client Secret', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => __( 'For this system please sign up in developer account and get your  application credential', 'dokan' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'Sandbox Client Secret', 'dokan' ),
        ],
        'bn_code'        => [
            'title'       => __( 'PayPal Partner Attribution Id', 'dokan' ),
            'type'        => 'text',
            'class'       => 'input-text regular-input ',
            'description' => __( 'PayPal Partner Attribution ID will be given to you after you setup your PayPal Marketplace account. If you do not have any, default one will be used.', 'dokan' ),
            'default'     => 'weDevs_SP_Dokan',
            'desc_tip'    => true,
            'placeholder' => __( 'PayPal Partner Attribution Id', 'dokan' ),
        ],
        'disbursement_mode' => [
            'title'       => __( 'Disbursement Mode', 'dokan' ),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __( 'Choose whether you wish to disburse funds to the vendors immediately or hold the funds. Holding funds gives you time to conduct additional vetting or enforce other platform-specific business logic.', 'dokan' ),
            'default'     => 'INSTANT',
            'desc_tip'    => true,
            'options'     => [
                'INSTANT'   => __( 'Immediate', 'dokan' ),
                'ON_ORDER_COMPLETE' => __( 'On Order Complete', 'dokan' ),
                'DELAYED' => __( 'Delayed', 'dokan' ),
            ],
        ],
        'disbursement_delay_period' => [
            'title'       => __( 'Disbursement Delay Period', 'dokan' ),
            'type'        => 'number',
            'class'       => 'input-text regular-input ',
            'description' => __( 'Specify after how many days funds will be disburse to corresponding vendor. Maximum holding period is 29 days. After 29 days, fund will be automatically disbursed to corresponding vendor.', 'dokan' ),
            'default'     => '7',
            'desc_tip'    => true,
            'placeholder' => __( 'Disbursement Delay Period', 'dokan' ),
            'custom_attributes' => [
                'min' => 1,
                'max' => 29,
            ],
        ],
        'button_type'    => [
            'title'         => __( 'Payment Button Type', 'dokan' ),
            'type'          => 'select',
            'class'         => 'wc-enhanced-select',
            'description'   => __( 'Smart Payment Buttons type is recommended.', 'dokan' ),
            'default'       => 'smart',
            'options'       => [
                'smart'    => __( 'Smart Payment Buttons', 'dokan' ),
                'standard' => __( 'Standard Button', 'dokan' ),
            ],
        ],
        'ucc_mode_notice' => [
            'title'       => __( 'Set up advanced credit and debit card payments', 'dokan' ),
            'type'        => 'title',
            /* translators: %s: URL */
            'description' => wp_kses(
                sprintf(
                // translators: 1) UCC supported country lists
                    __( 'Set up advanced payment options on your checkout page so your buyers can pay with debit and credit cards, PayPal, and alternative payment methods. <strong>Supported Countries:</strong> %1$s', 'dokan' ),
                    implode( ', ', Helper::get_advanced_credit_card_debit_card_supported_countries() )
                ),
                [
                    'strong' => [],
                ]
            ),
        ],
        'ucc_mode'  => [
            'title'   => __( 'Allow Unbranded Credit Card', 'dokan' ),
            'type'    => 'checkbox',
            'label'   => __( 'Allow advanced credit and debit card payments', 'dokan' ),
            'default' => 'no',
        ],
        'marketplace_logo' => [
            'title'       => __( 'Marketplace Logo', 'dokan' ),
            'type'        => 'url',
            'description' => __( 'When vendors connect their PayPal account, they will see this logo upper right corner of the PayPal connect window', 'dokan' ),
            'default'     => esc_url_raw( DOKAN_PLUGIN_ASSEST . '/images/dokan-logo.png' ),
        ],
        'display_notice_on_vendor_dashboard' => [
            'title'       => __( 'Display Notice to Connect Seller', 'dokan' ),
            'label'       => __( 'If checked, non-connected sellers will see a notice to connect their PayPal account on their vendor dashboard.', 'dokan' ),
            'type'        => 'checkbox',
            'description' => __( 'If this is enabled, non-connected sellers will see a notice to connect their Paypal account on their vendor dashboard.', 'dokan' ),
            'default'     => 'no',
            'desc_tip'    => true,
        ],
        'display_notice_to_non_connected_sellers' => [
            'title'       => __( 'Send Announcement to Connect Seller', 'dokan' ),
            'label'       => __( 'If checked, non-connected sellers will receive announcement notice to connect their PayPal account. ', 'dokan' ),
            'type'        => 'checkbox',
            'description' => __( 'If this is enabled non-connected sellers will receive announcement notice to connect their Paypal account once in a week by default.', 'dokan' ),
            'default'     => 'no',
            'desc_tip'    => true,
        ],
        'display_notice_interval' => [
            'title'       => __( 'Send Announcement Interval', 'dokan' ),
            'type'        => 'number',
            'description' => __( 'If Send Announcement to Connect Seller setting is enabled, non-connected sellers will receive announcement notice to connect their PayPal account once in a week by default. You can control notice display interval from here.', 'dokan' ),
            'default'     => '7',
            'desc_tip'    => false,
            'custom_attributes' => [
                'min' => 1,
            ],
        ],
        'webhook_message' => [
            'title'       => __( 'Webhook URL', 'dokan' ),
            'type'        => 'title',
            'description' => wp_kses(
                sprintf(
                // translators: 1) site url 2) paypal dev doc url
                    __( 'Webhook URL will be set <strong>automatically</strong> in your application settings with required events after you provide <strong>correct API information</strong>. You don\'t have to setup webhook url manually. Only make sure webhook url is available to <code>%1$s</code> in your PayPal <a href="%2$s" target="_blank">application settings</a>.', 'dokan' ),
                    home_url( 'wc-api/dokan-paypal', 'https' ), 'https://developer.paypal.com/developer/applications/'
                ),
                [
                    'a'         => [
                        'href'   => true,
                        'target' => true,
                    ],
                    'code'      => [],
                    'strong'    => [],
                ]
            ),
        ],
    ]
);
