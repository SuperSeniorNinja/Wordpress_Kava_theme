<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dokan booking accommodation manager
 *
 * @since 3.4.2
 */
class Dokan_Booking_Accommodation_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'dokan_booking_after_product_data_saved', [ $this, 'handle_accommodation_product_save' ], 10, 1 );

        add_action( 'woocommerce_before_booking_form', [ $this, 'render_checkin_checkout_template' ], 20 );

        add_filter( 'woocommerce_bookings_date_picker_start_label', [ $this, 'modify_accommodation_checkin_label' ], 20, 1 );
        add_filter( 'woocommerce_bookings_date_picker_end_label', [ $this, 'modify_accommodation_checkout_label' ], 20, 1 );

        add_filter( 'woocommerce_get_item_data', [ $this, 'get_accommodation_item_data' ], 20, 2 );
    }

    /**
     * Handles accommodation booking save
     *
     * @since 3.4.2
     *
     * @param int $post_id
     *
     * @return void
     */
    public function handle_accommodation_product_save( $product ) {
        if ( ! isset( $_POST['dokan_accommodation_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_accommodation_nonce'] ) ), 'dokan_accommodation_data_save' ) ) {
            return;
        }

        $product = new \WC_Product_Booking( $product->get_id() );

        if ( ! $product || ! $product->is_type( 'booking' ) ) {
            return;
        }

        if ( ! isset( $_POST['_is_dokan_accommodation'] ) ) {
            $product->update_meta_data( '_dokan_is_accommodation_booking', 'no' );
            $product->save_meta_data();
            return;
        }

        // Getting if accommodation booking is on or off
        $accommodation_booking = 'on' === wc_clean( wp_unslash( $_POST['_is_dokan_accommodation'] ) ) ? 'yes' : 'no';

        // Getting checkin and checkout time for the accommodation booking
        $checkin_time  = isset( $_POST['_dokan_accommodation_checkin_time'] ) ? wc_clean( wp_unslash( $_POST['_dokan_accommodation_checkin_time'] ) ) : '';
        $checkout_time = isset( $_POST['_dokan_accommodation_checkout_time'] ) ? wc_clean( wp_unslash( $_POST['_dokan_accommodation_checkout_time'] ) ) : '';

        // Updating product meta data
        $product->update_meta_data( '_dokan_is_accommodation_booking', $accommodation_booking );
        $product->update_meta_data( '_dokan_accommodation_checkin_time', $checkin_time );
        $product->update_meta_data( '_dokan_accommodation_checkout_time', $checkout_time );

        // Props for accommodation booking
        $props = [
            'duration_unit' => 'day',
            'duration_type' => 'customer',
            'duration'      => 1,
        ];

        // Setting props
        $product->set_props( $props );

        // Saving product
        $product->save();
    }

    /**
     * Renders checkin-checkout template to single product page
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function render_checkin_checkout_template() {
        global $product;

        // If the product is not accommodation booking, return
        if ( ! $this->validate_accommodation_product() ) {
            return;
        }

        // Getting checkin and checkout time
        $checkin_time  = $product->get_meta( '_dokan_accommodation_checkin_time', true );
        $checkout_time = $product->get_meta( '_dokan_accommodation_checkout_time', true );

        // Formatting checkin and checkout time
        $formatted_checkin_time  = dokan_format_datetime( $checkin_time, wc_time_format() );
        $formatted_checkout_time = dokan_format_datetime( $checkout_time, wc_time_format() );

        dokan_get_template_part(
            'booking/accommodation/checkin-checkout', '',
            [
                'is_booking'    => true,
                'checkin_time'  => $formatted_checkin_time,
                'checkout_time' => $formatted_checkout_time,
            ]
        );
    }

    /**
     * Changes the start label to "Check-in"
     *
     * @since 3.4.2
     *
     * @param string $label
     *
     * @return string
     */
    public function modify_accommodation_checkin_label( $label ) {
        // If the product is not accommodation booking, return label
        if ( ! $this->validate_accommodation_product() ) {
            return $label;
        }

        return __( 'Check-in', 'dokan' );
    }

    /**
     * Changes the end label to "Check-out"
     *
     * @since 3.4.2
     *
     * @param string $label
     *
     * @return string
     */
    public function modify_accommodation_checkout_label( $label ) {
        // If the product is not accommodation booking, return label
        if ( ! $this->validate_accommodation_product() ) {
            return $label;
        }

        return __( 'Check-out', 'dokan' );
    }

    /**
     * Display check-in and check-out info to cart
     *
     * @since 3.4.2
     *
     * @param array $other_data
     *
     * @param array $cart_item
     *
     * @return array
     */
    public function get_accommodation_item_data( $other_data, $cart_item ) {
        $product_id = $cart_item['data']->get_id();

        $product = wc_get_product( $product_id );

        if ( ! $product || ! $product->is_type( 'booking' ) ) {
            return $other_data;
        }

        // Checking if the booking is accommodation booking
        $is_accommodation = Dokan_Booking_Accommodation_Helper::is_accommodation_booking( $product_id );

        if ( ! $is_accommodation ) {
            return $other_data;
        }

        // Getting checkin and checkout time
        $checkin_time  = $product->get_meta( '_dokan_accommodation_checkin_time', true );
        $checkout_time = $product->get_meta( '_dokan_accommodation_checkout_time', true );

        // Formatting checkin and checkout time
        $formatted_checkin_time  = dokan_format_datetime( $checkin_time, wc_time_format() );
        $formatted_checkout_time = dokan_format_datetime( $checkout_time, wc_time_format() );

        // Getting formatted start and end date of booking
        $start_date = dokan_format_datetime( $cart_item['booking']['date'], wc_date_format() );
        $end_date   = dokan_format_datetime( $cart_item['booking']['_end_date'], wc_date_format() );

        // Adding cart item data
        $other_data[] = [
            'name'    => __( 'Check-in', 'dokan' ),
            /* translators: 1: booking start date 2: formatted checkin time */
            'value'   => esc_html( sprintf( __( '%1$s at %2$s', 'dokan' ), $start_date, $formatted_checkin_time ) ),
            'display' => '',
        ];

        $other_data[] = [
            'name'    => __( 'Check-out', 'dokan' ),
            /* translators: 1: booking end date 2: formatted checkout time */
            'value'   => esc_html( sprintf( __( '%1$s at %2$s', 'dokan' ), $end_date, $formatted_checkout_time ) ),
            'display' => '',
        ];

        return $other_data;
    }

    /**
     * Validates accommodation product
     *
     * @since 3.4.2
     *
     * @return bool
     */
    private function validate_accommodation_product() {
        global $post;

        if ( is_null( $post ) ) {
            return false;
        }

        $product = wc_get_product( $post->ID );

        // Check product and the type
        if ( ! $product instanceof WC_Product || ! $product->is_type( 'booking' ) ) {
            return false;
        }

        // Getting if the product is accommodation booking
        $is_accommodation = Dokan_Booking_Accommodation_Helper::is_accommodation_booking( $product->get_id() );

        // Check if it's accommodation booking
        if ( ! $is_accommodation ) {
            return false;
        }

        return true;
    }
}
