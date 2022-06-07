<?php
/**
 * lass Jet Woo Product Gallery Integration
 *
 * @package   JetWooProductGallery
 * @author    Crocoblock
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Integration' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Integration class
	 */
	class Jet_Woo_Product_Gallery_Integration {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Check if processing elementor widget
		 *
		 * @var boolean
		 */
		private $is_elementor_ajax = false;

		/**
		 * Initialize integration hooks
		 *
		 * @return void
		 */
		public function init() {

			add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );

			add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ), 10 );

			add_action( 'wp_ajax_elementor_render_widget', array( $this, 'set_elementor_ajax' ), 10, -1 );

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'editor_styles' ) );

		}

		/**
		 * Enqueue editor styles
		 *
		 * @return void
		 */
		public function editor_styles() {
			wp_enqueue_style(
				'jet-woo-product-gallery-icons',
				jet_woo_product_gallery()->plugin_url( 'assets/css/jet-woo-product-gallery-icons.css' ),
				array(),
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Set $this->is_elementor_ajax to true on Elementor AJAX processing
		 *
		 * @return  void
		 */
		public function set_elementor_ajax() {
			$this->is_elementor_ajax = true;
		}

		/**
		 * Check if we currently in Elementor mode
		 *
		 * @return void
		 */
		public function in_elementor() {

			$result = false;

			if ( wp_doing_ajax() ) {
				$result = $this->is_elementor_ajax;
			} elseif ( Elementor\Plugin::instance()->editor->is_edit_mode()
				|| Elementor\Plugin::instance()->preview->is_preview_mode() ) {
				$result = true;
			}

			return apply_filters( 'jet-woo-product-gallery/in-elementor', $result );

		}

		/**
		 * Register plugin widgets
		 *
		 * @param object $widgets_manager Elementor widgets manager instance.
		 *
		 * @return void
		 */
		public function register_widgets( $widgets_manager ) {

			$product_gallery_available_widgets = jet_woo_product_gallery_settings()->get( 'product_gallery_available_widgets' );

			require jet_woo_product_gallery()->plugin_path( 'includes/base/class-jet-woo-product-gallery-base.php' );

			foreach ( glob( jet_woo_product_gallery()->plugin_path( 'includes/widgets/' ) . '*.php' ) as $file ) {
				$slug    = basename( $file, '.php' );
				$enabled = isset( $product_gallery_available_widgets[ $slug ] ) ? $product_gallery_available_widgets[ $slug ] : '';

				if ( filter_var( $enabled, FILTER_VALIDATE_BOOLEAN ) || ! $product_gallery_available_widgets ) {
					$this->register_widget( $file, $widgets_manager );
				}
			}

		}


		/**
		 * Register addon by file name
		 *
		 * @param string $file            File name.
		 * @param object $widgets_manager Widgets manager instance.
		 *
		 * @return void
		 */
		public function register_widget( $file, $widgets_manager ) {

			$base  = basename( str_replace( '.php', '', $file ) );
			$class = ucwords( str_replace( '-', ' ', $base ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'Elementor\%s', $class );

			require $file;

			if ( class_exists( $class ) ) {
				$widgets_manager->register_widget_type( new $class );
			}

		}

		/**
		 * Register cherry category for elementor if not exists
		 *
		 * @return void
		 */
		public function register_category() {

			$elements_manager            = Elementor\Plugin::instance()->elements_manager;
			$jet_woo_product_gallery_cat = 'jet-woo-product-gallery';

			$elements_manager->add_category(
				$jet_woo_product_gallery_cat,
				array(
					'title' => esc_html__( 'Jet Product Gallery', 'jet-woo-product-gallery' ),
					'icon'  => 'font',
				),
				1
			);

		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance( $shortcodes = array() ) {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;

		}
	}


}

/**
 * Returns instance of Jet_Woo_Product_Gallery_Integration
 *
 * @return object
 */
function jet_woo_product_gallery_integration() {
	return Jet_Woo_Product_Gallery_Integration::get_instance();
}