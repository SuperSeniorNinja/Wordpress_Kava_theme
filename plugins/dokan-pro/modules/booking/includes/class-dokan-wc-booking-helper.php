<?php

/**
 * Class Dokan_WC_Booking_Helper
 *
 * @since 3.3.6
 */
class Dokan_WC_Booking_Helper {

    /**
     * Gets vendor booking products
     *
     * @since 3.3.6
     *
     * @return array
     */
    public static function get_vendor_booking_products() {
        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return [];
        }

        add_filter( 'get_booking_products_args', [ __CLASS__, 'filter_vendor_booking_products' ], 10, 1 );

        $booking_products = WC_Bookings_Admin::get_booking_products();

        remove_filter( 'get_booking_products_args', [ __CLASS__, 'filter_vendor_booking_products' ], 10 );

        return $booking_products;
    }

    /**
     * Filters vendor booking products
     *
     * @since 3.3.6
     *
     * @param array $args
     *
     * @return array
     */
    public static function filter_vendor_booking_products( $args ) {
        $args['author'] = dokan_get_current_user_id();
        return $args;
    }

    /**
     * Checks if global addon rma is active
     *
     * @since 3.3.6
     *
     * @return bool
     */
    public static function is_global_addon_rma_active() {
        if ( ! dokan_pro()->module->is_active( 'rma' ) ) {
            return false;
        }

        $global_warranty = get_user_meta( dokan_get_current_user_id(), '_dokan_rma_settings', true );

        $type = isset( $global_warranty['type'] ) ? $global_warranty['type'] : '';

        if ( 'addon_warranty' === $type ) {
            return true;
        }

        return false;
    }
}
