<?php
namespace Jet_Theme_Core\Compatibility;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Jet_Style_Manager {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		if ( ! defined( 'JET_SM_VERSION' ) ) {
			return false;
		}

		add_action( 'jet-theme-core/location/after-render/default-content', array( $this, 'print_template_css'), 10, 2 );
	}

	/**
	 * [add_verification description]
	 */
	public function print_template_css( $template_id, $location ) {
		$this->render_blocks_style( $template_id );
		$this->render_blocks_fonts( $template_id );
	}

	/**
	 * @param false $ID
	 * @param string $format
	 */
	public function render_blocks_style( $ID = false, $format = '<style class="jet-sm-gb-style">%s</style>' ){
		$style = \JET_SM\Gutenberg\Style_Manager::get_instance()->get_blocks_style( $ID );

		if( $style ){
			printf( $format, $style );
		}
	}

	/**
	 * @param false $ID
	 */
	public function render_blocks_fonts( $ID = false ){
		$fonts = \JET_SM\Gutenberg\Style_Manager::get_instance()->get_blocks_fonts( $ID );

		if( $fonts ){
			$fonts = trim( $fonts, '"' );
			$fonts = wp_unslash( $fonts );

			echo wp_kses( $fonts, [ 'link' => [ 'href' => true, 'rel' => true ] ] );
		}
	}

}
