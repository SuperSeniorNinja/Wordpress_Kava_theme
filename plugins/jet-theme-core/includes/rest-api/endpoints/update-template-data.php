<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Update_Template_Data extends Base {

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
		return 'update-template-data';
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
			'template_data' => array(
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
		$template_data = $args['template_data'];

		$update_data = jet_theme_core()->theme_builder->page_templates_manager->update_template_data( $template_id, $template_data );

		return rest_ensure_response( [
			'success' => true,
			'message' => $update_data['message'],
			'data'    => $update_data['data'],
		] );
	}

}
