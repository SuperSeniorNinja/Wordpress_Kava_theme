<?php
/**
 * Class Dokan_Admin_Support file
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Dokan_Admin_Support' ) ) :

    /**
     * Support ticket class for admin
     *
     * This class creates menu in admin dashboard, registers vue.js routes for admin
     * dashboard and enqueue scripts and styles.
     *
     * @class Dokan_Admin_Support
     *
     * @version 3.5.0
     */
    class Dokan_Admin_Support {

        /**
         * Class constructor.
         *
         * @since 3.5.0
         */
        public function __construct() {
            add_action( 'dokan_admin_menu', [ $this, 'add_admin_menu' ] );
            add_filter( 'dokan-admin-routes', [ $this, 'add_admin_route' ] );
            add_action( 'dokan-vue-admin-scripts', [ $this, 'enqueue_admin_script' ] );
        }

        /**
         * Add Dokan submenu
         *
         * @since 3.5.0
         *
         * @param string $capability
         *
         * @return void
         */
        public function add_admin_menu( $capability ) {
            if ( current_user_can( $capability ) ) {
                global $submenu;

                $title = esc_html__( 'Store Support', 'dokan' );
                $slug  = 'dokan';

                $submenu[ $slug ][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/admin-store-support' ]; //phpcs:ignore
            }
        }

        /**
         * Add admin page Route
         *
         * @since 3.5.0
         *
         * @param array $routes
         *
         * @return array
         */
        public function add_admin_route( $routes ) {
            $routes[] = [
                'path'      => '/admin-store-support',
                'name'      => 'AdminStoreSupport',
                'component' => 'AdminStoreSupport',
            ];

            return $routes;
        }

        /**
         * Enqueue admin script
         *
         * @since 3.5.0
         *
         * @return void
         */
        public function enqueue_admin_script() {
            // Use minified libraries if SCRIPT_DEBUG is turned off
            $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

            wp_enqueue_style(
                'dokan-admin-store-support-vue',
                DOKAN_STORE_SUPPORT_PLUGIN_ASSEST . '/dist/css/dokan-admin-store-support' . $suffix . '.css',
                [],
                DOKAN_PRO_PLUGIN_VERSION
            );
            wp_enqueue_script(
                'dokan-admin-store-support-vue',
                DOKAN_STORE_SUPPORT_PLUGIN_ASSEST . '/dist/js/dokan-admin-store-support' . $suffix . '.js',
                [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
                DOKAN_PRO_PLUGIN_VERSION,
                true
            );
        }
    }
endif;
