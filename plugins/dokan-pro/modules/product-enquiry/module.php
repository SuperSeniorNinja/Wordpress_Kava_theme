<?php

namespace WeDevs\DokanPro\Modules\ProductEnquiry;

/**
 * Dokan_Product_Enquiry class
 *
 * @class Dokan_Product_Enquiry The class that holds the entire Dokan_Product_Enquiry plugin
 */
class Module {

    /**
     * Constructor for the Dokan_Product_Enquiry class
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
        $this->define_constants();

        add_action( 'wp_ajax_dokan_product_enquiry', array( $this, 'send_email' ) );
        add_action( 'wp_ajax_nopriv_dokan_product_enquiry', array( $this, 'send_email' ) );

        add_filter( 'woocommerce_product_tabs', array( $this, 'register_tab' ), 99 );
        add_filter( 'dokan_settings_selling_option_vendor_capability', array( $this, 'guest_user_settings' ) );

        add_filter( 'dokan_email_classes', array( $this, 'add_email_class' ) );
        add_filter( 'dokan_email_list', array( $this, 'add_email_template_file' ) );
        add_filter( 'dokan_email_actions', array( $this, 'add_email_action' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Define constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'DOKAN_ENQUIRY_INC', __DIR__ . '/includes' );
        define( 'DOKAN_ENQUIRY_VIEWS', __DIR__ . '/views' );
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
        wp_enqueue_script( 'dpe-scripts', plugins_url( 'assets/js/enquiry.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_localize_script(
            'dpe-scripts', 'DokanEnquiry', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
            )
        );
    }

    /**
     * Get user agent string
     *
     * @return string
     */
    public function get_user_agent() {
        return substr( $_SERVER['HTTP_USER_AGENT'], 0, 150 );
    }

    /**
     * Get from name for email.
     *
     * @access public
     * @return string
     */
    public function get_from_name() {
        return wp_specialchars_decode( esc_html( get_option( 'woocommerce_email_from_name' ) ), ENT_QUOTES );
    }

    /**
     * Get from email address.
     *
     * @access public
     * @return string
     */
    public function get_from_address() {
        return sanitize_email( get_option( 'woocommerce_email_from_address' ) );
    }

    /**
     * Send email
     *
     * @since  0.1
     *
     * @return void
     */
    public function send_email() {
        if ( ! isset( $_POST['dokan_product_enquiry_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_product_enquiry_nonce'] ) ), 'dokan_product_enquiry' ) ) {
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        $url             = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
        $message         = isset( $_POST['enq_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['enq_message'] ) ) : '';
        $product_id      = isset( $_POST['enquiry_id'] ) ? absint( wp_unslash( $_POST['enquiry_id'] ) ) : 0;
        $vendor_id       = isset( $_POST['seller_id'] ) ? absint( wp_unslash( $_POST['seller_id'] ) ) : 0;
        $recaptcha_token = isset( $_POST['dokan_product_enquiry_recaptcha_token'] ) ? wp_unslash( $_POST['dokan_product_enquiry_recaptcha_token'] ) : ''; // phpcs:ignore

        if ( ! empty( $url ) ) {
            wp_send_json_error( __( 'Boo ya!', 'dokan' ) );
        }

        if ( is_user_logged_in() ) {
            $sender         = wp_get_current_user();
            $customer_name  = $sender->display_name;
            $customer_email = $sender->user_email;
        } else {
            $customer_name  = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '';
            $customer_email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        }

        if ( empty( $customer_name ) ) {
            wp_send_json_error( __( 'Customer name cannot be empty!', 'dokan' ) );
        }

        if ( empty( $customer_email ) ) {
            wp_send_json_error( __( 'Customer email cannot be empty!', 'dokan' ) );
        }

        if ( empty( $message ) ) {
            wp_send_json_error( __( 'Message cannot be empty!', 'dokan' ) );
        }

        // no seller found
        $vendor = dokan()->vendor->get( $vendor_id );
        if ( ! $vendor || is_wp_error( $vendor ) ) {
            wp_send_json_error( __( 'Something went wrong!', 'dokan' ) );
        }

        // no product found
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( __( 'Something went wrong!', 'dokan' ) );
        }

        // Validate recaptcha if site key and secret key exist.
        if ( dokan_get_recaptcha_site_and_secret_keys( true ) ) {
            $recaptcha_keys     = dokan_get_recaptcha_site_and_secret_keys();
            $recaptcha_validate = dokan_handle_recaptcha_validation( 'dokan_product_enquiry_recaptcha', $recaptcha_token, $recaptcha_keys['secret_key'] );

            if ( empty( $recaptcha_validate ) ) {
                wp_send_json_error( __( 'reCAPTCHA verification failed!', 'dokan' ) );
            }
        }

        // Email arguments.
        $email_args = array(
            $vendor,
            $product,
            dokan_get_client_ip(),
            $this->get_user_agent(),
            $customer_name,
            $customer_email,
            $message,
        );

        do_action_ref_array( 'dokan_send_enquiry_email', $email_args );

        $success = sprintf( '<div class="alert alert-success">%s</div>', __( 'Email sent successfully!', 'dokan' ) );
        wp_send_json_success( $success );
    }

    /**
     * Register product enquiry tab
     *
     * @since  0.1
     *
     * @param  array $tabs
     *
     * @return array
     */
    public function register_tab( $tabs ) {
        global $product, $post;

        $tabs['seller_enquiry_form'] = array(
            'title'    => __( 'Product Enquiry', 'dokan' ),
            'priority' => 29,
            'callback' => array( $this, 'show_form' ),
        );

        return $tabs;
    }

    /**
    * Settings for guest users
    *
    * @since 0.2
    *
    * @return void
    **/
    public function guest_user_settings( $settings_fields ) {
        $settings_fields['enable_guest_user_enquiry'] = array(
            'name'    => 'enable_guest_user_enquiry',
            'label'   => __( 'Guest Product Enquiry', 'dokan' ),
            'desc'    => __( 'Enable/Disable product enquiry for guest user', 'dokan' ),
            'type'    => 'checkbox',
            'default' => 'on',
            'tooltip' => __( 'When checked, user can inquire about products from the product page without signing in.', 'dokan' ),
        );

        return $settings_fields;
    }

    /**
     * Show enquiry form in single product page tab
     *
     * @since  0.1
     *
     * @return void
     */
    public function show_form() {
        global $post;
        $guest_enquiry = dokan_get_option( 'enable_guest_user_enquiry', 'dokan_selling', 'on' );
        ?>

        <h3 style="margin-bottom: 25px;"><?php esc_html_e( 'Product Enquiry', 'dokan' ); ?></h3>

        <div class="row">
            <div class="col-md-10">
                <form id="dokan-product-enquiry" method="post" class="form" role="form">
                    <?php if ( ! is_user_logged_in() ) { ?>
                        <div class="row">
                            <?php if ( $guest_enquiry == 'off' ) : ?>
                                <div class="col-xs-12 col-md-12 form-group">
                                    <?php esc_html_e( 'Please Login to make enquiry about this product', 'dokan' ); ?>
                                </div>
                                <div class="col-xs-12 col-md-12 form-group">
                                    <a class="btn btn-success btn-green btn-flat btn-lg " href="<?php echo add_query_arg( array( 'redirect_to' => get_permalink( $post->ID ) ), wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Login', 'dokan' ); ?></a>
                                </div>
                            <?php else : ?>
                                <div class="col-xs-6 col-md-6 form-group">
                                    <input class="form-control" id="name" name="author" placeholder="<?php esc_html_e( 'Your Name', 'dokan' ); ?>" type="text" required/>
                                </div>

                                <div class="col-xs-6 col-md-6 form-group">
                                    <input class="form-control" id="email" name="email" placeholder="you@example.com" type="email" required />
                                </div>

                                <input type="url" name="url" value="" style="display:none">
                            <?php endif ?>
                        </div>
                    <?php } ?>
                    <?php if ( $guest_enquiry == 'on' || is_user_logged_in() ) : ?>
                        <div class="form-group">
                            <textarea class="form-control" id="dokan-enq-message" name="enq_message" placeholder="<?php esc_html_e( 'Details about your enquiry...', 'dokan' ); ?>" rows="5" required></textarea>
                        </div>

                        <?php do_action( 'dokan_product_enquiry_after_form' ); ?>

                        <?php wp_nonce_field( 'dokan_product_enquiry', 'dokan_product_enquiry_nonce' ); ?>
                        <input type="hidden" name="dokan_product_enquiry_recaptcha_token" class="dokan_recaptcha_token">
                        <input type="hidden" name="enquiry_id" value="<?php echo esc_attr( $post->ID ); ?>">
                        <input type="hidden" name="seller_id" value="<?php echo esc_attr( $post->post_author ); ?>">
                        <input type="hidden" name="action" value="dokan_product_enquiry">

                        <input class="dokan-btn dokan-btn-theme" type="submit" value="<?php esc_html_e( 'Submit Enquiry', 'dokan' ); ?>">
                    <?php endif ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Add email class
     *
     * @param array $classes
     *
     * @return array
     */
    public function add_email_class( $classes ) {
        require_once DOKAN_ENQUIRY_INC . '/dokan-product-enquiry-email.php';

        $classes['Dokan_Product_Enquiry_Email'] = new \Dokan_Product_Enquiry_Email();

        return $classes;
    }

    /**
     * Add email template file
     *
     * @param array $templare_files
     *
     * @return array
     */
    public function add_email_template_file( $template_files ) {
        $template_files[] = 'product-enquiry-email-html.php';

        return $template_files;
    }

    /**
     * Add eamil aciton
     *
     * @param array $actions
     *
     * @return array
     */
    public function add_email_action( $actions ) {
        $actions[] = 'dokan_send_enquiry_email';

        return $actions;
    }
}
