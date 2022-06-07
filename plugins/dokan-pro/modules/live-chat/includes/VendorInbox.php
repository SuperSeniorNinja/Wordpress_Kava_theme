<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan seller inbox class
 *
 * @since 1.1
 */
class VendorInbox {
    /**
     * Constructor method of this class
     *
     * @since 1.1
     */
    public function __construct() {
        if ( ! AdminSettings::is_enabled() || 'talkjs' !== AdminSettings::get_provider() ) {
            return;
        }

        $this->init_hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 1.1
     *
     * @return void
     */
    public function init_hooks() {
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'dokan_add_inbox_menu' ), 22, 1 );
        add_filter( 'dokan_query_var_filter', array( $this, 'dokan_add_endpoint' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'dokan_load_inbox_template' ), 22 );
        add_action( 'dokan_set_template_path', [ $this, 'set_template_path' ], 10, 3 );
    }

    /**
     * Register inbox menu on seller dashboard
     *
     * @param array $urls
     *
     * @since 1.0
     *
     * @return array
     */
    public function dokan_add_inbox_menu( $urls ) {
        if ( dokan_is_seller_enabled( get_current_user_id() ) ) {
            $urls['inbox'] = array(
                'title' => __( 'Inbox', 'dokan' ),
                'icon'  => '<i class="fas fa-comment"></i>',
                'url'   => dokan_get_navigation_url( 'inbox' ),
                'pos'   => 195,
                'permission' => 'dokan_view_inbox_menu',
            );
        }

        return $urls;
    }

    /**
     * Add inbox endpoint to Dashboard
     *
     * @param array $query_var
     *
     * @since 1.0
     *
     * @return array
     */
    public function dokan_add_endpoint( $query_var ) {
        $query_var['inbox'] = 'inbox';

        return $query_var;
    }

    /**
     * Set template path
     *
     * @since DOKAN_PRO_SINEC
     *
     * @param string $template_path
     * @param string $template
     * @param array $args
     *
     * @return string
     */
    public function set_template_path( $template_path, $template, $args ) {
        if ( ! empty( $args['is_live_chat'] ) ) {
            return DOKAN_LIVE_CHAT_TEMPLATE;
        }

        return $template_path;
    }

    /**
     * Dokan Load inbox template
     *
     * @param  array $query_vars
     *
     * @since 1.0
     *
     * @return string
     */
    public function dokan_load_inbox_template( $query_vars ) {
        if ( ! isset( $query_vars['inbox'] ) ) {
            return;
        }

        dokan_get_template_part( 'inbox', '', [ 'is_live_chat' => true ] );
    }
}
