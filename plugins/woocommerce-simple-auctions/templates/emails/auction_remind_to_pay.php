<?php
/**
 * Customer remind to pay email
 *
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$product_data = wc_get_product($product_id);
?>

<?php do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf(wp_kses_post( __("Congratulations. You have won the auction for <a href='%s'>%s</a>. Your bid was: %s. Please click on this link to pay for your auction %s ", 'wc_simple_auctions') ), get_permalink($product_id), $product_data -> get_title(), wc_price($current_bid), '<a href="' . esc_attr(add_query_arg("pay-auction",$product_id, $checkout_url)). '">' . esc_html__('payment', 'wc_simple_auctions') . '</a>'); ?></p>



<?php do_action('woocommerce_email_footer', $email); ?>