<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dokan booking accommodation helper
 *
 * @since 3.4.2
 */
class Dokan_Booking_Accommodation_Helper {

    /**
     * Gets accommodation booking i18n strings
     *
     * @since 3.4.2
     *
     * @return array
     */
    public static function get_accommodation_booking_i18n_strings() {
        return [
            '_wc_booking_min_duration' => [
                'default'       => __( 'Minimum duration', 'dokan' ),
                'accommodation' => __( 'Minimum number of nights allowed in a booking', 'dokan' ),
            ],
            '_wc_booking_max_duration' => [
                'default'       => __( 'Maximum duration', 'dokan' ),
                'accommodation' => __( 'Maximum number of nights allowed in a booking', 'dokan' ),
            ],
            '_wc_booking_qty'          => [
                'default'       => __( 'Max bookings per block', 'dokan' ),
                'accommodation' => __( 'Number of rooms available', 'dokan' ),
            ],
            '_wc_booking_cost'         => [
                'default'       => __( 'Base cost', 'dokan' ),
                'accommodation' => __( 'Standard room base rate', 'dokan' ),
            ],
            '_wc_booking_block_cost'   => [
                'default'       => __( 'Block cost', 'dokan' ),
                'accommodation' => __( 'Standard room block rate', 'dokan' ),
            ],
        ];
    }

    /**
     * Checks if the product is accommodation booking
     *
     * @since 3.4.2
     *
     * @param $product_id integer
     *
     * @return bool
     */
    public static function is_accommodation_booking( $product_id ) {
        $product = wc_get_product( $product_id );

        if ( $product ) {
            return 'yes' === $product->get_meta( '_dokan_is_accommodation_booking', true );
        }

        return false;
    }

    /**
     * Gets formatted check-in time label
     *
     * @since 3.4.2
     *
     * @param string $checkin_time
     *
     * @return string
     */
    public static function get_formatted_checkin_time_label( $checkin_time ) {
        /* translators: 1: Check-in time */
        return sprintf( __( 'Check-in time: %1$s', 'dokan' ), $checkin_time );
    }

    /**
     * Gets formatted check-out time label
     *
     * @since 3.4.2
     *
     * @param string $checkout_time
     *
     * @return string
     */
    public static function get_formatted_checkout_time_label( $checkout_time ) {
        /* translators: 1: Check-out time */
        return sprintf( __( 'Check-out time: %1$s', 'dokan' ), $checkout_time );
    }
}
