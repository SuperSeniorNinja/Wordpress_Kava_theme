<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Checkout;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Processor\User;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

/**
 * CLass for managing checkout options
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        $this->init_classes();
        $this->hooks();
    }

    /**
     * Instantiates necessary classes.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init_classes() {
        new Ajax();
    }

    /**
     * Registers required hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        // Hooks to process extra fields on checkout page
        add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'save_extra_register_fields' ), 10, 2 );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_extra_register_fields' ), 10, 2 );

        // When billing address is changed by customer
        add_action( 'woocommerce_customer_save_address', array( $this, 'synchronize_account_data' ) );
    }

    /**
     * Validates extra fields for Mangopay checkout
     *
     * @since 3.5.0
     *
     * @param string $fields
     * @param object $errors
     *
     * @return object
     */
    public function validate_extra_register_fields( $fields, $errors ) {
        // Return if Mangopay is not the payment method
        if ( Helper::get_gateway_id() !== $fields['payment_method'] ) {
            return $errors;
        }

        // Return if user is not logged in
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return $errors;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        // Validate birthdate if it is not given already
        if ( empty( Meta::get_user_birthday( $user_id ) ) ) {
            if ( empty( $_POST['dokan_user_birthday'] ) ) {
                $errors->add( 'dokan-user-birthday-error', __( '<strong>Date of Birth</strong> is a required field.', 'dokan' ) );
            } elseif ( ! Helper::is_valid_date( sanitize_text_field( wp_unslash( $_POST['dokan_user_birthday'] ) ) ) ) {
                $errors->add( 'dokan-user-birthday-error', __( '<strong>Date of Birth</strong> is not valid.', 'dokan' ) );
            }
        }

        // Validate nationality if it is not given already
        if ( empty( Meta::get_user_nationality( $user_id ) ) && empty( $_POST['dokan_user_nationality'] ) ) {
            $errors->add( 'dokan-user-nationality-error', __( '<strong>Nationality</strong> is a required field.', 'dokan' ) );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        return $errors;
    }

    /**
     * Save the extra register fields.
     * We need this to enforce mandatory/required fields
     * that we need for creating a mangopay user
     *
     * @param int   $customer_id ID of the current customer
     * @param array $data        Posted data from the checkout page
     *
     * @return void
     */
    public function save_extra_register_fields( $customer_id, $data ) {
        // Return if Mangopay is not the payment method
        if ( Helper::get_gateway_id() !== $data['payment_method'] ) {
            return;
        }

        if ( ! empty( $data['billing_first_name'] ) ) {
            update_user_meta( $customer_id, 'first_name', $data['billing_first_name'] );
        }

        if ( ! empty( $data['billing_last_name'] ) ) {
            update_user_meta( $customer_id, 'last_name', $data['billing_last_name'] );
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        User::create(
            $customer_id,
            array(
                'person_type'   => 'NATURAL',
                'date_of_birth' => ! empty( $_POST['dokan_user_birthday'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_user_birthday'] ) ) : '',
                'nationality'   => ! empty( $_POST['dokan_user_nationality'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_user_nationality'] ) ) : '',
            )
        );
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Fires up when WC shop settings have been saved.
     *
     * @since 3.5.0
     *
     * @param int $wp_user_id
     *
     * @return void
     */
    public function synchronize_account_data( $wp_user_id ) {
        User::sync_account_data( $wp_user_id );
    }
}
