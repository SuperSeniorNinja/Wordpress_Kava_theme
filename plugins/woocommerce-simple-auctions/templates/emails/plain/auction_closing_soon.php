<?php
/**
 * Email notification template (plain) for auctions closing soon.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

$product_data = wc_get_product( $product_id );

echo $email_heading . "\n\n";

printf(
        esc_html__("Auction %s is going to is going to be closed at %s. Current bid is %s", 'wc_simple_auctions'),
        $product_data -> get_title(), date_i18n( get_option( 'date_format' ),
        strtotime( $product_data->get_auction_end_time() )).' '.date_i18n( get_option( 'time_format' ),
        strtotime( $product_data->get_auction_end_time() )),
        wc_price( $product_data -> get_curent_bid() )
    );

echo "\n\n";
echo get_permalink($product_id);
echo "\n\n";
echo "\n\n";
echo esc_html__("To unsubscribe from ending soon emails click on link below", 'wc_simple_auctions') ;
echo get_permalink( get_option('woocommerce_myaccount_page_id')).'/auctions-endpoint/';
echo "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
