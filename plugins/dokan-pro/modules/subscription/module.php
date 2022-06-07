<?php

namespace WeDevs\DokanPro\Modules\ProductSubscription;

use DokanPro\Modules\Subscription\Helper;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\Dokan\Vendor\Vendor;

class Module {

    /**
     * Class constructor
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
        require_once dirname( __FILE__ ) . '/includes/classes/class-dps-product-pack.php';

        $this->response = '';

        $this->define_constants();
        $this->file_includes();

        // load subscription class
        add_filter( 'dokan_get_class_container', [ __CLASS__, 'load_subscription_class' ] );
        add_action( 'dokan_vendor', [ __CLASS__, 'add_vendor_subscription' ] );

        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_product_subscription', [ $this, 'activate' ] );
        add_action( 'dokan_deactivated_module_product_subscription', [ $this, 'deactivate' ] );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
        // Add localize script.
        add_filter( 'dokan_admin_localize_script', array( $this, 'add_subscription_packs_to_localize_script' ) );

        // enable the settings only when the subscription is ON
        $enable_option = get_option( 'dokan_product_subscription', array( 'enable_pricing' => 'off' ) );

        if ( ! isset( $enable_option['enable_pricing'] ) || $enable_option['enable_pricing'] != 'on' ) {
            return;
        }

        $this->init_hooks();
    }

    /**
     * Init hooks
     *
     * @return void
     */
    public function init_hooks() {
        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );

        // Loads all actions
        add_filter( 'dokan_can_add_product', array( $this, 'seller_add_products' ), 1, 1 );
        add_filter( 'dokan_vendor_can_duplicate_product', array( $this, 'vendor_can_duplicate_product' ) );
        add_filter( 'dokan_update_product_post_data', array( $this, 'make_product_draft' ), 1 );
        add_action( 'dokan_can_post_notice', array( $this, 'display_product_pack' ) );
        add_filter( 'dokan_can_post', array( $this, 'can_post_product' ) );
        add_filter( 'dokan_product_cat_dropdown_args', [ __CLASS__, 'filter_category' ] );

        // filter product types
        add_filter( 'dokan_product_types', [ __CLASS__, 'filter_product_types' ], 99 );

        // filter capapbilies of accessing pages
        add_filter( 'map_meta_cap', [ __CLASS__, 'filter_capability' ], 20, 2 );

        // filter gallery iamge uploading
        add_action( 'dokan_product_gallery_image_count', [ $this, 'restrict_gallery_image_count' ] );
        add_action( 'dokan_add_product_js_template_end', [ $this, 'restrict_gallery_image_count' ] );
        add_action( 'woocommerce_before_single_product', [ $this, 'restrict_added_image_display' ] );
        add_filter( 'dokan_new_product_popup_args', [ $this, 'restrict_gallery_image_on_product_create' ], 21, 2 );
        add_filter( 'restrict_product_image_gallery_on_edit', [ $this, 'restrict_gallery_image_on_product_edit' ], 10, 1 );

        add_action( 'dps_schedule_pack_update', array( $this, 'schedule_task' ) );
        add_action( 'dokan_before_listing_product', array( $this, 'show_custom_subscription_info' ) );
        add_filter( 'woocommerce_register_post_type_product', [ __CLASS__, 'disable_creating_new_product' ] );

        add_filter( 'dokan_get_dashboard_nav', [ __CLASS__, 'add_new_page' ], 11, 1 );
        add_filter( 'dokan_set_template_path', array( $this, 'load_subscription_templates' ), 11, 3 );
        add_action( 'dokan_load_custom_template', array( $this, 'load_template_from_plugin' ) );

        add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'order_needs_processing' ), 10, 2 );
        add_filter( 'woocommerce_add_to_cart_redirect', [ __CLASS__, 'add_to_cart_redirect' ] );
        add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'maybe_empty_cart' ], 10, 3 );
        add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'remove_addons_validation' ], 1, 3 );

        add_action( 'woocommerce_order_status_changed', array( $this, 'process_order_pack_product' ), 10, 3 );

        add_action( 'template_redirect', array( $this, 'maybe_cancel_or_activate_subscription' ) );
        add_action( 'dps_cancel_recurring_subscription', array( $this, 'cancel_recurring_subscription' ), 10, 2 );
        add_action( 'dps_cancel_non_recurring_subscription', array( $this, 'cancel_non_recurring_subscription' ), 10, 3 );

        add_filter( 'dokan_query_var_filter', [ $this, 'add_subscription_endpoint' ] );

        // Handle popup error if subscription outdated
        add_action( 'dokan_new_product_popup_args', [ __CLASS__, 'can_create_product' ], 20, 2 );

        // remove subscripton product from vendor product listing page
        add_filter( 'dokan_product_listing_exclude_type', array( $this, 'exclude_subscription_product' ) );
        add_filter( 'dokan_count_posts', array( $this, 'exclude_subscription_product_count' ), 10, 3 );

        // remove subscription product from best selling and top rated product query
        add_filter( 'dokan_best_selling_products_query', array( $this, 'exclude_subscription_product_query' ) );
        add_filter( 'dokan_top_rated_products_query', array( $this, 'exclude_subscription_product_query' ) );

        // Allow vendor to import only allowed number of products
        add_filter( 'woocommerce_product_import_pre_insert_product_object', [ __CLASS__, 'import_products' ] );

        // include rest api class
        add_filter( 'dokan_rest_api_class_map', [ __CLASS__, 'rest_api_class_map' ] );

        // include email class
        add_action( 'dokan_loaded', [ __CLASS__, 'load_emails' ], 20 );

        //Category import restriction if category restriction enable, for XML
        add_filter( 'wp_import_post_data_raw', [ $this, 'restrict_category_on_xml_import' ] );

        //For csv
        add_action( 'woocommerce_product_import_before_process_item', [ $this, 'restrict_category_on_csv_import' ] );

        // for disabling email verification
        add_filter( 'dokan_maybe_email_verification_not_needed', [ $this, 'disable_email_verification' ], 10, 1 );

        // Duplicating product based on subscription
        add_filter( 'dokan_can_duplicate_product', [ $this, 'dokan_can_duplicate_product_on_subscription' ], 10, 1 );

        // Do not allow creating new product if vendor do not have any product remaining.
        add_filter( 'dokan_add_new_product_redirect', [ $this, 'redirect_to_product_edit_screen' ], 10, 2 );

        add_filter( 'dokan_vendor_shop_data', array( $this, 'add_currently_subscribed_pack_info_to_shop_data' ), 10, 2 );
        add_filter( 'dokan_vendor_to_array', array( $this, 'add_currently_subscribed_pack_info_to_shop_data' ), 10, 2 );
        add_action( 'dokan_before_update_vendor', array( $this, 'update_vendor_subscription_data' ), 10, 2 );
    }

    /**
     * Load email classes
     *
     * @return void
     */
    public static function load_emails() {
        add_filter( 'dokan_email_classes', [ __CLASS__, 'register_email_class' ] );
        add_filter( 'dokan_email_actions', [ __CLASS__, 'register_email_action' ] );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        do_action( 'dps_schedule_pack_update' );

        if ( false === wp_next_scheduled( 'dps_schedule_pack_update' ) ) {
            wp_schedule_event( time(), 'daily', 'dps_schedule_pack_update' );
        }

        // flush rewrite rules after plugin is activate
        $this->flush_rewrite_rules();

        // todo: we need to rewrite this bit of code, need to store page id in database
        if ( ! self::is_dokan_plugin() ) {
            if ( ! get_page_by_title( __( 'Product Subscription', 'dokan' ) ) ) {
                $dasboard_page = get_page_by_title( 'Dashboard' );

                $page_id = wp_insert_post(
                    array(
                        'post_title'   => wp_strip_all_tags( __( 'Product Subscription', 'dokan' ) ),
                        'post_content' => '[dps_product_pack]',
                        'post_status'  => 'publish',
                        'post_parent'  => $dasboard_page->ID,
                        'post_type'    => 'page',
                    )
                );
            }
        }
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        add_filter( 'dokan_query_var_filter', [ $this, 'add_subscription_endpoint' ] );
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Placeholder for deactivation function
     */
    public function deactivate() {
        $users = get_users(
            [
                'role'   => 'seller',
                'fields' => [ 'ID', 'user_email' ],
            ]
        );

        foreach ( $users as $user ) {
            Helper::make_product_publish( $user->ID );
        }

        wp_clear_scheduled_hook( 'dps_schedule_pack_update' );
    }

    /**
     * Check is Dokan is plugin or nor
     * @return boolean true|false
     */
    public static function is_dokan_plugin() {
        return defined( 'DOKAN_PLUGIN_VERSION' );
    }

    /**
     * Define constants
     *
     * @return void
     */
    function define_constants() {
        define( 'DPS_PATH', dirname( __FILE__ ) );
        define( 'DPS_URL', plugins_url( '', __FILE__ ) );
    }

    /**
     * Includes required files
     *
     * @return void
     */
    function file_includes() {
        if ( is_admin() ) {
            require_once DPS_PATH . '/includes/admin/admin.php';
        }

        require_once DPS_PATH . '/includes/classes/Helper.php';
        require_once DPS_PATH . '/includes/classes/class-dps-paypal-standard-subscriptions.php';
        require_once DPS_PATH . '/includes/classes/Shortcode.php';
        require_once DPS_PATH . '/includes/classes/Registration.php';
        require_once DPS_PATH . '/includes/Abstracts/VendorSubscription.php';
        require_once DPS_PATH . '/includes/classes/SubscriptionPack.php';
        require_once DPS_PATH . '/includes/classes/ProductStatusChanger.php';
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
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_style( 'dps-custom-style', DPS_URL . '/assets/css/style' . $suffix . '.css', [], date( 'Ymd' ) );
        wp_enqueue_script( 'dps-custom-js', DPS_URL . '/assets/js/script' . $suffix . '.js', array( 'jquery' ), time(), true );
        wp_localize_script(
            'dps-custom-js', 'dokanSubscription', array(
                'cancel_string'   => __( 'Do you really want to cancel the subscription?', 'dokan' ),
                'activate_string' => __( 'Want to activate the subscription again?', 'dokan' ),
        ) );
    }

    /**
     * Show_custom_subscription_info in Listing products
     */
    public function show_custom_subscription_info() {
        $vendor_id = dokan_get_current_user_id();

        if ( dokan_is_seller_enabled( $vendor_id ) ) {
            $remaining_product = Helper::get_vendor_remaining_products( $vendor_id );

            if ( true === $remaining_product ) {
                return printf( '<p class="dokan-info">%s</p>', __( 'You can add unlimited products', 'dokan' ) );
            }

            if ( $remaining_product == 0 || ! self::can_post_product() ) {
                if ( self::is_dokan_plugin() ) {
                    $permalink = dokan_get_navigation_url( 'subscription' );
                } else {
                    $page_id   = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );
                    $permalink = get_permalink( $page_id );
                }
                // $page_id = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );
                $info = sprintf( __( 'Sorry! You can not add or publish any more product. Please <a href="%s">update your package</a>.', 'dokan' ), $permalink );
                echo "<p class='dokan-info'>" . $info . '</p>';
                echo '<style>.dokan-add-product-link{display : none !important}</style>';
            } else {
                echo "<p class='dokan-info'>" . sprintf( __( 'You can add %d more product(s).', 'dokan' ), $remaining_product ) . '</p>';
            }
        }
    }

    /**
     * Add Subscription endpoint to the end of Dashboard
     * @param array $query_var
     * @return array
     */
    public function add_subscription_endpoint( $query_var ) {
        $query_var[] = 'subscription';

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
     * Load Dokan subscription templates
     *
     * @since 2.8
     *
     * @return void
     **/
    public function load_subscription_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_subscription'] ) && $args['is_subscription'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Load template for the dashboard
     *
     * @param  array $query_vars
     *
     * @return void
     */
    function load_template_from_plugin( $query_vars ) {
        if ( ! isset( $query_vars['subscription'] ) ) {
            return $query_vars;
        }

        if ( current_user_can( 'vendor_staff' ) ) {
            return dokan_get_template_part( 'global/no-permission' );
        }

        $installed_version = get_option( 'dokan_theme_version' );

        if ( $installed_version > '2.3' ) {
            dokan_get_template_part( 'subscription/product_subscription_plugin_new', '', array( 'is_subscription' => true ) );
        } else {
            dokan_get_template_part( 'subscription/product_subscription_plugin', '', array( 'is_subscription' => true ) );
        }
    }

    /**
     * Add new menu in seller dashboard
     *
     * @param array   $urls
     * @return array
     */
    public static function add_new_page( $urls ) {
        if ( self::is_dokan_plugin() ) {
            $permalink = dokan_get_navigation_url( 'subscription' );
        } else {
            $page_id = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );
            $permalink = get_permalink( $page_id );
        }

        if ( current_user_can( 'vendor_staff' ) ) {
            return $urls;
        }

        if ( dokan_is_seller_enabled( get_current_user_id() ) ) {
            $installed_version = get_option( 'dokan_theme_version' );

            if ( $installed_version > '2.3' ) {
                $urls['subscription'] = array(
                    'title' => __( 'Subscription', 'dokan' ),
                    'icon'  => '<i class="fas fa-book"></i>',
                    'url'   => $permalink,
                    'pos'   => 180,
                );
            } else {
                $urls['subscription'] = array(
                    'title' => __( 'Subscription', 'dokan' ),
                    'icon'  => '<i class="fas fa-book"></i>',
                    'url'   => $permalink,
                );
            }
        }

        return $urls;
    }

    /**
     * Restriction for adding product for seller
     *
     * @param array   $errors
     * @return string
     */
    public function seller_add_products( $errors ) {
        $user_id = dokan_get_current_user_id();

        if ( dokan_is_user_seller( $user_id ) ) {
            $remaining_product = Helper::get_vendor_remaining_products( $user_id );

            if ( true === $remaining_product ) {
                return;
            }

            if ( $remaining_product <= 0 ) {
                $errors[] = __( 'Sorry your subscription exceeds your package limits please update your package subscription', 'dokan' );
                return $errors;
            } else {
                update_user_meta( $user_id, 'product_no_with_pack', $remaining_product - 1 );
                return $errors;
            }
        }
    }

    /**
     * Vendor can duplicate product
     *
     * @return boolean
     */
    public function vendor_can_duplicate_product() {
        $vendor_id = dokan_get_current_user_id();

        if ( ! Helper::get_vendor_remaining_products( $vendor_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Make product status draft when vendor's remaining product is zero
     *
     * @param array $data
     *
     *  @return array
     */
    public function make_product_draft( $data ) {
        $vendor_id = dokan_get_current_user_id();

        if ( Helper::get_vendor_remaining_products( $vendor_id ) ) {
            return $data;
        }

        // if product status was not publish and pending then make it draft
        $product = wc_get_product( $data['ID'] );

        if ( 'publish' !== $product->get_status() && 'pending' !== $product->get_status() ) {
            $data['post_status'] = 'draft';
        }

        return $data;
    }

    /**
     * Get number of product by seller
     *
     * @param integer $user_id
     * @return integer
     */
    function get_number_of_product_by_seller( $user_id ) {
        global $wpdb;

        $allowed_status = apply_filters( 'dps_get_product_by_seller_allowed_statuses', array( 'publish', 'pending' ) );

        $query = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = $user_id AND post_type = 'product' AND post_status IN ( '" . implode( "','", $allowed_status ) . "' )";
        $count = $wpdb->get_var( $query );

        return $count;
    }

    /**
     * Check if have pack availability
     *
     * @since 1.2.1
     *
     * @return void
     */
    public static function can_create_product( $errors, $data ) {
        if ( isset( $data['ID'] ) ) {
            return;
        }

        $user_id = dokan_get_current_user_id();

        if ( dokan_is_user_seller( $user_id ) ) {
            $remaining_product = Helper::get_vendor_remaining_products( $user_id );

            if ( true === $remaining_product ) {
                return;
            }

            if ( $remaining_product <= 0 ) {
                $errors = new \WP_Error( 'no-subscription', __( 'Sorry your subscription exceeds your package limits please update your package subscription', 'dokan' ) );
            } else {
                update_user_meta( $user_id, 'product_no_with_pack', $remaining_product - 1 );
            }

            return $errors;
        }
    }

    /**
     * Display Product Pack
     */
    function display_product_pack() {
        if ( dokan_is_seller_enabled( get_current_user_id() ) ) {
            echo do_shortcode( '[dps_product_pack]' );
        } else {
            dokan_seller_not_enabled_notice();
        }
    }

    /**
     * Check is Seller has any subscription
     *
     * @return boolean
     */
    public static function can_post_product() {
        if ( get_user_meta( dokan_get_current_user_id(), 'can_post_product', true ) == '1' ) {
            return true;
        }

        return false;
    }

    /**
     * Filter vendor category according to subscription
     *
     * @since 1.1.5
     *
     * @return void
     **/
    public static function filter_category( $args ) {
        $user_id = get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $args;
        }

        $is_seller_enabled = dokan_is_seller_enabled( $user_id );

        if ( ! $is_seller_enabled ) {
            return $args;
        }

        $vendor = dokan()->vendor->get( $user_id )->subscription;

        if ( ! $vendor ) {
            return $args;
        }

        if ( ( self::can_post_product() ) && $vendor->has_subscription() ) {
            $override_cat = get_user_meta( $user_id, 'vendor_allowed_categories', true );
            $selected_cat = ! empty( $override_cat ) ? $override_cat : $vendor->get_allowed_product_categories();

            if ( empty( $selected_cat ) ) {
                return $args;
            }

            $args['include'] = $selected_cat;
            return $args;
        }

        return $args;
    }

    /**
     * Filter product types for a vendor
     *
     * @param  array $types
     *
     * @return array
     */
    public static function filter_product_types( $types ) {
        $user_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $types;
        }

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            return $types;
        }

        $allowed_product_types = Helper::get_vendor_allowed_product_types();

        if ( ! $allowed_product_types ) {
            return $types;
        }

        $types = array_filter(
            $types, function( $value, $key ) use ( $allowed_product_types ) {
            return in_array( $key, $allowed_product_types );
        }, ARRAY_FILTER_USE_BOTH
        );

        return $types;
    }

    /**
     * Filter capability for vendor
     *
     * @param  array $caps
     * @param  string $cap
     *
     * @return array
     */
    public static function filter_capability( $caps, $cap ) {
        global $wp_query;

        // if not vendor dashboard and not product edit page
        if ( ! dokan_is_seller_dashboard() && empty( $wp_query->query_vars['edit'] ) ) {
            return $caps;
        }

        if ( 'dokan_view_product_menu' === $cap ) {
            $allowed_product_types = Helper::get_vendor_allowed_product_types();

            $default_types = [ 'simple', 'variable', 'grouped', 'external' ];

            // if no other default product is selected ( ei: dokan_get_product_types() ) then don't show the product menu
            if ( $allowed_product_types && ! array_intersect( $default_types, $allowed_product_types ) ) {
                return [ 'no_permission' ];
            }
        }

        if ( 'dokan_view_booking_menu' === $cap ) {
            $allowed_product_types = Helper::get_vendor_allowed_product_types();

            if ( $allowed_product_types && ! in_array( 'booking', $allowed_product_types ) ) {
                return [ 'no_permission' ];
            }
        }

        if ( 'dokan_view_auction_menu' === $cap ) {
            $allowed_product_types = Helper::get_vendor_allowed_product_types();

            if ( $allowed_product_types && ! in_array( 'auction', $allowed_product_types ) ) {
                return [ 'no_permission' ];
            }
        }

        return $caps;
    }

    /**
     * Schedule task daily update this functions
     */
    public function schedule_task() {
        $users = get_users(
            [
                'role__in'   => [ 'seller', 'administrator' ],
                'fields' => [ 'ID', 'user_email' ],
            ]
        );

        foreach ( $users as $user ) {
            $vendor_subscription = dokan()->vendor->get( $user->ID )->subscription;

            // if no vendor is not subscribed to any pack, skip the vendor, this process also enable code editor autocomplete/quick access support.
            if ( ! $vendor_subscription instanceof \DokanPro\Modules\Subscription\SubscriptionPack ) {
                continue;
            }

            if ( ! Helper::is_subscription_product( $vendor_subscription->get_id() ) ) {
                continue;
            }

            if ( Helper::maybe_cancel_subscription( $user->ID ) ) {
                if ( Helper::check_vendor_has_existing_product( $user->ID ) ) {
                    Helper::make_product_draft( $user->ID );
                }

                $order_id = get_user_meta( $user->ID, 'product_order_id', true );

                if ( $order_id ) {
                    $subject = ( dokan_get_option( 'cancelling_email_subject', 'dokan_product_subscription' ) ) ? dokan_get_option( 'cancelling_email_subject', 'dokan_product_subscription' ) : __( 'Subscription Package Cancel notification', 'dokan' );
                    $message = ( dokan_get_option( 'cancelling_email_body', 'dokan_product_subscription' ) ) ? dokan_get_option( 'cancelling_email_body', 'dokan_product_subscription' ) : __( 'Dear subscriber, Your subscription has expired. Please renew your package to continue using it.', 'dokan' );
                    $headers = 'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

                    wp_mail( $user->user_email, $subject, $message, $headers );

                    Helper::log( 'Subscription cancel check: As the package has expired for order #' . $order_id . ', we are cancelling the Subscription Package of user #' . $user->ID );
                    Helper::delete_subscription_pack( $user->ID, $order_id );
                }
            }

            $is_seller_enabled  = dokan_is_seller_enabled( $user->ID );
            $can_post_product   = $vendor_subscription->can_post_product();
            $has_recurring_pack = $vendor_subscription->has_recurring_pack();
            $has_subscription   = $vendor_subscription->has_subscription();

            if ( ! $has_recurring_pack && $is_seller_enabled && $has_subscription && $can_post_product ) {
                if ( Helper::alert_before_two_days( $user->ID ) ) {
                    $subject = ( dokan_get_option( 'alert_email_subject', 'dokan_product_subscription' ) ) ? dokan_get_option( 'alert_email_subject', 'dokan_product_subscription' ) : __( 'Subscription Ending Soon', 'dokan' );
                    $message = ( dokan_get_option( 'alert_email_body', 'dokan_product_subscription' ) ) ? dokan_get_option( 'alert_email_body', 'dokan_product_subscription' ) : __( 'Dear subscriber, Your subscription will be ending soon. Please renew your package in a timely manner for continued usage.', 'dokan' );
                    $headers = 'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

                    wp_mail( $user->user_email, $subject, $message, $headers );
                    update_user_meta( $user->ID, 'dokan_vendor_subscription_cancel_email', 'yes' );
                }
            }
        }
    }

    /**
     * Process order for specific package
     *
     * @param integer $order_id
     * @param string  $old_status
     * @param string  $new_status
     */
    function process_order_pack_product( $order_id, $old_status, $new_status ) {
        $customer_id = get_post_meta( $order_id, '_customer_user', true );

        if ( $new_status == 'completed' ) {
            $order = new \WC_Order( $order_id );

            $product_items = $order->get_items();

            $product    = reset( $product_items );
            $product_id = $product['product_id'];

            if ( Helper::is_subscription_product( $product_id ) ) {
                if ( ! Helper::has_used_trial_pack( $customer_id ) ) {
                    Helper::add_used_trial_pack( $customer_id, $product_id );
                }

                if ( Helper::is_recurring_pack( $product_id ) ) {
                    return;
                }

                $pack_validity = get_post_meta( $product_id, '_pack_validity', true );
                update_user_meta( $customer_id, 'product_package_id', $product_id );
                update_user_meta( $customer_id, 'product_order_id', $order_id );
                update_user_meta( $customer_id, 'product_no_with_pack', get_post_meta( $product_id, '_no_of_product', true ) );
                update_user_meta( $customer_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );

                if ( empty( $pack_validity ) ) {
                    update_user_meta( $customer_id, 'product_pack_enddate', 'unlimited' );
                } else {
                    update_user_meta( $customer_id, 'product_pack_enddate', dokan_current_datetime()->modify( "+$pack_validity days" )->format( 'Y-m-d H:i:s' ) );
                }

                update_user_meta( $customer_id, 'can_post_product', '1' );
                update_user_meta( $customer_id, '_customer_recurring_subscription', '' );

                $admin_commission      = get_post_meta( $product_id, '_subscription_product_admin_commission', true );
                $admin_additional_fee  = get_post_meta( $product_id, '_subscription_product_admin_additional_fee', true );
                $admin_commission_type = get_post_meta( $product_id, '_subscription_product_admin_commission_type', true );

                if ( ! empty( $admin_commission ) && ! empty( $admin_additional_fee ) && ! empty( $admin_commission_type ) ) {
                    update_user_meta( $customer_id, 'dokan_admin_percentage', $admin_commission );
                    update_user_meta( $customer_id, 'dokan_admin_additional_fee', $admin_additional_fee );
                    update_user_meta( $customer_id, 'dokan_admin_percentage_type', $admin_commission_type );
                } elseif ( ! empty( $admin_commission ) && ! empty( $admin_commission_type ) ) {
                    update_user_meta( $customer_id, 'dokan_admin_percentage', $admin_commission );
                    update_user_meta( $customer_id, 'dokan_admin_percentage_type', $admin_commission_type );
                } else {
                    update_user_meta( $customer_id, 'dokan_admin_percentage', '' );
                }

                do_action( 'dokan_vendor_purchased_subscription', $customer_id );
            }
        }
    }

    /**
     * Redirect after add product into cart
     *
     * @param string $url url
     * @return string $url
     */
    public static function add_to_cart_redirect( $url ) {
        $product_id = isset( $_REQUEST['add-to-cart'] ) ? (int) $_REQUEST['add-to-cart'] : 0;

        if ( ! $product_id ) {
            return $url;
        }

        // If product is of the subscription type
        if ( Helper::is_subscription_product( $product_id ) ) {
            $url = wc_get_checkout_url();
        }

        return $url;
    }


    /**
     * When a subscription is added to the cart, remove other products/subscriptions to
     * work with PayPal Standard, which only accept one subscription per checkout.
     */
    public static function maybe_empty_cart( $valid, $product_id, $quantity ) {
        if ( Helper::is_subscription_product( $product_id ) ) {
            WC()->cart->empty_cart();
        }

        if ( Helper::cart_contains_subscription() ) {
            Helper::remove_subscriptions_from_cart();

            wc_add_notice( __( 'A subscription has been removed from your cart. Due to payment gateway restrictions, products and subscriptions can not be purchased at the same time.', 'dokan' ) );
        }

        return $valid;
    }

    /**
     * Remove addon required validation for dokan subscription product
     *
     * @param bool $valid
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public static function remove_addons_validation( $valid, $product_id, $quantity ) {
        if ( Helper::is_subscription_product( $product_id ) && class_exists( 'WC_Product_Addons_Cart' ) ) {
            remove_filter( 'woocommerce_add_to_cart_validation', array( $GLOBALS['Product_Addon_Cart'], 'validate_add_cart_item' ), 999 );
        }

        return $valid;
    }

    /**
     * Tell WC that we don't need any processing
     *
     * @param  bool $needs_processing
     * @param  array $product
     * @return bool
     */
    function order_needs_processing( $needs_processing, $product ) {
        if ( $product->get_type() == 'product_pack' ) {
            $needs_processing = false;
        }

        return $needs_processing;
    }

    public function maybe_cancel_or_activate_subscription() {
        $posted_data     = wp_unslash( $_POST );
        $cancel_action   = ! empty( $posted_data['dps_cancel_subscription'] ) ? 'cancel' : '';
        $activate_action = ! empty( $posted_data['dps_activate_subscription'] ) ? 'activate' : '';
        $nonce           = $cancel_action ? 'dps-sub-cancel' : 'dps-sub-activate';

        if ( ! $cancel_action && ! $activate_action ) {
            return;
        }

        if ( ! wp_verify_nonce( $posted_data['_wpnonce'], $nonce ) ) {
            wp_die( __( 'Nonce failure', 'dokan' ) );
        }

        $user_id  = get_current_user_id();
        $order_id = get_user_meta( $user_id, 'product_order_id', true );

        if ( self::is_dokan_plugin() ) {
            $page_url = dokan_get_navigation_url( 'subscription' );
        } else {
            $page_url = get_permalink( dokan_get_option( 'subscription_pack', 'dokan_product_subscription' ) );
        }

        if ( $cancel_action ) {
            $cancel_immediately = false;

            if ( $order_id && get_user_meta( $user_id, '_customer_recurring_subscription', true ) == 'active' ) {
                Helper::log( 'Subscription cancel check: User #' . $user_id . ' has canceled his Subscription of order #' . $order_id );
                do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                wp_redirect( add_query_arg( array( 'msg' => 'dps_sub_cancelled' ), $page_url ) );
                exit;
            } else {
                Helper::log( 'Subscription cancel check: User #' . $user_id . ' has canceled his Subscription of order #' . $order_id );
                do_action( 'dps_cancel_non_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                wp_redirect( add_query_arg( array( 'msg' => 'dps_sub_cancelled' ), $page_url ) );
                exit;
            }
        }

        if ( $activate_action ) {
            Helper::log( 'Subscription activation check: User #' . $user_id . ' has reactivate his Subscription of order #' . $order_id );
            do_action( 'dps_activate_recurring_subscription', $order_id, $user_id );
            wp_redirect( add_query_arg( array( 'msg' => 'dps_sub_activated' ), $page_url ) );
        }
    }

    /**
     * Cancel recurrring subscription via paypal
     *
     * @since 1.2.1
     *
     * @return void
     **/
    public function cancel_recurring_subscription( $order_id, $user_id ) {
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( $order && 'paypal' === $order->get_payment_method() ) {
            \DPS_PayPal_Standard_Subscriptions::cancel_subscription_with_paypal( $order_id, $user_id );
        }
    }

    /**
     * Cancel non recurring subscription
     *
     * @since 3.0.3
     *
     * @param int $order_id
     * @param int $vendor_id
     *
     * @return void
     */
    public function cancel_non_recurring_subscription( $order_id, $vendor_id, $cancel_immediately ) {
        /**
         * @since 3.3.7 Introduce new filter hook: dps_cancel_non_recurring_subscription_immediately
         * @param bool $cancel_immediately
         * @param int $order_id
         * @param int $vendor_id
         */
        $cancel_immediately = apply_filters( 'dps_cancel_non_recurring_subscription_immediately', $cancel_immediately, $order_id, $vendor_id );

        if ( $cancel_immediately || 'unlimited' === Helper::get_pack_end_date( $vendor_id ) ) {
            Helper::delete_subscription_pack( $vendor_id, $order_id );
            return;
        }

        $subscription = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $subscription ) {
            /* translators: 1) vendor id */
            dokan_log( sprintf( __( 'Unable to find subscription to be cancelled for vendor id# %s', 'dokan' ), $vendor_id ) );
            return;
        }

        $subscription->set_active_cancelled_subscription();
    }

    /**
     * Disable creating new product from backend
     *
     * @param  array $args
     *
     * @return array
     */
    public static function disable_creating_new_product( $args ) {
        $user_id = dokan_get_current_user_id();

        if ( current_user_can( 'manage_woocommerce' ) ) {
            return $args;
        }

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $args;
        }

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            return $args;
        }

        $remaining_product = Helper::get_vendor_remaining_products( $user_id );

        if ( $remaining_product == 0 || ! self::can_post_product() ) {
            $args['capabilities']['create_posts'] = 'do_not_allow';
        }

        return $args;
    }

    /**
     * Exclude subscription product from product listing page
     *
     * @param  array $terms
     *
     * @return array
     */
    public function exclude_subscription_product( $terms ) {
        $terms[] = 'product_pack';

        return $terms;
    }

    /**
     * Exclude subscription product from total product count
     *
     * @param  string $query
     *
     * @return string
     */
    public function exclude_subscription_product_count( $query, $post_type, $user_id ) {
        global $wpdb;

        $query = "SELECT post_status,
            COUNT( ID ) as num_posts
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_author = %d
            AND ID NOT IN (
                SELECT object_id
                FROM {$wpdb->term_relationships}
                WHERE term_taxonomy_id = (
                    SELECT term_id FROM {$wpdb->terms}
                    WHERE name = 'product_pack'
                )
            )
            GROUP BY post_status";

        $results = $wpdb->get_results(
            $wpdb->prepare(
                $query,
                $post_type, $user_id
            ),
            ARRAY_A
        );
    }

    /**
     * Import number of allowed products
     *
     * @param object $object
     * @throws \ReflectionException
     * @return object
     */
    public static function import_products( $object ) {
        $user_id = dokan_get_current_user_id();

        if ( user_can( $user_id, 'manage_woocommerce' ) ) {
            return $object;
        }

        $user_remaining_product = Helper::get_vendor_remaining_products( $user_id );

        // true means unlimited products
        if ( true === $user_remaining_product ) {
            return $object;
        }

        if ( $user_remaining_product < 1 ) {
            $rf = new \ReflectionProperty( get_class( $object ), 'data_store' );

            if ( ! is_object( $rf ) ) {
                return $object;
            }

            $rf->setAccessible( true );
            $rf->setValue( $object, null );
        }

        return $object;
    }

    /**
     * Include subscription api class
     *
     * @param  array $classes
     *
     * @return array
     */
    public static function rest_api_class_map( $classes ) {
        $class = [ dirname( __FILE__ ) . '/api/class-subscription-controller.php' => 'Dokan_REST_Subscription_Controller' ];

        return array_merge( $classes, $class );
    }

    /**
     * Register email class
     *
     * @param  array $wc_emails
     *
     * @return array
     */
    public static function register_email_class( $wc_emails ) {
        $wc_emails['Dokan_Subscription_Cancelled'] = require_once DPS_PATH . '/includes/emails/subscription-cancelled.php';

        return $wc_emails;
    }

    /**
     * Register email action
     *
     * @param array $actions
     *
     * @return array
     */
    public static function register_email_action( $actions ) {
        $actions[] = 'dokan_subscription_cancelled';

        return $actions;
    }

    /**
     * Load subscription class
     *
     * @param array $classes
     *
     * @return array
     */
    public static function load_subscription_class( $classes ) {
        $classes['subscription'] = new SubscriptionPack();

        return $classes;
    }

    /**
     * Add vendor subscriptionn class
     *
     * @param object $vendor
     *
     * @return void
     */
    public static function add_vendor_subscription( $vendor ) {
        $subscription_pack = null;

        if ( $vendor->get_id() && dokan_is_user_seller( $vendor->get_id() ) ) {
            $subscription_pack_id = get_user_meta( $vendor->get_id(), 'product_package_id', true );

            if ( $subscription_pack_id ) {
                // $subscription_pack = new Dokan_Subscription_Pack( $subscription_pack_id );
                return $vendor->subscription = new SubscriptionPack( $subscription_pack_id, $vendor->get_id() );
            }
        }

        $vendor->subscription = $subscription_pack;
    }

    /**
     * Exclude subscription products from the best selling products
     *
     * @since 2.9.10
     *
     * @param array $args
     *
     * @return array
     */
    public function exclude_subscription_product_query( $args ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'product_pack',
            'operator' => 'NOT IN',
        ];

        return $args;
    }


    /**
     * Restrict gallery image count for new product & edit product
     *
     * @return void
     */
    public function restrict_gallery_image_count() {
        $image_count = $this->get_restricted_image_count();
        if ( $image_count == - 1 ) {
            return;
        }
        if ( $image_count >= 0 ) { ?>
            <script type="text/javascript">
                ;(function () {
                    var image_count = <?php echo json_encode( $image_count, JSON_HEX_TAG ); ?>;
                    var observer = new MutationObserver(function () {
                        if (document.querySelector('.attachments-browser ul')) {
                            var selected_image = document.querySelectorAll("[aria-checked='true']").length;
                            var added_image = document.querySelectorAll("#product_images_container .image").length;
                            if(document.querySelector('.media-toolbar button').innerText !== 'Set featured image' ){
                                var submit_button=document.querySelector('.media-toolbar button');
                                if ((selected_image + added_image) > image_count || selected_image < 1) {
                                    submit_button.disabled = true;
                                } else {
                                    submit_button.disabled = false;
                                }
                            }

                            if (added_image >= image_count) {
                                document.querySelector("#product_images_container .add-image").style.display = 'none';
                            } else {
                                document.querySelector("#product_images_container .add-image").style.display = '';
                            }
                        }
                    });

                    observer.observe(document.body,
                        {
                            childList: true,
                            subtree: true,
                        }
                    )
                })();
            </script>

        <?php }
    }

    /**
     * Restrict already added gallery image using woocommerce_before_single_product
     *
     * @return void
     */
    public function restrict_added_image_display() {
        global $product, $post;

        $image_count = $this->get_restricted_image_count( $post->post_author );
        if ( $image_count == - 1 ) {
            return;
        }

        $product_gallery_image = $this->count_filter( $product->get_gallery_image_ids(), $image_count );
        $product->set_gallery_image_ids( $product_gallery_image );
    }

    /**
     * Restricted gallery image count for vendor subscription
     *
     * @return int
     */
    public function get_restricted_image_count( $vendor_id = null ) {
        $vendor_id = ! empty( $vendor_id ) ? $vendor_id : dokan_get_current_user_id();
        $vendor    = dokan()->vendor->get( $vendor_id )->subscription;

        if ( $vendor && $vendor->is_gallery_image_upload_restricted() ) {
            return $vendor->gallery_image_upload_count();
        }

        return -1;
    }

    /**
     * Restrict gallery image  when creating product
     *
     * @param '' $errors
     * @param array $data
     *
     * @return string
     */
    public function restrict_gallery_image_on_product_create( $errors, $data ) {
        $gallery_image = ! empty( $data['product_image_gallery'] ) ? array_filter( explode( ',', wc_clean( $data['product_image_gallery'] ) ) ) : [];
        $image_count   = $this->get_restricted_image_count();
        if ( $image_count == - 1 ) {
            return;
        }
        if ( count( $gallery_image ) > $image_count ) {
            $errors = new \WP_Error( 'not-allowed', __( sprintf( 'You are not allowed to add more than %s gallery images', $image_count ), 'dokan' ) );

            return $errors;
        }

    }


    /**
     * Restrict gallery image when editing product
     *
     * @param $postdata
     *
     * @return array
     */
    public function restrict_gallery_image_on_product_edit( $postdata ) {
        $gallery_image = ! empty( $postdata['product_image_gallery'] ) ? array_filter( explode( ',', wc_clean( $postdata['product_image_gallery'] ) ) ) : [];
        $image_count   = $this->get_restricted_image_count();
        if ( $image_count == - 1 ) {
            return;
        }
        $postdata['product_image_gallery'] = implode( ',', $this->count_filter( $gallery_image, $image_count ) );

        return $postdata;
    }

    /**
     * Count filter
     *
     * @param array $arr
     * @param int $count
     *
     * @return array
     */
    public function count_filter( $arr, $count ) {
        return array_filter( $arr, function ( $item, $key ) use ( $count ) {
            return $key <= $count - 1;
        }, ARRAY_FILTER_USE_BOTH );
    }

    /**
     * Restrict category if selected category found
     *
     * * @since 3.1.0
     *
     * @param $post
     *
     * @return null|\WP_Post $post
     */
    public function restrict_category_on_xml_import( $post ) {
        $category_name = array_values(
            array_map(
                function ( $category ) {
                    return $category['name'];
                }, array_filter(
                    $post['terms'], function ( $term ) {
                    return 'product_cat' === $term['domain'];
                }
                )
            )
        )[0];

        $allowed_categories = $this->get_vendor_allowed_categories();

        if ( ! empty( $allowed_categories ) ) {
            $categories = [];
            foreach ( $allowed_categories as $allowed_category ) {
                $categories[] = strtolower( get_term_field( 'name', $allowed_category ) );
            }

            if ( in_array( strtolower( $category_name ), $categories ) ) {
                return $post;
            }

            return null;
        }

        return $post;
    }

    /**
     * Restric product import on csv if category restriction enable
     *
     * @param $data
     * @since 3.1.0
     * @throws \Exception
     */
    public function restrict_category_on_csv_import( $data ) {
        $categories         = $data['category_ids'];
        $allowed_categories = $this->get_vendor_allowed_categories();

        if ( ! empty( $allowed_categories ) ) {
            foreach ( $categories as $category ) {
                if ( ! in_array( $category, $allowed_categories ) ) {
                    throw new \Exception( __( 'Current subscription does not allow this', 'dokan' ) . get_term_field( 'name', $category ) );
                }
            }
        }
    }

    /**
     * Get subscription allowed categories if exist
     *
     * @since 3.1.0
     *
     * @return array
     */
    protected function get_vendor_allowed_categories() {
        $vendor_subscription = dokan()->vendor->get( dokan_get_current_user_id() )->subscription;
        if ( ! $vendor_subscription ) {
            return [];
        }
        $allowed_categories  = $vendor_subscription->get_allowed_product_categories();

        return $allowed_categories;
    }

    /**
     * This method will disable email verification if vendor subscription module is on
     * and if subscription is enabled on registration form
     *
     * @since 3.2.0
     * @param bool $ret
     * @return bool
     */
    public function disable_email_verification( $ret ) {
        // if $ret is true, do not bother checking if settings if enabled or not
        if ( $ret ) {
            return $ret;
        }

        $enable_option = get_option( 'dokan_product_subscription', array( 'enable_subscription_pack_in_reg' => 'off' ) );

        // check if subscription is enabled on registration form, we don't need to check if product subscription is enabled for vendor or not,
        // because we are already checking this on class constructor
        if ( (string) $enable_option['enable_subscription_pack_in_reg'] !== 'on' ) {
            return $ret;
        }

        // send verify email if newly registered user role is a customer
        if (
            (
                isset( $_POST['woocommerce-register-nonce'] ) &&
                wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woocommerce-register-nonce'] ) ), 'woocommerce-register' ) &&
                isset( $_POST['role'] ) &&
                'customer' === $_POST['role']
            ) ||
            (
                isset( $_GET['dokan_email_verification'] ) && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) && ! isset( $_GET['page'] )
            )
        ) {
            return false;
        }

        // if product subscription is enabled on registration form, return true,
        // because we don't need to enable email verification if subscription module is active.
        return true;
    }

    /**
     * Redirect to currently created product edit screen
     *
     * @since 3.3.1
     *
     * @param string $redirect_to Redirect url.
     * @param int $product_id Created product ID.
     *
     * @return string
     */
    public function redirect_to_product_edit_screen( $redirect_to, $product_id ) {
        if ( Helper::get_vendor_remaining_products( dokan_get_current_user_id() ) ) {
            return $redirect_to;
        }

        if ( current_user_can( 'dokan_edit_product' ) ) {
            $redirect_to = dokan_edit_product_url( $product_id );
        } else {
            $redirect_to = dokan_get_navigation_url( 'products' );
        }
        return $redirect_to;
    }

    /**
     * @since 3.2.0
     *
     * Checking the ability to duplicate product based on subscription
     *
     * @param $can_duplicate
     *
     * @return bool|mixed|null
     */
    public function dokan_can_duplicate_product_on_subscription( $can_duplicate ) {

        if( ! $can_duplicate ) {
            return $can_duplicate;
        }

        // If the user is vendor staff, we are getting the specific vendor for that staff
        $user_id = (int) dokan_get_current_user_id();

        /** We are getting the subscription of the vendor
         * and checking if the vendor has remaining product based on active subscription
         **/
        if ( ! Helper::get_vendor_remaining_products( $user_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Add non_recurring_subscription_packs to dokan admin script
     *
     * @since 3.3.1
     *
     * @param array $localize_script
     *
     * @return array
     */
    public function add_subscription_packs_to_localize_script( $localize_script ) {
        $localize_script['non_recurring_subscription_packs'] = $this->get_nonrecurring_subscription_packs_with_emply_package();

        return $localize_script;
    }

    /**
     * Add Current subscription info to vendor info.
     *
     * @since 3.3.1
     *
     * @param array $shop_data
     * @param Vendor $vendor
     *
     * @return array
     */
    public function add_currently_subscribed_pack_info_to_shop_data( $shop_data, $vendor ) {
        $users_assigned_pack = get_user_meta( $vendor->id, 'product_package_id', true );

        if ( ! $users_assigned_pack ) {
            $shop_data['current_subscription']       = array(
                'name'  => 0,
                'label' => __( '-- Select a package --', 'dokan' ),
            );
            $shop_data['assigned_subscription']      = 0;
            $shop_data['assigned_subscription_info'] = array(
                'subscription_id'    => 0,
                'has_subscription'   => false,
                'expiry_date'        => '',
                'published_products' => 0,
                'remaining_products' => 0,
                'recurring'          => false,
                'start_date'         => '',
            );
        } else {
            $subscription_pack                       = new SubscriptionPack( $users_assigned_pack, $vendor->id );
            $shop_data['current_subscription']       = array(
                'name'  => $users_assigned_pack,
                'label' => get_the_title( $users_assigned_pack ),
            );
            $shop_data['assigned_subscription']      = $users_assigned_pack;
            $shop_data['assigned_subscription_info'] = $subscription_pack->get_info();

            $shop_data['assigned_subscription_info']['recurring']  = $subscription_pack->is_recurring();
            $shop_data['assigned_subscription_info']['start_date'] = dokan_format_date( $subscription_pack->get_pack_start_date() );
        }

        return $shop_data;
    }

    /**
     *  Get non recurring subscription packs with empty pack.
     *
     * @since 3.3.1
     *
     * @return array
     */
    private function get_nonrecurring_subscription_packs_with_emply_package() {
        $subscriptions_packs = ( new SubscriptionPack() )->get_nonrecurring_packages();
        $response_array = array(
            array(
                'name' => 0,
                'label' => __( '-- Select a package --', 'dokan' ),
            ),
        );
        foreach ( $subscriptions_packs as $subscriptions_pack ) {
            array_push(
                $response_array,
                array(
                    'name' => $subscriptions_pack->ID,
                    'label' => $subscriptions_pack->post_title,
                )
            );
        }

        return $response_array;
    }

    /**
     * Store Vendor Subscribed subscription package information.
     *
     * @since 3.3.1
     *
     * @param int $vendor_id
     * @param array $data
     *
     * @return void
     */
    public function update_vendor_subscription_data( $vendor_id, $data ) {
        if ( ! isset( $data['subscription_nonce'] ) || ! wp_verify_nonce( $data['subscription_nonce'], 'dokan_admin' ) ) {
            return;
        }
        $vendor_id                = absint( $vendor_id );
        $subscription_id          = absint( $data['assigned_subscription'] );
        $previous_subscription_id = absint( get_user_meta( $vendor_id, 'product_package_id', true ) );

        if ( ! empty( $subscription_id ) && $subscription_id !== $previous_subscription_id ) {
            // Manually creating a order with 0.00 price to set the subscription.
            try {
                $order = new \WC_Order();
                $order->add_product( wc_get_product( $subscription_id ) );
                $order->set_created_via( 'dokan' );
                $order->set_customer_id( absint( $vendor_id ) );
                $order->set_total( 0.00 );
                $order->set_status( 'completed' );
                $order->save();
                $order->add_order_note( __( 'Manually assigned Vendor Subscription by Admin', 'dokan' ), 0, get_current_user_id() );
            } catch ( \Exception $exception ) {
                Helper::log( 'Subscription manually assign error from admin of User #' . $vendor_id . ' Message: ' . $exception->getMessage() );
                return;
            }

            $pack_validity         = get_post_meta( $subscription_id, '_pack_validity', true );
            $admin_commission      = get_post_meta( $subscription_id, '_subscription_product_admin_commission', true );
            $admin_additional_fee  = get_post_meta( $subscription_id, '_subscription_product_admin_additional_fee', true );
            $admin_commission_type = get_post_meta( $subscription_id, '_subscription_product_admin_commission_type', true );

            update_user_meta( $vendor_id, 'product_package_id', $subscription_id );
            update_user_meta( $vendor_id, 'product_order_id', $order->get_id() );
            update_user_meta( $vendor_id, 'product_no_with_pack', get_post_meta( $subscription_id, '_no_of_product', true ) ); //number of products
            update_user_meta( $vendor_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );

            if ( absint( $pack_validity ) > 0 ) {
                update_user_meta( $vendor_id, 'product_pack_enddate', dokan_current_datetime()->modify( "+$pack_validity days" )->format( 'Y-m-d H:i:s' ) );
            } else {
                update_user_meta( $vendor_id, 'product_pack_enddate', 'unlimited' );
            }

            update_user_meta( $vendor_id, 'can_post_product', 1 );
            update_user_meta( $vendor_id, '_customer_recurring_subscription', '' );

            if ( ! empty( $admin_commission ) && ! empty( $admin_commission_type ) ) {
                update_user_meta( $vendor_id, 'dokan_admin_percentage', $admin_commission );
                update_user_meta( $vendor_id, 'dokan_admin_percentage_type', $admin_commission_type );
            } else {
                update_user_meta( $vendor_id, 'dokan_admin_percentage', '' );
            }

            if ( ! empty( $admin_additional_fee ) && ! empty( $admin_commission_type ) ) {
                update_user_meta( $vendor_id, 'dokan_admin_additional_fee', $admin_additional_fee );
            } else {
                update_user_meta( $vendor_id, 'dokan_admin_additional_fee', '' );
            }
        }
    }
}
