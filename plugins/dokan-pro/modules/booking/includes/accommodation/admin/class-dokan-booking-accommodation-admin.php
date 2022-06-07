<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dokan booking accommodation admin manager
 *
 * @since 3.4.2
 */
class Dokan_Booking_Accommodation_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_accommodation_fields' ] );

        add_filter( 'product_type_options', [ $this, 'add_booking_accommodation_option' ], 20, 1 );

        add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 30 );

        add_action( 'admin_print_scripts-post-new.php', [ $this, 'enqueue_scripts' ], 30 );
        add_action( 'admin_print_scripts-post.php', [ $this, 'enqueue_scripts' ], 30 );

        add_action( 'woocommerce_process_product_meta', [ $this, 'handle_accommodation_booking_meta_save' ] );
    }

    /**
     * Register scripts
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function register_scripts() {
        // Accommodation Booking
        $accommodation_i18n = \Dokan_Booking_Accommodation_Helper::get_accommodation_booking_i18n_strings();

        wp_register_script( 'dokan_accommodation_booking_admin_script', DOKAN_WC_BOOKING_PLUGIN_ASSET . '/js/admin.js', [ 'jquery' ], DOKAN_PRO_PLUGIN_VERSION, true );

        wp_register_script( 'dokan_accommodation_booking_script', DOKAN_WC_BOOKING_PLUGIN_ASSET . '/js/accommodation.js', [ 'jquery', 'dokan-util-helper' ], DOKAN_PRO_PLUGIN_VERSION, true );
        wp_localize_script( 'dokan_accommodation_booking_script', 'dokan_accommodation_i18n', $accommodation_i18n );
    }

    /**
     * Enqueue scripts
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function enqueue_scripts() {
        // Enqueue scripts
        wp_enqueue_script( 'dokan_accommodation_booking_script' );
        wp_enqueue_script( 'dokan_accommodation_booking_admin_script' );

        // Timepicker
        wp_enqueue_style( 'dokan-timepicker' );
        wp_enqueue_script( 'dokan-timepicker' );
    }

    /**
     * Adds accommodation fields to WooCommerce product data - general tab
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function add_accommodation_fields() {
        dokan_get_template_part(
            'booking/accommodation/admin/accommodation-fields', '', [
                'is_booking' => true,
            ]
        );
    }

    /**
     * Adds accommodation type option
     *
     * @since 3.4.2
     *
     * @param  array $options
     *
     * @return array
     */
    public function add_booking_accommodation_option( $options ) {
        return array_merge(
            $options, [
                'dokan_is_accommodation_booking' => [
                    'id'            => '_is_dokan_accommodation',
                    'wrapper_class' => 'show_if_booking',
                    'label'         => __( 'Accommodation', 'dokan' ),
                    'description'   => __( 'Enable this if the bookable product is dokan accommodation', 'dokan' ),
                    'default'       => 'no',
                ],
            ]
        );
    }

    /**
     * Handles accommodation booking meta save
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function handle_accommodation_booking_meta_save() {
        global $post;

        if ( ! isset( $_POST['dokan_accommodation_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_accommodation_nonce'] ) ), 'dokan_accommodation_fields_save' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $product_id = $post->ID;

        $product = wc_get_product( $product_id );

        if ( ! $product || ! $product->is_type( 'booking' ) ) {
            return;
        }

        if ( ! isset( $_POST['_is_dokan_accommodation'] ) ) {
            $product->update_meta_data( '_dokan_is_accommodation_booking', 'no' );
            $product->save_meta_data();
            return;
        }

        // Getting checkin and checkout time for the accommodation booking
        $checkin_time  = isset( $_POST['_dokan_accommodation_checkin_time'] ) ? wc_clean( wp_unslash( $_POST['_dokan_accommodation_checkin_time'] ) ) : '';
        $checkout_time = isset( $_POST['_dokan_accommodation_checkout_time'] ) ? wc_clean( wp_unslash( $_POST['_dokan_accommodation_checkout_time'] ) ) : '';

        // Validate checkin time
        if ( strtotime( $checkin_time ) ) {
            $product->update_meta_data( '_dokan_accommodation_checkin_time', $checkin_time );
        }

        // Validate checkout time
        if ( strtotime( $checkout_time ) ) {
            $product->update_meta_data( '_dokan_accommodation_checkout_time', $checkout_time );
        }

        $dokan_accommodation = 'on' === wc_clean( wp_unslash( $_POST['_is_dokan_accommodation'] ) ) ? 'yes' : 'no';

        $product->update_meta_data( '_dokan_is_accommodation_booking', $dokan_accommodation );

        // Save product meta
        $product->save_meta_data();
    }
}
