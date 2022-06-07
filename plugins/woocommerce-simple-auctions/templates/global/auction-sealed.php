<?php
/**
 * Auction sealed template
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $product, $post;


if ( $product->get_type() !== 'auction' ) {
	return;
}
if ( $product->get_auction_sealed() !== 'yes' ) {
	return;
} ?>
<p class="sealed-text"><?php echo apply_filters( 'sealed_bid_text', wp_kses_post( __( "This auction is <a href='#'>sealed</a>.", 'wc_simple_auctions' ) ) ); ?>
	<span class="sealed-bid-desc"><?php esc_html_e( 'In this type of auction all bidders simultaneously submit sealed bids so that no bidder knows the bid of any other participant. The highest bidder pays the price they submitted. If two bids with same value are placed for auction the one which was placed first wins the auction.', 'wc_simple_auctions' ); ?></span>
</p>
<?php
if ( ! empty( $product->get_auction_start_price() ) ) {
	?>
	<?php if ( $product->get_auction_type() == 'reverse' ) : ?>
			<p class="sealed-min-text"><?php echo apply_filters( 'sealed_min_text', sprintf( esc_html__( 'Maximum bid for this auction is %s.', 'wc_simple_auctions' ), wc_price( $product->get_auction_start_price() ) ) ); ?></p>
	<?php else : ?>
			<p class="sealed-min-text"><?php echo apply_filters( 'sealed_min_text', sprintf( esc_html__( 'Minimum bid for this auction is %s.', 'wc_simple_auctions' ), wc_price( $product->get_auction_start_price() ) ) ); ?></p>
	<?php endif; ?>
<?php }
