<?php

namespace WeDevs\DokanPro\Modules\VendorAnalytics;

class Module {

    /**
     * Constructor for the Dokan_Vendor_Analytics class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        $this->define_constant();
        $this->includes();
        $this->initiate();

        // Activation hook
        add_action( 'dokan_activated_module_vendor_analytics', array( $this, 'activate' ) );
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_analytics_page' ), 15 );
        add_filter( 'dokan_query_var_filter', array( $this, 'add_endpoint' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_analytics_template' ), 16 );
        add_filter( 'dokan_set_template_path', array( $this, 'load_vendor_analytics_templates' ), 11, 3 );
        add_action( 'dokan_analytics_content_area_header', array( $this, 'analytics_header_render' ) );
        add_action( 'dokan_analytics_content', array( $this, 'render_analytics_content' ) );
        add_filter( 'dokan_set_template_path', [ $this, 'load_view_templates' ], 10, 3 );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Define all constant
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constant() {
        define( 'DOKAN_VENDOR_ANALYTICS_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_VENDOR_ANALYTICS_URL', plugins_url( '', __FILE__ ) );
        define( 'DOKAN_VENDOR_ANALYTICS_ASSETS', DOKAN_VENDOR_ANALYTICS_URL . '/assets' );
        define( 'DOKAN_VENDOR_ANALYTICS_VIEWS', DOKAN_VENDOR_ANALYTICS_DIR . '/views' );
        define( 'DOKAN_VENDOR_ANALYTICS_INC_DIR', DOKAN_VENDOR_ANALYTICS_DIR . '/includes' );
        define( 'DOKAN_VENDOR_ANALYTICS_TOOLS_DIR', DOKAN_VENDOR_ANALYTICS_DIR . '/tools' );
    }

    /**
     * Includes all files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        include_once DOKAN_VENDOR_ANALYTICS_TOOLS_DIR . '/src/Dokan/autoload.php';
        require_once DOKAN_VENDOR_ANALYTICS_INC_DIR . '/functions.php';
        require_once DOKAN_VENDOR_ANALYTICS_INC_DIR . '/class-analytics-reports.php';
        require_once DOKAN_VENDOR_ANALYTICS_INC_DIR . '/class-dokan-vendor-analytics-admin-settings.php';
    }

    /**
     * Inistantiate all class
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initiate() {
        new \Dokan_Vendor_Analytics_Admin_Settings();
    }

    /**
     * Add staffs endpoint to the end of Dashboard
     *
     * @param array $query_var
     */
    public function add_endpoint( $query_var ) {
        $query_var['analytics'] = 'analytics';

        return $query_var;
    }

    /**
    * Get plugin path
    *
    * @since 2.8
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Render Analytics Header Template
     *
     * @since 2.4
     *
     * @return void
     */
    public function analytics_header_render() {
        dokan_get_template_part( 'vendor-analytics/header', '', array( 'is_vendor_analytics' => true ) );
    }

    /**
     * Render Analytics Content
     *
     * @return void
     */
    public function render_analytics_content() {
        global $woocommerce;

        $tabs  = dokan_get_analytics_tabs();
        $link    = dokan_get_navigation_url( 'analytics' );
        $current = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        dokan_get_template_part(
            'vendor-analytics/content', '', array(
                'is_vendor_analytics' => true,
                'tabs' => $tabs,
                'link' => $link,
                'current' => $current,
            )
        );
    }

    /**
     * Load vendor analytics views templates
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function load_view_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_vendor_analytics_views'] ) && $args['is_vendor_analytics_views'] ) {
            return $this->plugin_path() . '/views';
        }

        return $template_path;
    }

    /**
     * Load Dokan vendor analytics templates
     *
     * @since 2.8
     *
     * @return void
     */
    public function load_vendor_analytics_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_vendor_analytics'] ) && $args['is_vendor_analytics'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Load tools template
     *
     * @since  1.0
     *
     * @param  array $query_vars
     *
     * @return string
     */
    public function load_analytics_template( $query_vars ) {
        if ( isset( $query_vars['analytics'] ) ) {
            if ( ! current_user_can( 'dokandar' ) ) {
                dokan_get_template_part(
                    'global/dokan-error', '', array(
                        'deleted' => false,
                        'message' => __( 'You have no permission to view this page', 'dokan' ),
                    )
                );
            } else {
                dokan_get_template_part( 'vendor-analytics/analytics', '', array( 'is_vendor_analytics' => true ) );
            }
        }
    }



    /**
     * Add staffs page in seller dashboard
     *
     * @param array $urls
     *
     * @return array $urls
     */
    public function add_analytics_page( $urls ) {
        if ( dokan_is_seller_enabled( get_current_user_id() ) && current_user_can( 'dokandar' ) ) {
            $urls['analytics'] = array(
                'title' => __( 'Analytics', 'dokan' ),
                'icon'  => '<i class="fas fa-chart-area"></i>',
                'url'   => dokan_get_navigation_url( 'analytics' ),
                'pos'   => 181,
            );
        }

        return $urls;
    }

    /**
     * This method will load during module activation
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function activate() {
        // flush rewrite rules after plugin is activate
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
        add_filter( 'dokan_query_var_filter', array( $this, 'add_endpoint' ) );
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Enqueue styles and scripts
     *
     * @since 3.5.3
     *
     * @retun void
     */
    public function enqueue_scripts() {
        if ( dokan_is_seller_dashboard() && false !== get_query_var( 'analytics', false ) ) {
            wp_enqueue_script( 'dokan-flot' );
        }
    }
}
