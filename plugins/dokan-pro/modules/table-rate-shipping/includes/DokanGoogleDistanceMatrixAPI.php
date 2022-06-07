<?php

namespace WeDevs\DokanPro\Modules\TableRate;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * API class for Google Distance Matrix API.
 * 
 * Google Distance Matrix API class, handles all API calls to Google Distance
 * Matrix API
 *
 * @since 3.4.2
 */
class DokanGoogleDistanceMatrixAPI {

    /**
     * API URL
     *
     * @since 3.4.2
     */
    const API_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    /**
     * API Key.
     *
     * @since 3.4.2
     *
     * @var String
     */
    public $api_key;

    /**
     * Debug mode.
     *
     * @since 3.4.2
     *
     * @var string
     */
    public $debug;

    /**
     * Constructor.
     *
     * Set properties.
     *
     * @since 3.4.2
     *
     * @param string $api_key API key.
     * @param bool   $debug   Whether debug is enabled or not.
     *
     * @return void
     */
    public function __construct( $api_key, $debug ) {
        $this->api_key = $api_key;
        $this->debug   = $debug;
    }

    /**
     * Make a call to the Google Distance Matrix API.
     *
     * @since 3.4.2
     *
     * @throws Exception Request error.
     *
     * @param string $params Request params.
     *
     * @return WP_Error|array The response from remote get.
     */
    private function perform_request( $params ) {
        $args = array(
            'timeout'     => apply_filters( 'google_distance_matrix_api_timeout', 3 ), // Default to 3 seconds.
            'redirection' => 0,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'blocking'    => true,
            'user-agent'  => 'PHP ' . PHP_VERSION . '/WooCommerce ' . get_option( 'woocommerce_db_version' ),
        );

        $response = wp_remote_get( self::API_URL . '?' . ( ! empty( $this->api_key ) ? 'key=' . $this->api_key . '&' : '' ) . $params, $args );

        if ( $this->debug ) {
            parse_str( $params, $params_debug );
            wc_add_notice( 'Request: <br/><pre>' . print_r( $params_debug, true ) . '</pre>', 'notice' );
            wc_add_notice( 'Response: <br/><pre>' . print_r( $response, true ) . '</pre>', 'notice' );
        }

        if ( is_wp_error( $response ) ) {
            throw new Exception( $response );
        }

        return $response;
    }

    /**
     * Get the distance based on origin and destination address.
     *
     * @since 3.4.2
     *
     * @param  string $origin      Origin.
     * @param  string $destination Destination.
     * @param  string $sensor      Sensor.
     * @param  string $mode        Mode.
     * @param  string $avoid       Avoid.
     * @param  string $units       Units.
     * @param  mixed  $region      Region.
     * @return array
     */
    public function get_distance( $origin, $destination, $sensor = 'false', $mode = 'driving', $avoid = '', $units = 'metric', $region = false ) {
        $transient = md5(
            http_build_query(
                array(
                    'name'        => 'dokan_distance_rate',
                    'origin'      => $origin,
                    'destination' => $destination,
                    'sensor'      => $sensor,
                    'mode'        => $mode,
                    'avoid'       => $avoid,
                    'units'       => $units,
                    'region'      => $region,
                )
            )
        );

        $distance = get_transient( $transient );

        if ( false === $distance ) {
            if ( $this->debug ) {
                wc_add_notice( 'Distance not found in cache, will perform API request.', 'notice' );
            }

            $params                 = array();
            $params['origins']      = $origin;
            $params['destinations'] = $destination;
            $params['mode']         = $mode;
            $params['units']        = $units;
            $params['sensor']       = $sensor;

            if ( ! empty( $avoid ) ) {
                $params['avoid'] = $avoid;
            }

            if ( ! empty( $region ) ) {
                $params['region'] = $region;
            }

            /**
             * Filters the Google Distance Matrix API request parameters.
             *
             * @param array $params Parameters to pass to the API request.
             *
             * @since 3.4.2
             */
            $params   = apply_filters( 'dokan_google_distance_matrix_request_params', $params );
            $params   = http_build_query( $params );
            $response = $this->perform_request( $params );
            $distance = json_decode( $response['body'] );

            /**
             * Filter cache expiration of calculated distance.
             *
             * @param int $expiration The maximum of seconds to keep the data
             *                        before refreshing. Default to one week.
             *
             * @since 3.4.2
             */
            $expiration = apply_filters( 'dokan_distance_rate_shipping_cache_expiration', 7 * DAY_IN_SECONDS );

            // Only put valid results in transient.
            if (
                isset( $distance->rows[0]->elements[0]->status ) &&
                ( 'OK' === $distance->rows[0]->elements[0]->status )
            ) {
                set_transient( $transient, $distance, $expiration );
            }
        } elseif ( $this->debug ) {
            wc_add_notice( 'Using cached distance.', 'notice' );
            wc_add_notice( 'Response: <br/><pre>' . print_r( $distance, true ) . '</pre>', 'notice' );
        }

        /**
         * Filter response from Google Distance Matrix API.
         *
         * @since 3.4.2
         *
         * @param object $distance Response body.
         */
        return apply_filters( 'dokan_distance_rate_shipping_api_response', $distance );
    }
}
