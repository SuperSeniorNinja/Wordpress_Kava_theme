<?php

class Dokan_Seller_Vacation_Store_Settings {

    /**
     * Class construct
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_settings_form_bottom', array( $this, 'store_settings_form' ), 10, 2 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'dokan_store_profile_saved', array( $this, 'save_settings' ), 18 );
    }


    /**
     * Store settings form
     *
     * @since 2.9.10
     *
     * @param object $current_user
     * @param array  $profile_info
     *
     * @return void
     */
    public function store_settings_form( $current_user, $profile_info ) {
        $closing_style_options = array(
            'instantly' => __( 'Instantly Close', 'dokan' ),
            'datewise'  => __( 'Date Wise Close', 'dokan' ),
        );

        $setting_go_vacation      = isset( $profile_info['setting_go_vacation'] ) ? esc_attr( $profile_info['setting_go_vacation'] ) : 'no';
        $settings_closing_style   = isset( $profile_info['settings_closing_style'] ) ? esc_attr( $profile_info['settings_closing_style'] ) : 'open';
        $setting_vacation_message = isset( $profile_info['setting_vacation_message'] ) ? esc_attr( $profile_info['setting_vacation_message'] ) : '';

        $show_schedules            = dokan_validate_boolean( $setting_go_vacation ) && ( 'datewise' === $settings_closing_style );
        $seller_vacation_schedules = dokan_seller_vacation_get_vacation_schedules( $profile_info );

        dokan_seller_vacation_get_template( 'store-settings', array(
            'closing_style_options'     => $closing_style_options,
            'setting_go_vacation'       => $setting_go_vacation,
            'settings_closing_style'    => $settings_closing_style,
            'setting_vacation_message'  => $setting_vacation_message,
            'show_schedules'            => $show_schedules,
            'seller_vacation_schedules' => $seller_vacation_schedules,
        ) );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        global $wp;

        if( isset( $wp->query_vars['settings'] ) && 'store' === $wp->query_vars['settings'] ) {
            wp_enqueue_style( 'dokan-seller-vacation', DOKAN_SELLER_VACATION_ASSETS . '/css/dokan-seller-vacation.css', array(), DOKAN_PRO_PLUGIN_VERSION );

            wp_enqueue_script( 'dokan-seller-vacation', DOKAN_SELLER_VACATION_ASSETS . '/js/dokan-seller-vacation.js', array( 'jquery', 'jquery-ui-datepicker', 'dokan-moment' ), false, true );

            wp_localize_script( 'dokan-seller-vacation', 'dokanSellerVacation', array(
                'i18n' => array(
                    'vacation_date_is_not_set' => __( 'No vacation is set', 'dokan' ),
                    'invalid_from_date'        => __( 'Invalid from date', 'dokan' ),
                    'invalid_to_date'          => __( 'Invalid to date', 'dokan' ),
                    'empty_message'            => __( 'Message is empty', 'dokan' ),
                    'save'                     => __( 'Save', 'dokan' ),
                    'saving'                   => __( 'Saving', 'dokan' ),
                    'confirm_delete'           => __( 'Are you sure you want to delete this item?', 'dokan' ),
                ),
            ) );
        }
    }

    /**
     * Save vacation settings with store settings
     *
     * @since 2.9.10
     *
     * @param int   $store_id
     * @param array $dokan_settings
     *
     * @return void
     */
    public function save_settings( $store_id ) {
        $dokan_settings = dokan_get_store_info( $store_id );

        if( ! isset( $_POST['setting_go_vacation'] ) ) {
            return;
        }

        $dokan_settings['setting_go_vacation']      = sanitize_text_field( $_POST['setting_go_vacation'] );
        $dokan_settings['settings_closing_style']   = sanitize_text_field( $_POST['settings_closing_style'] );
        $dokan_settings['setting_vacation_message'] = sanitize_textarea_field( $_POST['setting_vacation_message'] );

        if ( $dokan_settings['setting_go_vacation'] == 'yes' ) {
            update_user_meta( $store_id, 'dokan_enable_seller_vacation', true );
        }

        if ( 'datewise' !== $dokan_settings['settings_closing_style'] ) {
            $dokan_settings['seller_vacation_schedules'] = array();
        }

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

        if ( ! dokan_validate_boolean( $dokan_settings['setting_go_vacation'] ) ) {
            $dokan_enable_seller_vacation = get_user_meta( $store_id, 'dokan_enable_seller_vacation', true );

            if( $dokan_enable_seller_vacation ) {
                update_user_meta( $store_id, 'dokan_enable_seller_vacation', false );
            }
        }

        $vendor = dokan()->vendor->get( $store_id );
        dokan_seller_vacation_update_product_status( [ $vendor ], false );
    }
}
