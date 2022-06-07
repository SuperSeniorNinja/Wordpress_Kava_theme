<?php
/**
 * Auction reserve template
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global  $product, $post;


if ( $product->get_type() !== 'auction' ) {
	return;
}
if ( $product->get_auction_sealed() === 'yes' || ! $product->is_reserved() ) {
	return;
}

if ( ( $product->is_reserved() === true ) && ( $product->is_reserve_met() === false ) ) : ?>
	<p class="reserve hold"  data-auction-id="<?php echo esc_attr( $product->get_id() ); ?>" ><?php echo apply_filters( 'reserve_bid_text', esc_html__( 'Reserve price has not been met', 'wc_simple_auctions' ) ); ?></p>
<?php endif; ?>	

<?php if ( ( $product->is_reserved() === true ) && ( $product->is_reserve_met() === true ) ) : ?>
	<p class="reserve free"  data-auction-id="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo apply_filters( 'reserve_met_bid_text', esc_html__( 'Reserve price has been met', 'wc_simple_auctions' ) ); ?></p>
<?php endif;
