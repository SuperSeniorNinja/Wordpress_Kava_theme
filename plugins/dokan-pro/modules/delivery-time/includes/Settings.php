<?php


namespace WeDevs\DokanPro\Modules\DeliveryTime;

/**
 * Class Settings
 *
 * @since 3.3.0
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime
 */
class Settings {

    /**
     * Settings constructor
     *
     * @since 3.3.0
     */
    public function __construct() {
        // Hooks
        add_filter( 'dokan_settings_sections', [ $this, 'load_settings_section' ], 21 );
        add_filter( 'dokan_settings_fields', [ $this, 'load_settings_fields' ], 21 );
        add_action( 'dokan_before_saving_settings', [ $this, 'validate_admin_delivery_settings' ], 20, 2 );
        add_action( 'dokan_after_saving_settings', [ $this, 'generate_admin_delivery_time_settings' ], 20, 2 );
    }

    /**
     * Load admin settings section
     *
     * @since 3.3.0
     *
     * @param array $section
     *
     * @return array
     */
    public function load_settings_section( $section ) {
        $section[] = array(
            'id'    => 'dokan_delivery_time',
            'title' => __( 'Delivery Time', 'dokan' ),
            'icon'  => 'dashicons-clock',
        );

        return $section;
    }

    /**
     * Load all settings fields
     *
     * @since 3.3.0
     *
     * @param array $fields
     *
     * @return array
     */
    public function load_settings_fields( $fields ) {
        $all_delivery_days = Helper::get_all_delivery_days();
        $all_time_slots    = Helper::get_all_delivery_time_slots();

        $fields['dokan_delivery_time'] = [
            'allow_vendor_override_settings' => [
                'name'    => 'allow_vendor_override_settings',
                'label'   => __( 'Allow vendor settings', 'dokan' ),
                'desc'    => __( 'Allow vendor to override settings', 'dokan' ),
                'type'    => 'checkbox',
                'default' => 'off',
                'tooltip' => __( 'Check this to allow vendors to override & customize the delivery settings. Otherwise, admin configured settings will be applied.', 'dokan' ),
            ],
            'delivery_date_label' => [
                'name'    => 'delivery_date_label',
                'label'   => __( 'Delivery date label', 'dokan' ),
                'desc'    => __( 'This label will show on checkout page', 'dokan' ),
                'default' => __( 'Delivery Date', 'dokan' ),
                'type'    => 'text',
            ],
            'preorder_date' => [
                'name'    => 'preorder_date',
                'label'   => __( 'Delivery blocked buffer', 'dokan' ),
                'desc'    => __( 'How many days the delivery date is blocked from current date? 0 for no block buffer', 'dokan' ),
                'default' => '0',
                'type'    => 'number',
                'min'     => '0',
            ],
            'delivery_box_info' => [
                'name'    => 'delivery_box_info',
                'label'   => __( 'Delivery box info', 'dokan' ),
                /* translators: %s: day */
                'desc'    => sprintf( __( 'This info will show on checkout page delivery time box. %s will be replaced by Delivery blocked buffer', 'dokan' ), '%DAY%' ),
                /* translators: %s: day */
                'default' => sprintf( __( 'This store needs %s day(s) to process your delivery request', 'dokan' ), '%DAY%' ),
                'type'    => 'text',
            ],
            'select_required' => [
                'name'    => 'selection_required',
                'label'   => __( 'Require Delivery Date and Time', 'dokan' ),
                'desc'    => __( 'Make choosing a delivery date and time mandatory for customers.', 'dokan' ),
                'default' => 'on',
                'type'    => 'checkbox',
            ],
            'delivery_day' => [
                'name'    => 'delivery_day',
                'label'   => __( 'Delivery day', 'dokan' ),
                'desc'    => __( 'Select days of the week you are open for delivery', 'dokan' ),
                'type'    => 'multicheck',
                'default' => $all_delivery_days,
                'options' => $all_delivery_days,
            ],
            'opening_time' => [
                'name'        => 'opening_time',
                'label'       => __( 'Opening time', 'dokan' ),
                'type'        => 'select',
                'placeholder' => __( 'Select opening time', 'dokan' ),
                'options'     => $all_time_slots,
                'desc'        => __( 'What time does your delivery start?', 'dokan' ),
            ],
            'closing_time' => [
                'name'        => 'closing_time',
                'label'       => __( 'Closing time', 'dokan' ),
                'type'        => 'select',
                'placeholder' => __( 'Select closing time', 'dokan' ),
                'options'     => $all_time_slots,
                'desc'        => __( 'What time does your delivery end?', 'dokan' ),
            ],
            'time_slot_minutes' => [
                'name'    => 'time_slot_minutes',
                'label'   => __( 'Time slot', 'dokan' ),
                'desc'    => __( 'Time slot in minutes. Please keep opening and closing time divisible by slot minutes. E.g ( 30, 60, 120 )', 'dokan' ),
                'default' => '0',
                'type'    => 'number',
                'step'    => '30',
                'max'     => '360',
            ],
            'order_per_slot' => [
                'name'    => 'order_per_slot',
                'label'   => __( 'Order per slot', 'dokan' ),
                'desc'    => __( 'How many orders you can process in a single slot? 0 for unlimited orders', 'dokan' ),
                'default' => '0',
                'type'    => 'number',
            ],
        ];

        return $fields;
    }


    /**
     * Validates admin delivery settings
     *
     * @since 3.3.0
     *
     * @param string $option_name
     * @param array $option_value
     *
     * @return void
     */
    public function validate_admin_delivery_settings( $option_name, $option_value ) {
        if ( 'dokan_delivery_time' !== $option_name ) {
            return;
        }

        foreach ( $option_value['delivery_day'] as $key => $day ) {
            if ( $key !== $day ) {
                unset( $option_value['delivery_day'][ $key ] );
            }
        }

        $errors = [];

        $delivery_date_label     = $option_value['delivery_date_label'];
        $delivery_blocked_buffer = $option_value['preorder_date'];
        $selected_delivery_days  = $option_value['delivery_day'];
        $delivery_box_info       = $option_value['delivery_box_info'];
        $time_slot_minutes       = $option_value['time_slot_minutes'];
        $opening_time            = $option_value['opening_time'];
        $closing_time            = $option_value['closing_time'];
        $order_per_slot          = $option_value['order_per_slot'];

        if ( empty( $delivery_date_label ) ) {
            $errors[] = [
                'name' => 'delivery_date_label',
                'error' => __( 'Delivery date label can not be empty', 'dokan' ),
            ];
        }

        if ( ! is_array( $selected_delivery_days ) || empty( $selected_delivery_days ) ) {
            $errors[] = [
                'name' => 'delivery_day',
                'error' => __( 'Delivery Day can not be empty! Please choose at least one delivery day', 'dokan' ),
            ];
        }

        if ( '' === $delivery_blocked_buffer || intval( $delivery_blocked_buffer ) < 0 ) {
            $errors[] = [
                'name' => 'preorder_date',
                'error' => __( 'Delivery blocked buffer can not be empty or less than 0', 'dokan' ),
            ];
        }

        if ( empty( $delivery_box_info ) ) {
            $errors[] = [
                'name' => 'delivery_box_info',
                'error' => __( 'Delivery box information can not be empty', 'dokan' ),
            ];
        }

        if ( empty( $opening_time ) || empty( $closing_time ) || ( strtotime( $opening_time ) > strtotime( $closing_time ) ) ) {
            $errors[] = [
                'name' => 'closing_time',
                'error' => __( 'Opening time must be greater than closing time', 'dokan' ),
            ];
        }

        if ( ! is_int( intval( $time_slot_minutes ) ) || intval( $time_slot_minutes ) < 10 || intval( $time_slot_minutes ) > 1440 ) {
            $errors[] = [
                'name' => 'time_slot_minutes',
                'error' => __( 'Time slot minutes can not be empty, less than 10 minutes or greater than 1440 minutes', 'dokan' ),
            ];
        }

        if ( '' === $order_per_slot || intval( $order_per_slot ) < 0 ) {
            $errors[] = [
                'name' => 'order_per_slot',
                'error' => __( 'Order per slot can not be empty or less than 0', 'dokan' ),
            ];
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error(
                [
                    'settings' => [
                        'name'  => $option_name,
                        'value' => $option_value,
                    ],
                    'message'  => __( 'Validation error', 'dokan' ),
                    'errors' => $errors,
                ],
                400
            );
        }
    }

    /**
     * Generates admin default delivery time settings for vendors
     *
     * @since 3.3.0
     *
     * @param string $option_name
     * @param array $option_value
     *
     * @return void
     */
    public function generate_admin_delivery_time_settings( $option_name, $option_value ) {
        if ( 'dokan_delivery_time' !== $option_name ) {
            return;
        }

        foreach ( $option_value['delivery_day'] as $key => $day ) {
            if ( $key !== $day ) {
                unset( $option_value['delivery_day'][ $key ] );
            }
        }

        $option_value['preorder_date']     = intval( $option_value['preorder_date'] );
        $option_value['order_per_slot']    = intval( $option_value['order_per_slot'] );
        $option_value['time_slot_minutes'] = intval( $option_value['time_slot_minutes'] );

        $selected_delivery_days         = $option_value['delivery_day'];
        $time_slot_minutes              = $option_value['time_slot_minutes'];
        $opening_time                   = $option_value['opening_time'];
        $closing_time                   = $option_value['closing_time'];
        $order_per_slot                 = $option_value['order_per_slot'];

        $time_slots = [];

        foreach ( $selected_delivery_days as $delivery_day ) {
            $option_value['default_time_slot_minutes'][ $delivery_day ] = $time_slot_minutes;
            $option_value['default_opening_time'][ $delivery_day ]      = $opening_time;
            $option_value['default_closing_time'][ $delivery_day ]      = $closing_time;
            $option_value['default_order_per_slot'][ $delivery_day ]    = $order_per_slot;

            // Generating time slots
            $time_slots[ $delivery_day ] = Helper::generate_delivery_time_slots( $time_slot_minutes, $opening_time, $closing_time );
        }

        if ( ! empty( $time_slots ) ) {
            update_option( '_dokan_delivery_slot_settings', $time_slots );
            update_option( $option_name, $option_value );
        }
    }
}
