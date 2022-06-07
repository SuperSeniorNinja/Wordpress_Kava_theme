<?php
namespace Jet_Theme_Core\Elementor;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Locations {

	/**
	 * @var array
	 */
	private $_pro_locations = array();

	/**
	 * Return all locations data
	 *
	 * @return array
	 */
	public function get_locations() {
		return $this->_pro_locations;
	}

	/**
	 * @param $id
	 * @param $structure_instance
	 */
	public function define_pro_locations( $id, $structure_instance ) {

		if ( $structure_instance->pro_location_mapping() ) {
			$this->_pro_locations[ $id ] = $structure_instance->pro_location_mapping();
		}
	}

	/**
	 * Register Elementor Pro locations
	 *
	 * @param  [type] $elementor_theme_manager [description]
	 * @return [type]                          [description]
	 */
	public function register_elementor_locations( $elementor_theme_manager ) {

		if ( ! \Jet_Theme_Core\Utils::has_elementor_pro() ) {
			return false;
		}

		/*$prevent_locations = jet_theme_core()->settings->get( 'prevent_pro_locations' );

		if ( filter_var( $prevent_locations, FILTER_VALIDATE_BOOLEAN ) ) {
			return false;
		}*/

		foreach ( $this->get_locations() as $jet_location => $pro_location ) {
			$elementor_theme_manager->register_location( $pro_location );
		}
	}

	/**
	 * @param int $template_id
	 * @param string $location
	 */
	public function render_elementor_template_content( $render_status, $template_id, $location ) {

		// Define elementor location render instance
		$elementor_location = new \Jet_Theme_Core\Locations\Render\Elementor_Location_Render( [
			'template_id' => $template_id,
			'location'    => $location,
		] );

		$render_status = $elementor_location->render();

		return $render_status;
	}

	/**
	 * Enqueue locations styles
	 *
	 * @return void
	 */
	/*public function enqueue_locations_styles() {

		$locations = jet_theme_core()->locations->get_locations();

		if ( empty( $locations ) ) {
			return;
		}

		$plugin = \Elementor\Plugin::instance();
		$plugin->frontend->enqueue_styles();
		$current_post_id = get_the_ID();

		foreach ( $locations as $location => $structure ) {
			$template_id = jet_theme_core()->template_conditions_manager->find_matched_conditions( $structure->get_id(), true );

			if ( $current_post_id !== $template_id ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
				$css_file->enqueue();
			}
		}
	}*/

	/**
	 * Locations constructor.
	 */
	function __construct() {
		add_action( 'jet-theme-core/locations/register', array( $this, 'define_pro_locations' ), 10, 2 );
		add_action( 'elementor/theme/register_locations', array( $this, 'register_elementor_locations' ) );
		add_filter( 'jet-theme-core/location/render/elementor-location-content', array( $this, 'render_elementor_template_content' ), 10, 3 );
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_locations_styles' ) );
	}

}
