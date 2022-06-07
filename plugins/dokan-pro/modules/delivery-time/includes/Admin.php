<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime;

/**
 * Class Admin
 *
 * @since 3.3.0
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime
 */
class Admin {

    /**
     * Delivery time admin constructor
     *
     * @since 3.3.0
     */
    public function __construct() {
        // Hooks
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
        add_action( 'add_meta_boxes', [ $this, 'add_admin_delivery_time_meta_box' ] );
        add_action( 'save_post_shop_order', [ $this, 'save_admin_delivery_time_meta_box' ], 10, 1 );
    }

    /**
     * Enqueue scripts
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 'dokan-delivery-time-admin-script' );
    }

    /**
     * Adds admin delivery meta box to WC order details page
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function add_admin_delivery_time_meta_box() {
        $order_id = isset( $_GET['post'] ) ? $_GET['post'] : 0; //phpcs:ignore
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        if ( ! $order->get_meta( 'has_sub_order' ) ) {
            add_meta_box( 'dokan_delivery_time_fields', __( 'Dokan delivery time', 'dokan' ), [ $this, 'render_delivery_time_meta_box' ], 'shop_order', 'side', 'core' );
        }
    }

    /**
     * Load dokan admin template
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function render_delivery_time_meta_box() {
        $order_id = isset( $_GET['post'] ) ? $_GET['post'] : 0; //phpcs:ignore
        if ( 0 === $order_id ) {
            return;
        }

        $order     = wc_get_order( $order_id );
        $vendor_id = dokan_get_seller_id_by_order( $order_id );

        $vendor_selected_delivery_date = $order->get_meta( 'dokan_delivery_time_date' );
        $vendor_selected_delivery_slot = $order->get_meta( 'dokan_delivery_time_slot' );
        $store_location                = $order->get_meta( 'dokan_store_pickup_location' );

        $vendor_info = [];

        $vendor_info['vendor_id']                     = $vendor_id;
        $vendor_info['vendor_selected_delivery_date'] = '-';
        $vendor_info['vendor_selected_delivery_slot'] = '-';
        $vendor_info['vendor_delivery_slots']         = [];

        if ( ! empty( $vendor_selected_delivery_date ) && ! empty( $vendor_selected_delivery_slot ) ) {
            $current_date = dokan_current_datetime();
            $current_date = $current_date->modify( $vendor_selected_delivery_date );
            $day          = strtolower( trim( $current_date->format( 'l' ) ) );

            $vendor_delivery_options = get_user_meta( $vendor_id, '_dokan_vendor_delivery_time_settings', true );
            $vendor_order_per_slot   = (int) isset( $vendor_delivery_options['order_per_slot'][ $day ] ) ? $vendor_delivery_options['order_per_slot'][ $day ] : -1;
            $vendor_delivery_slots   = Helper::get_available_delivery_slots_by_date( $vendor_id, $vendor_order_per_slot, $vendor_selected_delivery_date );

            $vendor_info['vendor_id']                     = $vendor_id;
            $vendor_info['vendor_selected_delivery_date'] = $vendor_selected_delivery_date;
            $vendor_info['vendor_selected_delivery_slot'] = $vendor_selected_delivery_slot;
            $vendor_info['store_location']                = $store_location;
            $vendor_info['vendor_delivery_slots']         = $vendor_delivery_slots;
        }

        dokan_get_template_part(
            'admin/meta-box', '', [
                'is_delivery_time' => true,
                'vendor_info'      => $vendor_info,
            ]
        );
    }

    /**
     * Saves admin delivery time meta box args
     *
     * @since 3.3.0
     *
     * @param int $order_id
     *
     * @return void
     */
    public function save_admin_delivery_time_meta_box( $order_id ) {
        if ( ! isset( $_POST['dokan_delivery_admin_time_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_delivery_admin_time_box_nonce'] ) ), 'dokan_delivery_admin_time_box_action' ) ) {
            return;
        }

        if ( 0 === $order_id ) {
            return;
        }

        $delivery_date                              = isset( $_POST['dokan_delivery_date'] ) ? wc_clean( wp_unslash( $_POST['dokan_delivery_date'] ) ) : '';
        $delivery_time_slot                         = isset( $_POST['dokan_delivery_time_slot'] ) ? wc_clean( wp_unslash( $_POST['dokan_delivery_time_slot'] ) ) : '';
        $vendor_selected_current_delivery_date_slot = isset( $_POST['vendor_selected_current_delivery_date_slot'] ) ? wc_clean( wp_unslash( $_POST['vendor_selected_current_delivery_date_slot'] ) ) : '-';

        $data = [
            'order_id'                                   => $order_id,
            'delivery_date'                              => $delivery_date,
            'delivery_time_slot'                         => $delivery_time_slot,
            'vendor_selected_current_delivery_date_slot' => $vendor_selected_current_delivery_date_slot,
        ];

        Helper::update_delivery_time_date_slot( $data );
    }
}
