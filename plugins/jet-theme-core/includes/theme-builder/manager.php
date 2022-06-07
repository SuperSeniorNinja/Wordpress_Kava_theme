<?php
namespace Jet_Theme_Core;
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Theme_Builder {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	public $post_type = 'jet-page-template';

	/**
	 * @var string
	 */
	public $page_slug = 'jet-theme-builder';

	/**
	 * @var bool
	 */
	public $page_templates_manager = false;

	/**
	 * @var bool
	 */
	public $page_templates_export_import = false;

	/**
	 * @var bool
	 */
	public $frontend_manager = false;

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Jet_Theme_Core
	 */
	private static $instance = null;

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
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {
		require jet_theme_core()->plugin_path( 'includes/theme-builder/includes/page-templates-manager.php' );
		require jet_theme_core()->plugin_path( 'includes/theme-builder/includes/page-templates-export-import.php' );
		require jet_theme_core()->plugin_path( 'includes/theme-builder/includes/frontend-manager.php' );
	}

	/**
	 * Init comnonents
	 */
	public function init_components() {
		$this->page_templates_manager = new Theme_Builder\Page_Templates_Manager();
		$this->page_templates_export_import = new Theme_Builder\Page_Templates_Export_Import();
		$this->frontend_manager = new Theme_Builder\Frontend_Manager();
	}

	/**
	 * Templates post type slug
	 *
	 * @return string
	 */
	public function slug() {
		return $this->post_type;
	}

	/**
	 * Register templates post type
	 *
	 * @return void
	 */
	public function register_post_types() {

		$args = array(
			'labels' => array(
				'name'          => esc_html__( 'Page Templates', 'jet-theme-core' ),
				'singular_name' => esc_html__( 'Page Template', 'jet-theme-core' ),
			),
			'public'          => false,
			'hierarchical'    => false,
			'show_in_rest'    => true,
			'can_export'      => true,
			'capability_type' => 'post',
			'rewrite'         => false,
			'supports'        => array ( 'title' ),
		);

		register_post_type(
			$this->slug(),
			apply_filters( 'jet-theme-core/page-templates/post-type/args', $args )
		);
	}

	/**
	 * [register_page description]
	 * @return [type] [description]
	 */
	public function register_page() {

		add_submenu_page(
			'jet-dashboard',
			esc_html__( 'Theme Builder', 'jet-theme-core' ),
			esc_html__( 'Theme Builder', 'jet-theme-core' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_page' )
		);

	}

	/**
	 * [render_page description]
	 * @return [type] [description]
	 */
	public function render_page() {
		include jet_theme_core()->get_template( 'admin/theme-builder/page.php' );
	}

	/**
	 * Template type popup assets
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();

		$module_data = jet_theme_core()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui          = new \CX_Vue_UI( $module_data );
		$ui->enqueue_assets_components();

		wp_enqueue_script(
			'jet-theme-builder-script',
			jet_theme_core()->plugin_url( 'includes/theme-builder/assets/builder/js/app.js' ), array(
				'jquery',
				'wp-api-fetch',
				'cx-vue-ui-components'
			),
			jet_theme_core()->get_version(),
			true
		);

		wp_localize_script(
			'jet-theme-builder-script',
			'JetThemeBuilderConfig',
			array(
				'createPageTemplatePath'           => 'jet-theme-core-api/v2/create-page-template',
				'copyPageTemplatePath'             => 'jet-theme-core-api/v2/copy-page-template',
				'removePageTemplatePath'           => 'jet-theme-core-api/v2/delete-page-template',
				'getPageTemplateListPath'          => 'jet-theme-core-api/v2/get-page-template-list',
				'createTemplatePath'               => 'jet-theme-core-api/v2/create-template',
				'getTemplateConditionsPath'        => 'jet-theme-core-api/v2/get-template-conditions',
				'getPageTemplateConditionsPath'    => 'jet-theme-core-api/v2/get-page-template-conditions',
				'updateTemplateConditionsPath'     => 'jet-theme-core-api/v2/update-template-conditions',
				'updatePageTemplateConditionsPath' => 'jet-theme-core-api/v2/update-page-template-conditions',
				'updatePageTemplateDataPath'       => 'jet-theme-core-api/v2/update-page-template-data',
				'updateTemplateDataPath'           => 'jet-theme-core-api/v2/update-template-data',
				'rawConditionsData'                => jet_theme_core()->template_conditions_manager->get_conditions_raw_data(),
				'templateTypeOptions'              => jet_theme_core()->structures->get_template_type_options(),
				'templatesList'                    => jet_theme_core()->templates->get_template_list(),
				'templateContentTypeOptions'       => jet_theme_core()->templates->get_template_content_type_options(),
				'templateContentTypeIcons'         => jet_theme_core()->templates->get_template_content_type_icons(),
			)
		);

	}

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		$this->load_files();

		add_action( 'jet-theme-core/init', array( $this, 'init_components' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_page' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

	}

}
