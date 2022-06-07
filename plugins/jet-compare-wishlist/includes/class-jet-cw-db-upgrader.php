<?php
/**
 * Jet Compare & Wishlist DB Upgrader Сlass
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_DB_Upgrader' ) ) {

	/**
	 * Define Jet_CW_DB_Upgrader class
	 */
	class Jet_CW_DB_Upgrader {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {
			/**
			 * Plugin initialized on new Jet_CW_DB_Upgrader call.
			 * Please ensure, that it called only on admin context
			 */
			$this->init_upgrader();
		}

		/**
		 * Initialize upgrader module
		 *
		 * @return void
		 */
		public function init_upgrader() {
			new CX_Db_Updater(
				array(
					'slug'      => 'jet-cw',
					'version'   => '1.4.0',
					'callbacks' => array(
						'1.3.0' => array(
							array( $this, 'clear_elementor_cache' ),
						),
						'1.3.1' => array(
							array( $this, 'clear_elementor_cache' ),
						),
						'1.3.2' => array(
							array( $this, 'clear_elementor_cache' ),
						),
						'1.4.0' => array(
							array( $this, 'clear_elementor_cache' ),
						),
					),
				)
			);
		}

		/**
		 * Update db updater 1.3.0
		 *
		 * @return void
		 */
		public function clear_elementor_cache() {
			if ( class_exists( 'Elementor\Plugin' ) ) {
				jet_cw()->elementor()->files_manager->clear_cache();
			}
		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 * @access public
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
 * Returns instance of Jet_CW_DB_Upgrader
 *
 * @return object
 */
function jet_cw_db_upgrader() {
	return Jet_CW_DB_Upgrader::get_instance();
}