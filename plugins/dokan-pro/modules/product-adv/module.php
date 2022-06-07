<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Admin\Admin;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Admin\Install;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Admin\Settings;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Admin\VendorSubscription;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\Cart;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\Order;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\Product;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\Shortcode;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\ProductWidget;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Module
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 *
 * @since 3.5.0
 */
final class Module {

    use ChainableContainer;

    /**
     * Cloning is forbidden.
     *
     * @since 3.5.0
     */
    public function __clone() {
        $message = ' Backtrace: ' . wp_debug_backtrace_summary();
        _doing_it_wrong( __METHOD__, $message . __( 'Cloning is forbidden.', 'dokan' ), DOKAN_PRO_PLUGIN_VERSION );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 3.5.0
     */
    public function __wakeup() {
        $message = ' Backtrace: ' . wp_debug_backtrace_summary();
        _doing_it_wrong( __METHOD__, $message . __( 'Unserializing instances of this class is forbidden.', 'dokan' ), DOKAN_PRO_PLUGIN_VERSION );
    }

    /**
     * Manager constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->set_controllers();
        $this->init_hooks();

        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_product_advertising', [ $this, 'activate' ], 10, 1 );
        add_action( 'dokan_deactivated_module_product_advertising', [ $this, 'deactivate' ], 10, 1 );
    }

    /**
     * Define module constants
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_PRODUCT_ADVERTISEMENT_FILE', __FILE__ );
        define( 'DOKAN_PRODUCT_ADVERTISEMENT_DIR', dirname( DOKAN_PRODUCT_ADVERTISEMENT_FILE ) );
        define( 'DOKAN_PRODUCT_ADVERTISEMENT_INC', DOKAN_PRODUCT_ADVERTISEMENT_DIR . '/includes/' );
        define( 'DOKAN_PRODUCT_ADVERTISEMENT_ASSETS', plugins_url( 'assets', DOKAN_PRODUCT_ADVERTISEMENT_FILE ) );
        define( 'DOKAN_PRODUCT_ADVERTISEMENT_TEMPLATE_PATH', DOKAN_PRODUCT_ADVERTISEMENT_DIR . '/templates/' );
    }

    /**
     * Set controllers
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['admin']         = new Admin();
        $this->container['settings']      = new Settings();
        $this->container['hooks']         = new Hooks();
        $this->container['products']      = new Product();
        $this->container['order']         = new Order();
        $this->container['cart']          = new Cart();
        $this->container['subscriptions'] = new VendorSubscription();

        if ( wp_doing_ajax() ) {
            $this->container['ajax'] = new Ajax();
        }

        if ( ! is_admin() ) {
            $this->container['shortcode'] = new Shortcode();
        }
    }

    /**
     * Call all hooks here
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_hooks() {
        // set action hooks
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] ); // include rest api class

        // set template path
        add_filter( 'dokan_set_template_path', [ $this, 'load_templates' ], 10, 3 );

        // register script and styles
        add_action( 'init', [ $this, 'register_scripts' ], 10 );

        // register widgets
        add_action( 'widgets_init', [ $this, 'register_product_advertisement_widget' ] );
    }

    /**
     * Register Product Advertisement Widget
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function register_product_advertisement_widget() {
        register_widget( ProductWidget::class );
    }

    /**
     * Rest api class map
     *
     * @param array $classes
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function rest_api_class_map( $classes ) {
        $class[ DOKAN_PRODUCT_ADVERTISEMENT_INC . '/REST/AdvertisementController.php' ] = '\WeDevs\DokanPro\Modules\ProductAdvertisement\REST\AdvertisementController';

        return array_merge( $classes, $class );
    }

    /**
     * Set template path for Product Advertisement module
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function load_templates( $template_path, $template, $args ) {
        if ( ! empty( $args['is_product_advertisement'] ) ) {
            return untrailingslashit( DOKAN_PRODUCT_ADVERTISEMENT_TEMPLATE_PATH );
        }

        return $template_path;
    }

    /**
     * Register all scripts
     *
     * @since 3.5.0
     *
     * @return void
     * */
    public function register_scripts() {
        $suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        // Register all js
        wp_register_script( 'dokan-product-adv-admin', DOKAN_PRODUCT_ADVERTISEMENT_ASSETS . '/js/admin' . $suffix . '.js', [ 'jquery', 'dokan-sweetalert2', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ], $version, true );

        // register all css
        wp_register_style( 'dokan-product-adv-admin', DOKAN_PRODUCT_ADVERTISEMENT_ASSETS . '/css/admin' . $suffix . '.css', [], $version );

        // register frontend scripts
        wp_register_script( 'dokan-product-adv-purchase', DOKAN_PRODUCT_ADVERTISEMENT_ASSETS . '/js/purchase_advertisement' . $suffix . '.js', [ 'jquery', 'dokan-sweetalert2' ], $version, true );
    }

    /**
     * This method will be called during module activation
     *
     * @since 3.5.0
     */
    public function activate( $instance ) {
        new Install();
    }

    /**
     * This method will be called during module deactivation
     *
     * @since 3.5.0
     */
    public function deactivate( $instance ) {
        // clear schedule
        wp_clear_scheduled_hook( 'dokan_product_advertisement_daily_at_midnight_cron' );
    }
}
