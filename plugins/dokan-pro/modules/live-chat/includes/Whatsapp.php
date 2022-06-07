<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Class Whatsapp
 * @package WeDevs\DokanPro\Modules\LiveChat
 *
 * @since 3.2.0
 *
 * @author weDevs
 */
class Whatsapp {
    /**
     * @var integer
     */
    private $seller_id;

    /**
     * Whatsapp constructor.
     *
     * @since 3.2.0
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_shortcode' ] );

        // chat button on vendor store page
        add_action( 'dokan_after_store_tabs', [ $this, 'render_live_chat_button' ] );

        // chat button on product page
        add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'render_live_chat_button_product_page' ] );
        add_action( 'dokan_product_seller_tab_end', [ $this, 'render_live_chat_button_product_tab' ], 10, 2 );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    /**
     * Register shortcode
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function register_shortcode() {
        add_shortcode( 'dokan-live-chat-whatsapp', [ $this, 'shortcode' ] );
    }

    /**
     * Create shortcode
     *
     * @param $atts
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function shortcode( $atts ) {
        $whatsapp_number = ! empty( $atts['number'] ) ? $atts['number'] : '';
        $this->seller_id = 0;

        if ( ! $whatsapp_number ) {
            return;
        }

        // If it's product page then get the store user from that product.
        if ( dokan_is_store_page() ) {
            // Get store user from dokan seller store page.
            $store_user      = dokan()->vendor->get( get_query_var( 'author' ) );
            $this->seller_id = $store_user->get_id();
        } elseif ( is_singular( 'product' ) ) {
            // Get store user from single product page.
            $this->seller_id = get_post_field( 'post_author', get_the_ID() );
        }

        $this->enqueue_chat_js();

        $url = $this->get_whatsapp_url( $whatsapp_number );

        ?>

        <a href="<?php echo $url; ?>" class="whatsapp-live-widget" target="_blank">
            <img src="<?php echo DOKAN_LIVE_CHAT_ASSETS; ?>/images/whatsapp.svg" alt="">
        </a>

        <?php
    }

    /**
     * Enqueue styles
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function enqueue_styles() {
        wp_enqueue_style( 'dokan-dashboard-live-chat', DOKAN_LIVE_CHAT_ASSETS . '/css/dashboard-livechat.css', [], DOKAN_PRO_PLUGIN_VERSION, 'all' );
    }

    /**
     * Enqueue custom chat js
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function enqueue_chat_js() {
        ?>
        <script>
            ;(function ($) {
                $('button.dokan-live-chat-whatsapp, .dokan-store-live-chat-btn').on('click', function (e) {
                    e.preventDefault();

                    document.querySelector("a.whatsapp-live-widget").click();
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Render live chat button
     *
     * @param $seller_id
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function render_live_chat_button( $seller_id ) {
        if ( ! AdminSettings::show_chat_on_store_page() ) {
            return;
        }

        $this->seller_id = $seller_id;
        $store           = dokan()->vendor->get( $seller_id )->get_shop_info();
        $whatsapp_number = ! empty( $store['whatsapp_number'] ) ? $store['whatsapp_number'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $whatsapp_number ) {
            return;
        }
        ?>
        <li class="dokan-store-support-btn-wrap dokan-right">
            <button class="dokan-btn dokan-btn-theme dokan-btn-sm dokan-live-chat dokan-live-chat-whatsapp"
                    style="position: relative; top: 3px">
                <?php echo esc_html__( 'Chat Now', 'dokan' ); ?>
            </button>

            <?php echo do_shortcode( sprintf( '[dokan-live-chat-whatsapp number="%s"]', $whatsapp_number ) ); ?>
        </li>
        <?php
    }

    /**
     * Render live chat button on product page
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function render_live_chat_button_product_page() {
        if ( ! AdminSettings::show_chat_above_product_tab() ) {
            return;
        }

        $this->render_on_product_single_page();
    }

    /**
     * Render live chat button on product tab
     *
     * @param $vendor
     * @param $store
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function render_live_chat_button_product_tab( $vendor, $store ) {
        if ( ! AdminSettings::show_chat_on_product_tab() ) {
            return;
        }

        $this->render_on_product_single_page();
    }

    /**
     * Get chat provider name
     *
     * @since 3.0.3
     *
     * @return string
     */
    public function get_name() {
        return 'whatsapp';
    }

    /**
     * Render on product single page
     *
     * @since 3.2.0
     *
     * @return void
     */
    private function render_on_product_single_page() {
        $product_id      = get_the_ID();
        $seller_id       = get_post_field( 'post_author', $product_id );
        $this->seller_id = $seller_id;
        $store           = dokan()->vendor->get( $seller_id )->get_shop_info();
        $whatsapp_number = ! empty( $store['whatsapp_number'] ) ? $store['whatsapp_number'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $whatsapp_number ) {
            return;
        }

        echo sprintf( '<button style="margin-left: 5px;" class="dokan-live-chat dokan-live-chat-whatsapp button alt">%s</button>', __( 'Chat Now', 'dokan' ) );

        echo do_shortcode( sprintf( '[dokan-live-chat-whatsapp number="%s"]', $whatsapp_number ) );
    }

    /**
     * Get whatsapp url filled with options from admin panel
     *
     * @param $whatsapp_number
     *
     * @since 3.2.0
     *
     * @return string
     */
    private function get_whatsapp_url( $whatsapp_number ) {
        $opening_pattern    = dokan_get_option( 'wa_opening_method', 'dokan_live_chat' );
        $pre_filled_message = $this->get_pre_filled_message();

        $sub_domain = 'in_browser' === $opening_pattern ? 'web' : 'api';
        $url        = "https://{$sub_domain}.whatsapp.com/send/?phone={$whatsapp_number}";

        if ( ! empty( $pre_filled_message ) ) {
            $url .= '&text=' . $pre_filled_message;
        }

        return $url;
    }

    /**
     * Get pre-filled message dynamically filled if variable is present
     *
     * @since 3.2.0
     *
     * @return string
     */
    public function get_pre_filled_message() {
        $pre_filled_message = dokan_get_option( 'wa_pre_filled_message', 'dokan_live_chat' );

        if ( empty( $pre_filled_message ) ) {
            return $pre_filled_message;
        }

        //parse the variables based on {}
        $context_count = preg_match_all( '/\{([^\%\}]+)\}/', $pre_filled_message, $variables );

        if ( $context_count > 0 ) {
            $variables = $variables[1];
            foreach ( $variables as $variable ) {
                $pre_filled_message = $this->replace_variables( $variable, $pre_filled_message );
            }
        }

        return $pre_filled_message;
    }

    /**
     * Replace variable with store value
     *
     * @param $variable
     * @param $message
     *
     * @since 3.2.0
     *
     * @return mixed
     */
    private function replace_variables( $variable, $message ) {
        if ( ! $this->seller_id ) {
            return $message;
        }

        $search = '{' . $variable . '}';

        switch ( $variable ) {
            case 'store_name':
                $store_info = dokan_get_store_info( $this->seller_id );
                $message    = str_replace( $search, $store_info['store_name'], $message );
                break;
            case 'store_url':
                $store_url = dokan_get_store_url( $this->seller_id );
                $message   = str_replace( $search, $store_url, $message );
                break;
        }

        return $message;
    }
}
