<?php

namespace DokanPro\Modules\VendorStaff;

use WeDevs\Dokan\Cache;

/**
 * Vendor Staff Cache class.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class VendorStaffCache {

    public function __construct() {
        add_action( 'dokan_after_save_staff', [ $this, 'clear_cache' ], 20, 2 );
        add_action( 'dokan_before_delete_staff', [ $this, 'clear_cache' ], 20, 2 );
    }

    /**
     * Clear Vendor Staff Caches.
     *
     * @since 3.4.2
     *
     * @param int $vendor_id
     * @param int $staff_id
     *
     * @return void
     */
    public function clear_cache( $vendor_id, $staff_id = null ) {
        Cache::invalidate_group( "vendor_staff_{$vendor_id}" );
    }
}
