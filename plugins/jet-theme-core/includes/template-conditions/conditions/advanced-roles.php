<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Roles extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'roles';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Roles', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'advanced';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 100;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_page';
	}

	/**
	 * [get_control description]
	 * @return [type] [description]
	 */
	public function get_control() {
		return [
			'type'        => 'f-select',
			'placeholder' => __( 'Select roles', 'jet-theme-core' ),
		];
	}

	/**
	 * [get_avaliable_options description]
	 * @return [type] [description]
	 */
	public function get_avaliable_options() {

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$options = [];

		foreach ( get_editable_roles() as $role_slug => $role_data ) {
			$options[] = [
				'label' => $role_data['name'],
				'value' => $role_slug,
			];
		}

		if ( ! empty( $options ) ) {
			$options[] = [
				'label' => esc_html__( 'Guest', 'jet-theme-core' ),
				'value' => 'guest',
			];
		}

		return $options;
	}

	/**
	 * [ajax_action description]
	 * @return [type] [description]
	 */
	public function ajax_action() {
		return false;
	}

	/**
	 * [get_label_by_value description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function get_label_by_value( $value = '' ) {

		$roles_string = '';

		if ( ! empty( $value ) && is_array( $value ) ) {
			$roles_string = implode( ', ', $value );
		}

		return $roles_string;
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $arg = '' ) {

		if ( empty( $arg ) ) {
			return false;
		}

		if ( $this->is_avaliable_for_user( $arg ) ) {
			return true;
		}

		return false;
	}

	/**
	 * [is_avaliable_for_user description]
	 * @param  [type]  $popup_roles [description]
	 * @return boolean              [description]
	 */
	public function is_avaliable_for_user( $roles ) {

		if ( empty( $roles ) ) {
			return true;
		}

		$user     = wp_get_current_user();
		$is_guest = empty( $user->roles ) ? true : false;

		if ( ! $is_guest ) {
			$user_role = $user->roles[0];
		} else {
			$user_role = 'guest';
		}

		if ( in_array( $user_role, $roles ) ) {
			return true;
		}

		return false;
	}

}
