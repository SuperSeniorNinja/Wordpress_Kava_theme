<?php

namespace WeDevs\DokanPro;

/**
 * Custom Withdraw method handler class
 *
 * @since 3.5.0
 *
 * @author weDevs <info@wedevs.com>
 */
class CustomWithdrawMethod {

    /**
     * Constructor for the CustomWithdrawMethod class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_withdraw_methods', [ $this, 'register_custom_withdraw_method' ], 10 );

        // Hooks for admin dashboard
        add_filter( 'dokan_settings_fields', array( $this, 'custom_withdraw_method_admin_settings' ), 10, 2 );
        add_action( 'dokan_before_saving_settings', [ $this, 'validate_custom_withdraw_method_admin_settings' ], 20, 2 );

        // Hooks for vendor dashboard
        add_filter( 'dokan_payment_method_title', [ $this, 'custom_payment_method_title' ], 10, 2 );
        add_action( 'dokan_store_profile_saved', [ $this, 'save_custom_withdraw_method_vendor_settings' ], 10, 2 );
        add_filter( 'dokan_get_seller_active_withdraw_methods', [ $this, 'seller_active_withdraw_methods' ], 10, 2 );
        // remove custom withdraw method if required fields is not provided
        add_filter( 'dokan_get_active_withdraw_methods', [ $this, 'remove_custom_withdraw_method' ], 10, 1 );
    }

    /**
     * Add custom withdraw method on admin withdraw and vendor payment settings
     *
     * @since 3.5.0
     *
     * @param  array $methods
     *
     * @return array
     */
    public function register_custom_withdraw_method( $methods ) {
        $methods['dokan_custom'] = [
            'title'    => __( 'Custom', 'dokan' ),
            'callback' => [ $this, 'dokan_custom_withdraw_method' ],
            'key'      => 'dokan_custom',
        ];

        return $methods;
    }

    /**
     * Callback for displaying custom withdraw method in store settings
     *
     * @since 3.5.0
     *
     * @param array $store_settings
     *
     * @return void
     */
    public function dokan_custom_withdraw_method( $store_settings ) {
        $label = dokan_get_option( 'withdraw_method_type', 'dokan_withdraw' );
        $value = isset( $store_settings['payment']['dokan_custom']['value'] ) ? esc_attr( $store_settings['payment']['dokan_custom']['value'] ) : '';

        ?>
        <div class="dokan-form-group">
            <div class="dokan-w8">
                <div class="dokan-input-group">
                    <span class="dokan-input-group-addon"><?php echo esc_html( $label ); ?></span>
                    <input value="<?php echo esc_attr( $value ); ?>" name="settings[dokan_custom][value]" class="dokan-form-control" type="text">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add additional settings fields for custom withdraw method
     *
     * @since 3.5.0
     *
     * @param array $settings_fields
     * @param object $dokan_settings
     *
     * @return array
     */
    public function custom_withdraw_method_admin_settings( $settings_fields, $dokan_settings ) {
        $custom_withdraw = [
            'withdraw_method_name'      => [
                'name'              => 'withdraw_method_name',
                'label'             => __( 'Method Name', 'dokan' ),
                'desc'              => __( 'This will be the title of the withdraw method. e.g. MoneyGram', 'dokan' ),
                'type'              => 'text',
                'class'             => 'withdraw_method_name',
                'sanitize_callback' => 'sanitize_text_field',
                'show_if' => [
                    'withdraw_methods' => [
                        'contains' => 'dokan_custom',
                    ],
                ],
            ],
            'withdraw_method_type'      => [
                'name'              => 'withdraw_method_type',
                'label'             => __( 'Method Type', 'dokan' ),
                'desc'              => __( 'Withdraw method type. e.g. Email or Phone Number', 'dokan' ),
                'type'              => 'text',
                'class'             => 'withdraw_method_type',
                'sanitize_callback' => 'sanitize_text_field',
                'show_if' => [
                    'withdraw_methods' => [
                        'contains' => 'dokan_custom',
                    ],
                ],
            ],
        ];

        return $dokan_settings->add_settings_after(
            $settings_fields,
            'dokan_withdraw',
            'withdraw_methods',
            $custom_withdraw
        );
    }

    /**
     * Validate custom withdraw method fields
     *
     * @since 3.5.0
     *
     * @param string $option_name
     * @param array $option_value
     *
     * @return void
     */
    public function validate_custom_withdraw_method_admin_settings( $option_name, $option_value ) {
        // check we are at withdraw settings
        if ( 'dokan_withdraw' !== $option_name ) {
            return;
        }
        // check if withdraw methods array is not empty
        if ( empty( $option_value['withdraw_methods'] ) || ! is_array( $option_value['withdraw_methods'] ) ) {
            return;
        }
        // check user selected dokan custom
        if ( ! in_array( 'dokan_custom', $option_value['withdraw_methods'], true ) || empty( $option_value['withdraw_methods']['dokan_custom'] ) ) {
            return;
        }

        if ( empty( $option_value['withdraw_method_name'] ) ) {
            $errors[] = [
                'name' => 'withdraw_method_name',
                'error' => __( 'Method name can not be empty', 'dokan' ),
            ];
        }

        if ( empty( $option_value['withdraw_method_type'] ) ) {
            $errors[] = [
                'name' => 'withdraw_method_type',
                'error' => __( 'Method type can not be empty', 'dokan' ),
            ];
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error(
                [
                    'settings' => [
                        'name'  => $option_name,
                        'value' => $option_value,
                    ],
                    'message'  => __( 'Validation error', 'dokan' ),
                    'errors' => $errors,
                ],
                400
            );
        }
    }

    /**
     * Whether display custom withdraw method title or default title on vendor payment settings page
     *
     * @since 3.5.0
     *
     * @param string $title
     *
     * @return string
     */
    public function custom_payment_method_title( $title, $method ) {
        if ( isset( $method['key'] ) && 'dokan_custom' === $method['key'] ) {
            $name  = dokan_get_option( 'withdraw_method_name', 'dokan_withdraw' );
            $title = ! empty( $name ) ? $name : $title;
        }
        return $title;
    }

    /**
     * Save custom withdraw method field data
     *
     * @since 3.5.0
     *
     * @param int $store_id
     * @param array $dokan_settings
     *
     * @return void
     */
    public function save_custom_withdraw_method_vendor_settings( $store_id, $dokan_settings ) {
        if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dokan_custom_withdraw' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        $value = isset( $_POST['settings']['dokan_custom']['value'] ) ? sanitize_text_field( wp_unslash( $_POST['settings']['dokan_custom']['value'] ) ) : '';

        if ( ! empty( $value ) ) {
            $dokan_settings['payment']['dokan_custom']['value'] = $value;
            update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
        }
    }

    /**
     * Add custom withdraw method in vendor withdraw request dropdown
     *
     * @since 3.5.0
     *
     * @param array $active_payment_methods
     * @param int $vendor_id
     *
     * @return array
     */
    public function seller_active_withdraw_methods( $active_payment_methods, $vendor_id ) {
        $store_info = dokan_get_store_info( $vendor_id );
        if ( isset( $store_info['payment']['dokan_custom']['value'] ) && $store_info['payment']['dokan_custom']['value'] !== false ) {
            $active_payment_methods[] = 'dokan_custom';
        }

        return $active_payment_methods;
    }

    /**
     * Hide custom withdraw method if name and label wasn't provided by admin
     *
     * @since 3.5.0
     *
     * @param array $methods
     *
     * @return array
     */
    public function remove_custom_withdraw_method( $methods ) {
        if ( isset( $methods['dokan_custom'] ) ) {
            $label = dokan_get_option( 'withdraw_method_type', 'dokan_withdraw' );
            $name  = dokan_get_option( 'withdraw_method_name', 'dokan_withdraw' );

            // return if custom gateway label and value is not set
            if ( empty( $name ) || empty( $label ) ) {
                unset( $methods['dokan_custom'] );
            }
        }

        return $methods;
    }
}
