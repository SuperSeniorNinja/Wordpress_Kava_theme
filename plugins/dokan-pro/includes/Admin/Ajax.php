<?php

namespace WeDevs\DokanPro\Admin;

/**
 * Ajax handling for Dokan in Admin area
 *
 * @since 2.2
 *
 * @author weDevs <info@wedevs.com>
 */
class Ajax {

    /**
     * Load automatically all actions
     */
    public function __construct() {
        add_action( 'wp_ajax_regen_sync_table', array( $this, 'regen_sync_order_table' ) );
        add_action( 'wp_ajax_check_duplicate_suborders', array( $this, 'check_duplicate_suborders' ) );
        add_action( 'wp_ajax_dokan-toggle-module', array( $this, 'toggle_module' ), 10 );
    }

    /**
     * Handle sync order table via ajax
     *
     * @return json success|error|data
     */
    public function regen_sync_order_table() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan', '403' ) );
        }

        global $wpdb;

        $limit        = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
        $offset       = isset( $_POST['offset'] ) ? $_POST['offset'] : 0;
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->dokan_orders );

            $total_orders = $wpdb->get_var( "SELECT count(ID)
                FROM $wpdb->posts
                WHERE post_type = 'shop_order'" );

            $parent_orders = $wpdb->get_var( "SELECT count(ID)
                FROM {$wpdb->posts} as p
                LEFT JOIN {$wpdb->postmeta} as m ON p.ID = m.post_id
                WHERE m.meta_key = 'has_sub_order' and p.post_type = 'shop_order' " );
            $total_orders = $total_orders - $parent_orders;
        }

        $sql = "SELECT ID FROM $wpdb->posts
                WHERE post_type = 'shop_order'
                LIMIT %d,%d";

        $orders = $wpdb->get_results( $wpdb->prepare($sql, $offset * $limit, $limit ) );

        if ( $orders ) {
            foreach ( $orders as $order) {
                dokan_sync_order_table( $order->ID );
            }

            $sql       = "SELECT * FROM " . $wpdb->dokan_orders;
            $generated = $wpdb->get_results( $sql );
            $done      = count( $generated );

            wp_send_json_success( array(
                'offset'       => $offset + 1,
                'total_orders' => $total_orders,
                'done'         => $done,
                'message'      => sprintf( __( '%d orders sync completed out of %d', 'dokan' ), $done, $total_orders )
            ) );
        } else {
            $dashboard_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dokan' ), __( 'Go to Dashboard &rarr;', 'dokan' ) );
            wp_send_json_success( array(
                'offset'  => 0,
                'done'    => 'All',
                'message' => sprintf( __( 'All orders has been synchronized. %s', 'dokan' ), $dashboard_link )
            ) );
        }
    }

    /**
     * Remove duplicate sub-orders if found
     *
     * @since 2.4.4
     *
     * @return json success|error|data
     */
    public function check_duplicate_suborders(){

        if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'check_duplicate_suborders' ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan', '403' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan', '403' ) );
        }

        if ( session_id() == '' ) {
            session_start();
        }

        global $wpdb;

        $limit        = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
        $offset       = isset( $_POST['offset'] ) ? $_POST['offset'] : 0;
        $prev_done    = isset( $_POST['done'] ) ? $_POST['done'] : 0;
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            unset( $_SESSION['dokan_duplicate_order_ids'] );
            $total_orders = $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts AS p
                LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
                WHERE post_type = 'shop_order' AND m.meta_key = 'has_sub_order'" );
        }

        $sql = "SELECT ID FROM $wpdb->posts AS p
        LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
        WHERE post_type = 'shop_order' AND m.meta_key = 'has_sub_order'
        LIMIT %d,%d";

        $orders           = $wpdb->get_results( $wpdb->prepare( $sql, $offset * $limit, $limit ) );
        $duplicate_orders = isset( $_SESSION['dokan_duplicate_order_ids'] ) ? $_SESSION['dokan_duplicate_order_ids'] : array();

        if ( $orders ) {
            foreach ( $orders as $order ) {

                $sellers_count = count( dokan_get_sellers_by( $order->ID ) );
                $sub_order_ids = dokan_get_suborder_ids_by( $order->ID );

                if ( $sellers_count < count( $sub_order_ids ) ) {
                    $duplicate_orders = array_merge( array_slice( $sub_order_ids, $sellers_count ), $duplicate_orders );
                }
            }

            if ( count( $duplicate_orders ) ) {
                $_SESSION['dokan_duplicate_order_ids'] = $duplicate_orders;
            }

            $done = $prev_done + count($orders);

            wp_send_json_success( array(
                'offset'       => $offset + 1,
                'total_orders' => $total_orders,
                'done'         => $done,
                'message'      => sprintf( __( '%d orders checked out of %d', 'dokan' ), $done, $total_orders )
            ) );

        } else {

            if( count( $duplicate_orders ) ) {
               wp_send_json_success( array(
                    'offset'  => 0,
                    'done'    => 'All',
                    'message' => sprintf( __( 'All orders are checked and we found some duplicate orders', 'dokan' ) ),
                    'duplicate' => true
                ) );
            }

            $dashboard_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dokan' ), __( 'Go to Dashboard &rarr;', 'dokan' ) );

            wp_send_json_success( array(
                    'offset'  => 0,
                    'done'    => 'All',
                    'message' => sprintf( __( 'All orders are checked and no duplicate was found. %s', 'dokan' ), $dashboard_link )
            ) );
        }
    }

    /**
    * Toggle module
    *
    * @since 2.9.0
    *
    * @return void
    **/
    public function toggle_module() {
        if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], 'dokan-admin-nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        $module = isset( $_POST['module'] ) ? sanitize_text_field( $_POST['module'] ) : '';
        $type   = isset( $_POST['type'] ) ? $_POST['type'] : '';

        if ( ! $module ) {
            wp_send_json_error( __( 'Invalid module provided', 'dokan' ) );
        }

        if ( ! in_array( $type, array( 'activate', 'deactivate' ) ) ) {
            wp_send_json_error( __( 'Invalid request type', 'dokan' ) );
        }

        $module_data = dokan_pro_get_module( $module );

        if ( 'activate' == $type ) {
            $status = dokan_pro_activate_module( $module );

            if ( is_wp_error( $status ) ) {
                wp_send_json_error( array(
                    'error' => $status->get_error_code(),
                    'message' => $status->get_error_message()
                ) );
            }

            $message = __( 'Activated', 'dokan' );
        } else {
            dokan_pro_deactivate_module( $module );
            $message = __( 'Deactivated', 'dokan' );
        }

        wp_send_json_success( $message );
    }
}
