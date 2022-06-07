<?php
/**
 *  Dokan store location WC order details
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper;

$location = isset( $location ) ? $location : '';
$date     = isset( $date ) ? $date : '';
$slot     = isset( $slot ) ? $slot : '';
?>

<div id="dokan-store-location-order-details">
    <div class="main">
        <div>
            <span><strong><?php esc_html_e( 'Store location pickup:', 'dokan' ); ?></strong></span>
            <span><?php echo esc_html( Helper::get_formatted_date_store_location_string( $date, $location, $slot ) ); ?></span>
        </div>
    </div>
</div>
