<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement;

use WeDevs\Dokan\Cache;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Manager
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Table name for advertisement table
     *
     * @var string
     *
     * @since 3.5.0
     */
    private $table;

    /**
     * Manager constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        global $wpdb;
        $this->table = "{$wpdb->prefix}dokan_advertised_products";
    }

    /**
     * This method will return data from dokan_advertised_products table
     *
     * @since 3.5.0
     *
     * @param array $args
     *
     * @return array|int|null|object
     */
    public function all( $args = [] ) {
        $default = [
            'id'          => [],
            'product_id'  => [],      // array of integers
            'vendor_id'   => [],      // array of integers
            'created_via' => '',      // possible values are order,admin,subscription
            'order_id'    => [],      // array of integers
            'expires_at'  => [
                'min' => '',    // timestamp or datetime string
                'max' => '',    // timestamp or datetime string
            ],
            'status'      => 0,       // 1 for active, 2 for inactive
            'post_status' => '',
            'added'       => 0,
            'search'      => '',
            'orderby'     => 'added',
            'order'       => 'DESC',
            'return'      => 'all',   // possible values are all, ids, product_ids, count, individual_count
            'per_page'    => 20,
            'page'        => 1,
        ];

        $args = wp_parse_args( $args, $default );

        global $wpdb;

        $fields      = '';
        $join        = "LEFT JOIN {$wpdb->posts} AS post ON featured.product_id = post.ID";
        $where       = '';
        $groupby     = '';
        $orderby     = '';
        $limits      = '';
        $query_args  = [ 1, 1 ];
        $status      = '';

        // determine which fields to return
        if ( 'ids' === $args['return'] ) {
            $fields = 'featured.id';
        } elseif ( 'product_ids' === $args['return'] ) {
            $fields = 'post.id';
        } elseif ( in_array( $args['return'], [ 'count', 'individual_count' ], true ) ) {
            $fields = 'COUNT(featured.id) AS count';
        } else {
            $fields = 'featured.*, post.post_title AS product_title, post.post_status AS post_status, post.post_author AS vendor_id, u_meta.meta_value AS store_name';
            // join user meta table
            $join .= " LEFT JOIN {$wpdb->usermeta} AS u_meta ON post.post_author = u_meta.user_id";
            // include dokan_store_name under where param
            $where .= ' AND u_meta.meta_key=%s';
            $query_args[] = 'dokan_store_name';
        }

        // check if id filter is applied
        if ( ! $this->is_empty( $args['id'] ) ) {
            $advertisement_ids = implode( "','", array_map( 'absint', (array) $args['id'] ) );
            $where .= " AND featured.id IN ('$advertisement_ids')";
        }

        // check if product_id filter is applied
        if ( ! $this->is_empty( $args['product_id'] ) ) {
            $product_ids = implode( "','", array_map( 'absint', (array) $args['product_id'] ) );
            $where .= " AND featured.product_id IN ('$product_ids')";
        }

        // check if vendor id filter is applied
        if ( ! $this->is_empty( $args['vendor_id'] ) ) {
            $vendor_id = implode( "','", array_map( 'absint', (array) $args['vendor_id'] ) );
            $where .= " AND post.post_author IN ('$vendor_id')";
        }

        // check if order id filter is applied
        if ( ! $this->is_empty( $args['order_id'] ) ) {
            $order_ids = implode( "','", array_map( 'absint', (array) $args['order_id'] ) );
            $where     .= " AND featured.order_id IN ('$order_ids')";
        }

        // check if status filter is applied
        if ( ! empty( $args['status'] ) ) {
            $status = $wpdb->prepare( ' AND featured.status = %d', $args['status'] );
        }

        // check if expires_at filter is applied
        // convert into timestamp
        $now = dokan_current_datetime();
        if ( ! empty( $args['expires_at']['min'] ) ) {
            // fix date format
            if ( is_numeric( $args['expires_at']['min'] ) ) {
                $now = $now->setTimestamp( $args['expires_at']['min'] );
            } else {
                $now = $now->modify( $args['expires_at']['min'] );
            }
            $args['expires_at']['min'] = $now ? $now->setTime( 0, 0, 0 )->getTimestamp() : 0;
        }

        // convert into timestamp
        if ( ! empty( $args['expires_at']['max'] ) ) {
            if ( is_numeric( $args['expires_at']['max'] ) ) {
                $now = $now->setTimestamp( $args['expires_at']['max'] );
            } else {
                $now = $now->modify( $args['expires_at']['max'] );
            }
            $args['expires_at']['max'] = $now ? $now->setTime( 23, 59, 59 )->getTimestamp() : 0;
        }

        // check if min and max both values are set, search  in between
        if ( ! empty( $args['expires_at']['min'] ) && ! empty( $args['expires_at']['max'] ) ) {
            // fix min max value
            if ( $args['expires_at']['min'] > $args['expires_at']['max'] ) {
                $temp = $args['expires_at']['min'];
                $args['expires_at']['min'] = $args['expires_at']['max'];
                $args['expires_at']['max'] = $temp;
            }

            $where        .= ' AND featured.expires_at BETWEEN %d AND %d';
            $query_args[] = $args['expires_at']['min'];
            $query_args[] = $args['expires_at']['max'];

            // check if min value is set
        } elseif ( ! empty( $args['expires_at']['min'] ) ) {
            $where        .= ' AND featured.expires_at >= %d';
            $query_args[] = $args['expires_at']['min'];

            //check if max value is set
        } elseif ( ! empty( $args['expires_at']['max'] ) ) {
            $where        .= ' AND featured.expires_at <= %d';
            $query_args[] = $args['expires_at']['max'];
        }

        // check if created_via filter is applied or not
        if ( ! empty( $args['created_via'] ) && in_array( $args['created_via'], [ 'admin', 'order', 'subscription' ], true ) ) {
            $where        .= ' AND featured.created_via = %s';
            $query_args[] = $args['created_via'];
        }

        // check if search applied
        if ( ! empty( $args['search'] ) ) {
            $like         = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where        .= ' AND ( post.post_title like %s OR featured.order_id like %s )';
            $query_args[] = $like;
            $query_args[] = $like;
        }

        // filter by post_stattus
        if ( ! empty( $args['post_status'] ) ) {
            $where        .= ' AND post.post_status=%s ';
            $query_args[] = $args['post_status'];
        }

        // order and order by param
        // supported order by param
        $supported_order_by = [
            'product_title' => 'post.post_title',
            'price'         => 'featured.price',
            'expires_at'    => 'featured.expires_at',
            'added'         => 'featured.added',
        ];

        if ( ! empty( $args['orderby'] ) && array_key_exists( $args['orderby'], $supported_order_by ) ) {
            $order   = in_array( strtolower( $args['order'] ), [ 'asc', 'desc' ], true ) ? strtoupper( $args['order'] ) : 'ASC';
            $orderby = "ORDER BY {$supported_order_by[ $args['orderby'] ]} {$order}"; //no need for prepare, we've already whitelisted the parameters

            //second order by in case of similar value on first order by field
            $orderby .= ", featured.id {$order}";
        }

        // pagination param
        if ( ! empty( $args['per_page'] ) && -1 !== intval( $args['per_page'] ) ) {
            $limit  = absint( $args['per_page'] );
            $page   = absint( $args['page'] );
            $page   = $page ? $page : 1;
            $offset = ( $page - 1 ) * $limit;

            $limits       = 'LIMIT %d, %d';
            $query_args[] = $offset;
            $query_args[] = $limit;
        }

        $cache_group = 'advertised_product';
        $cache_key   = 'get_products_' . md5( wp_json_encode( $args ) );
        if ( is_numeric( $args['vendor_id'] ) ) {
            $cache_group = "advertised_product_{$args['vendor_id']}";
        } elseif ( ! $this->is_empty( $args['vendor_id'] ) && 1 === count( $args['vendor_id'] ) ) {
            $cache_group = "advertised_product_{$args['vendor_id'][0]}";
        }

        $data = Cache::get( $cache_key, $cache_group );

        if ( in_array( $args['return'], [ 'ids', 'product_ids' ], true ) && false === $data ) {
            // @codingStandardsIgnoreStart
            $data = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT $fields FROM {$this->table} as featured $join WHERE %d=%d $where $status $groupby $orderby $limits",
                    $query_args
                )
            );
            // @codingStandardsIgnoreEnd

            Cache::set( $cache_key, $data, $cache_group );
        } elseif ( 'count' === $args['return'] && false === $data ) {
            // @codingStandardsIgnoreStart
            $data = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT $fields FROM {$this->table} as featured $join WHERE %d=%d $where $status $groupby",
                    $query_args
                )
            );
            // @codingStandardsIgnoreEnd
            Cache::set( $cache_key, $data, $cache_group );
        } elseif ( 'individual_count' === $args['return'] ) {
            $data = [
                'all'     => 0,
                'active'  => 0,
                'expired' => 0,
            ];

            // get active count
            $groupby = ' GROUP BY featured.status';
            // @codingStandardsIgnoreStart
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT $fields, featured.status AS status FROM {$this->table} as featured $join WHERE %d=%d $where $groupby",
                    $query_args
                ),
                ARRAY_A
            );
            // @codingStandardsIgnoreEnd

            foreach ( $results as $result ) {
                $count       = (int) $result['count'];
                $data['all'] += $count;
                // count active advertisements
                if ( '1' === $result['status'] ) {
                    $data['active'] = $count;
                    // count expired advertisements
                } elseif ( '2' === $result['status'] ) {
                    $data['expired'] = $count;
                }
            }

            // store on cache
            Cache::set( $cache_key, $data, $cache_group );
        } elseif ( false === $data ) {
            // @codingStandardsIgnoreStart
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT $fields FROM {$this->table} as featured $join WHERE %d=%d $where $status $groupby $orderby $limits",
                    $query_args
                ),
                ARRAY_A
            );
            // @codingStandardsIgnoreEnd

            // if per_page is 1, send single item
            if ( 1 === $args['per_page'] ) {
                $data = is_array( $data ) && ! empty( $data ) ? $data[0] : [];
            }
            // store on cache
            Cache::set( $cache_key, $data, $cache_group );
        }

        return $data;
    }

    /**
     * This method will return a single advertisement table data
     *
     * @since 3.5.0
     *
     * @param int $id
     *
     * @return WP_Error|array
     */
    public function get( $id = 0 ) {
        $args = [
            'id'       => $id,
            'per_page' => 1,
            'return'   => 'all',
        ];

        $data = $this->all( $args );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_advertisement_found', __( 'No advertisement data found with given advertisement id.', 'dokan' ) );
        }

        return $data;
    }

    /**
     * Insert a new advertisement into database.
     *
     * @since 3.5.0
     *
     * @param array $args
     *
     * @return int|WP_Error
     */
    public function insert( $args = [] ) {
        global $wpdb;

        $default = [
            'product_id'  => 0,
            'created_via' => 'admin',
            'order_id'    => 0,
            'price'       => 0.00,
            'expires_at'  => 0,
            'status'      => 1,
            'added'       => time(),
            'updated'     => time(),
        ];

        $args = wp_parse_args( $args, $default );

        // validate required fields
        if ( empty( $args['product_id'] ) ) {
            return new WP_Error( 'insert_advertisement_invalid_product', __( 'Invalid advertisement product id.', 'dokan' ) );
        }

        // fix expire after days
        if ( isset( $args['expires_after_days'] ) ) {
            $expires_after_days = $args['expires_after_days'];
            if ( -1 === $expires_after_days ) {
                $expires_after_days = 0;
            } elseif ( $expires_after_days > 0 ) {
                $expires_after_days = dokan_current_datetime()->modify( "+{$expires_after_days} days" )->getTimestamp();
            }
            $args['expires_at'] = $expires_after_days;
        }

        // just to make sure $args doesn't contain any unnecessary elements
        $data = [
            'product_id'  => $args['product_id'],
            'created_via' => $args['created_via'],
            'order_id'    => $args['order_id'],
            'price'       => $args['price'],
            'expires_at'  => $args['expires_at'],
            'status'      => $args['status'],
            'added'       => $args['added'],
            'updated'     => $args['updated'],
        ];

        $format = [
            '%d',
            '%s',
            '%d',
            '%f',
            '%d',
            '%d',
            '%d',
            '%d',
        ];

        // add advertisement data into database
        $inserted  = $wpdb->insert( $this->get_table(), $data, $format );
        $insert_id = $wpdb->insert_id;

        if ( false === $inserted ) {
            dokan_log( '[Dokan Product Advertisement] Error while inserting advertisement data: <strong>' . $wpdb->last_error . '</strong>, Data: ' . print_r( $data, true ) );
            return new WP_Error( 'insert_advertisement_error', __( 'Something went wrong while inserting advertisement data. Please contact admin.', 'dokan' ) );
        }

        do_action( 'dokan_after_product_advertisement_created', $insert_id, $data, $args );

        return $insert_id;
    }

    /**
     * This method will delete a single advertisement row
     *
     * @param int $id
     *
     * @since 3.5.0
     *
     * @return int|WP_Error
     */
    public function delete( $id = 0 ) {
        global $wpdb;

        $where        = [ 'id' => $id ];
        $format_where = [ '%d' ];

        do_action( 'dokan_before_deleting_product_advertisement', $id );

        $deleted = $wpdb->delete( $this->table, $where, $format_where );

        if ( false === $deleted || ( 0 === $deleted && ! empty( $wpdb->last_error ) ) ) {
            // translators: 1) MySql error
            return new WP_Error( 'item_delete_error', sprintf( __( 'Error while deleting advertisement data. Error: %s', 'dokan' ), $wpdb->last_error ), [ 'status' => 400 ] );
        }

        return $deleted;
    }

    /**
     * This method will batch delete advertisement data
     *
     * @param array $ids
     *
     * @since 3.5.0
     *
     * @return int|WP_Error
     */
    public function batch_delete( $ids = [] ) {
        global $wpdb;

        if ( ! is_array( $ids ) || empty( $ids ) ) {
            return new WP_Error( 'batch_delete_invalid_arg', __( 'No items found to delete.', 'dokan' ), [ 'status' => 400 ] );
        }

        // run absint on array elements
        $ids = array_map( 'absint', $ids );

        do_action( 'dokan_before_batch_delete_product_advertisement', $ids );

        $ids = implode( "','", $ids );

        $deleted = $wpdb->query( "DELETE FROM {$this->table} WHERE id in ('$ids')" ); // phpcs:ignore

        if ( $deleted === 0 && ! empty( $wpdb->last_error ) ) {
            // translators: 1) MySql error
            return new WP_Error( 'batch_delete_error', sprintf( __( 'Error while deleting advertisement data. Error: %s', 'dokan' ), $wpdb->last_error ), [ 'status' => 400 ] );
        }

        return $deleted;
    }

    /**
     * Delete advertisement(s) via product id
     *
     * @since 3.5.0
     *
     * @param $post_id
     *
     * @return bool
     */
    public function delete_advertisement_by_product_id( $product_id ) {
        // check if we got any advertisement data with given product_id
        $advertisement_data = $this->all(
            [
                'product_id' => $product_id,
                'return'     => 'all',
                'per_page'   => 1,
            ]
        );

        if ( empty( $advertisement_data ) ) {
            return false;
        }

        do_action( 'dokan_before_deleting_product_advertisement', $advertisement_data['id'] );

        global $wpdb;

        $where = [
            'product_id' => $product_id,
        ];
        // format where
        $format_where = [ '%d' ];

        return $wpdb->delete( $this->table, $where, $format_where );
    }

    /**
     * This method will batch expire
     *
     * @since 3.5.0
     *
     * @param array $ids
     *
     * @return int|WP_Error
     */
    public function batch_expire( $ids = [] ) {
        global $wpdb;

        if ( ! is_array( $ids ) || empty( $ids ) ) {
            return new WP_Error( 'batch_expire_invalid_arg', __( 'No items found to expire.', 'dokan' ), [ 'status' => 400 ] );
        }

        // run absint on array elements and implode into comma separated string
        $ids = array_map( 'absint', $ids );

        do_action( 'dokan_before_batch_expire_product_advertisement', $ids );

        $joined_ids = implode( "','", $ids );

        $now = dokan_current_datetime();
        $now = $now->getTimestamp();

        $updated = $wpdb->query( "UPDATE {$this->table} SET `status` = 2, expires_at = {$now}  WHERE id in ('$joined_ids')" ); // phpcs:ignore

        if ( $updated === 0 && ! empty( $wpdb->last_error ) ) {
            // translators: 1) mySql error
            return new WP_Error( 'batch_expire_error', sprintf( __( 'Error while updating advertisement data. Error: %s', 'dokan' ), $wpdb->last_error ), [ 'status' => 400 ] );
        }

        do_action( 'dokan_after_batch_expire_product_advertisement', $ids );

        return $updated;
    }

    /**
     * This method will expire advertisements
     *
     * @since 3.5.0
     *
     * @param int|null $timestamp
     */
    public function expire_advertisement_by_date( $timestamp = null ) {
        global $wpdb;
        // in case of empty timestamp, we will expire advertisement till yesterday
        if ( null === $timestamp ) {
            //get today midnight
            $timestamp = dokan_current_datetime()->modify( 'midnight' )->getTimestamp();
        }

        if ( ! is_numeric( $timestamp ) ) {
            return; // invalid timestamp
        }

        // get unique vendor ids, we need this in order to clear cache, 1 is for active advertisements
        // @codingStandardsIgnoreStart
        $unique_seller_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT distinct post.post_author as vendor_id
                    from {$this->get_table()} as featured
                    LEFT JOIN {$wpdb->posts} AS post ON featured.product_id = post.ID
                    WHERE featured.status = %d AND featured.expires_at != %d AND featured.expires_at < %d",
                [ 1, 0, $timestamp ]
            )
        );
        // @codingStandardsIgnoreEnd

        //return if we didn't found any data
        if ( empty( $unique_seller_ids ) ) {
            return;
        }

        if ( Helper::is_featured_enabled() ) {
            // get unique product id
            // @codingStandardsIgnoreStart
            $unique_product_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT featured.product_id FROM {$this->get_table()} AS featured WHERE featured.status = %d AND featured.expires_at < %d",
                    [ 1, $timestamp ]
                )
            );
            // @codingStandardsIgnoreEnd

            foreach ( $unique_product_ids as $product_id ) {
                Helper::make_product_featured( $product_id, false );
            }
        }

        // Set advertisement status as expired, 2 is for expired advertisements
        // @codingStandardsIgnoreStart
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->get_table()} as featured
                    SET featured.status = %d
                    WHERE featured.status = 1 AND featured.expires_at < %d",
                [ 2, $timestamp ]
            )
        );
        // @codingStandardsIgnoreEnd

        if ( false === $updated ) {
            // in case of errors, log error
            dokan_log( '[Dokan Product Advertisement] Error while expiring advertisements. Error: ' . $wpdb->last_error );
            return; // no need to clear advertisement cache
        }

        // clear individual seller cache
        foreach ( $unique_seller_ids as $seller_id ) {
            // delete individual seller cache
            if ( is_numeric( $seller_id ) ) {
                $cache_group = "advertised_product_{$seller_id}";
                Cache::invalidate_group( $cache_group );
            }
        }

        // clear global advertisement cache
        $cache_group = 'advertised_product';
        Cache::invalidate_group( $cache_group );
    }

    /**
     * This method will get a single vendor advertisement records
     *
     * @since 3.5.0
     *
     * @param array $args
     *
     * @return array
     */
    public function get_advertisements_by_vendor( $args = [] ) {
        $default = [
            'vendor_id' => dokan_get_current_user_id(),
            'status'    => 1,
            'per_page'  => -1,
            'return'    => 'all',
        ];

        $args = wp_parse_args( $args, $default );

        $cache_group = "advertised_product_{$args['vendor_id']}";
        $cache_key   = 'get_advertisement_by_vendor_' . md5( wp_json_encode( $args ) );
        // get result from cache
        $data = Cache::get( $cache_key, $cache_group );
        if ( false !== $data ) {
            return $data;
        }

        $results = $this->all( $args );
        $data    = [];

        foreach ( $results as $result ) {
            $data[ $result['product_id'] ] = $result;
        }

        Cache::set( $cache_key, $data, $cache_group );
        return $data;
    }

    /**
     * This method will return advertised products stores only
     *
     * @param array $args
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_stores( $args = [] ) {
        $default = [
            'per_page'    => 20,
            'page'        => 1,
        ];

        $args = wp_parse_args( $args, $default );

        global $wpdb;

        $fields      = ' DISTINCT post.post_author as vendor_id, u_meta.meta_value as store_name ';
        $join        = " LEFT JOIN {$wpdb->posts} AS post ON featured.product_id = post.ID LEFT JOIN {$wpdb->usermeta} AS u_meta ON post.post_author = u_meta.user_id";
        $where       = ' AND u_meta.meta_key=%s';
        $groupby     = '';
        $orderby     = ' ORDER BY store_name ASC';
        $limits      = '';
        $query_args  = [ 1, 1, 'dokan_store_name' ];

        // prepare search parameter
        if ( ! empty( $args['search'] ) ) {
            $like         = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where        .= ' AND u_meta.meta_value like %s';
            $query_args[] = $like;
        }

        if ( $args['per_page'] ) {
            $limit  = absint( $args['per_page'] );
            $page   = absint( $args['page'] );
            $page   = $page ? $page : 1;
            $offset = ( $page - 1 ) * $limit;

            $limits       = 'LIMIT %d, %d';
            $query_args[] = $offset;
            $query_args[] = $limit;
        }

        // @codingStandardsIgnoreStart
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT $fields FROM {$wpdb->prefix}dokan_advertised_products as featured $join WHERE %d=%d $where $groupby $orderby $limits", //phpcs:ignore
                $query_args
            ),
            ARRAY_A
        );
        // @codingStandardsIgnoreEnd

        return null === $data ? [] : $data;
    }

    /**
     * Get advertisements to display
     *
     * @since 3.5.0
     *
     * @param array $atts
     *
     * @return bool|\WP_Query false if no advertisement exists
     */
    public function get_advertisement_for_display( $atts ) {
        $defaults = [
            'count'     => get_option( 'woocommerce_catalog_columns', 3 ),
            'vendor_id' => '', // comma separated values
            'order'     => 'ASC',
            'orderby'   => 'product_title',
        ];
        $atts = wp_parse_args( $atts, $defaults );

        // fix vendor_id param
        if ( ! empty( $atts['vendor_id'] ) ) {
            $atts['vendor_id'] = array_map( 'absint', explode( ',', $atts['vendor_id'] ) );
        }

        // fix order param
        if ( ! in_array( strtolower( $atts['order'] ), [ 'asc', 'desc' ], true ) ) {
            $atts['order'] = 'ASC';
        }

        // fix orderby param
        $supported_orderby = [
            'product_title',
            'added',
            'expires_at',
            'views',
            'price',
        ];

        if ( ! empty( $atts['orderby'] ) && ! in_array( $atts['orderby'], $supported_orderby, true ) ) {
            $atts['orderby'] = 'product_title';
        }

        if ( ! empty( $atts['vendor_only_advertisement'] ) && false !== Helper::get_vendor_from_single_store_page() ) {
            $atts['vendor_id'] = Helper::get_vendor_from_single_store_page();
        }

        // prepare args
        $args = [
            'status'      => 1,
            'post_status' => 'publish',
            'per_page'    => intval( $atts['count'] ),
            'vendor_id'   => $atts['vendor_id'],
            'order'       => $atts['order'],
            'orderby'     => $atts['orderby'],
            'return'      => 'product_ids',
        ];

        $product_ids = $this->all( $args );

        if ( empty( $product_ids ) ) {
            return false;
        }

        $query_args = [
            'post_type'   => 'product',
            'post_status' => 'publish',
            'post__in'    => $product_ids,
        ];

        if ( Helper::is_hide_out_of_stock_products_enabled() ) {
            // get post via ids
            $product_visibility_term_ids = wc_get_product_visibility_term_ids();
            $query_args['tax_query'][] = array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $product_visibility_term_ids['outofstock'],
                    'operator' => 'NOT IN',
                ),
            ); // phpcs:ignore slow query ok.
        }

        $products = new \WP_Query( $query_args );

        return $products->have_posts() ? $products : false;
    }

    /**
     * Get advertisement table with prefix
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_table() {
        return $this->table;
    }

    /**
     * This will check if given var is empty or not.
     *
     * @since 3.5.0
     *
     * @param mixed $var
     *
     * @return bool
     */
    protected function is_empty( $var ) {
        if ( empty( $var ) ) {
            return true;
        }

        if ( isset( $var[0] ) && intval( $var[0] === 0 ) ) {
            return true;
        }

        return false;
    }
}
