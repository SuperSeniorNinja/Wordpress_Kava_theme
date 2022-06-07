<?php

class Dokan_WC_Bookings_Calendar {

    private $bookings;

    /**
     * Output the calendar view
     */
    public function output() {
        if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ) {
            wp_enqueue_script( 'chosen' );
            wc_enqueue_js( '$( "select#calendar-bookings-filter" ).chosen();' );
        } else {
            wp_enqueue_script( 'wc-enhanced-select' );
        }

        // @codingStandardsIgnoreStart
        $product_filter = isset( $_REQUEST['filter_bookings'] ) ? absint( $_REQUEST['filter_bookings'] ) : '';
        $view           = isset( $_REQUEST['view'] ) && $_REQUEST['view'] == 'day' ? 'day' : 'month';
        // @codingStandardsIgnoreEnd

        if ( 'day' === (string) $view ) {
            // @codingStandardsIgnoreLine
            $day = isset( $_REQUEST['calendar_day'] ) ? wc_clean( $_REQUEST['calendar_day'] ) : date( 'Y-m-d' );

            if ( version_compare( WC_BOOKINGS_VERSION, '1.15.0', '>=' ) ) {
                $this->bookings = WC_Booking_Data_Store::get_bookings_in_date_range(
                    strtotime( 'midnight', strtotime( $day ) ),
                    strtotime( 'midnight +1 day -1 min', strtotime( $day ) ),
                    $product_filter,
                    false
                );
            } else {
                $this->bookings = WC_Bookings_Controller::get_bookings_in_date_range(
                    strtotime( 'midnight', strtotime( $day ) ),
                    strtotime( 'midnight +1 day -1 min', strtotime( $day ) ),
                    $product_filter,
                    false
                );
            }
        } else {
            // @codingStandardsIgnoreStart
            $month = isset( $_REQUEST['calendar_month'] ) ? absint( $_REQUEST['calendar_month'] ) : date( 'n' );
            $year  = isset( $_REQUEST['calendar_year'] ) ? absint( $_REQUEST['calendar_year'] ) : date( 'Y' );

            if ( $year < ( date( 'Y' ) - 10 ) || $year > 2100 ) {
                $year = date( 'Y' );
            }

            if ( $month > 12 ) {
                $month = 1;
                $year ++;
            }

            if ( $month < 1 ) {
                $month = 1;
                $year --;
            }

            $start_of_week = absint( get_option( 'start_of_week', 1 ) );
            $last_day      = date( 't', strtotime( "$year-$month-01" ) );
            $start_date_w  = absint( date( 'w', strtotime( "$year-$month-01" ) ) );
            $end_date_w    = absint( date( 'w', strtotime( "$year-$month-$last_day" ) ) );
            // @codingStandardsIgnoreEnd

            // Calc day offset
            $day_offset = $start_date_w - $start_of_week;
            $day_offset = $day_offset >= 0 ? $day_offset : 7 - abs( $day_offset );

            // Cald end day offset
            $end_day_offset = 7 - ( $last_day % 7 ) - $day_offset;
            $end_day_offset = $end_day_offset >= 0 && $end_day_offset < 7 ? $end_day_offset : 7 - abs( $end_day_offset );

            // We want to get the last minute of the day, so we will go forward one day to midnight and subtract a min
            // @codingStandardsIgnoreLine
            $end_day_offset = $end_day_offset + 1;

            $start_timestamp = strtotime( "-{$day_offset} day", strtotime( "$year-$month-01" ) );
            $end_timestamp   = strtotime( "+{$end_day_offset} day midnight -1 min", strtotime( "$year-$month-$last_day" ) );

            if ( version_compare( WC_BOOKINGS_VERSION, '1.15.0', '>=' ) ) {
                $this->bookings = WC_Booking_Data_Store::get_bookings_in_date_range(
                    $start_timestamp,
                    $end_timestamp,
                    $product_filter,
                    false
                );
            } else {
                $this->bookings = WC_Bookings_Controller::get_bookings_in_date_range(
                    $start_timestamp,
                    $end_timestamp,
                    $product_filter,
                    false
                );
            }
        }

        include DOKAN_WC_BOOKING_DIR . ( '/templates/booking/calendar/html-calendar-' . $view . '.php' );
    }

    /**
     * List bookings for a day
     *
     * @param  [type] $day
     * @param  [type] $month
     * @param  [type] $year
     * @return [type]
     */
    public function list_bookings( $day, $month, $year ) {
        $date_start = strtotime( "$year-$month-$day 00:00" );
        $date_end   = strtotime( "$year-$month-$day 23:59" );

        foreach ( $this->bookings as $booking ) {
            // @codingStandardsIgnoreLine
            if ( get_post_field( 'post_author', $booking->product_id ) != dokan_get_current_user_id() ) {
                continue;
            }

            if (
                ( $booking->start >= $date_start && $booking->start < $date_end ) ||
                ( $booking->start < $date_start && $booking->end > $date_end ) ||
                ( $booking->end > $date_start && $booking->end <= $date_end )
                ) {
                    $edit_url = wp_nonce_url( add_query_arg( array( 'order_id' => $booking->order_id ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' );
                echo '<li><a href="' . $edit_url . '">';
                    echo '<strong>#' . $booking->id . ' - ';
				if ( $booking->get_product() ) {
                    $product = $booking->get_product();
					echo $product->get_title();
				}
                    echo '</strong>';
                    echo '<ul>';

				if ( ( $booking->get_customer() ) ) {
                    $customer = $booking->get_customer();
                    if ( ! empty( $customer->name ) ) {
                        echo '<li>' . __( 'Booked by', 'dokan' ) . ' ' . $customer->name . '</li>';
                    }
				}

				echo '<li>';

				if ( $booking->is_all_day() ) {
					echo __( 'All Day', 'dokan' );
				} else {
					echo $booking->get_start_date( '', 'g:ia' ) . '&mdash;' . $booking->get_end_date( '', 'g:ia' );
				}

                        echo '</li>';

				if ( $booking->get_resource() ) {
                    $resource = $booking->get_resource();
					echo '<li>' . __( 'Resource #', 'dokan' ) . $resource->ID . ' - ' . $resource->post_title . '</li>';
				}
                    echo '</ul></a>';
                echo '</li>';
            }
        }
    }

    /**
     * List bookings on a day
     */
    public function list_bookings_for_day() {
        $bookings_by_time = array();
        $all_day_bookings = array();
        $unqiue_ids       = array();

        foreach ( $this->bookings as $booking ) {
            $seller = get_post_field( 'post_author', $booking->get_product_id() );

            if ( (int) $seller !== dokan_get_current_user_id() ) {
                continue;
            }

            $edit_url = wp_nonce_url( add_query_arg( array( 'order_id' => $booking->order_id ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' );

            if ( $booking->is_all_day() ) {
                $all_day_bookings[] = $booking;
            } else {
                $start_time = $booking->get_start_date( '', 'Gi' );

                if ( ! isset( $bookings_by_time[ $start_time ] ) ) {
                    $bookings_by_time[ $start_time ] = array();
                }

                $bookings_by_time[ $start_time ][] = $booking;
            }

            $unqiue_ids[] = $booking->product_id . $booking->resource_id;
        }

        ksort( $bookings_by_time );

        $unqiue_ids = array_flip( $unqiue_ids );
        $index      = 0;
        $colours    = array( '#3498db', '#34495e', '#1abc9c', '#2ecc71', '#f1c40f', '#e67e22', '#e74c3c', '#2980b9', '#8e44ad', '#2c3e50', '#16a085', '#27ae60', '#f39c12', '#d35400', '#c0392b' );

        foreach ( $unqiue_ids as $key => $value ) {
            if ( isset( $colours[ $index ] ) ) {
                $unqiue_ids[ $key ] = $colours[ $index ];
            } else {
                $unqiue_ids[ $key ] = $this->random_color();
            }

            $index++;
        }

        $column = 0;

        foreach ( $all_day_bookings as $booking ) {
            echo '<li data-tip="' . esc_attr( $this->get_tip( $booking ) ) . '" style="background: ' . $unqiue_ids[ $booking->product_id . $booking->resource_id ] . '; left:' . 100 * $column . 'px; top: 0; bottom: 0; display:list-item; ">
                    <a href="' . $edit_url . '">' . $this->get_tip( $booking ) . '</a>
                </li>';
            $column++;
        }

        $start_column = $column;
        $last_end     = 0;

        foreach ( $bookings_by_time as $bookings ) {
            foreach ( $bookings as $booking ) {
                $short_start_time = $this->get_short_time( $booking->get_start() );
                $short_end_time   = $this->get_short_time( $booking->get_end() );
                // @codingStandardsIgnoreLine
                $booking_time     = sprintf( __( '%1$s %2$s', 'dokan' ), $short_start_time, $short_end_time );

                $element = "<li class='dokan-booking-time dokan-booking-{$short_start_time}' data-booking-time='{$booking_time}'>";
                $element .= "<a href='{$edit_url}'>{$this->get_tip( $booking )}</a>";
                $element .= '</li>';

                echo $element;
            }
        }
    }

    /**
     * Get a random colour
     */
    public function random_color() {
        // @codingStandardsIgnoreLine
        return sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
    }

    /**
     * Get a tooltip in day view
     * @param  object $booking
     * @return string
     */
    public function get_tip( $booking ) {
        $return  = '';
        $return .= '#' . $booking->id . ' - ';

        if ( $booking->get_product() ) {
            $product = $booking->get_product();
            $return .= $product->get_title();
        }

        if ( ( $booking->get_customer() ) ) {
            $customer = $booking->get_customer();

            if ( ! empty( $customer->name ) ) {
                $return .= '<br/>' . __( 'Booked by', 'dokan' ) . ' ' . $customer->name;
            }
        }

        if ( $booking->get_resource() ) {
            $resource = $booking->get_resource();
            $return .= '<br/>' . __( 'Resource #', 'dokan' ) . $resource->ID . ' - ' . $resource->post_title;
        }

        if ( $booking->get_start() ) {
            $return .= '<br/>' . $booking->get_start_date( '', 'g:ia' ) . '&mdash;' . $booking->get_end_date( '', 'g:ia' );
        }

        return $return;
    }

    /**
     * Filters products for narrowing search
     */
    public function product_filters() {
        $filters = array();

        $products = get_posts(
            apply_filters(
                'get_booking_products_args', array(
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'author'         => dokan_get_current_user_id(),
					'posts_per_page' => -1,
                    // @codingStandardsIgnoreLine
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => 'booking',
						),
					),
					'suppress_filters' => true,
                )
            )
        );

        foreach ( $products as $product ) {
            $filters[ $product->ID ] = $product->post_title;

            $booking   = new WC_Product_Booking( $product );
            $resources = $booking->get_resources();

            foreach ( $resources as $resource ) {
                $filters[ $resource->ID ] = '&nbsp;&nbsp;&nbsp;' . $resource->post_title;
            }
        }

        return $filters;
    }

    /**
     * Filters resources for narrowing search
     */
    public function resources_filters() {
        $filters = array();

        $resources = get_posts(
            apply_filters(
                'get_booking_resources_args', array(
					'post_status'      => 'publish',
					'post_type'        => 'bookable_resource',
					'posts_per_page'   => -1,
					'orderby'          => 'menu_order',
					'order'            => 'asc',
					'suppress_filters' => true,
					'author'           => dokan_get_current_user_id(),
                )
            )
        );

        foreach ( $resources as $resource ) {
            $filters[ $resource->ID ] = $resource->post_title;
        }

        return $filters;
    }

    /**
     * Get formatted time from timestamp with shortened time format.
     * Shortened format removes minutes when time is on the hour and removes
     * space between time and AM/PM.
     *
     * @param int $timestamp Timestamp to format.
     * @return string
     *
     * @since 1.15.0
     */
    public function get_short_time( $timestamp ) {
        $time_format = 'g:i a';
        // Remove spaces so AM/PM will be directly next to time.
        $time_format = str_replace( ' ', '', $time_format );

        // @codingStandardsIgnoreStart
        // Hide minutes if on the hour.
        if ( '00' === date( 'i', $timestamp ) ) {
            // Remove minutes from time format.
            $time_format = str_replace( ':i', '', $time_format );
        }

        return date( $time_format, $timestamp );
        // @codingStandardsIgnoreEnd
    }
}
