<?php
/**
 * Wishlist Data Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Wishlist_Data' ) ) {

	/**
	 * Define Jet_CW_Wishlist_Data class
	 */
	class Jet_CW_Wishlist_Data {

		/**
		 * Initialize variable for wishlist store type.
		 *
		 * @var null
		 */
		public $store_type = null;

		/**
		 * Initialize integration hooks
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'start_session' ) );

			$this->localize_wishlist_data();

			$this->store_type = jet_cw()->settings->get( 'wishlist_store_type' );

		}

		/**
		 * Run session
		 *
		 * @return void
		 */
		public function start_session() {
			if ( ! session_id() && 'session' === $this->store_type ) {
				session_start();
			}
		}

		/**
		 * Update wishlist data
		 *
		 * @param $pid
		 * @param $context
		 *
		 * @return array
		 */
		function update_data_wishlist( $pid, $context ) {

			$wishlist_list = $this->get_wish_list();
			$product_index = array_search( $pid, $wishlist_list );

			switch ( $context ) {
				case 'add':
					if ( ! $product_index ) {
						$wishlist_list[] = $pid;
					}
					break;
				case 'remove':
					if ( isset( $product_index ) ) {
						$index = array_search( $pid, $wishlist_list );
						unset( $wishlist_list[ $index ] );
					}
					break;
			}

			$this->set_wish_list( $wishlist_list );

			return $wishlist_list;

		}

		/**
		 * Returns products ids in wishlist.
		 *
		 * @return array The array of products ids in wishlist.
		 * @since 1.0.0
		 *
		 */
		public function get_wish_list() {

			switch ( $this->store_type ) {
				case 'session':
					$wishlist_list = isset( $_SESSION['jet-wish-list'] ) ? $_SESSION['jet-wish-list'] : '';
					break;
				case 'cookies':
					$wishlist_list = isset( $_COOKIE['jet-wish-list'] ) ? $_COOKIE['jet-wish-list'] : '';
					break;
				default:
					$wishlist_list = '';
					break;
			}

			$save_for_logged_user = filter_var( jet_cw()->settings->get( 'save_user_wish_list' ), FILTER_VALIDATE_BOOLEAN );

			if ( is_user_logged_in() && $save_for_logged_user ) {
				$wishlist_list = get_user_meta( get_current_user_id(), 'jet_wish_list', true );
			}

			if ( ! empty( $wishlist_list ) ) {
				$wishlist_list = explode( ':', $wishlist_list );
			} else {
				$wishlist_list = array();
			}

			foreach ( $wishlist_list as $key => $value ) {
				$product = get_post( $value );

				if ( empty( $product ) || 'publish' !== get_post_status( $product ) ) {
					array_splice( $wishlist_list, $key, 1 );
				}
			}

			return $wishlist_list;

		}

		/**
		 * Sets new list of products to wishlist.
		 *
		 * @param array $wishlist_list The new array of products to wishlist.
		 *
		 * @since 1.0.0
		 *
		 */
		public function set_wish_list( $wishlist_list = [] ) {

			$save_for_logged_user = filter_var( jet_cw()->settings->get( 'save_user_wish_list' ), FILTER_VALIDATE_BOOLEAN );
			$value                = implode( ':', $wishlist_list );

			switch ( $this->store_type ) {
				case 'session':
					$_SESSION['jet-wish-list'] = $value;
					break;
				case 'cookies':
					jet_cw()->widgets_store->set_cookie( 'jet-wish-list', $value );
					break;
				default:
					break;
			}

			if ( is_user_logged_in() && $save_for_logged_user ) {
				update_user_meta( get_current_user_id(), 'jet_wish_list', $value );
			}

		}

		/**
		 * Localize data for wishlist
		 */
		public function localize_wishlist_data() {

			$localized_data = apply_filters( 'jet-cw/wishlist/localized-data', array(
				'wishlistItemsCount' => count( $this->get_wish_list() ),
			) );

			jet_cw()->widgets_store->add_localized_data( $localized_data );

		}

	}

}