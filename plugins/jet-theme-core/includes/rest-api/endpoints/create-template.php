<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Create_Template extends Base {

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
		return 'create-template';
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
			'type' => array(
				'default'    => 'jet-header',
				'required'   => false,
			),
			'content_type' => array(
				'default'    => 'default',
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
				'data'    => [],
			) );
		}

		$name = $args['name'];
		$type = $args['type'];
		$content = $args['content'];

		$template_data = jet_theme_core()->templates->create_template( $type, $content, $name );

		return rest_ensure_response( [
			'success' => 'success' === $template_data['type'] ? true : false,
			'message' => $template_data['message'],
			'data' => [
				'redirect'      => $template_data['redirect'],
				'newTemplateId' => $template_data['newTemplateId'],
				'templatesList' => jet_theme_core()->templates->get_template_list(),
			]
		] );
	}

}
