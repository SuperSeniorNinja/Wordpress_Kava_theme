<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement;

use WP_Error;
use WeDevs\Dokan\Cache;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class AdvertisementCache
 *
 * @since 3.5.0
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 */
class AdvertisementCache {
    /**
     * AdvertisementCache constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        // after advertisement status has been changed
        add_action( 'dokan_after_product_advertisement_created', [ $this, 'after_product_advertisement_created' ], 10, 3 );
        add_action( 'dokan_before_deleting_product_advertisement', [ $this, 'before_deleting_product_advertisement' ], 10, 1 );
        add_action( 'dokan_before_batch_delete_product_advertisement', [ $this, 'batch_delete_product_advertisement' ], 10, 1 );
        add_action( 'dokan_before_batch_expire_product_advertisement', [ $this, 'batch_delete_product_advertisement' ], 10, 1 );
        // after product status has been updated
        add_action( 'dokan_product_updated', [ $this, 'after_product_update' ], 20, 1 );
        add_action( 'woocommerce_update_product', [ $this, 'after_product_update' ], 20, 1 );
        add_action( 'wp_trash_post', [ $this, 'after_product_update' ], 20, 1 );
    }

    /**
     * This method will delete advertisement cache by ids
     *
     * @since 3.5.0
     *
     * @param $advertisement_ids
     *
     * @return void
     */
    public function batch_delete_product_advertisement( $advertisement_ids ) {
        foreach ( $advertisement_ids as $advertisement_id ) {
            $this->before_deleting_product_advertisement( $advertisement_id );
        }
    }

    /**
     * Delete cache by advertisement id
     *
     * @since 3.5.0
     *
     * @param int $advertisement_id
     *
     * @return void
     */
    public function before_deleting_product_advertisement( $advertisement_id ) {
        $manager = new Manager();
        $advertisement_data = $manager->get( $advertisement_id );
        if ( is_wp_error( $advertisement_data ) ) {
            static::delete();
            return;
        }

        // get seller id
        $seller_id = dokan_get_vendor_by_product( $advertisement_data['product_id'], true );
        if ( ! $seller_id && ! empty( $advertisement_data['order_id'] ) ) {
            $seller_id = dokan_get_seller_id_by_order( $advertisement_data['order_id'] );
        }

        // delete cache
        static::delete( $seller_id );
    }

    /**
     * Delete advertisement cache after new advertisement is created
     *
     * @since 3.5.0
     *
     * @param int $advertisement_id
     * @param array $advertisement_data
     * @param array $args
     *
     * @return void
     */
    public function after_product_advertisement_created( $advertisement_id, $advertisement_data, $args ) {
        // clear advertisement cache
        $seller_id = dokan_get_vendor_by_product( $advertisement_data['product_id'], true );
        if ( ! $seller_id && ! empty( $args['order_id'] ) ) {
            $seller_id = dokan_get_seller_id_by_order( $args['order_id'] );
        }

        static::delete( $seller_id );
    }

    /**
     * Invalidate Advertisement Seller Cache
     *
     * @since 3.5.0
     *
     * @param int|null $seller_id
     *
     * @return void
     */
    public static function delete( $seller_id = null ) {
        // delete global advertisement cache
        $cache_group = 'advertised_product';
        Cache::invalidate_group( $cache_group );

        // delete individual seller cache
        if ( is_numeric( $seller_id ) ) {
            $cache_group = "advertised_product_{$seller_id}";
            Cache::invalidate_group( $cache_group );
        }
    }

    /**
     * Delete advertisement cache after a product has been updated
     *
     * @since 3.5.0
     *
     * @param int|\WC_Product $product
     *
     * @return void
     */
    public static function after_product_update( $product ) {
        // some hooks can return product object also, making sure we are getting id only
        if ( ! $product instanceof \WC_Product ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        $seller_id = dokan_get_vendor_by_product( $product, true );

        self::delete( $seller_id );
    }
}
