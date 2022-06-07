<?php
/**
 * Auction countdown template
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global  $product, $post;

if ( $product->get_type() !== 'auction'){
 return;
}

if ( ( $product->is_closed() === FALSE ) && ($product->is_started() === TRUE ) ) : ?>
	<div class="auction-time" id="countdown"><?php echo apply_filters('time_text', esc_html__( 'Time left:', 'wc_simple_auctions' ), $product->get_id() ); ?> 
		<div class="main-auction auction-time-countdown" data-time="<?php echo $product->get_seconds_remaining() ?>" data-auctionid="<?php echo $product->get_id() ?>" data-format="<?php echo get_option( 'simple_auctions_countdown_format' ) ?>"></div>
	</div>

<?php 
elseif ( ( $product->is_closed() === FALSE ) && ($product->is_started() === FALSE ) ) :?>
	<div class="auction-time future" id="countdown"><?php echo apply_filters('auction_starts_text', esc_html__( 'Auction starts in:', 'wc_simple_auctions' ), $product); ?> 
		<div class="auction-time-countdown future" data-time="<?php echo $product->get_seconds_to_auction() ?>" data-format="<?php echo get_option( 'simple_auctions_countdown_format' ) ?>"></div>
	</div>
<?php endif; 
