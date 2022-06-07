<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;

defined( 'ABSPATH' ) || exit;

class Messenger {
    const VERSION = 'v9.0';

    public function __construct() {
        add_action( 'init', [ $this, 'register_shortcode' ] );

        // chat button on vendor store page
        add_action( 'dokan_after_store_tabs', [ $this, 'render_live_chat_button' ] );

        // chat button on product page
        add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'render_live_chat_button_product_page' ] );
        add_action( 'dokan_product_seller_tab_end', array( $this, 'render_live_chat_button_product_tab' ), 10, 2 );
    }

    public function register_shortcode() {
        add_shortcode( 'dokan-live-chat-messenger', [ $this, 'shortcode' ] );
    }

    public function shortcode( $atts ) {
        $page_id = ! empty( $atts['page_id'] ) ? $atts['page_id'] : '';

        if ( ! $page_id ) {
            return;
        }

        $this->enqueue_messenger_js();
        $this->enqueue_chat_js();
        ?>
            <div
                class="dokan-hide fb-customerchat"
                theme_color="<?php echo esc_attr( AdminSettings::get_theme_color() ); ?>"
                attribution=setup_tool page_id="<?php echo esc_attr( $page_id ); ?>"
            >
            </div>
        <?php
    }

    public function enqueue_messenger_js() {
        ?>
        <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
            fjs.parentNode.insertBefore(js, fjs);

        }(document, 'script', 'facebook-jssdk'));
        </script>
        <?php
    }

    public function enqueue_chat_js() {
        ?>
        <script>
            jQuery( 'button.dokan-live-chat-messenger, .dokan-store-live-chat-btn' ).on( 'click', function( e ) {
                e.preventDefault();

                if (typeof FB === 'undefined') {
                    dokan_sweetalert( dokan.i18n_chat_message, { 
                        icon: 'error',
                    } );
                    return;
                }

                if (window.DokanFBChatLoaded !== undefined) {
                    FB.CustomerChat.showDialog();
                    return;
                }

                window.fbAsyncInit = function() {
                    FB.init({
                        xfbml: true,
                        version: '<?php echo self::VERSION; ?>',
                    });

                    FB.Event.subscribe('customerchat.load', function() {
                        window.DokanFBChatLoaded = true;
                    });
                };

                window.fbAsyncInit();
            });
        </script>
        <?php
    }

    public function render_live_chat_button( $vendor_id ) {
        if ( ! AdminSettings::show_chat_on_store_page() ) {
            return;
        }

        $store   = dokan()->vendor->get( $vendor_id )->get_shop_info();
        $page_id = ! empty( $store['fb_page_id'] ) ? $store['fb_page_id'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $page_id ) {
            return;
        }
        ?>
        <li class="dokan-store-support-btn-wrap dokan-right">
            <button class="dokan-btn dokan-btn-theme dokan-btn-sm dokan-live-chat dokan-live-chat-messenger" style="position: relative; top: 3px">
                <?php echo esc_html__( 'Chat Now', 'dokan' ); ?>
            </button>
            <?php echo do_shortcode( sprintf( '[dokan-live-chat-messenger page_id="%s"]', $page_id ) ); ?>
        </li>
        <?php
    }

    public function render_live_chat_button_product_page() {
        if ( ! AdminSettings::show_chat_above_product_tab() ) {
            return;
        }

        $product_id = get_the_ID();
        $seller_id  = get_post_field( 'post_author', $product_id );
        $store      = dokan()->vendor->get( $seller_id )->get_shop_info();
        $page_id = ! empty( $store['fb_page_id'] ) ? $store['fb_page_id'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $page_id ) {
            return;
        }

        echo sprintf( '<button style="margin-left: 5px;" class="dokan-live-chat dokan-live-chat-messenger button alt">%s</button>', __( 'Chat Now', 'dokan' ) );
        echo do_shortcode( sprintf( '[dokan-live-chat-messenger page_id="%s"]', $page_id ) );
    }

    public function render_live_chat_button_product_tab( $vendor, $store ) {
        if ( ! AdminSettings::show_chat_on_product_tab() ) {
            return;
        }

        $page_id = ! empty( $store['fb_page_id'] ) ? $store['fb_page_id'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $page_id ) {
            return;
        }

        echo sprintf( '<button style="margin-left: 5px;" class="dokan-live-chat dokan-live-chat-messenger button alt">%s</button>', __( 'Chat Now', 'dokan' ) );
        echo do_shortcode( sprintf( '[dokan-live-chat-messenger page_id="%s"]', $page_id ) );
    }

    /**
     * Get chat provider name
     *
     * @since 3.0.3
     *
     * @return string
     */
    public function get_name() {
        return 'messenger';
    }
}
