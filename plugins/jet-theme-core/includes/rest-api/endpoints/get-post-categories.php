<?php
namespace Jet_Theme_Core\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Get_Post_Categories extends Base {

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
		return 'get-post-categories';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$categories = \Jet_Theme_Core\Utils::get_terms_by_tax( 'category', '' );

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'Success', 'jet-theme-core' ),
			'data'    => $categories,
		] );
	}

}
