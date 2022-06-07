<?php

namespace WeDevs\DokanPro\Modules\ColorSchemeCustomizer;

/**
 * Dokan_Apperance class
 *
 * @class Dokan_Apperance The class that holds the entire Dokan_Apperance plugin
 */
class Module {

    public static $plugin_url;
    public static $plugin_path;
    public static $plugin_basename;

    /**
     * Constructor for the Dokan_Apperance class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        self::$plugin_basename = plugin_basename( __FILE__ );
        self::$plugin_url      = plugin_dir_url( self::$plugin_basename );
        self::$plugin_path     = trailingslashit( dirname( __FILE__ ) );

        add_action( 'init', array( $this, 'init_hooks' ) );
    }

    public function init_hooks() {
        add_filter( 'dokan_settings_sections', array( $this, 'render_apperance_section' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'render_apperance_settings' ) );

        add_action( 'wp_head', array( $this, 'load_styles' ) );
        add_action( 'dokan_setup_wizard_styles', array( $this, 'load_styles' ) );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-ac-styles', plugins_url( 'assets/css/style.css', __FILE__ ), false, date( 'Ymd' ) );
    }

    /**
     * Add Settings section in Dokan Settings
     *
     * @since 1.0
     *
     * @param array $sections
     *
     * @return array
     */
    public function render_apperance_section( $sections ) {
        $sections[] = array(
            'id'    => 'dokan_colors',
            'title' => __( 'Colors', 'dokan' ),
            'icon'  => 'dashicons-admin-customizer'
        );


        return $sections;
    }

    /**
     * Add Color pick options on Dokan Settings under Color section
     *
     * @since 1.0
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function render_apperance_settings( $settings_fields ) {
        $settings_fields['dokan_colors'] = array(
            'btn_text'           => array(
                'name'    => 'btn_text',
                'label'   => __( 'Button Text color', 'dokan' ),
                'type'    => 'color',
                'default' => '#ffffff'
            ),
            'btn_primary'        => array(
                'name'    => 'btn_primary',
                'label'   => __( 'Button Background color', 'dokan' ),
                'type'    => 'color',
                'default' => '#f05025'
            ),
            'btn_primary_border' => array(
                'name'    => 'btn_primary_border',
                'label'   => __( 'Button Border color', 'dokan' ),
                'type'    => 'color',
                'default' => '#f05025'
            ),
            'btn_hover_text'     => array(
                'name'    => 'btn_hover_text',
                'label'   => __( 'Button Hover Text color', 'dokan' ),
                'type'    => 'color',
                'default' => '#ffffff'
            ),
            'btn_hover'          => array(
                'name'    => 'btn_hover',
                'label'   => __( 'Button Hover color', 'dokan' ),
                'type'    => 'color',
                'default' => '#dd3b0f'
            ),
            'btn_hover_border'   => array(
                'name'    => 'btn_hover_border',
                'label'   => __( 'Button Hover Border color', 'dokan' ),
                'type'    => 'color',
                'default' => '#ca360e'
            ),
            'dash_nav_text'      => array(
                'name'    => 'dash_nav_text',
                'label'   => __( 'Dashboard Navigation Text', 'dokan' ),
                'type'    => 'color',
                'default' => '#ffffff'
            ),
            'dash_active_link'   => array(
                'name'    => 'dash_active_link',
                'label'   => __( 'Dashboard Navigation Active Menu', 'dokan' ),
                'type'    => 'color',
                'default' => '#f05025'
            ),
            'dash_nav_bg'        => array(
                'name'    => 'dash_nav_bg',
                'label'   => __( 'Dashboard Navigation Background', 'dokan' ),
                'type'    => 'color',
                'default' => '#242424'
            ),
            'dash_nav_border'    => array(
                'name'    => 'dash_nav_border',
                'label'   => __( 'Dashboard Menu Border', 'dokan' ),
                'type'    => 'color',
                'default' => '#454545'
            ),
        );

        return $settings_fields;
    }

    /**
     * Render styles to override default styles
     *
     * @since 1.0
     *
     * return void
     */
    public function load_styles() {
        $page = ( isset( $_GET['page'] ) && $_GET['page'] == 'dokan-seller-setup' ) ? 'seller-setup' : '';

        if ( ( ! dokan_is_seller_dashboard() && get_query_var( 'post_type' ) !== 'product' ) && $page !== 'seller-setup' && ! dokan_is_store_listing() && ! is_account_page() ) {
            return;
        }

        $btn_text   = dokan_get_option( 'btn_text', 'dokan_colors', '#ffffff' );
        $btn_bg     = dokan_get_option( 'btn_primary', 'dokan_colors', '#f05025' );
        $btn_border = dokan_get_option( 'btn_primary_border', 'dokan_colors', '#f05025' );

        $btn_h_text   = dokan_get_option( 'btn_hover_text', 'dokan_colors', '#ffffff' );
        $btn_h_bg     = dokan_get_option( 'btn_hover', 'dokan_colors', '#dd3b0f' );
        $btn_h_border = dokan_get_option( 'btn_hover_border', 'dokan_colors', '#ca360e' );

        $dash_active_menu = dokan_get_option( 'dash_active_link', 'dokan_colors', '#f05025' );
        $dash_nav_text    = dokan_get_option( 'dash_nav_text', 'dokan_colors', '#ffffff' );
        $dash_nav_bg      = dokan_get_option( 'dash_nav_bg', 'dokan_colors', '#242424' );
        $dash_nav_border  = dokan_get_option( 'dash_nav_border', 'dokan_colors', '#454545' );
        ?>
        <style>
            input[type="submit"].dokan-btn-theme, a.dokan-btn-theme, .dokan-btn-theme {
                color: <?php echo $btn_text ?> !important;
                background-color: <?php echo $btn_bg ?> !important;
                border-color: <?php echo $btn_border ?> !important;
            }
            input[type="submit"].dokan-btn-theme:hover,
            a.dokan-btn-theme:hover, .dokan-btn-theme:hover,
            input[type="submit"].dokan-btn-theme:focus,
            a.dokan-btn-theme:focus, .dokan-btn-theme:focus,
            input[type="submit"].dokan-btn-theme:active,
            a.dokan-btn-theme:active, .dokan-btn-theme:active,
            input[type="submit"].dokan-btn-theme.active, a.dokan-btn-theme.active,
            .dokan-btn-theme.active,
            .open .dropdown-toggleinput[type="submit"].dokan-btn-theme,
            .open .dropdown-togglea.dokan-btn-theme,
            .open .dropdown-toggle.dokan-btn-theme{
                color: <?php echo $btn_h_text ?> !important;
                background-color: <?php echo $btn_h_bg ?> !important;
                border-color: <?php echo $btn_h_border ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu{
                background-color : <?php echo $dash_nav_bg ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li a{
                color : <?php echo $dash_nav_text ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li:hover,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.dokan-common-links a:hover{
                background-color : <?php echo $dash_active_menu ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li a,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.dokan-common-links a{
                border-color : <?php echo $dash_nav_border ?> !important;
            }
        </style>

        <?php
    }
}
