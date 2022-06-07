<?php
namespace Jet_Theme_Core;

/**
 * API controller class
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Controller class
 */
class Rest_Api {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * [$api_namespace description]
	 * @var string
	 */
	public $api_namespace = 'jet-theme-core-api/v2';

	/**
	 * [$_endpoints description]
	 * @var null
	 */
	private $_endpoints = null;

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->load_files();

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/base.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/plugin-settings.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/sync-templates.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-page-templates.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-post-categories.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-posts.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-post-tags.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-post-types.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-static-pages.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/get-template-conditions.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/update-template-conditions.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/create-template.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/update-template-data.php' );

		// Theme Builder
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/theme-builder/get-page-template-list.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/theme-builder/create-page-template.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/theme-builder/copy-page-template.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/theme-builder/delete-page-template.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/theme-builder/get-page-template-conditions.php' );
		require jet_theme_core()->plugin_path( 'includes/rest-api/endpoints/theme-builder/update-page-template-data.php' );
	}

	/**
	 * Initialize all JetEngine related Rest API endpoints
	 *
	 * @return [type] [description]
	 */
	public function init_endpoints() {

		$this->_endpoints = array();
		$this->register_endpoint( new Endpoints\Plugin_Settings() );
		$this->register_endpoint( new Endpoints\Sync_Templates() );
		$this->register_endpoint( new Endpoints\Get_Page_Templates() );
		$this->register_endpoint( new Endpoints\Get_Post_Categories() );
		$this->register_endpoint( new Endpoints\Get_Posts() );
		$this->register_endpoint( new Endpoints\Get_Post_Tags() );
		$this->register_endpoint( new Endpoints\Get_Post_Types() );
		$this->register_endpoint( new Endpoints\Get_Static_Pages() );
		$this->register_endpoint( new Endpoints\Get_Template_Conditions() );
		$this->register_endpoint( new Endpoints\Update_Template_Conditions() );
		$this->register_endpoint( new Endpoints\Create_Template() );
		$this->register_endpoint( new Endpoints\Update_Template_Data() );

		$this->register_endpoint( new Endpoints\Get_Page_Template_List() );
		$this->register_endpoint( new Endpoints\Create_Page_Template() );
		$this->register_endpoint( new Endpoints\Copy_Page_Template() );
		$this->register_endpoint( new Endpoints\Delete_Page_Template() );
		$this->register_endpoint( new Endpoints\Get_Page_Template_Conditions() );
		$this->register_endpoint( new Endpoints\Update_Page_Template_Data() );

		do_action( 'jet-theme-core/rest-api/init-endpoints', $this );

	}

	/**
	 * Register new endpoint
	 *
	 * @param  object $endpoint_instance Endpoint instance
	 * @return void
	 */
	public function register_endpoint( $endpoint_instance = null ) {

		if ( $endpoint_instance ) {
			$this->_endpoints[ $endpoint_instance->get_name() ] = $endpoint_instance;
		}

	}

	/**
	 * Returns all registererd API endpoints
	 *
	 * @return [type] [description]
	 */
	public function get_endpoints() {

		if ( null === $this->_endpoints ) {
			$this->init_endpoints();
		}

		return $this->_endpoints;

	}

	/**
	 * Returns endpoints URLs
	 */
	public function get_endpoints_urls() {

		$result    = array();
		$endpoints = $this->get_endpoints();

		foreach ( $endpoints as $endpoint ) {
			$key = str_replace( '-', '', ucwords( $endpoint->get_name(), '-' ) );
			$result[ $key ] = get_rest_url( null, $this->api_namespace . '/' . $endpoint->get_name() . '/' . $endpoint->get_query_params() , 'rest' );
		}

		return $result;

	}

	/**
	 * Returns route to passed endpoint
	 *
	 * @return [type] [description]
	 */
	public function get_route( $endpoint = '', $full = false ) {

		$path = $this->api_namespace . '/' . $endpoint . '/';

		if ( ! $full ) {
			return $path;
		} else {
			return get_rest_url( null, $path );
		}

	}

	// Register our routes.
	public function register_routes() {

		$endpoints = $this->get_endpoints();

		foreach ( $endpoints as $endpoint ) {

			$args = array(
				'methods'             => $endpoint->get_method(),
				'callback'            => array( $endpoint, 'callback' ),
				'permission_callback' => array( $endpoint, 'permission_callback' ),
			);

			if ( ! empty( $endpoint->get_args() ) ) {
				$args['args'] = $endpoint->get_args();
			}

			$route = '/' . $endpoint->get_name() . '/' . $endpoint->get_query_params();

			register_rest_route( $this->api_namespace, $route, $args );
		}
	}

}

