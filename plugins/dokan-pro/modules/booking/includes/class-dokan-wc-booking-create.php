<?php
/**
 * Create new bookings page.
 *
 * @since 3.3.6
 */
class Dokan_WC_Bookings_Create {

    /**
     * Stores errors.
     *
     * @var array
     */
    private $errors = array();

    /**
     * Output the form.
     *
     * @version  3.3.6
     */
    public function output() {
        $this->errors = array();
        $step         = 1;

        try {
            if ( ! empty( $_POST['create_booking'] ) ) {
                if ( ! isset( $_POST['add_booking_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['add_booking_nonce'] ) ), 'create_booking_notification' ) ) {
                    dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'Nonce verification failed', 'dokan' ) ) );
                    return;
                }

                $customer_id         = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
                $bookable_product_id = absint( $_POST['bookable_product_id'] );
                $booking_order       = wc_clean( $_POST['booking_order'] );

                if ( ! $bookable_product_id ) {
                    throw new Exception( __( 'Please choose a bookable product', 'dokan' ) );
                }

                if ( 'existing' === $booking_order ) {

                    if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
                        $order_id = WC_Seq_Order_Number_Pro::find_order_by_order_number( wc_clean( $_POST['booking_order_id'] ) );
                    } else {
                        $order_id = absint( $_POST['booking_order_id'] );
                    }

                    $vendor_id = dokan_get_current_user_id();

                    if ( ! dokan_is_user_seller( $vendor_id ) ) {
                        throw new Exception( __( 'Sorry! You can not add the booking!', 'dokan' ) );
                    }

                    if ( ! dokan_is_seller_has_order( $vendor_id, $order_id ) ) {
                        throw new Exception( __( 'The order is not associated with your account!', 'dokan' ) );
                    }

                    $booking_order = $order_id;

                    if ( ! $booking_order || get_post_type( $booking_order ) !== 'shop_order' ) {
                        throw new Exception( __( 'Invalid order ID provided', 'dokan' ) );
                    }
                }

                $step ++;
                $product      = wc_get_product( $bookable_product_id );
                $booking_form = new WC_Booking_Form( $product );

            } elseif ( ! empty( $_POST['create_booking_2'] ) ) {
                if ( ! isset( $_POST['add_booking_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['add_booking_nonce'] ) ), 'create_booking_notification' ) ) {
                    dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'Nonce verification failed', 'dokan' ) ) );
                    return;
                }

                WC()->cart->empty_cart();

                $customer_id         = absint( $_POST['customer_id'] );
                $bookable_product_id = absint( $_POST['bookable_product_id'] );
                $booking_order       = wc_clean( $_POST['booking_order'] );
                $product             = wc_get_product( $bookable_product_id );
                $booking_data        = wc_bookings_get_posted_data( $_POST, $product );
                $cost                = WC_Dokan_WC_Booking_Cost_Calculation::calculate_booking_cost( $booking_data, $product );
                $booking_cost        = $cost && ! is_wp_error( $cost ) ? number_format( $cost, 2, '.', '' ) : 0;
                $create_order        = false;
                $order_id            = 0;
                $item_id             = 0;

                if ( wc_prices_include_tax() ) {
                    $base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
                    $base_taxes     = WC_Tax::calc_tax( $booking_cost, $base_tax_rates, true );
                    $booking_cost   = $booking_cost - array_sum( $base_taxes );
                }

                if ( empty( $booking_data['_year'] ) || empty( $booking_data['_month'] || empty( $booking_data['_day'] ) ) ) {
                    throw new Exception( __( 'Please choose a booking date.', 'dokan' ) );
                }

                $props = array(
                    'customer_id'   => $customer_id,
                    'product_id'    => $product->get_id(),
                    'resource_id'   => isset( $booking_data['_resource_id'] ) ? $booking_data['_resource_id'] : '',
                    'person_counts' => $booking_data['_persons'],
                    'cost'          => $booking_cost,
                    'start'         => $booking_data['_start_date'],
                    'end'           => $booking_data['_end_date'],
                    'all_day'       => $booking_data['_all_day'] ? true : false,
                );

                if ( 'new' === $booking_order ) {
                    $create_order = true;
                    $order_id     = $this->create_order( $booking_cost, $customer_id );

                    if ( ! $order_id ) {
                        throw new Exception( __( 'Error: Could not create order', 'dokan' ) );
                    }
                } elseif ( $booking_order > 0 ) {
                    $order_id = absint( $booking_order );

                    if ( ! $order_id || get_post_type( $order_id ) !== 'shop_order' ) {
                        throw new Exception( __( 'Invalid order ID provided', 'dokan' ) );
                    }

                    $order = new WC_Order( $order_id );

                    $order->set_total( $order->get_total( 'edit' ) + $booking_cost );
                    $order->save();

                    do_action( 'woocommerce_bookings_create_booking_page_add_order_item', $order_id );
                }

                if ( $order_id ) {
                    $item_id  = wc_add_order_item( $order_id, array(
                        'order_item_name' => $product->get_title(),
                        'order_item_type' => 'line_item',
                    ) );

                    if ( ! $item_id ) {
                        throw new Exception( __( 'Error: Could not create item', 'dokan' ) );
                    }

                    if ( ! empty( $customer_id ) ) {
                        $order = wc_get_order( $order_id );
                        $keys  = array(
                            'first_name',
                            'last_name',
                            'company',
                            'address_1',
                            'address_2',
                            'city',
                            'state',
                            'postcode',
                            'country',
                        );
                        $types = array( 'shipping', 'billing' );

                        foreach ( $types as $type ) {
                            $address = array();

                            foreach ( $keys as $key ) {
                                $address[ $key ] = (string) get_user_meta( $customer_id, $type . '_' . $key, true );
                            }
                            $order->set_address( $address, $type );
                        }
                    }

                    // Add line item meta
                    wc_add_order_item_meta( $item_id, '_qty', 1 );
                    wc_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
                    wc_add_order_item_meta( $item_id, '_product_id', $product->get_id() );
                    wc_add_order_item_meta( $item_id, '_variation_id', '' );
                    wc_add_order_item_meta( $item_id, '_line_subtotal', $booking_cost );
                    wc_add_order_item_meta( $item_id, '_line_total', $booking_cost );
                    wc_add_order_item_meta( $item_id, '_line_tax', 0 );
                    wc_add_order_item_meta( $item_id, '_line_subtotal_tax', 0 );

                    do_action( 'woocommerce_bookings_create_booking_page_add_order_item', $order_id );
                }
                // Calculate the order totals with taxes.
                $order = wc_get_order( $order_id );

                // Getting vendor ID
                $vendor_id = dokan_get_current_user_id();

                if ( is_a( $order, 'WC_Order' ) ) {
                    $order->calculate_totals( wc_tax_enabled() );
                }

                // Create the booking itself
                $new_booking = new WC_Booking( $props );
                $new_booking->set_order_id( $order_id );
                $new_booking->set_order_item_id( $item_id );

                if ( 0 === $customer_id ) {
                    $new_booking->set_customer_id( 0 );
                }

                $new_booking->set_status( $create_order ? 'unpaid' : 'confirmed' );
                $new_booking->save();

                if ( $order ) {
                    // Assigning the vendor to the order
                    $order->update_meta_data( '_dokan_vendor_id', $vendor_id );
                    $order->save_meta_data();

                    do_action( 'dokan_checkout_update_order_meta', $order_id, $vendor_id );
                }

                do_action( 'woocommerce_bookings_created_manual_booking', $new_booking );

                wc_clear_notices();
                wc_add_notice( __( 'The booking has been added successfully.', 'dokan' ), 'success' );

                $this->redirect_to_add_booking();
            }
        } catch ( Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        switch ( $step ) {
            case 1:
                include( DOKAN_WC_BOOKING_TEMPLATE_PATH . '/booking/add-booking/html-create-booking-page.php' );
                break;
            case 2:
                add_filter( 'wc_get_template', array( $this, 'use_default_form_template' ), 10, 5 );
                include( DOKAN_WC_BOOKING_TEMPLATE_PATH . '/booking/add-booking/html-create-booking-page-2.php' );
                remove_filter( 'wc_get_template', array( $this, 'use_default_form_template' ), 10 );
                break;
        }
    }

    /**
     * Create order.
     *
     * @param  float $total
     * @param  int $customer_id
     * @return int
     *
     * @since 3.3.6
     */
    public function create_order( $total, $customer_id ) {
        $order = new WC_Order();
        $order->set_customer_id( $customer_id );
        $order->set_total( $total );
        $order->set_created_via( 'bookings' );
        $order_id = $order->save();

        do_action( 'woocommerce_new_booking_order', $order_id );

        return $order_id;
    }

    /**
     * Output any errors
     */
    public function show_errors() {
        foreach ( $this->errors as $error ) {
            echo '<div class="error dokan-error"><p>' . esc_html( $error ) . '</p></div>';
        }
    }

    /**
     * Use default template form from the extension.
     *
     * This prevents any overridden template via theme being used in
     * create booking screen.
     *
     * @since WooCommerce 1.9.11
     * @see https://github.com/woothemes/woocommerce-bookings/issues/773
     */
    public function use_default_form_template( $located, $template_name, $args, $template_path, $default_path ) {
        if ( 'woocommerce-bookings' === $template_path ) {
            $located = $default_path . $template_name;
        }
        return $located;
    }

    /**
     * Redirects to add booking
     *
     * @since 3.3.6
     */
    public function redirect_to_add_booking() {
        $redirect_url = dokan_get_navigation_url( 'booking/my-bookings' );

        ?>
        <script type="text/javascript">
            function pageRedirect() {
                window.location.replace("<?php echo esc_url( $redirect_url ); ?>");
            }
            setTimeout("pageRedirect()", 1500);
        </script>
        <?php
    }
}
