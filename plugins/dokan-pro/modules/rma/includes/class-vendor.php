<?php

use WeDevs\DokanPro\Refund\Refund;

/**
 * Vendor dashboard for RMA
 *
 * @package dokan
 *
 * @since 1.0.0
 */
class Dokan_RMA_Vendor {

    use Dokan_RMA_Common;

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'dokan_set_template_path', [ $this, 'load_rma_templates' ], 10, 3 );
        add_filter( 'dokan_get_dashboard_settings_nav', [ $this, 'load_settings_menu' ], 12 );
        add_filter( 'dokan_dashboard_settings_heading_title', [ $this, 'load_settings_header' ], 12, 2 );
        add_filter( 'dokan_dashboard_settings_helper_text', [ $this, 'load_settings_helper_text' ], 12, 2 );
        add_action( 'dokan_render_settings_content', [ $this, 'load_settings_content' ], 12 );

        add_filter( 'dokan_get_dashboard_nav', [ $this, 'add_rma_menu' ], 10, 1 );
        add_filter( 'dokan_query_var_filter', [ $this, 'rma_endpoints' ] );
        add_action( 'dokan_load_custom_template', [ $this, 'load_rma_template' ], 10, 1 );
        add_action( 'dokan_rma_request_content_inside_before', [ $this, 'show_seller_enable_message' ] );
        add_action( 'dokan_rma_reqeusts_after', [ $this, 'add_popup_template' ], 10 );
        add_action( 'template_redirect', [ $this, 'save_rma_settings' ], 10 );
        add_action( 'template_redirect', [ $this, 'handle_delete_rma_request' ], 10 );
    }

    /**
     * Show Seller Enable Error Message
     *
     * @since 2.4
     *
     * @return void
     */
    public function show_seller_enable_message() {
        $user_id = get_current_user_id();

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            echo dokan_seller_not_enabled_notice();
        }
    }

    /**
     * Add popup template from refund
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_popup_template() {
        dokan_get_template_part( 'rma/tmpl-add-refund-popup', '', [ 'is_rma' => true ] );
        dokan_get_template_part( 'rma/tmpl-send-coupon-popup', '', [ 'is_rma' => true ] );
    }

    /**
     * Load rma templates. so that it can overide from theme
     *
     * Just create `rma` folder inside dokan folder then
     * override your necessary template.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_rma_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_rma'] ) && $args['is_rma'] ) {
            return dokan_pro()->module->rma->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Add vendor rma menu
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_rma_menu( $urls ) {
        if ( dokan_is_seller_enabled( dokan_get_current_user_id() ) ) {
            $urls['return-request'] = array(
                'title'      => __( 'Return Request', 'dokan' ),
                'icon'       => '<i class="fas fa-undo-alt" aria-hidden="true"></i>',
                'url'        => dokan_get_navigation_url( 'return-request' ),
                'pos'        => 170,
                'permission' => 'dokan_view_store_rma_menu',
            );
        }

        return $urls;
    }

    /**
     * Return request endpoind
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function rma_endpoints( $query_var ) {
        $query_var[] = 'return-request';

        return $query_var;
    }

    /**
     * Load rma template for vendor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_rma_template( $query_vars ) {
        if ( isset( $query_vars['return-request'] ) ) {
            if ( ! current_user_can( 'dokan_view_store_rma_menu' ) ) {
                dokan_get_template_part(
                    'global/dokan-error', '', [
                        'deleted' => false,
                        'message' => __( 'You have no permission to view this requests page', 'dokan' ),
                    ]
                );
            } else {
                $warrnty_requests     = new Dokan_RMA_Warranty_Request();
                $conversation_request = new Dokan_RMA_Conversation();

                $get_data = wp_unslash( $_GET ); //phpcs:ignore

                if ( ! empty( $get_data['request'] ) ) {
                    dokan_get_template_part(
                        'rma/vendor-rma-single-request', '', [
                            'is_rma'        => true,
                            'request'       => $warrnty_requests->get( sanitize_text_field( $get_data['request'] ) ),
                            'conversations' => $conversation_request->get( [ 'request_id' => sanitize_text_field( $get_data['request'] ) ] ),
                        ]
                    );
                } else {
                    $data            = [];
                    $pagination_html = '';
                    $item_per_page   = 20;
                    $total_count     = dokan_get_warranty_request( [ 'count' => true ] );
                    $page            = isset( $get_data['pagenum'] ) ? absint( $get_data['pagenum'] ) : 1;
                    $offset          = ( $page * $item_per_page ) - $item_per_page;
                    $total_page      = ceil( $total_count['total_count'] / $item_per_page );

                    if ( ! empty( $get_data['status'] ) ) {
                        $data['status'] = sanitize_text_field( $get_data['status'] );
                    }

                    $data['number']    = $item_per_page;
                    $data['offset']    = $offset;
                    $data['vendor_id'] = dokan_get_current_user_id();

                    if ( $total_page > 1 ) {
                        $pagination_html = '<div class="pagination-wrap">';
                        $page_links = paginate_links(
                            array(
                                'base'      => add_query_arg( 'pagenum', '%#%' ),
                                'format'    => '',
                                'type'      => 'array',
                                'prev_text' => __( '&laquo; Previous', 'dokan' ),
                                'next_text' => __( 'Next &raquo;', 'dokan' ),
                                'total'     => $total_page,
                                'current'   => $page,
                            )
                        );
                        $pagination_html .= '<ul class="pagination"><li>';
                        $pagination_html .= join( "</li>\n\t<li>", $page_links );
                        $pagination_html .= "</li>\n</ul>\n";
                        $pagination_html .= '</div>';
                    };

                    dokan_get_template_part(
                        'rma/vendor-rma-requests', '', [
                            'is_rma'          => true,
                            'requests'        => $warrnty_requests->all( $data ),
                            'total_count'     => $total_count,
                            'pagination_html' => $pagination_html,
                        ]
                    );
                }
            }
            return;
        }
    }

    /**
     * Load rma settings menu in dashboard
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_settings_menu( $sub_settins ) {
        $sub_settins['rma'] = [
            'title'      => __( 'RMA', 'dokan' ),
            'icon'       => '<i class="fas fa-undo-alt" aria-hidden="true"></i>',
            'url'        => dokan_get_navigation_url( 'settings/rma' ),
            'pos'        => 93,
            'permission' => 'dokan_view_store_rma_menu',
        ];

        return $sub_settins;
    }

    /**
     * Load Settings Header
     *
     * @since 1.0.0
     *
     * @param  string $header
     * @param  array $query_vars
     *
     * @return string
     */
    public function load_settings_header( $header, $query_vars ) {
        if ( 'rma' === $query_vars ) {
            $header = __( 'Return and Warranty', 'dokan' );
        }

        return $header;
    }

    /**
     * Load Settings page helper
     *
     * @since 1.0.0
     *
     * @param  string $help_text
     * @param  array $query_vars
     *
     * @return string
     */
    public function load_settings_helper_text( $help_text, $query_vars ) {
        if ( 'rma' === $query_vars ) {
            $help_text = __( 'Set your settings for return and warranty your products. This settings will effect globally for your products', 'dokan' );
        }

        return $help_text;
    }

    /**
     * Load Settings Content
     *
     * @since 1.0.0
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_settings_content( $query_vars ) {
        if ( isset( $query_vars['settings'] ) && 'rma' === $query_vars['settings'] ) {
            if ( ! current_user_can( 'dokan_view_store_rma_menu' ) ) {
                dokan_get_template_part(
                    'global/dokan-error', '', [
                        'deleted' => false,
                        'message' => __( 'You have no permission to view this page', 'dokan' ),
                    ]
                );
            } else {
                $reasons      = dokan_rma_refund_reasons();
                $rma_settings = $this->get_settings();
                dokan_get_template_part(
                    'rma/settings', '', [
                        'is_rma'       => true,
                        'reasons'      => $reasons,
                        'rma_settings' => $rma_settings,
                    ]
                );
            }
        }
    }

    /**
     * Save vendor rma all settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save_rma_settings() {
        $get_postdata = wp_unslash( $_POST );
        if ( ! isset( $get_postdata['dokan_rma_vendor_settings'] ) ) {
            return;
        }

        if ( ! isset( $get_postdata['dokan_store_rma_form_nonce'] ) || ! wp_verify_nonce( $get_postdata['dokan_store_rma_form_nonce'], 'dokan_store_rma_form_action' ) ) {
            return;
        }

        $rma_policy_content              = wp_filter_post_kses( $get_postdata['warranty_policy'] );
        $get_postdata                    = wc_clean( $get_postdata );
        $get_postdata['warranty_policy'] = $rma_policy_content;
        $data                            = $this->transform_rma_settings( $get_postdata );

        update_user_meta( dokan_get_current_user_id(), '_dokan_rma_settings', $data );

        wp_safe_redirect( add_query_arg( [ 'message' => 'success' ], dokan_get_navigation_url( 'settings/rma' ) ), 302 );
    }

    /**
     * Handle delete rma request
     *
     * @since 3.0.7
     *
     * @return void
     */
    public function handle_delete_rma_request() {
        if ( ! dokan_is_user_seller( dokan_get_current_user_id() ) ) {
            return;
        }

        if ( ! current_user_can( 'dokan_view_store_rma_menu' ) ) {
            return;
        }

        $get_data = wp_unslash( $_GET );

        if ( isset( $get_data['action'] ) && 'dokan-delete-rma-request' === $get_data['action'] ) {
            $request_id = isset( $get_data['request_id'] ) ? (int) $get_data['request_id'] : 0;

            if ( ! $request_id ) {
                wp_safe_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'return-request' ) ) );
                return;
            }

            if ( ! wp_verify_nonce( $get_data['_wpnonce'], 'dokan-delete-rma-request' ) ) {
                wp_safe_redirect( add_query_arg( array( 'message' => 'error' ), dokan_get_navigation_url( 'return-request' ) ) );
                return;
            }

            $warrnty_requests = new Dokan_RMA_Warranty_Request();
            $warrnty_requests->delete( $request_id, dokan_get_current_user_id() );

            do_action( 'dokan_rma_request_deleted', $request_id );

            wc_add_notice( __( 'Return Request has been deleted successfully', 'dokan' ), 'success' );

            wp_safe_redirect( add_query_arg( array( 'message' => 'rma_request_deleted' ), dokan_get_navigation_url( 'return-request' ) ) );
            exit;
        }
    }

}
