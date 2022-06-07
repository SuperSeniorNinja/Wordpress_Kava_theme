<?php
/**
 * Dokan Delivery time wc order details
 *
 * @since 3.3.0
 * @package DokanPro
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper;

$delivery_time_date_slot = isset( $delivery_time_date_slot ) ? $delivery_time_date_slot : [];

if ( empty( $delivery_time_date_slot['date'] ) ) {
    return;
}
?>

<div id="dokan-delivery-time-slot-order-details">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" height="20" viewBox="0 0 24 24" stroke="#333333">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="main">
        <span style="margin-right: 8px;"><strong><?php esc_html_e( 'Delivery Date: ', 'dokan' ); ?></strong></span>
        <span><?php echo esc_html( Helper::get_formatted_delivery_date_time_string( $delivery_time_date_slot['date'], $delivery_time_date_slot['slot'] ) ); ?></span>
    </div>
</div>
