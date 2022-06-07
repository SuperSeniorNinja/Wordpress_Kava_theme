<?php
/**
 * JetEngine compatibility package.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Engine_Package' ) ) {

	/**
	 * Define Jet_CW_Engine_Package class.
	 */
	class Jet_CW_Engine_Package {

		/**
		 * Jet_CW_Engine_Package constructor.
		 */
		public function __construct() {
			add_filter( 'jet-engine/ajax/get_listing/response', [ $this, 'define_cw_listing_lazy_load_data' ], 10, 2 );
		}

		/**
		 * Add the JetCompareWishlist widgets data to JetEngine response after lazy loading.
		 *
		 * @param array $response
		 * @param       $settings
		 *
		 * @return array
		 */
		public function define_cw_listing_lazy_load_data( $response, $settings ) {

			if ( 'yes' !== $settings['lazy_load'] ) {
				return $response;
			}

			$listing_settings = get_post_meta( $settings['lisitng_id'], '_elementor_page_settings', true );

			if ( 'product' !== $listing_settings['listing_post_type'] ) {
				return $response;
			}

			$response['jetCompareWishlistWidgets'] = jet_cw()->widgets_store->get_widgets_types();

			return $response;

		}

	}

}

new Jet_CW_Engine_Package();
