<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Class tawkto
 * @package WeDevs\DokanPro\Modules\LiveChat
 *
 * @since 3.2.0
 *
 * @author weDevs
 */
class Tawkto {
    /**
     * tawkto constructor.
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
    }

    /**
     * Register shortcode
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function register_shortcode() {
        add_shortcode( 'dokan-live-chat-tawkto', [ $this, 'shortcode' ] );
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
        $tawkto_property_id = ! empty( $atts['property_id'] ) ? $atts['property_id'] : '';
        $tawkto_widget_id   = ! empty( $atts['widget_id'] ) ? $atts['widget_id'] : '';

        if ( ! $tawkto_property_id || ! $tawkto_widget_id ) {
            return;
        }

        $this->enqueue_tawkto_js( $tawkto_property_id, $tawkto_widget_id );
        $this->enqueue_chat_js();
    }

    /**
     * Enqueue tawkto js
     *
     * @param $property_id
     * @param $widget_id
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function enqueue_tawkto_js( $property_id, $widget_id ) {
        ?>
        <!--Start of Tawk.to Script-->
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();

            Tawk_API.onLoad = function () {
                Tawk_API.hideWidget();

                <?php
                if ( is_user_logged_in() ) {
                $customer = wp_get_current_user();
                ?>

                Tawk_API.setAttributes({
                    'name': '<?php echo $customer->display_name ?>',
                    'email': '<?php echo ! empty( $customer->user_email ) ? $customer->user_email : "fake@email.com"; ?>',
                    'hash': "<?php echo hash_hmac( 'sha256', strval( $customer->ID ), $property_id ); ?>"
                }, function (error) {
                });

                <?php
                }
                ?>
            };

            (function () {
                var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/<?php echo $property_id; ?>/<?php echo $widget_id; ?>';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
        <!--End of Tawk.to Script-->
        <?php
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
            jQuery('button.dokan-live-chat-tawkto, .dokan-store-live-chat-btn').on('click', function (e) {
                e.preventDefault();

                Tawk_API.toggleVisibility();
                Tawk_API.maximize();
            });
        </script>
        <?php
    }

    /**
     * Render live chat button
     *
     * @param $vendor_id
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function render_live_chat_button( $vendor_id ) {
        if ( ! AdminSettings::show_chat_on_store_page() ) {
            return;
        }

        $store            = dokan()->vendor->get( $vendor_id )->get_shop_info();
        $tawk_property_id = ! empty( $store['tawkto_property_id'] ) ? $store['tawkto_property_id'] : '';
        $tawk_widget_id   = ! empty( $store['tawkto_widget_id'] ) ? $store['tawkto_widget_id'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $tawk_property_id || ! $tawk_widget_id ) {
            return;
        }
        ?>
        <li class="dokan-store-support-btn-wrap dokan-right">
            <button class="dokan-btn dokan-btn-theme dokan-btn-sm dokan-live-chat dokan-live-chat-tawkto"
                    style="position: relative; top: 3px">
                <?php echo esc_html__( 'Chat Now', 'dokan' ); ?>
            </button>
            <?php echo do_shortcode( sprintf( '[dokan-live-chat-tawkto property_id="%s" widget_id="%s"]', $tawk_property_id, $tawk_widget_id ) ); ?>
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
        return 'tawkto';
    }

    /**
     * Render on product single page
     *
     * @since 3.2.0
     *
     * @return void
     */
    private function render_on_product_single_page() {
        $product_id       = get_the_ID();
        $seller_id        = get_post_field( 'post_author', $product_id );
        $store            = dokan()->vendor->get( $seller_id )->get_shop_info();
        $tawk_property_id = ! empty( $store['tawkto_property_id'] ) ? $store['tawkto_property_id'] : '';
        $tawk_widget_id   = ! empty( $store['tawkto_widget_id'] ) ? $store['tawkto_widget_id'] : '';

        if ( empty( $store['live_chat'] ) || $store['live_chat'] !== 'yes' || ! $tawk_property_id || ! $tawk_widget_id ) {
            return;
        }

        echo sprintf( '<button style="margin-left: 5px;" class="dokan-live-chat dokan-live-chat-tawkto button alt">%s</button>', __( 'Chat Now', 'dokan' ) );
        echo do_shortcode( sprintf( '[dokan-live-chat-tawkto property_id="%s" widget_id="%s"]', $tawk_property_id, $tawk_widget_id ) );
    }
}
