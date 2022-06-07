<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Sync_Templates extends Base {

	/**
	 * [get_method description]
	 * @return [type] [description]
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'sync-templates';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$key     = 'jet-api_version';
		$version = get_transient( $key );

		if ( ! $version ) {
			set_transient( $key, JET_THEME_CORE_VERSION, DAY_IN_SECONDS );
		}

		delete_transient( 'jet_theme_core_templates_jet-api_' . $version );
		delete_transient( 'jet_theme_core_categories_jet-api_' . $version );
		delete_transient( 'jet_theme_core_keywords_jet-api_' . $version );

		return rest_ensure_response( array(
			'status'  => 'success',
			'message' => __( 'Templates library have been synchronized', 'jet-theme-core' ),
		) );
	}
}
