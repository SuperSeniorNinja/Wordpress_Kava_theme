<?php
/**
 * Products Gallery templates functions class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Functions' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Functions class
	 */
	class Jet_Woo_Product_Gallery_Functions {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Print photoswipe gallery trigger button
		 *
		 * @param $icon
		 */
		public function get_gallery_trigger_button( $icon ) {

			echo '<a href="#" class="jet-woo-product-gallery__trigger">';
			echo sprintf( '<span class="jet-woo-product-gallery__trigger-icon jet-product-gallery-icon">%s</span>', $icon );
			echo '</a>';

		}

		/**
		 * Returns carousel arrow
		 *
		 * @param string $classes Arrow additional classes list.
		 * @param string $arrow   Arrow.
		 *
		 * @return string
		 */
		public function get_slider_arrow( $classes, $arrow ) {
			if ( empty( $arrow ) ) {
				return '';
			}

			return apply_filters( 'jet-woo-product-gallery/slider/arrows-format', sprintf( '<span class="jet-product-gallery-icon %s">%s</span>', $classes, $arrow ) );
		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance( $shortcodes = [] ) {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Product_Gallery_Functions
 *
 * @return object
 */
function jet_woo_product_gallery_functions() {
	return Jet_Woo_Product_Gallery_Functions::get_instance();
}
