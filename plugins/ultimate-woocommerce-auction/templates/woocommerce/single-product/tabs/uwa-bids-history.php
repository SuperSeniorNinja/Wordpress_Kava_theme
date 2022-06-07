<?php
/**
 * Auction history tab
 * 
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit; 

global $woocommerce, $post, $product;
$datetimeformat = get_option('date_format').' '.get_option('time_format');
$heading = apply_filters('ultimate_woocommerce_auction_total_bids_heading', __( 'Total Bids Placed:', 'ultimate-woocommerce-auction' ) );
$current_bidder = $product->get_woo_ua_auction_current_bider();
?>
<h2><?php echo esc_html($heading); ?></h2>

<?php if(($product->is_woo_ua_closed() === TRUE ) and ($product->is_woo_ua_started() === TRUE )) : ?>
    
	<p><?php _e('Auction has expired', 'ultimate-woocommerce-auction') ?></p>
	<?php if ($product->get_woo_ua_auction_fail_reason() == '1'){
		 _e('Auction Expired because there were no bids', 'ultimate-woocommerce-auction');
	} elseif($product->get_woo_ua_auction_fail_reason() == '2'){
		_e('Auction expired without reaching reserve price', 'ultimate-woocommerce-auction');
	}
	
	if($product->get_woo_ua_auction_closed() == '3'){?>
		<p><?php _e('Product sold for buy now price', 'ultimate-woocommerce-auction') ?>: <span><?php echo wc_price($product->get_regular_price()) ?></span></p>
	<?php }elseif($product->get_woo_ua_auction_current_bider()){ ?>
		<p><?php _e('Highest bidder was', 'ultimate-woocommerce-auction') ?>: <span><?php echo esc_attr(uwa_user_display_name($current_bidder));?></span></p>
	<?php } ?>						
<?php endif; ?>	
<table id="auction-history-table-<?php echo esc_attr($product->get_id()); ?>" class="auction-history-table">
    <?php 
       $woo_ua_auction_history = $product->woo_ua_auction_history();	
        if ( !empty($woo_ua_auction_history) ): ?>
        <thead>
            <tr>
                <th><?php _e('Bidder Name', 'ultimate-woocommerce-auction')?></th>
				<th><?php _e('Bidding Time', 'ultimate-woocommerce-auction')?></th>
                <th><?php _e('Bid', 'ultimate-woocommerce-auction') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php 
            foreach ($woo_ua_auction_history as $history_value) { ?>
			<tr>
                <td class="bid_username"><?php echo esc_attr(uwa_user_display_name($history_value->userid));?></td>
				<td class="bid_date"><?php echo esc_attr(mysql2date($datetimeformat ,$history_value->date))?></td>
				<td class="bid_price"><?php echo wp_kses_post(wc_price($history_value->bid));?></td>
           </tr>
      <?php } ?> 
        </tbody>
    <?php endif;?>
        
	<tr class="start">
        <?php 
		$start_date = $product->get_woo_ua_auction_start_time(); ?>
		<?php if ($product->is_woo_ua_started() === TRUE) { ?>
		<td class="started"><?php esc_html_e('Auction started', 'ultimate-woocommerce-auction');?>
		<?php }   else { ?>									
		<td  class="started"><?php esc_html_e('Auction starting', 'ultimate-woocommerce-auction');?>		
		<?php } ?></td>	
		<td colspan="3"  class="bid_date"><?php echo esc_attr(mysql2date($datetimeformat,$start_date))?></td>							
	</tr>
</table>