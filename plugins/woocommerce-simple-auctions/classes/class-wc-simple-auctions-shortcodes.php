<?php
/**
 * Wc auctions Shortcode
 *
 */

class WC_Shortcode_Simple_Auction extends WC_Shortcodes {
	
	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'auctions'                                => __CLASS__ . '::auctions',
			'featured_auctions'                       => __CLASS__ . '::featured_auctions',
			'recent_auctions'                         => __CLASS__ . '::recent_auctions',
			'ending_soon_auctions'                    => __CLASS__ . '::ending_soon_auctions',
			'future_auctions'                         => __CLASS__ . '::future_auctions',
			'finished_auctions'                       => __CLASS__ . '::finished_auctions',
			'past_auctions'                           => __CLASS__ . '::finished_auctions',
			'my_active_auctions'                      => __CLASS__ . '::my_active_auctions',
			'woocommerce_simple_auctions_my_auctions' => __CLASS__ . '::my_auctions',
			'won_auctions'                         	  => __CLASS__ . '::won_auctions',
			'auctions_watchlist'                      => __CLASS__ . '::auctions_watchlist',
			'all_user_auctions'                       => __CLASS__ . '::all_user_auctions',
			'my_auctions_activity'                    => __CLASS__ . '::my_auctions_activity',

		);
		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}
	/**
	 * List multiple products shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function auctions( $atts ) {
		$atts = (array) $atts;
		$type = 'auctions';
		// Allow list product based on specific cases.
		if ( isset( $atts['won_auctions'] ) && wc_string_to_bool( $atts['won_auctions'] ) ) {
			$type = 'won_auctions';
		} elseif ( isset( $atts['auctions_winners'] ) && wc_string_to_bool( $atts['auctions_winners'] ) ) {
			$type = 'auctions_winners';
		} elseif ( isset( $atts['my_auctions'] ) && wc_string_to_bool( $atts['my_auctions'] ) ) {
			$type = 'my_auctions';
		}

		$shortcode = new WC_Shortcode_Simple_Auctions( $atts, $type );

		return $shortcode->get_content();
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function featured_auctions( $atts ) {
		$atts = array_merge(
			array(
				'limit'        => '12',
				'columns'      => '4',
				'orderby'      => 'date',
				'order'        => 'DESC',
				'category'     => '',
				'cat_operator' => 'IN',
			),
			(array) $atts
		);

		$atts['visibility'] = 'featured';

		$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'featured_auctions' );

		return $shortcode->get_content();
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function recent_auctions( $atts ) {
		$atts = array_merge(
			array(
				'limit'        => '12',
				'columns'      => '4',
				'orderby'      => 'date',
				'order'        => 'DESC',
				'category'     => '',
				'cat_operator' => 'IN',
			),
			(array) $atts
		);

		$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'recent_auctions' );

		return $shortcode->get_content();
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function ending_soon_auctions( $atts ) {
		$atts = array_merge(
			array(
				'limit'        => '12',
				'columns'      => '4',
				'orderby'      => 'meta_value',
				'order'        => 'ASC',
				'category'     => '',
				'meta_key' 	   => '_auction_dates_to',
				'cat_operator' => 'IN',
				'auction_status' =>'active',
				'future' =>'yes',
			),
			(array) $atts
		);


		$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'ending_soon_auctions' );

		return $shortcode->get_content();
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function future_auctions( $atts ) {
		$atts = array_merge(
			array(
				'limit'        		=> '12',
				'columns'     		=> '4',
				'orderby'      		=> 'date',
				'order'        		=> 'DESC',
				'category'     		=> '',
				'cat_operator'   	=> 'IN',
				'auction_status'	=>'future',
			),
			(array) $atts
		);

		$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'future_auctions' );

		return $shortcode->get_content();
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function finished_auctions( $atts ) {
		$atts = array_merge(
			array(
				'limit'        => '12',
				'columns'      => '4',
				'orderby'      => 'date',
				'order'        => 'DESC',
				'category'     => '',
				'cat_operator' => 'IN',
				'auction_status' =>'finished',
			),
			(array) $atts
		);

		$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'finished_auctions' );

		return $shortcode->get_content();
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function my_active_auctions( $atts ) {
		
		if ( is_user_logged_in() ) {

			global $wpdb;	

			$user_id = get_current_user_id();
			$postids = array();
			$userauction = $wpdb->get_results("SELECT DISTINCT auction_id FROM ".$wpdb->prefix."simple_auction_log WHERE userid = $user_id ", ARRAY_N );

			if(isset($userauction) && !empty($userauction)){
				foreach ($userauction as $auction) {
					$postids []= $auction[0];

				}
			}
			$postids = implode(',', $postids);
			$atts = array_merge(
				array(
					'limit'        => '12',
					'ids' 		   => $postids,
					'columns'      => '4',
					'orderby'      => 'date',
					'order'        => 'DESC',
					'category'     => '',
					'cat_operator' => 'IN',
					'auction_status' =>'active',
				),
				(array) $atts
			);

			$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'my_active_auctions' );

			return $shortcode->get_content();
		}
	}
	/**
	 * Output won auctions for user
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function won_auctions( $atts ) {

		if ( is_user_logged_in() ) {
			global $wpdb;	

			$user_id = get_current_user_id();
			$postids = array();
			
			$userauction = $wpdb->get_results("SELECT DISTINCT auction_id FROM ".$wpdb->prefix."simple_auction_log WHERE userid = $user_id ", ARRAY_N );

			if(isset($userauction) && !empty($userauction)){
				foreach ($userauction as $auction) {

					$normally_ended = get_post_meta( $auction[0], '_auction_closed', true );
					
					if ( isset($normally_ended) && ( $normally_ended == '2' ) ){

						$winner = get_post_meta( $auction[0], '_auction_current_bider', true );	

						if ( isset($winner) && ( $winner == $user_id ) ){

							$postids []= $auction[0];			

						}	
					}					
				}			
			}
			$postids = implode(',', $postids);
			
			if ( empty($postids) ) $postids = '1';

			$atts = array_merge(
				array(
					'limit'        		=> '-1',
					'ids' 		  		=> $postids,
					'columns'      		=> '4',
					'orderby'      		=> 'meta_value',
					'order'        		=> 'ASC',
					'category'    		=> '',
					'cat_operator' 		=> 'IN',	
					'meta_key'			=> '_auction_dates_to',
					'auction_status' 	=> 'finished',
				),
				(array) $atts
			);

			$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'won_auctions' );

			return $shortcode->get_content();
		}
	}
	/**
	 * Output featured products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 * 
	 */
	public static function my_auctions( $atts ) {

		if ( is_user_logged_in() ) {
			$output = '<div class="wc-auctions active-auctions clearfix woocommerce"><h2>' .esc_html__( 'Active Auctions', 'wc_simple_auctions' ) . '</h2>';
			$output .= self::my_active_auctions($atts) ;
			$output .= '</div><div class="wc-auctions active-auctions clearfix woocommerce"><h2>' .esc_html__( 'Won auctions', 'wc_simple_auctions' ) . '</h2>';
			$output .= self::won_auctions($atts);
			$output .= "</div>";
			return $output;

		} else {
			$output = '<div class="woocommerce"><p class="woocommerce-info">' .esc_html__('Please log in to see your auctions.','wc_simple_auctions' ) . '</p></div>';
			return $output;
		}
	}
	/**
	 * auctions_watchlist - shows user's auction watchlist
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 * 
	 */
	public static function auctions_watchlist( $atts ) {

		global $woocommerce_loop, $watchlist;

		
		if ( is_user_logged_in() ) {
			$user_ID = get_current_user_id();
			$watchlist_ids = get_user_meta($user_ID, '_auction_watch' );
			$watchlist_ids = implode(',', $watchlist_ids );
			if( !empty( $watchlist_ids ) ) {
				$watchlist = true;
				$atts = array_merge(
					array(
						'limit'        => '12',
						'columns'      => '4',
						'orderby'      => 'meta_value',
						'order'        => 'DESC',
						'meta_key' => '_auction_dates_to',
						'auction_arhive' => TRUE,
						'show_future_auctions' => TRUE,
						'ids' => $watchlist_ids
					),
					(array) $atts
				);
				$shortcode = new WC_Shortcode_Simple_Auctions( $atts, 'auctions_watchlist' );
				return $shortcode->get_content();
			} else {
				$output = '<div class="woocommerce"><p class="woocommerce-info">' . esc_html__('There are no auctions on your watchlist.','wc_simple_auctions' ) . '</p></div>';
			}

			
		} else {
			$output = '<div class="woocommerce"><p class="woocommerce-info">' . esc_html__('Please log in to see your auctions.','wc_simple_auctions' ) . '</p></div>';
		}
		return $output;
	}


		/**
		 * all_user_auctions shortcode - shows all auctions in which user participates
		 *
		 * @access public
		 * @param array $atts
		 * @return string
		 */
	public static function all_user_auctions( $atts ) {

			global $wpdb;

			if ( is_user_logged_in() ) {
				$limit = '';
				if(!empty($atts['limit'])){
					$limit = 'LIMIT '.intval($atts['limit']);
				}

				$user_id  = get_current_user_id();
				$postids = array();
				$userauction	 = $wpdb->get_results("SELECT DISTINCT auction_id FROM ".$wpdb->prefix."simple_auction_log WHERE userid = $user_id  $limit" ,ARRAY_N );
				if(isset($userauction) && !empty($userauction)){
					foreach ($userauction as $auction) {
						$postids []= $auction[0];

					}
				}

				$output = '
				<div class="simple-auctions active-auctions clearfix">
					<h2>'.esc_html__( 'All user auctions', 'wc_simple_auctions' ) . '</h2>';

					$args = array(
						'post__in' 			=> $postids ,
						'post_type' 		=> 'product',
						'posts_per_page' 	=> '-1',
	                    'order'		=> 'ASC',
	                    'orderby'	=> 'meta_value',
						'tax_query' 		=> array(
							array(
								'taxonomy' => 'product_type',
								'field' => 'slug',
								'terms' => 'auction'
							)
						),
						'meta_query' => array(

						       array(
										'key'     => '_auction_closed',
										'compare' => 'NOT EXISTS',
								)
						   ),
						'auction_arhive' => TRUE,
						'show_past_auctions' 	=>  TRUE,
					);

					$activeloop = new WP_Query( $args );
					if ( $activeloop->have_posts() && !empty($postids) ) {
						ob_start();
					    woocommerce_product_loop_start();
						while ( $activeloop->have_posts() ):$activeloop->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile;
						woocommerce_product_loop_end();
						$output .= ob_get_clean();
					} else {
						$output .=esc_html__("You are not participating in auction.","wc_simple_auctions" );
					}

					wp_reset_postdata();

				$output .= '</div>';
				return $output;
			}
		}

	/**
	 * my_auctions_activity shortcode - shows my auctions activity as log entries
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function my_auctions_activity( $atts ) {

		global $wpdb;

		if ( is_user_logged_in() ) {
			$limit = '';
			if(!empty($atts['limit'])){
				$limit= 'LIMIT '.intval($atts['limit']);
			}

			$user_id = get_current_user_id();
			$useractivity = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."simple_auction_log WHERE userid = $user_id  ORDER BY date DESC " . $limit );

			if($useractivity) {
				$output = '<table class="my_auctions_activity">';
				$output .= '<tr>';
				$output .= '<th>'.__('Date', 'wc_simple_auctions').'</th>';
				$output .= '<th>'.__('Auction', 'wc_simple_auctions').'</th>';
				$output .= '<th>'.__('Bid', 'wc_simple_auctions').'</th>';
				$output .= '<th>'.__('Status', 'wc_simple_auctions').'</th>';
				$output .= '</tr>';
				
				foreach ($useractivity as $key => $value) {
					if ( get_post_status ($value->auction_id ) == 'publish' ) {
						$class = '';
						$product = wc_get_product($value->auction_id);

						if($product && method_exists( $product, 'get_type') && $product->get_type() == 'auction'){
							if($product->is_closed()){
								$class .='closed ';
							}

							if($product->get_auction_current_bider() == $user_id && !$product->is_sealed()){
								$class .='winning ';
							}

							if($product->get_auction_current_bider() == $user_id && !$product->is_reserve_met()){
								$class .='reserved ';
							}

							if  ( strtotime( $product->get_auction_relisted() ) > strtotime( $value->date ) ){
								$class .='relisted ';
							}

							$output .= '<tr class="'.$class.'">';
							$output .= '<td>'.$value->date.'</td>';
							$output .= '<td><a href="'.get_permalink( $value->auction_id ).'">'.get_the_title( $value->auction_id ).'</a></td>';
							$output .= '<td>'.wc_price($value->bid).'</td>'; 
							$output .= '<td>'. $product->get_price_html() .'</td>';
							$output .= '</tr>';
						}
					}	
				}
				$output .= '</table>';
			}	
		} else  {
			$output = '<div class="woocommerce"><p class="woocommerce-info">'.__('Please log in to see your auctions activity.','wc_simple_auctions' ).'</p></div>';
		}

		return $output;
	}



}
