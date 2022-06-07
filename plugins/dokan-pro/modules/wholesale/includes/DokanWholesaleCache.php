<?php

use WeDevs\Dokan\Cache;

/**
 * Wholesale Cache class.
 *
 * Manage all caching for wholesale module.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class DokanWholesaleCache {

    public function __construct() {
        add_action( 'dokan_wholesale_customer_register', [ $this, 'clear_wholesale_customer_cache' ], 10 );
        add_action( 'dokan_wholesale_customer_status_changed', [ $this, 'clear_wholesale_customer_cache' ], 10 );
        add_action( 'dokan_wholesale_customer_batch_status_changed', [ $this, 'clear_wholesale_customer_cache' ], 10 );
        add_action( 'profile_update', [ $this, 'clear_cache_after_update_user' ], 10, 2 );
    }

    /**
     * Clear Wholesale customer caches.
     *
     * @since 3.4.2
     *
     * @return void
     */
    public static function clear_wholesale_customer_cache() {
        Cache::invalidate_group( 'wholesale_customers' );
    }

    /**
     * Clear wholesale customer cache after update an user.
     *
     * @since 3.4.2
     *
     * @param int    $user_id
     * @param object $old_user_data
     *
     * @return void
     */
    public static function clear_cache_after_update_user( $user_id, $old_user_data ) {
        self::clear_wholesale_customer_cache();
    }
}
