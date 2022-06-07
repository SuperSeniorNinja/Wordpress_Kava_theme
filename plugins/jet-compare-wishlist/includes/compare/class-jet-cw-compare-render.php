<?php
/**
 * Compare Render Class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Compare_Render' ) ) {

	/**
	 * Define Jet_CW_Compare_Render class
	 */
	class Jet_CW_Compare_Render {

		/**
		 * Initialize integration hooks
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'wp_ajax_jet_update_compare_list', array( $this, 'update_compare_list' ) );
			add_action( 'wp_ajax_nopriv_jet_update_compare_list', array( $this, 'update_compare_list' ) );

			add_action( 'wp_footer', array( $this, 'render_compare_messages' ) );

		}

		/**
		 * Processes buttons actions.
		 *
		 * @since  1.0.0
		 *
		 * @action wp_ajax_jet_update_compare_list
		 */
		function update_compare_list() {

			$pid     = isset( $_REQUEST['pid'] ) ? absint( $_REQUEST['pid'] ) : false;
			$context = isset( $_REQUEST['context'] ) ? strval( $_REQUEST['context'] ) : false;

			$data = jet_cw()->compare_data->update_data_compare( $pid, $context );

			wp_send_json( array(
				'content'           => $this->render_content( $pid ),
				'compareItemsCount' => count( $data ),
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

			foreach ( $widgets['compare'] as $selector => $widget_data ) {
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
				case 'jet-compare-count-button' :
					jet_cw_widgets_functions()->get_compare_count_button( $widget_setting );
					break;
				case 'jet-compare-button' :
					jet_cw_widgets_functions()->get_add_to_compare_button( $widget_setting, $product_id );
					break;
				case 'jet-compare' :
					jet_cw_widgets_functions()->get_widget_compare_table( $widget_setting );
					break;
				default:
					do_action( 'jet-cw/compare/render/get-content/' . $widget_type, $widget_setting, $product_id );
					break;
			}
		}

		/**
		 * Render compare button
		 *
		 * @param $settings
		 */
		public function render_compare_button( $settings ) {

			global $product;

			if ( $product ) {
				$product_id = $product->get_id();

				$selector = 'a.jet-compare-button__link[data-product-id="{pid}"][data-widget-id="' . $settings['_widget_id'] . '"]';

				jet_cw()->widgets_store->store_widgets_types( 'jet-compare-button', $selector, $settings, 'compare' );

				echo '<div class="jet-compare-button__container">';
				jet_cw_widgets_functions()->get_add_to_compare_button( $settings, $product_id );
				echo '</div>';
			} else {
				printf( '<h5 class=jet-compare-button--missing">%s</h5>', esc_html__( 'Product ID not found.', 'jet-cw' ) );
			}

		}

		/**
		 * Messages html
		 */
		public function render_compare_messages() {
			printf( '<div class="jet-compare-message jet-compare-message--max-items" style="display: none">%s</div>', __( 'You can`t add more product in compare', 'jet-cw' ) );
		}

	}

}