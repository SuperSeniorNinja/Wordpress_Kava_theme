<?php
namespace Jet_Theme_Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class Plugin {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Jet_Theme_Core
	 */
	private static $instance = null;

	/**
	 * A reference to an instance of cherry framework core class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private $core = null;

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
	 * Plugin base name
	 *
	 * @var string
	 */
	public $plugin_name = null;

	/**
	 * Components
	 */
	public $module_loader;
	public $settings;
	public $elementor_manager;
	public $dashboard_module;
	public $templates;
	public $theme_builder;
	public $templates_api;
	public $config;
	public $locations;
	public $structures;
	public $conditions;
	public $compatibility_manager;
	public $api;
	public $compatibility;
	public $admin_bar;
	public $frontend_manager;

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Jet_Theme_Core
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Returns plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		return JET_THEME_CORE_VERSION;
	}

	/**
	 * Load framework modules
	 *
	 * @return [type] [description]
	 */
	public function module_loader() {

		require $this->plugin_path( 'includes/modules/loader.php' );

		$this->module_loader = new \Jet_Theme_Core_CX_Loader( array(
			$this->plugin_path( 'includes/modules/interface-builder/cherry-x-interface-builder.php' ),
			$this->plugin_path( 'includes/modules/vue-ui/cherry-x-vue-ui.php' ),
			$this->plugin_path( 'includes/modules/jet-dashboard/jet-dashboard.php' ),
			$this->plugin_path( 'includes/modules/admin-bar/jet-admin-bar.php' ),
		) );
	}

	/**
	 * Manually init required modules.
	 *
	 * @return void
	 */
	public function init() {

		$this->load_files();

		$this->config                       = new Config();
		$this->api                         = new Api();
		$this->settings                    = new Settings();

		// Maybe init Elenentor Page Builder manager
		$this->elementor_manager           = new Elementor\Manager();
		$this->locations                   = new Locations\Manager();
		$this->structures                  = new Structures();
		$this->templates                   = new Templates();
		$this->theme_builder               = new Theme_Builder();
		$this->template_conditions_manager = new Template_Conditions\Manager();
		$this->compatibility               = new \Jet_Theme_Core_Compatibility();
		$this->compatibility_manager       = new Compatibility\Manager();
		$this->admin_bar                   = \Jet_Admin_Bar::get_instance();
		$this->frontend_manager            = new Frontend_Manager();

		//new \Jet_Theme_Core_Elementor_Integration();

		//Init Rest Api
		new Rest_Api();

		if ( is_admin() ) {
			$this->templates_api = new \Jet_Theme_Core_Templates_Api();
			new \Jet_Theme_Core_Ajax_Handlers();
		}

		do_action( 'jet-theme-core/init', $this );

	}

	/**
	 * [jet_dashboard_init description]
	 * @return [type] [description]
	 */
	public function jet_dashboard_init() {

		if ( is_admin() ) {

			$jet_dashboard_module_data = $this->module_loader->get_included_module_data( 'jet-dashboard.php' );

			$jet_dashboard = \Jet_Dashboard\Dashboard::get_instance();

			$jet_dashboard->init( array(
				'path'           => $jet_dashboard_module_data['path'],
				'url'            => $jet_dashboard_module_data['url'],
				'cx_ui_instance' => array( $this, 'jet_dashboard_ui_instance_init' ),
				'plugin_data'    => array(
					'slug'    => 'jet-theme-core',
					'file'     => 'jet-theme-core/jet-theme-core.php',
					'version' => $this->get_version(),
					'plugin_links' => array(
						array(
							'label'  => esc_html__( 'Theme Builder', 'jet-theme-core' ),
							'url'    => add_query_arg( array( 'post_type' => 'jet-theme-core' ), admin_url( 'edit.php' ) ),
							'target' => '_self',
						),
						array(
							'label'  => esc_html__( 'Kava Theme', 'jet-theme-core' ),
							'url'    => add_query_arg(
								array(
									'page'    => 'jet-dashboard-settings-page',
									'subpage' => 'jet-theme-core-general-settings'
								),
								admin_url( 'admin.php' )
							),
							'target' => '_self',
						),
						array(
							'label'  => esc_html__( 'Settings', 'jet-theme-core' ),
							'url'    => add_query_arg(
								array(
									'page'    => 'jet-dashboard-settings-page',
									'subpage' => 'jet-theme-core-general-settings'
								),
								admin_url( 'admin.php' )
							),
							'target' => '_self',
						),
					),
				),
			) );
		}
	}

	/**
	 * [jet_dashboard_ui_instance_init description]
	 * @return [type] [description]
	 */
	public function jet_dashboard_ui_instance_init() {
		$cx_ui_module_data = $this->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );

		return new \CX_Vue_UI( $cx_ui_module_data );
	}

	/**
	 * Load required files
	 *
	 * @return void
	 */
	public function load_files() {

		// Lib
		if ( !class_exists( 'Mobile_Detect' ) ) {
			require $this->plugin_path( 'includes/lib/class-mobile-detect.php' );
		}

		// Global
		require $this->plugin_path( 'includes/utils.php' );
		require $this->plugin_path( 'includes/settings/manager.php' );
		require $this->plugin_path( 'includes/config.php' );
		require $this->plugin_path( 'includes/api.php' );
		require $this->plugin_path( 'includes/ajax-handlers.php' );
		require $this->plugin_path( 'includes/rest-api/rest-api.php' );
		require $this->plugin_path( 'includes/elementor/manager.php' );
		require $this->plugin_path( 'includes/elementor/locations.php' );
		require $this->plugin_path( 'includes/locations/manager.php' );
		require $this->plugin_path( 'includes/compatibility.php' );
		require $this->plugin_path( 'includes/compatibility/manager.php' );
		require $this->plugin_path( 'includes/frontend.php' );

		// Templates
		require $this->plugin_path( 'includes/templates/manager.php' );
		require $this->plugin_path( 'includes/templates/templates-api.php' );
		require $this->plugin_path( 'includes/templates/templates-export-import.php' );

		// Theme Builder
		require $this->plugin_path( 'includes/theme-builder/manager.php' );

		// Structures
		require $this->plugin_path( 'includes/template-structures/manager.php' );

		// Conditions
		require $this->plugin_path( 'includes/template-conditions/manager.php' );

	}

	/**
	 * Returns path to file or dir inside plugin folder
	 *
	 * @param  string $path Path inside plugin dir.
	 * @return string
	 */
	public function plugin_path( $path = null ) {

		if ( ! $this->plugin_path ) {
			$this->plugin_path = trailingslashit( JET_THEME_CORE_PATH );
		}

		return $this->plugin_path . $path;
	}
	/**
	 * Returns url to file or dir inside plugin folder
	 *
	 * @param  string $path Path inside plugin dir.
	 * @return string
	 */
	public function plugin_url( $path = null ) {

		if ( ! $this->plugin_url ) {
			$this->plugin_url = trailingslashit( JET_THEME_CORE_URL );
		}

		return $this->plugin_url . $path;
	}

	/**
	 * Loads the translation files.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function lang() {
		load_plugin_textdomain( 'jet-theme-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'jet-theme-core/template-path', 'jet-theme-core/' );
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
	 * @since  1.0.0
	 * @return void
	 */
	public function activation() {}

	/**
	 * Do some stuff on plugin activation
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function deactivation() {}

	/**
	 * Sets up needed actions/filters for the plugin to initialize.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->plugin_name = plugin_basename( __FILE__ );

		// Load framework
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

}

Plugin::get_instance();