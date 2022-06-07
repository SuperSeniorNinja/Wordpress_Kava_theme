<?php

use WeDevs\Dokan\Exceptions\DokanException;

class Dokan_Seller_Vacation_Ajax {

    /**
     * Class constructor
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_dokan_seller_vacation_save_item', array( $this, 'ajax_vacation_save_item' ) );
        add_action( 'wp_ajax_dokan_seller_vacation_delete_item', array( $this, 'ajax_vacation_delete_item' ) );
    }

    /**
     * Save vacation item via AJAX request
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function ajax_vacation_save_item() {
        check_ajax_referer( 'dokan_reviews' );

        try {
            $post_data = wp_unslash( $_POST );

            if ( empty( $post_data['data']['item'] ) ) {
                throw new DokanException( 'parameter_is_missing', __( 'Item parameter is missing', 'dokan' ), 400 );
            }

            $vendor_id = get_current_user_id();
            $vendor    = dokan()->vendor->get( $vendor_id );
            $index     = isset( $post_data['data']['index'] ) ? absint( $post_data['data']['index'] ) : null;
            $item      = $post_data['data']['item'];

            if ( empty( $item['from'] ) || empty( $item['to'] ) || empty( $item['message'] ) ) {
                throw new DokanException( 'invalid_parameter', __( 'Invalid item parameter', 'dokan' ), 400 );
            }

            $from         = date( 'Y-m-d', strtotime( $item['from'] ) );
            $to           = date( 'Y-m-d', strtotime( $item['to'] ) );
            $current_time = date( 'Y-m-d', current_time( 'timestamp' ) );

            if ( ! ( $current_time <= $from && $current_time <= $to ) ) {
                throw new DokanException( 'invalid_date_range', __( 'Invalid date range', 'dokan' ), 400 );
            }

            $message = sanitize_text_field( $item['message'] );

            $vendor_settings = $vendor->get_shop_info();
            $schedules       = dokan_seller_vacation_get_vacation_schedules( $vendor_settings );
            $total_schedules = count( $schedules );
            $item            = array(
                'from'    => $from,
                'to'      => $to,
                'message' => $message,
            );

            if ( ! isset( $index ) ) {
                $schedules[] = $item;
            } else if ( isset( $schedules[ $index ] ) ) {
                $schedules[ $index ] = $item;
            }

            usort( $schedules, array( $this, 'sort_by_date_asc' ) );

            $vendor_settings['setting_go_vacation']       = 'yes';
            $vendor_settings['settings_closing_style']    = 'datewise';
            $vendor_settings['seller_vacation_schedules'] = $schedules;

            update_user_meta( $vendor_id, 'dokan_profile_settings', $vendor_settings );

            dokan_seller_vacation_update_product_status( array( $vendor ), false );

            wp_send_json_success( array(
                'schedules'  => $schedules,
            ) );
        } catch ( Exception $e ) {
            $this->send_json_error( $e, __( 'Unable to save vacation data', 'dokan' ) );
        }
    }

    /**
     * Delete vacation item via AJAX request
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function ajax_vacation_delete_item() {
        check_ajax_referer( 'dokan_reviews' );

        try {
            $post_data = wp_unslash( $_POST );

            if ( ! isset( $post_data['index'] ) ) {
                throw new DokanException( 'missing_item_index', __( 'Item index is missing', 'dokan' ) );
            }

            $index = absint( $post_data['index'] );

            $vendor_id       = get_current_user_id();
            $vendor          = dokan()->vendor->get( $vendor_id );
            $vendor_settings = $vendor->get_shop_info();
            $schedules       = dokan_seller_vacation_get_vacation_schedules( $vendor_settings );

            if ( ! isset( $schedules[ $index ] ) ) {
                throw new DokanException( 'invalid_item_index', __( 'Invalid item index', 'dokan' ) );
            }

            unset( $schedules[ $index ] );

            usort( $schedules, array( $this, 'sort_by_date_asc' ) );

            $vendor_settings['setting_go_vacation']       = 'yes';
            $vendor_settings['settings_closing_style']    = 'datewise';
            $vendor_settings['seller_vacation_schedules'] = $schedules;

            update_user_meta( $vendor_id, 'dokan_profile_settings', $vendor_settings );

            dokan_seller_vacation_update_product_status( array( $vendor ), false );

            wp_send_json_success( array(
                'schedules'  => $schedules,
            ) );
        } catch ( Exception $e ) {
            $this->send_json_error( $e, __( 'Unable to delete vacation item', 'dokan' ) );
        }
    }

    /**
     * Sort vacation list by date `from` ascending order
     *
     * @since 2.9.10
     *
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    public function sort_by_date_asc( $a, $b ) {
        return strtotime( $a['from'] ) - strtotime( $b['from'] );
    }

    /**
     * Send JSON error on AJAX response
     *
     * @since 2.9.10
     *
     * @param \Exception $e
     * @param string $default_message
     *
     * @return void
     */
    protected function send_json_error( Exception $e, $default_message ) {
        if ( $e instanceof DokanException ) {
            wp_send_json_error(
                new WP_Error( $e->get_error_code(), $e->get_message() ),
                $e->get_status_code()
            );
        }

        wp_send_json_error(
            new WP_Error( 'something_went_wrong', $default_message ),
            422
        );
    }
}
