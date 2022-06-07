<?php

class Dokan_Seller_Vacation_Install {

    /**
     * Class constructor
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_activated_module_seller_vacation', array( $this, 'activate' ) );
        add_action( 'dokan_deactivated_module_seller_vacation', array( $this, 'deactivate' ) );
    }

    /**
     * Placeholder for activation function
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function activate() {
        if ( ! wp_next_scheduled( 'check_daily_is_vacation_is_set_action' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'check_daily_is_vacation_is_set_action' );
        }
    }

    /**
     * Placeholder for deactivation function
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function deactivate() {
        Dokan_Seller_Vacation_Cron::unschedule_event();

        $processor_file = DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-update-seller-product-status.php';

        require_once $processor_file;

        $processor = new Dokan_Seller_Vacation_Update_Seller_Product_Status();
        $processor->cancel_process();
    }
}
