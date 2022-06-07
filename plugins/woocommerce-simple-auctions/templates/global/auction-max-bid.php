<?php
/**
 * Auction max-bid template
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global  $product, $post;


if ( $product->get_type() !== 'auction' && $product->get_auction_proxy() !== 'yes' ) {
	return;
}
$current_user     = wp_get_current_user();
$user_max_bid = $product->get_user_max_bid( $product->get_id() , $current_user->ID );
$max_min_bid_text = $product->get_auction_type() == 'reverse' ? esc_html__( 'Your min bid is', 'wc_simple_auctions' ) : esc_html__( 'Your max bid is', 'wc_simple_auctions' );

if ($product->get_auction_sealed() != 'yes'){

	if ($product->get_auction_proxy() &&  $product->get_auction_max_current_bider() && get_current_user_id() == $product->get_auction_max_current_bider()) {?>

		<p class="max-bid"><?php  esc_html_e( $max_min_bid_text , 'wc_simple_auctions' ) ?> <?php echo wc_price($product->get_auction_max_bid()) ?>

	<?php }
} elseif($user_max_bid > 0){ ?>

	<p class="max-bid"><?php  esc_html_e( $max_min_bid_text , 'wc_simple_auctions' ) ?> <?php echo wc_price($user_max_bid) ?>

<?php }
