<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Get_Page_Template_List extends Base {

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
		return 'get-page-template-list';
	}

	public function get_args() {
		return array(
			'templateName' => array(
				'default'    => false,
				'required'   => false,
			),
			'orderBy' => array(
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

		$template_name = $args['templateName'];
		$order_by = $args['orderBy'];

		$page_template_list = jet_theme_core()->theme_builder->page_templates_manager->get_page_template_list( $template_name, $order_by );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Success', 'jet-theme-core' ),
			'data'    => $page_template_list,
		] );
	}

}
