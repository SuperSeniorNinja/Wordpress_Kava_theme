<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Admin
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 *
 * @since 3.5.0
 */
class Admin {
    /**
     * Admin constructor.
     */
    public function __construct() {
        //enqueue required scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 10, 1 );
        // register admin menu
        add_action( 'dokan_admin_menu', [ $this, 'add_submenu' ], '16.1' );
        add_filter( 'dokan-admin-routes', [ $this, 'admin_routes' ] );
    }

    /**
     * Enqueue Admin Scripts
     *
     * @param string $hook
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function admin_enqueue_scripts( $hook ) {
        if ( 'toplevel_page_dokan' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'dokan-product-adv-admin' );
        wp_enqueue_style( 'dokan-product-adv-admin' );
    }

    /**
     * Add submenu page in dokan Dashboard
     *
     * @param string $capability
     *
     * @since DOKAN_PRP_SINCE
     *
     * @return void
     */
    public function add_submenu( $capability ) {
        if ( ! current_user_can( $capability ) ) {
            return;
        }

        global $submenu;

        $title = esc_html__( 'Advertising', 'dokan' );
        $slug  = 'dokan';

        $submenu[ $slug ][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/product-advertising' ]; // phpcs:ignore
    }

    /**
     * Add subscripton route
     *
     * @param  array $routes
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function admin_routes( $routes ) {
        $routes[] = [
            'path'      => '/product-advertising',
            'name'      => 'ProductAdvertisement',
            'component' => 'ProductAdvertisement',
        ];

        return $routes;
    }

}
