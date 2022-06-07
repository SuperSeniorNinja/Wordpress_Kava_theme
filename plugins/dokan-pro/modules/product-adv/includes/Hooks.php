<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement;

use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\ProductSection;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Hooks
 *
 * @since 3.5.0
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 */
class Hooks {
    /**
     * Hooks constructor.
     */
    public function __construct() {
        // make product featured
        add_action( 'dokan_after_product_advertisement_created', [ $this, 'make_product_featured' ], 10, 2 );

        // remove featured products after an advertisement is expired
        add_action( 'dokan_after_batch_expire_product_advertisement', [ $this, 'remove_featured_product' ], 10, 1 );

        // remove from feature product during product delete
        add_action( 'dokan_before_deleting_product_advertisement', [ $this, 'remove_deleted_featured_product' ], 10, 1 );
        add_action( 'dokan_before_batch_delete_product_advertisement', [ $this, 'remove_deleted_featured_product' ], 10, 1 );

        // expire advertisements daily cron hook
        add_action( 'dokan_product_advertisement_daily_at_midnight_cron', [ $this, 'expire_advertisements' ] );

        // remove advertisement base product after advertisement product has been deleted
        add_action( 'delete_post', [ $this, 'delete_advertisement_base_product' ], 20 );

        //display advertised products on top
        add_action( 'posts_results', [ $this, 'display_advertised_products_on_top' ], 10, 2 );

        //render advertise product section in single store page
        add_filter( 'dokan_product_sections_container', [ $this, 'render_product_section' ], 99, 1 );

        // fix admin report log list
        add_filter( 'dokan_log_exclude_commission', [ $this, 'report_log_exclude_commission' ], 10, 2 );

        // after deleting a product, delete advertisement
        add_action( 'delete_post', [ $this, 'delete_advertisement' ], 20, 1 );
    }

    /**
     * This method will mark advertised product as featured
     *
     * @since 3.5.0
     *
     * @param int $advertisement_id
     * @param array $data
     *
     * @return void
     */
    public function make_product_featured( $advertisement_id, $data ) {
        if ( ! Helper::is_featured_enabled() ) {
            return;
        }

        Helper::make_product_featured( $data['product_id'] );
    }

    /**
     * Remove from featured list when advertisement is expired
     *
     * @since 3.5.0
     *
     * @param array $ids
     *
     * @return void
     */
    public function remove_featured_product( $ids ) {
        // return if make featured is disabled
        if ( ! Helper::is_featured_enabled() ) {
            return;
        }

        $manager     = new Manager();
        $product_ids = $manager->all(
            [
                'id'     => $ids,
                'return' => 'product_ids',
            ]
        );

        foreach ( $product_ids as $product_id ) {
            Helper::make_product_featured( $product_id, false );
        }
    }

    /**
     * Remove from featured list when advertisement is deleted
     *
     * @since 3.5.0
     *
     * @param array $ids
     *
     * @return void
     */
    public function remove_deleted_featured_product( $ids ) {
        // return if make featured is disabled
        if ( ! Helper::is_featured_enabled() ) {
            return;
        }

        // get product by ids
        $manager  = new Manager();
        $items    = $manager->all(
            [
                'id'     => $ids,
                'return' => 'all',
            ]
        );

        // if advertisement status is 1, we'll consider this product
        $eligible_products = [];
        foreach ( $items as $item ) {
            if ( intval( $item['status'] ) === 1 ) {
                $eligible_products[] = $item['product_id'];
            }
        }

        foreach ( $eligible_products as $product_id ) {
            Helper::make_product_featured( $product_id, false );
        }
    }

    /**
     * Expire advertisement daily
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function expire_advertisements() {
        $manager = new Manager();
        $manager->expire_advertisement_by_date();
    }

    /**
     * Remove advertisement base product after advertisement product has been deleted.
     *
     * @since 3.5.0
     *
     * @param int
     *
     * @return void
     */
    public function delete_advertisement_base_product( $post_id ) {
        if ( $post_id === Helper::get_advertisement_base_product() ) {
            delete_option( Helper::get_advertisement_base_product_option_key() );
        }
    }

    /**
     * Display advertised products on top
     *
     * @since 3.5.0
     *
     * @param array $posts
     * @param object $query query arguments
     *
     * @return array
     */
    public function display_advertised_products_on_top( $posts, $query ) {
        if ( ! is_admin() && Helper::is_catalog_priority_enabled() && ( is_search() || is_shop() || is_product_category() || dokan_is_store_page() ) && $query->is_main_query() ) {
            $non_advertised = [];
            $advertised    = [];
            // get all advertised products
            $manager = new Manager();
            $advertised_products = $manager->all(
                [
                    'status'   => 1,
                    'per_page' => -1,
                    'return'   => 'product_ids',
                ]
            );

            foreach ( $posts as $post ) {
                if ( in_array( (string) $post->ID, $advertised_products, true ) ) {
                    $advertised[] = $post;
                } else {
                    $non_advertised[] = $post;
                }
            }

            if ( dokan_is_store_page() ) {
                //todo: hack applied here, our store page ordering wasn't setting query var order,
                //we are putting advertised products at top
                $posts = array_merge( $advertised, $non_advertised );
            } else {
                /* if order is ASC put featured at top, otherwise put featured at bottom */
                $posts = ( 'ASC' === strtoupper( $query->get( 'order' ) ) )
                    ? array_merge( $advertised, $non_advertised )
                    : array_merge( $non_advertised, $advertised );
            }
        }

        return $posts;
    }

    /**
     * Render product section under single product page
     *
     * @param $container
     *
     * @return array
     */
    public function render_product_section( $container ) {
        return array_merge(
            [ new ProductSection() ],
            $container
        );
    }

    /**
     * Exclude commission from report log if order contains advertisement product
     *
     * @since 3.5.0
     *
     * @param bool $exclude
     * @param object $order
     *
     * @return bool
     */
    public function report_log_exclude_commission( $exclude, $order ) {
        if ( Helper::has_product_advertisement_in_order( $order->order_id ) ) {
            return true;
        }

        return $exclude;
    }

    /**
     * Delete advertisement data if a product has been deleted
     *
     * @since 3.5.0
     *
     * @param $post_id
     *
     * @return void
     */
    public function delete_advertisement( $post_id ) {
        // try to get wooCommerce product from post_id
        $product = wc_get_product( $post_id );

        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        $manager = new Manager();
        $manager->delete_advertisement_by_product_id( $product->get_id() );
    }
}
