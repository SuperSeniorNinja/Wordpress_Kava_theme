<?php

namespace WeDevs\DokanPro\Modules\SellerVacation;

class Module {

    /**
     * Constructor for the Dokan_Seller_Vacation class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->instances();

        add_action( 'init', array( $this, 'custom_post_status_vacation' ) );

        add_filter( 'dokan_product_listing_query', array( $this, 'modified_product_listing_query' ) );
        add_filter( 'dokan_get_post_status', array( $this, 'show_vacation_status_listing' ), 12 );
        add_filter( 'dokan_get_post_status_label_class', array( $this, 'show_vacation_status_listing_label' ), 12 );
        add_filter( 'dokan_product_listing_post_statuses', array( $this, 'add_vacation_product_listing_statuses_filter' ), 12, 1 );

        add_action( 'dokan_product_listing_status_filter', array( $this, 'add_vacation_product_listing_filter' ), 10, 2 );
        add_action( 'dokan_store_profile_frame_after', array( $this, 'show_vacation_message' ), 10, 2 );
        add_action( 'template_redirect', array( $this, 'remove_product_from_cart_for_closed_store' ) );
        add_action( 'dokan_new_product_added', array( $this, 'product_status_modified_on_vacation' ), 12 );
        add_action( 'dokan_product_updated', array( $this, 'product_status_modified_on_vacation' ), 12 );
        add_action( 'dokan_product_duplicate_after_save', array( $this, 'set_vacation_duplicate_product_save' ), 35 );
        add_filter( 'dokan_bulk_product_statuses', array( $this, 'set_vacation_bulk_edit_product_statuses' ), 35 );

        add_action( 'plugins_loaded', [ $this, 'load_bg_class' ] );
    }

    /**
     * Module constants
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_SELLER_VACATION_FILE', __FILE__ );
        define( 'DOKAN_SELLER_VACATION_PATH', dirname( DOKAN_SELLER_VACATION_FILE ) );
        define( 'DOKAN_SELLER_VACATION_INCLUDES', DOKAN_SELLER_VACATION_PATH . '/includes' );
        define( 'DOKAN_SELLER_VACATION_URL', plugins_url( '', DOKAN_SELLER_VACATION_FILE ) );
        define( 'DOKAN_SELLER_VACATION_ASSETS', DOKAN_SELLER_VACATION_URL . '/assets' );
        define( 'DOKAN_SELLER_VACATION_VIEWS', DOKAN_SELLER_VACATION_PATH . '/views' );
    }

    /**
     * Include module related files
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/functions.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-install.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-store-settings.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-ajax.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-cron.php';
    }

    /**
     * Load background process file on plugins_loaded hook
     *
     * @since 3.2.4
     * @return void
     */
    public function load_bg_class() {
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-update-seller-product-status.php';
        global $dokan_pro_sv_update_seller_product_status;
        $dokan_pro_sv_update_seller_product_status = new \Dokan_Seller_Vacation_Update_Seller_Product_Status();
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function instances() {
        new \Dokan_Seller_Vacation_Install();
        new \Dokan_Seller_Vacation_Store_Settings();
        new \Dokan_Seller_Vacation_Ajax();
        new \Dokan_Seller_Vacation_Cron();
    }

    /**
     * Register custom post status "vacation"
     * @return void
     */
    public function custom_post_status_vacation() {
        register_post_status(
            'vacation', array(
				'label'                     => __( 'Vacation', 'dokan' ),
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
                /* Translators: %s: number of vacation */
				'label_count'               => _n_noop( 'Vacation <span class="count">(%s)</span>', 'Vacation <span class="count">(%s)</span>', 'dokan' ),
            )
        );
    }

    /**
     * Show Vacation message in store page
     * @param  array $store_user
     * @param  array $store_info
     * @return void
     */
    public function show_vacation_message( $store_user, $store_info, $raw_output = false ) {
        $vendor = dokan()->vendor->get( $store_user->ID );

        if ( dokan_seller_vacation_is_seller_on_vacation( $vendor->get_id() ) ) {
            $shop_info = $vendor->get_shop_info();

            $message = '';

            if ( 'datewise' !== $shop_info['settings_closing_style'] ) {
                $message = $store_info['setting_vacation_message'];
            } else {
                $schedules    = dokan_seller_vacation_get_vacation_schedules( $shop_info );
                $current_time = date( 'Y-m-d', current_time( 'timestamp' ) ); // phpcs:ignore

                foreach ( $schedules as $schedule ) {
                    $from = $schedule['from'];
                    $to   = $schedule['to'];

                    if ( $from <= $current_time && $current_time <= $to ) {
                        $message = $schedule['message'];
                        break;
                    }
                }
            }

            if ( $raw_output ) {
                echo esc_html( $message );
            } else {
                dokan_seller_vacation_get_template(
                    'vacation-message', array(
						'message' => $message,
                    )
                );
            }
        }
    }

    /**
     * Add vacation link in product listing filter
     * @param string $status_class
     * @param object $post_counts
     */
    public function add_vacation_product_listing_filter( $status_class, $post_counts ) {
        ?>
        <li<?php echo $status_class === 'vacation' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'vacation' ), get_permalink() ); ?>">
                <?php
                /* Translators: %d: number of vacation; */
                printf( __( 'Vacation (%d)', 'dokan' ), $post_counts->vacation );
                ?>
            </a>
        </li>
        <?php
    }

    /**
     * Show Vacation status with product in product listing
     *
     * @param  string $status
     *
     * @return string
     */
    public function show_vacation_status_listing( $status ) {
        $status['vacation'] = __( 'In vacation', 'dokan' );
        return $status;
    }

    /**
     * Get vacation status label
     *
     * @since 1.2
     *
     * @return void
     */
    public function show_vacation_status_listing_label( $labels ) {
        $labels['vacation'] = 'dokan-label-info';
        return $labels;
    }

    /**
     * Add vacation status on product listing query
     *
     * @since 3.1.2
     *
     * @param array $post_status
     *
     * @return array
     */
    public function add_vacation_product_listing_statuses_filter( $post_status ) {
        if ( is_array( $post_status ) ) {
            $post_status[] = 'vacation';
        }

        return $post_status;
    }

    /**
     * Modified Porduct query
     * @param  array $args
     * @return array
     */
    public function modified_product_listing_query( $args ) {
        $get = wp_unslash( $_GET ); // phpcs:ignore CSRF ok.

        if ( isset( $get['post_status'] ) && $get['post_status'] === 'vacation' ) {
            $args['post_status'] = $get['post_status'];
            return $args;
        }

        if ( is_array( $args['post_status'] ) ) {
            $args['post_status'][] = 'vacation';
            return $args;
        }
        return $args;
    }

    /**
     * Remove product from cart for closed store
     * @param  null
     * @return void
     */
    public function remove_product_from_cart_for_closed_store() {
        if ( is_cart() || is_checkout() ) {
            foreach ( WC()->cart->cart_contents as $item ) {
                $product_id = ( isset( $item['variation_id'] ) && $item['variation_id'] !== 0 ) ? $item['variation_id'] : $item['product_id'];

                if ( empty( $product_id ) ) {
                    continue;
                }

                $vendor_id = get_post_field( 'post_author', $product_id );

                if ( empty( $vendor_id ) ) {
                    continue;
                }

                if ( dokan_seller_vacation_is_seller_on_vacation( $vendor_id ) ) {
                    $product_cart_id = isset( $item['key'] ) ? $item['key'] : WC()->cart->generate_cart_id( $product_id );
                    WC()->cart->remove_cart_item( $product_cart_id );
                }
            }
        }
    }

    /**
     * Duplicate product status modified on vacation enable
     *
     * @since DOKAN_PRO_SINCH
     *
     * @param array $clone_product
     *
     * @return void
     */
    public function set_vacation_duplicate_product_save( $clone_product ) {
        if ( ! isset( $clone_product ) ) {
            return;
        }

        $seller_id = get_post_field( 'post_author', $clone_product->get_id() );

        if ( dokan_seller_vacation_is_seller_on_vacation( $seller_id ) ) {
            $product = wc_get_product( $clone_product->get_id() );
            $product->set_status( 'vacation' );
            $product->save();
        }
    }

    /**
     * Product status modified on vacation enable
     *
     * @since DOKAN_PRO_SINCH
     *
     * @param int $product_id
     *
     * @return void
     */
    public function product_status_modified_on_vacation( $product_id ) {
        $seller_id = get_post_field( 'post_author', $product_id );

        if ( dokan_seller_vacation_is_seller_on_vacation( $seller_id ) ) {
            $product = wc_get_product( $product_id );
            $product->set_status( 'vacation' );
            $product->save();
        }
    }

    /**
     * Bulk edit status modified on vacation enable
     *
     * @since DOKAN_PRO_SINCH
     *
     * @param array $status
     *
     * @return array $status
     */
    public function set_vacation_bulk_edit_product_statuses( $status ) {
        $vendor_id = dokan_get_current_user_id();
        if ( isset( $status['publish'] ) && dokan_seller_vacation_is_seller_on_vacation( $vendor_id ) ) {
            unset( $status['publish'] );
        }

        return $status;
    }
}
