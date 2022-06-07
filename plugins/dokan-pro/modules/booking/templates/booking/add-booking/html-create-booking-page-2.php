<?php
/**
 *  Dokan Create Booking Page 2
 *
 *  @since 3.3.6
 *
 *  @package dokan
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap woocommerce">
    <h2><?php esc_html_e( 'Add Booking', 'dokan' ); ?></h2>

    <?php $this->show_errors(); ?>

    <form method="POST" data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-booked-day-blocks' ) ); ?>" id="wc-bookings-booking-form">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e( 'Booking Data', 'dokan' ); ?></label>
                </th>
                <td>
                    <div class="wc-bookings-booking-form">
                        <?php $booking_form->output(); ?>
                        <div class="wc-bookings-booking-cost" style="display:none"></div>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">&nbsp;</th>
                <td>
                    <input type="submit" name="create_booking_2" class="button-primary" value="<?php esc_attr_e( 'Add Booking', 'dokan' ); ?>" />
                    <input type="hidden" name="customer_id" value="<?php echo esc_attr( $customer_id ); ?>" />
                    <input type="hidden" name="bookable_product_id" value="<?php echo esc_attr( $bookable_product_id ); ?>" />
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $bookable_product_id ); ?>" />
                    <input type="hidden" name="booking_order" value="<?php echo esc_attr( $booking_order ); ?>" />
                    <?php wp_nonce_field( 'create_booking_notification', 'add_booking_nonce' ); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
