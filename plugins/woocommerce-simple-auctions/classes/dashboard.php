<?php
/**
 * WooCommerce Simple Auctions Dashboard widgets
 *
 * @package WordPress
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 */

if ( ! class_exists( 'Wpgenie_Dashboard' ) ) {


	/**
	 * Admin dashboard management
	 *
	 * @since 1.0.0
	 *
	 */
	class Wpgenie_Dashboard {
		/**
		 * Products URL
		 *
		 * @var string
		 * @access protected
		 * @since 1.0.0
	         *
		 */
		protected $_productsFeed = 'https://wpgenie.org/tag/dashboard/feed/';

		/**
		 * Constructor
	         *
		 */
		public function __construct() {
			add_action( 'wp_dashboard_setup', array($this, 'dashboard_widget_setup' ) );
		}

		/**
		 * Init
		 *
		 */
		public function init() {

		}

		/**
		 * Dashboard widget setup
		 *
		 * @return void
		 * @since 1.0.0
		 * @access public
	         *
		 */
		public function dashboard_widget_setup() {

	            global $wp_meta_boxes;

		    wp_add_dashboard_widget( 'wpgenie_dashboard_products_news', esc_html__( 'wpgenie.org - Our latest themes and plugins' , 'wc_groupbuy' ), array($this, 'dashboard_products_news') );

			
		}

		/**
		 * Product news widget
		 *
		 * @return void
		 * @since 1.0.0
		 * @access public
	         *
		 */
		public function dashboard_products_news() {

	            include_once( ABSPATH . WPINC . "/feed.php" );

				if ( false === ( $rss_items = get_transient( 'wpgenie_feed' ) ) ) {

					$rss = fetch_feed( $this->_productsFeed );
					if ( is_wp_error( $rss ) ) {
						echo '{Temporarily unable to load feed.}';

						return;
					}
					$rss_items = $rss->get_items( 0, 6 ); // Show four items.

					$cached = array();
					foreach ( $rss_items as $item ) {
						$cached[] = array(
							'url'     => $item->get_permalink(),
							'title'   => $item->get_title(),
							'date'    => $item->get_date( "M jS Y" ),
							'content' => !empty($item->get_content()) ? substr( strip_tags( $item->get_content() ), 0, 128 ) . "..." : '',
						);
					}
					$rss_items = $cached;

					set_transient( 'wpgenie_feed', $cached, 12 * HOUR_IN_SECONDS );

				}

				?>

				<ul>
					<?php
					if ( false === $rss_items ) {
						echo "<li>No items</li>";

						return;
					}

					foreach ( $rss_items as $item ) {
						
						?>
						<li>
							<a target="_blank" href="<?php echo esc_url( $item['url'] ); ?>">
								<?php echo esc_html( $item['title'] ); ?>
							</a>
							<span class="wpgenie-rss-date"><?php echo $item['date']; ?></span>
							<?php if(!empty($item['content']) ){ ?>
								<div class="wpgenie_news">
									<?php echo strip_tags( $item['content'] ) . "..."; ?>
								</div>
							<?php } ?>	
						</li>
						<?php
					}

					?>
				</ul>
	            <?php
		}
	}

	new Wpgenie_Dashboard();

}