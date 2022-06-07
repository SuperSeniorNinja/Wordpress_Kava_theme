<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Background_Process', false ) ) {
    include_once dirname( WC_PLUGIN_FILE ) . 'includes/abstracts/class-wc-background-process.php';
}

class Dokan_Seller_Vacation_Update_Seller_Product_Status extends WC_Background_Process {

    const PRODUCT_LIMIT = 30;

    /**
     * Initiate new background process.
     *
     * @since 3.2.4
     * @return void
     */
    public function __construct() {
        // Uses unique prefix per blog so each blog has separate queue.
        $this->prefix = 'wp_' . get_current_blog_id();
        $this->action = 'dokan_pro_sv_update_seller_product_status'; //Dokan_Seller_Vacation_Update_Seller_Product_Status

        parent::__construct();
    }

    /**
     * Dispatch updater.
     *
     * Updater will still run via cron job if this fails for any reason.
     *
     * @since 3.2.4
     * @return void
     */
    public function dispatch() {
        $dispatched = parent::dispatch();

        if ( is_wp_error( $dispatched ) ) {
            dokan_log(
                sprintf( 'Unable to dispatch Dokan Seller Vacation Update Seller Product Status: %s', $dispatched->get_error_message() ),
                'error'
            );
        }
    }

    /**
     * Handle cron healthcheck
     *
     * Restart the background process if not already running
     * and data exists in the queue.
     *
     * @since 3.2.4
     * @return void
     */
    public function handle_cron_healthcheck() {
        if ( $this->is_process_running() ) {
            // Background process already running.
            return;
        }

        if ( $this->is_queue_empty() ) {
            // No data to process.
            $this->clear_scheduled_event();
            return;
        }

        $this->handle();
    }

    /**
     * Schedule fallback event.
     *
     * @since 3.2.4
     * @return void
     */
    protected function schedule_event() {
        if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
            wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
        }
    }

    /**
     * Perform task
     *
     * @since 2.9.10
     *
     * @param array $args
     *
     * @return bool|array
     */
    public function task( $args ) {
        if ( empty( $args['vendor_id'] ) ) {
            return false;
        }

        $vendor_id   = intval( $args['vendor_id'] );
        $on_vacation = dokan_seller_vacation_is_seller_on_vacation( $vendor_id );

        if ( $on_vacation ) {
            return $this->set_products_status_as_vacation( $args );
        } else {
            return $this->set_products_status_as_publish( $args );
        }
    }

    /**
     * Switch status from vacation to publish
     *
     * @since 2.9.10
     *
     * @param array $args
     * @return bool|array
     */
    private function set_products_status_as_publish( $args ) {
        $args['current_status'] = 'vacation';
        $args['new_status']     = 'publish';
        return $this->update_products( $args );
    }

    /**
     * Switch status from publish to vacation
     *
     * @since 2.9.10
     *
     * @param array $args
     * @return bool|array
     */
    private function set_products_status_as_vacation( $args ) {
        $args['current_status'] = 'publish';
        $args['new_status']     = 'vacation';
        return $this->update_products( $args );
    }

    /**
     * Update products
     *
     * @since 2.9.10
     *
     * @param array $args
     *
     * @return bool|array
     */
    private function update_products( $args ) {
        $vendor_id = intval( $args['vendor_id'] );

        $products = wc_get_products(
            [
				'author' => $vendor_id,
				'status' => $args['current_status'],
				'limit'  => self::PRODUCT_LIMIT,
            ]
        );

        if ( empty( $products ) ) {
            return false;
        }

        foreach ( $products as $product ) {
            $product->set_status( $args['new_status'] );
            $product->save();
        }

        return $args;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     *
     * @since 3.2.4
     * @return void
     */
    protected function complete() {
        dokan_log( 'Vendor vaccation mode product status update completed.', 'info' );
        parent::complete();
    }
}
