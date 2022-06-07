<?php

/**
* Admin class
*/
class Dokan_Wholesale_Admin {

    /**
     * Load automatically when class initiate
     *
     * @since 2.9.5
     */
    public function __construct() {
        add_filter( 'dokan_settings_sections', [ $this, 'load_settings_section' ], 20 );
        add_filter( 'dokan_settings_fields', [ $this, 'load_settings_fields' ], 20 );

        add_action( 'dokan-vue-admin-scripts', [ $this, 'admin_enqueue_scripts' ] );
        add_action( 'dokan_admin_menu', [ $this, 'add_submenu' ], 17 );
        add_filter( 'dokan-admin-routes', [ $this, 'admin_routes' ] );

        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'wholesale_metabox' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_wholesale_data' ] );

        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'wholesale_variation_metabox' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_wholesale_variation_data' ], 10, 2 );
    }

    /**
     * Load admin settings section
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function load_settings_section( $section ) {
        $section[] = array(
            'id'    => 'dokan_wholesale',
            'title' => __( 'Wholesale', 'dokan' ),
            'icon'  => 'dashicons-admin-multisite'
        );

        return $section;
    }

    /**
     * Load all settings fields
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function load_settings_fields( $fields ) {
        $fields['dokan_wholesale'] = array(
            'wholesale_price_display' => array(
                'name'    => 'wholesale_price_display',
                'label'   => __( 'Who can see wholesale price', 'dokan' ),
                'type'    => 'radio',
                'desc'    => __( 'Who can actually see the wholesale price in product page', 'dokan' ),
                'default' => 'wholesale_customer',
                'options' => [
                    'all_user'           => __( 'Display wholesale price to all users' ),
                    'wholesale_customer' => __( 'Display wholesale price to Wholesale customer only' ),
                ]
            ),

            'display_price_in_shop_archieve' => array(
                'name'    => 'display_price_in_shop_archieve',
                'label'   => __( 'Show wholesale price on shop archive', 'dokan' ),
                'type'    => 'checkbox',
                'desc'    => __( 'Show in price column', 'dokan' ),
                'default' => 'no',
            ),

            'need_approval_for_wholesale_customer' => array(
                'name'    => 'need_approval_for_wholesale_customer',
                'label'   => __( 'Need approval for customer', 'dokan' ),
                'type'    => 'select',
                'desc'    => __( 'Customer need admin approval for becoming a wholesale customer', 'dokan' ),
                'default' => 'yes',
                'options' => [
                    'yes' => __( 'Yes', 'dokan' ),
                    'no'  => __( 'No', 'dokan' )
                ]
            ),
        );

        return $fields;
    }

    /**
     * Enqueue vue component js
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_style( 'dokan-wholesale-admin-style', DOKAN_WHOLESALE_ASSETS_DIR . '/css/admin' . $suffix . '.css', false, DOKAN_PLUGIN_VERSION, 'all' );
        wp_enqueue_script( 'dokan-wholesale-admin', DOKAN_WHOLESALE_ASSETS_DIR . '/js/admin' . $suffix . '.js', array(  'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ), false, true );
    }

    /**
     * Add submenu page in dokan Dashboard
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function add_submenu( $capability ) {
        if ( current_user_can( $capability ) ) {
            global $submenu;

            $title = esc_html__( 'Wholesale Customer', 'dokan' );
            $slug  = 'dokan';

            $submenu[ $slug ][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/wholesale-customer' ];
        }
    }

    /**
     * Add subscripton route
     *
     * @param  array $routes
     *
     * @return array
     */
    public function admin_routes( $routes ) {
        $routes[] = [
            'path'      => '/wholesale-customer',
            'name'      => 'WholesaleCustomer',
            'component' => 'WholesaleCustomer'
        ];

        return $routes;
    }

    /**
     * Add wholesale matabox
     *
     * @since 2.9.13
     *
     * @return void
     */
    public function wholesale_metabox() {
        $product_id = get_the_ID();
        $product    = wc_get_product( $product_id );

        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $wholesale_data   = $product->get_meta( '_dokan_wholesale_meta' );
        $enable_wholesale = ! empty( $wholesale_data['enable_wholesale'] ) ? $wholesale_data['enable_wholesale'] : 'no';
        $price            = ! empty( $wholesale_data['price'] ) ? $wholesale_data['price'] : '';
        $quantity         = ! empty( $wholesale_data['quantity'] ) ? $wholesale_data['quantity'] : '';

        echo '<div class="options_group show_if_simple show_if_external">';
        woocommerce_wp_checkbox( [
            'id'          => 'enable_wholesale',
            'value'       => $enable_wholesale,
            'label'       => __( 'Wholesale', 'dokan' ),
            'description' => __( 'Enable wholesale for this product', 'dokan' ),
        ] );

        woocommerce_wp_text_input( [
            'id'        => 'wholesale_price',
            'value'     => $price,
            'data_type' => 'price',
            'label'     => __( 'Wholesale Price', 'dokan' ),
        ] );

        woocommerce_wp_text_input( [
            'id'        => 'wholesale_quantity',
            'value'     => $quantity,
            'data_type' => 'price',
            'label'     => __( 'Minimum Quantity for Wholesale ', 'dokan' ),
        ] );

        do_action( 'dokan_wholesale_options', $wholesale_data, $product_id );
        echo '</div>';
    }

    /**
     * Save wholesale matabox data
     *
     * @since 2.9.13
     *
     * @param int $product_id
     *
     * @return void
     */
    public function save_wholesale_data( $product_id ) {
        $product = wc_get_product( $product_id );

        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $wholesale_data                     = [];
        $wholesale_data['enable_wholesale'] = ! empty( $_POST['enable_wholesale'] ) ? wc_clean( $_POST['enable_wholesale'] ) : 'no';
        $wholesale_data['price']            = ! empty( $_POST['wholesale_price'] ) ? wc_format_decimal( $_POST['wholesale_price'] ) : '';
        $wholesale_data['quantity']         = ! empty( $_POST['wholesale_quantity'] ) ? absint( $_POST['wholesale_quantity'] ) : '';

        $product->update_meta_data( '_dokan_wholesale_meta', $wholesale_data );
        $product->save();
    }

    /**
     * Add wholesale variation metabox
     *
     * @since 2.9.13
     *
     * @param  int $loop
     * @param  array $variation_data
     * @param  object variation
     *
     * @return void
     */
    public function wholesale_variation_metabox( $loop, $variation_data, $variation ) {
        $product_id = ! empty( $variation->ID ) ? $variation->ID : 0;
        $product      = wc_get_product( $product_id );

        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $wholesale_data   = $product->get_meta( '_dokan_wholesale_meta' );
        $enable_wholesale = ! empty( $wholesale_data['enable_wholesale'] ) ? $wholesale_data['enable_wholesale'] : 'no';
        $price            = ! empty( $wholesale_data['price'] ) ? $wholesale_data['price'] : '';
        $quantity         = ! empty( $wholesale_data['quantity'] ) ? $wholesale_data['quantity'] : '';
        echo '<div class="options_group">';
        woocommerce_wp_checkbox( [
            'id'            => "variable_enable_wholesale{$loop}",
            'label'         => __( 'Enable wholesale for this product', 'dokan' ),
            'name'          => "variable_enable_wholesale[{$loop}]",
            'value'         => $enable_wholesale,
            'style'         => 'margin: 2px 5px !important',
            'wrapper_class' => 'form-row form-row-full form-field',
        ] );

        woocommerce_wp_text_input( [
            'id'        => "variable_wholesale_price{$loop}",
            'name'      => "variable_wholesale_price[{$loop}]",
            'value'     => $price,
            'data_type' => 'price',
            'label'     => __( 'Wholesale Price', 'dokan' ),
        ] );

        woocommerce_wp_text_input( [
            'id'        => "variable_wholesale_quantity{$loop}",
            'name'      => "variable_wholesale_quantity[{$loop}]",
            'value'     => $quantity,
            'data_type' => 'price',
            'label'     => __( 'Minimum Quantity for Wholesale ', 'dokan' ),
        ] );

        do_action( 'dokan_wholesale_variation_options', $wholesale_data, $loop, $product_id );
        echo '</div>';
    }

    /**
     * Save whole variation data
     *
     * @since 2.9.13
     *
     * @param int $product_id
     * @param int $loop
     *
     * @return void
     */
    public function save_wholesale_variation_data( $product_id, $loop ) {
        $product = wc_get_product( $product_id );

        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $wholesale_data                     = [];
        $wholesale_data['enable_wholesale'] = ! empty( $_POST['variable_enable_wholesale'][$loop] ) ? wc_clean( $_POST['variable_enable_wholesale'][$loop] ) : 'no';
        $wholesale_data['price']            = ! empty( $_POST['variable_wholesale_price'][$loop] ) ? wc_format_decimal( $_POST['variable_wholesale_price'][$loop] ) : '';
        $wholesale_data['quantity']         = ! empty( $_POST['variable_wholesale_quantity'][$loop] ) ? absint( $_POST['variable_wholesale_quantity'][$loop] ) : '';

        $product->update_meta_data( '_dokan_wholesale_meta', $wholesale_data );
        $product->save();
    }
}
