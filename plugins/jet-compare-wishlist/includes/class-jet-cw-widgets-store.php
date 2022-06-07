<?php
/**
 * Elementor Widgets Store class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Widgets_Store' ) ) {

	/**
	 * Define Jet_CW_Widgets_Store class
	 */
	class Jet_CW_Widgets_Store {


		/**
		 * Widgets Types Data
		 *
		 * @var array
		 */
		public $widgets_types;

		/**
		 * Check if widgets data was added
		 *
		 * @var bool
		 */
		public $widgets_data_added = false;

		/**
		 * Localized Data
		 *
		 * @var bool
		 */
		public $localized_data = array();

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'localize_data' ) );
		}

		/**
		 * Add data that needed localize
		 *
		 * @param array $data
		 */
		public function add_localized_data( $data = array() ) {
			$this->localized_data = array_merge( $this->localized_data, $data );
		}

		public function get_localized_data() {
			return $this->localized_data;
		}

		/**
		 * Store widgets types
		 *
		 * @param $widget_type
		 * @param $selector
		 * @param $widget_settings
		 */
		public function store_widgets_types( $widget_type, $selector, $widget_settings, $context ) {

			$this->enqueue_data();

			$this->widgets_types[ $context ][ urlencode( $selector ) ] = array(
				'settings' => $widget_settings,
				'type'     => $widget_type,
			);

		}

		/**
		 * Returns all provider settings
		 *
		 * @return array
		 */
		public function get_widgets_types() {
			return $this->widgets_types;
		}

		/**
		 * Return stored widgets data
		 *
		 * @return array
		 */
		public function get_stored_widgets() {
			return ! empty( $_REQUEST['widgets_data'] ) ? $_REQUEST['widgets_data'] : array();
		}

		/**
		 * Enqueue widgets data after render page
		 *
		 */
		public function enqueue_data() {
			if ( ! $this->widgets_data_added ) {
				add_action( 'wp_footer', array( $this, 'localize_data' ), 11 );
				$this->widgets_data_added = true;
			}
		}

		/**
		 * Set cookies for storing Compare&Wishlist items.
		 *
		 * @param $name
		 * @param $value
		 */
		public function set_cookie( $name, $value ) {

			$expire = time() + YEAR_IN_SECONDS;
			$secure = ( false !== strstr( get_option( 'home' ), 'https:' ) && is_ssl() );

			setcookie(
				$name,
				$value,
				$expire,
				COOKIEPATH ? COOKIEPATH : '/',
				COOKIE_DOMAIN,
				$secure,
				true
			);

			$_COOKIE[ $name ] = $value;

		}

		/**
		 * Enqueue filter scripts
		 */
		public function localize_data() {

			wp_enqueue_script(
				'jet-cw',
				jet_cw()->plugin_url( 'assets/js/jet-cw.min.js' ),
				array( 'jquery', 'elementor-frontend' ),
				jet_cw()->get_version(),
				true
			);

			$localized_data = apply_filters( 'jet-cw/localized-data', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'widgets' => $this->widgets_types,
			) );

			$localized_data = array_merge( $localized_data, $this->get_localized_data() );

			wp_localize_script( 'jet-cw', 'JetCWSettings', $localized_data );

		}

	}

}