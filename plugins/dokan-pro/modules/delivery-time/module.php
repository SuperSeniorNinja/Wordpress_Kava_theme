<?php


namespace WeDevs\DokanPro\Modules\DeliveryTime;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\DeliveryTime\Admin;
use WeDevs\DokanPro\Modules\DeliveryTime\Vendor;
use WeDevs\DokanPro\Modules\DeliveryTime\Frontend;
use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\StoreSettings;

/**
 * Class Module
 * @package WeDevs\DokanPro\DeliveryTime
 */
class Module {

    use ChainableContainer;

    /**
     * Delivery Time Manager constructor
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function __construct() {
        $this->define_constant();
        $this->initiate();

        add_action( 'dokan_activated_module_delivery_time', [ $this, 'activate' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_templates' ], 10, 3 );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_scripts' ] );

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Define all constants
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function define_constant() {
        define( 'DOKAN_DELIVERY_TIME_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_DELIVERY_INC_DIR', DOKAN_DELIVERY_TIME_DIR . '/includes' );
        define( 'DOKAN_DELIVERY_TEMPLATE_DIR', DOKAN_DELIVERY_TIME_DIR . '/templates' );
        define( 'DOKAN_DELIVERY_TIME_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
     * Initiates the classes
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function initiate() {
        // Load Delivery Time Admin class
        if ( is_admin() ) {
            $this->container['dt_admin']    = new Admin();
            $this->container['dt_settings'] = new Settings();
        }

        // Load Delivery Time Frontend class
        if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $this->container['dt_frontend'] = new Frontend();
        }

        // Load Delivery Time Vendor class
        $this->container['dt_vendor'] = new Vendor();

        // Load Store Location Pickup classes
        $this->container['dt_store_location_pickup']          = new StoreSettings();
        $this->container['dt_store_location_pickup_frontend'] = new StorePickup\Frontend();
        $this->container['dt_store_location_pickup_vendor']   = new StorePickup\Vendor();
    }

    /**
     * Activates the module
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function activate() {
        $this->create_tables();
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Set template path for Wholesale
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function load_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_delivery_time'] ) && $args['is_delivery_time'] ) {
            return DOKAN_DELIVERY_TEMPLATE_DIR;
        }
        return $template_path;
    }

    /**
     * Creates Delivery time database table
     *
     * @since 3.3.0
     *
     * @return void
     */
    private function create_tables() {
        global $wpdb;

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $collate = $wpdb->get_charset_collate();

        $table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_delivery_time` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` int(11) NOT NULL,
                  `vendor_id` int(11) NOT NULL,
                  `date` varchar(25) NOT NULL DEFAULT '',
                  `slot` varchar(25) NOT NULL DEFAULT '',
                  `delivery_type` varchar(25) DEFAULT 'delivery',
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `key_vendor_id_date` (`vendor_id`,`date`),
                  KEY `key_vendor_id_date_type` (`vendor_id`,`date`,`delivery_type`),
                  KEY `key_slot` (`slot`)
                ) ENGINE=InnoDB {$collate}";

        dbDelta( $table );
    }

    /**
     * Registers frontend scripts
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function register_frontend_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;

        wp_register_script( 'dokan-delivery-time-main-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-main' . $suffix . '.js', [ 'jquery' ], $version, true );
        wp_register_script( 'dokan-delivery-time-vendor-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-vendor' . $suffix . '.js', [ 'jquery' ], $version, true );

        wp_register_script( 'dokan-delivery-time-flatpickr-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/flatpickr.min.js', false, $version, true );
        wp_register_style( 'dokan-delivery-time-flatpickr-style', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/flatpickr.min.css', false, $version, 'all' );

        wp_register_script( 'dokan-delivery-time-fullcalender-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/fullcalender.min.js', false, $version, true );
        wp_register_style( 'dokan-delivery-time-fullcalender-style', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/fullcalender.min.css', false, $version, 'all' );

        wp_register_style( 'dokan-delivery-time-vendor-style', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/css/script-style' . $suffix . '.css', false, $version, 'all' );

        wp_register_script( 'dokan-store-location-pickup-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-store-location-pickup' . $suffix . '.js', [ 'jquery' ], $version, true );

        if ( false !== get_query_var( 'delivery-time-dashboard', false ) ) {
            wp_enqueue_script( 'dokan-chart' );

            wp_enqueue_style( 'dokan-timepicker' );
        }
    }

    /**
     * Registers admin scripts
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function register_admin_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;

        wp_register_script( 'dokan-delivery-time-admin-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-admin' . $suffix . '.js', [ 'jquery', 'jquery-ui-datepicker' ], $version, true );
    }
}
