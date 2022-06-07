<?php
/**
 * Class: Jet_Wishlist_Button
 * Name: Wishlist Button
 * Slug: jet-wishlist-button
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Wishlist_Button extends Jet_CW_Base {

	public function get_name() {
		return 'jet-wishlist-button';
	}

	public function get_title() {
		return esc_html__( 'Wishlist Button', 'jet-cw' );
	}

	public function get_icon() {
		return 'jet-cw-icon-wishlist-button';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/how-to-adjust-the-wishlist-settings-for-woocommerce-shop-using-jetcomparewishlist/';
	}

	public function get_categories() {
		return array( 'jet-cw' );
	}

	protected function register_controls() {

		jet_cw()->wishlist_integration->register_wishlist_button_content_controls( $this );

		jet_cw()->wishlist_integration->register_wishlist_button_style_controls( $this );

	}

	public static function render_callback( $settings = array() ) {
		jet_cw()->wishlist_render->render_wishlist_button( $settings );
	}

	protected function render() {

		$widget_id = $this->get_id();
		$settings  = $this->get_settings();

		$this->__context = 'render';

		$this->__open_wrap();

		$widget_settings = array(
			'button_icon_position' => $settings['wishlist_button_icon_position'],
			'use_button_icon'      => $settings['wishlist_use_button_icon'],
			'button_icon_normal'   => htmlspecialchars( $this->__render_icon( 'wishlist_button_icon_normal', '%s', '', false ) ),
			'button_label_normal'  => esc_html__( $settings['wishlist_button_label_normal'], 'jet-cw' ),
			'use_as_remove_button' => $settings['wishlist_use_as_remove_button'],
			'button_icon_added'    => htmlspecialchars( $this->__render_icon( 'wishlist_button_icon_added', '%s', '', false ) ),
			'button_label_added'   => esc_html__( $settings['wishlist_button_label_added'], 'jet-cw' ),
			'_widget_id'           => $widget_id,
		);

		if ( class_exists( 'Jet_Woo_Builder' ) && jet_woo_builder_tools()->is_builder_content_save() ) {
			echo jet_woo_builder()->parser->get_macros_string( $this->get_name(), $widget_settings );
		} else {
			echo self::render_callback( $widget_settings );
		}

		$this->__close_wrap();

	}

}
