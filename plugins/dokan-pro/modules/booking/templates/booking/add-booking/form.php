<?php
/**
 *  Dokan Add Booking form
 *
 *  @since 3.3.6
 *
 *  @package dokan
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once DOKAN_WC_BOOKING_DIR . '/includes/class-dokan-wc-booking-create.php';
$create_booking = new Dokan_WC_Bookings_Create();
$create_booking->output();
