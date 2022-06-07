<?php
/**
 * Wishlist Render Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Wishlist_Render' ) ) {

	/**
	 * Define Jet_CW_Wishlist_Render class
	 */
	class Jet_CW_Wishlist_Render {

		/**
		 * Initialize integration hooks
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'wp_ajax_jet_update_wish_list', array( $this, 'update_wish_list' ) );
			add_action( 'wp_ajax_nopriv_jet_update_wish_list', array( $this, 'update_wish_list' ) );

		}

		/**
		 * Processes buttons actions.
		 *
		 * @since  1.0.0
		 *
		 * @action wp_ajax_jet_update_wish_list
		 */
		function update_wish_list() {

			$pid     = isset( $_REQUEST['pid'] ) ? absint( $_REQUEST['pid'] ) : false;
			$context = isset( $_REQUEST['context'] ) ? strval( $_REQUEST['context'] ) : false;

			$data = jet_cw()->wishlist_data->update_data_wishlist( $pid, $context );

			wp_send_json( array(
				'content'            => $this->render_content( $pid ),
				'wishlistItemsCount' => count( $data ),
			) );

		}

		/**
		 * Render content
		 *
		 * @param $product_id
		 *
		 * @return array
		 */
		public function render_content( $product_id ) {

			$widgets         = jet_cw()->widgets_store->get_stored_widgets();
			$widgets_content = array();

			foreach ( $widgets['wishlist'] as $selector => $widget_data ) {
				$selector = urldecode( $selector );
				$selector = str_replace( '{pid}', $product_id, $selector );

				$widget_setting = $widget_data['settings'];
				$widget_type    = $widget_data['type'];

				ob_start();
				$this->get_render_content_type( $widget_setting, $product_id, $widget_type );
				$widgets_content[ $selector ] = ob_get_clean();
			}

			return $widgets_content;

		}

		/**
		 * Render current widget type
		 *
		 * @param $widget_setting
		 * @param $product_id
		 * @param $widget_type
		 */
		public function get_render_content_type( $widget_setting, $product_id, $widget_type ) {
			switch ( $widget_type ) {
				case 'jet-wishlist-count-button' :
					jet_cw_widgets_functions()->get_wishlist_count_button( $widget_setting );
					break;
				case 'jet-wishlist-button' :
					jet_cw_widgets_functions()->get_add_to_wishlist_button( $widget_setting, $product_id );
					break;
				case 'jet-wishlist' :
					jet_cw_widgets_functions()->get_widget_wishlist( $widget_setting );
					break;
				default:
					do_action( 'jet-cw/wishlist/render/get-content/' . $widget_type, $widget_setting, $product_id );
					break;
			}
		}

		/**
		 * Render wishlist button
		 *
		 * @param $settings
		 */
		public function render_wishlist_button( $settings ) {

			global $product;

			if ( $product ) {
				$product_id = $product->get_id();

				$selector = 'a.jet-wishlist-button__link[data-product-id="{pid}"][data-widget-id="' . $settings['_widget_id'] . '"]';

				jet_cw()->widgets_store->store_widgets_types( 'jet-wishlist-button', $selector, $settings, 'wishlist' );

				echo '<div class="jet-wishlist-button__container">';
				jet_cw_widgets_functions()->get_add_to_wishlist_button( $settings, $product_id );
				echo '</div>';
			} else {
				printf( '<h5 class=jet-wishlist-button--missing">%s</h5>', esc_html__( 'Product ID not found.', 'jet-cw' ) );
			}

		}

	}

}