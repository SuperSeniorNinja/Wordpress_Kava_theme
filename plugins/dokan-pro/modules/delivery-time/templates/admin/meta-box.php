<?php
/**
 * Dokan WC order details page delivery time meta box
 *
 * @since 3.3.0
 * @package DokanPro
 */

$vendor_info = isset( $vendor_info ) ? $vendor_info : [];
$store_location = isset( $vendor_info['store_location'] ) ? $vendor_info['store_location'] : '';
?>

<div id="dokan-admin-delivery-time">
    <div class="order_data_column_container">
        <div class="header">
            <span><strong><?php esc_html_e( 'Current delivery time: ', 'dokan' ); ?></strong></span>
            <span><?php echo esc_html( \WeDevs\DokanPro\Modules\DeliveryTime\Helper::get_formatted_delivery_date_time_string( $vendor_info['vendor_selected_delivery_date'], $vendor_info['vendor_selected_delivery_slot'] ) ); ?></span>
            <?php if ( ! empty( $store_location ) ) : ?>
                <br>
                <strong><?php esc_html_e( 'Store location: ', 'dokan' ); ?></strong>
                <span><?php echo esc_html( $store_location ); ?></span>
            <?php endif; ?>
            <input name="vendor_selected_current_delivery_date_slot" type="hidden" value="<?php echo esc_attr( \WeDevs\DokanPro\Modules\DeliveryTime\Helper::get_formatted_delivery_date_time_string( $vendor_info['vendor_selected_delivery_date'], $vendor_info['vendor_selected_delivery_slot'] ) ); ?>">
        </div>
        <div class="order_data_column">
            <p class="form-field form-field-wide">
                <label for="dokan-delivery-admin-date-picker"><?php esc_html_e( 'Delivery date:', 'dokan' ); ?></label>
                <input type="text" id="dokan-delivery-admin-date-picker" class="date-picker" readonly
                    data-vendor_id="<?php echo esc_attr( $vendor_info['vendor_id'] ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'dokan_delivery_time' ) ); ?>"
                    placeholder="<?php esc_attr_e( 'Select delivery date', 'dokan' ); ?>" name="" maxlength="10"
                    value=""/>
                <input type="hidden" id="dokan_delivery_date_input" name="dokan_delivery_date" value="" />
            </p>
            <ul class="form-field form-field-wide">
                <label for="dokan-delivery-admin-time-slot-picker"><?php esc_html_e( 'Delivery time slot:', 'dokan' ); ?></label>
                <li class="wide" >
                    <select id="dokan-delivery-admin-time-slot-picker" style="width: 100%;" name="dokan_delivery_time_slot">
                        <option value=""><?php esc_html_e( 'Select time slot', 'dokan' ); ?></option>
                    </select>
                </li>
            </ul>
            <?php wp_nonce_field( 'dokan_delivery_admin_time_box_action', 'dokan_delivery_admin_time_box_nonce' ); ?>
        </div>
    </div>
</div>
