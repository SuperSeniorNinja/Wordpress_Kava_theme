<?php

use WeDevs\DokanPro\Modules\ProductAdvertisement\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * @since 3.5.0
 *
 * @var int $product_id
 * @var int $vendor_id
 * @var bool $already_advertised
 * @var bool $can_advertise_for_free
 * @var string $expire_date
 * @var float $listing_price
 * @var bool|\DokanPro\Modules\Subscription\SubscriptionPack $subscription_status
 * @var int $remaining_slot if subscription exists, this will get remaining slot form package, otherwise from global settings
 * @var int $subscription_remaining_slot
 * @var int $expires_after_days if subscription exists, this will get remaining slot form package, otherwise from global settings
 * @var int $subscription_expires_after_days
 * @var string $post_status
 */
?>
<?php do_action( 'dokan_product_edit_before_product_advertisement', $product_id ); ?>

<style>
    .product-edit-new-container .dokan-proudct-advertisement {
        margin-bottom: 20px;
    }
</style>

<div class="dokan-edit-row dokan-proudct-advertisement dokan-clearfix">
    <div class="dokan-section-heading">
        <h2>
            <span class="fa-stack fa-xs tips">
                <i class="fa fa-circle fa-stack-2x" style="color:tomato; font-size: 2em;"></i>
                <i class="fa fa-bullhorn fa-stack-1x fa-inverse" data-fa-transform="shrink-6"></i>
            </span>
            <?php esc_html_e( 'Advertise Product', 'dokan' ); ?>
        </h2>
        <p><?php esc_html_e( 'Manage Advertisement for this product', 'dokan' ); ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fa fa-sort-desc fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <?php
        /**
         * 1. Check if product is status is published, if not, user will not be able to advertise this product.
         * 2. check if this product is already advertised, in that case, display expire date
         * 3. If product is not advertised:
         * a) check vendor subscription exists, and if available slot is greater than zero, user can advertise this product for free
         * b) if no subscription exists:
         *   i) global available slot is greater than zero and listing price is zero, user can can advertise this for free
         *   ii) if global available slot is greater than zero and listing price is greater than zero, user have to purchase this advertisement
         * c) otherwise vendor will not be able to advertise their products
         */
        ?>
        <?php if ( 'publish' !== $post_status && true !== $already_advertised ) : ?>
            <p>
                <?php printf( esc_html__( 'You can not advertise this product. Product needs to be published before you can advertise.', 'dokan' ) ); ?>
            </p>
        <?php elseif ( true === $already_advertised ) : ?>
            <label for="dokan_advertise_single_product">
                <input type="checkbox" id="dokan_advertise_single_product" name="dokan_advertise_single_product" value="on" checked="checked" disabled="disabled"" />
                <?php
                // translators: 1) localized date
                echo sprintf( __( 'Product advertisement is currently ongoing. Advertisement will end on: <strong>%s</strong>', 'dokan' ), $expire_date );
                ?>
            </label>
        <?php elseif ( $can_advertise_for_free ) : // ! empty( $remaining_slot ) will filter out 0(zero) and false value ?>
            <label for="dokan_advertise_single_product">
                <input type="checkbox"
                        id="dokan_advertise_single_product"
                        name="dokan_advertise_single_product"
                        value="off"
                        data-product-id="<?php echo esc_attr( $product_id ); ?>" />
                <?php
                printf(
                // translators: 1) remaining advertisement slot
                    __( 'You can advertise this product for free. Expire after <strong>%1$s</strong>, Remaining slot: <strong>%2$s</strong>', 'dokan' ),
                    Helper::format_expire_after_days_text( $expires_after_days ), Helper::get_formatted_remaining_slot_count( $remaining_slot )
                );
                ?>
            </label>
        <?php elseif ( ! empty( $remaining_slot ) ) : ?>
            <label for="dokan_advertise_single_product">
                <input type="checkbox"
                        id="dokan_advertise_single_product"
                        name="dokan_advertise_single_product"
                        value="off"
                        data-product-id="<?php echo intval( $product_id ); ?>"
                />
                <?php
                printf(
                    // translators: 1) advertisement expires after days 2) advertisement listing price html
                    __( 'Advertise this product for: <strong>%1$s</strong>, Advertisement Cost: <strong>%2$s</strong>, Remaining slot: <strong>%3$s</strong>', 'dokan' ),
                    Helper::format_expire_after_days_text( $expires_after_days ), wc_price( $listing_price ), Helper::get_formatted_remaining_slot_count( $remaining_slot )
                );
                ?>
            </label>
        <?php else : ?>
            <p>
                <?php printf( esc_html__( 'No advertisement slot is available. Please contact with site admin for further query.', 'dokan' ) ); ?>
            </p>
        <?php endif; ?>

        <div class="dokan-clearfix"></div>
    </div>
</div>

<?php do_action( 'dokan_product_edit_after_product_advertisement', $product_id ); ?>
