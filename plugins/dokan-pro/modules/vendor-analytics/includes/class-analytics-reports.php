<?php

/**
 * Dokan_Vendor_Analytics_Reports class
 */
class Dokan_Vendor_Analytics_Reports {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
    }

    /**
      * Handle product for staff uploading and editing
      *
      * @since 1.0.0
      *
      * @return void
      */
    public function filter_page_path( $filter ) {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! current_user_can( 'dokandar' ) ) {
            return;
        }

        $vendor_id  = get_current_user_id();
        $userdata = get_userdata( $vendor_id );
        $user_nicename = ( ! false == $userdata ) ? $userdata->user_nicename : '';
        $custom_store_url = dokan_get_option( 'custom_store_url', 'dokan_general', 'store' );
        $store_url_query = 'ga:pagePath==/' . $custom_store_url . '/' . $user_nicename . '/';

        $product_ids = $this->get_vendor_product_ids( $vendor_id );
        $product_url_query = '';

        if ( count( $product_ids ) ) {
            foreach ( $product_ids as $id ) {
                $product_url_query .= str_replace( home_url(), 'ga:pagePath==', get_permalink( $id ) ) . ',';
            }
        }

        $filter = $filter ? $filter . ',' : '';

        return $filter .= $product_url_query . $store_url_query;
    }

    /**
      * Handle load analytics connection
      *
      * @since 1.0.0
      *
      * @return void
      */
    public function load_dokan_vendor_analytics() {
        include_once DOKAN_VENDOR_ANALYTICS_TOOLS_DIR . '/src/Dokan/autoload.php';

        $client = dokan_vendor_analytics_client();
        $token  = dokan_vendor_analytics_token();

        if ( empty( json_decode( $token, true ) ) ) {
            return null;
        }

        $client->setAccessToken( $token );

        if ( $client->isAccessTokenExpired() ) {
            $refresh_token = $client->getRefreshToken();

            $response = wp_remote_post(
                dokan_vendor_analytics_get_refresh_token_url(), array(
					'body' => array(
						'refresh_token' => $refresh_token,
					),
                )
            );

            if ( is_wp_error( $response ) ) {
                dokan_log( $response->get_error_message() );
                return null;
            }

            $token = wp_remote_retrieve_body( $response );

            $client->setAccessToken( $token );

            $api_data  = get_option( 'dokan_vendor_analytics_google_api_data', array() );
            $api_data['token'] = $token;

            update_option( 'dokan_vendor_analytics_google_api_data', $api_data, false );
        }

        $profile_id = dokan_get_option( 'profile', 'dokan_vendor_analytics', null );

        return array(
			'client' => $client,
			'profile_id' => $profile_id,
		);
    }

    /**
     * Get dokan vendor analytics
     *
     * @param string $start_date
     * @param string $end_date
     * @param string $metrics
     * @param bool $dimensions
     * @param bool $sort
     * @param bool $filter
     * @param bool $limit
     *
     * @since 1.0.0
     *
     * @return Dokan_Service_Analytics_GaData|WP_Error|null
     */
    public function dokan_get_vendor_analytics( $start_date = '30daysAgo', $end_date = 'yesterday', $metrics = 'ga:users', $dimensions = false, $sort = false, $filter = false, $limit = false ) {
        try {
            $params = [];

            if ( $dimensions ) {
                $params['dimensions'] = $dimensions;
            }
            if ( $sort ) {
                $params['sort'] = $sort;
            }
            if ( $limit ) {
                $params['max-results'] = $limit;
            }
            $params['filters'] = $this->filter_page_path( $filter );

            $dokan_analytics = $this->load_dokan_vendor_analytics();

            if ( empty( $dokan_analytics['client'] ) ) {
                return null;
            }

            $service = new Dokan_Service_Analytics( $dokan_analytics['client'] );
            $result  = $service->data_ga->get( $dokan_analytics['profile_id'], $start_date, $end_date, $metrics, $params );

            return $result;
        } catch ( Exception $e ) {
            return new WP_Error(
                'dokan_get_vendor_analytics',
                $e->getMessage()
            );
        }
    }

    /**
     * analytics content
     *
     * @since 1.0.0
     */
    public function get_analytics_content( $metrics, $dimensions, $sort, $headers ) {
        $start_date = date( 'Y-m-01', current_time( 'timestamp' ) );
        $end_date = date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) );
        if ( isset( $_POST['dokan_analytics_filter'] ) ) {
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
        }
        $result = $this->dokan_get_vendor_analytics( $start_date, $end_date, $metrics, $dimensions, $sort, false, 5 );

        if ( empty( $result->totalsForAllResults ) ) {
            echo __( 'There is no analytics found for your store.', 'dokan' );
            return;
        }

        dokan_analytics_date_form( $start_date, $end_date );
        if ( $result->rows ) {
            ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php
                        foreach ( $headers as $header ) {
                            echo '<th>' . $header . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $result->rows as $row ) {
                        ?>
                        <tr>
                            <?php
                            foreach ( $row as $key => $column ) {
                                // if ( in_array( $result['columnHeaders'][$key]->getName(), array( 'ga:avgTimeOnPage', 'ga:entranceRate', 'ga:exitRate' ) ) ) {
                                //     $column = round( $column, 2 );
                                // }
                                switch ( $result['columnHeaders'][ $key ]->getName() ) {
                                    case 'ga:avgTimeOnPage':
                                        $column = round( $column, 2 );
                                        break;
                                    case 'ga:entranceRate':
                                        $column = round( $column, 2 ) . '%';
                                        break;
                                    case 'ga:exitRate':
                                        $column = round( $column, 2 ) . '%';
                                        break;
                                }
                                echo '<td>' . $column . '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        } else {
            echo 'No data found.';
        }
    }

    /**
     * Listing product argument filter
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_vendor_product_ids( $vendor_id ) {
        $products = new WP_Query(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'fields' => 'ids',
                'author' => $vendor_id,
                'nopaging' => true,
            )
        );

        return $products->posts;
    }

}
