<?php

use WeDevs\Dokan\Cache;

/**
 * Dokan Auction Cache class.
 *
 * Manage all cachings for dokan auction module.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class DokanAuctionCache {

    /**
     * Constructor for the DokanAuctionCache class.
     *
     * Manage the following Caching groups:
     * 1. auction_products[seller_id]    => Seller's auction products
     * 2. auction_activities_[seller_id] => Seller's Main Auction Activities
     *
     * @since 3.4.2
     */
    public function __construct() {
        add_action( 'dokan_new_auction_product_added', [ $this, 'after_created_auction_product' ], 20, 2 );
        add_action( 'dokan_update_auction_product', [ $this, 'after_created_auction_product' ], 20, 2 );
        add_action( 'before_delete_post', [ $this, 'clear_auction_type_product_caches' ], 20, 1 );

        add_action( 'woocommerce_new_product', [ $this, 'clear_auction_type_product_caches' ], 20 );
        add_action( 'woocommerce_update_product', [ $this, 'clear_auction_type_product_caches' ], 20 );
        add_action( 'woocommerce_product_duplicate', [ $this, 'clear_auction_type_product_caches' ], 20 );
        add_action( 'woocommerce_product_import_inserted_product_object', [ $this, 'clear_auction_type_product_caches' ], 20 );

        add_action( 'wp_trash_post', [ $this, 'clear_auction_type_product_caches' ], 20 );
        add_action( 'delete_post', [ $this, 'clear_auction_type_product_caches' ], 20 );

        // Related to auction activity
        add_action( 'woocommerce_simple_auctions_place_bid', [ $this, 'after_update_bid' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_delete_bid', [ $this, 'after_update_bid' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_started', [ $this, 'after_auction_meta_changes' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_close', [ $this, 'after_auction_meta_changes' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_won', [ $this, 'after_auction_meta_changes' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_fail', [ $this, 'after_auction_meta_changes' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_reserve_fail', [ $this, 'after_auction_meta_changes' ], 20, 1 );
        add_action( 'woocommerce_simple_auction_finished', [ $this, 'after_auction_meta_changes' ], 20, 1 );
    }

    /**
     * Clear Seller Auction product caches.
     *
     * @since 3.4.2
     *
     * @param int $product_id
     *
     * @return void
     */
    public function clear_auction_product_caches( $product_id ) {
        // Get Seller ID from product ID
        $seller_id = get_post_field( 'post_author', $product_id );

        // If no seller found, that means no need to use this invalidation.
        if ( ! $seller_id ) {
            return;
        }

        Cache::invalidate_group( "auction_products_{$seller_id}" );

        // Clear Woocommerce product type cache for this product.
        $product_type_cache_key = WC_Cache_Helper::get_cache_prefix( 'product_' . $product_id ) . '_type_' . $product_id;
        // Don't use Cache::set() here, cause it's for woocommerce cache invalidations.
        wp_cache_set( $product_type_cache_key, 'auction', 'products' );
    }

    /**
     * Clear Seller Auction activity caches.
     *
     * @since 3.4.2
     *
     * @param int $product_id
     *
     * @return void
     */
    public function clear_auction_activity_caches( $product_id ) {
        // Get Seller ID from product ID
        $seller_id = get_post_field( 'post_author', $product_id );

        Cache::invalidate_group( "auction_activities_{$seller_id}" );
    }

    /**
     * Reset cache group related to seller products.
     *
     * @since 3.4.2
     *
     * @param int|\WC_Product $product
     *
     * @return void
     */
    public function clear_auction_type_product_caches( $product ) {
        // Get auction product
        if ( ! $product instanceof \WC_Product ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        // Stop if product is not auction type
        if ( 'auction' !== $product->get_type() ) {
            return;
        }

        $this->clear_auction_product_caches( $product->get_id() );
    }

    /**
     * Clear Auction caches after creating auction product.
     *
     * @since 3.4.2
     *
     * @param int   $product_id
     * @param array $post_data
     *
     * @return void
     */
    public function after_created_auction_product( $product_id, $post_data ) {
        $this->clear_auction_product_caches( $product_id );
    }

    /**
     * Clear Auction caches after deleting auction product.
     *
     * @since 3.4.2
     *
     * @param int $product_id
     *
     * @return void
     */
    public function after_deleted_auction_product( $product_id ) {
        $this->clear_auction_product_caches( $product_id );
    }

    /**
     * Clear Auction caches after placing / deleting bid.
     *
     * @since 3.4.2
     *
     * @param array $data
     *
     * @return void
     */
    public function after_update_bid( $data ) {
        // We'll get the product ID from the data array
        $this->clear_auction_activity_caches( $data['product_id'] );
    }

    /**
     * Clear Auction started, failed, own or finished.
     *
     * @since 3.4.2
     *
     * @param int $id
     *
     * @return void
     */
    public function after_auction_meta_changes( $id ) {
        $this->clear_auction_activity_caches( $id );
    }
}
