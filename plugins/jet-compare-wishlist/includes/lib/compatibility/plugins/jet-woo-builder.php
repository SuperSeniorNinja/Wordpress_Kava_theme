<?php
/**
 * JetWooBuilder compatibility package.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Woo_Builder_Package' ) ) {

	/**
	 * Define Jet_CW_Woo_Builder_Package class.
	 */
	class Jet_CW_Woo_Builder_Package {

		/**
		 * Jet_CW_Woo_Builder_Package constructor.
		 */
		public function __construct() {
			add_filter( 'jet-woo-builder/ajax-handler/get-switcher-template/response', [ $this, 'define_cw_layout_switcher_data' ] );
		}

		/**
		 * Add the JetCompareWishlist widgets data to JetWooBuilder response after template switching.
		 *
		 * @param array $response
		 *
		 * @return array
		 */
		public function define_cw_layout_switcher_data( $response ) {

			$response['jetCompareWishlistWidgets'] = jet_cw()->widgets_store->get_widgets_types();

			return $response;

		}

	}

}

new Jet_CW_Woo_Builder_Package();
