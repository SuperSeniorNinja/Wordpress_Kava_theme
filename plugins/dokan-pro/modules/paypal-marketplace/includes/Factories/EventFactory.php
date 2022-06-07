<?php

namespace WeDevs\DokanPro\Modules\PayPalMarketplace\Factories;

use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Abstracts\WebhookEventHandler;
use BadMethodCallException;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class EventFactory
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\Factories
 *
 * @since 3.3.0
 */
class EventFactory {

    /**
     * Call the defined static methods
     *
     * @param $name
     * @param $arguments
     *
     * @since 3.3.0
     *
     * @return mixed
     * @throws DokanException
     */
    public static function __callStatic( $name, $arguments ) {
        try {
            if ( 'handle' !== $name ) {
                throw new BadMethodCallException( sprintf( 'The %s method is not callable.', $name ), 422 );
            }

            if ( ! empty( $arguments[0] ) ) {
                $event                          = $arguments[0];
                $webhook_event_handler_instance = self::get( $event );

                if ( $webhook_event_handler_instance instanceof WebhookEventHandler ) {
                    return $webhook_event_handler_instance->$name();
                }

                do_action( 'dokan_paypal_marketplace_events', $event, $name );
            }
        } catch ( \Exception $e ) {
            dokan_log( '[Dokan PayPal Marketplace] Webhook Rendering Error: ' . $e->getMessage() );
        }
    }

    /**
     * Create required event class instance
     *
     * @param $event
     *
     * @since 3.3.0
     *
     * @return WebhookEventHandler|void instance
     * @throws DokanException
     */
    public static function get( $event ) {
        $events = Helper::get_supported_webhook_events();
        $class  = null;

        if ( ! array_key_exists( $event->event_type, $events ) ) {
            return;
        }

        $class = $events[ $event->event_type ];
        $class = "\\WeDevs\\DokanPro\\Modules\\PayPalMarketplace\\WebhookEvents\\{$class}";

        if ( ! class_exists( $class ) ) {
            throw new DokanException(
                'dokan_paypal_unsupported_event',
                sprintf( 'This %s is not supported yet', $class ),
                422
            );
        }

        return new $class( $event );
    }
}
