<?php
/**
 * Ultimate Auction For WooCommerce Shortcode
 *
 */
 
class UWA_Shortcode extends WC_Shortcodes {

		private static $instance;	
	/**
	* Returns the *Singleton* instance of this class.
	*
	* @return Singleton The *Singleton* instance.
	*/
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	public function __construct() {			
		
		add_shortcode( 'uwa_new_auctions', array( $this, 'uwa_new_auctions_fun' ) );		
	  
	}	
	/**
	 * New Auction shortcode  
	 * [uwa_new_auctions days_when_added="10" columns="4" orderby="date" order="desc/asc" 
	 	show_expired="yes/no"]	 
	 *
	 * @param array $atts	 
	 *
	 */
	public function uwa_new_auctions_fun( $atts ) {

		global $woocommerce_loop, $woocommerce;		
		extract(shortcode_atts(array(
			//'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' => 'date',
			'order' => 'desc',
			'days_when_added' =>'12',
			'show_expired' =>'yes',
			'paginate' => 'false',
		  	'limit' => -1
		), $atts));

		$limit = (int)$limit;  // don't remove

		$meta_query = $woocommerce->query->get_meta_query();
		if($show_expired == 'no'){
        	$meta_query [] = array(
								'key'     => 'woo_ua_auction_closed',
								'compare' => 'NOT EXISTS',
							);
        }
				
		$days_when_added_pera = "-".$days_when_added." days";
		$after_day = wp_date('Y-m-d', strtotime($days_when_added_pera),get_uwa_wp_timezone());
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts'	=> 1,
			//'posts_per_page' => -1,   // -1 is default for all results to display
			'posts_per_page' => $limit,
			'orderby' => $orderby,
			'order' => $order,
			'meta_query' => $meta_query,
			'date_query' => array(
                    array(
						'after' =>$after_day 
                    ),
                ),
			'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')),
			'auction_arhive' => TRUE
		);

		/* Set Pagination Variable */
		if($paginate === "true"){
			$paged = get_query_var('paged') ? get_query_var('paged') : 1;
			$args['paged'] = $paged;
			//$woocommerce_loop['paged'] = $paged;
		}

		ob_start();
		$products = new WP_Query( $args );
		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php
			
				/* Pagination Top Text */				
				if($paginate === "true" && ($limit >= 1 || $limit === -1 ))  {				
					$args_toptext = array(
						'total'    => $products->found_posts,
						//'per_page' => $products->get( 'posts_per_page' ),
						'per_page' => $limit,
						'current'  => max(1, get_query_var('paged')),
					);
					wc_get_template( 'loop/result-count.php', $args_toptext );
				}
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

	   <?php else : ?>

            <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif;

		wp_reset_postdata();

		/* ---  Display Pagination ---  */

		if ( $paginate === "true" && $limit >= 1 && $limit < $products->found_posts ) { // don't change condition else design conflicts
			$big = 999999999;
			$current = max(1, get_query_var('paged'));
			$total   = $products->max_num_pages;
			$base    = esc_url_raw( str_replace( $big, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( $big, false ))));
			$format  = '?paged=%#%';			

			if ( $total <= 1 ) {
				return;
			}
			
			$display_data = '<nav class="woocommerce-pagination">';
			$display_data .= paginate_links( 
				apply_filters( 'woocommerce_pagination_args', 
					array( 
						'base'         => $base,
						'format'       => $format,						
						'add_args'     => false,						
						'current'      => $current,
						'total'        => $total,
						'prev_text'    => '&larr;',
						'next_text'    => '&rarr;',
						//'type'         => 'list',
						'end_size'     => 3,
						'mid_size'     => 3,
					) 
				));
			$display_data .= '</nav>';
			echo wp_kses_post($display_data);
			
		} /* end of if - paginate */

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}	

	
}

UWA_Shortcode::get_instance();