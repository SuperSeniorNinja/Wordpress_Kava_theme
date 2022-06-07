<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Helper
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 *
 * @since 3.5.0
 */
class Helper {

    /**
     * This method will return true if per product advertisement is enabled from product listing and product edit page.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_per_product_advertisement_enabled() {
        return 'on' === dokan_get_option( 'per_product_enabled', 'dokan_product_advertisement', 'on' );
    }

    /**
     * This method will return true if advertisement is enabled for vendor subscription pack
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_enabled_for_vendor_subscription() {
        return 'on' === dokan_get_option( 'vendor_subscription_enabled', 'dokan_product_advertisement', 'off' );
    }

    /**
     * This method will return true if advertisement is enabled for provided subscription pack
     *
     * @since 3.5.0
     *
     * @param int $pack_id
     *
     * @return bool
     */
    public static function is_advertisement_enabled_for_subscription_pack( $pack_id ) {
        return false === empty( get_post_meta( $pack_id, '_dokan_advertisement_slot_count', true ) );
    }

    /**
     * This method will return if admin wants to set purchased advertisement products as featured.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_featured_enabled() {
        return 'on' === dokan_get_option( 'featured', 'dokan_product_advertisement', 'off' );
    }

    /**
     * This method will return if admin wants to set purchased advertisement products as featured.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_catalog_priority_enabled() {
        return 'on' === dokan_get_option( 'catalog_priority', 'dokan_product_advertisement', 'on' );
    }

    /**
     * This method will return if admin wants to out of stocks products from advertisements.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_hide_out_of_stock_products_enabled() {
        return 'on' === dokan_get_option( 'hide_out_of_stock_items', 'dokan_product_advertisement', 'off' );
    }

    /**
     * This method will return advertisement cost for per product
     *
     * @since 3.5.0
     *
     * @return float 0 for free purchase or a positive float number
     */
    public static function get_advertisement_cost() {
        return floatval( dokan_get_option( 'cost', 'dokan_product_advertisement' ) );
    }

    /**
     * This method will return total advertisement slot mentioned under admin settings.
     *
     * @since 3.5.0
     *
     * @return int -1 for unlimited advertisement products or a non-zero positive integer
     */
    public static function get_total_advertisement_slot_count() {
        return intval( dokan_get_option( 'total_available_slot', 'dokan_product_advertisement', 20 ) );
    }

    /**
     * This method will return advertisement product count for provided subscription pack
     *
     * @since 3.5.0
     *
     * @param int $pack_id
     *
     * @return int -1 if no limit is set, non-zero positive integer otherwise
     */
    public static function get_subscription_pack_total_advertisement_slot( $pack_id ) {
        return intval( get_post_meta( $pack_id, '_dokan_advertisement_slot_count', true ) );
    }

    /**
     * This method will return total number of advertisement slot is available for a vendor by subscription
     *
     * If vendor subscription module is active and if vendor is subscribed to any subscription package,
     * this method will return assigned package's slot count (if any), otherwise, this will return
     * false
     *
     * @since 3.5.0
     *
     * @param int $vendor_id
     *
     * @return int|bool -1 for unlimited advertisement, positive integer otherwise. false if no slot is assigned
     */
    public static function get_total_advertisement_slot_count_by_vendor_subscription( $vendor_id ) {
        // check if vendor subscription module is enabled and user is under a subscription
        $subscription = static::check_subscription_status_for_vendor( $vendor_id );

        if ( false === $subscription ) {
            return $subscription;
        }

        // get subscription product count, if any
        $subscription_product_count = static::get_subscription_pack_total_advertisement_slot( $subscription->get_id() );

        // if subscription is found, return subscription pack product count
        if ( ! empty( $subscription_product_count ) ) {
            return $subscription_product_count;
        }

        // return false
        return false;
    }

    /**
     * This method will return available/remaining advertisement counts
     *
     * @since 3.5.0
     *
     * @return int -1 if no restriction is applied, positive integer otherwise
     */
    public static function get_available_advertisement_slot_count() {
        // if no of product count is -1, return from here
        if ( -1 === static::get_total_advertisement_slot_count() ) {
            return -1;
        }

        // get all active advertisement from database
        $manager = new Manager();
        $active_advertisements = $manager->all(
            [
                'status'   => 1,
                'per_page' => -1,
                'return'   => 'count',
            ]
        );

        $available = static::get_total_advertisement_slot_count() - $active_advertisements;

        // for negative available value, return 0, otherwise return $available as it is
        return $available >= 0 ? $available : 0;
    }

    /**
     * This method will return number of available advertisement slot count for a vendor by subscription
     *
     * If vendor subscription is exists and vendor is subscribe to any package, this will return available slot count from
     * package count, otherwise this will return false.
     *
     * @since 3.5.0
     *
     * @param int $vendor_id
     *
     * @return int|bool -1 for unlimited advertisement, positive integer otherwise
     */
    public static function get_available_advertisement_slot_count_by_vendor_subscription( $vendor_id ) {
        // check if vendor subscription module is enabled and user is under a subscription
        $subscription = static::check_subscription_status_for_vendor( $vendor_id );

        // if no subscription is found, return global advertisement slot count
        if ( false === $subscription ) {
            return $subscription;
        }

        // get total advertisement slot count
        $subscription_total_available_slot_count = static::get_total_advertisement_slot_count_by_vendor_subscription( $vendor_id );
        // return if slot count is -1
        // subscription slot count will get priority before global value
        if ( -1 === $subscription_total_available_slot_count ) {
            return -1;
            // check if subscription package value is empty, in that case return global available count
        } elseif ( empty( $subscription_total_available_slot_count ) ) {
            return false;
        }

        $manager = new Manager();
        // now calculate available slot for vendor
        $active_advertised_products = $manager->all(
            [
                'vendor_id' => $vendor_id,
                'status'    => 1,
                'per_page'  => -1,
                'return'    => 'count',
            ]
        );

        $available = $subscription_total_available_slot_count - $active_advertised_products;

        // for negative available value, return 0, otherwise return $available as it is
        return $available >= 0 ? $available : 0;
    }

    /**
     * This method will return total number of days a product will be advertised.
     *
     * @since 3.5.0
     *
     * @return int -1 if advertisement is for unlimited period of time or a non-zero positive integer
     */
    public static function get_expire_after_days() {
        return intval( dokan_get_option( 'expire_after_days', 'dokan_product_advertisement', 10 ) );
    }

    /**
     * This method will return advertised days for a vendor subscription pack
     *
     * @param $pack_id
     *
     * @since 3.5.0
     *
     * @return int -1 if no expire days, non-zero positive integer otherwise
     */
    public static function get_subscription_pack_expire_after_days( $pack_id ) {
        return intval( get_post_meta( $pack_id, '_dokan_advertisement_validity', true ) );
    }

    /**
     * This method will return advertised product's expire after days for a vendor by subscription
     *
     * If Vendor Subscription module is active and vendor is assigned to any subscription plan, this method will return
     * subscription pack's expire after days, otherwise this method will return false
     *
     * @since 3.5.0
     *
     * @param int $vendor_id
     *
     * @return int|bool -1 if no expire days, non-zero positive integer otherwise, false if not assigned
     */
    public static function get_expire_after_days_by_vendor_subscription( $vendor_id ) {
        // check if vendor subscription module is enabled and user is under a subscription
        $subscription = static::check_subscription_status_for_vendor( $vendor_id );

        // if no subscription is found, return global advertised expire after days count
        if ( false === $subscription ) {
            return $subscription;
        }

        // if subscription is found, return subscription pack product count
        $subscription_expire_after_days = static::get_subscription_pack_expire_after_days( $subscription->get_id() );
        if ( ! empty( $subscription_expire_after_days ) ) {
            return $subscription_expire_after_days;
        }

        // return global expire after days
        return false;
    }

    /**
     * Check whether subscription module is enabled or not
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function has_vendor_subscription_module() {
        // don't confused with product_subscription, id for vendor subscription module is product_subscription
        return dokan_pro()->module->is_active( 'product_subscription' ) && 'on' === dokan_get_option( 'enable_pricing', 'dokan_product_subscription' );
    }

    /**
     * This method will check if vendor is under any subscription pack
     *
     * @since 3.5.0
     *
     * @param int $vendor_id
     *
     * @return bool|\DokanPro\Modules\Subscription\SubscriptionPack
     */
    public static function check_subscription_status_for_vendor( $vendor_id = 0 ) {
        if ( empty( $vendor_id ) ) {
            return false;
        }
        // check if subscription module is enabled and advertisement is active for subscription
        if ( ! static::has_vendor_subscription_module() || ! static::is_enabled_for_vendor_subscription() ) {
            return false;
        }

        // check if user is under any subscription  pack
        $subscription = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $subscription instanceof \DokanPro\Modules\Subscription\SubscriptionPack ) {
            return false;
        }

        // check if subscription is enabled for subscription pack
        if ( ! static::is_advertisement_enabled_for_subscription_pack( $subscription->get_id() ) ) {
            return false;
        }

        return $subscription;
    }

    /**
     * This method will check if cart contain advertisement product
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function has_product_advertisement_in_cart() {
        if ( ! WC()->cart ) {
            return false;
        }

        foreach ( WC()->cart->get_cart() as $item ) {
            if ( isset( $item['dokan_product_advertisement'] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method will check if cart contain advertisement product
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function has_product_advertisement_in_order( $order ) {
        // check if we get
        if ( ! $order instanceof \WC_Abstract_Order && is_numeric( $order ) ) {
            // get order object from order_id
            $order = wc_get_order( $order );
        }

        if ( ! $order instanceof \WC_Abstract_Order ) {
            return false;
        }

        foreach ( $order->get_items() as $item ) {
            if ( $item->get_meta( 'dokan_advertisement_product_id' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \WC_Abstract_Order $order
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_advertisement_data_from_order( \WC_Abstract_Order $order ) {
        $data = [];

        foreach ( $order->get_items() as $item ) {
            if ( $item->get_meta( 'dokan_advertisement_product_id' ) ) {
                $data['product_id']         = (int) $item->get_meta( 'dokan_advertisement_product_id' );
                $data['advertisement_cost'] = (float) $item->get_meta( 'dokan_advertisement_cost' );
                $data['expires_after_days'] = (int) $item->get_meta( 'dokan_advertisement_expire_after_days' );
                break;
            }
        }

        return $data;
    }

    /**
     * This method will return formatted expire after days text
     *
     * @since 3.5.0
     *
     * @param int $expire_after_days
     *
     * @return string
     */
    public static function format_expire_after_days_text( $expire_after_days ) {
        if ( in_array( intval( $expire_after_days ), [ -1, 0 ], true ) ) {
            return __( 'Unlimited days', 'dokan' );
        }
        // translators: 1) expire after day count
        return sprintf( _n( '%s day', '%s days', $expire_after_days, 'dokan' ), number_format_i18n( $expire_after_days ) );
    }

    /**
     * This method will return formatted expire after date as localized string
     *
     * @since 3.5.0
     *
     * @param int $expires_at
     *
     * @return string
     */
    public static function get_formatted_expire_date( $expires_at ) {
        if ( 0 === intval( $expires_at ) ) {
            return __( 'Unlimited', 'dokan' );
        }
        return dokan_format_date( $expires_at );
    }

    /**
     * This method will return formatted expire after date as localized string
     *
     * @since 3.5.0
     *
     * @param int $remaining_slot
     *
     * @return string
     */
    public static function get_formatted_remaining_slot_count( $remaining_slot ) {
        if ( -1 === intval( $remaining_slot ) ) {
            return __( 'Unlimited', 'dokan' );
        }
        return $remaining_slot;
    }

    /**
     * This method will return option key for advertisement base product
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_advertisement_base_product_option_key() {
        return 'dokan_advertisement_product_id';
    }

    /**
     * Create advertisement product
     *
     * @since 3.5.0
     *
     * @return int
     */
    public static function get_advertisement_base_product() {
        // get advertisement product id from option table
        return (int) get_option( static::get_advertisement_base_product_option_key(), 0 );
    }

    /**
     * This method will return vendor id if called from single store page
     *
     * @since 3.5.0
     *
     * @return bool|int
     */
    public static function get_vendor_from_single_store_page() {
        //todo: move this function to dokan lite
        $custom_store_url = dokan_get_option( 'custom_store_url', 'dokan_general', 'store' );
        $store_name       = get_query_var( $custom_store_url );

        if ( ! empty( $store_name ) ) {
            $store_user = get_user_by( 'slug', $store_name );

            // no user found
            if ( ! $store_user ) {
                return false;
            }

            // Bell out for Vendor Stuff extensions
            if ( ! is_super_admin( $store_user->ID ) && user_can( $store_user->ID, 'vendor_staff' ) ) {
                return false;
            }

            // check if the user is seller
            if ( ! dokan_is_user_seller( $store_user->ID ) ) {
                return false;
            }

            return $store_user->ID;
        }

        return false;
    }

    /**
     * Mark product as featured
     *
     * @since 3.5.0
     *
     * @param int|\WC_Product $product
     * @param bool $featured
     *
     * @return void
     */
    public static function make_product_featured( $product, $featured = true ) {
        if ( ! $product instanceof \WC_Product && is_numeric( $product ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product ) {
            return;
        }

        $product->set_featured( $featured );
        $product->save();
    }

    /**
     * Check if product has been advertised
     *
     * @since 3.5.0
     *
     * @param int $product_id
     *
     * @return bool
     */
    public static function is_product_advertised( $product_id ) {
        if ( ! $product_id ) {
            return false;
        }

        $manager = new Manager();
        $advertised_products = $manager->all(
            [
                'product_id' => $product_id,
                'status'     => 1,
                'per_page'   => -1,
                'return'     => 'count',
            ]
        );

        return $advertised_products > 0;
    }

    /**
     * Get advertisement data by product
     *
     * @since 3.5.0
     *
     * @param int|null $product
     *
     * @return array
     */
    public static function get_advertisement_data_by_product( $product ) {
        // get product object
        if ( ! $product instanceof \WC_Product ) {
            $product = wc_get_product( $product );
        }

        if ( empty( $product ) ) {
            return [];
        }

        $vendor_id = dokan_get_vendor_by_product( $product, true );

        if ( ! $vendor_id ) {
            return [];
        }

        $already_advertised              = false;
        $can_advertise_for_free          = false;
        $expire_date                     = '';
        $subscription_status             = static::check_subscription_status_for_vendor( $vendor_id );
        $global_remaining_slot           = static::get_available_advertisement_slot_count();
        $remaining_slot                  = $global_remaining_slot;
        $subscription_remaining_slot     = $subscription_status ? static::get_available_advertisement_slot_count_by_vendor_subscription( $vendor_id ) : 0;
        $listing_price                   = static::get_advertisement_cost();
        $expires_after_days              = static::get_expire_after_days();
        $subscription_expires_after_days = $subscription_status ? static::get_expire_after_days_by_vendor_subscription( $vendor_id ) : 0;

        // check if product already advertised
        $manager = new Manager();
        $data = $manager->all(
            [
                'product_id' => $product->get_id(),
                'status'     => 1,
                'per_page'   => 1,
                'return'     => 'all',
            ]
        );
        if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
            $already_advertised = true;
            $expire_date = static::get_formatted_expire_date( $data['expires_at'] );
        }

        /**
         * 1. both per product and subscription are enabled
         * 2. only subscription is enabled
         * 3. only per product is enabled
         */

        if ( static::is_per_product_advertisement_enabled() && static::is_enabled_for_vendor_subscription() ) {
            // check if user can advertise this product for free
            if ( empty( $listing_price ) ) {
                $can_advertise_for_free = true;
            }

            // we will give priority to subscription slot and expire days
            if ( false !== $subscription_status && ! empty( $subscription_remaining_slot ) ) {
                $expires_after_days     = $subscription_expires_after_days;
                $remaining_slot         = $subscription_remaining_slot;
                $can_advertise_for_free = true;
            }
        } elseif ( static::is_enabled_for_vendor_subscription() ) {
            // check if user can advertise this product for free
            if ( false !== $subscription_status && ! empty( $subscription_remaining_slot ) ) {
                $can_advertise_for_free = true;
                $expires_after_days     = $subscription_expires_after_days;
                $remaining_slot         = $subscription_remaining_slot;
            } else {
                $remaining_slot = 0;
            }
        }

        //todo: return this as object
        return [
            'vendor_id'                       => $vendor_id,
            'product_id'                      => $product->get_id(),
            'subscription_status'             => $subscription_status,
            'remaining_slot'                  => $remaining_slot,
            'global_remaining_slot'           => $global_remaining_slot,
            'subscription_remaining_slot'     => $subscription_remaining_slot,
            'listing_price'                   => $listing_price,
            'expires_after_days'              => $expires_after_days,
            'subscription_expires_after_days' => $subscription_expires_after_days,
            'already_advertised'              => $already_advertised,
            'can_advertise_for_free'          => $can_advertise_for_free,
            'expire_date'                     => $expire_date,
            'post_status'                     => $product->get_status(),
        ];
    }

    /**
     * Get advertisement data and validate
     *
     * @since 3.5.0
     *
     * @param int $product_id
     * @param int $vendor_id
     *
     * @return array|WP_Error
     */
    public static function get_advertisement_data_for_insert( $product_id, $vendor_id ) {
        $advertisement_data = static::get_advertisement_data_by_product( $product_id );

        if ( empty( $advertisement_data ) ) {
            return new WP_Error( 'invalid_product', __( 'No product found with given product ID. Please check your input.', 'dokan' ) );
        }

        // check if product status is publish
        if ( 'publish' !== $advertisement_data['post_status'] ) {
            return new WP_Error( 'invalid_product', __( 'You can not advertise this product. Products need to be published before you can advertise.', 'dokan' ) );
        }

        // check if product is belong to given vendor id
        if ( ! $advertisement_data['vendor_id'] || intval( $vendor_id ) !== $advertisement_data['vendor_id'] ) {
            return new WP_Error( 'invalid_vendor', __( 'Product id does not belong to given vendor. Please check your input', 'dokan' ) );
        }

        // check advertisement already exists in database, this is to prevent duplicate entry
        if ( $advertisement_data['already_advertised'] ) {
            return new WP_Error( 'invalid_product', __( 'Advertisement for this product is already going on. Please select another product.', 'dokan' ) );
        }

        // check we've got slot left for advertisement
        if ( empty( $advertisement_data['remaining_slot'] ) ) {
            return new WP_Error( 'empty_slot', __( 'There are no advertisement slots available at this moment.', 'dokan' ) );
        }

        return $advertisement_data;
    }
}
