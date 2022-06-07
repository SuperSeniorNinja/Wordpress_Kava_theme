<?php

namespace Texty\Gateways;

use WP_Error;

/**
 * Twilio Class
 *
 * @see https://www.twilio.com/docs/sms/api/message
 */
class Fake implements GatewayInterface {

    /**
     * Get the name
     *
     * @return string
     */
    public function name() {
        return __( 'Fake Gateway', 'texty' );
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function description() {
        return __( 'This is a fake gateway that logs the messages to <code>debug.log</code> file without sending the actual SMS.', 'texty' );
    }

    /**
     * Get the logo
     *
     * @return string
     */
    public function logo() {
        return TEXTY_URL . '/assets/images/logo.svg';
    }

    /**
     * Get the settings
     *
     * @return array
     */
    public function get_settings() {
        return [];
    }

    /**
     * Send SMS
     *
     * @param string $to
     * @param string $message
     *
     * @return WP_Error|true
     */
    public function send( $to, $message ) {
        $message = sprintf( 'To: %s; Message: %s', $to, $message );
        error_log( $message );

        return true;
    }

    /**
     * Validate a REST API request
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|true
     */
    public function validate( $request ) {
        return [];
    }
}
