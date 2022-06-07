<?php

namespace WeDevs\DokanPro\Modules\Booking;

/**
 * Dependency Notice Handler Class
 *
 * @since 3.5.0
 */
class DependencyNotice {
    /**
     * Whether the module is loadable or not.
     *
     * @var bool
     */
    protected $missing_dependency = false;

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Check WooCommerce Booking plugin is activated
        if ( ! class_exists( 'WC_Bookings' ) ) {
            $this->missing_dependency = true;

            if ( current_user_can( 'activate_plugins' ) ) {
                add_filter( 'dokan_admin_notices', [ $this, 'wc_booking_activation_notice' ] );
                add_action( 'wp_ajax_dokan_activate_wc_booking', [ $this, 'activate_wc_booking' ] );
            }
        }
    }

    /**
     * Check has missing dependency
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function is_missing_dependency() {
        return $this->missing_dependency;
    }

    /**
     * WooCommerce Booking activation notice
     *
     * @since 3.5.0
     *
     * @param array $notices
     *
     * @return array
     */
    public function wc_booking_activation_notice( $notices ) {
        if ( $this->is_wc_booking_installed() ) {
            $notices[] = [
                'type'        => 'success',
                'title'       => __( 'Dokan WooCommerce Booking Integration module is almost ready!', 'dokan' ),
                /* translators: %s: plugin name */
                'description' => sprintf( __( 'You just need to activate the %s plugin to make it functional.', 'dokan' ), '<strong>WooCommerce Booking</strong>' ),
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'           => 'primary',
                        'text'           => __( 'Activate this plugin', 'dokan' ),
                        'loading_text'   => __( 'Activating...', 'dokan' ),
                        'completed_text' => __( 'Activated', 'dokan' ),
                        'reload'         => true,
                        'ajax_data'      => [
                            'action'   => 'dokan_activate_wc_booking',
                            '_wpnonce' => wp_create_nonce( 'dokan-wc-booking' ),
                        ],
                    ],
                ],
            ];
        } else {
            $notices[] = [
                'type'        => 'alert',
                'title'       => __( 'Dokan WooCommerce Booking Integration module is almost ready!', 'dokan' ),
                /* translators: %s: plugin name */
                'description' => sprintf( __( 'Dokan WooCommerce Booking requires %s plugin to be installed & activated!', 'dokan' ), '<strong> WooCommerce Booking</strong>' ),
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'   => 'primary',
                        'text'   => __( 'Get Now', 'dokan' ),
                        'target' => '_blank',
                        'action' => esc_url( 'https://woocommerce.com/products/woocommerce-bookings/' ),
                    ],
                ],
            ];
        }

        return $notices;
    }

    /**
     * Checks if Woocommerce Booking plugin is installed
     *
     * @since 3.5.0
     *
     * @return bool
     */
    private function is_wc_booking_installed() {
        $plugins = array_keys( get_plugins() );

        return in_array( 'woocommerce-bookings/woocommerce-bookings.php', $plugins, true );
    }

    /**
     * Activate Woocommerce Booking plugin
     *
     * @since 3.5.0
     *
     * @return void
     * */
    public function activate_wc_booking() {
        if (
            ! isset( $_REQUEST['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'dokan-wc-booking' ) // phpcs:ignore
        ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( __( 'You have no permission to do that', 'dokan' ) );
        }

        activate_plugin( 'woocommerce-bookings/woocommerce-bookings.php' );

        wp_send_json_success();
    }
}
