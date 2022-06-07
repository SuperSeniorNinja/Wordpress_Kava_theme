<?php

namespace WeDevs\DokanPro\Modules\Booking;

use WeDevs\Dokan\Cache;
use WeDevs\DokanPro\Modules\Booking\BookingCache;

/**
 * Dokan_WC_Booking class
 *
 * @class Dokan_WC_Booking The class that holds the entire Dokan_WC_Booking plugin
 */
class Module {

    /**
     * Module version
     *
     * @since 3.1.3
     *
     * @var string
     */
    public $version = null;

    /**
     * Constructor for the Dokan_WC_Booking class
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

        // Define all constant
        define( 'DOKAN_WC_BOOKING_PLUGIN_VERSION', DOKAN_PRO_PLUGIN_VERSION );
        define( 'DOKAN_WC_BOOKING_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_WC_BOOKING_PLUGIN_ASSET', plugins_url( 'assets', __FILE__ ) );
        define( 'DOKAN_WC_BOOKING_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
        define( 'DOKAN_WC_BOOKING_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

        add_action( 'dokan_activated_module_booking', array( $this, 'activate' ) );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );

        include_once DOKAN_WC_BOOKING_DIR . '/includes/DependencyNotice.php';

        $dependency = new DependencyNotice();

        if ( $dependency->is_missing_dependency() ) {
            return;
        }

        add_filter( 'dokan_get_all_cap', array( $this, 'add_capabilities' ) );
        add_filter( 'dokan_get_all_cap_labels', array( $this, 'add_caps_labels' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'init', array( $this, 'init_hooks' ) );

        // insert booking order into dokan_order_table if it's created by admin
        if ( is_admin() ) {
            add_action( 'woocommerce_bookings_create_booking_page_add_order_item', 'dokan_sync_insert_order' );
        }

        add_action( 'dokan_new_product_added', array( $this, 'save_booking_data' ), 10 );
        add_action( 'dokan_product_updated', array( $this, 'save_booking_data' ), 10 );

        // save per product commission for bookable product
        add_action( 'woocommerce_process_product_meta_booking', array( \WeDevs\DokanPro\Products::class, 'save_per_product_commission_options' ), 20 );

        //ajax
        add_action( 'wp_ajax_add_new_resource', array( $this, 'add_new_resource' ) );
        add_action( 'wp_ajax_nopriv_add_new_resource', array( $this, 'add_new_resource' ) );
        add_action( 'wp_ajax_delete_resource', array( $this, 'delete_resource' ) );
        add_action( 'wp_ajax_nopriv_delete_resource', array( $this, 'delete_resource' ) );
        add_action( 'wp_ajax_dokan_wc_booking_change_status', array( $this, 'change_booking_status' ) );
        add_action( 'wp_ajax_noprivdokan_wc_booking_change_status', array( $this, 'change_booking_status' ) );

        add_action( 'template_redirect', array( $this, 'update_resource_data' ) );

        //booking modification
        add_action( 'woocommerce_new_booking', array( $this, 'add_seller_id_meta' ) );
        add_action( 'shutdown', array( $this, 'add_seller_manage_cap' ) );
        add_action( 'wp_ajax_dokan-wc-booking-confirm', array( $this, 'mark_booking_confirmed' ) );

        // booking person type delete
        add_action( 'wp_ajax_woocommerce_remove_bookable_person', array( $this, 'dokan_remove_bookable_person' ) );

        // booking page filters
        add_filter( 'dokan_booking_menu', array( $this, 'dokan_get_bookings_menu' ) );
        add_filter( 'dokan_booking_menu_title', array( $this, 'dokan_get_bookings_menu_title' ) );

        add_filter( 'dokan_set_template_path', array( $this, 'load_booking_templates' ), 10, 3 );

        // insert bookable porduct type
        add_filter( 'dokan_get_product_types', array( $this, 'insert_bookable_product_type' ) );
        add_filter( 'dokan_get_coupon_types', [ $this, 'add_booking_discount' ] );
        add_filter( 'dokan_get_edit_product_url', [ $this, 'modify_edit_product_url' ], 10, 2 );

        // Clear addon notices on manual booking creation
        add_action( 'template_redirect', [ $this, 'clear_addons_validation_notices' ], 10 );

        // Init accommodation booking
        $this->init_accommodation_booking();
    }

    /**
    * Get plugin path
    *
    * @since 2.0
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
    * Load Dokan Booking templates
    *
    * @since 2.0
    *
    * @return void
    **/
    public function load_booking_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_booking'] ) && $args['is_booking'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Insert bookable product type
     *
     * @param  array $types
     *
     * @return array
     */
    public function insert_bookable_product_type( $types ) {
        $types['booking'] = __( 'Bookable Product', 'dokan' );

        return $types;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
            // @codingStandardsIgnoreLine
            $wp_roles = new \WP_Roles();
        }

        $all_cap = array(
            'dokan_view_booking_menu',
            'dokan_add_booking_product',
            'dokan_edit_booking_product',
            'dokan_delete_booking_product',
            'dokan_manage_booking_products',
            'dokan_manage_booking_calendar',
            'dokan_manage_bookings',
            'dokan_manage_booking_resource',
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
        add_filter( 'dokan_query_var_filter', array( $this, 'register_booking_queryvar' ) );
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
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

        if ( ! is_admin() && isset( $wp->query_vars['booking'] ) ) {
            global $post, $wp_scripts;

            /**
             * All styles goes here
             */
            // @codingStandardsIgnoreLine
            wp_enqueue_style( 'dokan_wc_booking-styles', plugins_url( 'assets/css/style.css', __FILE__ ), false, date( 'Ymd' ) );
            /**
             * All scripts goes here
             */
            wp_enqueue_script( 'dokan_wc_booking-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), $this->version, true );

            // Accommodation scripts
            $this->enqueue_accommodation_scripts();

            $jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_register_script( 'wc_bookings_writepanel_js', DOKAN_WC_BOOKING_PLUGIN_ASSET . '/js/writepanel.min.js', array( 'jquery', 'jquery-ui-datepicker' ), DOKAN_WC_BOOKING_PLUGIN_VERSION, true );
            wp_register_script( 'wc_bookings_settings_js', WC_BOOKINGS_PLUGIN_URL . '/assets/js/settings' . $suffix . '.js', array( 'jquery' ), WC_BOOKINGS_VERSION, true );
            wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
            $post_id = isset( $post->ID ) ? $post->ID : '';

            if ( dokan_is_seller_dashboard() ) {
                // @codingStandardsIgnoreLine
                if ( isset( $_GET['product_id'] ) ) {
                    // @codingStandardsIgnoreLine
                    $post_id = $_GET['product_id'];
                } else {
                    $post_id = '';
                }
            }

            $params = array(
                'i18n_remove_person'  => esc_js( __( 'Are you sure you want to remove this person type?', 'dokan' ) ),
                'nonce_delete_person' => wp_create_nonce( 'delete-bookable-person' ),
                'nonce_add_person'    => wp_create_nonce( 'add-bookable-person' ),
                'nonce_unlink_person' => wp_create_nonce( 'unlink-bookable-person' ),
                'i18n_remove_resource'  => esc_js( __( 'Are you sure you want to remove this resource?', 'dokan' ) ),
                'nonce_delete_resource' => wp_create_nonce( 'delete-bookable-resource' ),
                'nonce_add_resource'    => wp_create_nonce( 'add-bookable-resource' ),
                'i18n_minutes' => esc_js( __( 'minutes', 'dokan' ) ),
                'i18n_days'    => esc_js( __( 'days', 'dokan' ) ),
                'i18n_new_resource_name' => esc_js( __( 'Enter a name for the new resource', 'dokan' ) ),
                'post'                   => $post_id,
                'plugin_url'             => WC()->plugin_url(),
                'ajax_url'               => admin_url( 'admin-ajax.php' ),
                'calendar_image'         => WC()->plugin_url() . '/assets/images/calendar.png',
            );

            wp_localize_script( 'wc_bookings_writepanel_js', 'wc_bookings_writepanel_js_params', $params );

            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'wc_bookings_writepanel_js' );
            wp_enqueue_script( 'jquery-tiptip' );

            wp_enqueue_style( 'wc_bookings_admin_styles', DOKAN_WC_BOOKING_PLUGIN_ASSET . '/css/admin.css', null, DOKAN_WC_BOOKING_PLUGIN_VERSION );
            wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', null, WC_VERSION );
            // @codingStandardsIgnoreLine
            wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );

            add_filter( 'dokan_dashboard_nav_active', array( $this, 'set_booking_menu_as_active' ) );

            wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
            wp_localize_script(
                'wc-enhanced-select', 'wc_enhanced_select_params', array(
                    'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'dokan' ),
                    'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'dokan' ),
                    'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'dokan' ),
                    'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'dokan' ),
                    'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'dokan' ),
                    'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'dokan' ),
                    'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'dokan' ),
                    'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'dokan' ),
                    'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'dokan' ),
                    'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'dokan' ),
                    'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'dokan' ),
                    'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'dokan' ),
                    'ajax_url'                  => admin_url( 'admin-ajax.php' ),
                    'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
                )
            );

            wp_enqueue_script( 'wc-enhanced-select' );
        }
    }

    /**
     * Loaded all dependency hooks
     *
     * @return void
     */
    public function init_hooks() {
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_booking_page' ), 11, 1 );
        add_action( 'dokan_load_custom_template', array( $this, 'load_template_from_plugin' ) );
        add_filter( 'dokan_query_var_filter', array( $this, 'register_booking_queryvar' ) );
        add_filter( 'dokan_add_new_product_redirect', array( $this, 'set_redirect_url' ), 10, 2 );
        add_filter( 'dokan_product_listing_exclude_type', array( $this, 'exclude_booking_type_from_product_listing' ) );
        add_action( 'delete_post', array( $this, 'handle_deleted_bookable_product' ), 10, 1 );

        // Init Cache
        include_once DOKAN_WC_BOOKING_DIR . '/includes/BookingCache.php';
        new BookingCache();

        if ( ! class_exists( 'WC_Product_Booking' ) && defined( 'WC_BOOKINGS_MAIN_FILE' ) ) {
            $wcb_path = preg_replace( '(woocommmerce-bookings.php)', '', WC_BOOKINGS_MAIN_FILE );
            include_once $wcb_path . 'includes/class-wc-product-booking.php';
        }

        if ( ! is_admin() ) {
            include_once DOKAN_WC_BOOKING_DIR . '/includes/class-wc-booking-calendar.php';
            include_once DOKAN_WC_BOOKING_DIR . '/includes/class-dokan-wc-booking-helper.php';
            include_once DOKAN_WC_BOOKING_DIR . '/includes/class-dokan-wc-booking-cost-calculation.php';
        }

        //override emails
        add_filter( 'woocommerce_email_classes', array( $this, 'load_dokan_booking_cancelled_emails' ), 13 );
        add_filter( 'woocommerce_email_actions', array( $this, 'register_dokan_booking_cancelled_actions' ) );

        add_filter( 'woocommerce_email_classes', array( $this, 'load_dokan_booking_new_emails' ), 14 );
        add_filter( 'woocommerce_email_actions', array( $this, 'register_dokan_booking_new_actions' ) );
        add_filter( 'dokan_email_list', array( $this, 'set_email_template_directory' ) );
    }
    public function load_dokan_booking_cancelled_emails( $wc_emails ) {
        $wc_emails['Dokan_Email_Booking_Cancelled'] = include DOKAN_WC_BOOKING_DIR . '/includes/emails/class-dokan-booking-email-cancelled.php';

        return $wc_emails;
    }
    public function register_dokan_booking_cancelled_actions( $actions ) {
        $actions[] = 'woocommerce_bookings_cancelled_booking';

        return $actions;
    }

    public function load_dokan_booking_new_emails( $wc_emails ) {
        $wc_emails['Dokan_Email_Booking_New'] = include DOKAN_WC_BOOKING_DIR . '/includes/emails/class-dokan-booking-email-new.php';

        return $wc_emails;
    }

    public function register_dokan_booking_new_actions( $actions ) {
        $actions[] = 'woocommerce_admin_new_booking_notification';

        return $actions;
    }
    /**
     * Filter template for New Booking Email template path
     *
     * @since 1.1.2
     *
     * @param array $emails
     *
     * @return $emails
     */
    public function setup_emails( $emails ) {
        if ( ! isset( $emails['WC_Email_New_Booking'] ) ) {
            return;
        }

        $email = $emails['WC_Email_New_Booking'];

        $email->title       = __( 'Dokan New Booking', 'dokan' );
        $email->description = __( 'New booking emails are sent to the admin when a new booking is created and paid. This email is also received when a Pending confirmation booking is created.', 'dokan' );

        $email->template_base = DOKAN_WC_BOOKING_TEMPLATE_PATH;
        $email->recipient     = 'vendor@ofthe.product';

        return $emails;
    }

    /**
     * Filter Email recipient for New booking orders
     *
     * @since 1.1.2
     *
     * @param string $recipient
     *
     * @param WC_Booking $booking
     *
     * @return $recipient
     */
    public function set_seller_as_email_recipient( $recipient, $booking ) {
        if ( ! $booking ) {
            return $recipient;
        }

        $seller     = get_post_field( 'post_author', $booking->product_id );
        $sellerdata = get_userdata( $seller );

        return apply_filters( 'dokan_booking_new_email_recipient', $sellerdata->user_email, $booking );
    }

    /**
     * Add menu on seller dashboard
     * @since 1.0
     * @param array $urls
     * @return array $urls
     */
    public function add_booking_page( $urls ) {
        if ( ! current_user_can( 'dokan_view_booking_menu' ) ) {
            return $urls;
        }

        $urls['booking'] = array(
            'title' => __( 'Booking', 'dokan' ),
            'icon'  => '<i class="far fa-calendar-alt"></i>',
            'url'   => dokan_get_navigation_url( 'booking' ),
            'pos'   => 180,
        );

        return $urls;
    }

    /**
     * Register page templates
     *
     * @since 1.0
     *
     * @param array $query_vars
     *
     * @return array $query_vars
     */
    public function load_template_from_plugin( $query_vars ) {
        if ( isset( $query_vars['booking'] ) ) {
            if ( ! current_user_can( 'dokan_view_booking_menu' ) ) {
                dokan_get_template_part(
                    'global/dokan-error', '', array(
                        'deleted' => false,
                        'message' => __( 'You have no permission to view this booking page', 'dokan' ),
                    )
                );
            } else {
                dokan_get_template_part( 'booking/booking', '', array( 'is_booking' => true ) );
            }
            return;
        }
    }

    /**
     * Register dokan query vars
     *
     * @since 1.0
     *
     * @param array $vars
     *
     * @return array new $vars
     */
    public function register_booking_queryvar( $vars ) {
        $vars[] = 'booking';
        return $vars;
    }

    /**
     * Save Booking meta data
     *
     * @since 1.0
     *
     * @global Array $wpdb
     *
     * @param int $post_id
     *
     * @return void
     */
    public function save_booking_data( $post_id ) {
        global $wpdb;
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        $product_type = empty( $post_data['product_type'] ) ? 'simple' : sanitize_title( stripslashes( $post_data['product_type'] ) );

        if ( 'booking' !== $product_type ) {
            return;
        }

        $product = new \WC_Product_Booking( $post_id );

        if ( ! is_a( $product, 'WC_Product_Booking' ) ) {
            return;
        }

        // Save shipping class
        $product_shipping_class = isset( $post_data['product_shipping_class'] ) && $post_data['product_shipping_class'] > 0 ? absint( $post_data['product_shipping_class'] ) : '';
        wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class' );

        $resources = $this->get_posted_resources();
        $posted_props = array(
            'apply_adjacent_buffer'      => isset( $post_data['_wc_booking_apply_adjacent_buffer'] ),
            'availability'               => $this->get_posted_availability(),
            'block_cost'                 => wc_clean( $post_data['_wc_booking_block_cost'] ),
            'buffer_period'              => wc_clean( $post_data['_wc_booking_buffer_period'] ),
            'calendar_display_mode'      => wc_clean( $post_data['_wc_booking_calendar_display_mode'] ),
            'cancel_limit_unit'          => wc_clean( $post_data['_wc_booking_cancel_limit_unit'] ),
            'cancel_limit'               => wc_clean( $post_data['_wc_booking_cancel_limit'] ),
            'check_start_block_only'     => 'start' === $post_data['_wc_booking_check_availability_against'],
            'cost'                       => wc_clean( $post_data['_wc_booking_cost'] ),
            'default_date_availability'  => wc_clean( $post_data['_wc_booking_default_date_availability'] ),
            'display_cost'               => wc_clean( $post_data['_wc_display_cost'] ),
            'duration_type'              => wc_clean( $post_data['_wc_booking_duration_type'] ),
            'duration_unit'              => wc_clean( $post_data['_wc_booking_duration_unit'] ),
            'duration'                   => wc_clean( $post_data['_wc_booking_duration'] ),
            'enable_range_picker'        => isset( $post_data['_wc_booking_enable_range_picker'] ),
            'first_block_time'           => wc_clean( $post_data['_wc_booking_first_block_time'] ),
            'has_person_cost_multiplier' => isset( $post_data['_wc_booking_person_cost_multiplier'] ),
            'has_person_qty_multiplier'  => isset( $post_data['_wc_booking_person_qty_multiplier'] ),
            'has_person_types'           => isset( $post_data['_wc_booking_has_person_types'] ),
            'has_persons'                => isset( $post_data['_wc_booking_has_persons'] ),
            'has_resources'              => isset( $post_data['_wc_booking_has_resources'] ),
            'max_date_unit'              => wc_clean( $post_data['_wc_booking_max_date_unit'] ),
            'max_date_value'             => wc_clean( $post_data['_wc_booking_max_date'] ),
            'max_duration'               => wc_clean( $post_data['_wc_booking_max_duration'] ),
            'max_persons'                => isset( $post_data['_wc_booking_max_persons_group'] ) ? wc_clean( $post_data['_wc_booking_max_persons_group'] ) : '',
            'min_date_unit'              => wc_clean( $post_data['_wc_booking_min_date_unit'] ),
            'min_date_value'             => wc_clean( $post_data['_wc_booking_min_date'] ),
            'min_duration'               => wc_clean( $post_data['_wc_booking_min_duration'] ),
            'min_persons'                => isset( $post_data['_wc_booking_min_persons_group'] ) ? wc_clean( $post_data['_wc_booking_min_persons_group'] ) : '',
            'person_types'               => $this->get_posted_person_types( $product ),
            'pricing'                    => $this->get_posted_pricing(),
            'qty'                        => wc_clean( $post_data['_wc_booking_qty'] ),
            'requires_confirmation'      => isset( $post_data['_wc_booking_requires_confirmation'] ),
            'resource_label'             => isset( $post_data['_wc_booking_resource_label'] ) ? wc_clean( $post_data['_wc_booking_resource_label'] ) : '',
            'resource_base_costs'        => wp_list_pluck( $resources, 'base_cost' ),
            'resource_block_costs'       => wp_list_pluck( $resources, 'block_cost' ),
            'resource_ids'               => array_keys( $resources ),
            'resources_assignment'       => isset( $post_data['_wc_booking_resources_assignment'] ) ? wc_clean( $post_data['_wc_booking_resources_assignment'] ) : '',
            'user_can_cancel'            => isset( $post_data['_wc_booking_user_can_cancel'] ) ? wc_clean( $post_data['_wc_booking_user_can_cancel'] ) : '',
            'has_restricted_days'        => isset( $post_data['_wc_booking_has_restricted_days'] ) ? wc_clean( $post_data['_wc_booking_has_restricted_days'] ) : '',
            'restricted_days'            => isset( $post_data['_wc_booking_restricted_days'] ) ? wc_clean( $post_data['_wc_booking_restricted_days'] ) : '',
        );

        $product->set_props( $posted_props );

        // Update virtual
        $_virtual   = isset( $post_data['_virtual'] ) ? wc_clean( $post_data['_virtual'] ) : '';
        $is_virtual = 'on' === $_virtual ? 'yes' : 'no';
        update_post_meta( $post_id, '_virtual', $is_virtual );

        $product->save();

        /**
         * Fires after a product is saved.
         *
         * @since 3.4.2 added product and post_data params in hook args
         *
         * @param WC_Product $product   The product object.
         * @param array      $post_data The $_POST data.
         */
        do_action( 'dokan_booking_after_product_data_saved', $product, $post_data );
    }

    /**
     * Filter Redirect url after new booking product added
     *
     * @since 1.0
     *
     * @param string $url
     *
     * @param int $product_id
     *
     * @return $url
     */
    public function set_redirect_url( $url, $product_id ) {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        $product_type = isset( $post_data['product_type'] ) ? $post_data['product_type'] : '';
        // @codingStandardsIgnoreLine
        $tab          = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

        if ( 'booking' === $product_type ) {
            $url = add_query_arg( array( 'product_id' => $product_id ), dokan_get_navigation_url( 'booking' ) . 'edit/' );
            return $url;
        }

        if ( 'booking' === $tab ) {
            $url = add_query_arg( array(), dokan_get_navigation_url( 'booking' ) );
            return $url;
        }

        return $url;
    }

    /**
     * Add new resource via ajax
     *
     * @since 1.0
     *
     * @return void
     */
    public function add_new_resource() {
        // @codingStandardsIgnoreLine
        $add_resource_name = wc_clean( $_POST['add_resource_name'] );

        if ( empty( $add_resource_name ) ) {
            wp_send_json_error();
        }

        $resource    = array(
            'post_title'   => $add_resource_name,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_author'  => dokan_get_current_user_id(),
            'post_type'    => 'bookable_resource',
            'meta_input'   => [ 'qty' => 1 ],
        );
        $resource_id = wp_insert_post( $resource );
        $edit_url    = dokan_get_navigation_url( 'booking' ) . 'resources/edit/?id=' . $resource_id;
        ob_start();
        ?>
        <tr>
            <td><a href="<?php echo $edit_url; ?>"><?php echo $add_resource_name; ?></a></td>
            <td><?php esc_attr_e( 'N/A', 'dokan' ); ?></td>
            <td>
                <a class="dokan-btn dokan-btn-sm dokan-btn-theme" href ="<?php echo $edit_url; ?>"><?php esc_attr_e( 'Edit', 'dokan' ); ?></a>
                <button class="dokan-btn dokan-btn-theme dokan-btn-sm btn-remove" data-id="<?php echo $resource_id; ?>"><?php esc_attr_e( 'Remove', 'dokan' ); ?></button>
            </td>
        </tr>

        <?php
        $output = ob_get_clean();
        wp_send_json_success( $output );
    }

    /**
     * Update Resource Data via ajax
     *
     * @since 1.0
     *
     * @return void
     */
    public function update_resource_data() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        if ( ! isset( $post_data['dokan_booking_resource_update'] ) ) {
            return;
        }

        $post_id = intval( $post_data['resource_id'] );

        $post             = get_post( $post_id );
        $post->post_title = sanitize_text_field( $post_data['post_title'] );

        wp_update_post( $post );

        // Qty field
        update_post_meta( $post_id, 'qty', wc_clean( $post_data['_wc_booking_qty'] ) );
        // Availability
        $availability = array();
        $row_size     = isset( $post_data['wc_booking_availability_type'] ) ? count( $post_data['wc_booking_availability_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
            $availability[ $i ]['type']     = wc_clean( $post_data['wc_booking_availability_type'][ $i ] );
            $availability[ $i ]['bookable'] = wc_clean( $post_data['wc_booking_availability_bookable'][ $i ] );
            $availability[ $i ]['priority'] = intval( $post_data['wc_booking_availability_priority'][ $i ] );

            switch ( $availability[ $i ]['type'] ) {
                case 'custom':
                    $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_date'][ $i ] );
                    $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_date'][ $i ] );
                    break;
                case 'months':
                    $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_month'][ $i ] );
                    $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_month'][ $i ] );
                    break;
                case 'weeks':
                    $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_week'][ $i ] );
                    $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_week'][ $i ] );
                    break;
                case 'days':
                    $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_day_of_week'][ $i ] );
                    $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_day_of_week'][ $i ] );
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                    $availability[ $i ]['from'] = wc_booking_sanitize_time( $post_data['wc_booking_availability_from_time'][ $i ] );
                    $availability[ $i ]['to']   = wc_booking_sanitize_time( $post_data['wc_booking_availability_to_time'][ $i ] );
                    break;
                case 'time:range':
                    $availability[ $i ]['from'] = wc_booking_sanitize_time( $post_data['wc_booking_availability_from_time'][ $i ] );
                    $availability[ $i ]['to']   = wc_booking_sanitize_time( $post_data['wc_booking_availability_to_time'][ $i ] );

                    $availability[ $i ]['from_date'] = wc_clean( $post_data['wc_booking_availability_from_date'][ $i ] );
                    $availability[ $i ]['to_date']   = wc_clean( $post_data['wc_booking_availability_to_date'][ $i ] );
                    break;
            }
        }
        update_post_meta( $post_id, '_wc_booking_availability', $availability );

        $redirect_url = dokan_get_navigation_url( 'booking' ) . 'resources/edit/?id=' . $post_id;
        wp_safe_redirect( add_query_arg( array( 'message' => 'success' ), $redirect_url ) );
    }

    /**
     * Delete Booking resource
     *
     * @since 1.0
     *
     * @return JSON Success | Error
     */
    public function delete_resource() {
        // @codingStandardsIgnoreLine
        $post_id = wc_clean( $_POST['resource_id'] );

        if ( wp_delete_post( $post_id ) ) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Highlight Booking menu as active on Dokan Dashboard
     *
     * @since 1.0
     *
     * @param string $active_menu
     *
     * @return string
     */
    public function set_booking_menu_as_active( $active_menu ) {
        return 'booking';
    }

    /**
     * Add Seller meta to newly created Booking
     *
     * @since 1.0
     *
     * @param int $booking_id Newly created booking id
     *
     * @return void
     */
    public function add_seller_id_meta( $booking_id ) {
        $product_id = get_post_meta( $booking_id, '_booking_product_id', true );
        $seller_id  = get_post_field( 'post_author', $product_id );
        update_post_meta( $booking_id, '_booking_seller_id', $seller_id );
    }

    /**
     * Exclude Booking type products from dokan product listing
     *
     * @since 1.0
     *
     * @param array $product_types
     *
     * @return array $product_types
     */
    public function exclude_booking_type_from_product_listing( $product_types ) {
        $product_types[] = 'booking';
        return $product_types;
    }

    /**
     * Add Booking Manage capability to seller
     *
     * @since 1.0
     *
     * @global type $wp_roles
     *
     * @return void
     */
    public function add_seller_manage_cap() {
        global $wp_roles;

        if ( is_object( $wp_roles ) ) {
            $wp_roles->add_cap( 'seller', 'manage_bookings' );
        }
    }

    /**
     * Confirm bookings from seller dashboard with additional security checks
     *
     * @since 1.0
     *
     * @return void
     */
    public function mark_booking_confirmed() {
        if ( ! current_user_can( 'dokan_manage_bookings' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dokan' ) );
        }

        if ( ! check_admin_referer( 'wc-booking-confirm' ) ) {
            wp_die( __( 'You have taken too long. Please go back and retry.', 'dokan' ) );
        }

        $booking_id = isset( $_GET['booking_id'] ) && (int) $_GET['booking_id'] ? (int) $_GET['booking_id'] : '';

        if ( ! $booking_id ) {
            die;
        }

        // Additional check to see if Seller id is same as current user
        $seller = get_post_meta( $booking_id, '_booking_seller_id', true );

        if ( (int) $seller !== dokan_get_current_user_id() ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dokan' ) );
        }

        $booking = get_wc_booking( $booking_id );

        if ( $booking->get_status() !== 'confirmed' ) {
            $booking->update_status( 'confirmed' );
        }

        /**
         * Fires a Booking Confirm Action.
         *
         * @since 3.4.2
         *
         * @param \WC_Booking $booking
         */
        do_action( 'dokan_after_booking_confirmed', $booking );

        wp_safe_redirect( wp_get_referer() );
        die();
    }

    public static function get_booking_status_counts_by( $seller_id ) {
        global $wpdb;

        $statuses = array_unique( array_merge( get_wc_booking_statuses(), get_wc_booking_statuses( 'user' ), get_wc_booking_statuses( 'cancel' ) ) );
        $statuses = array_fill_keys( array_keys( array_flip( $statuses ) ), 0 );
        $counts   = $statuses + [ 'total' => 0 ];

        $cache_group = "bookings_{$seller_id}";
        $cache_key   = 'bookings_count';
        $results     = Cache::get( $cache_key, $cache_group );

        if ( false === $results ) {
            $meta_key = '_booking_seller_id';

            $sql = "Select post_status
            From $wpdb->posts as p
            LEFT JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id
            WHERE
            pm.meta_key = %s AND
            pm.meta_value = %d AND
            p.post_status != 'trash' ";

            // @codingStandardsIgnoreLine
            $results = $wpdb->get_results( $wpdb->prepare( $sql, $meta_key, $seller_id ) );

            Cache::set( $cache_key, $results, $cache_group );
        }

        foreach ( $results as $status ) {
            if ( isset( $counts[ $status->post_status ] ) ) {
                $counts[ $status->post_status ] += 1;
                $counts['total']                += 1;
            }
        }

        return (object) $counts;
    }

    public function change_booking_status() {
        check_ajax_referer( 'dokan_wc_booking_change_status' );
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        $booking_id = intval( $post_data['booking_id'] );
        $booking    = get_wc_booking( $booking_id );

        $status = wc_clean( $post_data['booking_order_status'] );

        if ( $booking->update_status( $status ) ) {
            $html = '<label class="dokan-label dokan-booking-label-' . esc_attr( $status ) . ' ">' . get_post_status_object( $status )->label . '</label>';

            /**
             * Fires a Booking Status Change Action
             *
             * @param int $booking_id
             *
             * @since 3.4.2
             */
            do_action( 'dokan_booking_change_status', $booking_id );

            wp_send_json_success( $html );
        } else {
            echo _e( 'Error Occured', 'dokan' );
        }

        exit();
    }

    /**
     * Returns the Booking menu Items
     *
     * @since 1.1
     * @return array
     */
    public function dokan_get_bookings_menu( $bookings ) {
        $bookings = array(
            '' => array(
                'title' => __( 'All Booking Product', 'dokan' ),
                'tabs'  => true,
            ),
            'new-product'     => array(
                'title' => __( 'Add Booking Product', 'dokan' ),
                'tabs'  => false,
            ),
            'my-bookings'     => array(
                'title' => __( 'Manage Bookings', 'dokan' ),
                'tabs'  => current_user_can( 'dokan_manage_bookings' ),
            ),
            'calendar'        => array(
                'title' => __( 'Calendar', 'dokan' ),
                'tabs'  => current_user_can( 'dokan_manage_booking_calendar' ),
            ),
            'resources'       => array(
                'title' => __( 'Manage Resources', 'dokan' ),
                'tabs'  => current_user_can( 'dokan_manage_booking_resource' ),
            ),
            'edit'            => array(
                'title' => __( 'Edit Booking Product', 'dokan' ),
                'tabs'  => false,
            ),
            'resources/edit'  => array(
                'title' => __( 'Edit Resource', 'dokan' ),
                'tabs'  => false,
            ),
            'booking-details' => array(
                'title' => __( 'Edit Booking', 'dokan' ),
                'tabs'  => false,
            ),
            'add-booking' => array(
                'title' => __( 'Add Booking', 'dokan' ),
                'tabs'  => false,
            ),
        );

        return apply_filters( 'dokan_booking_nav_titles', $bookings );
    }

    /**
     * Returns the Booking menu Items Title
     *
     * @since 1.1
     * @return array
     */
    public function dokan_get_bookings_menu_title( $current_page ) {
        $menus = apply_filters( 'dokan_booking_menu', '' );

        foreach ( $menus as $key => $value ) {
            // @codingStandardsIgnoreLine
            if ( $current_page == $key ) {
                $title = $value['title'];
            }
        }
        return $title;
    }


    private function get_posted_availability() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        $availability = array();
        $row_size     = isset( $post_data['wc_booking_availability_type'] ) ? count( $post_data['wc_booking_availability_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
                $availability[ $i ]['type']     = wc_clean( $post_data['wc_booking_availability_type'][ $i ] );
                $availability[ $i ]['bookable'] = wc_clean( $post_data['wc_booking_availability_bookable'][ $i ] );
                $availability[ $i ]['priority'] = intval( $post_data['wc_booking_availability_priority'][ $i ] );

            switch ( $availability[ $i ]['type'] ) {
                case 'custom':
                        $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_date'][ $i ] );
                        $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_date'][ $i ] );
                    break;
                case 'months':
                        $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_month'][ $i ] );
                        $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_month'][ $i ] );
                    break;
                case 'weeks':
                        $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_week'][ $i ] );
                        $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_week'][ $i ] );
                    break;
                case 'days':
                        $availability[ $i ]['from'] = wc_clean( $post_data['wc_booking_availability_from_day_of_week'][ $i ] );
                        $availability[ $i ]['to']   = wc_clean( $post_data['wc_booking_availability_to_day_of_week'][ $i ] );
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                        $availability[ $i ]['from'] = wc_booking_sanitize_time( $post_data['wc_booking_availability_from_time'][ $i ] );
                        $availability[ $i ]['to']   = wc_booking_sanitize_time( $post_data['wc_booking_availability_to_time'][ $i ] );
                    break;
                case 'time:range':
                        $availability[ $i ]['from'] = wc_booking_sanitize_time( $post_data['wc_booking_availability_from_time'][ $i ] );
                        $availability[ $i ]['to']   = wc_booking_sanitize_time( $post_data['wc_booking_availability_to_time'][ $i ] );

                        $availability[ $i ]['from_date'] = wc_clean( $post_data['wc_booking_availability_from_date'][ $i ] );
                        $availability[ $i ]['to_date']   = wc_clean( $post_data['wc_booking_availability_to_date'][ $i ] );
                    break;
            }
        }
        return $availability;
    }

    /**
     * Get posted pricing fields and format.
     *
     * @return array
     */
    private function get_posted_pricing() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        $pricing = array();
        $row_size     = isset( $post_data['wc_booking_pricing_type'] ) ? count( $post_data['wc_booking_pricing_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
                $pricing[ $i ]['type']          = wc_clean( $post_data['wc_booking_pricing_type'][ $i ] );
                $pricing[ $i ]['cost']          = wc_clean( $post_data['wc_booking_pricing_cost'][ $i ] );
                $pricing[ $i ]['modifier']      = wc_clean( $post_data['wc_booking_pricing_cost_modifier'][ $i ] );
                $pricing[ $i ]['base_cost']     = wc_clean( $post_data['wc_booking_pricing_base_cost'][ $i ] );
                $pricing[ $i ]['base_modifier'] = wc_clean( $post_data['wc_booking_pricing_base_cost_modifier'][ $i ] );

            switch ( $pricing[ $i ]['type'] ) {
                case 'custom':
                        $pricing[ $i ]['from'] = wc_clean( $post_data['wc_booking_pricing_from_date'][ $i ] );
                        $pricing[ $i ]['to']   = wc_clean( $post_data['wc_booking_pricing_to_date'][ $i ] );
                    break;
                case 'months':
                        $pricing[ $i ]['from'] = wc_clean( $post_data['wc_booking_pricing_from_month'][ $i ] );
                        $pricing[ $i ]['to']   = wc_clean( $post_data['wc_booking_pricing_to_month'][ $i ] );
                    break;
                case 'weeks':
                        $pricing[ $i ]['from'] = wc_clean( $post_data['wc_booking_pricing_from_week'][ $i ] );
                        $pricing[ $i ]['to']   = wc_clean( $post_data['wc_booking_pricing_to_week'][ $i ] );
                    break;
                case 'days':
                        $pricing[ $i ]['from'] = wc_clean( $post_data['wc_booking_pricing_from_day_of_week'][ $i ] );
                        $pricing[ $i ]['to']   = wc_clean( $post_data['wc_booking_pricing_to_day_of_week'][ $i ] );
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                        $pricing[ $i ]['from'] = wc_booking_sanitize_time( $post_data['wc_booking_pricing_from_time'][ $i ] );
                        $pricing[ $i ]['to']   = wc_booking_sanitize_time( $post_data['wc_booking_pricing_to_time'][ $i ] );
                    break;
                case 'time:range':
                        $pricing[ $i ]['from'] = wc_booking_sanitize_time( $post_data['wc_booking_pricing_from_time'][ $i ] );
                        $pricing[ $i ]['to']   = wc_booking_sanitize_time( $post_data['wc_booking_pricing_to_time'][ $i ] );

                        $pricing[ $i ]['from_date'] = wc_clean( $post_data['wc_booking_pricing_from_date'][ $i ] );
                        $pricing[ $i ]['to_date']   = wc_clean( $post_data['wc_booking_pricing_to_date'][ $i ] );
                    break;
                default:
                        $pricing[ $i ]['from'] = wc_clean( $post_data['wc_booking_pricing_from'][ $i ] );
                        $pricing[ $i ]['to']   = wc_clean( $post_data['wc_booking_pricing_to'][ $i ] );
                    break;
            }
        }
        return $pricing;
    }

    /**
     * Get posted person types.
     *
     * @return array
     */
    private function get_posted_person_types( $product ) {
        $post_data    = wp_unslash( $_POST ); // phpcs:ignore
        $person_types = array();

        if ( isset( $post_data['person_id'] ) && isset( $post_data['_wc_booking_has_persons'] ) ) {
                $person_ids         = $post_data['person_id'];
                $person_menu_order  = $post_data['person_menu_order'];
                $person_name        = $post_data['person_name'];
                $person_cost        = $post_data['person_cost'];
                $person_block_cost  = $post_data['person_block_cost'];
                $person_description = $post_data['person_description'];
                $person_min         = $post_data['person_min'];
                $person_max         = $post_data['person_max'];
                $max_loop           = max( array_keys( $post_data['person_id'] ) );

            for ( $i = 0; $i <= $max_loop; $i ++ ) {
                if ( ! isset( $person_ids[ $i ] ) ) {
                    continue;
				}
                    $person_id   = absint( $person_ids[ $i ] );
                    $person_type = new \WC_Product_Booking_Person_Type( $person_id );
                    $person_type->set_props(
                        array(
                            'name'        => wc_clean( stripslashes( $person_name[ $i ] ) ),
                            'description' => wc_clean( stripslashes( $person_description[ $i ] ) ),
                            'sort_order'  => absint( $person_menu_order[ $i ] ),
                            'cost'        => wc_clean( $person_cost[ $i ] ),
                            'block_cost'  => wc_clean( $person_block_cost[ $i ] ),
                            'min'         => wc_clean( $person_min[ $i ] ),
                            'max'         => wc_clean( $person_max[ $i ] ),
                            'parent_id'   => $product->get_id(),
                        )
                    );
                    $person_types[] = $person_type;
            }
        }
        return $person_types;
    }

    /**
     * Get posted resources. Resources are global, but booking products store information about the relationship.
     *
     * @return array
     */
    private function get_posted_resources() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore
        $resources = array();

        if ( isset( $post_data['resource_id'] ) && isset( $post_data['_wc_booking_has_resources'] ) ) {
                $resource_ids         = $post_data['resource_id'];
                $resource_menu_order  = $post_data['resource_menu_order'];
                $resource_base_cost   = $post_data['resource_cost'];
                $resource_block_cost  = $post_data['resource_block_cost'];
                $max_loop             = max( array_keys( $post_data['resource_id'] ) );
                $resource_base_costs  = array();
                $resource_block_costs = array();

            foreach ( $resource_menu_order as $key => $value ) {
                $resources[ absint( $resource_ids[ $key ] ) ] = array(
                    'base_cost'  => wc_clean( $resource_base_cost[ $key ] ),
                    'block_cost' => wc_clean( $resource_block_cost[ $key ] ),
                );
            }
        }

        return $resources;
    }

    /**
     * Delete bookable person type
     * @since 2.7.3
     */
    public function dokan_remove_bookable_person() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        // @codingStandardsIgnoreLine
        if ( ! isset( $post_data['action'] ) && $post_data['action'] != 'woocommerce_remove_bookable_person' ) {
            return;
        }
        if ( ! wp_verify_nonce( $post_data['security'], 'delete-bookable-person' ) ) {
            return;
        }

        wp_delete_post( intval( $post_data['person_id'] ) );
        exit;
    }

    /**
     * Add capabilities
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_capabilities( $capabilities ) {
        $capabilities['menu']['dokan_view_booking_menu'] = __( 'View booking menu', 'dokan' );

        $capabilities['booking'] = array(
            'dokan_manage_booking_products' => __( 'Manage booking products', 'dokan' ),
            'dokan_manage_booking_calendar' => __( 'Manage booking calendar', 'dokan' ),
            'dokan_manage_bookings'         => __( 'Manage bookings', 'dokan' ),
            'dokan_manage_booking_resource' => __( 'Manage booking resource', 'dokan' ),
            'dokan_add_booking_product'     => __( 'Add booking product', 'dokan' ),
            'dokan_edit_booking_product'    => __( 'Edit booking product', 'dokan' ),
            'dokan_delete_booking_product'  => __( 'Delete booking product', 'dokan' ),
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
        $caps['booking'] = __( 'Booking', 'dokan' );

        return $caps;
    }

    /**
     * Add booking discount type
     *
     * @since 2.9.13
     *
     * @param array $types
     *
     * @return array
     */
    public function add_booking_discount( $types ) {
        $types['booking_person'] = __( 'Booking Person Discount (Amount Off Per Person)', 'dokan' );

        return $types;
    }

    /**
     * @since 3.1.4
     * @param $url
     * @param $product
     *
     * @return mixed|string
     */
    public function modify_edit_product_url( $url, $product ) {
        if ( $product->get_type() === 'booking' ) {
            $url = add_query_arg(
                [
                    'product_id' => $product->get_id(),
                    'action'     => 'edit',
                ],
                dokan_get_navigation_url( 'booking' ) . '/edit/'
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
        array_push( $template_array, 'dokan-admin-new-booking.php', 'dokan-customer-booking-cancelled.php' );
        return $template_array;
    }

    /**
     * Clears addon validation notices while creating manual booking
     *
     * @since 3.3.6
     */
    public function clear_addons_validation_notices() {
        if ( class_exists( 'WC_Product_Addons_Cart' ) && 'add-booking' === get_query_var( 'booking' ) ) {
            wc_clear_notices();
        }
    }

    /**
     * Get Booking duration unit label.
     *
     * @since 3.3.4
     *
     * @param string $unit Duration unit.
     *
     * @return string
     */
    public static function get_booking_duration_unit_label( $unit ) {
        switch ( $unit ) {
            case 'month':
                $unit_label = __( 'Month(s)', 'dokan' );
                break;
            case 'day':
                $unit_label = __( 'Day(s)', 'dokan' );
                break;
            case 'hour':
                $unit_label = __( 'Hour(s)', 'dokan' );
                break;
            case 'minute':
                $unit_label = __( 'Minute(s)', 'dokan' );
                break;
            default:
                $unit_label = $unit;
        }
        return $unit_label;
    }

    /**
     * Initializes accommodation booking manager
     *
     * @since 3.4.2
     *
     * @return void
     */
    private function init_accommodation_booking() {
        // Load vendor and frontend manager class
        if ( ! is_admin() ) {
            include_once DOKAN_WC_BOOKING_DIR . '/includes/accommodation/class-dokan-booking-accommodation-manager.php';
            new \Dokan_Booking_Accommodation_Manager();
        }

        // Load admin manager class
        if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            include_once DOKAN_WC_BOOKING_DIR . '/includes/accommodation/admin/class-dokan-booking-accommodation-admin.php';
            new \Dokan_Booking_Accommodation_Admin();
        }

        // Load helper class
        include_once DOKAN_WC_BOOKING_DIR . '/includes/accommodation/class-dokan-booking-accommodation-helper.php';
    }

    /**
     * Enqueue scripts and styles for Accommodation Booking
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function enqueue_accommodation_scripts() {
        // Accommodation Booking
        $accommodation_i18n = \Dokan_Booking_Accommodation_Helper::get_accommodation_booking_i18n_strings();
        $time_format        = wc_time_format();

        wp_enqueue_script( 'dokan_accommodation_booking_script', plugins_url( 'assets/js/accommodation.js', __FILE__ ), [ 'jquery', 'dokan-util-helper' ], DOKAN_PRO_PLUGIN_VERSION, true );
        wp_localize_script( 'dokan_accommodation_booking_script', 'dokan_accommodation_i18n', $accommodation_i18n );

        // Timepicker
        wp_enqueue_style( 'dokan-timepicker' );
        wp_enqueue_script( 'dokan-timepicker' );
    }

    /**
     * Unlink the resources on delete of bookable products
     *
     * @since 3.5.2
     *
     * @param $post_id
     */
    public function handle_deleted_bookable_product( $post_id ) {
        $product = wc_get_product( absint( $post_id ) );

        if ( ! is_a( $product, 'WC_Product' ) || 'booking' !== $product->get_type() ) {
            return;
        }

        \WC_Bookings_Tools::unlink_resource( $post_id );
    }
}
