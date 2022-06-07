<?php
namespace ElementorPro\Modules\Wsa_Woocommerce;

use Elementor\Core\Documents_Manager;
use Elementor\Settings;
use ElementorPro\Base\Module_Base;
use ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager;
use ElementorPro\Modules\Wsa_Woocommerce\Conditions\Wsa_Conditions;
use ElementorPro\Modules\Wsa_Woocommerce\Documents\Auction_Archive;
use ElementorPro\Modules\Wsa_Woocommerce\Documents\Auction_Post;
use ElementorPro\Modules\Wsa_Woocommerce\Documents\Auction;
use ElementorPro\Plugin;

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	/**
	 * Main Spicy Extension Class
	 *
	 * The main class that initiates and runs the plugin.
	 *
	 * @since 1.0.0
	 */
	final class Wsa_Extension {


		/**
		 * Minimum Elementor Version
		 *
		 * @since 1.0.0
		 *
		 * @var string Minimum Elementor version required to run the plugin.
		 */
		const MINIMUM_ELEMENTOR_VERSION = '3';

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 *
		 * @access private
		 * @static
		 *
		 * @var Spicy_Extension The single instance of the class.
		 */
		private static $_instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 * @static
		 *
		 * @return Spicy_Extension An instance of the class.
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

		 	return self::$_instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		
		public function __construct() {	

			
			include( __DIR__ . '/conditions/auction-archive.php' );
			include( __DIR__ . '/conditions/auction-page.php' );
			include( __DIR__ . '/conditions/auction.php' );
			include( __DIR__ . '/conditions/wsa-conditions.php' );
			include( __DIR__ . '/documents/auction-archive.php' );
			include( __DIR__ . '/documents/auction.php' );
			include( __DIR__ . '/documents/auction-post.php' );

			add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
			add_action( 'elementor/theme/register_conditions', [ $this, 'register_conditions' ] );
			add_action( 'elementor/documents/register', [ $this, 'register_documents' ] );
		}

		

		/**
		 * Initialize the plugin
		 *
		 * Load the plugin only after Elementor (and other plugins) are loaded.
		 * Checks for basic plugin requirements, if one check fail don't continue,
		 * if all check have passed load the files required to run the plugin.
		 *
		 * Fired by `plugins_loaded` action hook.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function init() {

			// Check for required Elementor version			
			if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
				return;
			}
			
			
			// Add Plugin actions
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
			// Register Widget Styles
			add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );

		}

		public function widget_styles() {
			wp_enqueue_style( 'spicyPluginStylesheet', plugins_url( '/css/lotteries.css', __FILE__ ) );
		}

		public function init_editor_scripts_and_styles() {
			wp_enqueue_style( 'spicyPluginStylesheet', plugins_url( '/css/lotteries.css', __FILE__ ) );
		}

		/**
		 * Admin notice
		 *
		 * Warning when the site doesn't have a minimum required Elementor version.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function admin_notice_minimum_elementor_version() {

			if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

			$message = sprintf(
				/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'wc_simple_auctions' ),
				'<strong>' . esc_html__( 'Elementor WooCommerce Simple Auctions Extension', 'wc_simple_auctions' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'wc_simple_auctions' ) . '</strong>',
				self::MINIMUM_ELEMENTOR_VERSION
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

		}


		/**
		 * Init Widgets
		 *
		 * Include widgets files and register them
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function init_widgets() {

			// Include Widget files
			require_once( __DIR__ . '/classes/base-auctions-renderer.php' );
			require_once( __DIR__ . '/classes/auctions-renderer.php' );
			require_once( __DIR__ . '/classes/current-query-auctions-renderer.php' );
			require_once( __DIR__ . '/widgets/auctions.php' );
			require_once( __DIR__ . '/widgets/archive_auctions.php' );
			require_once( __DIR__ . '/widgets/auction_countdown.php' );
			require_once( __DIR__ . '/widgets/auction_history.php' );
			require_once( __DIR__ . '/widgets/auction_dates.php' );
			require_once( __DIR__ . '/widgets/auction_max_bid.php' );
			require_once( __DIR__ . '/widgets/auction_reserve.php' );
			require_once( __DIR__ . '/widgets/auction_sealed.php' );
			require_once( __DIR__ . '/widgets/auction_bid_form.php' );

			// Register widget
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Archive_Auctions() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_Countdown() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_History() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_Dates() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_Max_Bid() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_Reserve() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_Sealed() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auction_Bid_Form() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \ElementorPro\Modules\Woocommerce\Widgets\Auctions() );

		}

		public function register_conditions( $conditions_manager ) {

			$woocommerce_condition = new Wsa_Conditions();

			$conditions_manager->get_condition( 'general' )->register_sub_condition( $woocommerce_condition );

		}

		/**
		 * @param Documents_Manager $documents_manager
		 */
		public function register_documents( $documents_manager ) {
			$this->docs_types = [
				'auction-archive' => Auction_Archive::get_class_full_name(),
				'auction' => Auction::get_class_full_name(),
				//'auction-post' => Auction_Post::get_class_full_name(),
			];

			foreach ( $this->docs_types as $type => $class_name ) {
				$documents_manager->register_document_type( $type, $class_name );
			}

		}


    }

Wsa_Extension::instance();