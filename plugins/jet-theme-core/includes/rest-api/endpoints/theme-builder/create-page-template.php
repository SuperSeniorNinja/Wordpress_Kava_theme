<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Create_Page_Template extends Base {

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
		return 'create-page-template';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {

		return array(
			'name' => array(
				'default'    => '',
				'required'   => false,
			),
			'conditions' => array(
				'default'    => false,
				'required'   => false,
			),
			'layout' => array(
				'default'    => false,
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

		$name = $args['name'];
		$conditions = $args['conditions'];
		$layout = $args['layout'];

		$create_template_data = jet_theme_core()->theme_builder->page_templates_manager->create_page_template( $name, $conditions, $layout );

		return rest_ensure_response( [
			'success' => 'success' === $create_template_data[ 'type' ] ? true : false,
			'message' => $create_template_data[ 'message' ],
			'data'    => [
				'newTemplateId' => $create_template_data[ 'data' ][ 'newTemplateId' ],
				'list'          => $create_template_data[ 'data' ][ 'list' ],
			]
		] );
	}

}
