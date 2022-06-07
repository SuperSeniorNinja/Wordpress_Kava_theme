<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend;

use WeDevs\DokanPro\Modules\ProductAdvertisement\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Product
 *
 * @since 3.5.0
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 */
class Product {
    /**
     * Product constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        // add new column under vendor dashboard's product listing page
        add_action( 'dokan_product_list_table_after_status_table_header', [ $this, 'product_listing_table_column' ], 1 );

        // featured column value
        add_action( 'dokan_product_list_table_after_status_table_data', [ $this, 'product_listing_table_content' ], 1, 2 );

        // render advertise product section under single product edit page
        add_action( 'dokan_product_edit_after_options', [ $this, 'render_advertise_product_section' ], 99, 1 );

        // load frontend scripts
        add_action( 'wp_enqueue_scripts', [ $this, 'load_product_scripts' ], 10 );
    }

    /**
     *
     * @since 3.5.0
     *
     * @param int $post_id
     *
     * @return void
     */
    public function render_advertise_product_section( $post_id ) {
        // check permission, don't let vendor staff view this section
        if ( ! current_user_can( 'dokandar' ) ) {
            return;
        }

        // check if purchasing advertisement settings is enabled
        if ( ! Helper::is_per_product_advertisement_enabled() && ! Helper::is_enabled_for_vendor_subscription() ) {
            return;
        }

        $advertisement_data = Helper::get_advertisement_data_by_product( $post_id );

        if ( empty( $advertisement_data ) ) {
            return;
        }

        // load template
        dokan_get_template_part(
            'product-advertisement-content', '', array_merge(
                [
                    'is_product_advertisement' => true,
                    'product_id'          => $post_id,
                ],
                $advertisement_data
            )
        );
    }

    /**
     * This method  will print advertisement row data
     *
     * @since 3.5.0
     *
     * @param \WP_Post $post
     * @param \WC_Product $product
     *
     * @return void
     */
    public function product_listing_table_content( $post, $product ) {
        // get advertisement data via product
        $advertisement_data = Helper::get_advertisement_data_by_product( $product->get_id() );

        $title = '';
        $class = '';
        $color  = 'slategrey';
        if ( $advertisement_data['already_advertised'] ) {
            // translators: 1) Localized data
            $title = sprintf( __( 'Expires on: %s', 'dokan' ), $advertisement_data['expire_date'] );
            $color  = 'tomato';
            $class = 'advertised';
        }

        echo <<<EOD
<td class='product-advertisement-td'>
     <span class='fa-stack fa-xs tips dokan-product-advertisement {$class}'
             style="cursor: pointer;"
             data-title='{$title}'
             data-already-advertised='{$class}'
             data-product-status='{$product->get_status()}'
             data-product-id='{$product->get_id()}'>
         <i class="fa fa-circle fa-stack-2x adv_icon_1" style="color:{$color}"></i>
         <i class='fa fa-stack-1x fa-bullhorn fa-inverse adv_icon_2'></i>
     </span>
 </td>
EOD;
    }

    /**
     * This method will add featured column under vendor dashboard product listing page
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function product_listing_table_column() {
        $title = __( 'Advertised Products', 'dokan' );
        echo <<<EOD
<th class="product-advertisement-th">
    <span class="fa-stack fa-xs tips" data-title='{$title}'>
        <i class="fa fa-circle fa-stack-2x" style="color:tomato"></i>
        <i class="fa fa-bullhorn fa-stack-1x fa-inverse" data-fa-transform="shrink-6"></i>
    </span>
</th>

EOD;
    }

    /**
     * Load frontend scripts
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function load_product_scripts() {
        global $wp;

        // target only frontend dashboard product list and edit page
        if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['products'] ) ) {
            wp_enqueue_script( 'dokan-product-adv-purchase' );

            // localize scripts
            $localized_data = [
                'advertise_alert'              => esc_html__( 'Are you sure you want to advertise this product?', 'dokan' ),
                'advertise_product_nonce'      => wp_create_nonce( 'dokan_advertise_product_nonce' ),
                'on_error_message'             => esc_html__( 'Something went wrong.', 'dokan' ),
                'on_success_message'           => esc_html__( 'Success.', 'dokan' ),
                'product_not_published'        => esc_html__( 'You can not advertise this product. Products needs to be published before you can advertise.', 'dokan' ),
                'on_load_advertisement_status' => esc_html__( 'Loading advertisement data. Please wait...', 'dokan' ),
                'checkout_url'                 => wc_get_checkout_url(),
                'ajaxurl'                      => admin_url( 'admin-ajax.php' ),

            ];
            wp_localize_script( 'dokan-product-adv-purchase', 'dokan_purchase_advertisement', $localized_data );
        }
    }
}
