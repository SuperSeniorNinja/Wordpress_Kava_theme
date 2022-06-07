<?php
/**
*  Dokan Dashboard User Subscriptions Template
*
*  Load User Subscriptions related template
*
*  @since 2.4
*
*  @package dokan
*/
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">
    <?php

    /**
     * Hooks
     * dokan_dashboard_content_before hook hook
     * dokan_dashboard_user_subscription_content_before hook
     *
     *  @hooked get_dashboard_side_navigation
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_before' );
    do_action( 'dokan_dashboard_user_subscription_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-user-subscription-content">

    <?php

    /**
     * Hook dokan_user_subscription_content_inside_before
     *
     * @since 1.0
     */
    do_action( 'dokan_user_subscription_content_inside_before' );
    ?>

    <article class="dashboard-user-subscription-area">

        <header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                    <?php esc_html_e( 'User Subscriptions', 'dokan' ); ?>
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header><!-- .entry-header -->

        <?php
        global $woocommerce;

        $seller_id     = dokan_get_current_user_id();
        $customer_id   = isset( $_GET['customer_id'] ) ? sanitize_key( $_GET['customer_id'] ) : null;
        $order_status  = isset( $_GET['order_status'] ) ? sanitize_key( $_GET['order_status'] ) : 'all';
        $pagenum       = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $limit         = 10;
        $offset        = ( $pagenum - 1 ) * $limit;
        $secured       = isset( $_GET['security'] ) && wp_verify_nonce( sanitize_key( $_GET['security'] ), 'dokan-user-subscription-date' );
        $order_date    = isset( $_GET['order_date'] ) && $secured ? sanitize_key( $_GET['order_date'] ) : null;
        $relationship  = 'Parent';
        $subscriptions = dokan_vps_get_seller_subscriptions(
            array(
                'seller_id'   => $seller_id,
                'status'      => $order_status,
                'limit'       => $limit,
                'offset'      => $offset,
                'order_date'  => $order_date,
                'customer_id' => $customer_id,
                'return'      => 'subscriptions',
            )
        );

        $user_string   = '';
        $user_id       = '';

        if ( ! empty( $_GET['customer_id'] ) ) { // WPCS: input var ok.
            $user_id = absint( $_GET['customer_id'] ); // WPCS: input var ok, sanitization ok.
            $user    = get_user_by( 'id', $user_id );

            $user_string = sprintf(
                /* translators: 1: user display name 2: user ID 3: user email */
                esc_html__( '%1$s (#%2$s)', 'dokan' ),
                $user->display_name,
                absint( $user->ID )
            );
        }

        $filter_date = isset( $_GET['order_date'] ) && $secured && ! empty( $_GET['order_date'] ) ? sanitize_key( $_GET['order_date'] ) : '';
        ?>
        <form action="" method="GET" class="dokan-left">
            <div class="dokan-form-group">
                <input type="text" class="datepicker" style="width:120px; padding-bottom:7px" name="order_date" id="order_date_filter" placeholder="<?php esc_attr_e( 'Filter by Date', 'dokan' ); ?>" value="<?php echo esc_attr( $filter_date ); ?>">
                <input type="submit" name="dokan_order_filter" class="dokan-btn dokan-btn-sm dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Filter', 'dokan' ); ?>">
                <?php wp_nonce_field( 'dokan-user-subscription-date', 'security', false ); ?>
            </div>
        </form>
        <div class="dokan-clearfix"></div>

        <?php if ( $subscriptions ) : ?>
            <table class="dokan-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Status', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Subscription', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Item', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Start', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Next Payment', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'End', 'dokan' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $subscriptions as $skey => $subscription ) : ?>
                        <tr>
                            <td><label class="dokan-label dokan-label-<?php echo esc_attr( dokan_vps_get_subscription_status_class( $subscription->get_status() ) ); ?>"><?php echo esc_html( dokan_vps_get_subscription_status_translated( $subscription->get_status() ) ); ?></label></td>
                            <td>
                                <?php if ( current_user_can( 'dokan_view_order' ) ) : ?>
                                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'subscription_id' => $subscription->get_id() ), dokan_get_navigation_url( 'user-subscription' ) ) ) ); ?>">
                                        <strong>
                                            <?php
                                            // translators: %s Subscription Order number.
                                            echo sprintf( __( 'Subscription #%s', 'dokan' ), esc_attr( $subscription->get_order_number() ) );
                                            ?>
                                        </strong>
                                    </a>
                                <?php else : ?>
                                    <strong>
                                        <?php
                                        // translators: %s Subscription Order number.
                                        echo sprintf( __( 'Subscription #%s', 'dokan' ), esc_attr( $subscription->get_order_number() ) );
                                        ?>
                                    </strong>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php
                                $item_names = array();
                                foreach ( $subscription->get_items() as $item ) {
                                    $item_names[] = '<a href="' . dokan_edit_product_url( $item->get_id() ) . '">' . $item->get_name() . '</a> x ' . $item->get_quantity();
                                }
                                echo implode( ', ', $item_names );
                                ?>
                            </td>
                            <td>
                                <?php
                                $price_content = $subscription->get_formatted_order_total();
                                $price_content .= '<small class="meta" style="display:inline-block">';
                                // translators: placeholder is the display name of a payment gateway a subscription was paid by
                                $price_content .= esc_html( sprintf( __( 'Via %s', 'dokan' ), $subscription->get_payment_method_to_display() ) );

                                if ( WC_Subscriptions::is_duplicate_site() && $subscription->has_payment_gateway() && ! $subscription->get_requires_manual_renewal() ) {
                                    // translators: placeholder is the Payment method name that is in live mode.
                                    $staging_message = sprintf( __( 'Subscription locked to Manual Renewal while the store is in staging mode. Live payment method: %s', 'dokan' ), $subscription->get_payment_method_title() );
                                    $price_content .= sprintf( '<span class="tips" data-toggle="tooltip" data-placement="top"> %s </span>', esc_html( $staging_message ) );
                                }

                                $price_content .= '</small>';

                                echo $price_content;
                                ?>
                            </td>
                            <td>
                                <?php echo dokan_vps_get_date_content( $subscription, 'start_date' ); ?>
                            </td>
                            <td>
                                <?php echo dokan_vps_get_date_content( $subscription, 'next_payment_date' ); ?>
                            </td>
                            <td>
                                <?php echo dokan_vps_get_date_content( $subscription, 'end_date' ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="dokan-error">
                <?php esc_html_e( 'No subscription found', 'dokan' ); ?>
            </div>
        <?php endif; ?>


        <?php
        $subscription_count = dokan_vps_get_seller_subscriptions(
            array(
                'seller_id'   => $seller_id,
                'status'      => $order_status,
                'order_date'  => $order_date,
                'customer_id' => $customer_id,
                'return'      => 'count',
            )
        );
        $num_of_pages = ( $subscription_count ? ceil( $subscription_count / $limit ) : 0 );
        $base_url     = dokan_get_navigation_url( 'user-subscription' );

        if ( $num_of_pages > 1 ) {
            echo '<div class="pagination-wrap">';
            $page_links = paginate_links(
                array(
                    'current'   => $pagenum,
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

    </article>

    <?php

    /**
     * Hook dokan_user_subscription_content_inside_after
     *
     *  @since 1.0
     */
    do_action( 'dokan_user_subscription_content_inside_after' );
    ?>

    </div><!-- .dokan-dashboard-content -->


    <?php
    /**
     * Hook dokan_dashboard_content_after
     *  dokan_dashboard_user_subscription_content_after hook
     *
     *  @since 1.0
     */
    do_action( 'dokan_dashboard_content_after' );
    do_action( 'dokan_dashboard_user_subscription_content_after' );
    ?>
</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>
