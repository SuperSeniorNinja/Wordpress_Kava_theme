<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement\Admin;

use WeDevs\DokanPro\Modules\ProductAdvertisement\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Install
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 *
 * @since 3.5.0
 */
class Install {

    /**
     * Install constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->create_table();
        $this->create_advertisement_product();
        if ( $this->schedule_cron() ) {
            //early call expire cron
            do_action( 'dokan_product_advertisement_daily_at_midnight_cron' );
        }
    }

    /**
     * This method will create required table
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function create_table() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_advertised_products` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `product_id` bigint(20) UNSIGNED NOT NULL,
                    `created_via` ENUM('order','admin','subscription','free') NOT NULL DEFAULT 'admin',
                    `order_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
                    `price` decimal(19,4) NOT NULL DEFAULT 0.0000,
                    `expires_at` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
                    `added` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    `updated` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY product_id (product_id),
                    KEY order_id (order_id),
                    KEY expires_at (expires_at),
                    KEY status (status),
                    KEY expires_at_status (expires_at,status)
                ) ENGINE=InnoDB {$wpdb->get_charset_collate()};
                ";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql );
    }

    /**
     * This method will create advertisement base product
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function create_advertisement_product() {
        // get advertisement product id from option table
        $product_id = (int) get_option( Helper::get_advertisement_base_product_option_key(), false );
        if ( $product_id ) {
            return;
        }

        // create a new post
        $post = [
            'post_content' => 'This is Dokan advertisement payment product, do not delete.',
            'post_status'  => 'publish',
            'post_title'   => 'Product Advertisement Payment',
            'post_parent'  => '',
            'post_type'    => 'product',
        ];

        /* Create post */
        $post_id = wp_insert_post( $post );

        if ( is_wp_error( $post_id ) ) {
            return;
        }

        // try catch block used here just to get rid of phpcs errors
        try {
            // convert post into product
            $product = new \WC_Product_Simple();
            $product->set_id( $post_id );
            $product->set_catalog_visibility( 'hidden' );
            $product->set_virtual( true );
            $product->set_price( 0 );
            $product->set_regular_price( 0 );
            $product->set_sale_price( 0 );
            $product->set_manage_stock( false );
            $product->save();

            update_option( Helper::get_advertisement_base_product_option_key(), $product->get_id() );
        } catch ( \Exception $exception ) {
            return;
        }
    }

    /**
     * Schedule crom for midnight
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'dokan_product_advertisement_daily_at_midnight_cron' ) ) {
            // schedule cron at midnight local time
            $timestamp = dokan_current_datetime()->modify( 'midnight' )->getTimestamp();
            wp_schedule_event(
                $timestamp,
                'daily',
                'dokan_product_advertisement_daily_at_midnight_cron'
            );
        }
        return true;
    }
}
