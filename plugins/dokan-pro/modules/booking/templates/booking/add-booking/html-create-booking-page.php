<?php
/**
 *  Dokan Create Booking Page
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
    <?php wc_print_notices(); ?>

    <p><?php esc_html_e( 'You can create a new booking for a customer here. This form will create a booking for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'dokan' ); ?></p>


    <form method="POST" data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-booked-day-blocks' ) ); ?>">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row">
                    <label for="customer_id"><?php esc_html_e( 'Customer', 'dokan' ); ?></label>
                </th>
                <td>
                    <select name="customer_id" id="customer_id" class="wc-customer-search" data-placeholder="<?php esc_attr_e( 'Guest', 'dokan' ); ?>" data-allow_clear="true">
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="bookable_product_id"><?php esc_html_e( 'Bookable Product', 'dokan' ); ?></label>
                </th>
                <td>
                    <select id="bookable_product_id" name="bookable_product_id" class="chosen_select" style="width: 300px">
                        <option value=""><?php esc_html_e( 'Select a bookable product...', 'dokan' ); ?></option>
                        <?php foreach ( \Dokan_WC_Booking_Helper::get_vendor_booking_products() as $product ) : ?>
                            <option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( sprintf( '%s (#%s)', $product->get_name(), $product->get_id() ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="create_order"><?php esc_html_e( 'Create Order', 'dokan' ); ?></label>
                </th>
                <td>
                    <p>
                        <label>
                            <input type="radio" name="booking_order" value="new" class="checkbox" />
                            <?php esc_html_e( 'Create a new corresponding order for this new booking. Please note - the booking will not be active until the order is processed/completed.', 'dokan' ); ?>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="radio" name="booking_order" value="existing" class="checkbox" />
                            <?php esc_html_e( 'Assign this booking to an existing order with this ID:', 'dokan' ); ?>
                            <?php if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) : ?>
                                <input type="text" name="booking_order_id" value="" class="text" size="15" />
                            <?php else : ?>
                                <input type="number" name="booking_order_id" value="" class="text" size="10" />
                            <?php endif; ?>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="radio" name="booking_order" value="" class="checkbox" checked="checked" />
                            <?php esc_html_e( 'Don\'t create an order for this booking.', 'dokan' ); ?>
                        </label>
                    </p>
                </td>
            </tr>
            <?php do_action( 'woocommerce_bookings_after_create_booking_page' ); ?>
            <tr valign="top">
                <th scope="row">&nbsp;</th>
                <td>
                    <input type="submit" name="create_booking" class="button-primary" value="<?php esc_attr_e( 'Next', 'dokan' ); ?>" />
                    <?php wp_nonce_field( 'create_booking_notification', 'add_booking_nonce' ); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
