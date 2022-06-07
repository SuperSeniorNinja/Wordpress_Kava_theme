<?php
/**
 *  Dokan store location vendor order details
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

<div>
    <li>
        <span><?php esc_html_e( 'Store location pickup:', 'dokan' ); ?></span>
        <div><?php echo esc_html( Helper::get_formatted_date_store_location_string( $date, $location, $slot ) ); ?></div>
    </li>
</div>
