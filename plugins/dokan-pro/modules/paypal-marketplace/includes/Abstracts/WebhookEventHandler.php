<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts;

/**
 * Class WebhookHandler
 * @package WeDevs\Dokan\Gateways\PayPal\Interfaces
 *
 * @since 3.3.0
 *
 * @author weDevs
 */
abstract class WebhookEventHandler {

    /**
     * Event holder
     */
    private $event;

    /**
     * Handle the event
     *
     * @since 3.3.0
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Set event
     *
     * @param $event
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function set_event( $event ) {
        $this->event = $event;
    }

    /**
     * Get event
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_event() {
        return $this->event;
    }
}
