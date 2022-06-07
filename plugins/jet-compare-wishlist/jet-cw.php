<?php
/**
 * Plugin Name: JetCompareWishlist For Elementor
 * Plugin URI:  https://crocoblock.com/plugins/jetcomparewishlist/
 * Description: JetCompareWishlist - Compare and Wishlist functionality for Elementor Page Builder
 * Version:     1.4.2
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-cw
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * WC tested up to: 5.4
 * WC requires at least: 3.0
 *
 * Elementor tested up to: 3.4
 * Elementor Pro tested up to: 3.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_CW` doesn't exists yet.
if ( ! class_exists( 'Jet_CW' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_CW {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.4.2';

		/**
		 * Require Elementor Version
		 *
		 * @var string Elementor version required to run the plugin.
		 */
		private static $require_elementor_version = '3.0.0';

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Holds module loader variable
		 */
		public $module_loader;

		/**
		 * Check if Compare and Wishlist enabled
		 *
		 * @var bool
		 */
		public $compare_enabled;
		public $wishlist_enabled;

		/**
		 * @var Jet_CW_Widgets_Store
		 */
		public $widgets_store;

		/**
		 * Components
		 */
		public $widgets_templates;
		public $render;

		/**
		 * @var Jet_CW_Settings
		 */
		public $settings;

		/**
		 * @var Jet_CW_Assets
		 */
		public $assets;

		/**
		 * @var Jet_CW_Integration
		 */
		public $integration;

		/**
		 * @var Jet_CW_Compatibility
		 */
		public $compatibility;

		/**
		 * @var Jet_CW_Compare_Integration
		 */
		public $compare_integration;

		/**
		 * @var Jet_CW_Compare_Render
		 */
		public $compare_render;

		/**
		 * @var Jet_CW_Compare_Data
		 */
		public $compare_data;

		/**
		 * @var Jet_CW_Wishlist_Integration
		 */
		public $wishlist_integration;

		/**
		 * @var Jet_CW_Wishlist_Render
		 */
		public $wishlist_render;

		/**
		 * @var Jet_CW_Wishlist_Data
		 */
		public $wishlist_data;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @return void
		 * @since  1.0.0
		 * @access public
		 */
		public function __construct() {

			// Check if Elementor installed and activated
			if ( ! did_action( 'elementor/loaded' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );

				return;
			}

			// Check for required Elementor version
			if ( ! version_compare( ELEMENTOR_VERSION, self::$require_elementor_version, '>=' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_required_elementor_version' ] );
				return;
			}

			add_action( 'plugins_loaded', array( $this, 'woocommerce_loaded' ) );

			// Load the core functions/classes required by the rest of the plugin.
			add_action( 'after_setup_theme', array( $this, 'module_loader' ), -20 );

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), -999 );
			// Load files.
			add_action( 'init', array( $this, 'init' ), -999 );

			// Jet Dashboard Init
			add_action( 'init', array( $this, 'jet_dashboard_init' ), -999 );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		}

		/**
		 * Check that WooCommerce active
		 */
		function woocommerce_loaded() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_missing_woocommerce_plugin' ] );

				return;
			}
		}

		/**
		 * Show recommended plugins notice
		 */
		public function admin_notice_missing_main_plugin() {

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			$elementor_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url() . 'plugin-install.php?s=elementor&tab=search&type=term',
				'<strong>' . esc_html__( 'Elementor', 'jet-cw' ) . '</strong>'
			);

			$message = sprintf(
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'jet-cw' ),
				'<strong>' . esc_html__( 'Jet Compare Wishlist', 'jet-cw' ) . '</strong>',
				$elementor_link
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

			if ( ! class_exists( 'WooCommerce' ) ) {
				$woocommerce_link = sprintf(
					'<a href="%1$s">%2$s</a>',
					admin_url() . 'plugin-install.php?s=woocommerce&tab=search&type=term',
					'<strong>' . esc_html__( 'WooCommerce', 'jet-cw' ) . '</strong>'
				);

				$message = sprintf(
					esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'jet-cw' ),
					'<strong>' . esc_html__( 'Jet Compare Wishlist', 'jet-cw' ) . '</strong>',
					$woocommerce_link
				);

				printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			}

		}

		/**
		 * Admin notice
		 *
		 * Warning when the site doesn't have a minimum required Elementor version.
		 *
		 * @access public
		 */
		public function admin_notice_required_elementor_version() {

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'jet-cw' ),
				'<strong>' . esc_html__( 'JetCompareWishlist', 'jet-cw' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'jet-cw' ) . '</strong>',
				self::$require_elementor_version
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

		}

		/**
		 * Show WooCommerce plugin notice
		 */
		public function admin_notice_missing_woocommerce_plugin() {

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			$woocommerce_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url() . 'plugin-install.php?s=woocommerce&tab=search&type=term',
				'<strong>' . esc_html__( 'WooCommerce', 'jet-cw' ) . '</strong>'
			);

			$message = sprintf(
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'jet-cw' ),
				'<strong>' . esc_html__( 'Jet Compare Wishlist', 'jet-cw' ) . '</strong>',
				$woocommerce_link
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

		}


		/**
		 * Load plugin framework
		 */
		public function module_loader() {

			require $this->plugin_path( 'includes/modules/loader.php' );

			$this->module_loader = new Jet_CW_CX_Loader(
				[
					$this->plugin_path( 'includes/modules/db-updater/cherry-x-db-updater.php' ),
					$this->plugin_path( 'includes/modules/interface-builder/cherry-x-interface-builder.php' ),
					$this->plugin_path( 'includes/modules/post-meta/cherry-x-post-meta.php' ),
					$this->plugin_path( 'includes/modules/vue-ui/cherry-x-vue-ui.php' ),
					$this->plugin_path( 'includes/modules/jet-dashboard/jet-dashboard.php' ),
				]
			);

		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {

			if ( ! $this->has_elementor() ) {
				return;
			}

			$this->load_files();

			$this->settings      = new Jet_CW_Settings();
			$this->assets        = new Jet_CW_Assets();
			$this->integration   = new Jet_CW_Integration();
			$this->widgets_store = new Jet_CW_Widgets_Store();
			$this->compatibility = new Jet_CW_Compatibility();

			$this->compare_enabled  = $this->settings->get( 'enable_compare' );
			$this->wishlist_enabled = $this->settings->get( 'enable_wishlist' );

			if ( filter_var( $this->compare_enabled, FILTER_VALIDATE_BOOLEAN ) ) {
				$this->compare_integration = new Jet_CW_Compare_Integration();
				$this->compare_render      = new Jet_CW_Compare_Render();
				$this->compare_data        = new Jet_CW_Compare_Data();
			}

			if ( filter_var( $this->wishlist_enabled, FILTER_VALIDATE_BOOLEAN ) ) {
				$this->wishlist_integration = new Jet_CW_Wishlist_Integration();
				$this->wishlist_render      = new Jet_CW_Wishlist_Render();
				$this->wishlist_data        = new Jet_CW_Wishlist_Data();
			}

			if ( is_admin() ) {
				//Init JetCompareWishlist Settings
				new \Jet_CW\Settings();

				// Init DB upgrader
				require $this->plugin_path( 'includes/class-jet-cw-db-upgrader.php' );

				jet_cw_db_upgrader()->init();
			}

			//Init Rest Api
			new \Jet_CW\Rest_Api();

		}

		/**
		 * Init the JetDashboard module
		 */
		public function jet_dashboard_init() {
			if ( is_admin() ) {
				$jet_dashboard_module_data = $this->module_loader->get_included_module_data( 'jet-dashboard.php' );

				$jet_dashboard = \Jet_Dashboard\Dashboard::get_instance();

				$jet_dashboard->init(
					array(
						'path'           => $jet_dashboard_module_data['path'],
						'url'            => $jet_dashboard_module_data['url'],
						'cx_ui_instance' => array( $this, 'jet_dashboard_ui_instance_init' ),
						'plugin_data'    => array(
							'slug'         => 'jet-cw',
							'file'         => 'jet-compare-wishlist/jet-cw.php',
							'version'      => $this->get_version(),
							'plugin_links' => array(
								array(
									'label'  => esc_html__( 'Go to settings', 'jet-cw' ),
									'url'    => add_query_arg( array( 'page' => 'jet-dashboard-settings-page', 'subpage' => 'jet-cw-compare-settings' ), admin_url( 'admin.php' ) ),
									'target' => '_self',
								),
							),
						),
					)
				);
			}
		}

		/**
		 * Get Vue UI Instance for JetDashboard module
		 *
		 * @return object
		 */
		public function jet_dashboard_ui_instance_init() {

			$cx_ui_module_data = $this->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );

			return new CX_Vue_UI( $cx_ui_module_data );

		}

		/**
		 * Check if theme has elementor
		 *
		 * @return boolean
		 */
		public function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		/**
		 * Returns Elementor instance
		 *
		 * @return object
		 */
		public function elementor() {
			return \Elementor\Plugin::$instance;
		}

		/**
		 * Load required files.
		 *
		 * @return void
		 */
		public function load_files() {

			require $this->plugin_path( 'includes/rest-api/rest-api.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/base.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/plugin-settings.php' );

			require $this->plugin_path( 'includes/class-jet-cw-settings.php' );
			require $this->plugin_path( 'includes/class-jet-cw-tools.php' );
			require $this->plugin_path( 'includes/class-jet-cw-integration.php' );
			require $this->plugin_path( 'includes/class-jet-cw-functions.php' );
			require $this->plugin_path( 'includes/class-jet-cw-widgets-functions.php' );
			require $this->plugin_path( 'includes/class-jet-cw-widgets-store.php' );
			require $this->plugin_path( 'includes/class-jet-cw-assets.php' );

			require $this->plugin_path( 'includes/compare/class-jet-cw-compare-integration.php' );
			require $this->plugin_path( 'includes/compare/class-jet-cw-compare-render.php' );
			require $this->plugin_path( 'includes/compare/class-jet-cw-compare-data.php' );

			require $this->plugin_path( 'includes/settings/manager.php' );

			require $this->plugin_path( 'includes/wishlist/class-jet-cw-wishlist-integration.php' );
			require $this->plugin_path( 'includes/wishlist/class-jet-cw-wishlist-render.php' );
			require $this->plugin_path( 'includes/wishlist/class-jet-cw-wishlist-data.php' );

			require $this->plugin_path( 'includes/lib/compatibility/class-jet-cw-compatibility.php' );

		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param string $path Path inside plugin dir.
		 *
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;

		}

		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param string $path Path inside plugin dir.
		 *
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;

		}

		/**
		 * Loads the translation files.
		 *
		 * @return void
		 * @since  1.0.0
		 * @access public
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-cw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-cw/template-path', 'jet-compare-wishlist/' );
		}

		/**
		 * Returns path to template file.
		 *
		 * @return string|bool
		 */
		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}

		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function activation() {
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function deactivation() {
		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 * @access public
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}
	}
}

if ( ! function_exists( 'jet_cw' ) ) {
	/**
	 * Returns instance of the plugin class.
	 *
	 * @return object
	 * @since  1.0.0
	 */
	function jet_cw() {
		return Jet_CW::get_instance();
	}
}

jet_cw();
