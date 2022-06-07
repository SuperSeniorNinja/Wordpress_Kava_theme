<?php
/**
 * Ultimate Auction For WooCommerce Cron Setting Page 
 *
 * @author   WooThemes
 * @category Admin
 * @package  WooCommerce/Admin
 * @version  2.4.0
 */   
 
 if(isset($_POST['uwa-settings-submit']) == 'Save Changes')
	{
		
		// Cron Setting Section
		if (isset($_POST['uwa_cron_status_in'])) {
			update_option('woo_ua_cron_auction_status', absint($_POST['uwa_cron_status_in']));
		} 
		if (isset($_POST['uwa_cron_status_number'])) {
			update_option('woo_ua_cron_auction_status_number', absint($_POST['uwa_cron_status_number']));
		}
		
		// Auction Product Section		 
		$ajax_bid_enable  = isset($_POST['uwa_bid_ajax_enable']) ? sanitize_key($_POST['uwa_bid_ajax_enable']) : 'no';	
		if(!empty( $ajax_bid_enable )){
			update_option('woo_ua_auctions_bid_ajax_enable', $ajax_bid_enable);
		}
				
		if (isset($_POST['uwa_bid_ajax_interval'])) {
			update_option('woo_ua_auctions_bid_ajax_interval', absint($_POST['uwa_bid_ajax_interval']));
		
		}
		//shop Page
		$shop_enable  = isset($_POST['uwa_shop_enabled']) ? sanitize_key($_POST['uwa_shop_enabled']) : 'no';
		if(!empty( $shop_enable )){
			update_option('woo_ua_show_auction_pages_shop', $shop_enable);
		} 
		$search_enable  = isset($_POST['uwa_search_enabled']) ? sanitize_key($_POST['uwa_search_enabled']) : 'no';
		if(!empty( $search_enable )){
			update_option('woo_ua_show_auction_pages_search', $search_enable);
		} 
		$cat_enable  = isset($_POST['uwa_cat_enabled']) ? sanitize_key($_POST['uwa_cat_enabled']) : 'no';
		if(!empty( $cat_enable )){
			update_option('woo_ua_show_auction_pages_cat', $cat_enable);
		} 
		$tag_enable  = isset($_POST['uwa_tag_enabled']) ? sanitize_key($_POST['uwa_tag_enabled']) : 'no';		
		if(!empty( $tag_enable )){
			update_option('woo_ua_show_auction_pages_tag', $tag_enable);
		}
		$expired_enable  = isset($_POST['uwa_expired_enabled']) ? sanitize_key($_POST['uwa_expired_enabled']) : 'no';
		if(!empty( $expired_enable )){
			update_option('woo_ua_expired_auction_enabled', $expired_enable);
		} 
		
		$uwa_countdown_format  = isset( $_POST['uwa_countdown_format'] ) ? sanitize_text_field( $_POST['uwa_countdown_format'] ) : 'yowdHMS';
		if(!empty( $uwa_countdown_format )){
			update_option('woo_ua_auctions_countdown_format', $uwa_countdown_format);
		} 
		
		$hide_compact_enable  = isset($_POST['uwa_hide_compact_enable']) ? sanitize_key($_POST['uwa_hide_compact_enable']) : 'no';		
		if(!empty( $hide_compact_enable )){
			update_option('uwa_hide_compact_enable', $hide_compact_enable);
		} 
		
		
		$private_message  = isset($_POST['uwa_private_message']) ? sanitize_key($_POST['uwa_private_message']) : 'no';		
		if(!empty( $private_message )){
			update_option('woo_ua_auctions_private_message', $private_message);
		} 
		
		$bids_tab  = isset($_POST['uwa_bids_tab']) ? sanitize_key($_POST['uwa_bids_tab']) : 'no';		
		if(!empty( $bids_tab )){
			update_option('woo_ua_auctions_bids_section_tab', $bids_tab);
		} 
		
		$watchlists_tab  = isset($_POST['uwa_watchlists_tab']) ? sanitize_key($_POST['uwa_watchlists_tab']) : 'no';			
		if(!empty( $watchlists_tab )){
			update_option('woo_ua_auctions_watchlists', $watchlists_tab);
		} 	
		
		$owner_to_bid  = isset($_POST['uwa_allow_owner_to_bid']) ? sanitize_key($_POST['uwa_allow_owner_to_bid']) : 'no';			
		if(!empty( $owner_to_bid )){
			update_option('uwa_allow_owner_to_bid', $owner_to_bid);
		} 

		$admin_to_bid  = isset($_POST['uwa_allow_admin_to_bid']) ? sanitize_key($_POST['uwa_allow_admin_to_bid']) : 'no';		
		if(!empty( $admin_to_bid )){
			update_option('uwa_allow_admin_to_bid', $admin_to_bid);
		} 
		
		$bid_place_warning  = isset($_POST['uwa_enable_bid_place_warning']) ? sanitize_key($_POST['uwa_enable_bid_place_warning']) : 'no';			
		if(!empty( $bid_place_warning )){
			update_option('uwa_enable_bid_place_warning', $bid_place_warning);
		} 

			
	}
	
	
	// Cron Setting Section
	$uwa_cron_status_in = get_option('woo_ua_cron_auction_status', '2');
	$uwa_cron_status_number = get_option('woo_ua_cron_auction_status_number', '25');
	
	//Auction Section
	$uwa_bid_ajax_interval = get_option('woo_ua_auctions_bid_ajax_interval', '25');	
	
	$ajax_enable = get_option('woo_ua_auctions_bid_ajax_enable');
	//Shop Page   
	$expired_enable = get_option('woo_ua_expired_auction_enabled');
	$shop_enable = get_option('woo_ua_show_auction_pages_shop');	
	$search_enable = get_option('woo_ua_show_auction_pages_search');
	$cat_enable = get_option('woo_ua_show_auction_pages_cat');	
	$tag_enable = get_option('woo_ua_show_auction_pages_tag');	
	
	//Auction Detail Page
	$countdown_format = get_option('woo_ua_auctions_countdown_format');	   
	
	$private_tab_enable = get_option('woo_ua_auctions_private_message');	
	$bids_tab_enable = get_option('woo_ua_auctions_bids_section_tab');	
	$watchlists_tab_enable = get_option('woo_ua_auctions_watchlists');
	
	$compact_checked= get_option('uwa_hide_compact_enable');	
	$owner_bid_enable = get_option('uwa_allow_owner_to_bid',"no");	
	$admin_bid_enable = get_option('uwa_allow_admin_to_bid',"no");	
	$bid_warning = get_option('uwa_enable_bid_place_warning');
	
?>		
	
<div class="wrap" id="uwa_auction_setID">
	<div id='icon-tools' class='icon32'></br></div>
	
	<h2 class="uwa_main_h2"><?php esc_html_e( 'Ultimate Auction for WooCommerce', 'ultimate-woocommerce-auction' ); ?><span class="uwa_version_text"><?php esc_html_e( 'Version :', 'ultimate-woocommerce-auction' ); ?> <?php echo esc_attr(WOO_UA_VERSION); ?></span></h2>
	
	<div class="get_uwa_pro">

                 <!-- <a href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=horizontal banner&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url(WOO_UA_ASSETS_URL);?>/images/UWCA_row.jpg" alt="" /> </a>
                
    	<div class="clear"></div> -->
    	<?php
    	global $current_user;
				$user_id = $current_user->ID;
				/* If user clicks to ignore the notice, add that to their user meta */
				if (isset($_GET['uwa_pro_add_plugin_notice_ignore']) && '0' == absint($_GET['uwa_pro_add_plugin_notice_ignore'])) {
					update_user_meta($user_id, 'uwa_pro_add_plugin_notice_disable', 'true', true);
				}
				if (current_user_can('manage_options')) {
					$user_id = $current_user->ID;
					$user_hide_notice = get_user_meta( $user_id, 'uwa_pro_add_plugin_notice_disable', true );				
					if ($user_hide_notice != "true") {
													?>
					<div class="notice notice-info">
						<div class="get_uwa_pro" style="display:flex;justify-content: space-evenly;">
							<a href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=admin notice&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url(WOO_UA_ASSETS_URL);?>/images/UWCA_row.jpg" alt="" /> </a>
							<p class="uwa_hide_free">
							<?php
							//printf(__('<a href="%s">Hide Notice</a>', 'ultimate-woocommerce-auction'),esc_attr(add_query_arg('uwa_pro_add_plugin_notice_ignore', '0')));?>
							</p>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'uwa_pro_add_plugin_notice_ignore', '0' ), 'ultimate-woocommerce-auction', '_ultimate-woocommerce-auction_nonce' ) ); ?>" class="woocommerce-message-close notice-dismiss" style="position:relative;float:right;padding:9px 0px 9px 9px;text-decoration:none;"></a>									
							<div class="clear"></div>
						</div>
					</div>
						<?php
					}
				}	
				?>	
    </div>

 	
	<div id="uwa-auction-banner-text">	
	<?php esc_html__('If you like <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank"> our plugin working </a> with WooCommerce, please leave us a <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank">★★★★★</a> rating. A huge thanks in advance!', 'ultimate-woocommerce-auction' ); ?>	 
    </div>
	 <div class="uwa_setting_right">
		
			<div class="box_like_plugin">
				<div class="like_plugin">
						<h2 class="title_uwa_setting"><?php esc_html_e( 'Like this plugin?', 'ultimate-woocommerce-auction' ); ?></h2>
					<div class="text_uwa_setting">
						<div class="star_rating">
							<form class="rating">
							  <label>
							    <input type="radio" name="stars" value="yes" />
							    <span class="icon">★</span>
							  </label>
							  <label>
							    <input type="radio" name="stars" value="2" />
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							  </label>
							  <label>
							    <input type="radio" name="stars" value="3" />
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							    <span class="icon">★</span>   
							  </label>
							  <label>
							    <input type="radio" name="stars" value="4" />
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							  </label>
							  <label>
							    <input type="radio" name="stars" value="5" />
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							    <span class="icon">★</span>
							  </label>
							</form>
						</div>

						<div class="happy_img"> 
							<a target="_blank" href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post"> <img src="<?php echo esc_url(WOO_UA_ASSETS_URL);?>/images/we_just_need_love.png" alt="" /></a>
						</div>
					</div>
				</div>	
			</div>
			
				<div class="box_get_premium">
					<a href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=vertical banner&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url(WOO_UA_ASSETS_URL);?>/images/UWCA_col.jpg" alt="" /> </a>
			</div>
			
		
		</div>
	<div class="uwa_setting_left">
	
		<form  method='post' class='uwa_auction_setting_style'>

			<!-- beginning of the left meta box section -->
			<div id="wps-deals-misc" class="post-box-container">
				<div class="metabox-holder">	
				<div class="meta-box-sortables ui-sortable">
				<div id="general">					
					<div class="inside ">					
					<table class="form-table">
						<tbody>
						<tr>
						<th scope="row">
						<h2><?php esc_html_e( 'Auction Settings', 'ultimate-woocommerce-auction' ); ?></h2>
								
						</th>
						
						</tr>
						
							<tr>
								<tr>
								<th scope="row">
									<label for="uwa_cron_status_in"><?php esc_html_e( 'Check Auction Status:', 'ultimate-woocommerce-auction' ); ?></label>
								</th>
								<td>
									<?php esc_html_e( 'In every', 'ultimate-woocommerce-auction' ); ?>
									<input type="number" name="uwa_cron_status_in" class="regular-number" min="1" id="uwa_cron_status_in" 
									value="<?php echo esc_attr($uwa_cron_status_in); ?>"><?php esc_html_e( 'Minutes.', 'ultimate-woocommerce-auction' ); ?>
									</br>
									<div class="uwa-auction-settings-tip">
									<?php esc_html_e('A scheduler runs on an interval specified in this field in recurring manner.It checks, if some live auctions product can be expired and accordingly update their status.', 'ultimate-woocommerce-auction');
                                     ?>	
									</div>									 
								</td>
							   </tr>
							 
							   <tr>
								<th scope="row">
									<label for="uwa_cron_status_number"><?php esc_html_e( 'Auctions Processed Simultaneously:', 'ultimate-woocommerce-auction' ); ?></label>
								</th>
								
									<td>
									<?php esc_html_e( 'Process ', 'ultimate-woocommerce-auction' ); ?>
									<input type="number" name="uwa_cron_status_number" class="regular-number" min="1"
									id="uwa_cron_status_number" value="<?php echo esc_attr($uwa_cron_status_number); ?>"><?php esc_html_e( 'auctions per request.', 'ultimate-woocommerce-auction' ); ?>
									</br>
									<div class="uwa-auction-settings-tip">
									<?php esc_html_e('Number of auctions products Process per request.The scheduler processes the specified no. auctions whenever a schedule occurs.', 'ultimate-woocommerce-auction');
                                     ?>									
									
									<a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
		<span style="width: 500px; margin-left: -375px;">* <strong><?php esc_html_e( 'Note :', 'ultimate-woocommerce-auction' ); ?><strong> <ol>
										<li><?php esc_html_e( 'It is recommended to fill the above values in a balanced manner based upon the traffic, no. of auction products and no. of users on your site.', 'ultimate-woocommerce-auction' ); ?>
										</li>
										<li><?php esc_html_e( 'The less is the no. of auctions per request (fields 2 and 4 from above), the processing will be more optimized. If you are allowing so many auctions to be processed in each request, it can affect your site performance.', 'ultimate-woocommerce-auction' ); ?>
										</li>
										<li><?php esc_html_e( 'Similarly, you should also not set a very few no. of auction products since there may be delayed in expiry of some auction products and/or email notifications.', 'ultimate-woocommerce-auction' ); ?>
										</li>
										<li><?php esc_html_e( 'It is recommended not to keep on changing these values frequently as your auction products will be rescheduled every time you update the values.', 'ultimate-woocommerce-auction' ); ?>
										</li>										
									</ol>
		</span></a>	</div>
								</td>
							   </tr>
							   
							  
							  <tr>
								<tr>
								<th scope="row">
									<label for="uwa_bid_ajax_enable"><?php esc_html_e( 'Bidding Information:', 'ultimate-woocommerce-auction' ); ?></label>
								</th>
								<td>									
									<input type="checkbox" <?php checked($ajax_enable, 'yes'); ?> name="uwa_bid_ajax_enable" class="regular-number" id="uwa_bid_ajax_enable" value="yes"><?php esc_html_e( 'Enable Ajax update for latest bidding.', 'ultimate-woocommerce-auction' ); ?>
									</br>
									<div class="uwa-auction-settings-tip">
									<?php esc_html_e('Enables/disables ajax current bid checker (refresher) for auction - updates current bid value without refreshing page (increases server load, disable for best performance)', 'ultimate-woocommerce-auction');
                                     ?>	
								</div>									 
								</td>
							   </tr>
							 
							   <tr>
								<th scope="row">
									<label for="uwa_bid_ajax_interval"><?php esc_html_e( 'Check Bidding Info:', 'ultimate-woocommerce-auction' ); ?></label>
								</th>
								
									<td>
									<?php esc_html_e( 'In every', 'ultimate-woocommerce-auction' ); ?>
									<input type="number" name="uwa_bid_ajax_interval" class="regular-number" min="1" 
									id="uwa_bid_ajax_interval" value="<?php echo esc_attr($uwa_bid_ajax_interval); ?>"><?php esc_html_e( 'Second.', 'ultimate-woocommerce-auction' ); ?>
									</br>
									<div class="uwa-auction-settings-tip">
									<?php esc_html_e('Time interval between two ajax requests in seconds (bigger intervals means less load for server)', 'ultimate-woocommerce-auction');
                                     ?>									
									</div>							
								</td>
							   </tr>
							   
							  
							   
							   <tr>
					<th scope="row">
									<label for="uwa_bid_ajax_interval"><?php esc_html_e( 'Bidding Restriction:', 'ultimate-woocommerce-auction' ); ?></label>
								</th>
					<td class="uwaforminp">						
						<input type="checkbox" <?php checked($admin_bid_enable, 'yes'); ?> name="uwa_allow_admin_to_bid"  id="uwa_allow_admin_to_bid" value="yes">
						<?php esc_html_e('Allow Administrator to bid on their own auction.', 'ultimate-woocommerce-auction');  ?>
					</td>
				</tr>
				
				<tr>
					<th></th>
					<td class="uwaforminp">						
						<input type="checkbox" <?php checked($owner_bid_enable, 'yes'); ?> name="uwa_allow_owner_to_bid"  id="uwa_allow_owner_to_bid" value="yes">
					<?php esc_html_e('Allow Auction Owner (Seller/Vendor) to bid on their own auction.', 'ultimate-woocommerce-auction');  ?>								 
					</td>
				</tr>
							   
							   
							   
							   
							<tr >
							<td colspan="2">
							<h2 class="uwa_section_tr"><?php esc_html_e( 'Shop Page', 'ultimate-woocommerce-auction' ); ?></h2>						
							<span style='vertical-align: top;'><?php esc_html_e( 'The following options affect on frontend Shop Page.', 'ultimate-woocommerce-auction' ); ?></span>  
							</td>

							</tr>
							   
							 <tr>
							 <th scope="row"><?php esc_html_e( 'Auctions Display:', 'ultimate-woocommerce-auction' ); ?></th>
							 
							 <td>
							 <input <?php checked($expired_enable, 'yes'); ?> value="yes" name="uwa_expired_enabled" type="checkbox">
							  <?php esc_html_e( 'Show Expired Auctions.', 'ultimate-woocommerce-auction' ); ?>
							 </td>
							 </tr>
							   
							 
							<tr>
							 <th scope="row"><?php esc_html_e( 'Show Auctions on:', 'ultimate-woocommerce-auction' ); ?></th>							 
							 <td>
							 <input <?php checked($shop_enable, 'yes'); ?> value="yes" name="uwa_shop_enabled" type="checkbox">
							  <?php esc_html_e( 'On Shop Page.', 'ultimate-woocommerce-auction' ); ?>
							 </td>
							 </tr>
							<tr>
							 <th scope="row"></th>							 
							 <td>
							 <input <?php checked($search_enable, 'yes'); ?> value="yes" name="uwa_search_enabled" type="checkbox">
							 <?php esc_html_e( 'On Product Search Page.', 'ultimate-woocommerce-auction' ); ?>
							 </td>
							 </tr> 
							 
							<tr>
							 <th scope="row"></th>							 
							 <td>
							 <input <?php checked($cat_enable, 'yes'); ?> value="yes" name="uwa_cat_enabled" type="checkbox">
							  <?php esc_html_e( 'On Product Category Page.', 'ultimate-woocommerce-auction' ); ?>
							 </td>
							 </tr>  
	
							<tr>
							 <th scope="row"></th>							 
							 <td>
							 <input <?php checked($tag_enable, 'yes'); ?> value="yes" name="uwa_tag_enabled" type="checkbox"> <?php esc_html_e( 'On Product Tag Page.', 'ultimate-woocommerce-auction' ); ?>
							 </td>
							 </tr> 	
							<tr >
							
							   <td colspan="2">
							   <h2 class="uwa_section_tr"><?php esc_html_e( 'Auction Detail Page', 'ultimate-woocommerce-auction' ); ?></h2>
							   
								  
						<span style='vertical-align: top;'><?php esc_html_e( 'The following options affect on frontend Auction Detail page.', 'ultimate-woocommerce-auction' ); ?></span>  </td>
				  
							   </tr>    


<tr>
								<th scope="row">
									<label for="uwa_countdown_format"><?php esc_html_e( 'Countdown Format', 'ultimate-woocommerce-auction' ); ?></label>
								</th>
								
									<td>									
									<input type="text" name="uwa_countdown_format" class="regular-number" id="uwa_countdown_format" value="<?php echo esc_attr($countdown_format); ?>"><?php esc_html_e( 'The format for the countdown display. Default is yowdHMS', 'ultimate-woocommerce-auction' ); ?>
									<a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
		<span style="width: 500px; margin-left: -375px;"><?php esc_html_e("Use the following characters (in order) to indicate which periods you want to display: 'Y' for years, 'O' for months, 'W' for weeks, 'D' for days, 'H' for hours, 'M' for minutes, 'S' for seconds.	Use upper-case characters for mandatory periods, or the corresponding lower-case characters for optional periods, i.e. only display if non-zero. Once one optional period is shown, all the ones after that are also shown.", 'ultimate-woocommerce-auction');
                                     ?>	
		</span></a>	</div>
									</br>
															
								</td>
							   </tr>
						<tr>				
						<th scope="row">
							<label for="uwa_hide_compact_enable"><?php esc_html_e( 'Hide compact countdown', 'ultimate-woocommerce-auction' ); ?></label>
						</th>
						<td>
							<input <?php checked($compact_checked, 'yes'); ?> value="yes" name="uwa_hide_compact_enable" type="checkbox">
							<?php esc_html_e('Hide compact countdown format and display simple format.','ultimate-woocommerce-auction');?>	
					    </td>
					</tr>	 
							 
							 <tr>
							 <th scope="row"><?php esc_html_e( 'Enable Specific Sections:', 'ultimate-woocommerce-auction' ); ?></th>
							 
							 <td>
							<input <?php checked($private_tab_enable, 'yes'); ?> value="yes" name="uwa_private_message" type="checkbox">
								<?php esc_html_e('Enable Send Private message.','ultimate-woocommerce-auction');?>
							 </td>
							 </tr>							 
							 
							 <tr>
							 <th scope="row"></th>
							 
							 <td>
							 <input <?php checked($bids_tab_enable, 'yes'); ?> value="yes" name="uwa_bids_tab" type="checkbox">
								<?php esc_html_e('Enable Bids section.','ultimate-woocommerce-auction');?>	
							 </td>
							 </tr> 
							 
							<tr>
							 <th scope="row"></th>
							 
							 <td>
							 <input <?php checked($watchlists_tab_enable, 'yes'); ?>  value="yes" name="uwa_watchlists_tab" type="checkbox">
							 <?php esc_html_e( 'Enable Watchlists.', 'ultimate-woocommerce-auction' ); ?>
								
							 </td>
							 </tr> 		

							 <tr>
							 	<th scope="row"><label for="uwa_enable_bid_place_warning"><?php esc_html_e( 'Enable an alert box:', 'ultimate-woocommerce-auction' ); ?></label></th>
							 	<td class="uwaforminp">
							 						
						<input type="checkbox" <?php checked($bid_warning, 'yes'); ?> name="uwa_enable_bid_place_warning"  id="uwa_enable_bid_place_warning" value="yes">
						<?php esc_html_e('Enable an alert box for confirmation when user places a bid.', 'ultimate-woocommerce-auction');  ?><a href="" class="uwa_fields_tooltip" onclick="return false"><strong>?</strong>
						<span>
							<?php esc_html_e('This setting lets you enable an alert confirmation which is shown to user when they place a bid.', 'ultimate-woocommerce-auction');  ?>
					    </span></a>		
							 	</td>
							 </tr>					   

						</tbody>						
					</table>
					

						<tfoot>
							<tr>
								<td colspan="2" valign="top" scope="row">								
									<input type="submit" id="uwa-settings-submit" name="uwa-settings-submit" class="button-primary" value="<?php esc_html_e('Save Changes','ultimate-woocommerce-auction');?>" />
								</td>
							</tr>
						</tfoot>
					</table>
					
					
				</div><!-- /.inside -->
			</div></div></div></div>
			<!-- bend of the meta box section -->
        </form>		
		</div>
    </div>