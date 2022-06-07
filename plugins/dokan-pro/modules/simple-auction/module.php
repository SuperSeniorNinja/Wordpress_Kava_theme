<?php

namespace WeDevs\DokanPro\Modules\Auction;

use WP_User;

/**
 * Dokan_Auction class
 *
 * @class Dokan_Auction The class that holds the entire Dokan_Auction plugin
 */
class Module {

    /**
     * Module version
     *
     * @since 3.2.2
     *
     * @var string
     */
    public $version = null;

    /**
     * Constructor for the Dokan_Auction class
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
        $this->version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        define( 'DOKAN_AUCTION_DIR', dirname( __FILE__ ) );

        include_once DOKAN_AUCTION_DIR . '/includes/DependencyNotice.php';

        $dependency = new DependencyNotice();

        if ( $dependency->is_missing_dependency() ) {
            return;
        }

        $this->includes();

        // Hooking all caps
        add_filter( 'dokan_get_all_cap', array( $this, 'add_capabilities' ) );
        add_filter( 'dokan_get_all_cap_labels', array( $this, 'add_caps_labels' ) );

        // insert auction porduct type
        add_filter( 'dokan_get_product_types', array( $this, 'insert_auction_product_type' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'dokan_seller_meta_fields', array( $this, 'add_admin_user_options' ) );
        add_action( 'dokan_process_seller_meta_fields', array( $this, 'save_admin_user_option' ) );
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_auction_dashboad_menu' ), 20, 1 );
        add_filter( 'dokan_settings_selling_option_vendor_capability', array( $this, 'add_auction_dokan_settings' ) );
        add_filter( 'dokan_query_var_filter', array( $this, 'add_dokan_auction_endpoint' ) );
        add_filter( 'dokan_set_template_path', array( $this, 'load_auction_templates' ), 10, 3 );
        add_action( 'dokan_load_custom_template', array( $this, 'load_dokan_auction_template' ), 10, 1 );
        add_action( 'dokan_auction_before_general_options', [ $this, 'load_downloadable_virtual_option' ] );
        add_action( 'user_register', array( $this, 'dokan_admin_user_register_enable_auction' ), 16 );
        add_action( 'dokan_product_listing_exclude_type', array( $this, 'product_listing_exclude_auction' ), 11 );

        add_filter( 'dokan_dashboard_nav_active', array( $this, 'dashboard_auction_active_menu' ) );
        // dokan simple auciton email
        add_filter( 'woocommerce_email_classes', array( $this, 'load_auction_email_class' ) );
        add_filter( 'dokan_email_actions', array( $this, 'register_auction_email_action' ) );

        // send bid email to admin and vendor
        add_filter( 'woocommerce_email_recipient_bid_note', array( $this, 'send_bid_email' ), 99, 2 );

        add_filter( 'dokan_localized_args', array( $this, 'set_localized_args' ) );

        add_action( 'dokan_activated_module_auction', array( $this, 'activate' ) );

        add_filter( 'dokan_get_edit_product_url', [ $this, 'modify_edit_product_url' ], 10, 2 );
        add_filter( 'dokan_email_list', array( $this, 'set_email_template_directory' ) );

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Register activation hook
     *
     * @since  1.5.2
     *
     * @return void
     */
    public function activate() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
            // @codingStandardsIgnoreLine
            $wp_roles = new \WP_Roles();
        }

        $all_cap = array(
            'dokan_view_auction_menu',
            'dokan_add_auction_product',
            'dokan_edit_auction_product',
            'dokan_delete_auction_product',
        );

        foreach ( $all_cap as $key => $cap ) {
            $wp_roles->add_cap( 'seller', $cap );
            $wp_roles->add_cap( 'administrator', $cap );
            $wp_roles->add_cap( 'shop_manager', $cap );
        }

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
        add_filter( 'dokan_query_var_filter', array( $this, 'add_dokan_auction_endpoint' ) );
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Add capabilities
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_capabilities( $capabilities ) {
        $capabilities['menu']['dokan_view_auction_menu'] = __( 'View auction menu', 'dokan' );

        $capabilities['auction'] = array(
            'dokan_add_auction_product'    => __( 'Add auction product', 'dokan' ),
            'dokan_edit_auction_product'   => __( 'Edit auction product', 'dokan' ),
            'dokan_delete_auction_product' => __( 'Delete auction product', 'dokan' ),
        );

        return $capabilities;
    }

    /**
     * Add caps labels
     *
     * @since 3.0.0
     *
     * @param string $caps
     *
     * @return array
     */
    public function add_caps_labels( $caps ) {
        $caps['auction'] = __( 'Auction', 'dokan' );

        return $caps;
    }

    /**
     * Insert auction product type
     *
     * @param  array $types
     *
     * @return array
     */
    public function insert_auction_product_type( $types ) {
        $types['auction'] = __( 'Auction Product', 'dokan' );

        return $types;
    }

    /**
    * Include files
    *
    * @since 1.5.0
    *
    * @return void
    **/
    public function includes() {
        require_once dirname( __FILE__ ) . '/classes/class-auction.php';
        require_once dirname( __FILE__ ) . '/includes/dokan-auction-functions.php';

        // Init Cache for Auction module
        require_once dirname( __FILE__ ) . '/includes/DokanAuctionCache.php';
        new \DokanAuctionCache();
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
        global $wp;

        wp_enqueue_style( 'dokan-auction-styles', plugins_url( 'assets/css/dokan-auction-style.css', __FILE__ ), false, $this->version );

        if ( isset( $wp->query_vars['new-auction-product'] ) || isset( $wp->query_vars['auction-activity'] ) ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'dokan-form-validate' );
            wp_enqueue_script( 'jquery-ui' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'dokan-auctiondasd-timepicker', plugins_url( 'assets/js/jquery-ui-timepicker.js', __FILE__ ), array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'auction-product', plugins_url( 'assets/js/auction-product.js', __FILE__ ), [ 'jquery', 'dokan-script', 'dokan-pro-script' ], $this->version, true );
            wp_enqueue_media();
        }

        // @codingStandardsIgnoreLine
        if ( isset( $wp->query_vars['auction'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'dokan-auctiondasd-timepicker', plugins_url( 'assets/js/jquery-ui-timepicker.js', __FILE__ ), array( 'jquery' ), $this->version, true );
            wp_enqueue_media();
        }
    }

    /**
    * Get plugin path
    *
    * @since 1.5.1
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Show auction action in user profile
     *
     * @since 1.0.0
     *
     * @param object $user
     */
    public function add_admin_user_options( $user ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! user_can( $user, 'dokandar' ) ) {
            return;
        }

        $auction = get_user_meta( $user->ID, 'dokan_disable_auction', true );
        ?>
        <tr>
            <th><?php esc_html_e( 'Auction', 'dokan' ); ?></th>
            <td>
                <label for="dokan_disable_auction">
                    <input type="hidden" name="dokan_disable_auction" value="no">
                    <input name="dokan_disable_auction" type="checkbox" id="dokan_disable_auction" value="yes" <?php checked( $auction, 'yes' ); ?> />
                    <?php esc_html_e( 'Disable Auction', 'dokan' ); ?>
                </label>

                <p class="description"><?php esc_html_e( 'Disable auction capability for this vendor', 'dokan' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save admin user profile options
     *
     * @since  1.0.0
     *
     * @param  integer $user_id
     *
     * @return void
     */
    public function save_admin_user_option( $user_id ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // @codingStandardsIgnoreLine
        if ( ! isset( $_POST['dokan_enable_selling'] ) ) {
            return;
        }

        // @codingStandardsIgnoreLine
        $selling = wc_clean( wp_unslash( $_POST['dokan_disable_auction'] ) );
        update_user_meta( $user_id, 'dokan_disable_auction', $selling );
    }

    /**
     * Add auction settings in dokan settings
     *
     * @since 1.0.0
     *
     * @param array $settings_fields
     */
    public function add_auction_dokan_settings( $settings_fields ) {
        $settings_fields['new_seller_enable_auction'] = array(
            'name'    => 'new_seller_enable_auction',
            'label'   => __( 'New vendor Enable Auction', 'dokan' ),
            'desc'    => __( 'Make auction status enable for new registred vendor', 'dokan' ),
            'type'    => 'checkbox',
            'default' => 'on',
        );

        return $settings_fields;
    }

    /**
     * Show dashboard auction menu
     *
     * @since 1.0.0
     *
     * @param array $urls
     */
    public function add_auction_dashboad_menu( $urls ) {
        if ( dokan_is_seller_enabled( get_current_user_id() ) && ! dokan_is_seller_auction_disabled( get_current_user_id() ) ) {
            $urls['auction'] = array(
                'title' => __( 'Auction', 'dokan' ),
                'icon'  => '<i class="fas fa-gavel"></i>',
                'url'   => dokan_get_navigation_url( 'auction' ),
                'pos'   => 185,
                'permission' => 'dokan_view_auction_menu',
            );
        }

        return $urls;
    }

    /**
     * Register endpoint for auction
     *
     * @since 1.0.0
     *
     * @param array $query_var
     */
    public function add_dokan_auction_endpoint( $query_var ) {
        if ( ! dokan_is_seller_auction_disabled( get_current_user_id() ) ) {
            $query_var[] = 'auction';
            $query_var[] = 'new-auction-product';
            $query_var[] = 'auction-activity';
        }

        return $query_var;
    }

    /**
    * Load dokan pro templates
    *
    * @since 1.5.1
    *
    * @return void
    **/
    public function load_auction_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_auction'] ) && $args['is_auction'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Render auction dashboard template
     *
     * @since  1.0.0
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_dokan_auction_template( $query_vars ) {
        if ( isset( $query_vars['auction'] ) ) {
            if ( ! current_user_can( 'dokan_view_auction_menu' ) ) {
                dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'You have no permission to view this auction page', 'dokan' ) ) );
            } else {
                dokan_get_template_part( 'auction/template-auction', '', array( 'is_auction' => true ) );
            }
            return;
        }

        if ( isset( $query_vars['new-auction-product'] ) ) {
            if ( ! current_user_can( 'dokan_add_auction_product' ) ) {
                dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'You have no permission to view this page', 'dokan' ) ) );
            } else {
                dokan_get_template_part( 'auction/new-auction-product', '', array( 'is_auction' => true ) );
            }
            return;
        }

        if ( isset( $query_vars['auction-activity'] ) ) {
            if ( ! current_user_can( 'dokan_add_auction_product' ) ) {
                dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'You have no permission to view this page', 'dokan' ) ) );
            } else {
                // @codingStandardsIgnoreStart
                $date_from     = isset( $_GET['_auction_dates_from'] ) ? wc_clean( wp_unslash( $_GET['_auction_dates_from'] ) ) : '';
                $date_to       = isset( $_GET['_auction_dates_to'] ) ? wc_clean( wp_unslash( $_GET['_auction_dates_to'] ) ) : '';
                $search_string = isset( $_GET['auction_activity_search'] ) ? wc_clean( wp_unslash( $_GET['auction_activity_search'] ) ) : '';
                // @codingStandardsIgnoreEnd

                dokan_get_template_part( 'auction/auction-activity', '', [
                    'is_auction'    => true,
                    'date_from'     => $date_from,
                    'date_to'       => $date_to,
                    'search_string' => $search_string,
                ] );
            }
        }
    }

    /**
     * Disable selling capability by default once a seller is registered
     *
     * @since 1.0.0
     *
     * @param int $user_id
     */
    public function dokan_admin_user_register_enable_auction( $user_id ) {
        $user = new WP_User( $user_id );
        $role = reset( $user->roles );

        if ( 'seller' === (string) $role ) {
            if ( 'off' === (string) dokan_get_option( 'new_seller_enable_auction', 'dokan_selling' ) ) {
                update_user_meta( $user_id, 'dokan_disable_auction', 'yes' );
            } else {
                update_user_meta( $user_id, 'dokan_disable_auction', 'no' );
            }
        }
    }

    /**
    * Exclude auction product from product listing
    *
    * @since 1.5.1
    *
    * @return void
    **/
    public function product_listing_exclude_auction( $product_type ) {
        $product_type[] = 'auction';
        return $product_type;
    }

    /**
     * Set auction active menu in dokan dashboard
     *
     * @since  1.0.0
     *
     * @param  string $active_menu
     *
     * @return string
     */
    public function dashboard_auction_active_menu( $active_menu ) {
        if ( 'new-auction-product' === $active_menu || 'auction-activity' === $active_menu ) {
            $active_menu = 'auction';
        }
        return $active_menu;
    }

    /**
     * Load auction email class
     *
     * @since  2.7.1
     *
     * @param  array $wc_emails
     *
     * @return array
     */
    public function load_auction_email_class( $wc_emails ) {
        $wc_emails['Dokan_Auction_Email'] = include DOKAN_AUCTION_DIR . '/includes/emails/class-dokan-auction-email.php';

        return $wc_emails;
    }

    /**
     * Register auction email action hook
     *
     * @since  2.7.1
     *
     * @param  array $actions
     *
     * @return array
     */
    public function register_auction_email_action( $actions ) {
        $actions[] = 'dokan_new_auction_product_added';

        return $actions;
    }

    /**
     * Send bid email to seller and amdin
     *
     * @param $recipient
     *
     * @param $object
     *
     * @since 2.8.2
     *
     * @return string
     */
    public function send_bid_email( $recipient, $object ) {
        if ( ! $object ) {
            return;
        }

        $product_id = $object->get_id();

        if ( empty( $product_id ) ) {
            return $recipient;
        }

        $vendor_id    = get_post_field( 'post_author', $product_id );
        $vendor_email = dokan()->vendor->get( $vendor_id )->get_email();

        return $recipient . ',' . $vendor_email;
    }

    /**
     * Set localized args
     *
     * @param array $args
     *
     * @since DOKAN_PLUGIN_SINCE
     *
     * @return array
     */
    public function set_localized_args( $args ) {
        $auction_args = [
            'datepicker' => [
                'now'         => __( 'Now', 'dokan' ),
                'done'        => __( 'Done', 'dokan' ),
                'time'        => __( 'Time', 'dokan' ),
                'hour'        => __( 'Hour', 'dokan' ),
                'minute'      => __( 'Minute', 'dokan' ),
                'second'      => __( 'Second', 'dokan' ),
                'time-zone'   => __( 'Time Zone', 'dokan' ),
                'choose-time' => __( 'Choose Time', 'dokan' ),
            ],
        ];

        return array_merge( $args, $auction_args );
    }

    /**
     * @since 3.1.4
     * @param $url
     * @param $product
     *
     * @return mixed|string
     */
    public function modify_edit_product_url( $url, $product ) {
        if ( $product->get_type() === 'auction' ) {
            $url = add_query_arg(
                [
                    'product_id' => $product->get_id(),
                    'action'     => 'edit',
                ],
                dokan_get_navigation_url( 'auction' )
            );
        }
        return $url;
    }


    /**
     * Set Proper template directory.
     *
     * @param array $template_array
     *
     * @return array
     */
    public function set_email_template_directory( $template_array ) {
        array_push( $template_array, 'auction-product-added.php' );
        return $template_array;
    }

    /**
     * Load downloadable and virtual option on product edit page
     *
     * @param int $auction_id Auction Product ID
     *
     * @return void
     */
    public function load_downloadable_virtual_option( $auction_id ) {
        global $post;
        $is_downloadable    = 'yes' === get_post_meta( $auction_id, '_downloadable', true );
        $is_virtual         = 'yes' === get_post_meta( $auction_id, '_virtual', true );
        $digital_mode       = dokan_get_option( 'global_digital_mode', 'dokan_general', 'sell_both' );

        echo '<div class="product-edit-new-container">';
            dokan_get_template_part(
                'products/download-virtual',
                '',
                [
                    'post_id'         => $auction_id,
                    'post'            => $post,
                    'is_downloadable' => $is_downloadable,
                    'is_virtual'      => $is_virtual,
                    'digital_mode'    => $digital_mode,
                    'class'           => '',
                ]
            );
        echo '</div>';
    }
}
