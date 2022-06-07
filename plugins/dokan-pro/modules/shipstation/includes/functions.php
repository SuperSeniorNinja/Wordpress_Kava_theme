<?php

use WeDevs\Dokan\Cache;

/**
 * Include Dokan ShipStation template
 *
 * @since 1.0.0
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_shipstation_get_template( $name, $args = [] ) {
    dokan_get_template( "$name.php", $args, DOKAN_SHIPSTATION_VIEWS, trailingslashit( DOKAN_SHIPSTATION_VIEWS ) );
}

/**
 * Get Order data for a seller
 *
 * @since 1.0.0
 *
 * @param int   $seller_id
 * @param array $args
 *
 * @return array
 */
function dokan_shipstation_get_orders( $seller_id, $args = array() ) {
    global $wpdb;

    $current_time = dokan_current_datetime();

    $defaults = array(
        'count' => false,
        'start_date' => $current_time->setTime( 0, 0, 0 )->format( 'Y-m-d H:i:s' ),
        'end_date' => $current_time->format( 'Y-m-d H:i:s' ),
        'status' => null,
        'page' => 1,
        'fields' => array( 'do.*', 'p.post_date_gmt' ),
        'limit' => DOKAN_SHIPSTATION_EXPORT_LIMIT * ( $args['page'] - 1 ),
        'offset' => DOKAN_SHIPSTATION_EXPORT_LIMIT,
    );

    $args = wp_parse_args( $args, $defaults );

    $cache_group = "seller_order_data_{$seller_id}";
    $cache_key   = "shipstation_orders_" . md5( wp_json_encode( $args ) );
    $orders      = Cache::get( $cache_key, $cache_group );

    if ( false === $orders ) {
        $select = implode( ', ', $args['fields'] );

        $where = $wpdb->prepare(
            'do.seller_id = %d AND p.post_status != %s', $seller_id, 'trash'
        );

        if ( is_array( $args['status'] ) ) {
            $where .= sprintf( " AND order_status IN ('%s')", implode( "', '", $args['status'] ) );
        } else if ( $args['status'] ) {
            $where .= $wpdb->prepare( ' AND order_status = %s', $args['status'] );
        }

        $where .= $wpdb->prepare( ' AND p.post_date_gmt >= %s AND p.post_date_gmt <= %s', $args['start_date'], $args['end_date'] );

        $select = ! $args['count'] ? "SELECT $select" : "SELECT COUNT(p.ID) as count";
        $from = " FROM {$wpdb->prefix}dokan_orders AS do";
        $join = " LEFT JOIN $wpdb->posts p ON do.order_id = p.ID";
        $where = " WHERE $where";

        if ( ! $args['count'] ) {
            $group_by = ' GROUP BY do.order_id';
            $order_by = ' ORDER BY p.post_date_gmt ASC';
            $limit = $wpdb->prepare( ' LIMIT %d, %d', $args['limit'], $args['offset'] );
        } else {
            $group_by = '';
            $order_by = '';
            $limit = '';
        }

        $sql = $select . $from . $join . $where . $group_by . $order_by . $limit;

        $orders = $wpdb->get_results( $sql );

        Cache::set( $cache_key, $orders, $cache_group, HOUR_IN_SECONDS * 2 );
    }

    return $orders;
}
