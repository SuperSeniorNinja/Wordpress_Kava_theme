<?php
/**
 * Auction dates template
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global  $product, $post;
$gmt_offset = get_option( 'gmt_offset' ) > 0 ? '+' . get_option( 'gmt_offset' ) : get_option( 'gmt_offset' );
$dateformat = get_option( 'date_format' );
$timeformat = get_option( 'time_format' );

if ( $product->get_type() !== 'auction' ) {
	return;
}
if ( ( $product->is_closed() === false ) && ( $product->is_started() === true ) ) : ?>
<p class="auction-end"><?php echo apply_filters( 'time_left_text', esc_html__( 'Auction ends:', 'wc_simple_auctions' ), $product ); ?> <?php echo date_i18n( $dateformat, strtotime( $product->get_auction_end_time() ) ); ?>  <?php echo date_i18n( $timeformat, strtotime( $product->get_auction_end_time() ) ); ?> <br />
	<?php printf( esc_html__( 'Timezone: %s', 'wc_simple_auctions' ), get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : esc_html__( 'UTC ', 'wc_simple_auctions' ) . $gmt_offset ); ?>
</p>
	<?php
elseif ( ( $product->is_closed() === false ) && ( $product->is_started() === false ) ) :
	?>
	<p class="auction-starts"><?php echo apply_filters( 'time_text', esc_html__( 'Auction starts:', 'wc_simple_auctions' ), $product->get_id() ); ?> <?php echo date_i18n( $dateformat, strtotime( $product->get_auction_start_time() ) ); ?>  <?php echo date_i18n( $timeformat, strtotime( $product->get_auction_start_time() ) ); ?></p>
	<p class="auction-end"><?php echo apply_filters( 'time_text', esc_html__( 'Auction ends:', 'wc_simple_auctions' ), $product->get_id() ); ?> <?php echo date_i18n( $dateformat, strtotime( $product->get_auction_end_time() ) ); ?>  <?php echo date_i18n( $timeformat, strtotime( $product->get_auction_end_time() ) ); ?> </p>
	<?php
endif;
