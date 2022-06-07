<?php
/**
 * Dokan Vendor delivery time box template
 *
 * @since 3.3.0
 * @package DokanPro
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper;

$order_id            = isset( $order_id ) ? absint( $order_id ) : 0;
$vendor_id           = isset( $vendor_id ) ? absint( $vendor_id ) : 0;
$delivery_date_label = isset( $info['vendor_delivery_options']['delivery_date_label'] ) ? $info['vendor_delivery_options']['delivery_date_label'] : __( 'Select delivery date', 'dokan' );

$current_date = dokan_current_datetime();

// Delivery time
$delivery_time_date = get_post_meta( $order_id, 'dokan_delivery_time_date', true ) ? $current_date->modify( get_post_meta( $order_id, 'dokan_delivery_time_date', true ) )->format( 'F j, Y' ) : null;
$delivery_time_slot = get_post_meta( $order_id, 'dokan_delivery_time_slot', true ) ? get_post_meta( $order_id, 'dokan_delivery_time_slot', true ) : null;

$vendor_info = isset( $vendor_info ) ? $vendor_info : null;

wp_add_inline_script( 'dokan-delivery-time-vendor-script', 'let vendorInfo =' . wp_json_encode( $vendor_info ), 'before' );
?>

<div class="" style="width:100%">
    <div class="dokan-panel dokan-panel-default">
        <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Delivery Time', 'dokan' ); ?></strong></div>
        <div class="dokan-panel-body general-details vendor-delivery-time-box">
            <div>
                <span><?php esc_html_e( 'Delivery Date: ', 'dokan' ); ?></span>
                <?php echo esc_html( Helper::get_formatted_delivery_date_time_string( $delivery_time_date, $delivery_time_slot ) ); ?>
            </div>

            <form action="" method="post">
                <input type="hidden" name="vendor_selected_current_delivery_date_slot" value="<?php echo esc_attr( \WeDevs\DokanPro\Modules\DeliveryTime\Helper::get_formatted_delivery_date_time_string( $delivery_time_date, $delivery_time_slot ) ); ?>">
                <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">

                <input id="vendor-delivery-time-date-picker" data-vendor_id="<?php echo esc_attr( $vendor_id ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'dokan_delivery_time' ) ); ?>" class="delivery-time-date-picker"
                    name="dokan_delivery_date" type="text"
                    placeholder="<?php echo esc_attr( $delivery_date_label ); ?>"
                    readonly="readonly">
                <select class="delivery-time-slot-picker dokan-form-control" id="vendor-delivery-time-slot-picker" name="dokan_delivery_time_slot">
                    <option selected disabled><?php esc_html_e( 'Select time slot', 'dokan' ); ?></option>
                </select>

                <?php wp_nonce_field( 'dokan_vendor_delivery_time_box_action', 'dokan_vendor_delivery_time_box_nonce' ); ?>
                <input type="submit" id="dokan_update_delivery_time" name="dokan_update_delivery_time" class="dokan-btn add_note btn btn-sm btn-theme" value="<?php esc_attr_e( 'Update', 'dokan' ); ?>">
            </form>
        </div>
    </div>
</div>
