<?php
/**
 * Auction pay
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product, $post;

if(!(method_exists( $product, 'get_type') && $product->get_type() == 'auction')){
	return;
}

$user_id = get_current_user_id();

if ( ($user_id == $product->get_auction_current_bider() && $product->get_auction_closed() == '2' && !$product->get_auction_payed() ) ) :
?>

    <p><?php esc_html_e('Congratulations you have won this auction!', 'wc_simple_auctions') ?></p>
    
    <?php if(!($product->get_auction_type() == 'reverse' && get_option('simple_auctions_remove_pay_reverse') == 'yes')) { ?>

    	<p><a href="<?php echo apply_filters( 'woocommerce_simple_auction_pay_now_button', esc_attr( add_query_arg( "pay-auction", $product->get_id(), simple_auction_get_checkout_url() ) ) ); ?>" class="button"><?php esc_html_e('Pay Now', 'wc_simple_auctions') ?></a></p>

    <?php } ?>	

<?php endif; ?>