<?php
namespace WeDevs\DokanPro\Modules\OrderMinMax;

defined( 'ABSPATH' ) || exit;

/**
 * Class for Request A Quote module integration.
 *
 * @since 3.5.0
 */
class Module {

    /**
     * Class constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_templates' ], 20, 3 );
        add_action( 'dokan_enqueue_admin_scripts', [ $this, 'enqueue_script' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
        add_filter( 'dokan_localized_args', [ $this, 'conditional_localized_args' ] );
    }

    /**
     * Init the module.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init() {
        $this->define_constants();
        $this->initiate();
    }

    /**
     * Module constants
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'ORDER_MIN_MAX_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
        define( 'ORDER_MIN_MAX_FILE', __FILE__ );
        define( 'ORDER_MIN_MAX_PATH', dirname( ORDER_MIN_MAX_FILE ) );
        define( 'ORDER_MIN_MAX_INCLUDES', ORDER_MIN_MAX_PATH . '/includes' );
        define( 'ORDER_MIN_MAX_URL', plugins_url( '', ORDER_MIN_MAX_FILE ) );
        define( 'ORDER_MIN_MAX_ASSETS', ORDER_MIN_MAX_URL . '/assets' );
        define( 'ORDER_MIN_MAX_TEMPLATE_PATH', ORDER_MIN_MAX_PATH . '/templates/' );
    }

    /**
     * Initiate all classes
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function initiate() {
        if ( is_admin() ) {
            new Admin();
        }
        new Vendor();
        new FrontEnd();
    }

    /**
     * Enqueue admin script
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function enqueue_script() {
        $version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;
        wp_enqueue_script(
            'dokan-request-a-quote-admin',
            ORDER_MIN_MAX_ASSETS . '/js/order-min-max.js',
            [ 'jquery' ],
            $version,
            true
        );
    }

    /**
     * Set template path for Request Quote
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function load_templates( $template_path, $template, $args ) {
        if ( ( isset( $args['order_min_max_template'] ) && $args['order_min_max_template'] ) ) {
            return ORDER_MIN_MAX_TEMPLATE_PATH;
        }
        return $template_path;
    }


    /**
     * Filter 'dokan' localize script's arguments
     *
     * @since 2.5.3
     *
     * @param array $default_args
     *
     * @return array $default_args
     */
    public function conditional_localized_args( $default_args ) {
        $custom_args = [
            'dokan_i18n_negative_value_not_approved' => __( 'Value can not be null or negative', 'dokan' ),
            'dokan_i18n_value_set_successfully'      => __( 'Value successfully set', 'dokan' ),
            'dokan_i18n_deactivated_successfully'    => __( 'Deactivated successfully.', 'dokan' ),
        ];

        return array_merge( $default_args, $custom_args );
    }
}
