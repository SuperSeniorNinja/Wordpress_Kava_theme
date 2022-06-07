<?php

use WeDevs\Dokan\Cache;

/**
* Get vendors subscripton by orders
*
* @since 1.0.0
*
* @param Array $user_orders
*
* @return Array
*/
function dokan_vps_get_vendor_subscriptons_by_orders( $user_orders, $seller_id ) {
    $user_subscriptions = array();

    if ( $user_orders ) {
        foreach ( $user_orders as $order ) {
            $the_subscriptions = wcs_get_subscriptions_for_order( $order->order_id );
            foreach ( $the_subscriptions as $skey => $the_subscription ) {
                $subscription_products = $the_subscription->get_items();
                foreach ( $subscription_products as $pkey => $subscription_product ) {
                    if ( $seller_id === (int) get_post_field( 'post_author', $subscription_product->get_product_id() ) ) {
                        $user_subscriptions[ $skey ] = $the_subscription;
                    }
                }
            }
        }
        if ( $user_subscriptions ) {
            return $user_subscriptions;
        } else {
            return false;
        }
    }

    return false;
}

/**
 * Get all the orders from a specific seller
 *
 * @global Object $wpdb
 *
 * @param Integer $seller_id
 * @param String $status
 * @param String $order_date
 * @param Integer $limit
 * @param Integer $offset
 * @param Integer $customer_id
 *
 * @return Array
 */
function dokan_vps_get_seller_orders( $seller_id, $status = 'all', $order_date = null, $limit = 10, $offset = 0, $customer_id = null, $relation = null ) {
    // get all function arguments as key => value pairs
    $args = get_defined_vars();

    global $wpdb;

    $cache_group = "seller_order_data_{$seller_id}";
    $cache_key   = 'vps_orders_' . md5( wp_json_encode( $args ) );
    $orders      = Cache::get( $cache_key, $cache_group );

    if ( false === $orders ) {
        $join_meta                   = "LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID";
        $where_customer              = $customer_id ? sprintf( "pm.meta_key = '_customer_user' AND pm.meta_value = %d AND", $customer_id ) : '';
        $where_subscription_relation = $relation ? sprintf( "pm.meta_key = 'subscription_order_type' AND pm.meta_value = '%s' AND", $relation ) : '';
        $status_where                = ( $status === 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );
        $date_query                  = ( $order_date ) ? $wpdb->prepare( ' AND DATE( p.post_date ) = %s', $order_date ) : '';
        // @codingStandardsIgnoreStart
        $orders = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT do.order_id, p.post_date
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts AS p ON do.order_id = p.ID
                {$join_meta}
                WHERE
                        do.seller_id = %d AND
                        {$where_customer}
                        {$where_subscription_relation}
                        p.post_status != 'trash'
                        {$date_query}
                        {$status_where}
                GROUP BY do.order_id
                ORDER BY p.post_date DESC
                LIMIT %d, %d", $seller_id, $offset, $limit
            )
        );
        // @codingStandardsIgnoreEnd

        Cache::set( $cache_key, $orders, $cache_group );
    }

    return $orders;
}


/**
 * Get the orders total from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_vps_get_seller_orders_number( $seller_id, $status = 'all', $relation = null ) {
    // get all function arguments as key => value pairs
    $args = get_defined_vars();

    global $wpdb;

    $cache_group = "seller_order_data_{$seller_id}";
    $cache_key   = 'vps_orders_count_' . md5( wp_json_encode( $args ) );
    $count       = Cache::get( $cache_key, $cache_group );

    if ( false === $count ) {
        $join_meta = "LEFT JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID";
        $where_subscription_relation = $relation ? sprintf( "pm.meta_key = 'subscription_order_type' AND pm.meta_value = '%s' AND", $relation ) : '';
        $status_where = ( $status === 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );

        // @codingStandardsIgnoreStart
        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(do.order_id) as count
                        FROM {$wpdb->prefix}dokan_orders AS do
                        LEFT JOIN $wpdb->posts AS p ON do.order_id = p.ID
                        {$join_meta}
                        WHERE
                                do.seller_id = %d AND
                                {$where_subscription_relation}
                                p.post_status != 'trash'
                                {$status_where}", $seller_id
            )
        );
        // @codingStandardsIgnoreEnd

        Cache::set( $cache_key, $count, $cache_group );
    }

    return $count;
}

/**
* Get translated string of order status
*
* @param string $status
* @return string
*/
function dokan_vps_get_subscription_status_translated( $status ) {
    switch ( $status ) {
        case 'completed':
        case 'wc-completed':
            return __( 'Completed', 'dokan' );

        case 'active':
        case 'wc-active':
            return __( 'Active', 'dokan' );

        case 'expired':
        case 'wc-expired':
            return __( 'Expired', 'dokan' );

        case 'pending':
        case 'wc-pending':
            return __( 'Pending Payment', 'dokan' );

        case 'on-hold':
        case 'wc-on-hold':
            return __( 'On-hold', 'dokan' );

        case 'processing':
        case 'wc-processing':
            return __( 'Processing', 'dokan' );

        case 'refunded':
        case 'wc-refunded':
            return __( 'Refunded', 'dokan' );

        case 'cancelled':
        case 'wc-cancelled':
            return __( 'Cancelled', 'dokan' );

        case 'failed':
        case 'wc-failed':
            return __( 'Failed', 'dokan' );

        case 'pending-cancel':
        case 'wc-pending-cancel':
            return __( 'Pending Cancellation', 'dokan' );

        default:
            return apply_filters( 'dokan_vps_get_order_status_translated', '', $status );
    }
}

/**
* Get bootstrap label class based on order status
*
* @param string $status
* @return string
*/
function dokan_vps_get_subscription_status_class( $status ) {
    switch ( $status ) {
        case 'completed':
        case 'wc-completed':
        case 'active':
        case 'wc-active':
            return 'success';

        case 'pending-cancel':
        case 'wc-pending-cancel':
        case 'pending':
        case 'wc-expired':
        case 'expired':
        case 'wc-failed':
        case 'failed':
        case 'wc-pending':
            return 'danger';

        case 'on-hold':
        case 'wc-on-hold':
            return 'warning';

        case 'processing':
        case 'wc-processing':
            return 'info';

        case 'refunded':
        case 'wc-cancelled':
        case 'cancelled':
        case 'wc-refunded':
            return 'default';

        default:
            return apply_filters( 'dokan_get_order_status_class', '', $status );
    }
}

/**
 * Display Date format for subscriptions
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_vps_get_date_content( $subscription, $column ) {
    $date_type_map = array( 'last_payment_date' => 'last_order_date_created' );
    $date_type     = array_key_exists( $column, $date_type_map ) ? $date_type_map[ $column ] : $column;

    // @codingStandardsIgnoreStart
    if ( 0 == $subscription->get_time( $date_type, 'gmt' ) ) {
        $column_content = '-';
    } else {
        $column_content = sprintf( '<time class="%s" title="%s">%s</time>', esc_attr( $column ), esc_attr( date( __( 'Y/m/d g:i:s A', 'woocommerce-subscriptions' ) , $subscription->get_time( $date_type, 'site' ) ) ), esc_html( $subscription->get_date_to_display( $date_type ) ) );

        if ( 'next_payment_date' == $column && $subscription->payment_method_supports( 'gateway_scheduled_payments' ) && ! $subscription->is_manual() && $subscription->has_status( 'active' ) ) {
            $column_content .= '<div class="woocommerce-help-tip" data-tip="' . esc_attr__( 'This date should be treated as an estimate only. The payment gateway for this subscription controls when payments are processed.', 'woocommerce-subscriptions' ) . '"></div>';
        }
    }
    // @codingStandardsIgnoreEnd

    return $column_content;
}

/**
 * Get the subscriptions or count for a specific seller.
 *
 * @since 3.3.6
 *
 * @global wpdb $wpdb
 *
 * @param array $args
 *
 * @return array|int
 */
function dokan_vps_get_seller_subscriptions( $args = array() ) {
    global $wpdb;

    $default = array(
        'seller_id'   => dokan_get_current_user_id(),
        'status'      => 'all',
        'order_date'  => null,
        'limit'       => 0,
        'offset'      => 0,
        'customer_id' => null,
        'relation'    => null,
        'return'      => 'subscriptions',
    );

    $args                = wp_parse_args( $args, $default );
    $cache_group         = "seller_order_data_{$args['seller_id']}";
    $cache_key           = 'seller_subscriptions_info_' . md5( wp_json_encode( $args ) );
    $subscriptions_info  = Cache::get( $cache_key, $cache_group );
    $subscriptions_array = array();

    if ( false === $subscriptions_info ) {
        $status_where   = ( $args['status'] === 'all' ) ? '' : $wpdb->prepare( ' AND dokan_orders.order_status = %s', $args['status'] );
        $date_query     = ( $args['order_date'] ) ? $wpdb->prepare( ' AND DATE( subscriptions.post_date ) = %s', $args['order_date'] ) : '';
        $where_customer = $args['customer_id'] ? $wpdb->prepare( ' AND postmeta.meta_key = %s AND postmeta.meta_value = %d', '_customer_user', $args['customer_id'] ) : '';
        $join_customer  = $args['customer_id'] ? "INNER JOIN $wpdb->postmeta as postmeta on subscriptions.ID = postmeta.post_id" : '';
        $limit_query    = $args['limit'] ? $wpdb->prepare( 'ORDER BY subscriptions.ID DESC LIMIT %d, %d', $args['offset'], $args['limit'] ) : '';

        // phpcs:disable
        $subscriptions_query = $wpdb->prepare(
            "SELECT subscriptions.ID FROM $wpdb->posts as subscriptions
                INNER JOIN $wpdb->posts as orders ON subscriptions.post_parent = orders.ID
                INNER JOIN {$wpdb->prefix}dokan_orders as dokan_orders on dokan_orders.order_id = subscriptions.post_parent
                {$join_customer}
                WHERE
                    dokan_orders.seller_id = %d AND
                    subscriptions.post_type = 'shop_subscription' AND
                    orders.post_type = 'shop_order' AND
                    orders.post_status != 'trash' AND
                    subscriptions.post_parent = orders.ID
                    {$where_customer}
                    {$date_query} {$status_where}
                GROUP BY subscriptions.ID
                {$limit_query}
                ", $args['seller_id']
        );

        $subscriptions_count_query = "SELECT COUNT(*) as count FROM ( {$subscriptions_query} ) AS subscription_query";
        $subscriptions_info        = ( $args['return'] === 'subscriptions' ) ? $wpdb->get_results( $subscriptions_query ) : $wpdb->get_var( $subscriptions_count_query );
        // phpcs:enable

        Cache::set( $cache_key, $subscriptions_info, $cache_group );
    }

    if ( $args['return'] === 'subscriptions' ) {
        foreach ( $subscriptions_info as $subscription ) {
            $subscriptions_array[ $subscription->ID ] = wcs_get_subscription( $subscription->ID );
        }

        return $subscriptions_array;
    }
    return absint( $subscriptions_info );
}

