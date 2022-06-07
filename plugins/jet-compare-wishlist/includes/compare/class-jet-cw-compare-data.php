<?php
/**
 * Compare Data Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Compare_Data' ) ) {

	/**
	 * Define Jet_CW_Compare_Data class
	 */
	class Jet_CW_Compare_Data {

		/**
		 * Initialize variable for compare store type.
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

			$this->localize_compare_data();

			$this->store_type = jet_cw()->settings->get( 'compare_store_type' );

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
		 * Update compare data
		 *
		 * @param $pid
		 * @param $context
		 *
		 * @return array
		 */
		function update_data_compare( $pid, $context ) {

			$compare_list  = $this->get_compare_list();
			$product_index = array_search( $pid, $compare_list );

			switch ( $context ) {
				case 'add':
					if ( ! $product_index ) {
						$compare_list[] = $pid;
					}
					break;
				case 'remove':
					if ( isset( $product_index ) ) {
						$index = array_search( $pid, $compare_list );
						unset( $compare_list[ $index ] );
					}
					break;
			}

			$this->set_compare_list( $compare_list );

			return $compare_list;

		}

		/**
		 * Returns products ids in compare table.
		 *
		 * @return array The array of products ids in compare.
		 * @since 1.0.0
		 *
		 */
		public function get_compare_list() {

			switch ( $this->store_type ) {
				case 'session':
					$compare_list = isset( $_SESSION['jet-compare-list'] ) ? $_SESSION['jet-compare-list'] : '';
					break;
				case 'cookies':
					$compare_list = isset( $_COOKIE['jet-compare-list'] ) ? $_COOKIE['jet-compare-list'] : '';
					break;
				default:
					$compare_list = '';
					break;
			}

			$save_for_logged_user = filter_var( jet_cw()->settings->get( 'save_user_compare_list' ), FILTER_VALIDATE_BOOLEAN );

			if ( is_user_logged_in() && $save_for_logged_user ) {
				$compare_list = get_user_meta( get_current_user_id(), 'jet_compare_list', true );
			}

			if ( ! empty( $compare_list ) ) {
				$compare_list = explode( ':', $compare_list );
			} else {
				$compare_list = array();
			}

			foreach ( $compare_list as $key => $value ) {
				$product = get_post( $value );

				if ( empty( $product ) || 'publish' !== get_post_status( $product ) ) {
					array_splice( $compare_list, $key, 1 );
				}
			}

			return $compare_list;

		}

		/**
		 * Sets new list of products to compare.
		 *
		 * @param array $compare_list The new array of products to compare.
		 *
		 * @since 1.0.0
		 *
		 */
		public function set_compare_list( $compare_list = [] ) {

			$max_compare_items    = filter_var( jet_cw()->settings->get( 'compare_page_max_items' ), FILTER_VALIDATE_INT );
			$save_for_logged_user = filter_var( jet_cw()->settings->get( 'save_user_compare_list' ), FILTER_VALIDATE_BOOLEAN );

			if ( $max_compare_items >= count( $compare_list ) ) {
				$value = implode( ':', $compare_list );

				switch ( $this->store_type ) {
					case 'session':
						$_SESSION['jet-compare-list'] = $value;
						break;
					case 'cookies':
						jet_cw()->widgets_store->set_cookie( 'jet-compare-list', $value );
						break;
					default:
						break;
				}

				if ( is_user_logged_in() && $save_for_logged_user ) {
					update_user_meta( get_current_user_id(), 'jet_compare_list', $value );
				}
			}

		}

		/**
		 * Localize data for compare
		 */
		public function localize_compare_data() {

			$localized_data = apply_filters( 'jet-cw/compare/localized-data', array(
				'compareMaxItems'   => filter_var( jet_cw()->settings->get( 'compare_page_max_items' ), FILTER_VALIDATE_INT ),
				'compareItemsCount' => count( $this->get_compare_list() ),
			) );

			jet_cw()->widgets_store->add_localized_data( $localized_data );

		}

	}

}