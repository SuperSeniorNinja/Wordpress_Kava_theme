<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement\REST;

use WeDevs\DokanPro\Modules\ProductAdvertisement\Helper;
use WeDevs\DokanPro\Modules\ProductAdvertisement\Manager;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class AdvertisementController
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement\REST
 *
 * @since 3.5.0
 */
class AdvertisementController extends WP_REST_Controller {

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
    protected $base = 'product_adv';

    /**
     * Register all routes related with coupons
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => array_merge(
                        $this->get_collection_params(),
                        $this->get_advertisement_params()
                    ),
                ],
                'schema' => [ $this, 'get_public_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/expire', [
                'args' => [
                    'id' => [
                        'description'       => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'              => 'integer',
                        'required'          => true,
                        'arg_options' => [
                            'validate_callback' => [ $this, 'advertisement_exists' ],
                        ],
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'expire_item' ],
                    'permission_callback' => [ $this, 'expire_item_permissions_check' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', [
                'args' => [
                    'id' => [
                        'description'       => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'              => 'integer',
                        'required'          => true,
                        'arg_options' => [
                            'validate_callback' => [ $this, 'advertisement_exists' ],
                        ],
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/batch', [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'batch_items' ],
                    'permission_callback' => [ $this, 'batch_items_permissions_check' ],
                    'args'                => $this->get_batch_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/stores', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_stores' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => $this->get_collection_params(),
                ],
                'schema' => [ $this, 'get_public_schema_for_stores' ],
            ]
        );

        // frontend routes
        register_rest_route(
            $this->namespace, '/' . $this->base . '/create', [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => [
                        [
                            'context'  => $this->get_context_param(),
                            'product_id' => [
                                'description'       => __( 'To be advertise product id.', 'dokan' ),
                                'type'              => 'integer',
                                'required'          => true,
                                'sanitize_callback' => 'absint',
                                'validate_callback' => 'rest_validate_request_arg',
                                'minimum'           => 1,
                            ],
                            'vendor_id' => [
                                'description'       => __( 'Vendor of the product.', 'dokan' ),
                                'type'              => 'integer',
                                'required'          => true,
                                'sanitize_callback' => 'absint',
                                'validate_callback' => 'rest_validate_request_arg',
                                'minimum'           => 1,
                            ],
                        ],
                    ],
                ],
                'schema' => [ $this, 'get_public_schema_for_stores' ],
            ]
        );
    }


    /**
     * Checks if a given request has access to create an advertisement from dashboard.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @since 3.5.0
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function create_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Create a new advertisement
     *
     * @param WP_REST_Request $request
     *
     * @since 3.5.0
     *
     * @return WP_REST_Response
     */
    public function create_item( $request ) {
        $query_param = $request->get_params();

        // get advertisement data
        $advertisement_data = Helper::get_advertisement_data_by_product( $query_param['product_id'] );

        if ( empty( $advertisement_data ) ) {
            return rest_ensure_response( new WP_Error( 'invalid_product', __( 'No product found with given product ID. Please check your input.', 'dokan' ), [ 'status' => 401 ] ) );
        }

        // check if product status is publish
        if ( 'publish' !== $advertisement_data['post_status'] ) {
            return rest_ensure_response( new WP_Error( 'invalid_product', __( 'You can not advertise this product. Products need to be published before you can advertise.', 'dokan' ), [ 'status' => 401 ] ) );
        }

        // check if product is belong to given vendor id
        if ( ! $advertisement_data['vendor_id'] || intval( $query_param['vendor_id'] ) !== $advertisement_data['vendor_id'] ) {
            return rest_ensure_response( new WP_Error( 'invalid_vendor', __( 'Product id does not belong to given vendor. Please check your input', 'dokan' ), [ 'status' => 401 ] ) );
        }

        // check advertisement already exists in database, this is to prevent duplicate entry
        if ( $advertisement_data['already_advertised'] ) {
            return rest_ensure_response( new WP_Error( 'invalid_product', __( 'Advertisement for this product is already going on. Please select another product.', 'dokan' ), [ 'status' => 401 ] ) );
        }

        // check we've got slot left for advertisement
        if ( empty( $advertisement_data['global_remaining_slot'] ) ) {
            return rest_ensure_response( new WP_Error( 'empty_slot', __( 'There are no advertisement slots available at this moment.', 'dokan' ), [ 'status' => 401 ] ) );
        }

        $manager = new Manager();
        // prepare item for database
        $args = [
            'product_id'         => $advertisement_data['product_id'],
            'created_via'        => 'admin',      // possible values are order, admin, subscription, free
            'price'              => 0,
            'expires_after_days' => $advertisement_data['expires_after_days'],
            'status'             => 1,       // 1 for active, 2 for inactive
        ];

        $inserted = $manager->insert( $args );
        if ( is_wp_error( $inserted ) ) {
            return rest_ensure_response( new WP_Error( $inserted->get_error_code(), $inserted->get_error_message(), [ 'status' => 401 ] ) );
        }

        $response = rest_ensure_response( $manager->get( $inserted ) );

        return $response;
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @since 3.5.0
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function get_items_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Retrieves advertisement data
     *
     * @param WP_REST_Request $request
     *
     * @since 3.5.0
     *
     * @return WP_REST_Response
     */
    public function get_items( $request ) {
        $manager      = new Manager();
        $query_params = $request->get_params();
        $items        = [];

        // get item count
        $query_params['return'] = 'individual_count';
        $count = $manager->all( $query_params );

        // only run query if count value is greater than 0
        if ( $count['all'] > 0 ) {
            $query_params['return'] = 'all';
            $items = $manager->all( $query_params );
        }

        // check if got some results from database
        if ( ! empty( $items ) ) {
            $data = [];
            foreach ( $items as $item ) {
                $item   = $this->prepare_item_for_response( $item, $request );
                $data[] = $this->prepare_response_for_collection( $item );
            }
            $items = $data;
            unset( $data );
        }

        // get all status
        $filtered_count = $count['all'];
        if ( $query_params['status'] === 1 ) {
            $filtered_count = $count['active'];
        } elseif ( $query_params['status'] === 2 ) {
            $filtered_count = $count['expired'];
        }

        $response = rest_ensure_response( $items );
        $response->header( 'X-Status-All', $count['all'] );
        $response->header( 'X-Status-Active', $count['active'] );
        $response->header( 'X-Status-Expired', $count['expired'] );
        $response = $this->format_collection_response( $response, $request, $filtered_count );

        return $response;
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @since 3.5.0
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function expire_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Expire a single advertisement
     *
     * @param WP_REST_Request $request
     *
     * @since 3.5.0
     *
     * @return WP_REST_Response
     */
    public function expire_item( WP_REST_Request $request ) {
        $ids     = ! empty( $request['id'] ) ? (array) $request['id'] : [];
        $manager = new Manager();

        $data = $manager->batch_expire( $ids );

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @since 3.5.0
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Delete a single advertisement
     *
     * @param WP_REST_Request $request
     *
     * @since 3.5.0
     *
     * @return WP_REST_Response
     */
    public function delete_item( $request ) {
        $ids     = ! empty( $request['id'] ) ? (array) $request['id'] : [];
        $manager = new Manager();
        $data    = '';

        $data = $manager->batch_delete( $ids );

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @since 3.5.0
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function batch_items_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Batch update/delete advertised products
     *
     * @param WP_REST_Request $request
     *
     * @since 3.5.0
     *
     * @return WP_REST_Response
     */
    public function batch_items( $request ) {
        $action  = ! empty( $request['action'] ) ? $request['action'] : '';
        $ids     = ! empty( $request['ids'] ) ? $request['ids'] : [];
        $manager = new Manager();
        $data    = '';

        if ( 'delete' === $action ) {
            $data = $manager->batch_delete( $ids );
        } elseif ( 'expire' === $action ) {
            $data = $manager->batch_expire( $ids );
        }

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * This method will return unique stores for advertised products
     *
     * @param WP_REST_Request $request
     *
     * @since 3.5.0
     *
     * @return WP_REST_Response
     */
    public function get_stores( $request ) {
        $manager      = new Manager();
        $query_params = $request->get_params();

        // get item count
        $items = $manager->get_stores( $query_params );

        // rest ensure response
        $response = rest_ensure_response( $items );

        return $response;
    }

    /**
     * Prepare refund for response
     *
     * @since 3.5.0
     *
     * @param array $item
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request ) {
        $data = [
            'id'            => absint( $item['id'] ),
            'product_id'    => absint( $item['product_id'] ),
            'product_title' => sanitize_text_field( $item['product_title'] ),
            'vendor_id'     => absint( $item['vendor_id'] ),
            'store_name'    => sanitize_text_field( $item['store_name'] ),
            'created_via'   => in_array( $item['created_via'], [ 'order', 'admin', 'subscription', 'free' ], true ) ? $item['created_via'] : 'admin',
            'order_id'      => absint( $item['order_id'] ),
            'price'         => $item['price'],
            'expires_at'    => Helper::get_formatted_expire_date( $item['expires_at'] ),
            'status'        => absint( $item['status'] ),
            'post_status'   => sanitize_text_field( $item['post_status'] ),
            'added'         => $item['added'] > 0 ? dokan_format_date( $item['added'] ) : '',
        ];

        $response = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $item, $request ) );

        return apply_filters( 'dokan_rest_prepare_advertisement_object', $response, $item, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @since 3.5.0
     *
     * @param array $item
     * @param WP_REST_Request $request Request object.
     *
     * @return array Links for the given item.
     */
    protected function prepare_links( $item, $request ) {
        $links = [
            'self' => [
                'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $item['id'] ) ),
            ],
            'collection' => [
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
            ],
        ];

        return $links;
    }

    /**
     * Format item's collection for response
     *
     * @since 3.5.0
     *
     * @param  WP_REST_Response|WP_Error $response
     * @param  WP_REST_Request $request
     * @param  int $total_items
     *
     * @return WP_REST_Response|WP_Error
     */
    public function format_collection_response( $response, $request, $total_items ) {
        if ( 0 === $total_items ) {
            return $response;
        }

        // Store pagation values for headers then unset for count query.
        $per_page = (int) ( ! empty( $request['per_page'] ) ? $request['per_page'] : 20 );
        $page     = (int) ( ! empty( $request['page'] ) ? $request['page'] : 1 );

        $response->header( 'X-WP-Total', (int) $total_items );

        $max_pages = ceil( $total_items / $per_page );

        $response->header( 'X-WP-TotalPages', (int) $max_pages );
        $base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ) );

        // get previous page  link
        if ( $page > 1 ) {
            $prev_page = $page - 1;
            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }

        // get next page link
        if ( $max_pages > $page ) {
            $next_page = $page + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    }

    /**
     * This method will check if an advertisement exists or not, will be used only with rest api validate callback
     *
     * @param $value
     * @param $request WP_REST_Request
     * @param $key
     *
     * @since 3.5.0
     *
     * @return bool|WP_Error
     */
    public function advertisement_exists( $value, $request, $key ) {
        //we don't need to validate id field type as int for product_advertisement/1 endpoint, we need this for edit or delete request
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][ $key ] ) ) {
            $argument = $attributes['args'][ $key ];
            // Check to make sure our argument is a int.
            if ( 'int' === $argument['type'] && ! is_int( $value ) ) {
                // translators: 1) name of the key 2) data type
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'dokan' ), $key, 'int' ), [ 'status' => 400 ] );
            }
        } else {
            // This code won't execute because we have specified this argument as required.
            // If we reused this validation callback and did not have required args then this would fire.
            // translators: 1) name of the key
            return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'dokan' ), $key ), [ 'status' => 400 ] );
        }

        $manager = new Manager();
        $data = $manager->get( $value );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        return true;
    }

    /**
     * Retrieves the query params for the collections.
     *
     * @since 3.5.0
     *
     * @return array Query parameters for the collection.
     */
    public function get_advertisement_params() {
        return [
            'vendor_id' => [
                'description'       => __( 'Vendor IDs to filter form', 'dokan' ),
                'type'              => 'array',
                'default'           => [],
                'validate_callback' => 'rest_validate_request_arg',
                'items'             => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
            'product_id' => [
                'description'       => __( 'Product IDs to filter form', 'dokan' ),
                'type'              => 'array',
                'default'           => [],
                'validate_callback' => 'rest_validate_request_arg',
                'items'             => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
            'order_id' => [
                'description'       => __( 'Order IDs to filter form', 'dokan' ),
                'type'              => 'array',
                'default'           => [],
                'validate_callback' => 'rest_validate_request_arg',
                'items'             => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
            'status' => [
                'description' => __( 'Advertised product status, 0 to get all status, 1 for active advertisements and 2 for inactive advertisements', 'dokan' ),
                'required'    => false,
                'type'        => 'integer',
                'enum'        => [ 0, 1, 2 ],
                'default'     => 0,
            ],
            'expires_at'  => [
                'description' => __( 'Get advertised products by their expire date', 'dokan' ),
                'required'    => false,
                'type'        => 'object',
                'properties' => [
                    'min'  => [
                        'type'    => [ 'string', null ],
                        'pattern' => '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])',
                        'required'    => false,
                    ],
                    'max' => [
                        'type'    => [ 'string', null ],
                        'pattern' => '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])',
                        'required'    => false,
                    ],
                ],
            ],
            'created_via' => [
                'description' => __( 'Filter how advertisement was created', 'dokan' ),
                'required'    => false,
                'type'        => 'string',
                'enum'        => [ '', 'order', 'admin', 'subscription', 'free' ],
                'default'     => '',
            ],
            'return' => [
                'description' => __( 'How data will be returned', 'dokan' ),
                'type'        => 'string',
                'enum'        => [ 'all', 'ids', 'count', 'individual_count' ],
                'context'     => [ 'view' ],
                'default'     => 'all',
            ],
        ];
    }

    /**
     * Schema for batch processing
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_batch_params() {
        return [
            'context'  => $this->get_context_param(),
            'action'  => [
                'required'    => true,
                'description' => __( 'Batch action name to process', 'dokan' ),
                'type'        => 'string',
                'enum'        => [ 'expire', 'delete' ],
                'context'     => [ 'edit' ],
            ],
            'ids' => [
                'required'    => true,
                'description' => __( 'Batch action to carry on advertisement items', 'dokan' ),
                'type'        => 'array',
                'context'     => [ 'edit' ],
                'items'       => [
                    'type' => 'integer',
                ],
            ],
        ];
    }

    /**
     * Get the Cart schema, conforming to JSON Schema.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'product_advertisement',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                    'minimum'     => 1,
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                    'arg_options' => [
                        'validate_callback' => [ $this, 'advertisement_exists' ],
                    ],
                ],
                'product_id' => [
                    'description' => __( 'ID of the advertised product', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'required'    => true,
                ],
                'product_title' => [
                    'description' => __( 'Title of the advertised product', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'vendor_id' => [
                    'description' => __( 'ID of the product owner', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'store_name' => [
                    'description' => __( 'Store name of the advertised product', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'created_via' => [
                    'description' => __( 'How this advertisement was created, possible values are order or admin', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'enum'        => [ 'order', 'admin', 'subscription', 'free' ],
                    'default'     => 'admin',
                ],
                'order_id' => [
                    'description' => __( 'Order id of the advertised product', 'dokan' ),
                    'type'        => 'integer',
                    'minimum'     => 0,
                    'context'     => [ 'view', 'edit' ],
                    'default'     => 0,
                ],
                'price' => [
                    'description' => __( 'What was the price of this advertised products', 'dokan' ),
                    'type'        => 'number',
                    'context'     => [ 'view', 'edit' ],
                    'minimum'     => 0,
                    'default'    => 0.0000,
                ],
                'expires_at' => [
                    'description' => __( 'Advertisement expire date for this product', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => '',
                ],
                'status'     => [
                    'description' => __( 'Status of the advertise product, 1 for active and 2 for to get inactive advertisement', 'dokan' ),
                    'type'        => 'integer',
                    'enum'        => [ 0, 1, 2 ],
                    'default'     => 0,
                    'context'     => [ 'view', 'edit' ],
                ],
                'added' => [
                    'description' => __( 'When this advertisement was created', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'default'     => '',
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }

    /**
     * Get the Cart schema, conforming to JSON Schema.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_public_schema_for_stores() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'advertised_store',
            'type'       => 'object',
            'properties' => [
                'vendor_id' => [
                    'description' => __( 'ID of the product owner', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'store_name' => [
                    'description' => __( 'Store name of the advertised product', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }
}
