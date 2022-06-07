<?php
/**
 * Returns the tabs for the analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_get_analytics_tabs() {
    $tabs = array(
        'title'  => __( 'Analytics', 'dokan' ),
        'tabs' => array(
            "general"   => array(
                'title'       => __( 'General', 'dokan' ),
                'function'    => 'dokan_general_analytics'
            ),
            "pages"     => array(
                'title'       => __( 'Top pages', 'dokan' ),
                'function'    => 'dokan_page_analytics'
            ),
            "activity"  => array(
                'title'       => __( 'Activity', 'dokan' ),
                'function'    => 'dokan_activity_analytics'
            ),
            "geographic"=> array(
                'title'       => __( 'Location', 'dokan' ),
                'function'    => 'dokan_geographic_analytics'
            ),
            "system"    => array(
                'title'       => __( 'System', 'dokan' ),
                'function'    => 'dokan_system_analytics'
            ),
            "promotions"=> array(
                'title'       => __( 'Promotions', 'dokan' ),
                'function'    => 'dokan_promotion_analytics'
            ),
            "keyword"   => array(
                'title'       => __( 'Keyword', 'dokan' ),
                'function'    => 'dokan_keyword_analytics'
            )
        )
    );

    return apply_filters( 'dokan_analytics_tabs', $tabs );
}

/**
 * Returns date form
 *
 * @since 1.0
 * @return array
 */
function dokan_analytics_date_form( $start_date, $end_date ) {
?>
    <form method="post" class="dokan-form-inline report-filter dokan-clearfix" action="">
        <div class="dokan-form-group">
            <label for="from"><?php _e( 'From:', 'dokan' ); ?></label> <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( $start_date ); ?>" />
        </div>

        <div class="dokan-form-group">
            <label for="to"><?php _e( 'To:', 'dokan' ); ?></label>
            <input type="text" name="end_date" id="to" class="datepicker" readonly="readonly" value="<?php echo esc_attr( $end_date ); ?>" />

            <input type="submit" name="dokan_analytics_filter" class="dokan-btn dokan-btn-success dokan-btn-sm dokan-theme" value="<?php _e( 'Show', 'dokan' ); ?>" />
        </div>
    </form>
    <?php
}

/**
 * Returns general analytics
 *
 * @since 1.0
 * @return void
 */
function dokan_general_analytics() {
    $metrics    = 'ga:users,ga:sessions,ga:users,ga:pageviews,ga:bounceRate,ga:newUsers,ga:sessionDuration';
    $dimensions = 'ga:date';
    $sort       = '-ga:sessions';

    $start_date = date( 'Y-m-01', current_time('timestamp') );
    $end_date = date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) );
    if ( isset( $_POST['dokan_analytics_filter'] ) ) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
    }
    $Vendor_filter = new Dokan_Vendor_Analytics_Reports();
    $result = $Vendor_filter->dokan_get_vendor_analytics( $start_date, $end_date, $metrics, $dimensions, $sort );

    if ( empty( $result->totalsForAllResults ) ) {
        echo __( 'There is no analytics found for your store.', 'dokan' );
        return;
    }

    $analytics_total = $vendor_analytics = array();

    foreach ( $result->totalsForAllResults as $key => $row ) {
        $new_key = str_replace('ga:', '', $key);
        $analytics_total[$new_key] = $row;
    }
    foreach ( $result->rows as $key => $row ) {
        $vendor_analytics[$key] = new stdClass();
        $vendor_analytics[$key]->post_date  = $row[0];
        $vendor_analytics[$key]->users      = $row[1];
        $vendor_analytics[$key]->sessions   = $row[2];
    }
    dokan_analytics_date_form( $start_date, $end_date );
    ?>

    <div id="poststuff" class="dokan-reports-wrap">
        <div class="dokan-analytics-sidebar report-left dokan-left">
            <ul class="chart-legend">
                <?php foreach ($analytics_total as $title => $count) {
                    printf( '<li><strong>%s</strong>%s</li>', round( $count, 2 ), dokan_vendor_analytics_get_report_title( $title ) );
                } ?>
            </ul>
        </div>

        <div class="dokan-reports-main report-right dokan-right">
            <div class="postbox">
                <h3><span><?php _e( 'Analytics', 'dokan' ); ?></span></h3>
                <?php  dokan_analytics_overview_chart_data( $start_date, $end_date, 'day', $vendor_analytics ); ?>
            </div>
        </div>
    </div>

    <?php
}

/**
 * Prepares chart data for sales overview
 *
 * @since 1.0
 *
 * @global type $wp_locale
 * @param type $start_date
 * @param type $end_date
 * @param type $group_by
 */
function dokan_analytics_overview_chart_data( $start_date, $end_date, $group_by, $analytics ) {
    global $wp_locale;

    $start_date_to_time = strtotime( $start_date );
    $end_date_to_time = strtotime( $end_date );

    if ( $group_by == 'day' ) {
        $group_by_query       = 'YEAR(post_date), MONTH(post_date), DAY(post_date)';
        $chart_interval       = ceil( max( 0, ( $end_date_to_time - $start_date_to_time ) / ( 60 * 60 * 24 ) ) );
        $barwidth             = 60 * 60 * 24 * 1000;
    } else {
        $group_by_query = 'YEAR(post_date), MONTH(post_date)';
        $chart_interval = 0;
        $min_date             = $start_date_to_time;
        while ( ( $min_date   = strtotime( "+1 MONTH", $min_date ) ) <= $end_date_to_time ) {
            $chart_interval ++;
        }
        $barwidth             = 60 * 60 * 24 * 7 * 4 * 1000;
    }

    // Prepare data for report
    $user_counts      = dokan_prepare_chart_data( $analytics, 'post_date', 'users', $chart_interval, $start_date_to_time, $group_by );
    $session_counts     = dokan_prepare_chart_data( $analytics, 'post_date', 'sessions', $chart_interval, $start_date_to_time, $group_by );

    // Encode in json format
    $chart_data = json_encode( array(
        'user_counts'      => array_values( $user_counts ),
        'session_counts'     => array_values( $session_counts )
    ) );

    $chart_colours = array(
        'user_counts'  => '#3498db',
        'session_counts'   => '#1abc9c'
    );

    ?>
    <div class="chart-container">
        <div class="chart-placeholder main" style="width: 100%; height: 350px;"></div>
    </div>

    <script type="text/javascript">
        jQuery(function($) {

            var analytics_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
            var isRtl = '<?php echo is_rtl() ? "1" : "0"; ?>'

            var series = [
                {
                    label: "<?php echo esc_js( __( 'Users', 'dokan' ) ) ?>",
                    data: analytics_data.user_counts,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 1, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 2, fill: false },
                    shadowSize: 0,
                    append_tooltip: " <?php echo __( 'users', 'dokan' ); ?>"
                },
                {
                    label: "<?php echo esc_js( __( 'Sessions', 'dokan' ) ) ?>",
                    data: analytics_data.session_counts,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 3, fill: false },
                    shadowSize: 0,
                    append_tooltip: " <?php echo __( 'sessions', 'dokan' ); ?>"
                },
            ];

            var main_chart = jQuery.plot(
                jQuery('.chart-placeholder.main'),
                series,
                {
                    legend: {
                        show: true,
                        position: 'nw'
                    },
                    series: {
                        lines: { show: true, lineWidth: 4, fill: false },
                        points: { show: true }
                    },
                    grid: {
                        borderColor: '#eee',
                        color: '#aaa',
                        borderWidth: 1,
                        hoverable: true,
                        show: true,
                        aboveData: false,
                    },
                    xaxis: {
                        color: '#aaa',
                        position: "bottom",
                        tickColor: 'transparent',
                        mode: "time",
                        timeformat: "<?php if ( $group_by == 'day' ) echo '%d %b'; else echo '%b'; ?>",
                        monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
                        tickLength: 1,
                        minTickSize: [1, "<?php echo $group_by; ?>"],
                        font: {
                            color: "#aaa"
                        },
                        transform: function (v) { return ( isRtl == '1' ) ? -v : v; },
                        inverseTransform: function (v) { return ( isRtl == '1' ) ? -v : v; }
                    },
                    yaxes: [
                        {
                            position: ( isRtl == '1' ) ? "right" : "left",
                            min: 0,
                            minTickSize: 1,
                            tickDecimals: 0,
                            color: '#d4d9dc',
                            font: { color: "#aaa" }
                        },
                        {
                            position: ( isRtl == '1' ) ? "right" : "left",
                            min: 0,
                            tickDecimals: 2,
                            alignTicksWithAxis: 1,
                            color: 'transparent',
                            font: { color: "#aaa" }
                        }
                    ],
                    colors: ["<?php echo $chart_colours['user_counts']; ?>", "<?php echo $chart_colours['session_counts']; ?>"]
                }
            );

            jQuery('.chart-placeholder').resize();
        });

    </script>
    <?php
}

/**
 * Returns page analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_page_analytics() {
    $metrics    = 'ga:pageviews,ga:avgTimeOnPage,ga:bounceRate';
    $dimensions = 'ga:PageTitle,ga:pagePath';
    $sort       = '-ga:pageviews';
    $headers = array(
        'PageTitle'     => __( 'Page Title', 'dokan' ),
        'pagePath'      => __( 'Page Path', 'dokan' ),
        'pageviews'     => __( 'Page Views', 'dokan' ),
        'avgTimeOnPage' => __( 'Avg Time', 'dokan' ),
        'bounceRate'    => __( 'Bounce Rate', 'dokan' )
    );
    $Vendor_filter = new Dokan_Vendor_Analytics_Reports();
    $Vendor_filter->get_analytics_content( $metrics, $dimensions, $sort, $headers );
}

/**
 * Returns activity analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_activity_analytics() {
    $metrics    = 'ga:entrances,ga:exits,ga:entranceRate,ga:exitRate';
    $dimensions = 'ga:pageTitle,ga:pagePath';
    $sort       = '-ga:entrances';
    $headers = array(
        'pageTitle'     => __( 'Page Title', 'dokan' ),
        'pagePath'      => __( 'Page Path', 'dokan' ),
        'entrances'     => __( 'Entrances', 'dokan' ),
        'exits'         => __( 'Exits', 'dokan' ),
        'entranceRate'  => __( 'Entrance Rate', 'dokan' ),
        'exitRate'      => __( 'Exit Rate', 'dokan' )
    );
    $Vendor_filter = new Dokan_Vendor_Analytics_Reports();
    $Vendor_filter->get_analytics_content( $metrics, $dimensions, $sort, $headers );
}

/**
 * Returns geo analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_geographic_analytics() {
    $metrics    = 'ga:users,ga:pageviews,ga:avgTimeOnPage,ga:bounceRate';
    $dimensions = 'ga:city,ga:country';
    $sort       = '-ga:users';
    $headers = array(
        'city'          => __( 'City', 'dokan' ),
        'country'       => __( 'Country', 'dokan' ),
        'users'         => __( 'Users', 'dokan' ),
        'pageviews'     => __( 'Page Views', 'dokan' ),
        'avgTimeOnPage' => __( 'Avg Time', 'dokan' ),
        'bounceRate'    => __( 'Bounce Rate', 'dokan' ),
    );

    $start_date = date( 'Y-m-01', current_time('timestamp') );
    $end_date = date( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) );
    if ( isset( $_POST['dokan_analytics_filter'] ) ) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
    }

    $reports = new Dokan_Vendor_Analytics_Reports();

    $results = $reports->dokan_get_vendor_analytics( $start_date, $end_date, $metrics, $dimensions, $sort, false, 20 );

    if ( is_wp_error( $results ) ) {
        echo esc_html( $results->get_error_message() );
        return;
    }

    if ( empty( $results->totalsForAllResults ) ) {
        echo __( 'There is no analytics found for your store.', 'dokan' );
        return;
    }

    dokan_analytics_date_form( $start_date, $end_date );

    $chart_data = array();

    if ( ! empty( $results->rows ) ) {
        foreach ( $results->rows as $row_data ) {
            $country = $row_data[1];
            $users = $row_data[2];

            if ( ! isset( $chart_data[ $country ] ) ) {
                $chart_data[ $country ] = 0;
            }

            $chart_data[ $country ] += absint( $users );
        }
    }

    $args = array(
        'is_vendor_analytics_views' => true,
        'results'                   => $results,
        'headers'                   => $headers,
        'rows'                      => ( isset( $results->rows ) && ! empty( $results->rows ) ) ? $results->rows : array(),
    );

    wp_enqueue_script( 'echarts-js', DOKAN_VENDOR_ANALYTICS_ASSETS . '/js/echarts.min.js', array(), false, true );
    wp_enqueue_script( 'echarts-js-map-world', DOKAN_VENDOR_ANALYTICS_ASSETS . '/js/world.js', array( 'echarts-js' ), false, true );
    wp_enqueue_script( 'dokan-vendor-analytics-locations', DOKAN_VENDOR_ANALYTICS_ASSETS . '/js/dokan-vendor-analytics-locations.js', array( 'echarts-js', 'echarts-js-map-world' ), false, true );
    wp_localize_script( 'dokan-vendor-analytics-locations', 'dokanVendorAnalytics', array(
        'chart_data' => $chart_data,
    ) );

    dokan_get_template_part( 'location-map', '', $args );
}

/**
 * Returns system analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_system_analytics() {
    $metrics       = 'ga:pageviews';
    $dimensions    = 'ga:browser,ga:operatingSystem,ga:operatingSystemVersion';
    $sort          = '-ga:pageviews';
    $headers       = array(
        'browser'                => __( 'Browser', 'dokan' ),
        'operatingSystem'        => __( 'Operating System', 'dokan' ),
        'operatingSystemVersion' => __( 'OS Version', 'dokan' ),
        'sessions'               => __( 'Sessions', 'dokan' )
    );
    $Vendor_filter = new Dokan_Vendor_Analytics_Reports();
    $Vendor_filter->get_analytics_content( $metrics, $dimensions, $sort, $headers );
}

/**
 * Returns promotion analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_promotion_analytics() {
    $metrics    = 'ga:sessions';
    $dimensions = 'ga:source,ga:medium,ga:socialNetwork';
    $sort       = '-ga:sessions';
    $headers = array(
        'source'        => __( 'Source', 'dokan' ),
        'medium'        => __( 'Medium', 'dokan' ),
        'socialNetwork' => __( 'Social Media', 'dokan' ),
        'sessions'      => __( 'Sessions', 'dokan' ),
    );
    $Vendor_filter = new Dokan_Vendor_Analytics_Reports();
    $Vendor_filter->get_analytics_content( $metrics, $dimensions, $sort, $headers );
}

/**
 * Returns keyword analytics
 *
 * @since 1.0
 * @return array
 */
function dokan_keyword_analytics() {
    $metrics    = 'ga:sessions';
    $dimensions = 'ga:keyword';
    $sort       = '-ga:sessions';
    $headers = array(
        'keyword'   => __( 'Keyword', 'dokan' ),
        'sessions'  => __( 'Sessions', 'dokan' ),
    );
    $Vendor_filter = new Dokan_Vendor_Analytics_Reports();
    $Vendor_filter->get_analytics_content( $metrics, $dimensions, $sort, $headers );
}

/**
 * Get dokan analytics app client_id
 *
 * @since 1.0.0
 *
 * @return string
 */
function dokan_vendor_analytics_client_id() {
    /**
     * Filter to change the client_id of the app
     *
     * @since 1.0.0
     *
     * @var string
     */
    return apply_filters( 'dokan_vendor_analytics_client_id', '805455242052-r6h35kdd24ojcslu2ct2eqmjc398pp8i.apps.googleusercontent.com' );
}

/**
 * Get dokan analytics app redirect url
 *
 * @since 1.0.0
 *
 * @return string
 */
function dokan_vendor_analytics_get_redirect_uri() {
    /**
     * Filter to change the redirect uri
     *
     * @since 1.0.0
     *
     * @var string
     */
    return apply_filters( 'dokan_vendor_analytics_redirect_uri', 'https://api.getdokan.com/vendor-analytics/redirect' );
}

/**
 * Get dokan analytics app refresh token url
 *
 * @since 1.0.0
 *
 * @return string
 */
function dokan_vendor_analytics_get_refresh_token_url() {
    /**
     * Filter to change the refresh token url
     *
     * @since 1.0.0
     *
     * @var string
     */
    return apply_filters( 'dokan_vendor_analytics_refresh_token_url', 'https://api.getdokan.com/vendor-analytics/refresh-token' );
}

/**
 * Get google auth url for dokan analytics app
 *
 * @since 1.0.0
 *
 * @return string
 */
function dokan_vendor_analytics_get_auth_url() {
    $url = 'https://accounts.google.com/o/oauth2/auth?';

    $state = site_url( '?wc-api=dokan_vendor_analytics' );

    $query = array(
        'next'            => $state,
        'scope'           => 'https://www.googleapis.com/auth/analytics.readonly',
        'response_type'   => 'code',
        'access_type'     => 'offline',
        'approval_prompt' => 'force',
    );

    $query['redirect_uri'] = dokan_vendor_analytics_get_redirect_uri();
    $query['client_id']    = dokan_vendor_analytics_client_id();
    $query['state']        = $state;

    return $url . http_build_query( $query );
}

/**
 * Get configured Dokan_Client
 *
 * @since 1.0.0
 *
 * @return Dokan_Client
 */
function dokan_vendor_analytics_client() {
    $client = new Dokan_Client();
    $client->setScopes( array( 'https://www.googleapis.com/auth/analytics.readonly' ) );
    $client->setAccessType( 'offline' );
    $client->setRedirectUri( dokan_vendor_analytics_get_redirect_uri() );
    $client->setClientId( dokan_vendor_analytics_client_id() );

    return $client;
}

/**
 * Get analytics token
 *
 * @since 1.0.0
 *
 * @return string
 */
function dokan_vendor_analytics_token() {
    $api_data = get_option( 'dokan_vendor_analytics_google_api_data', array() );
    $token    = ! empty( $api_data['token'] ) ? $api_data['token'] : '{}';

    return $token;
}

/**
 * Get a tokenized instance of Google_Client
 *
 * If token is expired, it'll refresh first.
 *
 * @since 3.0.5
 *
 * @return \Dokan_Client|\WP_Error
 */
function dokan_vendor_analytics_get_tokenized_client() {
    try {
        $client = dokan_vendor_analytics_client();
        $token  = dokan_vendor_analytics_token();

        if ( empty( json_decode( $token, true ) ) ) {
            throw new Exception( __( 'Token is empty', 'dokan' ) );
        }

        $client->setAccessToken( $token );

        if ( $client->isAccessTokenExpired() ) {
            $refresh_token = $client->getRefreshToken();

            $response = wp_remote_post( dokan_vendor_analytics_get_refresh_token_url(), array(
                'timeout' => 30,
                'body'    => array(
                    'refresh_token' => $refresh_token,
                )
            ) );

            if ( is_wp_error( $response ) ) {
                throw new Exception( $response->get_error_message() );
            }

            $token = wp_remote_retrieve_body( $response );

            if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
                throw new Exception( $token );
            }

            $client->setAccessToken( $token );

            $api_data          = get_option( 'dokan_vendor_analytics_google_api_data', array() );
            $api_data['token'] = $token;

            update_option( 'dokan_vendor_analytics_google_api_data', $api_data, false );
        }

        return $client;
    } catch ( Exception $e ) {
        return new WP_Error(
            'dokan_vendor_analytics_get_tokenized_client_error',
            $e->getMessage()
        );
    }
}

/**
 * Get analytics profiles from Google API
 *
 * @since 3.0.5
 *
 * @return array|\WP_Error
 */
function dokan_vendor_analytics_api_get_profiles() {
    try {
        $client = dokan_vendor_analytics_get_tokenized_client();

        if ( is_wp_error( $client ) ) {
            throw new Exception( $client->get_error_message() );
        }

        $service = new Dokan_Service_Analytics( $client );

        $profiles      = [];
        $profiles_map  = [];

        $profile_items = $service->management_accountSummaries->listManagementAccountSummaries()->getItems();

        if ( empty( $profile_items ) ) {
            return $profiles;
        }

        if ( ! empty( $profile_items ) ) {
            foreach ( $profile_items as $item ) {
                foreach ( $item['webProperties'] as $web_properties ) {
                    $group = array(
                        'group_label'  => $web_properties->getName(),
                        'group_values' => array(),
                    );

                    foreach ( $web_properties->getProfiles() as $profile ) {
                        $group['group_values'][] = array(
                            'label' => $profile->name . ' (' . $web_properties->id . ')',
                            'value' => 'ga:' . $profile->id,
                        );

                        $profiles_map[ 'ga:' . $profile->id ] = $web_properties->id;
                    }

                    $profiles[] = $group;
                }
            }
        }

        $api_data                 = get_option( 'dokan_vendor_analytics_google_api_data', array() );
        $api_data['profiles']     = $profiles;
        $api_data['profiles_map'] = $profiles_map;

        update_option( 'dokan_vendor_analytics_google_api_data', $api_data, false );

        return $profiles;
    } catch ( Exception $e ) {
        return new WP_Error(
            'dokan_vendor_analytics_get_tokenized_client_error',
            $e->getMessage()
        );
    }
}

/**
 * Get user readable title from title key.
 *
 * @since 3.3.7
 *
 * @param string $title_key
 *
 * @return string
 */
function dokan_vendor_analytics_get_report_title( $title_key ) {
    switch ( $title_key ) {
        case 'users':
            $title = __( 'Users', 'dokan' );
            break;
        case 'sessions':
            $title = __( 'Sessions', 'dokan' );
            break;
        case 'pageviews':
            $title = __( 'Page Views', 'dokan' );
            break;
        case 'bounceRate':
            $title = __( 'Bounce Rate', 'dokan' );
            break;
        case 'newUsers':
            $title = __( 'New Users', 'dokan' );
            break;
        case 'sessionDuration':
            $title = __( 'Session Duration', 'dokan' );
            break;
        default:
            $title = $title_key;
    }

    return $title;
}
