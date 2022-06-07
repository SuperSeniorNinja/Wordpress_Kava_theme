<?php
/**
 * Ending Soon Auctions Widget
 *
 * @category 	Widgets
 * @version 	1.0.0
 * @extends 	WP_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_SA_Widget_Ending_Soon_Auction extends WP_Widget {

	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_name;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
     * 
	 */
	function __construct() {

		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'woocommerce widget__ending_soon_auctions';
		$this->woo_widget_description = esc_html__( 'Display a list of your ending soon auctions on your site.', 'wc_simple_auctions' );
		$this->woo_widget_idbase = 'woocommerce_ending_soon_auctions';
		$this->woo_widget_name = esc_html__( 'WooCommerce Ending Soon Auctions', 'wc_simple_auctions' );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		parent::__construct('ending_soon_auctions', $this->woo_widget_name, $widget_ops);		

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	/**
	 * Widget function
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
     * 
	 */
	function widget($args, $instance) {
		global $woocommerce;

		$cache = wp_cache_get('widget_ending_soon_auctions', 'widget');		

		if ( !is_array($cache) ) $cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? esc_html__('Ending soon Auctions', 'wc_simple_auctions' ) : $instance['title'], $instance, $this->id_base);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

	    $query_args = array('posts_per_page' => $number, 'no_found_rows' => 1, 'post_status' => 'publish', 'post_type' => 'product');
	    $query_args['meta_query'] = array();
	    $query_args['meta_query'][]    = $woocommerce->query->stock_status_meta_query();
        $query_args['meta_query'][]= array(	'key'  => '_auction_closed',	'compare' => 'NOT EXISTS');
        if(empty( $instance['furure_auctions'] ) ) {
        	$query_args['meta_query'][]= array('key' => '_auction_started','compare' => 'NOT EXISTS' );	
        }
	    $query_args['meta_query']      = array_filter( $query_args['meta_query'] );		
		$query_args['tax_query']       = array(array('taxonomy' => 'product_type' , 'field' => 'slug', 'terms' => 'auction')); 
		$query_args['auction_arhive']  = TRUE;
		$query_args['meta_key'] = '_auction_dates_to';
		$query_args['orderby']  = 'meta_value';
		$query_args['order']    = 'ASC';		

		$r = new WP_Query($query_args);

		if ( $r->have_posts() ) {
		        
		    $hide_time = empty( $instance['hide_time'] ) ? 0 : 1;

			echo $before_widget;

			if ( $title )
				echo $before_title . $title . $after_title;			

			echo '<ul class="product_list_widget">';

			while ( $r->have_posts()) {
				$r->the_post();
				
				global $product;
				$time = '';
				$timetext = esc_html__('Time left', 'wc_simple_auctions');
				$datatime = $product->get_seconds_remaining();
				$product_id = $product->get_id();
				if(!$product->is_started()){
					$timetext = esc_html__('Starting in', 'wc_simple_auctions');
					$datatime = $product->get_seconds_to_auction();
				}
				if($hide_time != 1 && !$product->is_closed())
					$time = '<span class="time-left">'.apply_filters('time_text',$timetext,$product_id).'</span>
					<div class="auction-time-countdown" data-time="'.$datatime.'" data-auctionid="'.$product_id.'" data-format="'.get_option( 'simple_auctions_countdown_format' ).'"></div>';
				if($product->is_closed())
						$time = '<span class="has-finished">'.apply_filters('time_text',esc_html__('Auction finished', 'wc_simple_auctions'),$product_id).'</span>';
				echo '<li>
					<a href="' . get_permalink() . '">
						' . ( has_post_thumbnail() ? get_the_post_thumbnail( $r->post->ID, 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					</a> ' . $product->get_price_html() . $time . '
					
				</li>';
			}

			echo '</ul>';

			echo $after_widget;
		}

		wp_reset_postdata();

		$content = ob_get_clean();

		if ( isset( $args['widget_id'] ) ) $cache[$args['widget_id']] = $content;

		echo $content;

		wp_cache_set('widget_ending_soon_auctions', $cache, 'widget');
	}

	/**
	 * Update function
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
     * 
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['hide_time'] = empty( $new_instance['hide_time'] ) ? 0 : 1;
		$instance['furure_auctions'] = empty( $new_instance['furure_auctions'] ) ? 0 : 1;

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_ending_soon_auctions']) ) delete_option('widget_ending_soon_auctions');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_ending_soon_auctions', 'widget');
	}

	/**
	 * Form function
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
     * 
	 */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
			$number = 5;		
		$hide_time = empty( $instance['hide_time'] ) ? 0 : 1;

		$furure_auctions = empty( $instance['furure_auctions'] ) ? 0 : 1;
        ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e( 'Title:', 'wc_simple_auctions' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php esc_html_e( 'Number of auctions to show:', 'wc_simple_auctions' ); ?></label>
		<input id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" /></p>
		
		<p><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('hide_time') ); ?>" name="<?php echo esc_attr( $this->get_field_name('hide_time') ); ?>"<?php checked( $hide_time ); ?> />
		<label for="<?php echo $this->get_field_id('hide_time'); ?>"><?php esc_html_e( 'Hide time left', 'wc_simple_auctions' ); ?></label></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('furure_auctions') ); ?>" name="<?php echo esc_attr( $this->get_field_name('furure_auctions') ); ?>"<?php checked( $furure_auctions ); ?> />
		<label for="<?php echo $this->get_field_id('furure_auctions'); ?>"><?php esc_html_e( 'Show future auctions', 'wc_simple_auctions' ); ?></label></p>
        <?php
	}
}