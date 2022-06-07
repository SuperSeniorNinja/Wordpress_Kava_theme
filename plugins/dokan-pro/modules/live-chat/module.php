<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;
use WeDevs\Dokan\Traits\ChainableContainer;

class Module {

    use ChainableContainer;

    /**
     * Constructor method for this class
     */
    public function __construct() {
        $this->define_constants();
        $this->init_classes();

        add_action( 'dokan_activated_module_live_chat', [ $this, 'activate' ] );
        add_action( 'dokan_deactivated_module_live_chat', [ $this, 'deactivate' ] );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Define all the constants
     *
     * @since 1.0
     *
     * @return string
     */
    private function define_constants() {
        define( 'DOKAN_LIVE_CHAT', dirname( __FILE__ ) );
        define( 'DOKAN_LIVE_CHAT_INC', DOKAN_LIVE_CHAT . '/includes' );
        define( 'DOKAN_LIVE_CHAT_ASSETS', plugins_url( 'assets', __FILE__ ) );
        define( 'DOKAN_LIVE_CHAT_TEMPLATE', __DIR__ . '/templates' );
    }

    /**
     * Init classes
     *
     * @since 3.0.0
     *
     * @return void
     */
    private function init_classes() {
        $this->container['admin_settings']  = new AdminSettings();
        $this->container['vendor_settings'] = new VendorSettings();
        $this->container['chat']            = new Chat();
        $this->container['customer_inbox']  = new CustomerInbox();
        // load VendorInbox class if not already loaded
        if ( ! isset( $this->container['vendor_inbox'] ) ) {
            $this->container['vendor_inbox'] = new VendorInbox();
        }
    }

    /**
     * Add permission on activation
     *
     * @since 1.0
     *
     * @return void
     */
    public function activate() {
        $role = get_role( 'seller' );
        $role->add_cap( 'dokan_view_inbox_menu', true );

        // fix rewrite rules
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        if ( ! isset( $this->container['vendor_inbox'] ) ) {
            $this->container['vendor_inbox'] = new VendorInbox();
        }
        // calling end point hooks, because VendorInbox will prevent this hooks to load if settings is not enabled
        add_filter( 'dokan_query_var_filter', array( $this->container['vendor_inbox'], 'dokan_add_endpoint' ) );
        // flash rewrite rules
        dokan()->rewrite->register_rule();
        flush_rewrite_rules();
    }

    /**
     * Remove permission on deactivation
     *
     * @since 1.0
     *
     * @return void
     */
    public function deactivate() {
        $role = get_role( 'seller' );
        $role->remove_cap( 'dokan_view_inbox_menu' );
    }
}
