<?php
namespace Jet_CW;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Controller class
 */
class Settings {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Contain modules subpages
	 * @var array
	 */
	public $subpage_modules = array();

	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {

		$this->subpage_modules = apply_filters( 'jet-cw/settings/registered-subpage-modules', array(
			'jet-cw-compare-settings' => array(
				'class' => '\\Jet_CW\\Settings\\Compare',
				'args'  => array(),
			),
			'jet-cw-wishlist-settings' => array(
				'class' => '\\Jet_CW\\Settings\\Wishlist',
				'args'  => array(),
			),
			'jet-cw-avaliable-addons' => array(
				'class' => '\\Jet_CW\\Settings\\Avaliable_Addons',
				'args'  => array(),
			),
		) );

		add_action( 'init', array( $this, 'register_settings_category' ), 10 );
		add_action( 'init', array( $this, 'init_plugin_subpage_modules' ), 10 );

	}

	/**
	 * Register settings page category
	 */
	public function register_settings_category() {
		\Jet_Dashboard\Dashboard::get_instance()->module_manager->register_module_category( array(
			'name'     => esc_html__( 'JetCompareWishlist', 'jet-cw' ),
			'slug'     => 'jet-cw-settings',
			'priority' => 1
		) );
	}

	/**
	 * Initialize plugin subpages modules
	 */
	public function init_plugin_subpage_modules() {

		require jet_cw()->plugin_path( 'includes/settings/subpage-modules/compare.php' );
		require jet_cw()->plugin_path( 'includes/settings/subpage-modules/wishlist.php' );
		require jet_cw()->plugin_path( 'includes/settings/subpage-modules/avaliable-addons.php' );

		foreach ( $this->subpage_modules as $subpage => $subpage_data ) {
			\Jet_Dashboard\Dashboard::get_instance()->module_manager->register_subpage_module( $subpage, $subpage_data );
		}

	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;

	}

}

