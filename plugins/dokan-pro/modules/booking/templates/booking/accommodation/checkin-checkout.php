<?php
/**
 * Dokan accommodation booking checkin-checkout
 *
 * @since 3.4.2
 * @package DokanPro
 */

$checkin_time  = isset( $checkin_time ) ? $checkin_time : '';
$checkout_time = isset( $checkout_time ) ? $checkout_time : '';
?>

<div id="dokan-accommodation-booking-checkin-checkout">
    <p><?php echo esc_html( Dokan_Booking_Accommodation_Helper::get_formatted_checkin_time_label( $checkin_time ) ); ?></p>
    <p><?php echo esc_html( Dokan_Booking_Accommodation_Helper::get_formatted_checkout_time_label( $checkout_time ) ); ?></p>
</div>
