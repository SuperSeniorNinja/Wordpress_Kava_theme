<?php
/**
 * WPML compatibility package
 */

class WPML_Jet_CW_Compare extends WPML_Elementor_Module_With_Items {

	/**
	 * Returns items field key
	 *
	 * @return string
	 */
	public function get_items_field() {
		return 'compare_table_data';
	}

	/**
	 * Returns fields keys
	 *
	 * @return array
	 */
	public function get_fields() {
		return array( 'compare_table_data_title', 'compare_table_data_remove_text' );
	}

	/**
	 * Returns compare table data titles
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'compare_table_data_title':
				return esc_html__( 'Jet Compare: Title', 'jet-cw' );
			case 'compare_table_data_remove_text':
				return esc_html__( 'Jet Compare: Remove', 'jet-cw' );
			default:
				return '';
		}
	}

	/**
	 * Returns editor type
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'compare_table_data_title':
				return 'LINE';
			case 'compare_table_data_remove_text':
				return 'LINE';
			default:
				return '';
		}
	}

}
