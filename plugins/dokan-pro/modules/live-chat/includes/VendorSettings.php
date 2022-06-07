<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

defined( 'ABSPATH' ) || exit;

/**
 * Vendor Settings Class
 *
 * @since 1.0.0
 */
class VendorSettings {
    /**
     * Constructor method
     *
     * @return void
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize all the hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'dokan_settings_form_bottom', [ $this, 'dokan_live_chat_seller_settings' ], 15, 2 );
        add_action( 'dokan_store_profile_saved', [ $this, 'dokan_live_chat_save_seller_settings' ], 15 );
    }

    /**
     * Register live caht seller settings on seller dashboard
     *
     * @param int $user_id
     *
     * @param object $profile
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function dokan_live_chat_seller_settings( $user_id, $profile ) {
        if ( ! AdminSettings::is_enabled() ) {
            return;
        }

        $provider     = AdminSettings::get_provider();
        $is_messenger = 'messenger' === $provider;
        $is_tawkto    = 'tawkto' === $provider;
        $is_whatsapp  = 'whatsapp' === $provider;
        $enable_chat  = isset( $profile['live_chat'] ) ? $profile['live_chat'] : 'no';

        dokan_get_template( '/vendor-settings/settings.php', [
            'enable_chat' => $enable_chat,
        ], DOKAN_LIVE_CHAT_TEMPLATE, DOKAN_LIVE_CHAT_TEMPLATE );

        //messenger
        if ( $is_messenger ) {
            $fb_page_id = ! empty( $profile['fb_page_id'] ) ? $profile['fb_page_id'] : '';

            dokan_get_template( '/vendor-settings/messenger.php', [
                'fb_page_id' => $fb_page_id,
            ], DOKAN_LIVE_CHAT_TEMPLATE, DOKAN_LIVE_CHAT_TEMPLATE );
        }

        //tawkto
        if ( $is_tawkto ) {
            $tawkto_property_id = ! empty( $profile['tawkto_property_id'] ) ? $profile['tawkto_property_id'] : '';
            $tawkto_widget_id   = ! empty( $profile['tawkto_widget_id'] ) ? $profile['tawkto_widget_id'] : '';

            dokan_get_template( '/vendor-settings/tawkto.php', [
                'tawkto_property_id' => $tawkto_property_id,
                'tawkto_widget_id'   => $tawkto_widget_id,
            ], DOKAN_LIVE_CHAT_TEMPLATE, DOKAN_LIVE_CHAT_TEMPLATE );
        }

        //whatsapp
        if ( $is_whatsapp ) {
            $whatsapp_number = ! empty( $profile['whatsapp_number'] ) ? $profile['whatsapp_number'] : '';

            dokan_get_template( '/vendor-settings/whatsapp.php', [
                'whatsapp_number' => $whatsapp_number,
            ], DOKAN_LIVE_CHAT_TEMPLATE, DOKAN_LIVE_CHAT_TEMPLATE );
        }
    }

    /**
     * Save dokan live chat seller settings
     *
     * @param int $user_id
     *
     * @return void
     */
    public function dokan_live_chat_save_seller_settings( $user_id ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'dokan_store_settings_nonce' ) ) {
            return;
        }

        if ( ! isset( $_POST['live_chat'] ) ) {
            return;
        }

        $store_info                       = dokan_get_store_info( $user_id );
        $store_info['live_chat']          = isset( $_POST['live_chat'] ) ? wc_clean( wp_unslash( $_POST['live_chat'] ) ) : '';
        $store_info['fb_page_id']         = isset( $_POST['fb_page_id'] ) ? wc_clean( wp_unslash( $_POST['fb_page_id'] ) ) : '';
        $store_info['tawkto_property_id'] = isset( $_POST['tawkto_property_id'] ) ? wc_clean( wp_unslash( $_POST['tawkto_property_id'] ) ) : '';
        $store_info['tawkto_widget_id']   = isset( $_POST['tawkto_widget_id'] ) ? wc_clean( wp_unslash( $_POST['tawkto_widget_id'] ) ) : '';
        $store_info['whatsapp_number']    = isset( $_POST['whatsapp_number'] ) ? wc_clean( wp_unslash( $_POST['whatsapp_number'] ) ) : '';

        update_user_meta( $user_id, 'dokan_profile_settings', $store_info );
    }
}
