<?php
namespace DokanPro\Modules\Subscription;

use WeDevs\Dokan\Traits\Singleton;
use DokanPro\Modules\Subscription\Helper;

defined( 'ABSPATH' ) || exit;

class ProductStatusChanger {

    use Singleton;

    /**
     * Boot method
     *
     * @since 2.9.13
     */
    protected function boot() {
        $this->hooks();
    }

    /**
     * Init hooks
     *
     * @since 2.9.13
     */
    protected function hooks() {
        add_filter( 'dokan_bulk_product_statuses', [ $this, 'product_statuses' ] );
        add_action( 'dokan_bulk_product_status_change', [ $this, 'publish_products' ], 10, 2 );
        add_action( 'dokan_product_listing_filter_from_end', [ $this, 'product_filter_form' ] );
        add_filter( 'dokan_pre_product_listing_args', [ $this, 'filter_products' ], 15, 2 );
        add_action( 'dokan_vendor_purchased_subscription', [ $this, 'change_product_status' ] );
    }

    /**
     * Add product status filter
     *
     * @since 2.9.13
     *
     * @param array $statuses
     *
     * @return array
     */
    public function product_statuses( $statuses ) {
        if ( $this->maybe_hide_the_form() ) {
            return $statuses;
        }

        $statuses['publish'] = __( 'Publish Products', 'dokan' );

        return $statuses;
    }

    /**
     * Publish products
     *
     * @since 2.9.13
     *
     * @param  string $action
     * @param  array $product_ids
     *
     * @return void
     */
    public function publish_products( $action, $product_ids ) {
        if ( 'publish' !== $action || empty( $product_ids ) ) {
            return;
        }

        global $wpdb;
        $vendor_id                    = dokan_get_current_user_id();
        $product_status_after_end     = dokan_get_option( 'product_status_after_end', 'dokan_product_subscription', 'draft' );

        if ( ! Helper::get_vendor_remaining_products( $vendor_id ) ) {
            return;
        }

        if ( Helper::vendor_can_publish_unlimited_products( $vendor_id ) ) {
            $product_where = sprintf( " AND ID IN ('%s')", implode( "', '", $product_ids ) );

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->posts} SET post_status = 'publish'
                WHERE post_author = %d
                {$product_where} AND post_status != 'publish' AND post_status != 'pending'",
                    $vendor_id
                )
            );

            return;
        }

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );

            if ( ! $product || $product_status_after_end !== $product->get_status() ) {
                continue;
            }

            if ( Helper::get_vendor_remaining_products( $vendor_id ) ) {
                $product->set_status( 'publish' );
                $product->save();
            }
        }
    }

    /**
     * Product filtering form
     *
     * @since 2.9.13
     *
     * @param  array $get_data
     *
     * @return void
     */
    public function product_filter_form( $get_data ) {
        if ( $this->maybe_hide_the_form() ) {
            return;
        }

        $selected = ! empty( $get_data['filter_by_other'] ) ? $get_data['filter_by_other'] : '';
        $filters  = apply_filters( 'dokan_get_other_product_filters', [
            'featured'     => __( 'Featured', 'dokan' ),
            'top_rated'    => __( 'Top Rated', 'dokan' ),
            'best_selling' => __( 'Best Selling', 'dokan' ),
        ] );
        ?>
        <div class="dokan-form-group">
            <select name="filter_by_other" class="dokan-form-control">
                <option selected="selected" value="-1"><?php esc_attr_e( '- Select Filter -', 'dokan' ); ?></option>
                <?php foreach ( $filters as $key => $filter ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key ); ?>>
                        <?php echo esc_attr( $filter ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Filter best selling products
     *
     *@since 2.9.13
     *
     * @param  array $args
     * @param  array $get_data
     *
     * @return array
     */
    public function filter_products( $args, $get_data ) {
        if ( ! isset( $get_data['filter_by_other'] ) ) {
            return $args;
        }

        if ( 'best_selling' === $get_data['filter_by_other'] ) {
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';

            $this->set_default_tax_query( $args );
        }

        if ( 'top_rated' === $get_data['filter_by_other'] ) {
            $this->set_default_tax_query( $args );

            add_filter( 'posts_clauses', [ 'WC_Shortcodes', 'order_by_rating_post_clauses' ] );
        }

        if ( 'featured' === $get_data['filter_by_other'] ) {
            $this->set_default_tax_query( $args );

            $product_visibility_term_ids = wc_get_product_visibility_term_ids();
            $args['tax_query'][]         = [
                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => $product_visibility_term_ids['featured'],
            ];
        }

        return $args;
    }

    /**
     * Set default tax query
     *
     * @since 2.9.13
     *
     * @param array $args
     *
     * @return array
     */
    public function set_default_tax_query( $args ) {
        $product_visibility_term_ids = wc_get_product_visibility_term_ids();

        $args['tax_query'][] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
            'operator' => 'NOT IN',
        ];

        return $args;
    }

    /**
     * Maybe hide the form fields when vendor has reached the product uploading limit
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public function maybe_hide_the_form() {
        if ( ! Helper::get_vendor_remaining_products( dokan_get_current_user_id() ) ) {
            return true;
        }

        return false;
    }

    /**
     * Change product status on subscription purchased
     *
     * @since 2.9.13
     *
     * @param int $vendor_id
     *
     * @return void
     */
    public function change_product_status( $vendor_id ) {
        if ( ! Helper::get_vendor_remaining_products( $vendor_id ) ) {
            Helper::make_product_draft( $vendor_id );
        }

        if ( Helper::vendor_can_publish_unlimited_products( $vendor_id ) ) {
            Helper::make_product_publish( $vendor_id );
        }

        // delete user meta after vendor purchased a subscription
        delete_user_meta( $vendor_id, 'dokan_vendor_subscription_cancel_email' );
    }
}

ProductStatusChanger::instance();
