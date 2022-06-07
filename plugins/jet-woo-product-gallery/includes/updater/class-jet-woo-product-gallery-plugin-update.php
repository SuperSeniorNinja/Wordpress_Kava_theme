<?php
/**
 * Class for the update plugins.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require jet_woo_product_gallery()->plugin_path( 'includes/updater/class-jet-woo-product-gallery-base-update.php' );

/**
 * Define plugin updater class.
 *
 * @since 1.0.0
 */
class Jet_Woo_Product_Gallery_Plugin_Update extends Jet_Woo_Product_Gallery_Base_Update {

	/**
	 * Init class parameters.
	 *
	 * @param array $attr Input attributes array.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $attr = array() ) {
		$this->base_init( $attr );

		add_action( 'pre_set_site_transient_update_plugins', array( $this, 'update' ) );
	}

	/**
	 * Process update.
	 *
	 * @param object $data Update data.
	 *
	 * @return object
	 * @since  1.0.0
	 */
	public function update( $data ) {

		$new_update = $this->check_update();

		if ( $new_update['version'] ) {
			$this->api['plugin'] = $this->api['slug'] . '/' . $this->api['slug'] . '.php';

			$update = new stdClass();

			$update->slug        = $this->api['slug'];
			$update->plugin      = $this->api['plugin'];
			$update->new_version = $new_update['version'];
			$update->url         = isset( $this->api['details_url'] ) ? $this->api['details_url'] : false;
			$update->package     = $new_update['package'];

			$data->response[ $this->api['plugin'] ] = $update;
		}

		return $data;

	}

}