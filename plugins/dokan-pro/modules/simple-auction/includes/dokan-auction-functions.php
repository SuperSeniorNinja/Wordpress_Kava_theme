<?php

/**
 * Common functions for dokan auction module.
 *
 * Loaded all functions related to auction
 */

use WeDevs\Dokan\Cache;

/**
 * Check if the seller is enabled
 *
 * @since 1.0.0
 *
 * @param int $user_id
 *
 * @return boolean
 */
function dokan_is_seller_auction_disabled( $user_id ) {
    $auction = get_user_meta( $user_id, 'dokan_disable_auction', true );

    return 'yes' === $auction ? true : false;
}

/**
 * Auction post status counting.
 *
 * @since  1.0.0
 *
 * @param  string $post_type
 * @param  integer $user_id
 *
 * @return array
 */
function dokan_count_auction_posts( $post_type, $user_id ) {
    global $wpdb;

    $cache_group = "auction_products_{$user_id}";
    $cache_key   = "count_auction_{$post_type}";
    $counts      = Cache::get( $cache_key, $cache_group );

    if ( false !== $counts ) {
        return $counts;
    }

    $term = get_term_by( 'slug', 'auction', 'product_type' );

    $query   = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} INNER JOIN {$wpdb->prefix}term_relationships ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id) AND ( {$wpdb->prefix}term_relationships.term_taxonomy_id IN ( %s ) ) AND {$wpdb->prefix}posts.post_author = %s AND {$wpdb->prefix}posts.post_type = %s GROUP BY post_status";
    $results = $wpdb->get_results( $wpdb->prepare( $query, $term->term_id, $user_id, $post_type ), ARRAY_A );
    $counts  = array_fill_keys( get_post_stati(), 0 );

    $total = 0;
    foreach ( $results as $row ) {
        $counts[ $row['post_status'] ] = (int) $row['num_posts'];
        $total += (int) $row['num_posts'];
    }

    $counts['total'] = $total;
    $counts          = (object) $counts;

    Cache::set( $cache_key, $counts, $cache_group );

    return $counts;
}

/**
 * Auction listing status filter
 *
 * @since  1.0.0
 *
 * @return void
 */
function dokan_auction_product_listing_status_filter() {
    $permalink    = dokan_get_navigation_url( 'auction' );
    $status_class = isset( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : 'all';
    $post_counts  = dokan_count_auction_posts( 'product', dokan_get_current_user_id() );
    ?>
    <ul class="dokan-listing-filter dokan-left subsubsub">
        <li<?php echo 'all' === $status_class ? ' class="active"' : ''; ?>>
            <a href="<?php echo esc_url( $permalink ); ?>"><?php printf( __( 'All (%d)', 'dokan' ), $post_counts->total - $post_counts->trash ); ?></a>
        </li>
        <li<?php echo 'publish' === $status_class ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'publish' ), $permalink ); ?>"><?php printf( __( 'Online (%d)', 'dokan' ), $post_counts->publish ); ?></a>
        </li>
        <li<?php echo 'pending' === $status_class ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'pending' ), $permalink ); ?>"><?php printf( __( 'Pending Review (%d)', 'dokan' ), $post_counts->pending ); ?></a>
        </li>
        <li<?php echo 'draft' === $status_class ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'draft' ), $permalink ); ?>"><?php printf( __( 'Draft (%d)', 'dokan' ), $post_counts->draft ); ?></a>
        </li>
    </ul> <!-- .post-statuses-filter -->
    <?php
}

/**
 * Gets auction activity for a specific vendor,
 * pass $count = true to get the count of auction activity
 *
 * @param bool $count (Optional)
 *
 * @since 3.3.9
 *
 * @return array|integer
 */
function dokan_auction_get_activity( $count = false ) {
    global $wpdb;

    $vendor_id = dokan_get_current_user_id();

    // Early return if the user is not vendor
    if ( ! dokan_is_user_seller( $vendor_id ) ) {
        return [];
    }

    $date_from_filter = '';
    $search_query = '';

    // Pagination params
    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $limit   = 10;
    $offset  = ( $pagenum - 1 ) * $limit;

    // Nonce check for filter params
    $nonce_check = isset( $_GET['auction_activity_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['auction_activity_nonce'] ) ), 'dokan-auction-activity' );

    // Getting filter params
    $date_from = $nonce_check && isset( $_GET['_auction_dates_from'] ) ? esc_sql( wp_unslash( $_GET['_auction_dates_from'] ) ) : false;
    $date_to   = $nonce_check && isset( $_GET['_auction_dates_to'] ) ? esc_sql( wp_unslash( $_GET['_auction_dates_to'] ) ) : false;

    // Getting search param
    $search_string = isset( $_GET['auction_activity_search'] ) ? esc_sql( wp_unslash( $_GET['auction_activity_search'] ) ) : false;

    // Handling limit query
    $limit_query = $count ? '' : $wpdb->prepare( 'LIMIT %d, %d', $offset, $limit );

    // Handling date range query
    if ( $date_from && $date_to ) {
        $date_from_filter = $wpdb->prepare( ' AND date BETWEEN CAST( %s AS DATETIME ) AND CAST( %s AS DATETIME )', $date_from, $date_to );
    } elseif ( $date_to ) {
        $date_from_filter = $wpdb->prepare( ' AND date <= CAST( %s AS DATETIME )', $date_to );
    } elseif ( $date_from ) {
        $date_from_filter = $wpdb->prepare( ' AND date >= CAST( %s AS DATETIME )', $date_from );
    }

    // Handling search query
    if ( $search_string ) {
        $like         = '%' . $wpdb->esc_like( $search_string ) . '%';
        $search_query = $wpdb->prepare( "AND ( `{$wpdb->users}`.user_nicename LIKE %s OR `{$wpdb->posts}`.post_title LIKE %s )", $like, $like );
    }

    $cache_group = "auction_activities_{$vendor_id}";

    if ( $count ) {
        $query = $wpdb->prepare(
            "SELECT COUNT(*)
        FROM `{$wpdb->prefix}simple_auction_log` SL
        LEFT JOIN `{$wpdb->users}` ON SL.userid = `{$wpdb->users}`.id
        LEFT JOIN `{$wpdb->posts}` ON SL.auction_id = `{$wpdb->posts}`.id
        WHERE `{$wpdb->posts}`.post_author = %d
            {$search_query}
            {$date_from_filter}
        ;",
            $vendor_id
        );

        $cache_key = 'activities_count_' . md5( $query );
        $count     = Cache::get( $cache_key, $cache_group );

        if ( false === $count ) {
            $count = absint( $wpdb->get_var( $query ) );
            Cache::set( $cache_key, $count, $cache_group );
        }

        return $count;
    }

    $query = $wpdb->prepare(
        "SELECT SL.*, `{$wpdb->users}`.user_nicename, `{$wpdb->users}`.user_email, `{$wpdb->posts}`.post_title, `{$wpdb->posts}`.ID AS post_id FROM `{$wpdb->prefix}simple_auction_log` SL
        LEFT JOIN `{$wpdb->users}` ON SL.userid = `{$wpdb->users}`.id
        LEFT JOIN `{$wpdb->posts}` ON SL.auction_id = `{$wpdb->posts}`.id
        WHERE `{$wpdb->posts}`.post_author = %d
            {$search_query}
            {$date_from_filter}
        ORDER BY date DESC
            {$limit_query};",
        $vendor_id
    );

    $cache_key  = 'activities_' . md5( $query );
    $activities = Cache::get( $cache_key, $cache_group );

    if ( false === $activities ) {
        $activities = $wpdb->get_results( $query, ARRAY_A );
        Cache::set( $cache_key, $activities, $cache_group );
    }

    return $activities;
}
