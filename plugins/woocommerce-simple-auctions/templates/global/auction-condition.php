<?php
/**
 * Auction condition template
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global  $product, $post;

if ( $product->get_type() !== 'auction'){
 return;
}
?>
<p class="auction-condition">
	<?php echo apply_filters('conditiond_text', esc_html__( 'Item condition:', 'wc_simple_auctions' ), $product); ?>
	<span class="curent-bid"><?php esc_html_e( $product->get_condition() , 'wc_simple_auctions' ) ?></span>
</p>
