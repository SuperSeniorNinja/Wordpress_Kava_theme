<?php
/**
 * Email notification template (HTML) for auctions closing soon.
 *
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$product_data = wc_get_product($product_id);

do_action('woocommerce_email_header', $email_heading, $email);

?>

<p>
<?php
    printf(
        wp_kses_post( __("Auction <a href='%s'>%s</a> is going to be closed at %s. Current bid is %s", 'wc_simple_auctions') ),
            get_permalink($product_id), $product_data -> get_title(),  date_i18n( get_option( 'date_format' ),
            strtotime( $product_data->get_auction_end_time() )).' '.date_i18n( get_option( 'time_format' ),
            strtotime( $product_data->get_auction_end_time() )), wc_price($product_data -> get_curent_bid())
    );
?>
</p>
<p><small>
	<?php
	printf(
        wp_kses_post( __("To unsubscribe from ending soon emails <a href='%s'>click here</a>", 'wc_simple_auctions') ), get_permalink( get_option('woocommerce_myaccount_page_id')).'/auctions-endpoint/');
    ?></small>
</p>

<?php do_action('woocommerce_email_footer', $email); ?>
