<?php

use WeDevs\Dokan\Cache;

global $woocommerce;

$seller_id = dokan_get_current_user_id();
$paged     = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
$limit     = 10;
$offset    = ( $paged - 1 ) * $limit;

$args = [
    'post_type'      => 'wc_booking',
    'paged'          => $paged,
    'posts_per_page' => $limit,
    'offset'         => $offset,
    'meta_key'       => '_booking_seller_id',
    'meta_value'     => $seller_id,
];

//set filters
$booking_date        = isset( $_GET['month'] ) ? sanitize_key( $_GET['month'] ) : NULL;
$booking_product     = isset( $_GET['product_id'] ) ? sanitize_key( $_GET['product_id'] ) : NULL;
$status_class        = isset( $_GET['booking_status'] ) ? $args['post_status'] = sanitize_key( $_GET['booking_status'] ) : 'total';

$cache_group = "bookings_{$seller_id}";
$cache_key   = 'bookings_' . md5( wp_json_encode( $args ) );
$query       = Cache::get( $cache_key, $cache_group );

if ( false === $query ) {
    $query = new WP_Query( $args );
    Cache::set( $cache_key, $query, $cache_group );
}

$bookings = $query->posts;
$counts   = dokan_pro()->module->booking::get_booking_status_counts_by( $seller_id );

$bookings_url         = dokan_get_navigation_url( 'booking/my-bookings' );
$booking_products_url = dokan_get_navigation_url( 'booking/edit' );
$booking_details_url  = dokan_get_navigation_url( 'booking/booking-details' );
$resource_url         = dokan_get_navigation_url( 'booking/resources/edit' );
$orders_url           = dokan_get_navigation_url( 'orders' );
$products_url         = dokan_get_navigation_url( 'products' );

$orders_counts = $counts;
$order_date    = ( isset( $_GET['order_date'] ) ) ? $_GET['order_date'] : '';
$status_list   = array_filter( (array) $counts );

unset( $status_list['total'] );
?>
<header class="dokan-dashboard-header">
    <h1 class="entry-title"><?php esc_html_e( 'Manage Bookings', 'dokan' ); ?></h1>
</header><!-- .dokan-dashboard-header -->

<div class="dokan-orders-content dokan-orders-area">
    <article class="dokan-orders-area">
        <ul class="list-inline order-statuses-filter">
            <li<?php echo $status_class === 'total' ? ' class="active"' : ''; ?>>
            <a href="<?php echo $bookings_url; ?>">
                <?php
                // translators: Order count total number.
                printf( __( 'All (%d)', 'dokan' ), $orders_counts->total );
                ?>
                </span>
            </a>
        </li>
        <?php
        foreach ( $status_list as $booking_status => $status_count ) {
            ?>
            <li<?php echo $status_class === $booking_status ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( 'booking_status', $booking_status, $bookings_url ); ?>">
                <?php
                // translators: Booking Status label; Count associated with the status.
                echo sprintf( '%s (%d)', get_post_status_object( $booking_status )->label, $status_count );
                ?>
                </span>
            </a>
        </li>
            <?php
        }
        ?>
</ul>

<?php

if ( $bookings ) {
    ?>
    <table class="dokan-table dokan-table-striped dokan-bookings-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Booking Status', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'ID', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Booked Product', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Customer', 'dokan' ); ?></th>
                <th><?php esc_html_e( '# of persons', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Order', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Start Date', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'End Date', 'dokan' ); ?></th>
                <th width="17%"><?php esc_html_e( 'Action', 'dokan' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ( $bookings as $booking ) {
                $the_booking = get_wc_booking( $booking->ID );
                $product     = $the_booking->get_product();
                ?>
                <tr class="type-wc_booking ">
                    <td class="booking_status column-booking_status">
                        <?php $booking_status = $the_booking->get_status(); ?>
                        <span class="status-<?php echo $booking_status; ?> dokan-tooltips-help tips" data-original-title = '<?php echo sprintf( '%s', $booking_status ); ?>'>
                            <?php
                            //translators: Booking status.
                            echo sprintf( '%s', $booking_status );
                            ?>
                        </span>
                    </td>
                    <td class="booking_id column-booking_id">
                        <?php echo sprintf( '<a href="%s">%s %d </a>', add_query_arg( 'booking_id', $the_booking->ID, $booking_details_url ), __( 'Booking #', 'dokan' ), $the_booking->ID ); ?>
                    </td>
                    <td class="booked_product column-booked_product">
                        <?php
                        $resource = $the_booking->get_resource();

                        if ( $product ) {
                            echo '<a href="' . add_query_arg( 'product_id', $product->get_id(), $booking_products_url ) . '">' . $product->get_title() . '</a>';
                            if ( $resource ) {
                                echo ' (<a href="' . add_query_arg( 'id', $resource->get_id(), $resource_url ) . '">' . $resource->get_title() . '</a>)';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>

                    <td class="customer column-customer">
                        <?php
                        $customer = $the_booking->get_customer();

                        if ( $customer ) {
                            echo '<a href="mailto:' . $customer->email . '">' . $customer->name . '</a>';
                        } else {
                            echo '-';
                        }
                        ?>
                </td>
                <td class="num_of_persons column-num_of_persons">
                    <?php
                    if ( ! is_object( $product ) || ! $product->has_persons() ) {
                        esc_html_e( 'N/A', 'dokan' );
                    } else {
                        $persons       = get_post_meta( $the_booking->ID, '_booking_persons', true );
                        $total_persons = 0;
                        if ( ! empty( $persons ) && is_array( $persons ) ) {
                            foreach ( $persons as $person_count ) {
                                $total_persons = $total_persons + $person_count;
                            }
                        }

                        echo esc_html( $total_persons );
                    }
                    ?>

                </td>
                <td class="order column-order">
                    <?php
                    $order = $the_booking->get_order();
                    if ( $order ) {
                        echo '<a href="' . wp_nonce_url( add_query_arg( array( 'order_id' => $order->get_id() ), dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) . '">#' . $order->get_order_number() . '</a> - ' . esc_html( wc_bookings_get_status_label( $the_booking->get_status() ) );
                    } else {
                        echo '-';
                    }
                    ?>
                </td>

                <td class="start_date column-start_date">
                    <?php
                    echo $the_booking->get_start_date();
                    ?>
                </td>
                <td class="end_date column-end_date">
                    <?php
                    echo $the_booking->get_end_date();
                    ?>
                </td>
                <td class="booking_actions column-booking_actions" width="17%">
                    <?php
                    $actions = array();

                    $actions['view'] = array(
                        'url'    => add_query_arg( 'booking_id', $the_booking->ID, $booking_details_url ),
                        'name'   => __( 'View', 'dokan' ),
                        'action' => 'view',
                    );

                    if ( in_array( $the_booking->get_status(), array( 'pending-confirmation' ), true ) ) {
                        $actions['confirm'] = array(
                            'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=dokan-wc-booking-confirm&booking_id=' . $the_booking->ID ), 'wc-booking-confirm' ),
                            'name'   => __( 'Confirm', 'dokan' ),
                            'action' => 'confirm',
                        );
                    }

                    foreach ( $actions as $action ) {
                        printf( '<a class="dokan-btn dokan-btn-default dokan-btn-sm tips %s" href="%s" data-original-title="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
                    }
                    ?>
                </td>
            </tr>

            <?php } ?>
        </tbody>
    </table>

    <?php
    $booking_count = $counts->$status_class;
    $num_of_pages = ceil( $booking_count / $limit );

    $base_url  = dokan_get_navigation_url( 'booking/my-bookings' );
    if ( $num_of_pages > 1 ) {
        echo '<div class="pagination-wrap">';
        $page_links = paginate_links(
            array(
                'current'   => $paged,
                'total'     => $num_of_pages,
                'base'      => $base_url . '%_%',
                'format'    => '?pagenum=%#%',
                'add_args'  => false,
                'type'      => 'array',
            )
        );

        echo "<ul class='pagination'>\n\t<li>";
        echo join( "</li>\n\t<li>", $page_links );
        echo "</li>\n</ul>\n";
        echo '</div>';
    }
    ?>

    <?php } else { ?>

    <div class="dokan-error">
        <?php esc_html_e( 'No Bookings found', 'dokan' ); ?>
    </div>

    <?php } ?>
</article>
</div>
<script>
    (function($){
        $(document).ready(function(){
            $('.datepicker').datepicker({
                dateFormat: 'yy-m-d'
            });
        });
    })(jQuery);
</script>
