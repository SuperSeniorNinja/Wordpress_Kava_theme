<?php

namespace WeDevs\DokanPro\Modules\ReportAbuse;

class Admin {

    /**
     * Class constructor
     *
     * @since 2.9.8
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_admin_menu', [ self::class, 'add_admin_menu' ] );
        add_filter( 'dokan-admin-routes', [ self::class, 'add_admin_route' ] );
        add_action( 'dokan-vue-admin-scripts', [ self::class, 'enqueue_admin_script' ] );
    }

    /**
     * Add Dokan submenu
     *
     * @since 2.9.8
     *
     * @param string $capability
     *
     * @return void
     */
    public static function add_admin_menu( $capability ) {
        if ( current_user_can( $capability ) ) {
            global $submenu;

            $title = esc_html__( 'Abuse Reports', 'dokan' );
            $slug  = 'dokan';

            $submenu[ $slug ][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/abuse-reports' ];
        }
    }

    /**
     * Add admin page Route
     *
     * @since 2.9.8
     *
     * @param array $routes
     *
     * @return array
     */
    public static function add_admin_route( $routes ) {
        $routes[] = [
            'path'      => '/abuse-reports',
            'name'      => 'AbuseReports',
            'component' => 'AbuseReports'
        ];

        $routes[] = [
            'path'      => '/abuse-reports/:id',
            'name'      => 'AbuseReportsSingle',
            'component' => 'AbuseReportsSingle'
        ];

        return $routes;
    }

    /**
     * Enqueue admin script
     *
     * @since 2.9.8
     *
     * @return void
     */
    public static function enqueue_admin_script() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_style( 'woocommerce_select2', WC()->plugin_url() . '/assets/css/select2.css', [], WC_VERSION );
        wp_enqueue_script(
            'dokan-report-abuse-admin-vue',
            DOKAN_REPORT_ABUSE_ASSETS . '/js/dokan-report-abuse-admin' . $suffix . '.js',
            [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap', 'selectWoo' ],
            DOKAN_PRO_PLUGIN_VERSION,
            true
        );
    }
}
