<?php

/**
 * Class DokanFollowStoreRestController
 *
 * @since 3.2.1
 *
 * @author weDevs
 */
class DokanFollowStoreRestController extends WP_REST_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'follow-store';

    /**
     * Register follow-store routes
     *
     * @since 3.2.1
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_current_status' ],
                'args'                => [
                    'vendor_id' => [
                        'type'        => 'integer',
                        'description' => __( 'Vendor ID', 'dokan' ),
                        'required'    => true,
                    ],
                ],
                'permission_callback' => 'is_user_logged_in',
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'toggle_follow_status' ],
                'args'                => [
                    'vendor_id' => [
                        'type'        => 'integer',
                        'description' => __( 'Vendor ID', 'dokan' ),
                        'required'    => true,
                    ],
                ],
                'permission_callback' => 'is_user_logged_in',
            ],
        ] );
    }

    /**
     * Toggle follow status of a store
     *
     * @param $request
     *
     * @since 3.2.1
     *
     * @return WP_Error|WP_REST_Response
     */
    public function toggle_follow_status( $request ) {
        $vendor_id = $request->get_param( 'vendor_id' );
        $vendor    = dokan()->vendor->get( $vendor_id );

        if ( ! $vendor->id ) {
            return new WP_Error(
                'dokan_rest_no_vendor_found',
                __( 'No vendor found', 'dokan' ),
                [ 'status' => 404 ]
            );
        }

        $customer_id = get_current_user_id();

        $status = dokan_follow_store_toggle_status( $vendor->id, $customer_id );

        if ( is_wp_error( $status ) ) {
            return new WP_Error(
                'dokan_rest_vendor_follow_toggle',
                $status->get_error_message(),
                [ 'status' => 422 ]
            );
        }

        $data = [ 'status' => $status ];

        return rest_ensure_response( $data );
    }

    /**
     * Get current follow status for a store
     *
     * @param $request
     *
     * @since 3.2.1
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_current_status( $request ) {
        $vendor_id = $request->get_param( 'vendor_id' );
        $vendor    = dokan()->vendor->get( $vendor_id );

        if ( ! $vendor->id ) {
            return new WP_Error(
                'dokan_rest_no_vendor_found',
                __( 'No vendor found', 'dokan' ),
                [ 'status' => 404 ]
            );
        }

        $customer_id = get_current_user_id();

        $is_following = dokan_follow_store_is_following_store( $vendor_id, $customer_id );

        if ( is_wp_error( $is_following ) ) {
            return new WP_Error(
                'dokan_rest_vendor_follow_status',
                $is_following->get_error_message(),
                [ 'status' => 422 ]
            );
        }

        return rest_ensure_response( [ 'status' => $is_following ] );
    }
}
