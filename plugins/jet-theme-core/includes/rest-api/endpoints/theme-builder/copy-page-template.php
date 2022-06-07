<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Copy_Page_Template extends Base {

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
		return 'copy-page-template';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {

		return array(
			'id' => array(
				'default'    => '',
				'required'   => false,
			),
		);
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$args = $request->get_params();

		if ( is_wp_error( $request ) ) {
			return rest_ensure_response( [
				'success' => false,
				'message' => __( 'Server Error', 'jet-theme-core' ),
				'data'    => []
			] );
		}

		$id = $args['id'];
		$remove_template_data = jet_theme_core()->theme_builder->page_templates_manager->copy_page_template( $id );

		return rest_ensure_response( [
			'success' => 'success' === $remove_template_data[ 'type' ] ? true : false,
			'message' => $remove_template_data[ 'message' ],
			'data'    => [
				'list' => $remove_template_data[ 'data' ][ 'list' ],
			]
		] );
	}

}
