<?php

/**
 * Include module template
 *
 * @since 2.9.10
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_seller_vacation_get_template( $name, $args = array() ) {
    dokan_get_template( "$name.php", $args, DOKAN_SELLER_VACATION_VIEWS, trailingslashit( DOKAN_SELLER_VACATION_VIEWS ) );
}


/**
 * Get vacation schedules
 *
 * @since 2.9.10
 *
 * @param array $profile_info
 *
 * @return array
 */
function dokan_seller_vacation_get_vacation_schedules( $profile_info ) {
    $vacation_schedules = array();

    $go_vacation = isset( $profile_info['setting_go_vacation'] ) && dokan_validate_boolean( $profile_info['setting_go_vacation'] );
    $datewise_vacation = isset( $profile_info['settings_closing_style'] ) && 'datewise' === $profile_info['settings_closing_style'];

    if ( $go_vacation && $datewise_vacation ) {
        if ( isset( $profile_info['seller_vacation_schedules'] ) && is_array( $profile_info['seller_vacation_schedules'] ) ) {
            $vacation_schedules = $profile_info['seller_vacation_schedules'];
        } else if ( isset( $profile_info['settings_close_from'] ) && isset( $profile_info['settings_close_to'] ) ) {
            $vacation_schedules = array(
                array(
                    'from'    => $profile_info['settings_close_from'],
                    'to'      => $profile_info['settings_close_to'],
                    'message' => ! empty( $profile_info['setting_vacation_message'] ) ? $profile_info['setting_vacation_message'] : '',
                )
            );
        }
    }

    return $vacation_schedules;
}

/**
 * Trigger background process to change product status
 *
 * @since 2.9.10
 *
 * @param array $vendors
 * @param bool  $cancel_all_process
 *
 * @return void
 */
function dokan_seller_vacation_update_product_status( $vendors = array(), $cancel_all_process = true ) {
    global $dokan_pro_sv_update_seller_product_status;

    if ( $cancel_all_process ) {
        $dokan_pro_sv_update_seller_product_status->kill_process();
    }

    if ( empty( $vendors ) ) {
        $vendors = dokan()->vendor->all( [ 'fields' => 'ID' ] );
    }

    foreach ( $vendors as $vendor ) {
        if ( $vendor instanceof \WeDevs\Dokan\Vendor\Vendor ) {
            $vendor = $vendor->get_id();
        }
        if ( intval( $vendor ) ) {
            $dokan_pro_sv_update_seller_product_status->push_to_queue(
                [
                    'vendor_id' => intval( $vendor ),
                ]
            );
        }
    }
    $dokan_pro_sv_update_seller_product_status->save()->dispatch();
}

/**
 * Check if seller is on vacation
 *
 * @since 2.9.10
 *
 * @param int $vendor_id
 *
 * @return bool
 */
function dokan_seller_vacation_is_seller_on_vacation( $vendor_id ) {
    $vendor       = dokan()->vendor->get( $vendor_id );
    $profile_info = $vendor->get_shop_info();

    $setting_go_vacation = isset( $profile_info['setting_go_vacation'] ) ? dokan_validate_boolean( $profile_info['setting_go_vacation'] ) : false;

    if ( ! $setting_go_vacation ) {
        return false;
    }

    $settings_closing_style = ! empty( $profile_info['settings_closing_style'] ) ? $profile_info['settings_closing_style'] : 'instantly';

    if ( 'instantly' === $settings_closing_style ) {
        return true;
    }

    $schedules = dokan_seller_vacation_get_vacation_schedules( $vendor->get_shop_info() );

    if ( empty( $schedules ) ) {
        return false;
    }

    $current_time = date( 'Y-m-d', current_time( 'timestamp' ) );

    foreach ( $schedules as $schedule ) {
        $from = $schedule['from'];
        $to   = $schedule['to'];

        if ( $from <= $current_time && $current_time <= $to ) {
            return true;
        }
    }

    return false;
}
