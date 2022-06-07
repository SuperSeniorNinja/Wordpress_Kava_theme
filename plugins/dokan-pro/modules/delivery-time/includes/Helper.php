<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper as StorePickupHelper;

/**
 * Class DeliveryTimeHelper
 */
class Helper {

    /**
     * Gets all time slots for a day
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_all_delivery_time_slots() {
        $minutes = [];

        $date       = dokan_current_datetime();
        $start_date = $date->setTime( 0, 0, 0 );
        $end_date   = $date->setTime( 23, 59, 59 );

        $interval    = new DateInterval( 'PT30M' );
        $date_period = new DatePeriod( $start_date, $interval, $end_date );

        foreach ( $date_period as $date ) {
            $time             = $date->format( 'h:i A' );
            $minutes[ $time ] = $time;
        }

        return $minutes;
    }

    /**
     * Gets all delivery days
     *
     * @since 3.3.0
     *
     * @return array
     */
    public static function get_all_delivery_days() {
        $days = [
            'sunday'    => __( 'Sunday', 'dokan' ),
            'monday'    => __( 'Monday', 'dokan' ),
            'tuesday'   => __( 'Tuesday', 'dokan' ),
            'wednesday' => __( 'Wednesday', 'dokan' ),
            'thursday'  => __( 'Thursday', 'dokan' ),
            'friday'    => __( 'Friday', 'dokan' ),
            'saturday'  => __( 'Saturday', 'dokan' ),
        ];

        return $days;
    }

    /**
     * Generates time slot based on start, end time and defined slot duration
     *
     * @since 3.3.0
     *
     * @param int $duration
     * @param string $start
     * @param string $end
     *
     * @return array
     */
    public static function generate_delivery_time_slots( $duration, $start, $end ) {
        $time       = [];
        $date       = dokan_current_datetime();
        $start_date = $date->modify( $start );
        $end_date   = $date->modify( $end );
        $interval   = new DateInterval( 'PT' . intval( $duration ) . 'M' );

        while ( $start_date < $end_date ) {
            $start = $start_date->format( 'h:i a' );

            $start_date = $start_date->add( $interval );
            $end = $start_date->format( 'h:i a' );

            $time[ $start . ' - ' . $end ]['start'] = $start;
            $time[ $start . ' - ' . $end ]['end']   = $end;
        }

        return $time;
    }

    /**
     * Gets available delivery slots by date for a vendor
     *
     * @since 3.3.0
     *
     * @param int $vendor_id
     * @param int $vendor_order_per_slot
     * @param string $date
     *
     * @return array
     */
    public static function get_available_delivery_slots_by_date( $vendor_id, $vendor_order_per_slot, $date ) {
        global $wpdb;

        $delivery_slots = [];
        $blocked_slots  = [];

        if ( empty( $vendor_id ) || empty( $date ) || -1 === $vendor_order_per_slot ) {
            return $delivery_slots;
        }

        $default_delivery_slots_all = self::get_delivery_slot_settings( $vendor_id );

        if ( empty( $default_delivery_slots_all ) ) {
            return $delivery_slots;
        }

        $current_date           = dokan_current_datetime();
        $current_date           = $current_date->modify( $date );
        $day                    = strtolower( trim( $current_date->format( 'l' ) ) );
        $default_delivery_slots = isset( $default_delivery_slots_all[ $day ] ) ? $default_delivery_slots_all[ $day ] : [];

        if ( empty( $default_delivery_slots ) ) {
            return $delivery_slots;
        }

        // Vendor vacation support
        $vendor_infos = dokan()->vendor->get( $vendor_id )->get_shop_info();

        $vendor_vacation_active = isset( $vendor_infos['setting_go_vacation'] ) ? $vendor_infos['setting_go_vacation'] : '';
        $vendor_vacation_style  = isset( $vendor_infos['settings_closing_style'] ) ? $vendor_infos['settings_closing_style'] : '';

        if ( 'yes' === $vendor_vacation_active && 'instantly' === $vendor_vacation_style ) {
            return $delivery_slots;
        }

        $vendor_vacation_dates = ( isset( $vendor_infos['seller_vacation_schedules'] ) && is_array( $vendor_infos['seller_vacation_schedules'] ) ) ? $vendor_infos['seller_vacation_schedules'] : [];
        foreach ( $vendor_vacation_dates as $vacation_date ) {
            if ( ( $date >= $vacation_date['from'] ) && ( $date <= $vacation_date['to'] ) ) {
                return $delivery_slots;
            }
        }

        // When vendor order per slot is 0, no limitation on delivery time slots
        if ( 0 === (int) $vendor_order_per_slot ) {
            return $default_delivery_slots;
        }

        $blocked_slots_result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT slot, COUNT(*) AS count
                    FROM {$wpdb->prefix}dokan_delivery_time
                    where vendor_id = %d
                    AND date = %s
                    GROUP BY slot
                    HAVING count >= %d",
                $vendor_id,
                $date,
                absint( $vendor_order_per_slot )
            )
        );

        foreach ( $blocked_slots_result as $blocked_slot ) {
            $blocked_slots[ $blocked_slot->slot ] = $blocked_slot->slot;
        }

        $delivery_slots = array_diff_key( $default_delivery_slots, array_flip( $blocked_slots ) );

        return $delivery_slots;
    }

    /**
     * Saves dokan delivery time date slot for tracking the slot availability on a date
     *
     * @since 3.3.0
     *
     * @param array $data
     *
     * @return void
     */
    public static function save_delivery_time_date_slot( $data ) {
        $order                  = $data['order'];
        $vendor_id              = $data['vendor_id'];
        $delivery_date          = $data['delivery_date'];
        $delivery_time_slot     = $data['delivery_time_slot'];
        $selected_delivery_type = isset( $data['selected_delivery_type'] ) ? $data['selected_delivery_type'] : 'delivery';

        if ( empty( $delivery_date ) ) {
            dokan_log( sprintf( 'Failed to get delivery date for order id: %1$s', $order->get_id() ) );
            return;
        }

        if ( empty( $delivery_time_slot ) ) {
            dokan_log( sprintf( 'Failed to get delivery slot for order id: %1$s', $order->get_id() ) );
            return;
        }

        $args = [
            'order_id'      => $order->get_id(),
            'vendor_id'     => absint( $vendor_id ),
            'date'          => $delivery_date,
            'slot'          => $delivery_time_slot,
            'delivery_type' => $selected_delivery_type,
        ];

        global $wpdb;

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'dokan_delivery_time',
            $args,
            [
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
            ]
        );

        // Check if the order is inserted to DB
        if ( ! $inserted ) {
            dokan_log( sprintf( 'Failed to insert delivery time row for order id: %1$s', $order->get_id() ) );
            return;
        }

        $order->update_meta_data( 'dokan_delivery_time_slot', $delivery_time_slot );
        $order->update_meta_data( 'dokan_delivery_time_date', $delivery_date );

        do_action( 'dokan_delivery_time_before_meta_save', $order, $data );

        $order->save_meta_data();
    }

    /**
     * Gets delivery time settings for vendor
     *
     * @since 3.3.0
     *
     * @param int $vendor_id
     *
     * @return array
     */
    public static function get_delivery_time_settings( $vendor_id ) {
        $delivery_settings = [];

        if ( ! $vendor_id ) {
            return $delivery_settings;
        }

        // Getting override settings for vendor
        $vendor_can_override_settings = dokan_get_option( 'allow_vendor_override_settings', 'dokan_delivery_time', 'off' );

        // Getting vendor delivery settings
        $vendor_delivery_time_settings = get_user_meta( $vendor_id, '_dokan_vendor_delivery_time_settings', true );

        // Getting admin delivery settings
        $admin_delivery_time_settings = get_option( 'dokan_delivery_time', [] );

        if ( empty( $admin_delivery_time_settings['delivery_day'] ) ) {
            return $delivery_settings;
        }

        $delivery_settings['allow_vendor_delivery_time_option'] = isset( $vendor_delivery_time_settings['allow_vendor_delivery_time_option'] ) ? $vendor_delivery_time_settings['allow_vendor_delivery_time_option'] : 'off';
        $delivery_settings['preorder_date']                     = $admin_delivery_time_settings['preorder_date'];
        $delivery_settings['delivery_day']                      = $admin_delivery_time_settings['delivery_day'];
        $delivery_settings['time_slot_minutes']                 = $admin_delivery_time_settings['default_time_slot_minutes'];
        $delivery_settings['opening_time']                      = $admin_delivery_time_settings['default_opening_time'];
        $delivery_settings['closing_time']                      = $admin_delivery_time_settings['default_closing_time'];
        $delivery_settings['order_per_slot']                    = $admin_delivery_time_settings['default_order_per_slot'];

        if ( 'on' === $vendor_can_override_settings && isset( $vendor_delivery_time_settings['delivery_day'] ) ) {
            return $vendor_delivery_time_settings;
        }

        return $delivery_settings;
    }

    /**
     * Gets delivery slot settings for a vendor
     *
     * @since 3.3.0
     *
     * @param int $vendor_id
     *
     * @return array
     */
    public static function get_delivery_slot_settings( $vendor_id ) {
        $delivery_slot_settings = [];

        if ( ! $vendor_id ) {
            return $delivery_slot_settings;
        }

        // Getting override settings for vendor
        $vendor_can_override_settings = dokan_get_option( 'allow_vendor_override_settings', 'dokan_delivery_time', 'off' );

        // Getting admin slot settings
        $delivery_slot_settings = get_option( '_dokan_delivery_slot_settings', [] );

        // Getting admin buffer day setting
        $delivery_buffer_days = dokan_get_option( 'preorder_date', 'dokan_delivery_time', '0' );
        $time_slot_duration   = dokan_get_option( 'time_slot_minutes', 'dokan_delivery_time', '0' );

        // Get todays date data
        $now   = dokan_current_datetime();
        $today = strtolower( $now->format( 'l' ) );

        // Getting vendor slot settings
        $vendor_slot_settings = get_user_meta( $vendor_id, '_dokan_vendor_delivery_time_slots', true );
        if ( 'on' === $vendor_can_override_settings && ( is_array( $vendor_slot_settings ) && ! empty( $vendor_slot_settings ) ) ) {
            $delivery_slot_settings        = $vendor_slot_settings;
            $vendor_delivery_time_settings = get_user_meta( $vendor_id, '_dokan_vendor_delivery_time_settings', true );
            $delivery_buffer_days          = isset( $vendor_delivery_time_settings['preorder_date'] ) ? $vendor_delivery_time_settings['preorder_date'] : '0';
            $time_slot_duration            = isset( $vendor_delivery_time_settings['time_slot_minutes'][ $today ] ) ? $vendor_delivery_time_settings['time_slot_minutes'][ $today ] : '0';
        }

        // return if delivery time slo
        if ( 0 !== intval( $delivery_buffer_days ) || ! array_key_exists( $today, $delivery_slot_settings ) ) {
            return $delivery_slot_settings;
        }

        $current_time = $now->modify( "+ $time_slot_duration minutes" )->format( 'h:i a' );
        $delivery_slot_settings[ $today ] = array_filter(
            $delivery_slot_settings[ $today ], function( $data ) use ( $current_time ) {
                return strtotime( $data['start'] ) > strtotime( $current_time );
            }
        );

        return $delivery_slot_settings;
    }

    /**
     * Formats delivery date and time slot string
     *
     * @since 3.3.0
     *
     * @param string $date
     * @param string $slot
     *
     * @return string
     * @throws \Exception
     */
    public static function get_formatted_delivery_date_time_string( $date, $slot ) {
        $formatted_string = '-- @ --';
        if ( empty( $date ) || empty( $slot ) || ! strtotime( $date ) ) {
            return $formatted_string;
        }

        try {
            $current_date   = dokan_current_datetime();
            $current_date   = $current_date->modify( $date );
            $change_to_date = $current_date->format( wc_date_format() );

            $formatted_string = $change_to_date . ' @ ' . $slot;
        } catch ( Exception $e ) {
            /* translators: %1$s selected date */
            dokan_log( sprintf( __( 'Failed to parse date for: %1$s', 'dokan' ), $date ) );
        }

        return $formatted_string;
    }

    /**
     * Updates the delivery time date slot
     *
     * @since 3.3.0
     *
     * @param array $data
     *
     * @return void
     */
    public static function update_delivery_time_date_slot( $data ) {
        $delivery_date                              = isset( $data['delivery_date'] ) ? $data['delivery_date'] : '';
        $delivery_time_slot                         = isset( $data['delivery_time_slot'] ) ? $data['delivery_time_slot'] : '';
        $vendor_selected_current_delivery_date_slot = isset( $data['vendor_selected_current_delivery_date_slot'] ) ? $data['vendor_selected_current_delivery_date_slot'] : '';
        $order_id                                   = isset( $data['order_id'] ) ? $data['order_id'] : 0;

        $vendor_id = (int) dokan_get_seller_id_by_order( $order_id );

        if ( empty( $delivery_date ) || empty( $delivery_time_slot ) || ! strtotime( $delivery_date ) || 0 === $order_id || 0 === $vendor_id ) {
            return;
        }

        $order = wc_get_order( $order_id );

        global $wpdb;

        // Delete delivery time slot record for the order
        $wpdb->delete( $wpdb->prefix . 'dokan_delivery_time', [ 'order_id' => $order_id ] );

        // Save new slot to the database
        $data = [
            'order'              => $order,
            'vendor_id'          => $vendor_id,
            'delivery_date'      => $delivery_date,
            'delivery_time_slot' => $delivery_time_slot,
        ];

        self::save_delivery_time_date_slot( $data );

        $current_date   = dokan_current_datetime();
        $change_to_date = $current_date->modify( $delivery_date )->format( wc_date_format() );

        // Saving order note
        /* translators: %1$s vendor selected date slot, %2$s changed date %3$s changed slot. */
        $note = sprintf( __( 'Order delivery time changed from %1$s to %2$s @ %3$s', 'dokan' ), $vendor_selected_current_delivery_date_slot, $change_to_date, $delivery_time_slot );
        $order->add_order_note( $note, false, true );

        // Saving order meta
        $order->update_meta_data( 'dokan_delivery_time_date', $delivery_date );
        $order->update_meta_data( 'dokan_delivery_time_slot', $delivery_time_slot );

        $order->save_meta_data();
    }

    /**
     * Checks if delivery time is enabled for a specific vendor
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     *
     * @return bool
     */
    public static function is_delivery_time_enabled_for_vendor( $vendor_id ) {
        $vendor_settings = get_user_meta( $vendor_id, '_dokan_vendor_delivery_time_settings', true );

        if ( ! isset( $vendor_settings['allow_vendor_delivery_time_option'] ) || 'on' !== $vendor_settings['allow_vendor_delivery_time_option'] ) {
            return false;
        }

        return true;
    }

    /**
     * Gets delivery event additional infos
     *
     * @since 3.3.7
     *
     * @param int $order_id
     * @param string $type
     * @param string $date
     * @param string $slot
     *
     * @return array
     */
    public static function get_delivery_event_additional_info( $order_id, $type, $date, $slot ) {
        $additional_info = [];

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return $additional_info;
        }

        $body = sprintf( '<span>%s</span>', self::get_formatted_delivery_date_time_string( $date, $slot ) );

        if ( 'store-pickup' === $type ) {
            $location       = $order->get_meta( 'dokan_store_pickup_location' );
            $formatted_info = StorePickupHelper::get_formatted_date_store_location_string( $date, $location, $slot );
            $body           = sprintf( '<span>%s</span>', $formatted_info );
        }

        $additional_info['body'] = $body;

        return $additional_info;
    }
}
