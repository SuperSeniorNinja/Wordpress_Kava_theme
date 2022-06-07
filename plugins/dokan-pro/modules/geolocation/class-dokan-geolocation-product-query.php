<?php

/**
 * Module related WP_Query filters for WC Products
 *
 * @since 1.0.0
 */
class Dokan_Geolocation_Product_Query {

    /**
     * Latitude query value
     *
     * @since 1.0.0
     *
     * @var float
     */
    private $latitude = 0;

    /**
     * Longitude query value
     *
     * @since 1.0.0
     *
     * @var float
     */
    private $longitude = 0;

    /**
     * Distance/Radius query value
     *
     * @since 1.0.0
     *
     * @var int
     */
    private $distance = 0;

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'woocommerce_product_query', array( $this, 'add_query_filters' ) );
    }

    /**
     * Add WooCommerce product query filters
     *
     * @since 1.0.0
     *
     * @param \WP_Query $query
     */
    public function add_query_filters( $query ) {
        if ( ! $this->is_geolocation_show_on_shop_page() ) {
            return;
        }

        $this->latitude  = isset( $_GET['latitude'] ) ? $_GET['latitude'] : null;
        $this->longitude = isset( $_GET['longitude'] ) ? $_GET['longitude'] : null;
        $this->distance  = isset( $_GET['distance'] ) ? $_GET['distance'] : 0;

        add_filter( 'posts_fields_request', array( $this, 'posts_fields_request' ) );
        add_filter( 'posts_join_request', array( $this, 'posts_join_request' ) );
        add_filter( 'posts_groupby_request', array( $this, 'posts_groupby_request' ) );
    }

    /**
     * Add extra select fields
     *
     * @since 1.0.0
     *
     * @param string $fields
     *
     * @return string
     */
    public function posts_fields_request( $fields ) {
        $fields .= ', metalat.meta_value as dokan_geo_latitude, metalong.meta_value as dokan_geo_longitude, metaaddr.meta_value as dokan_geo_address';

        if ( $this->latitude && $this->longitude ) {
            // unit in kilometers or miles
            $distance_unit = dokan_get_option( 'distance_unit', 'dokan_geolocation', 'km' );

            $distance_earth_center_to_surface = ( 'km' === $distance_unit ) ? 6371 : 3959;

            $fields .= ", (
                {$distance_earth_center_to_surface} * acos(
                    cos( radians( {$this->latitude} ) ) *
                    cos( radians( metalat.meta_value ) ) *
                    cos(
                        radians( metalong.meta_value ) - radians( {$this->longitude} )
                    ) +
                    sin( radians( {$this->latitude} ) ) *
                    sin( radians( metalat.meta_value ) )
                )
            ) as geo_distance";
        }

        remove_filter( 'posts_fields_request', array( $this, 'posts_fields_request' ) );

        return $fields;
    }

    /**
     * Add extra join SQL statements
     *
     * @since 1.0.0
     *
     * @param string $join
     *
     * @return string
     */
    public function posts_join_request( $join ) {
        global $wpdb;

        $join .= " inner join {$wpdb->postmeta} as metalat on {$wpdb->posts}.ID = metalat.post_id and metalat.meta_key = 'dokan_geo_latitude'";
        $join .= " inner join {$wpdb->postmeta} as metalong on {$wpdb->posts}.ID = metalong.post_id and metalong.meta_key = 'dokan_geo_longitude'";
        $join .= " inner join {$wpdb->postmeta} as metaaddr on {$wpdb->posts}.ID = metaaddr.post_id and metaaddr.meta_key = 'dokan_geo_address'";

        remove_filter( 'posts_join_request', array( $this, 'posts_join_request' ) );

        return $join;
    }

    /**
     * Add HAVING clause after GROUP BY clause
     *
     * @since 1.0.0
     *
     * @param string $groupby
     *
     * @return string
     */
    public function posts_groupby_request( $groupby ) {
        if ( $this->latitude && $this->longitude && $this->distance ) {
            $distance = absint( $this->distance );
            $groupby .= " having geo_distance < {$distance}";
        }

        remove_filter( 'posts_groupby_request', array( $this, 'posts_groupby_request' ) );

        return $groupby;
    }

    /**
     * Is geolocation show on shop page
     *
     * @since 3.3.0
     *
     * @return bool
     */
    public function is_geolocation_show_on_shop_page() {
        $show_map_pages = dokan_get_option( 'show_location_map_pages', 'dokan_geolocation', 'shop' );

        if ( ( is_shop() || is_product_taxonomy() ) && ( 'shop' === $show_map_pages || 'all' === $show_map_pages ) ) {
            return true;
        }

        return false;
    }
}
