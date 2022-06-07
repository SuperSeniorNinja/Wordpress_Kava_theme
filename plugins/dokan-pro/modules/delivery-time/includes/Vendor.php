<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime;

/**
 * Class Vendor
 * @package WeDevs\DokanPro\Modules\DeliveryTime
 */
class Vendor {

    /**
     * Delivery time vendor constructor
     *
     * @since 3.3.0
     */
    public function __construct() {
        add_filter( 'dokan_get_dashboard_settings_nav', [ $this, 'register_dashboard_menu' ] );
        add_filter( 'dokan_query_var_filter', [ $this, 'delivery_time_template_endpoint' ] );

        add_filter( 'dokan_get_dashboard_nav', [ $this, 'register_delivery_calender_menu' ], 20 );
        add_action( 'dokan_load_custom_template', [ $this, 'load_dashboard_content' ], 20 );

        add_action( 'dokan_render_settings_content', [ $this, 'load_settings_content' ], 12 );
        add_action( 'dokan_order_detail_after_order_general_details', [ $this, 'render_vendor_delivery_box' ], 10, 1 );

        add_action( 'template_redirect', [ $this, 'save_delivery_time_settings' ], 10 );
        add_action( 'template_redirect', [ $this, 'save_vendor_delivery_time_box' ], 10 );

        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ], 20 );

        add_action( 'wp_ajax_dokan_get_dashboard_calendar_event', [ $this, 'get_dashboard_calendar_event' ] );
    }

    /**
     * @param $urls
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function register_dashboard_menu( $urls ) {
        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return $urls;
        }

        $admin_default_settings = Helper::get_delivery_time_settings( $vendor_id );

        if ( empty( $admin_default_settings ) ) {
            return $urls;
        }

        $urls['delivery-time'] = array(
            'title'      => __( 'Delivery Time', 'dokan' ),
            'icon'       => '<i class="far fa-clock"></i>',
            'url'        => dokan_get_navigation_url( 'settings/delivery-time' ),
            'pos'        => 60,
        );

        return $urls;
    }

    /**
     * Registers calender menu for delivery time
     *
     * @since 3.3.0
     *
     * @param array $urls
     *
     * @return array
     */
    public function register_delivery_calender_menu( $urls ) {
        $urls['delivery-time-dashboard'] = array(
            'title'      => __( 'Delivery Time', 'dokan' ),
            'icon'       => '<i class="far fa-clock"></i>',
            'url'        => dokan_get_navigation_url( 'delivery-time-dashboard' ),
            'pos'        => 60,
        );

        return $urls;
    }

    /**
     * Loads scripts
     *
     * @since 3.3.0
     */
    public function load_scripts() {
        global $wp;

        if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['delivery-time-dashboard'] ) ) {
            wp_enqueue_script( 'dokan-delivery-time-fullcalender-script' );
            wp_enqueue_style( 'dokan-delivery-time-fullcalender-style' );

            wp_enqueue_script( 'dokan-delivery-time-vendor-script' );
            wp_enqueue_style( 'dokan-delivery-time-vendor-style' );
        }

        if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['orders'] ) ) {
            wp_enqueue_script( 'dokan-delivery-time-flatpickr-script' );
            wp_enqueue_style( 'dokan-delivery-time-flatpickr-style' );

            wp_enqueue_script( 'dokan-delivery-time-vendor-script' );
            wp_enqueue_style( 'dokan-delivery-time-vendor-style' );
        }

        if ( dokan_is_seller_dashboard() && ( isset( $wp->query_vars['settings'] ) && 'delivery-time' === (string) $wp->query_vars['settings'] ) ) {
            wp_enqueue_style( 'dokan-delivery-time-vendor-style' );
        }
    }

    /**
     * @since 3.3.0
     *
     * @param array $query_var
     *
     * @return array
     */
    public function delivery_time_template_endpoint( $query_var ) {
        $query_var[] = 'delivery-time';
        $query_var[] = 'delivery-time-dashboard';
        return $query_var;
    }

    /**
     * @since 3.3.0
     *
     * @param $query_vars
     *
     * @return void
     */
    public function load_settings_content( $query_vars ) {
        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        $admin_default_settings = Helper::get_delivery_time_settings( $vendor_id );

        if ( empty( $admin_default_settings ) || isset( $query_vars['settings'] ) && 'delivery-time' !== $query_vars['settings'] ) {
            return;
        }

        $all_delivery_days = Helper::get_all_delivery_days();
        $all_time_slots    = Helper::get_all_delivery_time_slots();

        $vendor_id = dokan_get_current_user_id();

        $vendor_delivery_time_settings = Helper::get_delivery_time_settings( $vendor_id );
        $vendor_can_override_settings  = dokan_get_option( 'allow_vendor_override_settings', 'dokan_delivery_time', 'off' );

        $this->handle_prompt_message_templates();

        dokan_get_template_part(
            'form', '', [
                'is_delivery_time'              => true,
                'all_delivery_days'             => $all_delivery_days,
                'all_delivery_time_slots'       => $all_time_slots,
                'vendor_delivery_time_settings' => $vendor_delivery_time_settings,
                'vendor_can_override_settings'  => $vendor_can_override_settings,
            ]
        );
    }

    /**
     * Loads delivery time dashboard content
     *
     * @since 3.3.0
     *
     * @param array $query_vars
     */
    public function load_dashboard_content( $query_vars ) {
        if ( empty( $query_vars ) || ! array_key_exists( 'delivery-time-dashboard', $query_vars ) ) {
            return;
        }

        dokan_get_template_part(
            'dashboard', '', [
                'is_delivery_time' => true,
            ]
        );
    }

    /**
     * Handle saving of vendor delivery time settings
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function save_delivery_time_settings() {
        if ( ! isset( $_POST['dokan_update_delivery_time_settings'] ) || ! isset( $_POST['dokan_delivery_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_delivery_settings_nonce'] ) ), 'dokan_delivery_time_form_action' ) ) {
            return;
        }

        $data = [];

        $data['allow_vendor_delivery_time_option'] = isset( $_POST['delivery_show_time_option'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_show_time_option'] ) ) : 'off';

        // Getting default settings for vendor
        $vendor_can_override_settings = dokan_get_option( 'allow_vendor_override_settings', 'dokan_delivery_time', 'off' );
        $vendor_id = (int) dokan_get_current_user_id();

        // If vendor is not allowed to override settings, return
        if ( empty( $vendor_can_override_settings ) || 'off' === $vendor_can_override_settings ) {
            $vendor_delivery_days = dokan_get_option( 'delivery_day', 'dokan_delivery_time', [] );

            if ( empty( $vendor_delivery_days ) ) {
                wp_safe_redirect( dokan_get_navigation_url( 'settings/delivery-time' ), 302 );
                exit;
            }

            // Use global $_POST directly for this hook
            do_action( 'dokan_delivery_time_disabled_override' );

            // Saving delivery meta for only vendor allowing time delivery setting
            $user_settings = [
                'allow_vendor_delivery_time_option' => $data['allow_vendor_delivery_time_option'],
            ];
            update_user_meta( $vendor_id, '_dokan_vendor_delivery_time_settings', $user_settings );

            wp_safe_redirect( add_query_arg( [ 'message' => 'success' ], dokan_get_navigation_url( 'settings/delivery-time' ) ), 302 );
            exit;
        }

        $data['preorder_date']                = isset( $_POST['preorder_date'] ) ? sanitize_text_field( wp_unslash( $_POST['preorder_date'] ) ) : 0;
        $data['delivery_prep_date']           = isset( $_POST['delivery_prep_date'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_prep_date'] ) ) : 0;
        $data['delivery_day']                 = ( isset( $_POST['delivery_day'] ) && is_array( $_POST['delivery_day'] ) ) ? wc_clean( wp_unslash( $_POST['delivery_day'] ) ) : [];
        $data['enable_delivery_notification']  = isset( $_POST['enable_delivery_notification'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_delivery_notification'] ) ) : 'off';

        if ( empty( $data['delivery_day'] ) ) {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'message'       => 'failed',
                        'error-message' => 'empty-delivery-day',
                    ], dokan_get_navigation_url( 'settings/delivery-time' )
                ), 302
            );
            exit;
        }

        foreach ( $data['delivery_day'] as $delivery_day ) {
            $data['opening_time'][ $delivery_day ]      = isset( $_POST['delivery_opening_time'][ $delivery_day ] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_opening_time'][ $delivery_day ] ) ) : '';
            $data['closing_time'][ $delivery_day ]      = isset( $_POST['delivery_closing_time'][ $delivery_day ] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_closing_time'][ $delivery_day ] ) ) : '';
            $data['time_slot_minutes'][ $delivery_day ] = isset( $_POST['delivery_time_slot'][ $delivery_day ] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_time_slot'][ $delivery_day ] ) ) : '';
            $data['order_per_slot'][ $delivery_day ]    = isset( $_POST['order_per_slot'][ $delivery_day ] ) ? sanitize_text_field( wp_unslash( $_POST['order_per_slot'][ $delivery_day ] ) ) : 0;
        }

        $time_slots = [];

        foreach ( $data['delivery_day'] as $delivery_day ) {
            if ( empty( $data['opening_time'][ $delivery_day ] ) || empty( $data['closing_time'][ $delivery_day ] ) || strtotime( $data['opening_time'][ $delivery_day ] ) > strtotime( $data['closing_time'][ $delivery_day ] ) ) {
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'message'       => 'failed',
                            'error-message' => 'time-mismatch',
                        ], dokan_get_navigation_url( 'settings/delivery-time' )
                    ), 302
                );
                exit;
            }

            if ( '' === $data['order_per_slot'][ $delivery_day ] || (int) $data['order_per_slot'][ $delivery_day ] < 0 ) {
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'message'       => 'failed',
                            'error-message' => 'order-per-slot',
                        ], dokan_get_navigation_url( 'settings/delivery-time' )
                    ), 302
                );
                exit;
            }

            if ( (int) $data['time_slot_minutes'][ $delivery_day ] < 10 || (int) $data['time_slot_minutes'][ $delivery_day ] > 1440 ) {
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'message'       => 'failed',
                            'error-message' => 'time-slot',
                        ], dokan_get_navigation_url( 'settings/delivery-time' )
                    ), 302
                );
                exit;
            }

            // Generating time slots
            $time_slots[ $delivery_day ] = Helper::generate_delivery_time_slots( $data['time_slot_minutes'][ $delivery_day ], $data['opening_time'][ $delivery_day ], $data['closing_time'][ $delivery_day ] );
        }

        // Saving delivery metas
        update_user_meta( $vendor_id, '_dokan_vendor_delivery_time_settings', $data );

        // Saving time slot meta
        update_user_meta( $vendor_id, '_dokan_vendor_delivery_time_slots', $time_slots );

        // Use global $_POST directly for this hook
        do_action( 'dokan_delivery_time_after_save_settings' );

        wp_safe_redirect( add_query_arg( [ 'message' => 'success' ], dokan_get_navigation_url( 'settings/delivery-time' ) ), 302 );
        exit;
    }

    /**
     * Handles prompt messages
     *
     * @since 3.3.0
     *
     * @return void
     */
    private function handle_prompt_message_templates() {
        if ( isset( $_GET['message'] ) && 'success' === $_GET['message'] ) { // phpcs:ignore
            dokan_get_template_part( 'global/dokan-message', '', [ 'message' => __( 'Delivery settings has been saved successfully!', 'dokan' ) ] );
        }

        if ( isset( $_GET['message'] ) && 'failed' === $_GET['message'] && isset( $_GET['error-message'] ) && 'time-mismatch' === $_GET['error-message'] ) { // phpcs:ignore
            dokan_get_template_part(
                'global/dokan-error', '', [
                    'message' => __( 'Please make sure the opening time is greater than the closing time!', 'dokan' ),
                    'deleted' => false,
                ]
            );
        }

        if ( isset( $_GET['message'] ) && 'failed' === $_GET['message'] && isset( $_GET['error-message'] ) && 'order-per-slot' === $_GET['error-message'] ) { // phpcs:ignore
            dokan_get_template_part(
                'global/dokan-error', '', [
                    'message' => __( 'Please make sure order per slot is not empty, less than 0', 'dokan' ),
                    'deleted' => false,
                ]
            );
        }

        if ( isset( $_GET['message'] ) && 'failed' === $_GET['message'] && isset( $_GET['error-message'] ) && 'time-slot' === $_GET['error-message'] ) { // phpcs:ignore
            dokan_get_template_part(
                'global/dokan-error', '', [
                    'message' => __( 'Please make sure time slot minutes is not empty, less than 10 minutes or greater than 1440 minutes', 'dokan' ),
                    'deleted' => false,
                ]
            );
        }

        if ( isset( $_GET['message'] ) && 'failed' === $_GET['message'] && isset( $_GET['error-message'] ) && 'empty-delivery-day' === $_GET['error-message'] ) { // phpcs:ignore
            dokan_get_template_part(
                'global/dokan-error', '', [
                    'message' => __( 'Please make sure at least one delivery day is selected!', 'dokan' ),
                    'deleted' => false,
                ]
            );
        }
    }

    /**
     * Renders vendor delivery time box
     *
     * @since 3.3.0
     *
     * @param \WC_Order $order
     *
     * @return void
     */
    public function render_vendor_delivery_box( $order ) {
        if ( ! $order ) {
            return;
        }

        $order_id = $order->get_id();

        $store_location = $order->get_meta( 'dokan_store_pickup_location' );

        if ( ! empty( $store_location ) ) {
            return;
        }

        $date = dokan_current_datetime();
        $date = $date->format( 'Y-m-d' );

        $vendor     = [];
        $_vendor_id = dokan_get_seller_id_by_order( $order_id );

        $vendor_delivery_options = Helper::get_delivery_time_settings( $_vendor_id );

        if ( ! isset( $vendor_delivery_options['allow_vendor_delivery_time_option'] ) || 'on' !== $vendor_delivery_options['allow_vendor_delivery_time_option'] ) {
            return;
        }

        $store_info = dokan_get_store_info( $_vendor_id );

        $current_date = dokan_current_datetime();
        $current_date = $current_date->modify( $date );
        $day          = strtolower( trim( $current_date->format( 'l' ) ) );

        $vendor_order_per_slot              = (int) isset( $vendor_delivery_options['order_per_slot'][ $day ] ) ? $vendor_delivery_options['order_per_slot'][ $day ] : -1;
        $vendor_preorder_blocked_date_count = (int) $vendor_delivery_options['preorder_date'] > 0 ? $vendor_delivery_options['preorder_date'] : 0;
        $vendor_delivery_slots              = Helper::get_available_delivery_slots_by_date( $_vendor_id, $vendor_order_per_slot, $date );

        $vendor['store_name']              = $store_info['store_name'];
        $vendor['delivery_time_slots']     = $vendor_delivery_slots;
        $vendor['vendor_delivery_options'] = $vendor_delivery_options;
        $vendor['vendor_vacation_days']    = ( dokan_pro()->module->is_active( 'seller_vacation' ) && isset( $store_info['seller_vacation_schedules'] ) ) ? $store_info['seller_vacation_schedules'] : [];

        $current_date                  = $current_date->modify( '+' . $vendor_preorder_blocked_date_count . ' day' );
        $vendor_preorder_block_date_to = strtolower( trim( $current_date->format( 'Y-m-d' ) ) );

        $vendor['vendor_preorder_blocked_dates'] = [];

        if ( $vendor_preorder_blocked_date_count > 0 ) {
            $vendor['vendor_preorder_blocked_dates'] = [
                [
                    'from' => $date,
                    'to'   => $vendor_preorder_block_date_to,
                ],
            ];
        }

        dokan_get_template_part(
            'vendor-delivery-time-box', '', [
                'is_delivery_time' => true,
                'order_id'         => $order_id,
                'vendor_id'        => $_vendor_id,
                'vendor_info'      => $vendor,
            ]
        );
    }

    /**
     * Saves vendor delivery time box args
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function save_vendor_delivery_time_box() {
        if ( ! isset( $_POST['dokan_update_delivery_time'] ) || ! isset( $_POST['dokan_vendor_delivery_time_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_vendor_delivery_time_box_nonce'] ) ), 'dokan_vendor_delivery_time_box_action' ) ) {
            return;
        }

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

        if ( 0 === $order_id ) {
            return;
        }

        $delivery_date                              = isset( $_POST['dokan_delivery_date'] ) ? wc_clean( wp_unslash( $_POST['dokan_delivery_date'] ) ) : '';
        $delivery_time_slot                         = isset( $_POST['dokan_delivery_time_slot'] ) ? wc_clean( wp_unslash( $_POST['dokan_delivery_time_slot'] ) ) : '';
        $vendor_selected_current_delivery_date_slot = isset( $_POST['vendor_selected_current_delivery_date_slot'] ) ? wc_clean( wp_unslash( $_POST['vendor_selected_current_delivery_date_slot'] ) ) : '-';

        $data = [
            'order_id'                                   => $order_id,
            'delivery_date'                              => $delivery_date,
            'delivery_time_slot'                         => $delivery_time_slot,
            'vendor_selected_current_delivery_date_slot' => $vendor_selected_current_delivery_date_slot,
        ];

        Helper::update_delivery_time_date_slot( $data );

        $url = add_query_arg(
            [
                'order_id' => $order_id,
                '_wpnonce' => wp_create_nonce( 'dokan_view_order' ),
            ], dokan_get_navigation_url( 'orders' )
        );
        wp_safe_redirect( $url );
        exit;
    }


    /**
     * Gets dashboard calendar events from AJAX request
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function get_dashboard_calendar_event() {
        if ( ! isset( $_POST['action'] ) || wc_clean( wp_unslash( $_POST['action'] ) ) !== 'dokan_get_dashboard_calendar_event' ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ), '403' );
        }

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_vendor_get_calendar_nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        $start_date = isset( $_POST['start_date'] ) ? wc_clean( wp_unslash( $_POST['start_date'] ) ) : '';
        $end_date   = isset( $_POST['end_date'] ) ? wc_clean( wp_unslash( $_POST['end_date'] ) ) : '';

        if ( ! strtotime( $start_date ) || ! strtotime( $end_date ) ) {
            wp_send_json_error( __( 'Invalid date for delivery time calendar', 'dokan' ) );
        }

        global $wpdb;

        $filter_type = isset( $_POST['type_filter'] ) ? wc_clean( wp_unslash( $_POST['type_filter'] ) ) : '';
        $filter_query = ( ! empty( $filter_type ) && in_array( $filter_type, [ 'delivery', 'store-pickup' ], true ) ) ? $wpdb->prepare( ' AND `delivery_type` = %s', $filter_type ) : '';

        $vendor_id = dokan_get_current_user_id();

        // @codingStandardsIgnoreStart
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dokan_delivery_time`
                WHERE `vendor_id` = %d
                AND DATE(`date`) BETWEEN %s AND %s
                {$filter_query}
                ORDER BY `date`;",
                $vendor_id,
                $start_date,
                $end_date
            )
        );
        // @codingStandardsIgnoreEnd

        $calendar_events = [];

        foreach ( $results as $result ) {
            $order_id  = absint( $result->order_id );
            $time_slot = preg_split( '/[\\n\-]+/', $result->slot );

            $type = $result->delivery_type;

            if ( empty( $type ) ) {
                return;
            }

            $url = add_query_arg(
                [
                    'order_id' => $order_id,
                    '_wpnonce' => wp_create_nonce( 'dokan_view_order' ),
                ], dokan_get_navigation_url( 'orders' )
            );

            $start_date = dokan_current_datetime();
            $start_date = $start_date->modify( $result->date . ' ' . $time_slot[0] );
            $end_date   = $start_date->modify( $time_slot[1] )->format( 'Y-m-d\TH:i' );
            $start_date = $start_date->format( 'Y-m-d\TH:i' );

            $title = '';

            if ( 'delivery' === $type ) {
                /* translators: %s: order ID */
                $title = sprintf( __( 'Delivery #%1$s', 'dokan' ), $result->order_id );
            } elseif ( 'store-pickup' === $type ) {
                /* translators: %s: order ID */
                $title = sprintf( __( 'Store Pickup #%1$s', 'dokan' ), $result->order_id );
            }

            $additional_info = Helper::get_delivery_event_additional_info( $order_id, $type, $result->date, $result->slot );

            $calendar_events[] = [
                'title' => $title,
                'start' => $start_date,
                'end'   => $end_date,
                'url'   => $url,
                'info'  => $additional_info,
            ];
        }

        wp_send_json_success( [ 'calendar_events' => $calendar_events ], 200 );
    }
}
