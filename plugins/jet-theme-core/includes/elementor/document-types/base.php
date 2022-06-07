<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Jet_Document_Base extends Elementor\Core\Base\Document {

	/**
	 * @return string
	 */
	public function get_name() {
		return '';
	}

	/**
	 * @return string
	 */
	public static function get_title() {
		return '';
	}

	/**
	 * @return array
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['admin_tab_group'] = '';
		$properties['support_kit']     = true;

		return $properties;
	}

	/**
	 * @return bool
	 */
	public function has_conditions() {
		return true;
	}

	/**
	 * @return array
	 */
	public function get_conditions_groups() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_preview_as_query_args() {
		return array();
	}

	/**
	 * @return array
	 */
	protected function get_default_data() {

		if ( $this->has_conditions() ) {
			return array(
				'id' => 0,
				'settings' => array(
					'jet_conditions' => array(
						'main' => '',
					),
				),
			);
		} else {
			return array(
				'id' => 0,
				'settings' => array(),
			);
		}

	}

	/**
	 *
	 */
	protected function register_controls() {
		parent::register_controls();
	}

	/**
	 * @param null $data
	 * @param false $with_html_content
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_elements_raw_data( $data = null, $with_html_content = false ) {

		jet_theme_core()->elementor_manager->switch_to_preview_query();

		$editor_data = parent::get_elements_raw_data( $data, $with_html_content );

		jet_theme_core()->elementor_manager->restore_current_query();

		return $editor_data;
	}

	/**
	 * @param $data
	 *
	 * @return string
	 * @throws Exception
	 */
	public function render_element( $data ) {

		jet_theme_core()->elementor_manager->switch_to_preview_query();

		$render_html = parent::render_element( $data );

		jet_theme_core()->elementor_manager->restore_current_query();

		return $render_html;
	}

}
