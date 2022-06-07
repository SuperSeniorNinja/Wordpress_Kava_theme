<?php

/**
* User subscription for Vendor Dashboard
*/
class Dokan_VSP_User_Subscription {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'dokan_query_var_filter', [ $this, 'load_subscription_query_var' ], 15, 1 );
        add_filter( 'dokan_get_dashboard_nav', [ $this, 'add_subscription_menu' ], 15, 1 );
        add_filter( 'dokan_load_custom_template', [ $this, 'load_subscription_content' ], 15, 1 );
        add_action( 'dokan_vps_subscriptions_related_orders_meta_box_rows', [ $this, 'render_related_order_content' ], 15, 1 );
        add_action( 'template_redirect', [ $this, 'handle_subscription_schedule' ], 99 );
        add_action( 'wp_ajax_dokan_vps_change_status', [ $this, 'change_subscription_status' ] );
    }

    /**
     * Load subscription query vars
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_subscription_query_var( $query_vars ) {
        $query_vars[] = 'user-subscription';

        return $query_vars;
    }

    /**
     * Add subscription menu
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_subscription_menu( $urls ) {
        $urls['user-subscription'] = [
            'title' => __( 'User Subscriptions', 'dokan' ),
            'icon'  => '<i class="fas fa-users"></i>',
            'url'   => dokan_get_navigation_url( 'user-subscription' ),
            'pos'   => 50,
            'permission' => 'dokan_view_order_menu'
        ];

        return $urls;
    }

    /**
     * Load subscription content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_subscription_content( $query_vars ) {
        if ( isset( $query_vars['user-subscription'] ) ) {
            $subscription_id = isset( $_GET['subscription_id'] ) ? intval( $_GET['subscription_id'] ) : 0;

            if ( $subscription_id ){
                $_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
                if ( $_nonce ) {
                    dokan_get_template_part( 'subscription/subscription-details', '', [ 'is_subscription_product' => true, 'subscription_id' => $subscription_id ] );
                } else {
                    echo __( 'You have no permission to view this information', 'dokan' );
                }
            } else{
                dokan_get_template_part( 'subscription/subscrptions', '', [ 'is_subscription_product' => true ] );
            }
            return;
        }
    }

    /**
     * Render related order content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render_related_order_content( $post ) {
        $subscriptions = array();
        $orders        = array();
        $is_subscription_screen = wcs_is_subscription( $post->ID );

        // On the subscription page, just show related orders
        if ( $is_subscription_screen ) {
            $this_subscription = wcs_get_subscription( $post->ID );
            $subscriptions[]   = $this_subscription;
        } elseif ( wcs_order_contains_subscription( $post->ID, array( 'parent', 'renewal' ) ) ) {
            $subscriptions = wcs_get_subscriptions_for_order( $post->ID, array( 'order_type' => array( 'parent', 'renewal' ) ) );
        }

        // First, display all the subscriptions
        foreach ( $subscriptions as $subscription ) {
            wcs_set_objects_property( $subscription, 'relationship', __( 'Subscription', 'woocommerce-subscriptions' ), 'set_prop_only' );
            $orders[] = $subscription;
        }

        //Resubscribed
        $initial_subscriptions = array();

        if ( $is_subscription_screen ) {

            $initial_subscriptions = wcs_get_subscriptions_for_resubscribe_order( $this_subscription );

            $resubscribe_order_ids = WCS_Related_Order_Store::instance()->get_related_order_ids( $this_subscription, 'resubscribe' );

            foreach ( $resubscribe_order_ids as $order_id ) {
                $order    = wc_get_order( $order_id );
                $relation = wcs_is_subscription( $order ) ? _x( 'Resubscribed Subscription', 'relation to order', 'woocommerce-subscriptions' ) : _x( 'Resubscribe Order', 'relation to order', 'woocommerce-subscriptions' );
                wcs_set_objects_property( $order, 'relationship', $relation, 'set_prop_only' );
                $orders[] = $order;
            }
        } else if ( wcs_order_contains_subscription( $post->ID, array( 'resubscribe' ) ) ) {
            $initial_subscriptions = wcs_get_subscriptions_for_order( $post->ID, array( 'order_type' => array( 'resubscribe' ) ) );
        }

        foreach ( $initial_subscriptions as $subscription ) {
            wcs_set_objects_property( $subscription, 'relationship', _x( 'Initial Subscription', 'relation to order', 'woocommerce-subscriptions' ), 'set_prop_only' );
            $orders[] = $subscription;
        }

        // Now, if we're on a single subscription or renewal order's page, display the parent orders
        if ( 1 == count( $subscriptions ) ) {
            foreach ( $subscriptions as $subscription ) {
                if ( $subscription->get_parent_id() ) {
                    $order = $subscription->get_parent();
                    wcs_set_objects_property( $order, 'relationship', _x( 'Parent Order', 'relation to order', 'woocommerce-subscriptions' ), 'set_prop_only' );
                    $orders[] = $order;
                }
            }
        }

        // Finally, display the renewal orders
        foreach ( $subscriptions as $subscription ) {

            foreach ( $subscription->get_related_orders( 'all', 'renewal' ) as $order ) {
                wcs_set_objects_property( $order, 'relationship', _x( 'Renewal Order', 'relation to order', 'woocommerce-subscriptions' ), 'set_prop_only' );
                $orders[] = $order;
            }
        }

        $orders = apply_filters( 'woocommerce_subscriptions_admin_related_orders_to_display', $orders, $subscriptions, $post );

        foreach ( $orders as $order ) {
            if ( wcs_get_objects_property( $order, 'id' ) == $post->ID ) {
                continue;
            }
            dokan_get_template_part( 'subscription/html-related-orders-row', '', [ 'is_subscription_product' => true, 'order' => $order ] );
        }
    }

    /**
     * Handle subscription schedule
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_subscription_schedule() {
        if ( ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'dokan_change_subscription_schedule' ) ) {

            $subscription_id = isset( $_POST['subscription_id'] ) ? intval( $_POST['subscription_id'] ) : 0;

            if ( isset( $_POST['_billing_interval'] ) ) {
                update_post_meta( $subscription_id, '_billing_interval', $_POST['_billing_interval'] );
            }

            if ( ! empty( $_POST['_billing_period'] ) ) {
                update_post_meta( $subscription_id, '_billing_period', $_POST['_billing_period'] );
            }

            $subscription = wcs_get_subscription( $subscription_id );

            $dates = array();

            foreach ( wcs_get_subscription_date_types() as $date_type => $date_label ) {
                $date_key = wcs_normalise_date_type_key( $date_type );

                if ( 'last_order_date_created' == $date_key ) {
                    continue;
                }

                $utc_timestamp_key = $date_type . '_timestamp_utc';

                // A subscription needs a created date, even if it wasn't set or is empty
                if ( 'date_created' === $date_key && empty( $_POST[ $utc_timestamp_key ] ) ) {
                    $datetime = current_time( 'timestamp', true );
                } elseif ( isset( $_POST[ $utc_timestamp_key ] ) ) {
                    $datetime = $_POST[ $utc_timestamp_key ];
                } else { // No date to set
                    continue;
                }
                $dates[ $date_key ] = date( 'Y-m-d H:i:s', $datetime );
            }

            try {
                $subscription->update_dates( $dates, 'gmt' );

                wp_cache_delete( $subscription_id, 'posts' );
            } catch ( Exception $e ) {
                wcs_add_admin_notice( $e->getMessage(), 'error' );
            }

            $subscription->save();
        }
    }

    /**
     * Change subscription status
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function change_subscription_status() {
        check_ajax_referer( 'dokan_vps_change_status' );

        if ( ! current_user_can( 'dokan_manage_order' ) ) {
            wp_send_json_error( __( 'You have no permission to manage this order', 'dokan' ) );
            return;
        }

        $subscription_id     = isset( $_POST['subscription_id'] ) ? intval( $_POST['subscription_id'] ) : '';
        $subscription_status = isset( $_POST['subscription_status'] ) ? sanitize_text_field( $_POST['subscription_status'] ) : '';

        $subscription = wcs_get_subscription( $subscription_id );
        $subscription->update_status( $subscription_status );

        $statuses     = wcs_get_subscription_statuses();
        $status_label = isset( $statuses[ $subscription_status ] ) ? $statuses[ $subscription_status ] : $subscription_status;
        $status_class = dokan_vps_get_subscription_status_class( $subscription_status );

        $html = '<label class="dokan-label dokan-label-' . esc_attr( $status_class ) . '">' . esc_attr( $status_label ) . '</label>';

        wp_send_json_success( $html );
    }
}
