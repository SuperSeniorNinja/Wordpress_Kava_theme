<?php
/**
 * Product Gallery assets class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Assets' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Assets class
	 */
	class Jet_Woo_Product_Gallery_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			if ( class_exists( 'WooCommerce' ) ) {
				add_action( 'elementor/frontend/after_enqueue_scripts', array( 'WC_Frontend_Scripts', 'localize_printed_scripts', ), 5 );
			} else {
				add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_photoswipe_scripts' ), 5 );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		}

		/**
		 * Enqueue admin assets
		 */
		public function enqueue_admin_assets() {
			wp_enqueue_style(
				'jet-woo-product-gallery-admin',
				jet_woo_product_gallery()->plugin_url( 'assets/css/jet-woo-product-gallery-admin.css' ),
				false,
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @return void
		 * @since  1.0.0
		 * @access public
		 */
		public function enqueue_styles() {
			wp_enqueue_style(
				'jet-woo-product-gallery',
				jet_woo_product_gallery()->plugin_url( 'assets/css/jet-woo-product-gallery.css' ),
				false,
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Enqueue plugin scripts only with elementor scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_enqueue_script(
				'jet-woo-product-gallery',
				jet_woo_product_gallery()->plugin_url( 'assets/js/jet-woo-product-gallery' . $this->suffix() . '.js' ),
				array( 'jquery', 'elementor-frontend' ),
				jet_woo_product_gallery()->get_version(),
				true
			);

			wp_localize_script(
				'jet-woo-product-gallery',
				'jetWooProductGalleryData',
				apply_filters( 'jet-woo-product-gallery/frontend/localize-data', array() )
			);

		}

		/**
		 * Enqueue photoswipe scripts and styles when woocommerce is inactive.
		 *
		 * @return void
		 */
		public function enqueue_photoswipe_scripts() {

			wp_enqueue_script(
				'photoswipe',
				jet_woo_product_gallery()->plugin_url( 'assets/js/photoswipe/photoswipe.min.js' ),
				[],
				jet_woo_product_gallery()->get_version(),
				true
			);

			wp_enqueue_script(
				'photoswipe-ui-default',
				jet_woo_product_gallery()->plugin_url( 'assets/js/photoswipe/photoswipe-ui-default.min.js' ),
				[ 'photoswipe' ],
				jet_woo_product_gallery()->get_version(),
				true
			);

			wp_enqueue_style(
				'photoswipe',
				jet_woo_product_gallery()->plugin_url( 'assets/css/photoswipe/photoswipe.min.css' ),
				[],
				jet_woo_product_gallery()->get_version()
			);

			wp_enqueue_style(
				'photoswipe-default-skin',
				jet_woo_product_gallery()->plugin_url( 'assets/css/photoswipe/default-skin/default-skin.min.css' ),
				[ 'photoswipe' ],
				jet_woo_product_gallery()->get_version()
			);

		}

		/**
		 * Add suffix to scripts
		 *
		 * @return string
		 */
		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Product_Gallery_Assets
 *
 * @return object
 */
function jet_woo_product_gallery_assets() {
	return Jet_Woo_Product_Gallery_Assets::get_instance();
}
