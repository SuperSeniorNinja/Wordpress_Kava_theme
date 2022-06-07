<?php
/**
 * Popup compatibility package.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Popup_Package' ) ) {

	/**
	 * Define Jet_CW_Popup_Package class
	 */
	class Jet_CW_Popup_Package {

		/**
		 * Jet_CW_Popup_Package constructor.
		 */
		public function __construct() {
			add_filter( 'jet-popup/ajax-request/after-content-define/post-data', array( $this, 'define_popup_qw' ) );
		}

		public function define_popup_qw( $popup_data ) {

			if ( empty( $popup_data['isJetWooBuilder'] ) || empty( $popup_data['productId'] ) || empty( $popup_data['templateId'] ) ) {
				return $popup_data;
			}

			$popup_data['jetCompareWishlistWidgets'] = jet_cw()->widgets_store->get_widgets_types();

			return $popup_data;

		}

	}

}

new Jet_CW_Popup_Package();
