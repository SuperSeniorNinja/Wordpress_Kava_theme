<?php

/**
 * Include Dokan Geolocation template
 *
 * @since 1.0.0
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_geo_get_template( $name, $args = [] ) {
    dokan_get_template( "$name.php", $args, 'dokan/modules/geolocation', trailingslashit( DOKAN_GEOLOCATION_VIEWS ) );
}

/**
 * Default geolocation latitude and longitude
 *
 * @since 1.0.0
 *
 * @return array
 */
function dokan_geo_get_default_location() {
    $location = dokan_get_option( 'location', 'dokan_geolocation' );

    if ( empty( $location['latitude'] ) || empty( $location['longitude'] ) ) {
        $location              = [];
        $location['latitude']  = 23.709921;
        $location['longitude'] = 90.40714300000002;
        $location['address']   = 'Dhaka';
    }

    /**
     * Filter default latitude and longitude use by Geolocation module
     *
     * @since 1.0.0
     *
     * @param array $location
     */
    return apply_filters( 'dokan_geolocation_default_location', $location );
}

/**
 * Enqueue locations map style and scripts
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_geo_enqueue_locations_map() {
    ob_start();
    dokan_geo_get_template( 'map-marker-info-window' );
    $info_window_template = ob_get_clean();

    dokan()->scripts->load_gmap_script();

    wp_enqueue_style( 'dokan-geo-locations-map' );
    wp_enqueue_script( 'dokan-geo-locations-map' );

    /**
     * Filter to modify the map marker image
     *
     * @since 1.0.0
     *
     * @param string $marker_image_path
     */
    $image = apply_filters( 'dokan_geolocation_marker_image_path', DOKAN_GEOLOCATION_ASSETS . '/images/marker-32x32.png' );

    /**
     * Filter to modify the map marker clusterer images
     *
     * @since 1.0.0
     *
     * @param string
     */
    $clusterer = apply_filters( 'dokan_geolocation_marker_clusterer_image_path', DOKAN_GEOLOCATION_ASSETS . '/images/clusterer-40x40.png' );

    $dokan_geo = array(
        'marker' => array(
            'image'     => $image,
            'clusterer' => $clusterer,
        ),
        'info_window_template' => $info_window_template,
        'default_geolocation'  => dokan_geo_get_default_location(),
        'map_zoom'             => dokan_get_option( 'map_zoom', 'dokan_geolocation', 11 ),
        'is_auto_zoom'         => is_singular( 'product' ) ? 0 : 1, // Autozoom only work when pass 1
    );

    $source = dokan_get_option( 'map_api_source', 'dokan_appearance', 'google_maps' );

    if ( 'mapbox' === $source ) {
        $dokan_geo['mapbox_access_token'] = dokan_get_option( 'mapbox_access_token', 'dokan_appearance', null );
    }

    wp_localize_script( 'dokan-geo-locations-map', 'DokanGeo', $dokan_geo );
}

/**
 * Dokan Geolocation Filter Form
 *
 * @since 1.0.0
 *
 * @param string $scope   null|product|vendor
 * @param string $display inline|block
 *
 * @return void
 */
function dokan_geo_filter_form( $scope = '', $display = 'inline' ) {
    global $wp;

    dokan()->scripts->load_gmap_script();

    wp_enqueue_style( 'dokan-geo-filters' );
    wp_enqueue_script( 'dokan-geo-filters' );

    $get_data = wp_unslash( $_GET ); // phpcs:ignore

    $s             = get_query_var( 's', '' );
    $seller_s      = isset( $get_data['dokan_seller_search'] ) ? sanitize_text_field( $get_data['dokan_seller_search'] ) : '';
    $search_query  = $seller_s;
    $latitude      = isset( $get_data['latitude'] ) ? sanitize_text_field( $get_data['latitude'] ) : null;
    $longitude     = isset( $get_data['longitude'] ) ? sanitize_text_field( $get_data['longitude'] ) : null;
    $address       = isset( $get_data['address'] ) ? sanitize_text_field( $get_data['address'] ) : '';
    $distance_min  = dokan_get_option( 'distance_min', 'dokan_geolocation', 0 );
    $distance_max  = dokan_get_option( 'distance_max', 'dokan_geolocation', 10 );
    $distance_unit = dokan_get_option( 'distance_unit', 'dokan_geolocation', 'km' );
    $distance      = isset( $get_data['distance'] ) ? sanitize_text_field( $get_data['distance'] ) : $distance_max;

    /**
     * Add wc_product_dropdown_categories argument filter
     *
     * @since 1.0.0
     *
     * @param array $args
     */
    $wc_categories_args = apply_filters(
        'dokan_geolocation_product_dropdown_categories_args', array(
            'pad_counts' => 0,
            'show_count' => 0,
        )
    );

    $get_current_page_url = home_url();

    $store_listing_page_url = $get_current_page_url;

    if ( $scope === 'vendor' || $scope === '' ) {
        $store_listing_page_url = get_permalink( dokan_get_option( 'store_listing', 'dokan_pages', 0 ) );
    }

    global $post;
    if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'dokan-stores' ) ) {
        $store_listing_page_url = home_url( $wp->request );
    }

    $args = array(
        'scope'      => $scope,
        'display'    => $display,
        's'          => $s,
        'seller_s'   => $seller_s,
        'latitude'   => $latitude,
        'longitude'  => $longitude,
        'address'    => $address,
        'distance'   => absint( $distance ),
        'placeholders' => array(
            'search_all'      => dokan_get_option( 'placeholder_search_all', 'dokan_geolocation', __( 'Search Vendors or Products', 'dokan' ) ),
            'search_vendors'  => dokan_get_option( 'placeholder_search_vendors', 'dokan_geolocation', __( 'Search Vendors', 'dokan' ) ),
            'search_products' => dokan_get_option( 'placeholder_search_product', 'dokan_geolocation', __( 'Search Products', 'dokan' ) ),
            'location'        => dokan_get_option( 'placeholder_location', 'dokan_geolocation', __( 'Location', 'dokan' ) ),
        ),
        'slider' => array(
            'min'      => $distance_min,
            'max'      => $distance_max,
            'unit'     => ( 'km' === $distance_unit ) ? 'km' : 'miles',
        ),
        'wc_categories_args' => $wc_categories_args,
        'wc_shop_page'       => $get_current_page_url,
        'store_listing_page' => $store_listing_page_url,
    );

    if ( dokan_is_store_categories_feature_on() ) {
        $args['categories'] = get_terms(
            array(
                'taxonomy'   => 'store_category',
                'hide_empty' => false,
            )
        );

        $args['store_category'] = ! empty( $get_data['store_categories'] ) ? sanitize_text_field( $get_data['store_categories'] ) : null;
    }

    $source = dokan_get_option( 'map_api_source', 'dokan_appearance', 'google_maps' );

    if ( 'mapbox' === $source ) {
        $mapbox_access_token = dokan_get_option( 'mapbox_access_token', 'dokan_appearance', null );

        if ( $mapbox_access_token ) {
            $args['mapbox_access_token'] = $mapbox_access_token;
        }
    }

    dokan_geo_get_template( 'filters', $args );
}

/**
 * Prints product location map with address
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_geo_product_location() {
    global $product;

    if ( ! $product instanceof WC_Product ) {
        $product = wc_get_product( get_the_ID() );
    }

    dokan_geo_enqueue_locations_map();

    $args = array(
        'address' => $product->get_meta( 'dokan_geo_address', true ),
    );

    dokan_geo_get_template( 'product-location', $args );
}

/**
 * A helper function to remove Geolocation hook in seller listing footer content
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_geo_remove_seller_listing_footer_content_hook() {
    add_action(
        'dokan_seller_listing_footer_content', function () {
            remove_action( 'dokan_seller_listing_footer_content', array( Dokan_Geolocation_Vendor_View::class, 'seller_listing_footer_content' ), 11, 1 );
        }, 9
    );
}

/**
 * A helper function to escape float values
 *
 * @since 2.9.5
 *
 * @return float
 */
function dokan_geo_float_val( $val ) {
    return floatval( preg_replace( '/[^-0-9\.]/', '', $val ) );
}

/**
 * Dokan Geolocation Store Lists Filter Form
 *
 * @since 3.0.0
 *
 * @return void
 */
function dokan_geo_store_lists_filter_form() {
    dokan()->scripts->load_gmap_script();

    wp_enqueue_style( 'dokan-geo-filters' );
    wp_enqueue_script( 'dokan-geo-filters-store-lists' );

    $get_data = wp_unslash( $_GET ); // phpcs:ignore

    $latitude      = isset( $get_data['latitude'] ) ? sanitize_text_field( $get_data['latitude'] ) : null;
    $longitude     = isset( $get_data['longitude'] ) ? sanitize_text_field( $get_data['longitude'] ) : null;
    $address       = isset( $get_data['address'] ) ? sanitize_text_field( $get_data['address'] ) : '';
    $distance_min  = dokan_get_option( 'distance_min', 'dokan_geolocation', 0 );
    $distance_max  = dokan_get_option( 'distance_max', 'dokan_geolocation', 10 );
    $distance_unit = dokan_get_option( 'distance_unit', 'dokan_geolocation', 'km' );
    $distance      = isset( $get_data['distance'] ) ? sanitize_text_field( $get_data['distance'] ) : $distance_max;

    $args = [
        'latitude'  => $latitude,
        'longitude' => $longitude,
        'address'   => $address,
        'distance'  => absint( $distance ),
        'placeholders' => [
            'location'       => dokan_get_option( 'placeholder_location', 'dokan_geolocation', __( 'Location', 'dokan' ) ),
            'search_vendors' => dokan_get_option( 'placeholder_search_vendors', 'dokan_geolocation', __( 'Search Vendors', 'dokan' ) ),
        ],
        'slider' => [
            'min'  => $distance_min,
            'max'  => $distance_max,
            'unit' => ( 'km' === $distance_unit ) ? 'km' : 'miles',
        ],
    ];

    $source = dokan_get_option( 'map_api_source', 'dokan_appearance', 'google_maps' );

    if ( 'mapbox' === $source ) {
        $mapbox_access_token = dokan_get_option( 'mapbox_access_token', 'dokan_appearance', null );

        if ( $mapbox_access_token ) {
            $args['mapbox_access_token'] = $mapbox_access_token;
        }
    }

    dokan_geo_get_template( 'store-lists-filters', $args );
}
