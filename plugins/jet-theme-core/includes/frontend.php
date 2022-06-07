<?php
namespace Jet_Theme_Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Frontend_Manager {
	/**
	 * @param $template_id
	 * @param $location
	 */
	public function render_default_template_content( $render_status, $template_id, $location ) {

		if ( ! $template_id ) {
			return false;
		}

		$content_type = jet_theme_core()->templates->get_template_content_type( $template_id );

		if ( 'default' !== $content_type ) {
			return false;
		}

		// Define elementor location render instance
		$block_editor_location = new \Jet_Theme_Core\Locations\Render\Block_Editor_Render( [
			'template_id' => $template_id,
			'location'    => $location,
		] );

		$render_status = $block_editor_location->render();

		return $render_status;
	}

	/**
	 * Frontend constructor.
	 */
	public function __construct() {
		add_filter( 'jet-theme-core/location/render/default-location-content', array( $this, 'render_default_template_content' ), 10, 3 );
	}

}
