<?php

namespace WeDevs\DokanPro\Modules\ShipStation;

class Module {

    /**
     * Module version
     *
     * @var string
     *
     * @since 1.0.0
     */
    public $version = '1.0.0';

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->instances();
    }

    /**
     * Module constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_SHIPSTATION_VERSION' , $this->version );
        define( 'DOKAN_SHIPSTATION_PATH' , dirname( __FILE__ ) );
        define( 'DOKAN_SHIPSTATION_INCLUDES' , DOKAN_SHIPSTATION_PATH . '/includes' );
        define( 'DOKAN_SHIPSTATION_URL' , plugins_url( '', __FILE__ ) );
        define( 'DOKAN_SHIPSTATION_ASSETS' , DOKAN_SHIPSTATION_URL . '/assets' );
        define( 'DOKAN_SHIPSTATION_VIEWS', DOKAN_SHIPSTATION_PATH . '/views' );
        define( 'DOKAN_SHIPSTATION_EXPORT_LIMIT', 100 );
    }

    /**
     * Include module related PHP files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_SHIPSTATION_INCLUDES . '/functions.php';
        require_once DOKAN_SHIPSTATION_INCLUDES . '/class-dokan-shipstation-hooks.php';
        require_once DOKAN_SHIPSTATION_INCLUDES . '/class-dokan-shipstation-settings.php';
    }

    /**
     * Create module related class instances
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function instances() {
        new \Dokan_ShipStation_Hooks();
        new \Dokan_ShipStation_Settings();
    }
}
