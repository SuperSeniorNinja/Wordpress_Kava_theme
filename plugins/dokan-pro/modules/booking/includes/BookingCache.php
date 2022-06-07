<?php

namespace WeDevs\DokanPro\Modules\Booking;

use WeDevs\Dokan\Cache;

/**
 * Dokan Booking Cache Class.
 *
 * Manage all of the cachings for booking module.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class BookingCache {

    /**
     * Constructor.
     *
     * Managed Cachings for the following groups:
     * 1. booking_products_[seller_id] => For Seller's Bookable Products
     * 2. bookings_[seller_id]         => For Seller's Booking Activities
     *
     * @since 3.4.2
     */
    public function __construct() {
        // Clear Booking Product related caches
        add_action( 'dokan_booking_after_product_data_saved', [ $this, 'clear_booking_type_product_caches' ], 10, 2 );
        add_action( 'dokan_product_duplicate_after_save', [ $this, 'clear_booking_type_product_caches' ], 10, 2 );
        add_action( 'before_delete_post', [ $this, 'clear_booking_type_product_caches' ], 10, 1 );
        add_action( 'wp_trash_post', [ $this, 'clear_booking_type_product_caches' ], 10, 1 );
        add_action( 'delete_post', [ $this, 'clear_booking_type_product_caches' ], 10 );
        add_action( 'woocommerce_new_product', [ $this, 'clear_booking_type_product_caches' ], 20 );
        add_action( 'woocommerce_update_product', [ $this, 'clear_booking_type_product_caches' ], 20 );
        add_action( 'woocommerce_product_duplicate', [ $this, 'clear_booking_type_product_caches' ], 20 );
        add_action( 'woocommerce_product_import_inserted_product_object', [ $this, 'clear_booking_type_product_caches' ], 20 );

        // Clear Booking related caches
        add_action( 'dokan_after_booking_confirmed', [ $this, 'clear_booking_data_caches' ], 10, 1 );
        add_action( 'dokan_booking_change_status', [ $this, 'clear_booking_data_caches' ], 10, 1 );
        add_action( 'woocommerce_before_booking_object_save', [ $this, 'clear_booking_data_caches' ], 10, 2 );
        add_action( 'woocommerce_bookings_created_manual_booking', [ $this, 'clear_booking_data_caches' ], 10, 1 );
    }

    /**
     * Clear Booking activity data caches.
     *
     * @since 3.4.2
     *
     * @param \WC_Booking|int $booking    Booking product object or ID.
     * @param array           $data_store Booking data store.
     *
     * @return void
     */
    public function clear_booking_data_caches( $booking, $data_store = [] ) {
        // Ensure we have a booking object, If ID passed, get the booking object.
        if ( ! $booking instanceof \WC_Booking ) {
            $booking = get_wc_booking( $booking );
        }

        // Do not clear cache if it is not a product.
        if ( ! $booking instanceof \WC_Booking ) {
            return;
        }

        // Get Parent Product ID from Booking object.
        $product_id = $booking->get_product_id();

        $seller_id = get_post_field( 'post_author', $product_id );

        if ( ! $seller_id ) {
            return;
        }

        Cache::invalidate_group( "bookings_{$seller_id}" );
    }

    /**
     * Clear Booking Product Caches.
     *
     * If come here, then there must be a product id and it is booking type
     *
     * @since 3.4.2
     *
     * @param int $product_id
     *
     * @return void
     */
    public function clear_product_caches( $product_id ) {
        $seller_id = get_post_field( 'post_author', $product_id );

        // If no seller found, that means no need to use this invalidation.
        if ( ! $seller_id ) {
            return;
        }

        Cache::invalidate_group( "booking_products_{$seller_id}" );
    }

    /**
     * Clear Booking Product caches.
     *
     * @since 3.4.2
     *
     * @param int|\WC_Product   $product    Product ID or object.
     * @param array|\WC_Product $post_data
     *
     * @return void
     */
    public function clear_booking_type_product_caches( $product, $post_data = [] ) {
        // Check if it is a WC Product, If got a product id, then convert it to WC_Product
        if ( ! $product instanceof \WC_Product ) {
            $product = wc_get_product( $product );
        }

        // Do not clear cache if it is not a product
        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        // Don't invalidate cache if product is not booking type
        if ( ! $product->is_type( 'booking' ) ) {
            return;
        }

        $this->clear_product_caches( $product->get_id() );
    }
}
