<?php
    
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if (!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class wc_simple_auctions_List_Table extends WP_List_Table {    
   

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
        parent::__construct( array(
            'singular'  => 'ref',     // singular name of the listed records
            'plural'    => 'refs',    // plural name of the listed records
            'ajax'      => false      // does this table support ajax?
        ) );		
    }
    function column_default($item, $column_name){
        $url =  "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
        switch($column_name){
            case 'auction_id':
                return '<a href="'.get_permalink( $item[$column_name] ).'">'.get_the_title(  $item[$column_name] ).'</a>';
			case 'bid':
                return wc_price($item[$column_name]);
            case 'date':
                return $item[$column_name];
            case 'userid':
                
                $userdata = get_userdata( $item[$column_name] );
                if ($userdata){
                    $filter = esc_attr(add_query_arg( 'userid',$item[$column_name], $url ));
                    return '<a href="'. get_edit_user_link($item[$column_name] ) .'">'. esc_attr( $userdata->user_nicename ) .'</a> <a href="'.$filter.'" class="user-filter"></a>'
                        ;
                } else {
                    return 'User id:'.$item[$column_name];
                }
                
                
			case 'proxy':
                return ($item[$column_name] == 1) ? 'Yes' : '' ;
            default:
                $value = isset($item[$column_name]) ? $item[$column_name] : false ;
                return  apply_filters('woocommerce_simple_auctions_activity_column_default',$value,$item,$column_name);
        }
    }
    
    function column_title($item){        
        // Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['hits'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    public function single_row( $item ) {
        $class =apply_filters('woocommerce_simple_auctions_activity_row_class','', $item);
        echo '<tr class="'.$class.'">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'auction_id'     => 'Auction',
            'userid'     => 'User',
            'bid'    	=> 'Bid',
            'date'  => 'Date',
            'proxy'  => 'Proxy'
        );
        return apply_filters('woocommerce_simple_auctions_activity_columns',$columns);
    }
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'auction_id'           => array('auction_id',false),     // true means it's already sorted
            'bid'           => array('bid',false),
            'date'    => array('date',false),
        );
        return  apply_filters('woocommerce_simple_auctions_activity_sortable_columns',$sortable_columns);
    }
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
    
        return;
    }
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {

        global $wpdb, $_wp_column_headers, $wp;
        
        $where =' '; $user_filter =' '; $date_from_filter = '';
        $current_url = esc_attr(add_query_arg( $wp->query_string, '', home_url( $wp->request ) ));      
		$screen = get_current_screen();
		$searchstring = isset($_GET["s"])  ? esc_sql($_GET["s"]) : FALSE ;
        $userid_filter = isset($_GET["userid"])  ? esc_sql($_GET["userid"]) : FALSE ;
        $date_from = isset($_GET["datefrom"])  ? esc_sql($_GET["datefrom"]) : FALSE ;
        $date_to = isset($_GET["dateto"])  ? esc_sql($_GET["dateto"]) : FALSE ;
        
        if ($searchstring){
            $where = 'where ( '.$wpdb->prefix.'users.user_nicename LIKE "%'.$searchstring.'%"  OR '.$wpdb->prefix.'posts.post_title LIKE "%'.$searchstring.'%" )';
        }
        if ($userid_filter){
            if ($where  != ' '){
              $user_filter = ' AND '  ;
            } else {
                $user_filter = ' where '  ;
            }
            $user_filter .= ' '.$wpdb->prefix.'users.ID = '.$userid_filter;
        }
        if ($date_from or $date_to){
            if ($where != ' ' && $user_filter != ' ' ){
              $date_from_filter = ' AND '  ;
            } else {
                $date_from_filter = ' where '  ;
            }
            if ($date_from && $date_to ){
                $date_from_filter .= "date BETWEEN CAST('".$date_from."' AS DATETIME) AND CAST('".$date_to."' AS DATETIME)";
            } elseif($date_to) {
                $date_from_filter .= "date <= CAST('".$date_to."' AS DATETIME)";
            } elseif($date_from){
                $date_from_filter .= "date >= CAST('".$date_from."' AS DATETIME)";
            }            
        } 
	    /* -- Preparing your query -- */ 
        $query = "SELECT * FROM ".$wpdb->prefix."simple_auction_log LEFT JOIN ".$wpdb->users." ON ".$wpdb->prefix."simple_auction_log.userid = ".$wpdb->users.".id  LEFT JOIN ".$wpdb->posts." ON ".$wpdb->prefix."simple_auction_log.auction_id = ".$wpdb->posts.".ID $where $user_filter $date_from_filter";
            
	    /* -- Ordering parameters -- */	    
	    $orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'date';
	    
        $order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : 'DESC';
        
        if($orderby == 'date' OR $orderby == 'auction_id' ){
            $scnd_order = ',bid DESC ';
        } else{
            $scnd_order = ' ';
        }

	    if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order.' '.$scnd_order; }

        $query = apply_filters('woocommerce_simple_auctions_activity_query', $query, $where, $user_filter, $date_from_filter, $orderby,$order, $scnd_order );

	    /* -- Pagination parameters -- */
        $totalitems = $wpdb->query($query); // return the total number of affected rows      
        $user = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option('per_page', 'option');
        $perpage = get_user_meta($user, $option, true);
        
		if ( empty ( $perpage ) || $perpage < 1 ) { 
	       $perpage = $screen->get_option( 'per_page', 'default' );			 
		}		
        
        // Which page is this?
        $paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
		
        // Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        
        // How many pages do we have in total?        
        $totalpages = ceil($totalitems/$perpage);        
        
	    if(!empty($paged) && !empty($perpage)){
	    	
		    $offset=($paged-1)*$perpage;
    		$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	    }

		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
		) );
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $wpdb->get_results($query, ARRAY_A );
    }

    function datepicker(){

        $auction_dates_from = isset($_GET["datefrom"])  ? esc_sql($_GET["datefrom"]) : FALSE ;

        $auction_dates_to = isset($_GET["dateto"])  ? esc_sql($_GET["dateto"]) : FALSE ;
        
        echo '<div><p class="form-field auction_activityrange">
                <label for="_auction_dates_from">' . esc_html__('Date range', 'wc_simple_auctions') . '</label>
                <input type="text" class="short datetimepicker" name="datefrom" id="_auction_dates_from" value="' . $auction_dates_from . '" placeholder="' . esc_html_x('From&hellip; YYYY-MM-DD HH:MM', 'placeholder', 'wc_simple_auctions') . '" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
                <input type="text" class="short datetimepicker" name="dateto" id="_auction_dates_to" value="' . $auction_dates_to . '" placeholder="' . esc_html_x('To&hellip; YYYY-MM-DD HH:MM', 'placeholder', 'wc_simple_auctions') . '" maxlength="16" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
                <input type="submit" id="activityrange-submit" class="button" value="submit">
            </p></div>';
    }
}