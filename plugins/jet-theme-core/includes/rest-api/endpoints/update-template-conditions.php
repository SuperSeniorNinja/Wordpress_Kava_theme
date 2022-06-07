<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Update_Template_Conditions extends Base {

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
		return 'update-template-conditions';
	}

	/**
	 * Returns arguments config
	 *
	 * @return [type] [description]
	 */
	public function get_args() {

		return array(
			'template_id' => array(
				'default'    => '',
				'required'   => false,
			),
			'conditions' => array(
				'default'    => array(),
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
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Server Error', 'jet-theme-core' ),
			) );
		}

		if ( empty( $args['template_id'] ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Server Error', 'jet-theme-core' ),
			) );
		}

		$template_id = $args['template_id'];
		$conditions = $args['conditions'];

		jet_theme_core()->template_conditions_manager->update_template_conditions( $template_id, $conditions );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Conditions have been saved', 'jet-theme-core' ),
			'data' => [
				'verboseHtml' => jet_theme_core()->template_conditions_manager->post_conditions_verbose( $template_id ),
			],
		] );
	}

}
